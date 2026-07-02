<?php

namespace App\Http\Controllers;

use App\Exports\MutationsExport;
use App\Http\Requests\StoreMutationRequest;
use App\Http\Requests\UpdateMutationRequest;
use App\Models\Account;
use App\Services\DashboardService;
use App\Services\MutationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class MutationController extends Controller
{
    public function __construct(
        protected MutationService $mutationService
    ) {}

    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $result = $this->mutationService->getAll($filters);

        $accounts = Account::active()->visible()->get();
        $period = now()->format('Y-m');
        $accountBalances = app(DashboardService::class)->calculateAccountBalances($accounts, $period);

        return view('mutations.index', [
            'mutations' => $result['mutations'],
            'accounts' => $accounts,
            'accountBalances' => $accountBalances,
            'totalAmount' => $result['totalAmount'],
        ]);
    }

    public function store(StoreMutationRequest $request)
    {
        try {
            $this->mutationService->create($request->validated());
            return redirect()->back()->with('success', 'Mutasi berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat mutasi: ' . $e->getMessage());
        }
    }

    public function update(UpdateMutationRequest $request, $id)
    {
        try {
            $this->mutationService->update($id, $request->validated());
            return redirect()->back()->with('success', 'Mutasi berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah mutasi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->mutationService->delete($id);
            return redirect()->back()->with('success', 'Mutasi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus mutasi: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $deleted = 0;
        foreach ($request->ids as $id) {
            try {
                $this->mutationService->delete($id);
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

        return Excel::download(new MutationsExport($filters), 'mutasi.xlsx');
    }

    private function parseFilters(Request $request): array
    {
        $raw = $request->only(['date_from', 'date_to', 'search']);
        $raw = array_map(fn($v) => $v === '' ? null : $v, $raw);

        return array_filter(
            Validator::make($raw, [
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'search' => 'nullable|string|max:100',
            ])->valid(),
            fn($v) => $v !== null
        );
    }
}
