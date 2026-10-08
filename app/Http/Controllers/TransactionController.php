<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('print_voucher') == 1) {
            $voucherNumber = $request->get('voucher_number');
            $dateStr = $request->get('date');
            $date = Carbon::parse($dateStr);
            
            $itemDescs = $request->get('item_desc', []);
            $itemNps = $request->get('item_np', []);
            $itemAmounts = $request->get('item_amount', []);
            $itemKets = $request->get('item_ket', []);
            $prodi = $request->get('prodi');
            
            $items = [];
            $totalAmount = 0;
            foreach ($itemDescs as $index => $desc) {
                $amount = (float) ($itemAmounts[$index] ?? 0);
                $np = $itemNps[$index] ?? '';
                if (!empty($desc) || $amount > 0 || !empty($np)) {
                    $items[] = [
                        'no' => $index + 1,
                        'np' => $np,
                        'desc' => $desc,
                        'amount' => $amount,
                        'ket' => $itemKets[$index] ?? ''
                    ];
                    $totalAmount += $amount;
                }
            }
            
            $tx = null;
            if ($request->filled('transaction_id')) {
                $tx = Transaction::with(['category', 'job'])->find($request->transaction_id);
            }

            if (empty($items) && $tx) {
                if (!empty($tx->voucher_number)) {
                    $sameVoucherTxs = Transaction::with(['category', 'job'])
                        ->where('voucher_number', $tx->voucher_number)
                        ->where('account', $tx->account)
                        ->orderBy('id', 'asc')
                        ->get();
                } else {
                    $sameVoucherTxs = collect([$tx]);
                }

                $totalAmount = 0;
                foreach ($sameVoucherTxs as $index => $itemTx) {
                    $cleanNp = $itemTx->category ? str_replace(['-', ' '], '', $itemTx->category->code) : '';
                    $items[] = [
                        'no' => $index + 1,
                        'np' => $cleanNp,
                        'desc' => $itemTx->description,
                        'amount' => (float)$itemTx->amount,
                        'ket' => $itemTx->ket ?? ''
                    ];
                    $totalAmount += (float)$itemTx->amount;
                }
                if (empty($prodi)) {
                    $prodi = $tx->job ? $tx->job->name : 'Pusat';
                }
            }
            
            if (empty($prodi)) {
                $prodi = ($tx && $tx->job) ? $tx->job->name : 'Pusat';
            }
            
            $rawTerbilang = \App\Helpers\TerbilangHelper::spelling($totalAmount);
            $cleanTerbilang = preg_replace('/\s+rupiah\s*$/i', '', trim($rawTerbilang));
            $terbilang = ucwords(strtolower($cleanTerbilang)) . ' Rupiah';
            
            $approver = $request->get('approver', 'Ir. Rina Agustin Indriani, MURP');
            $verifier = $request->get('verifier', "Noor'aini Kartikarini, S.M");
            $payer = $request->get('payer', 'Irma Yaniarti');
            $defaultRecipient = (!empty($tx) && !empty($tx->paraf)) ? $tx->paraf : 'Titis Marsela';
            $recipient = $request->get('recipient', $defaultRecipient);
            
            if ($tx && !empty($voucherNumber)) {
                $tx->update(['voucher_number' => $voucherNumber]);
            }
            
            $transactionType = $request->get('transaction_type', 'expense');

            return view('print-voucher', compact(
                'voucherNumber',
                'date',
                'prodi',
                'items',
                'totalAmount',
                'terbilang',
                'approver',
                'verifier',
                'payer',
                'recipient',
                'transactionType'
            ));
        }

        $activeAccount = session('active_account', 'petty_cash');

        $accountDetails = [
            'petty_cash' => ['code' => '1-1110', 'name' => 'Kas Kecil (Petty Cash)', 'icon' => 'wallet'],
            'bank_mandiri_1' => ['code' => '1-1121', 'name' => 'Bank Mandiri Giro I', 'icon' => 'landmark'],
            'bank_mandiri_2' => ['code' => '1-1122', 'name' => 'Bank Mandiri Giro II', 'icon' => 'building-2'],
        ];
        $currentAccountInfo = $accountDetails[$activeAccount] ?? [
            'code' => '1-1100', 'name' => 'Kas / Bank', 'icon' => 'wallet'
        ];

        // All-time transactions for this account ordered chronologically to compute Bank Register running balance
        $allAccountTransactions = Transaction::where('account', $activeAccount)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'type', 'amount', 'date']);

        $runningBalances = [];
        $runningTotal = 0;
        foreach ($allAccountTransactions as $t) {
            $runningTotal += ($t->type === 'income' ? (float)$t->amount : -(float)$t->amount);
            $runningBalances[$t->id] = $runningTotal;
        }

        $currentAccountBalance = $runningTotal;
        
        // Fetch all categories for filter and form dropdowns
        $categories = Category::orderBy('name', 'asc')->get();

        // Fetch all active/available jobs for dropdowns
        $jobs = \App\Models\AccountingJob::academicOrder()->get();

        $isTrash = $request->get('status') === 'trash';
        $trashedCount = Transaction::onlyTrashed()->where('account', $activeAccount)->count();

        // Build transaction query
        $query = $isTrash
            ? Transaction::onlyTrashed()->with(['category', 'job'])->where('account', $activeAccount)
            : Transaction::with(['category', 'job'])->where('account', $activeAccount);

        // Filter: Search
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Filter: Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter: Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter: Job
        if ($request->filled('job_id')) {
            $query->where('job_id', $request->job_id);
        }

        // Filter: Month
        if ($request->filled('month') && $request->month !== 'all') {
            $query->whereMonth('date', $request->month);
            $query->whereYear('date', Carbon::now()->year);
        }

        // Filter: Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Calculate filtered totals for quick stats
        $totalsQuery = clone $query;
        $filteredIncome = (float) (clone $totalsQuery)->where('type', 'income')->sum('amount');
        $filteredExpense = (float) (clone $totalsQuery)->where('type', 'expense')->sum('amount');
        $filteredNet = $filteredIncome - $filteredExpense;

        // Sorting
        $sortColumn = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'desc');
        
        // Allowed sort columns
        if (in_array($sortColumn, ['date', 'amount'])) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('date', 'desc');
        }
        
        // Secondary ordering by created_at to maintain consistency
        $query->orderBy('created_at', 'desc');

        // Pagination
        $transactions = $query->paginate(15)->withQueryString();

        // Attach running balance to each displayed transaction
        foreach ($transactions as $tx) {
            $tx->running_balance = $runningBalances[$tx->id] ?? 0;
            if ($tx->journal_entry_id && $tx->category && str_starts_with($tx->category->code, '1-11')) {
                $counterpart = Transaction::where('journal_entry_id', $tx->journal_entry_id)
                    ->where('id', '!=', $tx->id)
                    ->with('category')
                    ->first();
                if ($counterpart && $counterpart->category) {
                    $tx->category = $counterpart->category;
                }
            }
        }

        return view('transactions', compact(
            'activeAccount',
            'currentAccountInfo',
            'currentAccountBalance',
            'categories',
            'jobs',
            'transactions',
            'filteredIncome',
            'filteredExpense',
            'filteredNet',
            'sortColumn',
            'sortDirection',
            'isTrash',
            'trashedCount'
        ));
    }

    public function store(Request $request)
    {
        $accountNames = [
            'petty_cash' => 'Kas Kecil (Petty Cash)',
            'bank_mandiri_1' => 'Bank Mandiri 1',
            'bank_mandiri_2' => 'Bank Mandiri 2',
        ];

        // Handle Multi-Item Transaction (Beberapa pos pengeluaran/pemasukan dalam 1 nomor bukti)
        if ($request->has('items') && is_array($request->items) && count($request->items) > 1) {
            $request->validate([
                'account' => 'required|in:petty_cash,bank_mandiri_1,bank_mandiri_2',
                'type' => 'required|in:income,expense,transfer',
                'to_account' => 'exclude_unless:type,transfer|required|different:account|in:petty_cash,bank_mandiri_1,bank_mandiri_2',
                'date' => 'required|date',
                'voucher_number' => 'nullable|string|max:255',
                'job_id' => 'nullable|exists:accounting_jobs,id',
                'paraf' => 'nullable|string|max:255',
                'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'items' => 'required|array|min:2',
                'items.*.category_id' => 'required_unless:type,transfer|nullable|exists:categories,id',
                'items.*.amount' => 'required|numeric|min:0.01',
                'items.*.description' => 'nullable|string|max:1000',
                'items.*.job_id' => 'nullable|exists:accounting_jobs,id',
                'items.*.ket' => 'nullable|string|max:255',
            ]);

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('attachments', 'public');
            }

            $savedCount = 0;
            $fromName = $accountNames[$request->account] ?? $request->account;

            if ($request->type === 'transfer') {
                $toName = $accountNames[$request->to_account] ?? $request->to_account;
                $transferCat = Category::where('code', '1-1100')->first()
                    ?? Category::firstOrCreate(
                        ['code' => '1-1100'],
                        ['name' => 'Kas dan Setara Kas', 'type' => 'expense', 'icon' => '💵', 'color' => '#3b82f6']
                    );

                $totalTransfer = 0;
                \Illuminate\Support\Facades\DB::transaction(function () use ($request, $transferCat, $fromName, $toName, $attachmentPath, &$savedCount, &$totalTransfer) {
                    foreach ($request->items as $item) {
                        $amt = (float)$item['amount'];
                        $totalTransfer += $amt;
                        $descOutflow = !empty($item['description']) ? trim($item['description']) : "Transfer dana ke {$toName}";
                        $descInflow = !empty($item['description']) ? trim($item['description']) : "Transfer dana dari {$fromName}";

                        // Sisi 1: Pengeluaran dari Rekening Asal
                        $outflow = Transaction::create([
                            'account' => $request->account,
                            'type' => 'expense',
                            'category_id' => $transferCat->id,
                            'job_id' => !empty($item['job_id']) ? $item['job_id'] : ($request->job_id ?: null),
                            'amount' => $amt,
                            'voucher_number' => $request->voucher_number ?: null,
                            'date' => $request->date,
                            'description' => $descOutflow,
                            'paraf' => $request->paraf ?: null,
                            'ket' => !empty($item['ket']) ? $item['ket'] : "Transfer Kas/Bank Keluar ke {$toName}",
                            'attachment' => $attachmentPath,
                        ]);

                        // Sisi 2: Pemasukan ke Rekening Tujuan
                        $inflow = Transaction::create([
                            'account' => $request->to_account,
                            'type' => 'income',
                            'category_id' => $transferCat->id,
                            'job_id' => !empty($item['job_id']) ? $item['job_id'] : ($request->job_id ?: null),
                            'amount' => $amt,
                            'voucher_number' => $request->voucher_number ?: null,
                            'date' => $request->date,
                            'description' => $descInflow,
                            'paraf' => $request->paraf ?: null,
                            'ket' => !empty($item['ket']) ? $item['ket'] : "Transfer Kas/Bank Masuk dari {$fromName}",
                            'attachment' => $attachmentPath,
                            'related_transaction_id' => $outflow->id,
                        ]);

                        $outflow->update(['related_transaction_id' => $inflow->id]);
                        $savedCount++;
                    }
                });

                session(['active_account' => $request->account]);
                $voucherText = $request->voucher_number ? " (No. Bukti: {$request->voucher_number})" : "";
                return redirect()->back()->with('success', "{$savedCount} pos rincian transfer{$voucherText} total Rp " . number_format($totalTransfer, 0, ',', '.') . " dari {$fromName} ke {$toName} berhasil diproses!");
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $attachmentPath, &$savedCount) {
                foreach ($request->items as $item) {
                    $description = !empty($item['description']) ? trim($item['description']) : null;
                    if (!$description) {
                        $cat = Category::find($item['category_id']);
                        $description = $cat ? $cat->name : ($request->description ?: '-');
                    }

                    Transaction::create([
                        'account' => $request->account,
                        'type' => $request->type,
                        'category_id' => $item['category_id'],
                        'job_id' => !empty($item['job_id']) ? $item['job_id'] : ($request->job_id ?: null),
                        'amount' => $item['amount'],
                        'voucher_number' => $request->voucher_number ?: null,
                        'date' => $request->date,
                        'description' => $description,
                        'paraf' => $request->paraf ?: null,
                        'ket' => !empty($item['ket']) ? $item['ket'] : ($request->ket ?: null),
                        'attachment' => $attachmentPath,
                    ]);
                    $savedCount++;
                }
            });

            session(['active_account' => $request->account]);
            $accountName = $accountNames[$request->account] ?? $request->account;
            $voucherText = $request->voucher_number ? " (No. Bukti: {$request->voucher_number})" : "";
            return redirect()->back()->with('success', "{$savedCount} pos rincian transaksi{$voucherText} berhasil ditambahkan ke {$accountName}!");
        }

        $request->validate([
            'account' => 'required|in:petty_cash,bank_mandiri_1,bank_mandiri_2',
            'type' => 'required|in:income,expense,transfer',
            'to_account' => 'exclude_unless:type,transfer|required|different:account|in:petty_cash,bank_mandiri_1,bank_mandiri_2',
            'category_id' => 'required_unless:type,transfer|nullable|exists:categories,id',
            'job_id' => 'nullable|exists:accounting_jobs,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'voucher_number' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'paraf' => 'nullable|string|max:255',
            'ket' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        // Handle Transfer Kas/Bank ala MYOB
        if ($request->type === 'transfer') {
            $transferCat = Category::where('code', '1-1100')->first()
                ?? Category::firstOrCreate(
                    ['code' => '1-1100'],
                    ['name' => 'Kas dan Setara Kas', 'type' => 'expense', 'icon' => '💵', 'color' => '#3b82f6']
                );

            $fromName = $accountNames[$request->account] ?? $request->account;
            $toName = $accountNames[$request->to_account] ?? $request->to_account;

            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $transferCat, $fromName, $toName, $attachmentPath) {
                // Sisi 1: Pengeluaran dari Rekening Asal
                $outflow = Transaction::create([
                    'account' => $request->account,
                    'type' => 'expense',
                    'category_id' => $transferCat->id,
                    'job_id' => $request->job_id ?: null,
                    'amount' => $request->amount,
                    'voucher_number' => $request->voucher_number ?: null,
                    'date' => $request->date,
                    'description' => $request->description ?: "Transfer dana ke {$toName}",
                    'paraf' => $request->paraf ?: null,
                    'ket' => $request->ket ?: "Transfer Kas/Bank Keluar ke {$toName}",
                    'attachment' => $attachmentPath,
                ]);

                // Sisi 2: Pemasukan ke Rekening Tujuan
                $inflow = Transaction::create([
                    'account' => $request->to_account,
                    'type' => 'income',
                    'category_id' => $transferCat->id,
                    'job_id' => $request->job_id ?: null,
                    'amount' => $request->amount,
                    'voucher_number' => $request->voucher_number ?: null,
                    'date' => $request->date,
                    'description' => $request->description ?: "Transfer dana dari {$fromName}",
                    'paraf' => $request->paraf ?: null,
                    'ket' => $request->ket ?: "Transfer Kas/Bank Masuk dari {$fromName}",
                    'attachment' => $attachmentPath,
                    'related_transaction_id' => $outflow->id,
                ]);

                // Hubungkan kedua transaksi berpasangan
                $outflow->update(['related_transaction_id' => $inflow->id]);
            });

            session(['active_account' => $request->account]);
            return redirect()->back()->with('success', "Transfer Rp " . number_format($request->amount, 0, ',', '.') . " dari {$fromName} ke {$toName} berhasil diproses!");
        }

        Transaction::create([
            'account' => $request->account,
            'type' => $request->type,
            'category_id' => $request->category_id,
            'job_id' => $request->job_id ?: null,
            'amount' => $request->amount,
            'voucher_number' => $request->voucher_number ?: null,
            'date' => $request->date,
            'description' => $request->description,
            'paraf' => $request->paraf ?: null,
            'ket' => $request->ket,
            'attachment' => $attachmentPath,
        ]);

        session(['active_account' => $request->account]);

        $accountName = $accountNames[$request->account] ?? $request->account;
        return redirect()->back()->with('success', "Transaksi berhasil ditambahkan ke {$accountName}!");
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $request->validate([
            'account' => 'required|in:petty_cash,bank_mandiri_1,bank_mandiri_2',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:categories,id',
            'job_id' => 'nullable|exists:accounting_jobs,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'voucher_number' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'paraf' => 'nullable|string|max:255',
            'ket' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = [
            'account' => $request->account,
            'type' => $request->type,
            'category_id' => $request->category_id,
            'job_id' => $request->job_id ?: null,
            'amount' => $request->amount,
            'voucher_number' => $request->voucher_number ?: null,
            'date' => $request->date,
            'description' => $request->description,
            'paraf' => $request->paraf ?: null,
            'ket' => $request->ket,
        ];

        if ($request->hasFile('attachment')) {
            if ($transaction->attachment) {
                Storage::disk('public')->delete($transaction->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }

        $transaction->update($data);

        session(['active_account' => $request->account]);

        $accountNames = [
            'petty_cash' => 'Kas Kecil',
            'bank_mandiri_1' => 'Bank Mandiri 1',
            'bank_mandiri_2' => 'Bank Mandiri 2',
        ];
        $accountName = $accountNames[$request->account] ?? $request->account;
        return redirect()->back()->with('success', "Transaksi berhasil diperbarui di {$accountName}!");
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $desc = $transaction->description ?: 'Transaksi';
        $voucher = $transaction->voucher_number ? " [{$transaction->voucher_number}]" : "";
        $deletedId = $transaction->id;

        // Jika transaksi transfer berpasangan, soft-delete juga pasangannya
        if ($transaction->type === 'transfer' && $transaction->related_transaction_id) {
            $related = Transaction::find($transaction->related_transaction_id);
            if ($related) {
                $related->delete();
            }
        }

        $transaction->delete();

        return redirect()->back()->with('success_deleted', [
            'id' => $deletedId,
            'message' => "Transaksi{$voucher} \"{$desc}\" berhasil dipindahkan ke Sampah.",
        ]);
    }

    public function restore($id)
    {
        $transaction = Transaction::onlyTrashed()->findOrFail($id);
        $desc = $transaction->description ?: 'Transaksi';
        $voucher = $transaction->voucher_number ? " [{$transaction->voucher_number}]" : "";

        // Jika transaksi transfer berpasangan, pulihkan juga pasangannya
        if ($transaction->type === 'transfer' && $transaction->related_transaction_id) {
            $related = Transaction::onlyTrashed()->find($transaction->related_transaction_id);
            if ($related) {
                $related->restore();
            }
        }

        $transaction->restore();

        return redirect()->back()->with('success', "Transaksi{$voucher} \"{$desc}\" berhasil dipulihkan kembali ke Buku Kas!");
    }

    public function forceDelete($id)
    {
        $transaction = Transaction::onlyTrashed()->findOrFail($id);
        $desc = $transaction->description ?: 'Transaksi';

        if ($transaction->attachment) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($transaction->attachment);
        }

        if ($transaction->type === 'transfer' && $transaction->related_transaction_id) {
            $related = Transaction::onlyTrashed()->find($transaction->related_transaction_id);
            if ($related) {
                if ($related->attachment) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($related->attachment);
                }
                $related->forceDelete();
            }
        }

        $transaction->forceDelete();

        return redirect()->back()->with('success', "Transaksi \"{$desc}\" telah dihapus secara permanen.");
    }

    public function bulkDelete(Request $request)
    {
        return redirect()->back()->with('error', 'Silakan gunakan tombol Hapus pada masing-masing transaksi.');
    }

    public function exportCsv(Request $request)
    {
        $activeAccount = session('active_account', 'petty_cash');
        
        $accountDetails = [
            'petty_cash' => ['code' => '1-1110', 'name' => 'Kas Kecil (Petty Cash)'],
            'bank_mandiri_1' => ['code' => '1-1121', 'name' => 'Bank Mandiri Giro I'],
            'bank_mandiri_2' => ['code' => '1-1122', 'name' => 'Bank Mandiri Giro II'],
        ];
        $accountInfo = $accountDetails[$activeAccount] ?? ['code' => '1-1100', 'name' => 'Kas / Bank'];

        $allAccountTransactions = Transaction::where('account', $activeAccount)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'type', 'amount', 'date']);

        $runningBalances = [];
        $runningTotal = 0;
        foreach ($allAccountTransactions as $t) {
            $runningTotal += ($t->type === 'income' ? (float)$t->amount : -(float)$t->amount);
            $runningBalances[$t->id] = $runningTotal;
        }
        
        $query = Transaction::with(['category', 'job'])->where('account', $activeAccount);

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('job_id')) {
            $query->where('job_id', $request->job_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('date', 'desc')->get();

        $accountLabel = $accountInfo['name'];
        $filename = "Bank_Register_" . str_replace(' ', '_', $accountInfo['code'] . '_' . $accountLabel) . "_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($transactions, $accountInfo, $runningBalances) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, ['BANK REGISTER (BUKU KAS & BANK) - MYOB STANDARD']);
            fputcsv($file, ['Akun: [' . $accountInfo['code'] . '] ' . $accountInfo['name']]);
            fputcsv($file, ['Tanggal Unduh: ' . date('d/m/Y H:i')]);
            fputcsv($file, []);
            fputcsv($file, ['Tanggal', 'No. Bukti / ID#', 'Akun / Kategori (COA)', 'Proyek (Job)', 'Uraian / Deskripsi', 'Penerimaan / Masuk (Deposit)', 'Pengeluaran / Keluar (Withdrawal)', 'Saldo Berjalan (Balance)']);

            foreach ($transactions as $t) {
                $deposit = ($t->type === 'income') ? $t->amount : 0;
                $withdrawal = ($t->type === 'expense') ? $t->amount : 0;
                $balance = $runningBalances[$t->id] ?? 0;
                $catLabel = $t->category ? ($t->category->code ? "[{$t->category->code}] " : '') . $t->category->name : '—';
                $jobLabel = $t->job ? "[{$t->job->code}] {$t->job->name}" : '—';

                fputcsv($file, [
                    $t->date->format('d/m/Y'),
                    $t->voucher_number ?? '—',
                    $catLabel,
                    $jobLabel,
                    $t->description,
                    $deposit,
                    $withdrawal,
                    $balance
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_buku_kas.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM agar Excel mengenali UTF-8 dengan benar
            fputs($file, "\xEF\xBB\xBF");
            
            // Header
            fputcsv($file, ['Tanggal', 'No. Bukti', 'NP', 'Uraian', 'Debet', 'Kredit', 'Saldo', 'Paraf', 'Ket'], ';');
            
            // Data contoh
            $data = [
                [date('d-M-Y'), 'BKT-001', '', 'Saldo Awal', '1000000', '0', '1000000', '', ''],
                [date('d-M-Y', strtotime('+1 day')), 'BKT-002', '5-1100', 'Beli ATK', '0', '50000', '950000', '', '']
            ];
            
            foreach ($data as $row) {
                fputcsv($file, $row, ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt'
        ]);

        $activeAccount = session('active_account', 'petty_cash');
        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = [];
        if ($extension === 'csv' || $extension === 'txt') {
            if (($handle = fopen($path, 'r')) !== false) {
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (count($data) === 1 && str_contains($data[0], ';')) {
                        $data = explode(';', $data[0]);
                    }
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            try {
                $rows = \App\Helpers\XlsxParser::parse($path);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
            }
        }

        if (empty($rows)) {
            return redirect()->back()->with('error', 'File kosong atau tidak dapat dibaca.');
        }

        // Header detection logic
        $headerRow = $rows[0];
        $dateIndex = 0;
        $voucherIndex = 1;
        $npIndex = 2;
        $descriptionIndex = 3;
        $debetIndex = 4;
        $kreditIndex = 5;
        $parafIndex = 7;
        $ketIndex = 8;
        $hasHeader = false;

        foreach ($headerRow as $index => $colVal) {
            $colValClean = strtolower(trim((string)$colVal));
            if (str_contains($colValClean, 'tgl') || str_contains($colValClean, 'tanggal') || str_contains($colValClean, 'date')) {
                $dateIndex = $index;
                $hasHeader = true;
            } elseif (str_contains($colValClean, 'bukti') || str_contains($colValClean, 'voucher') || str_contains($colValClean, 'no.')) {
                $voucherIndex = $index;
                $hasHeader = true;
            } elseif ($colValClean === 'np' || str_contains($colValClean, 'akun') || str_contains($colValClean, 'acc') || str_contains($colValClean, 'perkiraan')) {
                $npIndex = $index;
                $hasHeader = true;
            } elseif (str_contains($colValClean, 'uraian') || str_contains($colValClean, 'desc') || str_contains($colValClean, 'keterangan') || str_contains($colValClean, 'detail')) {
                $descriptionIndex = $index;
                $hasHeader = true;
            } elseif (str_contains($colValClean, 'debet') || str_contains($colValClean, 'debit') || str_contains($colValClean, 'masuk') || str_contains($colValClean, 'inflow')) {
                $debetIndex = $index;
                $hasHeader = true;
            } elseif (str_contains($colValClean, 'kredit') || str_contains($colValClean, 'keluar') || str_contains($colValClean, 'outflow')) {
                $kreditIndex = $index;
                $hasHeader = true;
            } elseif (str_contains($colValClean, 'paraf') || str_contains($colValClean, 'ttd')) {
                $parafIndex = $index;
                $hasHeader = true;
            } elseif (str_contains($colValClean, 'ket') || str_contains($colValClean, 'note')) {
                $ketIndex = $index;
                $hasHeader = true;
            }
        }

        $startIdx = $hasHeader ? 1 : 0;
        $successCount = 0;

        $cleanAmount = function($val) {
            if (empty($val)) return 0.0;
            $val = str_replace(['Rp', 'rp', ' ', "\xc2\xa0"], '', $val);
            if (str_contains($val, '.') && str_contains($val, ',')) {
                if (strrpos($val, '.') > strrpos($val, ',')) {
                    $val = str_replace(',', '', $val);
                } else {
                    $val = str_replace('.', '', $val);
                    $val = str_replace(',', '.', $val);
                }
            } else {
                if (str_contains($val, ',')) {
                    if (preg_match('/,\d{1,2}$/', $val)) {
                        $val = str_replace(',', '.', $val);
                    } else {
                        $val = str_replace(',', '', $val);
                    }
                }
                if (str_contains($val, '.')) {
                    if (preg_match('/\.\d{3}/', $val)) {
                        $val = str_replace('.', '', $val);
                    }
                }
            }
            return (float) preg_replace('/[^0-9.]/', '', $val);
        };

        // Standard categories by NP codes
        $npCategoryMapping = [
            '11599' => ['name' => 'Piutang Panjar', 'icon' => '🎓', 'color' => '#f59e0b'],
            '11121' => ['name' => 'Kas Bank Operasional', 'icon' => '🏦', 'color' => '#6366f1'],
            '11111' => ['name' => 'Kas Kecil (Petty Cash)', 'icon' => '💵', 'color' => '#10b981'],
        ];

        for ($i = $startIdx; $i < count($rows); $i++) {
            $row = $rows[$i];

            if (count($row) < 3) continue;

            $dateVal = isset($row[$dateIndex]) ? trim((string)$row[$dateIndex]) : '';
            $voucherVal = isset($row[$voucherIndex]) ? trim((string)$row[$voucherIndex]) : '';
            $npVal = isset($row[$npIndex]) ? trim((string)$row[$npIndex]) : '';
            $descriptionVal = isset($row[$descriptionIndex]) ? trim((string)$row[$descriptionIndex]) : '';
            $debetVal = isset($row[$debetIndex]) ? trim((string)$row[$debetIndex]) : '';
            $kreditVal = isset($row[$kreditIndex]) ? trim((string)$row[$kreditIndex]) : '';
            $parafVal = isset($row[$parafIndex]) ? trim((string)$row[$parafIndex]) : '';
            $ketVal = isset($row[$ketIndex]) ? trim((string)$row[$ketIndex]) : '';

            if (empty($dateVal) && empty($descriptionVal) && empty($debetVal) && empty($kreditVal)) {
                continue;
            }

            // Parse Date
            try {
                if (is_numeric($dateVal) && (float)$dateVal > 40000) {
                    $unixTimestamp = ($dateVal - 25569) * 86400;
                    $date = Carbon::parse(date('Y-m-d', $unixTimestamp));
                } else {
                    $date = Carbon::parse($dateVal);
                }
            } catch (\Exception $e) {
                $date = Carbon::today();
            }

            // Parse Amount
            $debet = $cleanAmount($debetVal);
            $kredit = $cleanAmount($kreditVal);

            $type = 'expense';
            $amount = 0.0;

            if ($debet > 0) {
                $type = 'income';
                $amount = $debet;
            } elseif ($kredit > 0) {
                $type = 'expense';
                $amount = $kredit;
            } else {
                continue;
            }

            $cleanNp = preg_replace('/[^0-9]/', '', $npVal);

            // Find category
            $category = null;
            if (!empty($npVal)) {
                $trimmedNp = trim($npVal);
                $category = Category::where('code', $trimmedNp)
                    ->orWhere('code', $cleanNp)
                    ->orWhereRaw("REPLACE(REPLACE(code, '-', ''), ' ', '') = ?", [$cleanNp])
                    ->first();
            }

            if (!$category) {
                $descLower = strtolower($descriptionVal);
                if (str_contains($descLower, 'panjar')) {
                    $category = Category::where('code', '11599')->orWhere('name', 'like', '%panjar%')->first();
                } elseif (str_contains($descLower, 'saldo awal')) {
                    $category = Category::where('name', 'like', '%saldo awal%')->orWhere('name', 'like', '%modal%')->orWhere('name', 'like', '%equity%')->first();
                } elseif (str_contains($descLower, 'gaji') || str_contains($descLower, 'honor')) {
                    $category = Category::where('name', 'like', '%gaji%')->orWhere('name', 'like', '%honor%')->first();
                } elseif (str_contains($descLower, 'spp') || str_contains($descLower, 'uang ujian')) {
                    $category = Category::where('name', 'like', '%spp%')->orWhere('name', 'like', '%ujian%')->first();
                } elseif (str_contains($descLower, 'listrik') || str_contains($descLower, 'token')) {
                    $category = Category::where('name', 'like', '%listrik%')->first();
                } elseif (str_contains($descLower, 'air') || str_contains($descLower, 'pdam')) {
                    $category = Category::where('name', 'like', '%air%')->first();
                }
            }

            if (!$category) {
                $catName = "Akun " . $npVal;
                if (isset($npCategoryMapping[$cleanNp])) {
                    $catName = $npCategoryMapping[$cleanNp]['name'];
                    $icon = $npCategoryMapping[$cleanNp]['icon'];
                    $color = $npCategoryMapping[$cleanNp]['color'];
                } else {
                    $icon = $type === 'income' ? '💵' : '📋';
                    $color = '#6366f1';
                }

                if (empty($cleanNp) && str_contains(strtolower($descriptionVal), 'panjar')) {
                    $cleanNp = '11599';
                    $catName = 'Piutang Panjar';
                    $icon = '🎓';
                } elseif (empty($cleanNp) && str_contains(strtolower($descriptionVal), 'saldo awal')) {
                    $catName = 'Saldo Awal';
                    $icon = '💰';
                }

                $category = Category::create([
                    'code' => $cleanNp ?: null,
                    'name' => $catName,
                    'type' => $type,
                    'icon' => $icon,
                    'color' => $color
                ]);
            }

            // Save Transaction
            Transaction::create([
                'account' => $activeAccount,
                'type' => $type,
                'voucher_number' => $voucherVal ?: null,
                'category_id' => $category->id,
                'amount' => $amount,
                'description' => $descriptionVal,
                'paraf' => $parafVal ?: null,
                'ket' => $ketVal ?: null,
                'date' => $date->format('Y-m-d'),
            ]);

            $successCount++;
        }

        return redirect()->back()->with('success', "$successCount transaksi berhasil diimport!");
    }
}
