<?php

namespace App\Http\Controllers;

use App\Exports\IncomesExport;
use App\Http\Requests\StoreIncomeRequest;
use App\Http\Requests\UpdateIncomeRequest;
use App\Models\Account;
use App\Services\IncomeService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IncomeController extends Controller
{
    public function __construct(
        protected IncomeService $incomeService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'category', 'search']);

        return view('incomes.index', [
            'incomes' => $this->incomeService->getAll($filters),
            'categories' => $this->incomeService->getCategories(),
            'accounts' => Account::active()->get(),
        ]);
    }

    public function store(StoreIncomeRequest $request)
    {
        $this->incomeService->create($request->validated());

        return redirect()->back()->with('success', 'Pendapatan berhasil dicatat.');
    }

    public function update(UpdateIncomeRequest $request, $id)
    {
        $this->incomeService->update($id, $request->validated());

        return redirect()->back()->with('success', 'Pendapatan berhasil diubah.');
    }

    public function destroy($id)
    {
        $this->incomeService->delete($id);

        return redirect()->back()->with('success', 'Pendapatan berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'category', 'search']);

        return Excel::download(new IncomesExport($filters), 'pendapatan.xlsx');
    }
}
