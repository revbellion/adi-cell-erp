<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Income;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        $months = $request->get('months', 12);

        $results = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('Y-m');
            $label = $date->locale('id')->isoFormat('MMMM YYYY');

            $income = Income::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');

            $expense = Expense::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');

            $incomeByCategory = Income::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->selectRaw('COALESCE(category, "Tanpa Kategori") as cat, SUM(amount) as total')
                ->groupBy('cat')
                ->pluck('total', 'cat');

            $expenseByCategory = Expense::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->selectRaw('COALESCE(category, "Tanpa Kategori") as cat, SUM(amount) as total')
                ->groupBy('cat')
                ->pluck('total', 'cat');

            $results[] = [
                'month' => $month,
                'label' => $label,
                'income' => $income,
                'expense' => $expense,
                'profit' => $income - $expense,
                'income_categories' => $incomeByCategory,
                'expense_categories' => $expenseByCategory,
            ];
        }

        return view('summary.index', compact('results', 'months'));
    }
}
