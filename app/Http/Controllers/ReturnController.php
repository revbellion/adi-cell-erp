<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReturnPurchaseRequest;
use App\Http\Requests\StoreReturnRequest;
use App\Models\Account;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(
        protected ReturnService $returnService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'type' => $request->input('type'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'search' => $request->input('search'),
        ];

        $data = $this->returnService->getAll($filters);

        return view('returns.index', array_merge($data, [
            'filters' => $filters,
            'accounts' => Account::active()->visible()->get(),
            'products' => Product::active()->orderBy('name')->get(),
        ]));
    }

    /**
     * Store sales return.
     */
    public function store(StoreReturnRequest $request)
    {
        try {
            $this->returnService->recordReturnSales($request->validated());
            return redirect()->back()->with('success', 'Retur penjualan berhasil dicatat.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat retur: ' . $e->getMessage());
        }
    }

    /**
     * Store purchase return.
     */
    public function storePurchase(StoreReturnPurchaseRequest $request)
    {
        try {
            $this->returnService->recordReturnPurchase($request->validated());
            return redirect()->back()->with('success', 'Retur pembelian berhasil dicatat.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat retur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: get receipt products for return form.
     */
    public function getReceipt(Request $request)
    {
        $receiptId = $request->input('receipt_id');

        if (empty($receiptId)) {
            return response()->json(['error' => 'No nota tidak boleh kosong.'], 400);
        }

        try {
            $data = $this->returnService->getReceiptProducts($receiptId);
            return response()->json($data);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
