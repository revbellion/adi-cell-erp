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
use Maatwebsite\Excel\Facades\Excel;

class ReceivableController extends Controller
{
    public function __construct(
        protected ReceivableService $receivableService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to', 'search']);

        return view('receivables.index', [
            'receivables' => $this->receivableService->getAll($filters),
            'accounts' => Account::active()->get(),
        ]);
    }

    public function store(StoreReceivableRequest $request)
    {
        $this->receivableService->create($request->validated());

        return redirect()->back()->with('success', 'Piutang berhasil dicatat.');
    }

    public function update(UpdateReceivableRequest $request, $id)
    {
        $this->receivableService->update($id, $request->validated());

        return redirect()->back()->with('success', 'Piutang berhasil diubah.');
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
        $filters = $request->only(['status', 'date_from', 'date_to', 'search']);

        return Excel::download(new ReceivablesExport($filters), 'piutang.xlsx');
    }
}
