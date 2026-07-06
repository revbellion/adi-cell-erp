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
use App\Models\HppRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function calculateAccountBalances($accounts, string $period): array
    {
        $dateStart = sprintf('%04d-%02d-01', ...array_map('intval', explode('-', $period)));
        $dateEnd = Carbon::parse($dateStart)->endOfMonth();

        $openingBalances = OpeningBalance::where('period', $period)->pluck('amount', 'account_id');
        $mutationsIn = Mutation::whereBetween('date', [$dateStart, $dateEnd])->selectRaw('to_account_id, SUM(amount) as total')->groupBy('to_account_id')->pluck('total', 'to_account_id');
        $mutationsOut = Mutation::whereBetween('date', [$dateStart, $dateEnd])->selectRaw('from_account_id, SUM(amount) as total')->groupBy('from_account_id')->pluck('total', 'from_account_id');
        $expenses = Expense::whereBetween('date', [$dateStart, $dateEnd])->selectRaw('account_id, SUM(amount) as total')->groupBy('account_id')->pluck('total', 'account_id');
        $incomes = Income::whereBetween('date', [$dateStart, $dateEnd])->whereNotNull('account_id')->selectRaw('account_id, SUM(amount) as total')->groupBy('account_id')->pluck('total', 'account_id');

        $balances = [];
        foreach ($accounts as $account) {
            $balances[$account->id] = (int) (
                ($openingBalances[$account->id] ?? 0)
                + ($mutationsIn[$account->id] ?? 0)
                - ($mutationsOut[$account->id] ?? 0)
                - ($expenses[$account->id] ?? 0)
                + ($incomes[$account->id] ?? 0)
            );
        }

        return $balances;
    }

    public function getDashboardData(string $period): array
    {
        $dateStart = sprintf('%04d-%02d-01', ...array_map('intval', explode('-', $period)));
        $dateEnd = Carbon::parse($dateStart)->endOfMonth();

        $accounts = $this->loadBalances($period);
        [$totalReceivable, $totalEquity] = $this->getReceivableAndEquity($accounts);
        [$cashBalance, $bcaBalance, $bcaInProcess, $cashInProcess] = $this->getCashBcaTransitSummary($accounts);

        // Profit = Laba Rugi standar (cocok sama Laporan Laba Rugi)
        $totalIncome = Income::whereBetween('date', [$dateStart, $dateEnd])
            ->whereNotIn('category', $this->nonPnlIncomeCategories())
            ->sum('amount') ?? 0;

        $totalRevenue = $totalIncome;

        $totalHpp = HppRecord::whereBetween('date', [$dateStart, $dateEnd])
            ->sum('hpp_amount') ?? 0;

        $totalOpExpense = Expense::whereBetween('date', [$dateStart, $dateEnd])
            ->whereNotIn('category', $this->nonPnlExpenseCategories())
            ->sum('amount') ?? 0;

        $netProfit = $totalIncome - $totalHpp - $totalOpExpense;

        // Card "Pengeluaran Bulan Ini" = expense PnL saja (exclude Stok Masuk, Piutang, dll)
        $totalExpense = Expense::whereBetween('date', [$dateStart, $dateEnd])
            ->whereNotIn('category', $this->nonPnlExpenseCategories())
            ->sum('amount') ?? 0;

        // Card "Omset Bulan Ini" = pendapatan real (Penjualan, OMSET PPOB, Jasa Servis, Jasa Cetak, Jasa Tarik Tunai EDC)
        $totalOmset = Income::whereBetween('date', [$dateStart, $dateEnd])
            ->whereIn('category', ['Penjualan', 'OMSET', 'Jasa Servis', 'Jasa Cetak', 'Jasa Tarik Tunai EDC'])
            ->sum('amount') ?? 0;

        $products = Product::activeWithCategory()->get();

        return [
            'accounts' => $accounts,
            'totalEquity' => $totalEquity,
            'totalReceivable' => $totalReceivable,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
            'bcaBalance' => $bcaBalance,
            'bcaInProcess' => $bcaInProcess,
            'cashInProcess' => $cashInProcess,
            'totalIncome' => $totalIncome,
            'totalOmset' => $totalOmset,
            'cashBalance' => $cashBalance,
            'products' => $products,
            'dailyProfits' => $this->getDailyProfits(),
            'chartMonths' => $this->getChartData(),
            'lowStockCount' => $products->filter(fn($p) => $p->is_low_stock)->count(),
            'expiringProducts' => $this->getExpiringProducts(),
        ];
    }

    private function loadBalances(string $period): \Illuminate\Support\Collection
    {
        $accounts = Account::active()->get()->keyBy('id');
        $balances = $this->calculateAccountBalances($accounts, $period);

        foreach ($accounts as $account) {
            $account->balance = $balances[$account->id] ?? 0;
        }

        return $accounts;
    }

    private function getReceivableAndEquity($accounts): array
    {
        $unpaidSub = DB::raw('(SELECT receivable_id, SUM(amount) as paid FROM receivable_payments GROUP BY receivable_id) as rp');

        $totalReceivable = DB::table('receivables')
            ->leftJoin($unpaidSub, 'receivables.id', '=', 'rp.receivable_id')
            ->where('receivables.status', 'unpaid')
            ->selectRaw('COALESCE(SUM(receivables.amount - COALESCE(rp.paid, 0)), 0) as total_remaining')
            ->value('total_remaining') ?? 0;

        $totalEquity = $accounts->sum('balance');

        return [$totalReceivable, $totalEquity];
    }

    private function getCashBcaTransitSummary($accounts): array
    {
        $cashBalance = (int) (($accounts->firstWhere('name', config('accounts.cash_name'))?->balance) ?? 0);
        $bcaBalance = (int) (($accounts->firstWhere('name', config('accounts.bca_name'))?->balance) ?? 0);

        // BCA In Process = EDC pending (cash sudah keluar, bank belum settle)
        $bcaInProcess = \App\Models\PendingTransaction::where('status', 'pending')
            ->where('type', 'edc')
            ->sum('amount') ?? 0;

        // Cash In Process = transfer + tf_masuk pending (BCA sudah terima, cash belum diserahkan)
        $cashInProcess = \App\Models\PendingTransaction::where('status', 'pending')
            ->whereIn('type', ['transfer', 'tf_masuk'])
            ->sum('amount') ?? 0;

        return [$cashBalance, $bcaBalance, $bcaInProcess, $cashInProcess];
    }

    private function getDailyProfits(): array
    {
        $today = Carbon::now()->endOfDay();
        $monthStart = Carbon::now()->startOfMonth();
        $sevenDaysAgo = Carbon::now()->subDays(6)->startOfDay();

        // Mulai dari max(7 hari lalu, awal bulan) — supaya tidak include hari dari bulan lalu
        $start = $sevenDaysAgo->max($monthStart);

        // Pendapatan asli (sama kayak rumus profit utama)
        $dailyIncomes = Income::whereBetween('date', [$start, $today])
            ->whereNotIn('category', $this->nonPnlIncomeCategories())
            ->selectRaw('DATE(date) as d, SUM(amount) as total')->groupBy('d')->pluck('total', 'd');

        // Biaya asli (sama kayak rumus profit utama)
        $dailyExpenses = Expense::whereBetween('date', [$start, $today])
            ->whereNotIn('category', $this->nonPnlExpenseCategories())
            ->selectRaw('DATE(date) as d, SUM(amount) as total')->groupBy('d')->pluck('total', 'd');

        // HPP per hari (cost of goods sold)
        $dailyHpp = HppRecord::whereBetween('date', [$start, $today])
            ->selectRaw('DATE(date) as d, SUM(hpp_amount) as total')->groupBy('d')->pluck('total', 'd');

        $dailyProfits = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            if (Carbon::parse($date)->lt($start)) continue;
            $income = (int) ($dailyIncomes[$date] ?? 0);
            $expense = (int) ($dailyExpenses[$date] ?? 0);
            $hpp = (int) ($dailyHpp[$date] ?? 0);
            $dailyProfits[] = [
                'date' => $date,
                'income' => $income,
                'expense' => $expense,
                'profit' => $income - $expense - $hpp,
            ];
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

    private function getExpiringProducts(): \Illuminate\Support\Collection
    {
        $warningDays = config('products.expiry_warning_days', 7);
        $now = Carbon::now();
        $warningDate = $now->copy()->addDays($warningDays);

        return StockTransaction::where('type', 'in')
            ->whereNotNull('expired_at')
            ->where('expired_at', '>=', $now)
            ->where('expired_at', '<=', $warningDate)
            ->with('product.category')
            ->get();
    }

    /**
     * Kategori income yang tidak masuk pendapatan operasional (cash movement murni).
     */
    private function nonPnlIncomeCategories(): array
    {
        return collect(config('categories.income.system'))
            ->where('pnl', false)
            ->pluck('key')
            ->values()
            ->all();
    }

    /**
     * Kategori expense yang tidak masuk biaya operasional (cash movement murni).
     */
    private function nonPnlExpenseCategories(): array
    {
        $system = collect(config('categories.expense.system'))
            ->where('pnl', false)->pluck('key');
        $user = collect(config('categories.expense.user'))
            ->where('pnl', false)->pluck('key');
        return $system->merge($user)->values()->all();
    }
}
