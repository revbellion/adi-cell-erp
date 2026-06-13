<?php

namespace App\Http\Controllers;

use App\Exports\MutationsExport;
use App\Http\Requests\StoreMutationRequest;
use App\Http\Requests\UpdateMutationRequest;
use App\Models\Account;
use App\Services\MutationService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MutationController extends Controller
{
    public function __construct(
        protected MutationService $mutationService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'search']);

        return view('mutations.index', [
            'mutations' => $this->mutationService->getAll($filters),
            'accounts' => Account::active()->get(),
        ]);
    }

    public function store(StoreMutationRequest $request)
    {
        $this->mutationService->create($request->validated());

        return redirect()->back()->with('success', 'Mutasi berhasil dicatat.');
    }

    public function update(UpdateMutationRequest $request, $id)
    {
        $this->mutationService->update($id, $request->validated());

        return redirect()->back()->with('success', 'Mutasi berhasil diubah.');
    }

    public function destroy($id)
    {
        $this->mutationService->delete($id);

        return redirect()->back()->with('success', 'Mutasi berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'search']);

        return Excel::download(new MutationsExport($filters), 'mutasi.xlsx');
    }
}
