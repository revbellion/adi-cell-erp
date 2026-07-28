<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Expense;
use App\Models\Income;
use App\Models\HppRecord;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function recordStockIn(array $data): StockTransaction
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);
            $discount = $data['discount'] ?? 0;
            $total = ($data['qty'] * $data['price']) - $discount;

            $transaction = StockTransaction::create([
                'product_id'    => $data['product_id'],
                'type'          => 'in',
                'qty'           => $data['qty'],
                'remaining_qty' => $data['qty'],
                'price'         => $data['price'],
                'account_id'    => $data['account_id'],
                'description'   => $data['description'] ?? null,
                'date'          => $data['date'] . ' ' . now()->format('H:i:s'),
                'expired_at'    => !empty($data['expired_at']) ? $data['expired_at'] . ' 23:59:59' : null,
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

    /**
     * Catat stok masuk secara bulk (banyak item sekaligus).
     */
    public function recordBulkStockIn(array $items, array $info): void
    {
        DB::transaction(function () use ($items, $info) {
            $products = Product::whereIn('id', array_column($items, 'product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $date = $info['date'] . ' ' . now()->format('H:i:s');

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw new \InvalidArgumentException(
                        'Produk ID ' . $item['product_id'] . ' tidak ditemukan.'
                    );
                }

                $subtotal = $item['qty'] * $item['price'];
                $discount = $item['discount'] ?? 0;
                $total = $subtotal - $discount;

                $transaction = StockTransaction::create([
                    'product_id'    => $product->id,
                    'type'          => 'in',
                    'qty'           => $item['qty'],
                    'remaining_qty' => $item['qty'],
                    'price'         => $item['price'],
                    'account_id'    => $info['account_id'],
                    'description'   => $item['description'] ?? null,
                    'date'          => $date,
                    'expired_at'    => !empty($item['expired_at']) ? $item['expired_at'] . ' 23:59:59' : null,
                ]);

                $product->increment('stock', $item['qty']);

                $desc = 'Pembelian ' . $product->name . ' (' . $item['qty'] . ' ' . $product->unit . ')';
                if ($discount > 0) {
                    $desc .= ' — diskon Rp ' . number_format($discount, 0, ',', '.');
                }

                Expense::create([
                    'account_id'          => $info['account_id'],
                    'category'            => 'Stok Masuk',
                    'amount'              => $total,
                    'description'         => $desc,
                    'date'                => $date,
                    'stock_transaction_id'=> $transaction->id,
                ]);
            }
        });
    }

    public function recordSale(array $items, array $saleInfo): string
    {
        return DB::transaction(function () use ($items, $saleInfo) {
            $receiptId = 'INV-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $discount = $saleInfo['discount'] ?? 0;

            $products = Product::whereIn('id', array_column($items, 'product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

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

            // Grand total setelah diskon
            $grandTotal = max($total - $discount, 0);

            $income = Income::create([
                'account_id'  => $saleInfo['account_id'],
                'category'    => 'Penjualan',
                'amount'      => $grandTotal,
                'discount'    => $discount,
                'description' => 'Penjualan ' . now()->format('d/m/Y H:i') . ' - ' . implode(', ', $itemNames),
                'date'        => $saleInfo['date'] . ' ' . now()->format('H:i:s'),
            ]);

            // Rasio diskon untuk distribusi proporsional ke HppRecord
            $ratio = $total > 0 ? $grandTotal / $total : 1;

            foreach ($transactions as $trx) {
                $trx['data']['income_id'] = $income->id;
                StockTransaction::create($trx['data']);
                $trx['product']->decrement('stock', $trx['data']['qty']);

                // --- FIFO HPP Calculation ---
                $product = $trx['product'];
                $qtyToSell = $trx['data']['qty'];
                $sellingPrice = $trx['data']['price'];
                $fifoBatches = [];
                $hppAmount = 0;

                // Ambil batch stok masuk (FIFO: tertua dulu)
                $batches = StockTransaction::where('product_id', $product->id)
                    ->whereIn('type', ['in', 'opname'])
                    ->where('remaining_qty', '>', 0)
                    ->orderBy('date')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToSell <= 0) break;

                    $consumed = min($batch->remaining_qty, $qtyToSell);
                    $hppAmount += $consumed * $batch->price;
                    $qtyToSell -= $consumed;

                    // Kurangi remaining_qty batch
                    $batch->decrement('remaining_qty', $consumed);

                    $fifoBatches[] = [
                        'stock_transaction_id' => $batch->id,
                        'qty'                  => $consumed,
                        'price'                => $batch->price,
                    ];
                }

                if ($qtyToSell > 0) {
                    \Log::warning('FIFO inconsistency', [
                        'product' => $product->name,
                        'qty_to_sell' => $trx['data']['qty'],
                        'unmatched_qty' => $qtyToSell,
                        'hpp_amount' => $hppAmount,
                        'stock' => $product->stock,
                    ]);
                }

                $sellingAmount = (int) round($trx['data']['qty'] * $sellingPrice * $ratio);

                HppRecord::create([
                    'date'                => $trx['data']['date'],
                    'product_category_id' => $product->category_id,
                    'product_id'          => $product->id,
                    'income_id'           => $income->id,
                    'receipt_id'          => $receiptId,
                    'qty'                 => $trx['data']['qty'],
                    'hpp_amount'          => $hppAmount,
                    'fifo_batches'        => $fifoBatches,
                    'selling_amount'      => $sellingAmount,
                    'profit_amount'       => $sellingAmount - $hppAmount,
                ]);
            }

            return $receiptId;
        });
    }

    public function deleteSale(string $receiptId): void
    {
        DB::transaction(function () use ($receiptId) {
            // Ambil HPP records untuk reverse FIFO
            $hppRecords = HppRecord::where('receipt_id', $receiptId)->get();

            // Reverse FIFO: kembalikan remaining_qty ke batch stok masuk
            foreach ($hppRecords as $hpp) {
                if ($hpp->fifo_batches) {
                    $batches = is_string($hpp->fifo_batches)
                        ? json_decode($hpp->fifo_batches, true)
                        : $hpp->fifo_batches;

                    foreach ($batches as $batch) {
                        StockTransaction::where('id', $batch['stock_transaction_id'])
                            ->increment('remaining_qty', $batch['qty']);
                    }
                }
            }

            $transactions = StockTransaction::with(['product' => function ($query) {
                $query->lockForUpdate();
            }])->where('receipt_id', $receiptId)->where('type', 'out')->get();

            if ($transactions->isEmpty()) {
                return;
            }

            $incomeId = $transactions->first()->income_id;

            // Hapus HPP records
            $hppRecords->each->delete();

            foreach ($transactions as $trx) {
                if ($trx->product) {
                    $trx->product->increment('stock', $trx->qty);
                } else {
                    throw new \DomainException('Produk terkait penjualan ini sudah tidak ada. Tidak bisa menghapus.');
                }
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
            $product = $transaction->product()->lockForUpdate()->first();
            if (! $product) {
                throw new \DomainException('Produk terkait stok ini sudah tidak ada. Tidak bisa menghapus.');
            }

            // Cek apakah batch ini masih utuh (belum terjual via FIFO)
            if ($transaction->remaining_qty !== null && $transaction->remaining_qty < $transaction->qty) {
                $consumed = $transaction->qty - $transaction->remaining_qty;
                throw new \DomainException(
                    'Stok ' . $product->name . ' batch ini sudah terjual ' . $consumed . ' ' . $product->unit .
                    '. Hapus penjualan terlebih dahulu sebelum menghapus stok masuk ini.'
                );
            }

            if ($product->stock < $transaction->qty) {
                throw new \DomainException(
                    'Stok ' . $product->name . ' tidak mencukupi untuk menghapus transaksi ini. ' .
                    'Stok saat ini: ' . $product->stock . ', stok yang akan dikurangi: ' . $transaction->qty
                );
            }

            $product->decrement('stock', $transaction->qty);
            Expense::where('stock_transaction_id', $transaction->id)->delete();
            $transaction->delete();
        });
    }

    public function recordOpname(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['id']);
                $oldStock = $product->stock;
                $newStock = $item['qty'];

                if ($newStock < 0) {
                    throw new \DomainException(
                        "Stok fisik {$product->name} tidak boleh negatif."
                    );
                }

                $price = $product->purchase_price;

                StockTransaction::create([
                    'product_id'    => $product->id,
                    'type'          => 'opname',
                    'qty'           => $newStock,
                    'remaining_qty' => $newStock,
                    'price'         => $price,
                    'account_id'    => null,
                    'description'   => $item['description'] ?? 'Stok opname',
                    'date'          => now()->format('Y-m-d H:i:s'),
                ]);

                $product->update([
                    'stock' => $newStock,
                ]);

                // Catat selisih sebagai expense/income
                $diff = $newStock - $oldStock;
                if ($diff != 0) {
                    $amount = abs($diff) * $price;
                    if ($diff < 0) {
                        // Stok turun → Expense
                        Expense::create([
                            'account_id'  => null,
                            'category'    => 'Stok Opname Minus',
                            'amount'      => $amount,
                            'description' => "Penyesuaian stok {$product->name} ({$oldStock} → {$newStock})",
                            'date'        => now()->format('Y-m-d H:i:s'),
                        ]);
                    } else {
                        // Stok naik → Income
                        Income::create([
                            'account_id'  => null,
                            'amount'      => $amount,
                            'category'    => 'Stok Opname Plus',
                            'description' => "Penyesuaian stok {$product->name} ({$oldStock} → {$newStock})",
                            'date'        => now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        });
    }

    public function getStockInHistory(array $filters = []): array
    {
        $query = StockTransaction::with('product', 'account')->where('type', 'in');

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        $aggregates = (clone $query)
            ->selectRaw('COALESCE(SUM(qty), 0) as total_qty, COALESCE(SUM(qty * price), 0) as total_value')
            ->first();

        $history = $query->latest()->paginate(20)->withQueryString();

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

    public function getReportData(array $filters = []): array
    {
        $query = Product::with('category')->active();

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['search'])) {
            $s = addcslashes($filters['search'], '%_');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('unit', 'like', "%{$s}%");
            });
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();

        // Semua produk aktif (tanpa filter) untuk statistik
        $allProducts = Product::active()->get();
        $totalStockValue = $allProducts->sum('stock_value');
        $lowStockProducts = $allProducts->filter(function ($p) {
            return $p->is_low_stock;
        });

        // Hitung dari hpp_records agar cocok (sudai FIFO)
        $totalSale = \App\Models\HppRecord::sum('selling_amount');
        $totalHpp = \App\Models\HppRecord::sum('hpp_amount');

        return compact('products', 'totalStockValue', 'lowStockProducts', 'totalSale', 'totalHpp');
    }
}
