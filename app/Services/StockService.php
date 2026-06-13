<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function recordStockIn(array $data): StockTransaction
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);
            $total = $data['qty'] * $data['price'];

            $transaction = StockTransaction::create([
                'product_id'  => $data['product_id'],
                'type'        => 'in',
                'qty'         => $data['qty'],
                'price'       => $data['price'],
                'account_id'  => $data['account_id'],
                'description' => $data['description'] ?? null,
                'date'        => $data['date'] . ' ' . now()->format('H:i:s'),
            ]);

            $product->increment('stock', $data['qty']);

            Expense::create([
                'account_id'          => $data['account_id'],
                'category'            => 'Stok Masuk',
                'amount'              => $total,
                'description'         => 'Pembelian ' . $product->name . ' (' . $data['qty'] . ' ' . $product->unit . ')',
                'date'                => $data['date'] . ' ' . now()->format('H:i:s'),
                'stock_transaction_id'=> $transaction->id,
            ]);

            return $transaction;
        });
    }

    public function recordSale(array $data): StockTransaction
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);
            $total = $data['qty'] * $data['price'];

            if ($product->stock < $data['qty']) {
                throw new \InvalidArgumentException('Stok ' . $product->name . ' tidak mencukupi. Tersedia: ' . $product->stock);
            }

            $transaction = StockTransaction::create([
                'product_id'  => $data['product_id'],
                'type'        => 'out',
                'qty'         => $data['qty'],
                'price'       => $data['price'],
                'account_id'  => $data['account_id'],
                'description' => $data['description'] ?? null,
                'date'        => $data['date'] . ' ' . now()->format('H:i:s'),
            ]);

            $product->decrement('stock', $data['qty']);

            Income::create([
                'account_id'          => $data['account_id'],
                'category'            => 'Penjualan',
                'amount'              => $total,
                'description'         => 'Penjualan ' . $product->name . ' (' . $data['qty'] . ' ' . $product->unit . ')',
                'date'                => $data['date'] . ' ' . now()->format('H:i:s'),
                'stock_transaction_id'=> $transaction->id,
            ]);

            return $transaction;
        });
    }

    public function deleteStockIn(StockTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $product = $transaction->product;
            $product->decrement('stock', $transaction->qty);

            Expense::where('stock_transaction_id', $transaction->id)->delete();

            $transaction->delete();
        });
    }

    public function deleteSale(StockTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $product = $transaction->product;
            $product->increment('stock', $transaction->qty);

            Income::where('stock_transaction_id', $transaction->id)->delete();

            $transaction->delete();
        });
    }

    public function recordOpname(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                StockTransaction::create([
                    'product_id'  => $product->id,
                    'type'        => 'opname',
                    'qty'         => $item['qty'],
                    'price'       => $item['price'] ?? 0,
                    'account_id'  => null,
                    'description' => $item['description'] ?? 'Stok opname',
                    'date'        => now()->format('Y-m-d H:i:s'),
                ]);

                $product->update([
                    'stock'          => $item['qty'],
                    'purchase_price' => $item['price'] ?? $product->purchase_price,
                ]);
            }
        });
    }

    public function getStockInHistory()
    {
        return StockTransaction::with('product', 'account')
            ->where('type', 'in')
            ->latest()
            ->paginate(20);
    }

    public function getSalesHistory()
    {
        return StockTransaction::with('product', 'account')
            ->where('type', 'out')
            ->latest()
            ->paginate(20);
    }

    public function getReportData()
    {
        $products = Product::with('category')->active()->get();
        $totalStockValue = $products->sum('stock_value');
        $lowStockProducts = $products->filter(function ($p) {
            return $p->is_low_stock;
        });

        $totalPurchase = StockTransaction::where('type', 'in')->sum(DB::raw('qty * price'));
        $totalSale = StockTransaction::where('type', 'out')->sum(DB::raw('qty * price'));

        return compact('products', 'totalStockValue', 'lowStockProducts', 'totalPurchase', 'totalSale');
    }
}
