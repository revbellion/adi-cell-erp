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

    public function recordSale(array $items, array $saleInfo): string
    {
        return DB::transaction(function () use ($items, $saleInfo) {
            $receiptId = 'INV-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $total = 0;
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['qty']) {
                    throw new \InvalidArgumentException(
                        'Stok ' . $product->name . ' tidak mencukupi. Tersedia: ' . $product->stock
                    );
                }

                $total += $item['qty'] * $item['price'];
            }

            $income = Income::create([
                'account_id'  => $saleInfo['account_id'],
                'category'    => 'Penjualan',
                'amount'      => $total,
                'description' => 'Penjualan ' . now()->format('d/m/Y H:i'),
                'date'        => $saleInfo['date'] . ' ' . now()->format('H:i:s'),
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $item['qty'] * $item['price'];

                StockTransaction::create([
                    'product_id'  => $product->id,
                    'type'        => 'out',
                    'qty'         => $item['qty'],
                    'price'       => $item['price'],
                    'account_id'  => $saleInfo['account_id'],
                    'description' => $item['description'] ?? 'Penjualan ' . $product->name,
                    'date'        => $saleInfo['date'] . ' ' . now()->format('H:i:s'),
                    'income_id'   => $income->id,
                    'receipt_id'  => $receiptId,
                ]);

                $product->decrement('stock', $item['qty']);

                $income->description = str_replace(
                    ')', ', ' . $product->name . ' (' . $item['qty'] . ' ' . $product->unit . ')',
                    $income->description
                );
            }

            if (count($items) > 1) {
                $income->description .= ')';
            }
            $income->save();

            return $receiptId;
        });
    }

    public function deleteSale(string $receiptId): void
    {
        DB::transaction(function () use ($receiptId) {
            $transactions = StockTransaction::where('receipt_id', $receiptId)->where('type', 'out')->get();

            if ($transactions->isEmpty()) {
                return;
            }

            $incomeId = $transactions->first()->income_id;

            foreach ($transactions as $trx) {
                $trx->product->increment('stock', $trx->qty);
                $trx->delete();
            }

            if ($incomeId) {
                Income::where('id', $incomeId)->delete();
            }
        });
    }

    public function deleteStockIn(StockTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $transaction->product->decrement('stock', $transaction->qty);
            Expense::where('stock_transaction_id', $transaction->id)->delete();
            $transaction->delete();
        });
    }

    public function recordOpname(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $product = Product::findOrFail($item['id']);

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
        $receipts = StockTransaction::where('type', 'out')
            ->select('receipt_id', DB::raw('COUNT(*) as item_count'), DB::raw('SUM(qty * price) as total'))
            ->whereNotNull('receipt_id')
            ->groupBy('receipt_id')
            ->orderByRaw('MAX(created_at) DESC')
            ->paginate(15);

        $receipts->getCollection()->transform(function ($r) {
            $r->items = StockTransaction::with('product', 'income')
                ->where('receipt_id', $r->receipt_id)
                ->where('type', 'out')
                ->get();
            $r->income = $r->items->first()->income;
            return $r;
        });

        return $receipts;
    }

    public function getProductHistory(int $productId)
    {
        return StockTransaction::with('product', 'account')
            ->where('product_id', $productId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(30);
    }

    public function getReceipt(string $receiptId): ?object
    {
        $items = StockTransaction::with('product', 'income')
            ->where('receipt_id', $receiptId)
            ->where('type', 'out')
            ->get();

        if ($items->isEmpty()) return null;

        $income = $items->first()->income;
        $total = $items->sum(fn($i) => $i->qty * $i->price);

        return (object) [
            'receipt_id' => $receiptId,
            'date'       => $items->first()->date,
            'items'      => $items,
            'income'     => $income,
            'total'      => $total,
            'item_count' => $items->sum('qty'),
        ];
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
