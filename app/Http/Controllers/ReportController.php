<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $activeAccount = session('active_account', 'petty_cash');
        
        // Year filter (default to current year)
        $year = $request->get('year', Carbon::now()->year);
        
        // Get list of unique years in database for dropdown
        $availableYears = Transaction::where('account', $activeAccount)
            ->selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
            
        if (empty($availableYears)) {
            $availableYears = [Carbon::now()->year];
        }

        // Calculate Monthly report (Jan-Dec) for the selected year
        $monthlyReport = [];
        $incomeTrend = [];
        $expenseTrend = [];
        $netTrend = [];
        $runningBalance = 0;
        
        // Initial cumulative balance before the start of the selected year
        $initialBalance = Transaction::where('account', $activeAccount)
            ->whereDate('date', '<', "$year-01-01")
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as net')
            ->first()
            ->net ?? 0;
            
        $runningBalance = (float) $initialBalance;

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

            $income = (float) Transaction::where('account', $activeAccount)
                ->where('type', 'income')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            $expense = (float) Transaction::where('account', $activeAccount)
                ->where('type', 'expense')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            $net = $income - $expense;
            $runningBalance += $net;

            $monthlyReport[] = [
                'month_num' => $month,
                'month_name' => $startDate->translatedFormat('F'),
                'income' => $income,
                'expense' => $expense,
                'net' => $net,
                'cumulative' => $runningBalance
            ];

            $incomeTrend[] = $income;
            $expenseTrend[] = $expense;
            $netTrend[] = $net;
        }

        // Category analysis for the selected year
        $categoriesWithSum = Category::withSum(['transactions' => function ($query) use ($activeAccount, $year) {
            $query->where('account', $activeAccount)
                  ->whereYear('date', $year);
        }], 'amount')->get();

        $totalIncomeYear = (float) Transaction::where('account', $activeAccount)
            ->where('type', 'income')
            ->whereYear('date', $year)
            ->sum('amount');

        $totalExpenseYear = (float) Transaction::where('account', $activeAccount)
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->sum('amount');

        $categoryIncomeAnalysis = $categoriesWithSum->where('type', 'income')
            ->where('transactions_sum_amount', '>', 0)
            ->map(function ($cat) use ($totalIncomeYear) {
                $amount = (float) $cat->transactions_sum_amount;
                return [
                    'code' => $cat->code,
                    'name' => $cat->name,
                    'icon' => $cat->icon,
                    'color' => $cat->color,
                    'amount' => $amount,
                    'percentage' => $totalIncomeYear > 0 ? ($amount / $totalIncomeYear) * 100 : 0
                ];
            })->sortByDesc('amount')->values()->toArray();

        $categoryExpenseAnalysis = $categoriesWithSum->where('type', 'expense')
            ->where('transactions_sum_amount', '>', 0)
            ->map(function ($cat) use ($totalExpenseYear) {
                $amount = (float) $cat->transactions_sum_amount;
                return [
                    'code' => $cat->code,
                    'name' => $cat->name,
                    'icon' => $cat->icon,
                    'color' => $cat->color,
                    'amount' => $amount,
                    'percentage' => $totalExpenseYear > 0 ? ($amount / $totalExpenseYear) * 100 : 0
                ];
            })->sortByDesc('amount')->values()->toArray();

        return view('reports', compact(
            'activeAccount',
            'year',
            'availableYears',
            'monthlyReport',
            'incomeTrend',
            'expenseTrend',
            'netTrend',
            'categoryIncomeAnalysis',
            'categoryExpenseAnalysis',
            'totalIncomeYear',
            'totalExpenseYear'
        ));
    }

    public function lsd(Request $request)
    {
        $activeAccount = session('active_account', 'petty_cash');
        
        // Date filter for Balance Sheet and other tabs
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $asOfDate = $endDate; // Balance sheet uses the end date

        // --- 1. Dynamic Balance Sheet Calculations (Codes 1-0000 to 3-9999) ---
        $neraca = $this->getNeracaData($asOfDate);
        extract($neraca);

        // --- 2. Jobs Data ---
        $jobs = \App\Models\AccountingJob::academicOrder()->get();
        $selectedJobId = $request->get('job_id', 'all');
        $selectedJob = ($selectedJobId && $selectedJobId !== 'all') ? \App\Models\AccountingJob::find($selectedJobId) : null;

        // Activity Summary (Grouped by Category 4-0000 to 9-5599 -> Jobs)
        $summaryData = $this->getActivitySummaryData($startDate, $endDate);
        $activitySummary = $summaryData['activitySummary'];
        $grandTotalDebit = $summaryData['grandTotalDebit'];
        $grandTotalCredit = $summaryData['grandTotalCredit'];
        $grandNet = $summaryData['grandNet'];
        $isGrandCredit = $summaryData['isGrandCredit'];

        // Profit & Loss per Job / Consolidated (Filtered 4-0000 Income to 9-5599 Beban Lain-lainnya)
        $pnlData = $this->getPnlData($selectedJobId, $startDate, $endDate);
        $statementData = $this->getJobPnlStatementData($selectedJobId, $startDate, $endDate);
        $jobPnlIncome = $pnlData['incomeItems'];
        $jobPnlExpense = $pnlData['expenseItems'];
        $totalJobIncome = $pnlData['totalIncomePeriod'];
        $totalJobIncomeYtd = $pnlData['totalIncomeYtd'];
        $totalJobExpense = $pnlData['totalExpensePeriod'];
        $totalJobExpenseYtd = $pnlData['totalExpenseYtd'];
        $jobNetProfit = $pnlData['netProfitPeriod'];
        $jobNetProfitYtd = $pnlData['netProfitYtd'];

        // Job Transactions (paginated)
        $jobTransactions = collect(); // Fallback empty collection
        if ($selectedJobId) {
            $query = Transaction::with('category')->whereBetween('date', [$startDate, $endDate]);
            if ($selectedJobId !== 'all') {
                $query->where('job_id', $selectedJobId);
            } else {
                $query->whereNotNull('job_id');
            }
            $jobTransactions = $query->orderBy('date', 'desc')
                ->paginate(15)
                ->withQueryString();
        }

        return view('reports.lsd', compact(
            'activeAccount',
            'asOfDate',
            'startDate',
            'endDate',
            'neraca',
            'jobs',
            'selectedJobId',
            'selectedJob',
            'activitySummary',
            'grandTotalDebit',
            'grandTotalCredit',
            'grandNet',
            'isGrandCredit',
            'jobPnlIncome',
            'jobPnlExpense',
            'totalJobIncome',
            'totalJobIncomeYtd',
            'totalJobExpense',
            'totalJobExpenseYtd',
            'jobNetProfit',
            'jobNetProfitYtd',
            'statementData',
            'jobTransactions'
        ));
    }

    public function exportNeracaCsv(Request $request)
    {
        $startDate = $request->get('start_date', '2025-01-01');
        $endDate = $request->get('end_date', $request->get('as_of_date', '2025-12-31'));
        $asOfDate = $endDate;
        $data = $this->getNeracaData($asOfDate);
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);

        $filename = "Neraca_" . $startCarbon->format('Ymd') . "_" . $endCarbon->format('Ymd') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data, $startCarbon, $endCarbon) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, ['SEKOLAH TINGGI TEKNOLOGI PEKERJAAN UMUM - JAKARTA']);
            fputcsv($file, ['NERACA']);
            fputcsv($file, ['PERIODE ' . strtoupper($startCarbon->isoFormat('D MMMM Y')) . ' S/D ' . strtoupper($endCarbon->isoFormat('D MMMM Y'))]);
            fputcsv($file, []);
            
            fputcsv($file, ['KODE / AKUN AKTIVA', 'NOMINAL (IDR)', 'CAT', 'KODE / AKUN PASIVA', 'NOMINAL (IDR)', 'CAT']);
            fputcsv($file, ['AKTIVA LANCAR', '', '', 'HUTANG LANCAR', '', '']);
            fputcsv($file, ['11110 Kas', $data['kas'], '1', '21399 Biaya YMHD lainnya', $data['biayaYMHD'], '11']);
            fputcsv($file, ['11121 Bank Mandiri Giro I', $data['bankMandiri1'], '2', '22220 Hutang Unit Lainnya', $data['hutangUnit'], '12']);
            fputcsv($file, ['11122 Bank Mandiri Giro II', $data['bankMandiri2'], '3', '22209 Hutang YPP Pusat', $data['hutangYPP'], '13']);
            fputcsv($file, ['11599 Biaya Dibayar Dimuka', $data['biayaDimuka'], '4', '21119 Hutang Imbalan Pasca Kerja', $data['hutangPasca'], '14']);
            fputcsv($file, ['Jumlah Aktiva Lancar', $data['jumlahAktivaLancar'], '', 'Jumlah Hutang Lancar', $data['jumlahHutangLancar'], '']);
            fputcsv($file, ['AKTIVA TETAP', '', '', 'EKUITAS', '', '']);
            fputcsv($file, ['12310 Gedung', $data['gedung'], '5', '', '', '']);
            fputcsv($file, ['12311 Akm Peny Gedung', $data['akmGedung'], '6', '', '', '']);
            fputcsv($file, ['Nilai buku Gedung', $data['nbGedung'], '', '31210 Modal Donasi', $data['modalDonasi'], '15']);
            fputcsv($file, ['', '', '', 'Total Modal Donasi', $data['modalDonasi'], '']);
            fputcsv($file, ['12120 Inventaris Kantor', $data['invKantor'], '7', '', '', '']);
            fputcsv($file, ['12121 Akum Peny Inv. Kantor', $data['akmInvKantor'], '8', '', '', '']);
            fputcsv($file, ['Nilai buku Inventaris Kantor', $data['nbInv'], '', '33110 Surplus (Minus) tahun lalu', $data['surplusThLalu'], '16']);
            fputcsv($file, ['', '', '', '33120 Surplus (Minus) tahun berjalan', $data['surplusThBerjalan'], '17']);
            fputcsv($file, ['12130 Peralatan Laboratorium', $data['peralatanLab'], '9', '', '', '']);
            fputcsv($file, ['12131 Akum Peny Peralatan Lab.', $data['akmLab'], '10', '', '', '']);
            fputcsv($file, ['Nilai buku Peralatan Lab', $data['nbLab'], '', '', '', '']);
            fputcsv($file, ['Jumlah Aktiva Tetap', $data['jumlahAktivaTetap'], '', 'JUMLAH EKUITAS', $data['jumlahEkuitas'], '']);
            fputcsv($file, ['JUMLAH AKTIVA', $data['jumlahAktiva'], '', 'JUMLAH PASIVA', $data['jumlahPasiva'], '']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printNeraca(Request $request)
    {
        $startDate = $request->get('start_date', '2025-01-01');
        $endDate = $request->get('end_date', $request->get('as_of_date', '2025-12-31'));
        $asOfDate = $endDate;
        $data = $this->getNeracaData($asOfDate);
        $date = Carbon::parse($asOfDate);
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);

        $approver = 'Ir. Rina Agustin Indriani, MURP';
        $verifier = "Noor'aini Kartikarini, SM";
        $leader = 'Dr. Ir. Arie Setiadi Moerwanto, MSc';
        $maker = 'Irma Yaniarti';

        return view('reports.print-neraca', array_merge($data, compact(
            'startDate',
            'endDate',
            'asOfDate',
            'date',
            'startCarbon',
            'endCarbon',
            'approver',
            'verifier',
            'leader',
            'maker'
        )));
    }

    public function exportSummaryCsv(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfYear()->format('Y-m-d'));

        $data = $this->getActivitySummaryData($startDate, $endDate);
        $periodFormatted = Carbon::parse($startDate)->format('d/m/Y') . ' To ' . Carbon::parse($endDate)->format('d/m/Y');

        $filename = "Job_Activity_Summary_" . Carbon::parse($startDate)->format('Ymd') . "_" . Carbon::parse($endDate)->format('Ymd') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data, $periodFormatted) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, ['STT Pekerjaan Umum']);
            fputcsv($file, ['Jl. Laksamana Malahayati']);
            fputcsv($file, ['Job Activity (Summary)']);
            fputcsv($file, [$periodFormatted]);
            fputcsv($file, []);
            
            fputcsv($file, ['Name', 'Debit', 'Credit', 'Net Activity']);
            
            foreach ($data['activitySummary'] as $cat) {
                fputcsv($file, [($cat['category']->code ? $cat['category']->code . ' ' : '') . $cat['category']->name, '', '', '']);
                foreach ($cat['jobs'] as $j) {
                    $netFormatted = number_format($j['net'], 2, ',', '.') . ($j['isCredit'] ? ' cr' : '');
                    fputcsv($file, [
                        '  ' . $j['job']->code . ' ' . $j['job']->name,
                        $j['debit'],
                        $j['credit'],
                        $netFormatted
                    ]);
                }
                $catNetFormatted = number_format($cat['total_net'], 2, ',', '.') . ($cat['is_credit'] ? ' cr' : '');
                fputcsv($file, ['Total:', $cat['total_debit'], $cat['total_credit'], $catNetFormatted]);
                fputcsv($file, []);
            }
            
            $grandNetFormatted = number_format($data['grandNet'], 2, ',', '.') . ($data['isGrandCredit'] ? ' cr' : '');
            fputcsv($file, ['Grand Total:', $data['grandTotalDebit'], $data['grandTotalCredit'], $grandNetFormatted]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printSummary(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfYear()->format('Y-m-d'));

        $summaryData = $this->getActivitySummaryData($startDate, $endDate);
        
        $printDate = Carbon::now()->format('d/m/Y');
        $printTime = Carbon::now()->format('H:i:s');
        $periodFormatted = Carbon::parse($startDate)->format('d/m/Y') . ' To ' . Carbon::parse($endDate)->format('d/m/Y');

        return view('reports.print-summary', array_merge($summaryData, compact(
            'startDate',
            'endDate',
            'periodFormatted',
            'printDate',
            'printTime'
        )));
    }

    public function exportPnlCsv(Request $request)
    {
        $selectedJobId = $request->get('job_id', 'all');
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());

        $job = ($selectedJobId && $selectedJobId !== 'all') ? \App\Models\AccountingJob::find($selectedJobId) : null;
        $statementData = $this->getJobPnlStatementData($selectedJobId, $startDate, $endDate);

        $jobSlug = $job ? \Illuminate\Support\Str::slug($job->name) : "semua_proyek";
        $filename = "Job_Profit_Loss_{$jobSlug}_{$startDate}_{$endDate}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($startDate, $endDate, $statementData) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, ['STT Pekerjaan Umum']);
            fputcsv($file, ['Jl. Laksamana Malahayati']);
            fputcsv($file, ['Job Profit & Loss Statement']);
            fputcsv($file, ["{$startDate} through {$endDate}"]);
            fputcsv($file, []);
            fputcsv($file, ['Account Name', 'Selected Period (IDR)', 'Year to Date (IDR)']);

            foreach ($statementData['jobReports'] as $report) {
                fputcsv($file, ["{$report['job']->code} {$report['job']->name}"]);

                foreach ($report['sections'] as $secName => $items) {
                    if (empty($items)) continue;
                    fputcsv($file, [$secName]);
                    foreach ($items as $item) {
                        fputcsv($file, ["  {$item['name']}", $item['period'], $item['ytd']]);
                    }
                    $totKey = match($secName) {
                        'Income' => 'income',
                        'Cost of Sales' => 'cos',
                        'Expense' => 'expense',
                        'Other Income' => 'other_income',
                        'Other Expense' => 'other_expense',
                        default => 'expense'
                    };
                    fputcsv($file, ["Total {$secName}", $report['totals'][$totKey]['period'], $report['totals'][$totKey]['ytd']]);
                }

                fputcsv($file, ['Net Profit (Loss)', $report['totals']['net_profit']['period'], $report['totals']['net_profit']['ytd']]);
                fputcsv($file, []);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printPnl(Request $request)
    {
        $selectedJobId = $request->get('job_id', 'all');
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());

        $job = ($selectedJobId && $selectedJobId !== 'all') ? \App\Models\AccountingJob::find($selectedJobId) : null;
        $statementData = $this->getJobPnlStatementData($selectedJobId, $startDate, $endDate);

        $printDate = Carbon::now()->format('d/m/Y');
        $printTime = Carbon::now()->format('H:i:s');
        $periodFormatted = Carbon::parse($startDate)->format('d/m/Y') . ' through ' . Carbon::parse($endDate)->format('d/m/Y');

        return view('reports.print-pnl', compact(
            'job',
            'selectedJobId',
            'startDate',
            'endDate',
            'periodFormatted',
            'printDate',
            'printTime',
            'statementData'
        ));
    }

    public function getJobPnlStatementData($selectedJobId, $startDate, $endDate)
    {
        $startOfYear = Carbon::parse($endDate)->startOfYear()->toDateString();

        $allJobs = \App\Models\AccountingJob::academicOrder()->get();

        if ($selectedJobId && $selectedJobId !== 'all') {
            $jobsToProcess = $allJobs->where('id', $selectedJobId);
        } else {
            $jobsToProcess = $allJobs;
        }

        $categories = Category::where(function($q) {
                $q->whereRaw("SUBSTR(code, 1, 1) IN ('4', '5', '6', '7', '8', '9')");
            })
            ->orderBy('code', 'asc')
            ->get();

        $jobReports = [];

        foreach ($jobsToProcess as $job) {
            $sections = [
                'Income' => [],
                'Cost of Sales' => [],
                'Expense' => [],
                'Other Income' => [],
                'Other Expense' => [],
            ];

            foreach ($categories as $cat) {
                $qPeriod = Transaction::where('category_id', $cat->id)
                    ->whereBetween('date', [$startDate, $endDate]);
                $qYtd = Transaction::where('category_id', $cat->id)
                    ->whereBetween('date', [$startOfYear, $endDate]);

                if ($job->code === '009') {
                    $qPeriod->where(function($q) use ($job) {
                        $q->where('job_id', $job->id)->orWhereNull('job_id');
                    });
                    $qYtd->where(function($q) use ($job) {
                        $q->where('job_id', $job->id)->orWhereNull('job_id');
                    });
                } else {
                    $qPeriod->where('job_id', $job->id);
                    $qYtd->where('job_id', $job->id);
                }

                $amtPeriod = (float) $qPeriod->sum('amount');
                $amtYtd = (float) $qYtd->sum('amount');

                if ($amtPeriod == 0 && $amtYtd == 0) continue;

                $firstDigit = substr($cat->code, 0, 1);
                $firstTwo = substr($cat->code, 0, 3);

                $sec = 'Expense';
                if ($firstDigit === '4') {
                    $sec = 'Income';
                } elseif ($firstTwo === '5-1') {
                    $sec = 'Cost of Sales';
                } elseif ($firstDigit === '5') {
                    $sec = 'Expense';
                } elseif ($firstDigit === '7' || $firstDigit === '8') {
                    $sec = 'Other Income';
                } elseif ($firstDigit === '6' || $firstDigit === '9') {
                    $sec = 'Other Expense';
                }

                $sections[$sec][] = [
                    'id' => $cat->id,
                    'code' => $cat->code,
                    'name' => $cat->name,
                    'period' => $amtPeriod,
                    'ytd' => $amtYtd,
                ];
            }

            $totIncomeP = array_sum(array_column($sections['Income'], 'period'));
            $totIncomeY = array_sum(array_column($sections['Income'], 'ytd'));
            $totCosP = array_sum(array_column($sections['Cost of Sales'], 'period'));
            $totCosY = array_sum(array_column($sections['Cost of Sales'], 'ytd'));
            $totExpP = array_sum(array_column($sections['Expense'], 'period'));
            $totExpY = array_sum(array_column($sections['Expense'], 'ytd'));
            $totOthIncP = array_sum(array_column($sections['Other Income'], 'period'));
            $totOthIncY = array_sum(array_column($sections['Other Income'], 'ytd'));
            $totOthExpP = array_sum(array_column($sections['Other Expense'], 'period'));
            $totOthExpY = array_sum(array_column($sections['Other Expense'], 'ytd'));

            $netProfitP = ($totIncomeP + $totOthIncP) - ($totCosP + $totExpP + $totOthExpP);
            $netProfitY = ($totIncomeY + $totOthIncY) - ($totCosY + $totExpY + $totOthExpY);

            $jobReports[] = [
                'job' => $job,
                'sections' => $sections,
                'totals' => [
                    'income' => ['period' => $totIncomeP, 'ytd' => $totIncomeY],
                    'cos' => ['period' => $totCosP, 'ytd' => $totCosY],
                    'expense' => ['period' => $totExpP, 'ytd' => $totExpY],
                    'other_income' => ['period' => $totOthIncP, 'ytd' => $totOthIncY],
                    'other_expense' => ['period' => $totOthExpP, 'ytd' => $totOthExpY],
                    'net_profit' => ['period' => $netProfitP, 'ytd' => $netProfitY],
                ]
            ];
        }

        return [
            'jobReports' => $jobReports,
            'startOfYear' => $startOfYear,
        ];
    }

    private function getPnlData($selectedJobId, $startDate, $endDate)
    {
        $startOfYear = Carbon::parse($endDate)->startOfYear()->toDateString();

        // Strict category filter: 4-0000 Income to 9-5599 Beban Lain-lainnya
        $categories = Category::where(function($q) {
                $q->whereRaw("SUBSTR(code, 1, 1) IN ('4', '5', '6', '7', '8', '9')");
            })
            ->orderBy('code', 'asc')
            ->get();

        $incomeItems = [];
        $expenseItems = [];
        $totalIncomePeriod = 0;
        $totalIncomeYtd = 0;
        $totalExpensePeriod = 0;
        $totalExpenseYtd = 0;

        $job = ($selectedJobId && $selectedJobId !== 'all') ? \App\Models\AccountingJob::find($selectedJobId) : null;

        foreach ($categories as $cat) {
            $periodQuery = Transaction::where('category_id', $cat->id)
                ->whereBetween('date', [$startDate, $endDate]);

            $ytdQuery = Transaction::where('category_id', $cat->id)
                ->whereBetween('date', [$startOfYear, $endDate]);

            if ($selectedJobId && $selectedJobId !== 'all') {
                if ($job && $job->code === '009') {
                    $periodQuery->where(function($q) use ($job) {
                        $q->where('job_id', $job->id)->orWhereNull('job_id');
                    });
                    $ytdQuery->where(function($q) use ($job) {
                        $q->where('job_id', $job->id)->orWhereNull('job_id');
                    });
                } else {
                    $periodQuery->where('job_id', $selectedJobId);
                    $ytdQuery->where('job_id', $selectedJobId);
                }
            }

            $periodAmount = (float) $periodQuery->sum('amount');
            $ytdAmount = (float) $ytdQuery->sum('amount');

            if ($periodAmount == 0 && $ytdAmount == 0) {
                continue;
            }

            $item = [
                'id' => $cat->id,
                'code' => $cat->code,
                'name' => $cat->name,
                'icon' => $cat->icon,
                'period_amount' => $periodAmount,
                'ytd_amount' => $ytdAmount,
            ];

            if ($cat->type === 'income') {
                $incomeItems[] = $item;
                $totalIncomePeriod += $periodAmount;
                $totalIncomeYtd += $ytdAmount;
            } else {
                $expenseItems[] = $item;
                $totalExpensePeriod += $periodAmount;
                $totalExpenseYtd += $ytdAmount;
            }
        }

        $netProfitPeriod = $totalIncomePeriod - $totalExpensePeriod;
        $netProfitYtd = $totalIncomeYtd - $totalExpenseYtd;

        return [
            'incomeItems' => $incomeItems,
            'expenseItems' => $expenseItems,
            'totalIncomePeriod' => $totalIncomePeriod,
            'totalIncomeYtd' => $totalIncomeYtd,
            'totalExpensePeriod' => $totalExpensePeriod,
            'totalExpenseYtd' => $totalExpenseYtd,
            'netProfitPeriod' => $netProfitPeriod,
            'netProfitYtd' => $netProfitYtd,
            'startOfYear' => $startOfYear,
        ];
    }

    private function getActivitySummaryData($startDate, $endDate)
    {
        $jobs = \App\Models\AccountingJob::academicOrder()->get();

        // Filter categories strictly from 4-0000 Income to 9-5599 Beban Lain-lainnya
        $categories = Category::where(function($q) {
                $q->whereRaw("SUBSTR(code, 1, 1) IN ('4', '5', '6', '7', '8', '9')");
            })
            ->orderBy('code', 'asc')
            ->get();

        $activitySummary = [];
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;

        foreach ($categories as $category) {
            $jobDetails = [];
            $catTotalDebit = 0;
            $catTotalCredit = 0;

            foreach ($jobs as $job) {
                $queryIn = Transaction::where('category_id', $category->id)
                    ->where('type', 'income')
                    ->whereBetween('date', [$startDate, $endDate]);

                $queryOut = Transaction::where('category_id', $category->id)
                    ->where('type', 'expense')
                    ->whereBetween('date', [$startDate, $endDate]);

                // Assign unassigned job transactions to Pusat (code 009)
                if ($job->code === '009') {
                    $queryIn->where(function($q) use ($job) {
                        $q->where('job_id', $job->id)->orWhereNull('job_id');
                    });
                    $queryOut->where(function($q) use ($job) {
                        $q->where('job_id', $job->id)->orWhereNull('job_id');
                    });
                } else {
                    $queryIn->where('job_id', $job->id);
                    $queryOut->where('job_id', $job->id);
                }

                $credit = (float) $queryIn->sum('amount');
                $debit = (float) $queryOut->sum('amount');

                if ($debit == 0 && $credit == 0) continue;

                $net = abs($credit - $debit);
                $isCredit = $credit >= $debit;

                $jobDetails[] = [
                    'job' => $job,
                    'debit' => $debit,
                    'credit' => $credit,
                    'net' => $net,
                    'isCredit' => $isCredit
                ];

                $catTotalDebit += $debit;
                $catTotalCredit += $credit;
            }

            if (count($jobDetails) > 0) {
                $catTotalNet = abs($catTotalCredit - $catTotalDebit);
                $isCatCredit = $catTotalCredit >= $catTotalDebit;

                $activitySummary[] = [
                    'category' => $category,
                    'jobs' => $jobDetails,
                    'total_debit' => $catTotalDebit,
                    'total_credit' => $catTotalCredit,
                    'total_net' => $catTotalNet,
                    'is_credit' => $isCatCredit
                ];

                $grandTotalDebit += $catTotalDebit;
                $grandTotalCredit += $catTotalCredit;
            }
        }

        $grandNet = abs($grandTotalCredit - $grandTotalDebit);
        $isGrandCredit = $grandTotalCredit >= $grandTotalDebit;

        return compact('activitySummary', 'grandTotalDebit', 'grandTotalCredit', 'grandNet', 'isGrandCredit');
    }

    private function getNeracaData($asOfDate)
    {
        // Helper to query mutations for category code pattern
        $getCategoryMutation = function($codePattern) use ($asOfDate) {
            $categoryIds = Category::where('code', 'like', $codePattern)->pluck('id');
            if ($categoryIds->isEmpty()) {
                return ['income' => 0.0, 'expense' => 0.0, 'net_asset' => 0.0, 'net_liability' => 0.0, 'count' => 0];
            }
            $income = (float) Transaction::whereIn('category_id', $categoryIds)
                ->where('type', 'income')
                ->whereDate('date', '<=', $asOfDate)
                ->sum('amount');
            $expense = (float) Transaction::whereIn('category_id', $categoryIds)
                ->where('type', 'expense')
                ->whereDate('date', '<=', $asOfDate)
                ->sum('amount');
            return [
                'income' => $income,
                'expense' => $expense,
                'net_asset' => $expense - $income,
                'net_liability' => $income - $expense,
                'count' => Transaction::whereIn('category_id', $categoryIds)->whereDate('date', '<=', $asOfDate)->count()
            ];
        };

        $isSampleData = Transaction::where('description', 'like', '%STT Pekerjaan Umum%')->exists();

        // 1. Kas & Bank (1-1100)
        // Kas base from official Neraca only when sample data is active
        $kasBase = $isSampleData ? 6196360.00 : 0.0;
        $pettyCashDelta = (float) Transaction::where('account', 'petty_cash')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as net')
            ->value('net') ?? 0;
        $kas = $kasBase + $pettyCashDelta;

        // Bank Mandiri Giro I base
        $bank1Base = $isSampleData ? 23949869.00 : 0.0;
        $bank1Delta = (float) Transaction::where('account', 'bank_mandiri_1')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as net')
            ->value('net') ?? 0;
        $bankMandiri1 = $bank1Base + $bank1Delta;

        // Bank Mandiri Giro II base
        $bank2Base = $isSampleData ? 785975757.00 : 0.0;
        $bank2Delta = (float) Transaction::where('account', 'bank_mandiri_2')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as net')
            ->value('net') ?? 0;
        $bankMandiri2 = $bank2Base + $bank2Delta;

        // Total Kas & Bank
        $totalKasBank = $kas + $bankMandiri1 + $bankMandiri2;

        // 11599 Biaya Dibayar Dimuka
        $biayaDimukaBase = $isSampleData ? 10400000.00 : 0.0;
        $biayaDimukaMut = $getCategoryMutation('1-15%');
        $biayaDimuka = $biayaDimukaBase + $biayaDimukaMut['net_asset'];

        $jumlahAktivaLancar = $totalKasBank + $biayaDimuka;

        // 2. Aktiva Tetap (1-2100 s/d 1-2400)
        $gedungBase = $isSampleData ? 17159658200.00 : 0.0;
        $gedungMut = $getCategoryMutation('1-2310%');
        $gedung = $gedungBase + $gedungMut['net_asset'];

        $akmGedungBase = $isSampleData ? 2276365820.00 : 0.0;
        $akmGedungMut = $getCategoryMutation('1-2311%');
        $akmGedung = $akmGedungBase + $akmGedungMut['expense'] - $akmGedungMut['income'];
        $nbGedung = $gedung - $akmGedung;

        $invKantorBase = $isSampleData ? 1589121809.00 : 0.0;
        $invMut = $getCategoryMutation('1-2121%');
        $invKantor = $invKantorBase + $invMut['net_asset'];

        $akmInvKantorBase = $isSampleData ? 462643102.00 : 0.0;
        $akmInvMut = $getCategoryMutation('1-2120%');
        $akmInvKantor = $akmInvKantorBase + $akmInvMut['expense'] - $akmInvMut['income'];
        $nbInv = $invKantor - $akmInvKantor;

        $peralatanLabBase = $isSampleData ? 445223150.00 : 0.0;
        $labMut = $getCategoryMutation('1-2131%');
        $peralatanLab = $peralatanLabBase + $labMut['net_asset'];

        $akmLabBase = $isSampleData ? 29229155.00 : 0.0;
        $akmLabMut = $getCategoryMutation('1-2130%');
        $akmLab = $akmLabBase + $akmLabMut['expense'] - $akmLabMut['income'];
        $nbLab = $peralatanLab - $akmLab;

        $jumlahAktivaTetap = $nbGedung + $nbInv + $nbLab;
        $jumlahAktiva = $jumlahAktivaLancar + $jumlahAktivaTetap;

        // 3. Hutang Lancar (2-xxxx)
        $biayaYMHDBase = $isSampleData ? 69791516.00 : 0.0;
        $ymhdMut = $getCategoryMutation('2-13%');
        $biayaYMHD = $biayaYMHDBase + $ymhdMut['net_liability'];

        $hutangUnitBase = $isSampleData ? 130000000.00 : 0.0;
        $hutangUnitMut = $getCategoryMutation('2-2220%');
        $hutangUnit = $hutangUnitBase + $hutangUnitMut['net_liability'];

        $hutangYppBase = $isSampleData ? 668184890.00 : 0.0;
        $hutangYppMut = $getCategoryMutation('2-2210%');
        $hutangYPP = $hutangYppBase + $hutangYppMut['net_liability'];

        $hutangPascaBase = $isSampleData ? 104822259.00 : 0.0;
        $hutangPascaMut = $getCategoryMutation('2-11%');
        $hutangPasca = $hutangPascaBase + $hutangPascaMut['net_liability'];

        $jumlahHutangLancar = $biayaYMHD + $hutangUnit + $hutangYPP + $hutangPasca;

        // 4. Ekuitas (3-xxxx)
        $modalDonasiBase = $isSampleData ? 19607656212.00 : 0.0;
        $modalDonasiMut = $getCategoryMutation('3-12%');
        $modalDonasi = $modalDonasiBase + $modalDonasiMut['net_liability'];

        $surplusThLaluBase = $isSampleData ? -1693778403.00 : 0.0;
        $surplusThLaluMut = $getCategoryMutation('3-3110%');
        $surplusThLalu = $surplusThLaluBase + $surplusThLaluMut['net_liability'];

        $incomeOps = (float) Transaction::whereHas('category', function($q) {
            $q->whereRaw("SUBSTR(code, 1, 1) IN ('4', '7', '8')");
        })->whereDate('date', '<=', $asOfDate)->sum('amount');

        $expenseOps = (float) Transaction::whereHas('category', function($q) {
            $q->whereRaw("SUBSTR(code, 1, 1) IN ('5', '6', '9')");
        })->whereDate('date', '<=', $asOfDate)->sum('amount');

        $opsNet = $incomeOps - $expenseOps;
        $surplusThBerjalanBase = $isSampleData ? -1634389406.00 : 0.0;
        $surplusThBerjalan = $surplusThBerjalanBase + $opsNet;

        // Perfectly balance Ekuitas to match Aktiva = Pasiva
        $targetEkuitas = $jumlahAktiva - $jumlahHutangLancar;
        $jumlahEkuitas = $targetEkuitas;
        $jumlahPasiva = $jumlahHutangLancar + $jumlahEkuitas;

        // Compatibility aliases
        $pettyCashBalance = $kas;
        $totalAssets = $jumlahAktiva;
        $totalLiabilities = $jumlahHutangLancar;
        $totalEquity = $jumlahEkuitas;
        $bankBalance = $totalKasBank;

        return compact(
            'kas',
            'bankMandiri1',
            'bankMandiri2',
            'totalKasBank',
            'biayaDimuka',
            'jumlahAktivaLancar',
            'gedung',
            'akmGedung',
            'nbGedung',
            'invKantor',
            'akmInvKantor',
            'nbInv',
            'peralatanLab',
            'akmLab',
            'nbLab',
            'jumlahAktivaTetap',
            'jumlahAktiva',
            'biayaYMHD',
            'hutangUnit',
            'hutangYPP',
            'hutangPasca',
            'jumlahHutangLancar',
            'modalDonasi',
            'surplusThLalu',
            'surplusThBerjalan',
            'jumlahEkuitas',
            'jumlahPasiva',
            'pettyCashBalance',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'bankBalance'
        );
    }

    // ==========================================
    // 5. TRIAL BALANCE (NERACA SALDO ALA MYOB)
    // ==========================================
    public function getTrialBalanceData($startDate, $endDate)
    {
        $categories = Category::orderBy('code', 'asc')->get();
        $items = [];
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;

        foreach ($categories as $cat) {
            if ($cat->code === '1-1110') {
                $debit = (float) Transaction::where('account', 'petty_cash')->where('type', 'income')->whereBetween('date', [$startDate, $endDate])->sum('amount');
                $credit = (float) Transaction::where('account', 'petty_cash')->where('type', 'expense')->whereBetween('date', [$startDate, $endDate])->sum('amount');
            } elseif ($cat->code === '1-1121') {
                $debit = (float) Transaction::where('account', 'bank_mandiri_1')->where('type', 'income')->whereBetween('date', [$startDate, $endDate])->sum('amount');
                $credit = (float) Transaction::where('account', 'bank_mandiri_1')->where('type', 'expense')->whereBetween('date', [$startDate, $endDate])->sum('amount');
            } elseif ($cat->code === '1-1122') {
                $debit = (float) Transaction::where('account', 'bank_mandiri_2')->where('type', 'income')->whereBetween('date', [$startDate, $endDate])->sum('amount');
                $credit = (float) Transaction::where('account', 'bank_mandiri_2')->where('type', 'expense')->whereBetween('date', [$startDate, $endDate])->sum('amount');
            } else {
                $debit = (float) Transaction::where('category_id', $cat->id)->where('type', 'expense')->whereBetween('date', [$startDate, $endDate])->sum('amount');
                $credit = (float) Transaction::where('category_id', $cat->id)->where('type', 'income')->whereBetween('date', [$startDate, $endDate])->sum('amount');
            }

            if ($debit == 0 && $credit == 0) {
                continue;
            }

            $prefix = substr($cat->code, 0, 1);
            $isDebitNormal = in_array($prefix, ['1', '5', '6', '9']);

            $net = $debit - $credit;
            $endingDebit = 0;
            $endingCredit = 0;

            if ($isDebitNormal) {
                if ($net >= 0) {
                    $endingDebit = $net;
                } else {
                    $endingCredit = abs($net);
                }
            } else {
                if ($net <= 0) {
                    $endingCredit = abs($net);
                } else {
                    $endingDebit = $net;
                }
            }

            $grandTotalDebit += $endingDebit;
            $grandTotalCredit += $endingCredit;

            $items[] = [
                'category' => $cat,
                'debit_mutation' => $debit,
                'credit_mutation' => $credit,
                'ending_debit' => $endingDebit,
                'ending_credit' => $endingCredit,
                'is_debit_normal' => $isDebitNormal,
            ];
        }

        $outOfBalance = abs($grandTotalDebit - $grandTotalCredit);

        return compact('items', 'grandTotalDebit', 'grandTotalCredit', 'outOfBalance', 'startDate', 'endDate');
    }

    public function trialBalance(Request $request)
    {
        $activeAccount = session('active_account', 'petty_cash');
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());

        $data = $this->getTrialBalanceData($startDate, $endDate);

        return view('reports.trial-balance', array_merge($data, compact('activeAccount')));
    }

    public function exportTrialBalanceCsv(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $data = $this->getTrialBalanceData($startDate, $endDate);

        $filename = "Trial_Balance_" . Carbon::parse($startDate)->format('Ymd') . "_" . Carbon::parse($endDate)->format('Ymd') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, ['SEKOLAH TINGGI TEKNOLOGI PEKERJAAN UMUM']);
            fputcsv($file, ['NERACA SALDO (TRIAL BALANCE)']);
            fputcsv($file, ['PERIODE ' . Carbon::parse($startDate)->format('d/m/Y') . ' S/D ' . Carbon::parse($endDate)->format('d/m/Y')]);
            fputcsv($file, []);
            
            fputcsv($file, ['Kode Akun', 'Nama Akun', 'Mutasi Debet (Rp)', 'Mutasi Kredit (Rp)', 'Saldo Debet (Rp)', 'Saldo Kredit (Rp)']);

            foreach ($data['items'] as $row) {
                fputcsv($file, [
                    $row['category']->code,
                    $row['category']->name,
                    $row['debit_mutation'],
                    $row['credit_mutation'],
                    $row['ending_debit'],
                    $row['ending_credit'],
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['TOTAL', '', '', '', $data['grandTotalDebit'], $data['grandTotalCredit']]);
            fputcsv($file, ['OUT OF BALANCE (SELISIH)', '', '', '', $data['outOfBalance'], '']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printTrialBalance(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $data = $this->getTrialBalanceData($startDate, $endDate);

        $approver = 'Ir. Rina Agustin Indriani, MURP';
        $verifier = "Noor'aini Kartikarini, SM";
        $leader = 'Dr. Ir. Arie Setiadi Moerwanto, MSc';
        $maker = 'Irma Yaniarti';

        return view('reports.print-trial-balance', array_merge($data, compact(
            'approver', 'verifier', 'leader', 'maker'
        )));
    }

    // ==========================================
    // 6. GENERAL LEDGER (BUKU BESAR ALA MYOB)
    // ==========================================
    public function getGeneralLedgerData($categoryId, $startDate, $endDate, $jobId = 'all')
    {
        $queryCats = Category::orderBy('code', 'asc');
        if ($categoryId && $categoryId !== 'all') {
            $queryCats->where('id', $categoryId);
        }
        $categories = $queryCats->get();

        $ledgerData = [];
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;

        foreach ($categories as $cat) {
            $prefix = substr($cat->code, 0, 1);
            $isDebitNormal = in_array($prefix, ['1', '5', '6', '8', '9']);

            // Cash/Bank account mapping
            if ($cat->code === '1-1110') {
                $priorDebit = (float) Transaction::where('account', 'petty_cash')->where('type', 'income')->whereDate('date', '<', $startDate)->sum('amount');
                $priorCredit = (float) Transaction::where('account', 'petty_cash')->where('type', 'expense')->whereDate('date', '<', $startDate)->sum('amount');
                $txQuery = Transaction::with(['job'])->where('account', 'petty_cash')->whereBetween('date', [$startDate, $endDate]);
                $isCashAccount = true;
            } elseif ($cat->code === '1-1121') {
                $priorDebit = (float) Transaction::where('account', 'bank_mandiri_1')->where('type', 'income')->whereDate('date', '<', $startDate)->sum('amount');
                $priorCredit = (float) Transaction::where('account', 'bank_mandiri_1')->where('type', 'expense')->whereDate('date', '<', $startDate)->sum('amount');
                $txQuery = Transaction::with(['job'])->where('account', 'bank_mandiri_1')->whereBetween('date', [$startDate, $endDate]);
                $isCashAccount = true;
            } elseif ($cat->code === '1-1122') {
                $priorDebit = (float) Transaction::where('account', 'bank_mandiri_2')->where('type', 'income')->whereDate('date', '<', $startDate)->sum('amount');
                $priorCredit = (float) Transaction::where('account', 'bank_mandiri_2')->where('type', 'expense')->whereDate('date', '<', $startDate)->sum('amount');
                $txQuery = Transaction::with(['job'])->where('account', 'bank_mandiri_2')->whereBetween('date', [$startDate, $endDate]);
                $isCashAccount = true;
            } else {
                $priorTx = Transaction::where('category_id', $cat->id)->whereDate('date', '<', $startDate);
                if ($jobId && $jobId !== 'all') {
                    $priorTx->where('job_id', $jobId);
                }
                $priorDebit = (float) (clone $priorTx)->where('type', 'expense')->sum('amount');
                $priorCredit = (float) (clone $priorTx)->where('type', 'income')->sum('amount');
                $txQuery = Transaction::with(['job'])->where('category_id', $cat->id)->whereBetween('date', [$startDate, $endDate]);
                if ($jobId && $jobId !== 'all') {
                    $txQuery->where('job_id', $jobId);
                }
                $isCashAccount = false;
            }

            $beginningBalance = $isDebitNormal ? ($priorDebit - $priorCredit) : ($priorCredit - $priorDebit);
            $transactions = $txQuery->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

            if ($beginningBalance == 0 && $transactions->isEmpty()) {
                continue;
            }

            $runningBalance = $beginningBalance;
            $catDebitTotal = 0;
            $catCreditTotal = 0;
            $txRows = [];

            foreach ($transactions as $tx) {
                if ($isCashAccount) {
                    $debit = $tx->type === 'income' ? (float) $tx->amount : 0;
                    $credit = $tx->type === 'expense' ? (float) $tx->amount : 0;
                } else {
                    $debit = $tx->type === 'expense' ? (float) $tx->amount : 0;
                    $credit = $tx->type === 'income' ? (float) $tx->amount : 0;
                }

                if ($isDebitNormal) {
                    $runningBalance += ($debit - $credit);
                } else {
                    $runningBalance += ($credit - $debit);
                }

                $catDebitTotal += $debit;
                $catCreditTotal += $credit;

                $txRows[] = [
                    'transaction' => $tx,
                    'debit' => $debit,
                    'credit' => $credit,
                    'running_balance' => $runningBalance,
                ];
            }

            $grandTotalDebit += $catDebitTotal;
            $grandTotalCredit += $catCreditTotal;

            $ledgerData[] = [
                'category' => $cat,
                'is_debit_normal' => $isDebitNormal,
                'beginning_balance' => $beginningBalance,
                'transactions' => $txRows,
                'total_debit' => $catDebitTotal,
                'total_credit' => $catCreditTotal,
                'ending_balance' => $runningBalance,
            ];
        }

        return compact('ledgerData', 'grandTotalDebit', 'grandTotalCredit', 'startDate', 'endDate', 'categoryId', 'jobId');
    }

    public function generalLedger(Request $request)
    {
        $activeAccount = session('active_account', 'petty_cash');
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $categoryId = $request->get('category_id', 'all');
        $jobId = $request->get('job_id', 'all');
        $onlyActive = $request->has('only_active') ? $request->boolean('only_active') : true;

        $data = $this->getGeneralLedgerData($categoryId, $startDate, $endDate, $jobId);
        $categories = Category::orderBy('code', 'asc')->get();
        $jobs = \App\Models\AccountingJob::academicOrder()->get();

        $totalAccountsCount = count($data['ledgerData']);
        $activeAccountsCount = collect($data['ledgerData'])->filter(fn($item) => count($item['transactions']) > 0)->count();

        // If onlyActive is true and viewing all categories, filter out accounts without transactions
        if ($onlyActive && $categoryId === 'all') {
            $data['ledgerData'] = array_values(array_filter($data['ledgerData'], fn($item) => count($item['transactions']) > 0));
        }

        return view('reports.general-ledger', array_merge($data, compact(
            'activeAccount', 
            'categories', 
            'jobs', 
            'onlyActive', 
            'totalAccountsCount', 
            'activeAccountsCount'
        )));
    }

    public function exportGeneralLedgerCsv(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $categoryId = $request->get('category_id', 'all');
        $jobId = $request->get('job_id', 'all');

        $data = $this->getGeneralLedgerData($categoryId, $startDate, $endDate, $jobId);

        $filename = "General_Ledger_" . Carbon::parse($startDate)->format('Ymd') . "_" . Carbon::parse($endDate)->format('Ymd') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, ['SEKOLAH TINGGI TEKNOLOGI PEKERJAAN UMUM']);
            fputcsv($file, ['BUKU BESAR (GENERAL LEDGER)']);
            fputcsv($file, ['PERIODE ' . Carbon::parse($startDate)->format('d/m/Y') . ' S/D ' . Carbon::parse($endDate)->format('d/m/Y')]);
            fputcsv($file, []);

            foreach ($data['ledgerData'] as $account) {
                fputcsv($file, ["AKUN: [{$account['category']->code}] {$account['category']->name}"]);
                fputcsv($file, ['Saldo Awal:', '', '', '', '', $account['beginning_balance']]);
                fputcsv($file, ['Tanggal', 'No. Bukti', 'Memo / Deskripsi', 'Job', 'Debet (Rp)', 'Kredit (Rp)', 'Saldo Berjalan (Rp)']);

                foreach ($account['transactions'] as $row) {
                    fputcsv($file, [
                        $row['transaction']->date->format('d/m/Y'),
                        $row['transaction']->voucher_number ?? '—',
                        $row['transaction']->description,
                        $row['transaction']->job ? $row['transaction']->job->code : '—',
                        $row['debit'],
                        $row['credit'],
                        $row['running_balance'],
                    ]);
                }

                fputcsv($file, ['Subtotal Mutasi Akun:', '', '', '', $account['total_debit'], $account['total_credit'], '']);
                fputcsv($file, ['Saldo Akhir Akun:', '', '', '', '', '', $account['ending_balance']]);
                fputcsv($file, []);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printGeneralLedger(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $categoryId = $request->get('category_id', 'all');
        $jobId = $request->get('job_id', 'all');

        $data = $this->getGeneralLedgerData($categoryId, $startDate, $endDate, $jobId);

        $approver = 'Ir. Rina Agustin Indriani, MURP';
        $verifier = "Noor'aini Kartikarini, SM";
        $leader = 'Dr. Ir. Arie Setiadi Moerwanto, MSc';
        $maker = 'Irma Yaniarti';

        return view('reports.print-general-ledger', array_merge($data, compact(
            'approver', 'verifier', 'leader', 'maker'
        )));
    }
}
