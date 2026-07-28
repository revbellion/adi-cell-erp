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

    public function stockIn(Request $request)
    {
        $products = Product::with('category')->active()->orderBy('name')->get();
        $accounts = Account::active()->visible()->get();
        $categories = ProductCategory::orderBy('name')->get();
        $filters = $request->only(['date_from', 'date_to']);
        $result = $this->stockService->getStockInHistory($filters);
        return view('stock.in', array_merge(compact('products', 'accounts', 'categories', 'filters'), $result));
    }

    public function storeIn(Request $request)
    {
        $validated = $request->validate([
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.price'          => 'required|integer|min:0',
            'items.*.description'    => 'nullable|string|max:255',
            'items.*.discount'       => 'nullable|integer|min:0',
            'items.*.expired_at'     => 'nullable|date',
            'account_id'             => 'required|exists:accounts,id',
            'date'                   => 'required|date',
        ]);

        try {
            $this->stockService->recordBulkStockIn($validated['items'], [
                'account_id' => $validated['account_id'],
                'date'       => $validated['date'],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat stok masuk: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Stok masuk berhasil dicatat.');
    }

    /**
     * AJAX: Tambah barang baru dari halaman stok masuk.
     */
    public function quickAddProduct(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:product_categories,id',
            'name'           => 'required|string|max:100|unique:products,name',
            'purchase_price' => 'required|integer|min:0',
            'selling_price'  => 'required|integer|min:0',
            'unit'           => 'required|string|max:20',
        ]);

        $validated['stock'] = 0;
        $validated['stock_min'] = 0;
        $validated['is_active'] = true;

        $product = Product::create($validated);
        $product->load('category');

        return response()->json([
            'id'       => $product->id,
            'name'     => $product->name,
            'price'    => $product->purchase_price,
            'unit'     => $product->unit,
            'stock'    => $product->stock,
            'category' => $product->category->name ?? '',
        ]);
    }

    public function sales()
    {
        $products = Product::with('category')->active()->orderBy('name')->get();
        $accounts = Account::active()->visible()->where('type', '!=', 'ppob')->get();
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
            'items.*.description' => 'nullable|string|max:255',
            'account_id'       => 'required|exists:accounts,id',
            'date'             => 'required|date',
            'discount'         => 'nullable|integer|min:0',
            'paid_amount'      => 'nullable|integer|min:0',
            'change_amount'    => 'nullable|integer|min:0',
        ]);

        try {
            $receiptId = $this->stockService->recordSale($validated['items'], [
                'account_id'    => $validated['account_id'],
                'date'          => $validated['date'],
                'discount'      => $validated['discount'] ?? 0,
                'paid_amount'   => $validated['paid_amount'] ?? 0,
                'change_amount' => $validated['change_amount'] ?? 0,
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
        $categories = \App\Models\ProductCategory::orderBy('name')->get();
        return view('stock.opname', compact('products', 'categories'));
    }

    public function storeOpname(Request $request)
    {
        $validated = $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|exists:products,id',
            'items.*.qty'      => 'required|integer|min:0',
            'items.*.description' => 'nullable|string|max:255',
        ]);

        $items = collect($validated['items'])->filter(fn($i) => isset($i['qty']) && $i['qty'] >= 0)->values()->toArray();

        if (empty($items)) {
            return redirect()->back()->with('error', 'Tidak ada barang yang dipilih.');
        }

        try {
            $this->stockService->recordOpname($items);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan stok opname: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Stok opname berhasil disimpan.');
    }

    public function report(Request $request)
    {
        $categories = \App\Models\ProductCategory::orderBy('name')->get();
        $filters = $request->only(['search', 'category_id']);
        $data = $this->stockService->getReportData($filters);
        return view('stock.report', array_merge($data, compact('categories', 'filters')));
    }
}
