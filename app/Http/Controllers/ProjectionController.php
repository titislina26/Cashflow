<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProjectionController extends Controller
{
    public function index(Request $request)
    {
        $activeAccount = session('active_account', 'petty_cash');
        $projectionMonths = (int) $request->get('months', 6);
        $insufficientData = false;

        // Verify if there are enough transactions
        $transactionCount = Transaction::where('account', $activeAccount)->count();
        if ($transactionCount < 3) {
            return view('projections', [
                'activeAccount' => $activeAccount,
                'insufficientData' => true,
                'projectionMonths' => $projectionMonths
            ]);
        }

        // Get monthly aggregate data for the last 6 months (only months that have transactions)
        $monthlyStats = Transaction::where('account', $activeAccount)
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, 
                         SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income, 
                         SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->values();

        if ($monthlyStats->isEmpty()) {
            return view('projections', [
                'activeAccount' => $activeAccount,
                'insufficientData' => true,
                'projectionMonths' => $projectionMonths
            ]);
        }

        // Calculate averages
        $avgIncome = $monthlyStats->avg('income') ?? 0;
        $avgExpense = $monthlyStats->avg('expense') ?? 0;

        // Calculate slope (trend) for linear regression
        $incomes = $monthlyStats->pluck('income')->toArray();
        $expenses = $monthlyStats->pluck('expense')->toArray();

        $incomeSlope = $this->calculateSlope($incomes);
        $expenseSlope = $this->calculateSlope($expenses);

        // Generate scenario projections
        $optimistic = [];
        $realistic = [];
        $pessimistic = [];

        $optCumulative = 0;
        $realCumulative = 0;
        $pesCumulative = 0;

        for ($i = 0; $i < $projectionMonths; $i++) {
            $futureDate = Carbon::now()->addMonths($i + 1);
            $label = $futureDate->translatedFormat('F Y');
            $shortLabel = $futureDate->translatedFormat('M');

            // Realistic (multiplier 1.0)
            $realIncome = round(max(0, $avgIncome + $incomeSlope * $i));
            $realExpense = round(max(0, $avgExpense + $expenseSlope * $i));
            $realNet = $realIncome - $realExpense;
            $realCumulative += $realNet;

            // Optimistic (income +20%, expense -15%)
            $optIncome = round(max(0, ($avgIncome + $incomeSlope * $i) * 1.20));
            $optExpense = round(max(0, ($avgExpense + $expenseSlope * $i) * 0.85));
            $optNet = $optIncome - $optExpense;
            $optCumulative += $optNet;

            // Pessimistic (income -20%, expense +15%)
            $pesIncome = round(max(0, ($avgIncome + $incomeSlope * $i) * 0.80));
            $pesExpense = round(max(0, ($avgExpense + $expenseSlope * $i) * 1.15));
            $pesNet = $pesIncome - $pesExpense;
            $pesCumulative += $pesNet;

            $realistic[] = [
                'label' => $label,
                'shortLabel' => $shortLabel,
                'income' => $realIncome,
                'expense' => $realExpense,
                'net' => $realNet,
                'cumulative' => $realCumulative
            ];

            $optimistic[] = [
                'label' => $label,
                'shortLabel' => $shortLabel,
                'income' => $optIncome,
                'expense' => $optExpense,
                'net' => $optNet,
                'cumulative' => $optCumulative
            ];

            $pessimistic[] = [
                'label' => $label,
                'shortLabel' => $shortLabel,
                'income' => $pesIncome,
                'expense' => $pesExpense,
                'net' => $pesNet,
                'cumulative' => $pesCumulative
            ];
        }

        return view('projections', compact(
            'activeAccount',
            'projectionMonths',
            'insufficientData',
            'realistic',
            'optimistic',
            'pessimistic',
            'optCumulative',
            'realCumulative',
            'pesCumulative'
        ));
    }

    private function calculateSlope(array $values)
    {
        $n = count($values);
        if ($n < 2) return 0.0;

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumX += $i;
            $sumY += $values[$i];
            $sumXY += $i * $values[$i];
            $sumX2 += $i * $i;
        }

        $denom = $n * $sumX2 - $sumX * $sumX;
        if ($denom == 0) return 0.0;

        return ($n * $sumXY - $sumX * $sumY) / $denom;
    }
}
