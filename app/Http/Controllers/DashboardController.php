<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $activeAccount = session('active_account', 'petty_cash');

        // Fetch categories and jobs for modal dropdowns on Dashboard
        $categories = Category::orderBy('name', 'asc')->get();
        $jobs = \App\Models\AccountingJob::academicOrder()->get();

        // Total metrics (all time)
        $allTimeIncome = Transaction::where('account', $activeAccount)
            ->where('type', 'income')
            ->sum('amount');

        $allTimeExpense = Transaction::where('account', $activeAccount)
            ->where('type', 'expense')
            ->sum('amount');

        $netBalance = $allTimeIncome - $allTimeExpense;
        $allTimeTransactionsCount = Transaction::where('account', $activeAccount)->count();

        // SIK-Flow specific metrics:
        // 1. Total Saldo Kas
        $pettyCashBalance = Transaction::where('account', 'petty_cash')->where('type', 'income')->sum('amount') - Transaction::where('account', 'petty_cash')->where('type', 'expense')->sum('amount');
        $bankMandiri1Balance = Transaction::where('account', 'bank_mandiri_1')->where('type', 'income')->sum('amount') - Transaction::where('account', 'bank_mandiri_1')->where('type', 'expense')->sum('amount');
        $bankMandiri2Balance = Transaction::where('account', 'bank_mandiri_2')->where('type', 'income')->sum('amount') - Transaction::where('account', 'bank_mandiri_2')->where('type', 'expense')->sum('amount');
        
        $bankBalance = $bankMandiri1Balance + $bankMandiri2Balance;
        $totalSaldoKas = $pettyCashBalance + $bankMandiri1Balance + $bankMandiri2Balance;

        // 2. Total Panjar Aktif (ACC 11599, 1-15xx, 1-14xx, or Category containing "panjar")
        $panjarCategoryIds = Category::where(function($q) {
            $q->where('code', '11599')
              ->orWhere('code', 'like', '1-14%')
              ->orWhere('code', 'like', '1-15%')
              ->orWhere('name', 'like', '%panjar%');
        })->pluck('id')->toArray();

        // Panjar yang sudah selesai dipertanggungjawabkan (memiliki SPJ via related_transaction_id)
        $settledPanjarIds = Transaction::whereNotNull('related_transaction_id')
            ->pluck('related_transaction_id')
            ->toArray();

        // Ambil transaksi panjar aktif yang belum dipertanggungjawabkan
        $panjarTransactions = Transaction::with(['category', 'job', 'journalEntry'])
            ->whereIn('category_id', $panjarCategoryIds)
            ->whereNotIn('id', $settledPanjarIds)
            ->whereNull('related_transaction_id')
            ->where('description', 'not like', '[PEMBATALAN]%')
            ->orderBy('date', 'desc')
            ->get();

        $totalPanjarAktif = max(0, (float) (
            $panjarTransactions->where('type', 'expense')->sum('amount') - 
            $panjarTransactions->where('type', 'income')->sum('amount')
        ));

        $overduePanjarCount = 0;
        foreach ($panjarTransactions as $tx) {
            if (\Carbon\Carbon::now()->diffInDays($tx->date) > 30) {
                $overduePanjarCount++;
            }
        }

        $panjarSummaryByAccount = [
            'petty_cash' => 0,
            'bank_mandiri_1' => 0,
            'bank_mandiri_2' => 0,
        ];

        $panjarBySource = [
            'petty_cash' => [
                'key' => 'petty_cash',
                'code' => '1-1110',
                'name' => 'Kas Kecil (Petty Cash)',
                'badge' => 'Kas Kecil',
                'color' => '#d97706',
                'bg' => 'rgba(245, 158, 11, 0.08)',
                'border' => 'rgba(245, 158, 11, 0.25)',
                'icon' => 'wallet',
                'total' => 0,
                'active_count' => 0,
                'recipients' => [],
                'items' => [],
            ],
            'bank_mandiri_1' => [
                'key' => 'bank_mandiri_1',
                'code' => '1-1121',
                'name' => 'Bank Mandiri Giro I',
                'badge' => 'Mandiri 1',
                'color' => '#2563eb',
                'bg' => 'rgba(59, 130, 246, 0.08)',
                'border' => 'rgba(59, 130, 246, 0.25)',
                'icon' => 'landmark',
                'total' => 0,
                'active_count' => 0,
                'recipients' => [],
                'items' => [],
            ],
            'bank_mandiri_2' => [
                'key' => 'bank_mandiri_2',
                'code' => '1-1122',
                'name' => 'Bank Mandiri Giro II',
                'badge' => 'Mandiri 2',
                'color' => '#7c3aed',
                'bg' => 'rgba(139, 92, 246, 0.08)',
                'border' => 'rgba(139, 92, 246, 0.25)',
                'icon' => 'building-2',
                'total' => 0,
                'active_count' => 0,
                'recipients' => [],
                'items' => [],
            ],
        ];

        foreach ($panjarTransactions as $tx) {
            $sourceAccount = $tx->account;

            if ($sourceAccount === 'general_journal' && $tx->journal_entry_id) {
                $counterpart = Transaction::where('journal_entry_id', $tx->journal_entry_id)
                    ->where('id', '!=', $tx->id)
                    ->where('account', '!=', 'general_journal')
                    ->first();
                if ($counterpart) {
                    $sourceAccount = $counterpart->account;
                }
            }

            $tx->source_account = $sourceAccount;
            $tx->source_account_name = match($sourceAccount) {
                'petty_cash' => 'Kas Kecil',
                'bank_mandiri_1' => 'Bank Mandiri 1',
                'bank_mandiri_2' => 'Bank Mandiri 2',
                default => 'Kas / Bank'
            };

            // Extract recipient name ("Siapa")
            $recipientName = trim(preg_replace('/^(panjar|uang\s*muka|um|u\/m|biaya\s*dimuka)\s*[:\-]?\s*/i', '', $tx->description ?: ''));
            if (empty($recipientName)) {
                $recipientName = $tx->description ?: 'Tanpa Nama';
            }
            $tx->recipient_name = $recipientName;

            $voucherNo = $tx->voucher_number ?: ($tx->journalEntry ? $tx->journalEntry->entry_number : '—');
            $jobStr = $tx->job ? '[' . $tx->job->code . '] ' . $tx->job->name : '—';
            $val = ($tx->type === 'expense' ? 1 : -1) * (float) $tx->amount;

            if (isset($panjarSummaryByAccount[$sourceAccount])) {
                $panjarSummaryByAccount[$sourceAccount] += $val;
            }

                $ageDays = (int) \Carbon\Carbon::now()->diffInDays($tx->date);
                $statusLabel = $ageDays > 30 ? '> 30 Hari (Jatuh Tempo)' : ($ageDays >= 15 ? '15 - 30 Hari' : '< 15 Hari');
                $statusColor = $ageDays > 30 ? '#ef4444' : ($ageDays >= 15 ? '#f59e0b' : '#10b981');
                $statusBg = $ageDays > 30 ? 'rgba(239, 68, 68, 0.15)' : ($ageDays >= 15 ? 'rgba(245, 158, 11, 0.15)' : 'rgba(16, 185, 129, 0.15)');

                if (!isset($panjarBySource[$sourceAccount]['recipients'][$recipientName])) {
                    $panjarBySource[$sourceAccount]['recipients'][$recipientName] = [
                        'name' => $recipientName,
                        'total' => 0,
                        'job' => $jobStr,
                        'last_date' => $tx->date->format('d M Y'),
                        'description' => $tx->description ?: '—',
                        'voucher' => $voucherNo,
                        'age_days' => $ageDays,
                        'is_overdue' => $ageDays > 30,
                        'status_label' => $statusLabel,
                        'status_color' => $statusColor,
                        'status_bg' => $statusBg,
                        'count' => 0
                    ];
                } else {
                    if ($ageDays > $panjarBySource[$sourceAccount]['recipients'][$recipientName]['age_days']) {
                        $panjarBySource[$sourceAccount]['recipients'][$recipientName]['age_days'] = $ageDays;
                        $panjarBySource[$sourceAccount]['recipients'][$recipientName]['is_overdue'] = $ageDays > 30;
                        $panjarBySource[$sourceAccount]['recipients'][$recipientName]['status_label'] = $statusLabel;
                        $panjarBySource[$sourceAccount]['recipients'][$recipientName]['status_color'] = $statusColor;
                        $panjarBySource[$sourceAccount]['recipients'][$recipientName]['status_bg'] = $statusBg;
                    }
                }
                $panjarBySource[$sourceAccount]['recipients'][$recipientName]['total'] += $val;
                $panjarBySource[$sourceAccount]['recipients'][$recipientName]['count']++;

                $panjarBySource[$sourceAccount]['items'][] = [
                    'id' => $tx->id,
                    'date' => $tx->date->format('d M Y'),
                    'voucher' => $voucherNo,
                    'recipient' => $recipientName,
                    'job' => $jobStr,
                    'description' => $tx->description ?: '—',
                    'amount' => (float) $tx->amount,
                    'type' => $tx->type,
                    'age_days' => $ageDays,
                    'is_overdue' => $ageDays > 30,
                    'status_label' => $statusLabel,
                    'status_color' => $statusColor,
                    'status_bg' => $statusBg,
                ];
        }

        foreach ($panjarBySource as $srcKey => &$src) {
            $activeRecipients = array_filter($src['recipients'], fn($r) => $r['total'] > 0);
            $src['active_count'] = count($activeRecipients);
        }
        unset($src);

        // Recent transactions
        $recentTransactions = Transaction::with('category')
            ->where('account', $activeAccount)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentTransactions as $tx) {
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

        // Last 6 months trend
        $months = [];
        $incomeTrend = [];
        $expenseTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->translatedFormat('F Y');

            $income = Transaction::where('account', $activeAccount)
                ->where('type', 'income')
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');

            $expense = Transaction::where('account', $activeAccount)
                ->where('type', 'expense')
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');

            $incomeTrend[] = (float) $income;
            $expenseTrend[] = (float) $expense;
        }

        // Category breakdown for current month (for donut charts)
        $monthTxs = Transaction::with('category')
            ->where('account', $activeAccount)
            ->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->get();

        foreach ($monthTxs as $tx) {
            if ($tx->journal_entry_id && $tx->category && str_starts_with($tx->category->code, '1-11')) {
                $counterpart = Transaction::where('journal_entry_id', $tx->journal_entry_id)
                    ->where('id', '!=', $tx->id)
                    ->with('category')
                    ->first();
                if ($counterpart && $counterpart->category) {
                    $tx->category = $counterpart->category;
                    $tx->category_id = $counterpart->category_id;
                }
            }
        }

        $incomeCategories = $monthTxs->where('type', 'income')
            ->groupBy('category_id')
            ->map(function ($group) {
                $cat = $group->first()->category;
                return [
                    'name' => $cat ? $cat->name : 'Lainnya',
                    'amount' => (float) $group->sum('amount'),
                    'color' => $cat->color ?? '#10b981',
                    'icon' => $cat->icon ?? '💵',
                ];
            })->values()->toArray();

        $expenseCategories = $monthTxs->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($group) {
                $cat = $group->first()->category;
                return [
                    'name' => $cat ? $cat->name : 'Lainnya',
                    'amount' => (float) $group->sum('amount'),
                    'color' => $cat->color ?? '#f59e0b',
                    'icon' => $cat->icon ?? '📋',
                ];
            })->values()->toArray();

        // Monthly comparison: This month vs Last month
        $thisMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        $thisMonthIncome = Transaction::where('account', $activeAccount)
            ->where('type', 'income')
            ->whereYear('date', $thisMonth->year)
            ->whereMonth('date', $thisMonth->month)
            ->sum('amount');

        $thisMonthExpense = Transaction::where('account', $activeAccount)
            ->where('type', 'expense')
            ->whereYear('date', $thisMonth->year)
            ->whereMonth('date', $thisMonth->month)
            ->sum('amount');

        $lastMonthIncome = Transaction::where('account', $activeAccount)
            ->where('type', 'income')
            ->whereYear('date', $lastMonth->year)
            ->whereMonth('date', $lastMonth->month)
            ->sum('amount');

        $lastMonthExpense = Transaction::where('account', $activeAccount)
            ->where('type', 'expense')
            ->whereYear('date', $lastMonth->year)
            ->whereMonth('date', $lastMonth->month)
            ->sum('amount');

        // Growth rate percentage
        $incomeGrowth = $lastMonthIncome > 0 ? (($thisMonthIncome - $lastMonthIncome) / $lastMonthIncome) * 100 : 0;
        $expenseGrowth = $lastMonthExpense > 0 ? (($thisMonthExpense - $lastMonthExpense) / $lastMonthExpense) * 100 : 0;

        // Transaction count this month
        $thisMonthTransactionsCount = Transaction::where('account', $activeAccount)
            ->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->count();

        return view('dashboard', compact(
            'activeAccount',
            'allTimeIncome',
            'allTimeExpense',
            'netBalance',
            'recentTransactions',
            'months',
            'incomeTrend',
            'expenseTrend',
            'incomeCategories',
            'expenseCategories',
            'thisMonthIncome',
            'thisMonthExpense',
            'incomeGrowth',
            'expenseGrowth',
            'allTimeTransactionsCount',
            'thisMonthTransactionsCount',
            'totalSaldoKas',
            'totalPanjarAktif',
            'overduePanjarCount',
            'panjarTransactions',
            'panjarSummaryByAccount',
            'panjarBySource',
            'pettyCashBalance',
            'bankBalance',
            'bankMandiri1Balance',
            'bankMandiri2Balance',
            'categories',
            'jobs'
        ));
    }
}
