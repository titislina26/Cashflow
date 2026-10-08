<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\AccountingJob;
use App\Models\JournalEntry;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $activeAccount = session('active_account', 'petty_cash');

        $query = JournalEntry::with(['transactions.category', 'transactions.job']);

        // Search in memo or journal_number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('journal_number', 'like', "%{$search}%")
                  ->orWhere('memo', 'like', "%{$search}%");
            });
        }

        // Month filter
        if ($request->filled('month') && $request->month !== 'all') {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', Carbon::now()->year);
        }

        // Date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $totalEntries = (clone $query)->count();
        $totalVolume = (clone $query)->sum('total_amount');

        $journalEntries = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Generate next suggested journal number (e.g. JU-2609-001)
        $monthPrefix = 'JU-' . Carbon::now()->format('ym') . '-';
        $latestThisMonth = JournalEntry::where('journal_number', 'like', "{$monthPrefix}%")
            ->orderBy('journal_number', 'desc')
            ->first();

        $nextNum = 1;
        if ($latestThisMonth) {
            $parts = explode('-', $latestThisMonth->journal_number);
            $lastSeq = end($parts);
            $nextNum = intval($lastSeq) + 1;
        }
        $suggestedJournalNumber = $monthPrefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $categories = Category::orderBy('code', 'asc')->get();
        $jobs = AccountingJob::academicOrder()->get();

        return view('journal-entries.index', compact(
            'activeAccount',
            'journalEntries',
            'totalEntries',
            'totalVolume',
            'suggestedJournalNumber',
            'categories',
            'jobs'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'journal_number' => 'required|string|max:50|unique:journal_entries,journal_number',
            'date' => 'required|date',
            'memo' => 'required|string|max:500',
            'lines' => 'required|array|min:2',
            'lines.*.category_id' => 'required|exists:categories,id',
            'lines.*.job_id' => 'nullable|exists:accounting_jobs,id',
            'lines.*.description' => 'nullable|string|max:255',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ], [
            'journal_number.unique' => 'Nomor Jurnal ini sudah pernah digunakan.',
            'lines.min' => 'Jurnal Umum membutuhkan minimal 2 baris akun (Debet dan Kredit).',
        ]);

        $lines = $request->input('lines', []);
        $totalDebit = 0;
        $totalCredit = 0;
        $validLines = [];

        foreach ($lines as $line) {
            $debit = (float)($line['debit'] ?? 0);
            $credit = (float)($line['credit'] ?? 0);

            if ($debit > 0 && $credit > 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Satu baris hanya boleh diisi Debet ATAU Kredit, tidak boleh keduanya.');
            }

            if ($debit > 0 || $credit > 0) {
                $totalDebit += $debit;
                $totalCredit += $credit;
                $validLines[] = [
                    'category_id' => $line['category_id'],
                    'job_id' => !empty($line['job_id']) ? $line['job_id'] : null,
                    'description' => $line['description'] ?? null,
                    'debit' => $debit,
                    'credit' => $credit,
                ];
            }
        }

        if (count($validLines) < 2) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Jurnal Umum minimal harus memiliki 2 baris transaksi yang bernilai (Debet & Kredit).');
        }

        // Validate double-entry equality (out of balance must be 0)
        if (abs($totalDebit - $totalCredit) > 0.01) {
            $outOfBalance = abs($totalDebit - $totalCredit);
            return redirect()->back()
                ->withInput()
                ->with('error', "Jurnal tidak seimbang! Total Debet (Rp " . number_format($totalDebit, 0, ',', '.') . ") tidak sama dengan Total Kredit (Rp " . number_format($totalCredit, 0, ',', '.') . "). Selisih: Rp " . number_format($outOfBalance, 0, ',', '.'));
        }

        DB::transaction(function () use ($request, $validLines, $totalDebit) {
            $journalEntry = JournalEntry::create([
                'journal_number' => $request->journal_number,
                'date' => $request->date,
                'memo' => $request->memo,
                'total_amount' => $totalDebit,
            ]);

            foreach ($validLines as $item) {
                $category = Category::find($item['category_id']);
                $code = $category ? $category->code : '';

                // Determine matching account in case cash/bank is involved
                $account = 'general_journal';
                if ($code === '1-1110') {
                    $account = 'petty_cash';
                } elseif ($code === '1-1121') {
                    $account = 'bank_mandiri_1';
                } elseif ($code === '1-1122') {
                    $account = 'bank_mandiri_2';
                }

                $isDebit = $item['debit'] > 0;
                $amount = $isDebit ? $item['debit'] : $item['credit'];
                // Accounting convention in reports:
                // For cash/bank accounts: Debet = cash in (income), Kredit = cash out (expense)
                // For non-cash accounts: Debet = expense/asset increase (expense), Kredit = income/liability increase (income)
                if ($account !== 'general_journal') {
                    $type = $isDebit ? 'income' : 'expense';
                } else {
                    $type = $isDebit ? 'expense' : 'income';
                }

                Transaction::create([
                    'account' => $account,
                    'type' => $type,
                    'amount' => $amount,
                    'category_id' => $item['category_id'],
                    'job_id' => $item['job_id'],
                    'date' => $request->date,
                    'description' => $item['description'] ?: $request->memo,
                    'voucher_number' => $request->journal_number,
                    'journal_entry_id' => $journalEntry->id,
                ]);
            }
        });

        return redirect()->route('journal-entries.index')
            ->with('success', "Jurnal Umum [{$request->journal_number}] berhasil disimpan!");
    }

    public function show($id)
    {
        $journalEntry = JournalEntry::with(['transactions.category', 'transactions.job'])->findOrFail($id);
        return response()->json($journalEntry);
    }

    public function destroy($id)
    {
        $journalEntry = JournalEntry::findOrFail($id);
        $num = $journalEntry->journal_number;
        $journalEntry->delete();

        return redirect()->route('journal-entries.index')
            ->with('success', "Jurnal Umum [{$num}] berhasil dihapus!");
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = JournalEntry::with(['transactions.category', 'transactions.job']);
        if ($startDate) $query->whereDate('date', '>=', $startDate);
        if ($endDate) $query->whereDate('date', '<=', $endDate);

        $entries = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        $filename = "General_Journal_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($entries) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, ['SEKOLAH TINGGI TEKNOLOGI PEKERJAAN UMUM']);
            fputcsv($file, ['JURNAL UMUM (GENERAL JOURNAL)']);
            fputcsv($file, ['Diekspor pada: ' . date('d-m-Y H:i')]);
            fputcsv($file, []);

            fputcsv($file, [
                'Tanggal',
                'No. Bukti Jurnal',
                'Memo / Keterangan',
                'Kode Akun',
                'Nama Akun',
                'Job / Proyek',
                'Uraian Baris',
                'Debet (Rp)',
                'Kredit (Rp)'
            ]);

            foreach ($entries as $je) {
                foreach ($je->transactions as $tx) {
                    $isDebit = ($tx->account !== 'general_journal') ? ($tx->type === 'income') : ($tx->type === 'expense');
                    fputcsv($file, [
                        $je->date->format('d/m/Y'),
                        $je->journal_number,
                        $je->memo,
                        $tx->category->code ?? '—',
                        $tx->category->name ?? '—',
                        $tx->job ? "[{$tx->job->code}] {$tx->job->name}" : '—',
                        $tx->description ?? '',
                        $isDebit ? $tx->amount : 0,
                        !$isDebit ? $tx->amount : 0,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
