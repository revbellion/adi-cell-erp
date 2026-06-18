<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function index()
    {
        $products = Product::with('category')->latest()->paginate(50);
        $categories = ProductCategory::orderBy('name')->get();
        $totalProducts = Product::count();
        $totalStockValue = Product::sum(DB::raw('stock * purchase_price'));
        return view('products.index', compact('products', 'categories', 'totalProducts', 'totalStockValue'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:product_categories,id',
            'name'           => 'required|string|max:100|unique:products,name',
            'purchase_price' => 'required|integer|min:0',
            'selling_price'  => 'required|integer|min:0',
            'stock'          => 'required|integer|min:0',
            'stock_min'      => 'required|integer|min:0',
            'unit'           => 'required|string|max:20',
        ]);

        $validated['is_active'] = true;

        Product::create($validated);

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:product_categories,id',
            'name'           => 'required|string|max:100|unique:products,name,' . $product->id,
            'purchase_price' => 'required|integer|min:0',
            'selling_price'  => 'required|integer|min:0',
            'stock'          => 'required|integer|min:0',
            'stock_min'      => 'required|integer|min:0',
            'unit'           => 'required|string|max:20',
            'is_active'      => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->back()->with('success', 'Barang berhasil diubah.');
    }

    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Barang berhasil dinonaktifkan.');
    }

    public function history(Product $product)
    {
        $result = $this->stockService->getProductHistory($product->id);
        return view('products.history', array_merge(compact('product'), $result));
    }
}
