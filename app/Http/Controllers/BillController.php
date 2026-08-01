<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringBillRequest;
use App\Http\Requests\UpdateRecurringBillRequest;
use App\Models\Account;
use App\Models\BillPayment;
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
            'accounts' => Account::active()->visible()->get(),
            'categories' => $this->billService->getPaymentCategories(),
        ]);
    }

    public function store(StoreRecurringBillRequest $request)
    {
        try {
            $this->billService->createBill($request->validated());
            return redirect()->back()->with('success', 'Tagihan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan tagihan: ' . $e->getMessage());
        }
    }

    public function update(UpdateRecurringBillRequest $request, RecurringBill $bill)
    {
        try {
            $this->billService->updateBill($bill, $request->validated());
            return redirect()->back()->with('success', 'Tagihan berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah tagihan: ' . $e->getMessage());
        }
    }

    public function destroy(RecurringBill $bill)
    {
        try {
            $this->billService->deleteBill($bill);
            return redirect()->back()->with('success', 'Tagihan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus tagihan: ' . $e->getMessage());
        }
    }

    public function pay(Request $request, RecurringBill $recurring_bill)
    {
        $request->validate([
            'period' => 'required|string|max:7',
            'amount' => 'nullable|integer|min:1',
            'account_id' => 'required|exists:accounts,id',
        ]);

        try {
            $this->billService->payBill(
                $recurring_bill,
                $request->period,
                $request->amount,
                $request->account_id
            );
            return redirect()->back()->with('success', 'Tagihan ' . $recurring_bill->name . ' berhasil dibayar.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membayar tagihan: ' . $e->getMessage());
        }
    }

    public function updatePayment(Request $request, BillPayment $billPayment)
    {
        $request->validate([
            'amount' => 'nullable|integer|min:1',
            'account_id' => 'required|exists:accounts,id',
        ]);

        try {
            $this->billService->updatePayment(
                $billPayment,
                $request->amount,
                $request->account_id
            );
            return redirect()->back()->with('success', 'Pembayaran tagihan berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah pembayaran: ' . $e->getMessage());
        }
    }
}
