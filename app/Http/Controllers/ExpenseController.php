<?php

namespace App\Http\Controllers;

use App\Exports\ExpensesExport;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Services\ExpenseService;
use App\Models\Account;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService
    ) {}

    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $result = $this->expenseService->getAll($filters);

        return view('expenses.index', [
            'expenses' => $result['expenses'],
            'accounts' => Account::active()->visible()->get(),
            'categories' => $this->expenseService->getCategories(),
            'userCategories' => config('categories.expense.user'),
            'totalAmount' => $result['totalAmount'],
            'typeFilter' => $filters['type'] ?? null,
        ]);
    }

    public function store(StoreExpenseRequest $request)
    {
        try {
            $this->expenseService->create($request->validated());
            return redirect()->back()->with('success', 'Pengeluaran berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat pengeluaran: ' . $e->getMessage());
        }
    }

    public function update(UpdateExpenseRequest $request, $id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->receivable_id !== null) {
            return redirect()->back()->with('error', 'Pengeluaran ini berasal dari modul Piutang. Silakan edit dari modul Piutang.');
        }

        try {
            $this->expenseService->update($id, $request->validated());
            return redirect()->back()->with('success', 'Pengeluaran berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah pengeluaran: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->receivable_id !== null) {
            return redirect()->back()->with('error', 'Pengeluaran ini berasal dari modul Piutang. Silakan hapus dari modul Piutang.');
        }

        try {
            $this->expenseService->delete($id);
            return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus pengeluaran: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $deleted = 0;
        foreach ($request->ids as $id) {
            try {
                $this->expenseService->delete($id);
                $deleted++;
            } catch (\Exception $e) {
                // skip
            }
        }
        return redirect()->back()->with('success', "{$deleted} data berhasil dihapus.");
    }

    public function export(Request $request)
    {
        $filters = $this->parseFilters($request);

        return Excel::download(new ExpensesExport($filters), 'pengeluaran.xlsx');
    }

    private function parseFilters(Request $request): array
    {
        $raw = $request->only(['date_from', 'date_to', 'category', 'search', 'type']);
        $raw = array_map(fn($v) => $v === '' ? null : $v, $raw);

        return array_filter(
            Validator::make($raw, [
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'category' => 'nullable|string|max:100',
                'search' => 'nullable|string|max:100',
                'type' => 'nullable|in:real,cash_movement,operational',
            ])->valid(),
            fn($v) => $v !== null
        );
    }
}
