<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount('products')->latest()->get();
        return view('product-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name',
        ]);

        ProductCategory::create($validated);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name,' . $productCategory->id,
        ]);

        $productCategory->update($validated);

        return redirect()->back()->with('success', 'Kategori berhasil diubah.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($productCategory->products()->exists()) {
            return redirect()->back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki barang.');
        }

        $productCategory->delete();

        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }
}
