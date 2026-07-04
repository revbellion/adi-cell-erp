<?php

namespace App\Services;

use App\Models\Account;
use App\Models\HppRecord;
use App\Models\Income;
use App\Models\Expense;
use App\Models\InitialCapital;
use App\Models\OpeningBalance;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BalanceSheetService
{
    public function getData(string $date): array
    {
        $targetDate = Carbon::parse($date)->endOfDay();
        $period = $targetDate->format('Y-m');
        $monthStart = $targetDate->copy()->startOfMonth();

        // Auto-init initial capital dari periode pertama jika belum ada
        $this->ensureInitialCapital();

        // === ASET LANCAR ===

        // 1. Saldo per akun
        $accounts = Account::active()->orderBy('type')->orderBy('name')->get();
        $accountBalances = $this->calculateBalances($accounts, $targetDate);

        $totalCash = 0;
        $cashAccounts = [];
        $ewalletAccounts = [];
        $bankAccounts = [];
        $ppobAccounts = [];

        foreach ($accounts as $account) {
            $balance = $accountBalances[$account->id] ?? 0;
            $entry = [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type,
                'balance' => $balance,
            ];

            if ($account->type === 'cash') {
                $cashAccounts[] = $entry;
            } elseif ($account->type === 'bank') {
                $bankAccounts[] = $entry;
            } elseif ($account->type === 'ewallet') {
                $ewalletAccounts[] = $entry;
            } elseif ($account->type === 'ppob') {
                $ppobAccounts[] = $entry;
            }

            $totalCash += $balance;
        }

        // 2. Piutang (belum dibayar)
        $totalReceivables = Receivable::unpaid()->sum('amount');

        // 3. Persediaan barang (stock value)
        $totalInventory = Product::active()->select(DB::raw('SUM(stock * purchase_price) as total'))->value('total') ?? 0;

        // 4. Total Aset Lancar
        $totalCurrentAssets = $totalCash + $totalReceivables + $totalInventory;

        // === KEWAJIBAN ===
        // Belum ada modul hutang, placeholder 0
        $totalLiabilities = 0;

        // === EKUITAS ===

        // 1. Modal Awal (modal awal bisnis saat pertama kali didirikan)
        $initialCapital = InitialCapital::sum('amount');

        // 2. Prive (pengambilan profit owner)
        $totalPrive = Expense::where('date', '<=', $targetDate)
            ->where('category', 'Prive')->sum('amount');

        // 3. Laba ditahan (profit from start of year until before this month)
        $retainedEarnings = $this->calculateRetainedEarnings($targetDate);

        // 4. Laba periode berjalan
        $currentProfit = $this->calculatePeriodProfit($monthStart, $targetDate);

        $totalEquity = $initialCapital + $retainedEarnings + $currentProfit - $totalPrive;

        return [
            'date' => $targetDate->format('Y-m-d'),
            'dateFormatted' => Carbon::parse($date)->translatedFormat('d F Y'),
            'accounts' => [
                'cash' => $cashAccounts,
                'bank' => $bankAccounts,
                'ewallet' => $ewalletAccounts,
                'ppob' => $ppobAccounts,
            ],
            'totalCash' => $totalCash,
            'totalReceivables' => $totalReceivables,
            'totalInventory' => $totalInventory,
            'totalCurrentAssets' => $totalCurrentAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalModalAwal' => $initialCapital,
            'initialCapital' => $initialCapital,
            'totalPrive' => $totalPrive,
            'retainedEarnings' => $retainedEarnings,
            'currentProfit' => $currentProfit,
            'totalEquity' => $totalEquity,
            'balanceCheck' => $totalCurrentAssets === ($totalLiabilities + $totalEquity),
            'balanceDiff' => $totalCurrentAssets - ($totalLiabilities + $totalEquity),
        ];
    }

    private function calculateBalances($accounts, Carbon $targetDate): array
    {
        $period = $targetDate->format('Y-m');

        $openingBalances = OpeningBalance::where('period', $period)
            ->pluck('amount', 'account_id');

        $mutationsIn = Mutation::where('date', '<=', $targetDate)
            ->selectRaw('to_account_id, SUM(amount) as total')
            ->groupBy('to_account_id')
            ->pluck('total', 'to_account_id');

        $mutationsOut = Mutation::where('date', '<=', $targetDate)
            ->selectRaw('from_account_id, SUM(amount) as total')
            ->groupBy('from_account_id')
            ->pluck('total', 'from_account_id');

        $totalIncomes = Income::where('date', '<=', $targetDate)
            ->whereNotNull('account_id')
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        $totalExpenses = Expense::where('date', '<=', $targetDate)
            ->whereNotNull('account_id')
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        $balances = [];
        foreach ($accounts as $account) {
            $balances[$account->id] = (int) (
                ($openingBalances[$account->id] ?? 0)
                + ($mutationsIn[$account->id] ?? 0)
                - ($mutationsOut[$account->id] ?? 0)
                + ($totalIncomes[$account->id] ?? 0)
                - ($totalExpenses[$account->id] ?? 0)
            );

            if ($balances[$account->id] < 0) {
                $balances[$account->id] = 0;
            }
        }

        return $balances;
    }

    private function calculateRetainedEarnings(Carbon $targetDate): int
    {
        $yearStart = $targetDate->copy()->startOfYear();
        $monthStart = $targetDate->copy()->startOfMonth();

        // Profit from year start until before this month
        if ($monthStart->eq($yearStart)) {
            return 0;
        }

        $periodEnd = $monthStart->copy()->subDay()->endOfDay();

        return $this->calculateProfitBetween($yearStart, $periodEnd);
    }

    private function calculatePeriodProfit(Carbon $periodStart, Carbon $periodEnd): int
    {
        return $this->calculateProfitBetween($periodStart, $periodEnd);
    }

    private function calculateProfitBetween(Carbon $start, Carbon $end): int
    {
        $totalIncome = Income::whereBetween('date', [$start, $end])
            ->whereNotIn('category', $this->nonPnlIncomeCategories())
            ->sum('amount') ?? 0;

        $totalExpense = Expense::whereBetween('date', [$start, $end])
            ->whereNotIn('category', $this->nonPnlExpenseCategories())
            ->sum('amount') ?? 0;

        $totalHpp = HppRecord::whereBetween('date', [$start, $end])
            ->sum('hpp_amount') ?? 0;

        return $totalIncome - $totalHpp - $totalExpense;
    }

    /**
     * Kategori income yang tidak masuk pendapatan operasional.
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
     * Kategori expense yang tidak masuk biaya operasional.
     */
    private function nonPnlExpenseCategories(): array
    {
        $system = collect(config('categories.expense.system'))
            ->where('pnl', false)->pluck('key');
        $user = collect(config('categories.expense.user'))
            ->where('pnl', false)->pluck('key');
        return $system->merge($user)->values()->all();
    }

    public function getAvailableDates(): array
    {
        $dates = collect();

        Income::select('date')->each(function ($item) use ($dates) {
            $dates->push($item->date->format('Y-m-d'));
        });
        Expense::select('date')->each(function ($item) use ($dates) {
            $dates->push($item->date->format('Y-m-d'));
        });

        return $dates
            ->unique()
            ->sort()
            ->reverse()
            ->values()
            ->toArray();
    }

    private function ensureInitialCapital(): void
    {
        if (InitialCapital::exists()) {
            return;
        }

        $firstPeriod = OpeningBalance::min('period');
        if (!$firstPeriod) {
            return;
        }

        $total = OpeningBalance::where('period', $firstPeriod)->sum('amount');
        if ($total <= 0) {
            return;
        }

        InitialCapital::create([
            'amount' => $total,
            'date' => $firstPeriod . '-01',
            'description' => 'Modal awal otomatis dari saldo awal periode ' . $firstPeriod,
        ]);
    }
}
