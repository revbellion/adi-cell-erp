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

    public function report()
    {
        $data = $this->stockService->getReportData();
        return view('stock.report', $data);
    }
}
