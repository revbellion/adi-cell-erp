<?php

namespace App\Services;

use App\Models\Account;
use App\Models\OpeningBalance;
use App\Models\Mutation;
use App\Models\Expense;
use App\Models\Receivable;
use App\Models\Income;
use App\Models\Product;
use App\Models\StockTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDashboardData(string $period): array
    {
        $dateStart = sprintf('%04d-%02d-01', ...array_map('intval', explode('-', $period)));
        $dateEnd = Carbon::parse($dateStart)->endOfMonth();

        [$accounts, $totalExpense, $totalOpeningBalance, $totalMutationsIn, $totalMutationsOut, $totalIncome] = $this->loadBalances($period, $dateStart, $dateEnd);
        [$totalReceivable, $totalEquity, $netProfit] = $this->getReceivableAndEquity($accounts, $totalOpeningBalance);
        [$cashBalance, $bcaBalance, $avgDaily] = $this->getCashBcaSummary($accounts, $totalExpense, $dateEnd, $period);

        $products = Product::activeWithCategory()->get();

        return [
            'accounts' => $accounts,
            'totalEquity' => $totalEquity,
            'totalReceivable' => $totalReceivable,
            'totalExpense' => $totalExpense,
            'totalOpeningBalance' => $totalOpeningBalance,
            'totalMutationsIn' => $totalMutationsIn,
            'totalMutationsOut' => $totalMutationsOut,
            'netProfit' => $netProfit,
            'bcaBalance' => $bcaBalance,
            'totalIncome' => $totalIncome,
            'avgDaily' => $avgDaily,
            'cashBalance' => $cashBalance,
            'recentMutations' => Mutation::with('fromAccount', 'toAccount')->latest()->take(10)->get(),
            'recentReceivables' => Receivable::with('receivablePayments')->latest()->take(10)->get(),
            'dailyProfits' => $this->getDailyProfits(),
            'chartMonths' => $this->getChartData(),
            'totalStockValue' => $products->sum('stock_value'),
            'lowStockCount' => $products->filter(fn($p) => $p->is_low_stock)->count(),
            'periodPurchase' => StockTransaction::where('type', 'in')->whereBetween('date', [$dateStart, $dateEnd])->sum(DB::raw('qty * price')),
            'periodSale' => StockTransaction::where('type', 'out')->whereBetween('date', [$dateStart, $dateEnd])->sum(DB::raw('qty * price')),
        ];
    }

    private function loadBalances(string $period, string $dateStart, string $dateEnd): array
    {
        $accounts = Account::active()->get()->keyBy('id');

        $openingBalances = OpeningBalance::where('period', $period)->pluck('amount', 'account_id');
        $mutationsIn = Mutation::whereBetween('date', [$dateStart, $dateEnd])->selectRaw('to_account_id, SUM(amount) as total')->groupBy('to_account_id')->pluck('total', 'to_account_id');
        $mutationsOut = Mutation::whereBetween('date', [$dateStart, $dateEnd])->selectRaw('from_account_id, SUM(amount) as total')->groupBy('from_account_id')->pluck('total', 'from_account_id');
        $expenses = Expense::whereBetween('date', [$dateStart, $dateEnd])->selectRaw('account_id, SUM(amount) as total')->groupBy('account_id')->pluck('total', 'account_id');
        $payments = DB::table('receivable_payments')->whereBetween('date', [$dateStart, $dateEnd])->selectRaw('account_id, SUM(amount) as total')->groupBy('account_id')->pluck('total', 'account_id');
        $incomes = Income::whereBetween('date', [$dateStart, $dateEnd])->whereNotNull('account_id')->selectRaw('account_id, SUM(amount) as total')->groupBy('account_id')->pluck('total', 'account_id');

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

        return [
            $accounts,
            $expenses->sum(),
            $openingBalances->sum(),
            $mutationsIn->sum(),
            $mutationsOut->sum(),
            $incomes->sum(),
        ];
    }

    private function getReceivableAndEquity($accounts, int $totalOpeningBalance): array
    {
        $totalReceivable = DB::table('receivables')
            ->leftJoin(DB::raw('(SELECT receivable_id, SUM(amount) as paid FROM receivable_payments GROUP BY receivable_id) as rp'), 'receivables.id', '=', 'rp.receivable_id')
            ->where('receivables.status', 'unpaid')
            ->selectRaw('COALESCE(SUM(receivables.amount - COALESCE(rp.paid, 0)), 0) as total_remaining')
            ->value('total_remaining') ?? 0;

        $totalEquity = $accounts->sum('balance') + $totalReceivable;

        return [$totalReceivable, $totalEquity, $totalEquity - $totalOpeningBalance];
    }

    private function getCashBcaSummary($accounts, int $totalExpense, Carbon $dateEnd, string $period): array
    {
        $cashBalance = (int) ($accounts->firstWhere('name', config('accounts.cash_name'))->balance ?? 0);
        $bcaBalance = (int) ($accounts->firstWhere('name', config('accounts.bca_name'))->balance ?? 0);

        [$year, $month] = array_map('intval', explode('-', $period));
        $now = Carbon::now();
        $isCurrentPeriod = ($year == $now->year && $month == $now->month);
        $dayOfMonth = $isCurrentPeriod ? $now->day : $dateEnd->day;

        return [$cashBalance, $bcaBalance, $dayOfMonth > 0 ? $totalExpense / $dayOfMonth : 0];
    }

    private function getDailyProfits(): array
    {
        $today = Carbon::now()->toDateString();
        $sevenDaysAgo = Carbon::now()->subDays(6)->toDateString();

        $dailyIncomes = Income::whereBetween('date', [$sevenDaysAgo, $today])
            ->selectRaw('DATE(date) as d, SUM(amount) as total')->groupBy('d')->pluck('total', 'd');

        $dailyExpenses = Expense::whereBetween('date', [$sevenDaysAgo, $today])
            ->selectRaw('DATE(date) as d, SUM(amount) as total')->groupBy('d')->pluck('total', 'd');

        $dailyProfits = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $income = (int) ($dailyIncomes[$date] ?? 0);
            $expense = (int) ($dailyExpenses[$date] ?? 0);
            $dailyProfits[] = ['date' => $date, 'income' => $income, 'expense' => $expense, 'profit' => $income - $expense];
        }

        return $dailyProfits;
    }

    public function getKasirData(): array
    {
        $today = now()->toDateString();

        $products = Product::activeWithCategory()->get();
        $totalStockValue = $products->sum('stock_value');
        $lowStockCount = $products->filter(fn($p) => $p->is_low_stock)->count();
        $lowStockProducts = $products->filter(fn($p) => $p->is_low_stock)->take(10);

        $todaySales = StockTransaction::where('type', 'out')
            ->whereDate('date', $today)
            ->with('product')
            ->get();

        $todayRevenue = $todaySales->sum(fn($t) => $t->qty * $t->price);
        $todayItemsSold = $todaySales->sum('qty');
        $todayCount = $todaySales->groupBy('receipt_id')->count();

        $recentReceipts = StockTransaction::where('type', 'out')
            ->whereDate('date', $today)
            ->selectRaw('receipt_id, SUM(qty * price) as total, COUNT(*) as items')
            ->groupBy('receipt_id')
            ->orderByDesc('receipt_id')
            ->take(10)
            ->get();

        return [
            'todayRevenue' => $todayRevenue,
            'todayItemsSold' => $todayItemsSold,
            'todayCount' => $todayCount,
            'totalStockValue' => $totalStockValue,
            'lowStockCount' => $lowStockCount,
            'lowStockProducts' => $lowStockProducts,
            'recentReceipts' => $recentReceipts,
        ];
    }

    public function getChartData(): array
    {
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $now = Carbon::now()->endOfMonth();

        $chartIncomes = Income::whereBetween('date', [$sixMonthsAgo, $now])
            ->selectRaw('YEAR(date) as y, MONTH(date) as m, SUM(amount) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn($i) => sprintf('%04d-%02d', $i->y, $i->m));

        $chartExpenses = Expense::whereBetween('date', [$sixMonthsAgo, $now])
            ->selectRaw('YEAR(date) as y, MONTH(date) as m, SUM(amount) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn($i) => sprintf('%04d-%02d', $i->y, $i->m));

        $labels = [];
        $incomes = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');

            $labels[] = $date->locale('id')->isoFormat('MMM');
            $incomes[] = (int) ($chartIncomes[$key]->total ?? 0);
            $expenses[] = (int) ($chartExpenses[$key]->total ?? 0);
        }

        return [
            'labels' => $labels,
            'incomes' => $incomes,
            'expenses' => $expenses,
        ];
    }
}
