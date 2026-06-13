<?php

namespace App\Services;

use App\Models\Account;
use App\Models\OpeningBalance;
use App\Models\Mutation;
use App\Models\Expense;
use App\Models\Receivable;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDashboardData(string $period): array
    {
        [$year, $month] = explode('-', $period);
        $year = (int) $year;
        $month = (int) $month;
        $dateStart = sprintf('%04d-%02d-01', $year, $month);
        $dateEnd = Carbon::parse($dateStart)->endOfMonth();

        $accounts = Account::active()->get()->keyBy('id');

        $openingBalances = OpeningBalance::where('period', $period)
            ->pluck('amount', 'account_id');

        $mutationsIn = Mutation::whereBetween('date', [$dateStart, $dateEnd])
            ->selectRaw('to_account_id, SUM(amount) as total')
            ->groupBy('to_account_id')
            ->pluck('total', 'to_account_id');

        $mutationsOut = Mutation::whereBetween('date', [$dateStart, $dateEnd])
            ->selectRaw('from_account_id, SUM(amount) as total')
            ->groupBy('from_account_id')
            ->pluck('total', 'from_account_id');

        $expenses = Expense::whereBetween('date', [$dateStart, $dateEnd])
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        $payments = DB::table('receivable_payments')
            ->whereBetween('date', [$dateStart, $dateEnd])
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        $incomes = Income::whereBetween('date', [$dateStart, $dateEnd])
            ->whereNotNull('account_id')
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        foreach ($accounts as $account) {
            $account->balance = (int) (
                ($openingBalances[$account->id] ?? 0)
                + ($mutationsIn[$account->id] ?? 0)
                - ($mutationsOut[$account->id] ?? 0)
                - ($expenses[$account->id] ?? 0)
                + ($payments[$account->id] ?? 0)
                + ($incomes[$account->id] ?? 0)
            );
        }

        $totalReceivable = Receivable::unpaid()->sum('amount');
        $totalEquity = $accounts->sum('balance') + $totalReceivable;

        $cashAccount = $accounts->firstWhere('name', 'CASH');
        $cashBalance = $cashAccount ? (int) $cashAccount->balance : 0;

        $bcaAccount = $accounts->firstWhere('name', 'BCA');
        $bcaBalance = $bcaAccount ? (int) $bcaAccount->balance : 0;

        $totalExpense = Expense::whereBetween('date', [$dateStart, $dateEnd])->sum('amount');
        $totalOpeningBalance = OpeningBalance::where('period', $period)->sum('amount');
        $totalMutationsIn = Mutation::whereBetween('date', [$dateStart, $dateEnd])->sum('amount');
        $totalMutationsOut = Mutation::whereBetween('date', [$dateStart, $dateEnd])->sum('amount');

        $totalIncome = Income::whereBetween('date', [$dateStart, $dateEnd])->sum('amount');

        $profitBersih = $totalEquity - $totalOpeningBalance;

        $dayOfMonth = Carbon::now()->day;
        $daysInMonth = Carbon::now()->daysInMonth;
        $avgDaily = $dayOfMonth > 0 ? $totalExpense / $dayOfMonth : 0;
        $estIncome = $daysInMonth * $avgDaily;

        $recentMutasi = Mutation::with('fromAccount', 'toAccount')
            ->latest()
            ->take(10)
            ->get();

        $recentReceivables = Receivable::with('receivablePayments')
            ->latest()
            ->take(10)
            ->get();

        $dailyProfits = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $dailyIncome = Income::whereDate('date', $date)->sum('amount');
            $dailyExpense = Expense::whereDate('date', $date)->sum('amount');
            $dailyProfits[] = [
                'date' => $date,
                'income' => $dailyIncome,
                'expense' => $dailyExpense,
                'profit' => $dailyIncome - $dailyExpense,
            ];
        }

        return [
            'accounts' => $accounts,
            'totalEquity' => $totalEquity,
            'totalReceivable' => $totalReceivable,
            'totalExpense' => $totalExpense,
            'totalOpeningBalance' => $totalOpeningBalance,
            'totalMutationsIn' => $totalMutationsIn,
            'totalMutationsOut' => $totalMutationsOut,
            'profitBersih' => $profitBersih,
            'bcaBalance' => $bcaBalance,
            'totalIncome' => $totalIncome,
            'avgDaily' => $avgDaily,
            'cashBalance' => $cashBalance,
            'recentMutasi' => $recentMutasi,
            'recentReceivables' => $recentReceivables,
            'dailyProfits' => $dailyProfits,
            'chartMonths' => $this->getChartData(),
        ];
    }

    public function getChartData(): array
    {
        $labels = [];
        $incomes = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;
            $start = sprintf('%04d-%02d-01', $year, $month);
            $end = Carbon::parse($start)->endOfMonth();

            $labels[] = $date->locale('id')->isoFormat('MMM');
            $incomes[] = Income::whereBetween('date', [$start, $end])->sum('amount');
            $expenses[] = Expense::whereBetween('date', [$start, $end])->sum('amount');
        }

        return [
            'labels' => $labels,
            'incomes' => $incomes,
            'expenses' => $expenses,
        ];
    }
}
