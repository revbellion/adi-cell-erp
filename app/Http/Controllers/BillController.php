<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringBillRequest;
use App\Http\Requests\UpdateRecurringBillRequest;
use App\Models\Account;
use App\Models\RecurringBill;
use App\Services\BillService;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function __construct(
        protected BillService $billService
    ) {}

    public function index(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));

        return view('bills.index', [
            'bills' => $this->billService->getBillsWithStatus($period),
            'period' => $period,
            'accounts' => Account::active()->get(),
            'categories' => $this->billService->getPaymentCategories(),
        ]);
    }

    public function store(StoreRecurringBillRequest $request)
    {
        $this->billService->createBill($request->validated());

        return redirect()->back()->with('success', 'Tagihan berhasil ditambahkan.');
    }

    public function update(UpdateRecurringBillRequest $request, RecurringBill $bill)
    {
        $this->billService->updateBill($bill, $request->validated());

        return redirect()->back()->with('success', 'Tagihan berhasil diubah.');
    }

    public function destroy(RecurringBill $bill)
    {
        $this->billService->deleteBill($bill);

        return redirect()->back()->with('success', 'Tagihan berhasil dihapus.');
    }

    public function pay(Request $request, RecurringBill $recurring_bill)
    {
        $request->validate([
            'period' => 'required|string|max:7',
            'amount' => 'nullable|integer|min:1',
            'account_id' => 'required|exists:accounts,id',
        ]);

        $this->billService->payBill(
            $recurring_bill,
            $request->period,
            $request->amount,
            $request->account_id
        );

        return redirect()->back()->with('success', 'Tagihan ' . $recurring_bill->name . ' berhasil dibayar.');
    }
}
