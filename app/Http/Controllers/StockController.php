<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockTransaction;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $result = $this->stockService->getStockInHistory();
        return view('stock.in', array_merge(compact('products', 'accounts'), $result));
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
        $accounts = Account::active()->where('type', '!=', 'ppob')->get();
        $categories = ProductCategory::orderBy('name')->get();
        $result = $this->stockService->getSalesHistory();
        return view('stock.sales', array_merge(compact('products', 'accounts', 'categories'), [
            'history' => $result['receipts'],
            'totalSales' => $result['totalSales'],
        ]));
    }

    public function storeSale(Request $request)
    {
        $validated = $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'      => 'required|integer|min:1',
            'items.*.price'    => 'required|integer|min:0',
            'items.*.desc'     => 'nullable|string|max:255',
            'account_id'       => 'required|exists:accounts,id',
            'date'             => 'required|date',
        ]);

        try {
            $receiptId = $this->stockService->recordSale($validated['items'], [
                'account_id' => $validated['account_id'],
                'date'       => $validated['date'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Penjualan berhasil dicatat. No: ' . $receiptId)->with('receipt_id', $receiptId);
    }

    public function destroy(string $receiptId)
    {
        try {
            $this->stockService->deleteSale($receiptId);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus penjualan: ' . $e->getMessage());
        }
        return redirect()->back()->with('success', 'Penjualan berhasil dihapus.');
    }

    public function destroyStockIn(StockTransaction $stockTransaction)
    {
        try {
            $this->stockService->deleteStockIn($stockTransaction);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus stok masuk: ' . $e->getMessage());
        }
        return redirect()->back()->with('success', 'Transaksi stok masuk berhasil dihapus.');
    }

    public function receipt(string $receiptId)
    {
        $receipt = $this->stockService->getReceipt($receiptId);

        if (!$receipt) {
            return redirect()->route('stock.sales')->with('error', 'Nota tidak ditemukan.');
        }

        return view('stock.receipt', compact('receipt'));
    }

    public function receiptPdf(string $receiptId)
    {
        $receipt = $this->stockService->getReceipt($receiptId);

        if (!$receipt) {
            return redirect()->route('stock.sales')->with('error', 'Nota tidak ditemukan.');
        }

        $pdf = Pdf::loadView('stock.receipt-pdf', compact('receipt'));
        $pdf->setPaper([0, 0, 226, 500], 'portrait');

        return $pdf->stream('resi-' . $receiptId . '.pdf');
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
