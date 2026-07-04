<?php

namespace App\Http\Controllers;

use App\Exports\ReceivablesExport;
use App\Http\Requests\StoreReceivablePaymentRequest;
use App\Http\Requests\StoreReceivableRequest;
use App\Http\Requests\UpdateReceivableRequest;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Receivable;
use App\Services\ReceivableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ReceivableController extends Controller
{
    public function __construct(
        protected ReceivableService $receivableService
    ) {}

    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $result = $this->receivableService->getAll($filters);

        return view('receivables.index', [
            'receivables' => $result['receivables'],
            'accounts' => Account::active()->visible()->get(),
            'customers' => Customer::active()->orderBy('name')->get(),
            'totalAmount' => $result['totalAmount'],
            'totalRemaining' => $result['totalRemaining'],
        ]);
    }

    public function store(StoreReceivableRequest $request)
    {
        try {
            $this->receivableService->create($request->validated());
            return redirect()->back()->with('success', 'Piutang berhasil dicatat.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(UpdateReceivableRequest $request, $id)
    {
        try {
            $this->receivableService->update($id, $request->validated());
            return redirect()->back()->with('success', 'Piutang berhasil diubah.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function pay(StoreReceivablePaymentRequest $request)
    {
        try {
            $this->receivableService->pay($request->input('receivable_id'), $request->validated());
            return redirect()->back()->with('success', 'Pembayaran berhasil dicatat.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function batchPay(Request $request)
    {
        $request->validate([
            'receivable_ids' => 'required|string',
            'account_id'     => 'required|exists:accounts,id',
            'date'           => 'required|date',
        ]);

        $ids = array_filter(explode(',', $request->receivable_ids));
        $success = 0;
        $failed = 0;

        DB::transaction(function () use ($ids, $request, &$success, &$failed) {
            foreach ($ids as $id) {
                try {
                    $receivable = Receivable::lockForUpdate()->findOrFail($id);
                    if ($receivable->status === 'unpaid' && $receivable->remaining > 0) {
                        $this->receivableService->pay($id, [
                            'account_id' => $request->account_id,
                            'amount'     => $receivable->remaining,
                            'date'       => $request->date,
                        ]);
                        $success++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                }
            }
        });

        $msg = "{$success} piutang berhasil dibayar.";
        if ($failed > 0) $msg .= " {$failed} gagal.";

        return redirect()->back()->with('success', $msg);
    }

    public function addNominal(Request $request, $id)
    {
        $request->validate(['amount' => 'required|integer|min:1']);

        try {
            $this->receivableService->addNominal($id, $request->input('amount'));
            return redirect()->back()->with('success', 'Nominal piutang berhasil ditambahkan.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function void($id)
    {
        try {
            $this->receivableService->void($id);
            return redirect()->back()->with('success', 'Piutang berhasil dibatalkan.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->receivableService->delete($id);
            return redirect()->back()->with('success', 'Piutang berhasil dihapus.');
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
                $this->receivableService->delete($id);
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

        return Excel::download(new ReceivablesExport($filters), 'piutang.xlsx');
    }

    private function parseFilters(Request $request): array
    {
        $raw = $request->only(['status', 'date_from', 'date_to', 'search']);
        $raw = array_map(fn($v) => $v === '' ? null : $v, $raw);

        return array_filter(
            Validator::make($raw, [
                'status' => 'nullable|in:unpaid,paid,overdue,voided',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'search' => 'nullable|string|max:100',
            ])->valid(),
            fn($v) => $v !== null
        );
    }
}
