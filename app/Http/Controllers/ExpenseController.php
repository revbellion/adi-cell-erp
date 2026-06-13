<?php

namespace App\Http\Controllers;

use App\Exports\ExpensesExport;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Services\ExpenseService;
use App\Models\Account;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'category', 'search']);

        return view('expenses.index', [
            'expenses' => $this->expenseService->getAll($filters),
            'accounts' => Account::active()->get(),
            'categories' => $this->expenseService->getCategories(),
        ]);
    }

    public function store(StoreExpenseRequest $request)
    {
        $this->expenseService->create($request->validated());

        return redirect()->back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function update(UpdateExpenseRequest $request, $id)
    {
        $this->expenseService->update($id, $request->validated());

        return redirect()->back()->with('success', 'Pengeluaran berhasil diubah.');
    }

    public function destroy($id)
    {
        $this->expenseService->delete($id);

        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'category', 'search']);

        return Excel::download(new ExpensesExport($filters), 'pengeluaran.xlsx');
    }
}
