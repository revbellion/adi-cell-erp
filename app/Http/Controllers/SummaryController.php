<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        $months = max(1, min(120, (int) $request->get('months', 12)));

        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $allIncomes = Income::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('YEAR(date) as y, MONTH(date) as m, COALESCE(category, "Tanpa Kategori") as cat, SUM(amount) as total')
            ->groupBy('y', 'm', 'cat')
            ->get();

        $allExpenses = Expense::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('YEAR(date) as y, MONTH(date) as m, COALESCE(category, "Tanpa Kategori") as cat, SUM(amount) as total')
            ->groupBy('y', 'm', 'cat')
            ->get();

        $incomeByMonth = $allIncomes->groupBy(fn($i) => sprintf('%04d-%02d', $i->y, $i->m));
        $expenseByMonth = $allExpenses->groupBy(fn($i) => sprintf('%04d-%02d', $i->y, $i->m));

        $results = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');
            $label = $date->locale('id')->isoFormat('MMMM YYYY');

            $monthIncomes = $incomeByMonth->get($key, collect());
            $monthExpenses = $expenseByMonth->get($key, collect());

            $incomeTotal = $monthIncomes->sum('total');
            $expenseTotal = $monthExpenses->sum('total');

            $results[] = [
                'month' => $key,
                'label' => $label,
                'income' => $incomeTotal,
                'expense' => $expenseTotal,
                'profit' => $incomeTotal - $expenseTotal,
                'income_categories' => $monthIncomes->pluck('total', 'cat'),
                'expense_categories' => $monthExpenses->pluck('total', 'cat'),
            ];
        }

        return view('summary.index', compact('results', 'months'));
    }
}
