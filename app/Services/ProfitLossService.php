<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\HppRecord;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitLossService
{
    public function getData(string $period): array
    {
        $dateStart = sprintf('%04d-%02d-01', ...array_map('intval', explode('-', $period)));
        $dateEnd = Carbon::parse($dateStart)->endOfMonth();

        // === PENDAPATAN (Revenue) — exclude pure cash movements ===
        $revenueQuery = Income::whereBetween('date', [$dateStart, $dateEnd])
            ->whereNotNull('category')
            ->whereNotIn('category', $this->nonPnlIncomeCategories())
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc');

        $revenueByCategory = $revenueQuery->get();
        $totalRevenue = $revenueByCategory->sum('total');

        // === PENDAPATAN LAIN (Other Income) ===
        $otherIncomeQuery = Income::whereBetween('date', [$dateStart, $dateEnd])
            ->where(function ($q) {
                $q->whereIn('category', $this->nonPnlIncomeCategories());
            })
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc');

        $otherIncomeByCategory = $otherIncomeQuery->get();
        $totalOtherIncome = $otherIncomeByCategory->sum('total');

        // === HPP (Cost of Goods Sold) ===
        $hppData = HppRecord::whereBetween('date', [$dateStart, $dateEnd])
            ->select(
                DB::raw('SUM(hpp_amount) as total_hpp'),
                DB::raw('SUM(selling_amount) as total_selling'),
                DB::raw('SUM(profit_amount) as total_profit'),
                DB::raw('SUM(qty) as total_qty')
            )
            ->first();

        $totalHpp = (int) ($hppData->total_hpp ?? 0);
        $totalSelling = (int) ($hppData->total_selling ?? 0);
        $totalProfitHpp = (int) ($hppData->total_profit ?? 0);

        // === HPP per kategori produk ===
        $hppByCategory = HppRecord::whereBetween('date', [$dateStart, $dateEnd])
            ->whereNotNull('product_category_id')
            ->select('product_category_id', DB::raw('SUM(hpp_amount) as total_hpp'), DB::raw('SUM(selling_amount) as total_selling'), DB::raw('SUM(profit_amount) as total_profit'), DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_category_id')
            ->with('category')
            ->get();

        // === BIAYA (Expenses) — exclude pure cash movements ===
        $expensesQuery = Expense::whereBetween('date', [$dateStart, $dateEnd])
            ->whereNotNull('category')
            ->whereNotIn('category', $this->nonPnlExpenseCategories())
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc');

        $expensesByCategory = $expensesQuery->get();
        $totalExpenses = $expensesByCategory->sum('total');

        // === DIVISI JASA ===
        $cetakRevenue = Income::whereBetween('date', [$dateStart, $dateEnd])
            ->where('category', 'Jasa Cetak')->sum('amount');
        $cetakExpense = Expense::whereBetween('date', [$dateStart, $dateEnd])
            ->where('category', 'Jasa Cetak')->sum('amount');

        $servisRevenue = Income::whereBetween('date', [$dateStart, $dateEnd])
            ->where('category', 'Jasa Servis')->sum('amount');
        $servisExpense = Expense::whereBetween('date', [$dateStart, $dateEnd])
            ->where('category', 'Jasa Servis')->sum('amount');

        // === Hitung Laba/Rugi ===
        $labaKotor = $totalRevenue - $totalHpp;
        $labaBersih = $labaKotor - $totalExpenses;

        // === Data untuk ringkasan ===
        $summary = [
            'total_revenue' => $totalRevenue,
            'total_hpp' => $totalHpp,
            'total_selling' => $totalSelling,
            'total_profit_hpp' => $totalProfitHpp,
            'total_other_income' => $totalOtherIncome,
            'total_expenses' => $totalExpenses,
            'laba_kotor' => $labaKotor,
            'laba_bersih' => $labaBersih,
            'total_qty' => (int) ($hppData->total_qty ?? 0),
        ];

        return compact(
            'revenueByCategory', 'totalRevenue',
            'otherIncomeByCategory', 'totalOtherIncome',
            'hppByCategory', 'totalHpp', 'totalSelling', 'totalProfitHpp',
            'expensesByCategory', 'totalExpenses',
            'summary',
            'dateStart', 'dateEnd',
            'cetakRevenue', 'cetakExpense',
            'servisRevenue', 'servisExpense',
        );
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

    /**
     * Mendapatkan daftar periode yang tersedia (bulan-bulan yang ada transaksinya).
     */
    public function getAvailablePeriods(): array
    {
        $dates = collect();

        Income::select('date')->each(function ($item) use ($dates) {
            $dates->push($item->date->format('Y-m'));
        });
        Expense::select('date')->each(function ($item) use ($dates) {
            $dates->push($item->date->format('Y-m'));
        });

        return $dates
            ->unique()
            ->sort()
            ->reverse()
            ->values()
            ->toArray();
    }
}
