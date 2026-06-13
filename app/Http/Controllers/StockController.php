<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function stockIn()
    {
        $products = Product::with('category')->active()->orderBy('name')->get();
        $accounts = Account::active()->get();
        $history = $this->stockService->getStockInHistory();
        return view('stock.in', compact('products', 'accounts', 'history'));
    }

    public function storeIn(Request $request)
    {
        $validated = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'qty'         => 'required|integer|min:1',
            'price'       => 'required|integer|min:0',
            'account_id'  => 'required|exists:accounts,id',
            'date'        => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        $this->stockService->recordStockIn($validated);

        return redirect()->back()->with('success', 'Stok masuk berhasil dicatat.');
    }

    public function sales()
    {
        $products = Product::with('category')->active()->orderBy('name')->get();
        $accounts = Account::active()->get();
        $history = $this->stockService->getSalesHistory();
        return view('stock.sales', compact('products', 'accounts', 'history'));
    }

    public function storeSale(Request $request)
    {
        $validated = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'qty'         => 'required|integer|min:1',
            'price'       => 'required|integer|min:0',
            'account_id'  => 'required|exists:accounts,id',
            'date'        => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $this->stockService->recordSale($validated);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Penjualan berhasil dicatat.');
    }

    public function destroy(StockTransaction $stockTransaction)
    {
        if ($stockTransaction->type === 'in') {
            $this->stockService->deleteStockIn($stockTransaction);
            return redirect()->back()->with('success', 'Transaksi stok masuk berhasil dihapus.');
        }

        $this->stockService->deleteSale($stockTransaction);
        return redirect()->back()->with('success', 'Penjualan berhasil dihapus.');
    }

    public function opname()
    {
        $products = Product::with('category')->active()->orderBy('name')->get();
        return view('stock.opname', compact('products'));
    }

    public function storeOpname(Request $request)
    {
        $validated = $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|exists:products,id',
            'items.*.qty'      => 'required|integer|min:0',
            'items.*.price'    => 'nullable|integer|min:0',
            'items.*.desc'     => 'nullable|string|max:255',
        ]);

        $items = collect($validated['items'])->filter(fn($i) => $i['qty'] > 0)->values()->toArray();

        if (empty($items)) {
            return redirect()->back()->with('error', 'Tidak ada barang dengan stok > 0.');
        }

        $this->stockService->recordOpname($items);

        return redirect()->back()->with('success', 'Stok opname berhasil disimpan.');
    }

    public function report()
    {
        $data = $this->stockService->getReportData();
        return view('stock.report', $data);
    }
}
