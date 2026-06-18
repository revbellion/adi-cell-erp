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

            $products = Product::findMany(array_column($items, 'product_id'))->keyBy('id');

            $total = 0;
            $itemNames = [];
            $transactions = [];

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (!$product || $product->stock < $item['qty']) {
                    $name = $product ? $product->name : 'ID#' . $item['product_id'];
                    throw new \InvalidArgumentException(
                        'Stok ' . $name . ' tidak mencukupi. Tersedia: ' . ($product->stock ?? 0)
                    );
                }

                $subtotal = $item['qty'] * $item['price'];
                $total += $subtotal;
                $itemNames[] = $product->name . ' (' . $item['qty'] . ' ' . $product->unit . ')';

                $transactions[] = [
                    'product' => $product,
                    'data'    => [
                        'product_id'  => $product->id,
                        'type'        => 'out',
                        'qty'         => $item['qty'],
                        'price'       => $item['price'],
                        'account_id'  => $saleInfo['account_id'],
                        'description' => $item['description'] ?? 'Penjualan ' . $product->name,
                        'date'        => $saleInfo['date'] . ' ' . now()->format('H:i:s'),
                        'receipt_id'  => $receiptId,
                    ],
                ];
            }

            $income = Income::create([
                'account_id'  => $saleInfo['account_id'],
                'category'    => 'Penjualan',
                'amount'      => $total,
                'description' => 'Penjualan ' . now()->format('d/m/Y H:i') . ' - ' . implode(', ', $itemNames),
                'date'        => $saleInfo['date'] . ' ' . now()->format('H:i:s'),
            ]);

            foreach ($transactions as $trx) {
                $trx['data']['income_id'] = $income->id;
                StockTransaction::create($trx['data']);
                $trx['product']->decrement('stock', $trx['data']['qty']);
            }

            return $receiptId;
        });
    }

    public function deleteSale(string $receiptId): void
    {
        DB::transaction(function () use ($receiptId) {
            $transactions = StockTransaction::with('product')->where('receipt_id', $receiptId)->where('type', 'out')->get();

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

    public function getStockInHistory(): array
    {
        $query = StockTransaction::with('product', 'account')->where('type', 'in');

        $aggregates = (clone $query)
            ->selectRaw('COALESCE(SUM(qty), 0) as total_qty, COALESCE(SUM(qty * price), 0) as total_value')
            ->first();

        $history = $query->latest()->paginate(20);

        return [
            'history' => $history,
            'totalQty' => (int) $aggregates->total_qty,
            'totalValue' => (int) $aggregates->total_value,
        ];
    }

    public function getSalesHistory(): array
    {
        $totalSales = StockTransaction::where('type', 'out')
            ->whereNotNull('receipt_id')
            ->sum(DB::raw('qty * price'));

        $receipts = StockTransaction::where('type', 'out')
            ->select('receipt_id', DB::raw('COUNT(*) as item_count'), DB::raw('SUM(qty * price) as total'))
            ->whereNotNull('receipt_id')
            ->groupBy('receipt_id')
            ->orderByRaw('MAX(created_at) DESC')
            ->paginate(15);

        $allItems = StockTransaction::with('product', 'income')
            ->whereIn('receipt_id', $receipts->pluck('receipt_id'))
            ->where('type', 'out')
            ->get()
            ->groupBy('receipt_id');

        $receipts->getCollection()->transform(function ($r) use ($allItems) {
            $r->items = $allItems->get($r->receipt_id, collect());
            $r->income = $r->items->first()->income ?? null;
            return $r;
        });

        return compact('receipts', 'totalSales');
    }

    public function getProductHistory(int $productId): array
    {
        $query = StockTransaction::with('product', 'account')
            ->where('product_id', $productId);

        $stats = (clone $query)
            ->selectRaw("COALESCE(SUM(CASE WHEN type='in' THEN qty ELSE 0 END), 0) as total_qty_in")
            ->selectRaw("COALESCE(SUM(CASE WHEN type='out' THEN qty ELSE 0 END), 0) as total_qty_out")
            ->selectRaw('COALESCE(SUM(qty * price), 0) as total_value')
            ->first();

        $transactions = $query->orderByDesc('date')->orderByDesc('id')->paginate(30);

        return [
            'transactions' => $transactions,
            'totalQtyIn' => (int) $stats->total_qty_in,
            'totalQtyOut' => (int) $stats->total_qty_out,
            'totalValue' => (int) $stats->total_value,
        ];
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

    public function getReportData(): array
    {
        $products = Product::activeWithCategory()->get();
        $totalStockValue = $products->sum('stock_value');
        $lowStockProducts = $products->filter(function ($p) {
            return $p->is_low_stock;
        });

        $totalPurchase = StockTransaction::where('type', 'in')->sum(DB::raw('qty * price'));
        $totalSale = StockTransaction::where('type', 'out')->sum(DB::raw('qty * price'));

        return compact('products', 'totalStockValue', 'lowStockProducts', 'totalPurchase', 'totalSale');
    }
}
