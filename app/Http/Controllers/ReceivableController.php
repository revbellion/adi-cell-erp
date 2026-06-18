<?php

namespace App\Http\Controllers;

use App\Exports\ReceivablesExport;
use App\Http\Requests\StoreReceivablePaymentRequest;
use App\Http\Requests\StoreReceivableRequest;
use App\Http\Requests\UpdateReceivableRequest;
use App\Models\Account;
use App\Models\Receivable;
use App\Services\ReceivableService;
use Illuminate\Http\Request;
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
            'accounts' => Account::active()->get(),
            'totalAmount' => $result['totalAmount'],
            'totalRemaining' => $result['totalRemaining'],
        ]);
    }

    public function store(StoreReceivableRequest $request)
    {
        $this->receivableService->create($request->validated());

        return redirect()->back()->with('success', 'Piutang berhasil dicatat.');
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
        $this->receivableService->pay($request->input('receivable_id'), $request->validated());

        return redirect()->back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function destroy($id)
    {
        $this->receivableService->delete($id);

        return redirect()->back()->with('success', 'Piutang berhasil dihapus.');
    }

    public function whatsappLink($id)
    {
        $receivable = Receivable::findOrFail($id);

        return redirect($this->receivableService->generateWhatsAppLink($receivable));
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
                'status' => 'nullable|in:unpaid,paid,overdue',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'search' => 'nullable|string|max:100',
            ])->valid(),
            fn($v) => $v !== null
        );
    }
}
