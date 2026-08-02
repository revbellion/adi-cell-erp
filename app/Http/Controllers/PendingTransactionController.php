<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePendingTransactionRequest;
use App\Models\Account;
use App\Models\PendingTransaction;
use App\Services\PendingTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PendingsExport;

class PendingTransactionController extends Controller
{
    public function __construct(
        protected PendingTransactionService $pendingService
    ) {}

    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $result = $this->pendingService->getAll($filters);

        return view('pending-transactions.index', [
            'pendings' => $result['pendings'],
            'accounts' => Account::active()->visible()->get(),
            'totalPending' => $result['totalPending'],
            'totalCompleted' => $result['totalCompleted'],
            'totalCancelled' => $result['totalCancelled'],
        ]);
    }

    public function store(StorePendingTransactionRequest $request)
    {
        try {
            $this->pendingService->create($request->validated());
            return redirect()->back()->with('success', 'Transaksi pending berhasil dicatat.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function complete(Request $request, $id)
    {
        $validated = $request->validate([
            'completed_type' => 'required|in:masuk,keluar',
            'completed_account_id' => 'required|exists:accounts,id',
            'completed_date' => 'required|date',
        ]);

        try {
            $this->pendingService->complete($id, $validated);
            return redirect()->back()->with('success', 'Transaksi berhasil diselesaikan.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->pendingService->delete($id);
            return redirect()->back()->with('success', 'Transaksi pending berhasil dihapus.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancel($id)
    {
        try {
            $this->pendingService->cancel($id);
            return redirect()->back()->with('success', 'Transaksi pending berhasil dibatalkan.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updateDescription(Request $request, $id)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
        ]);

        try {
            $this->pendingService->updateDescription($id, $validated['description']);
            return redirect()->back()->with('success', 'Deskripsi transaksi berhasil diperbarui.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $deleted = 0;
        foreach ($request->ids as $id) {
            try {
                $this->pendingService->delete($id);
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

        return Excel::download(new PendingsExport($filters), 'transaksi-pending.xlsx');
    }

    private function parseFilters(Request $request): array
    {
        $raw = $request->only(['status', 'type', 'search']);
        $raw = array_map(fn($v) => $v === '' ? null : $v, $raw);

        return array_filter(
            Validator::make($raw, [
                'status' => 'nullable|in:pending,completed,cancelled',
                'type' => 'nullable|in:edc,transfer,tf_masuk',
                'search' => 'nullable|string|max:100',
            ])->valid(),
            fn($v) => $v !== null
        );
    }
}
