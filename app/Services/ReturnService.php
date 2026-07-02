<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\HppRecord;
use App\Models\Income;
use App\Models\Product;
use App\Models\StockReturn;
use App\Models\StockTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    /**
     * Retur Penjualan — customer mengembalikan barang.
     */
    public function recordReturnSales(array $data): StockReturn
    {
        return DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $receiptId = $data['receipt_id'];
            $qtyReturn = (int) $data['qty'];
            $returnDate = $data['return_date'] . ' ' . now()->format('H:i:s');

            // Cari HppRecord untuk receipt + product ini
            $hppRecords = HppRecord::where('receipt_id', $receiptId)
                ->where('product_id', $product->id)
                ->orderBy('date')
                ->get();

            if ($hppRecords->isEmpty()) {
                throw new \DomainException(
                    "Produk {$product->name} tidak ditemukan di nota {$receiptId}."
                );
            }

            $totalHppQty = $hppRecords->sum('qty');
            if ($qtyReturn > $totalHppQty) {
                throw new \DomainException(
                    "Qty retur ({$qtyReturn}) melebihi qty penjualan ({$totalHppQty}) untuk produk {$product->name}."
                );
            }

            // Reverse FIFO: kembalikan remaining_qty ke inbound batch
            $remainingToReturn = $qtyReturn;
            $totalHppAmount = 0;

            foreach ($hppRecords as $hpp) {
                if ($remainingToReturn <= 0) break;

                $batches = is_string($hpp->fifo_batches)
                    ? json_decode($hpp->fifo_batches, true)
                    : ($hpp->fifo_batches ?? []);

                foreach ($batches as $batch) {
                    if ($remainingToReturn <= 0) break;

                    $returnFromBatch = min($batch['qty'], $remainingToReturn);
                    $totalHppAmount += $returnFromBatch * $batch['price'];

                    // Kembalikan remaining_qty ke inbound StockTransaction
                    StockTransaction::where('id', $batch['stock_transaction_id'])
                        ->increment('remaining_qty', $returnFromBatch);

                    $remainingToReturn -= $returnFromBatch;
                }

                // Kurangi qty HppRecord atau hapus jika 0
                if ($qtyReturn >= $hpp->qty) {
                    // Hapus HppRecord jika semua qty diretur
                    $hpp->delete();
                }
                // Note: untuk retur partial, kita kurangi qty HPP
                // Tapi karena HppRecord menyimpan fifo_batches, lebih bersih
                // update qty dan fifo_batches untuk sisa yang tidak diretur
            }

            // Jika ada HPP records yang masih tersisa setelah retur, update qty-nya
            $remainingHppRecords = HppRecord::where('receipt_id', $receiptId)
                ->where('product_id', $product->id)
                ->get();

            if ($remainingHppRecords->isNotEmpty()) {
                $totalRemainingQty = $remainingHppRecords->sum('qty');
                // Hapus batch yang sudah diretur dari fifo_batches
                foreach ($remainingHppRecords as $hpp) {
                    $batches = is_string($hpp->fifo_batches)
                        ? json_decode($hpp->fifo_batches, true)
                        : ($hpp->fifo_batches ?? []);

                    $filteredBatches = [];
                    $remainingQtyInRecord = $hpp->qty;

                    foreach ($batches as $batch) {
                        if ($remainingQtyInRecord <= 0) break;
                        $keepQty = min($batch['qty'], $remainingQtyInRecord);
                        $filteredBatches[] = [
                            'stock_transaction_id' => $batch['stock_transaction_id'],
                            'qty' => $keepQty,
                            'price' => $batch['price'],
                        ];
                        $remainingQtyInRecord -= $keepQty;
                    }

                    $hpp->update([
                        'fifo_batches' => $filteredBatches,
                    ]);
                }
            }

            // Tambah stok produk
            $product->increment('stock', $qtyReturn);

            // Hitung harga rata-rata untuk total retur
            $avgPrice = $qtyReturn > 0 ? (int) ($totalHppAmount / $qtyReturn) : 0;

            // Catat retur
            $stockReturn = StockReturn::create([
                'type'        => 'sales',
                'receipt_id'  => $receiptId,
                'product_id'  => $product->id,
                'qty'         => $qtyReturn,
                'price'       => $avgPrice,
                'total'       => $totalHppAmount,
                'reason'      => $data['reason'] ?? null,
                'return_date' => $data['return_date'],
            ]);

            // Buat Expense untuk retur jika ada biaya (refund)
            if (!empty($data['refund_amount']) && (int) $data['refund_amount'] > 0) {
                Expense::create([
                    'account_id'  => $data['account_id'] ?? null,
                    'category'    => 'Retur Penjualan',
                    'amount'      => (int) $data['refund_amount'],
                    'description' => "Refund retur {$product->name} ({$qtyReturn} {$product->unit}) - {$receiptId}",
                    'date'        => $returnDate,
                ]);
            }

            return $stockReturn;
        });
    }

    /**
     * Retur Pembelian — mengembalikan barang ke supplier.
     */
    public function recordReturnPurchase(array $data): StockReturn
    {
        return DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $qtyReturn = (int) $data['qty'];

            // Cari inbound batch tertua yang masih memiliki remaining_qty
            $inboundBatches = StockTransaction::where('product_id', $product->id)
                ->whereIn('type', ['in', 'opname'])
                ->where('remaining_qty', '>', 0)
                ->orderBy('date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $totalAvailable = $inboundBatches->sum('remaining_qty');
            if ($qtyReturn > $totalAvailable) {
                throw new \DomainException(
                    "Stok tersedia untuk diretur: {$totalAvailable} {$product->unit}. Tidak cukup untuk retur {$qtyReturn}."
                );
            }

            if ($qtyReturn > $product->stock) {
                throw new \DomainException(
                    "Stok {$product->name} saat ini {$product->stock} {$product->unit}. Tidak cukup untuk retur {$qtyReturn}."
                );
            }

            // Kurangi remaining_qty dari batch inbound (FIFO: dari yang tertua)
            $remainingToReturn = $qtyReturn;
            $totalPrice = 0;

            foreach ($inboundBatches as $batch) {
                if ($remainingToReturn <= 0) break;

                $returnFromBatch = min($batch->remaining_qty, $remainingToReturn);
                $batch->decrement('remaining_qty', $returnFromBatch);
                $totalPrice += $returnFromBatch * $batch->price;
                $remainingToReturn -= $returnFromBatch;
            }

            // Kurangi stok produk
            $product->decrement('stock', $qtyReturn);

            $avgPrice = $qtyReturn > 0 ? (int) ($totalPrice / $qtyReturn) : 0;

            // Catat retur
            $stockReturn = StockReturn::create([
                'type'        => 'purchase',
                'receipt_id'  => null,
                'product_id'  => $product->id,
                'qty'         => $qtyReturn,
                'price'       => $avgPrice,
                'total'       => $totalPrice,
                'reason'      => $data['reason'] ?? null,
                'return_date' => $data['return_date'],
            ]);

            // Buat Income untuk retur pembelian (uang kembali dari supplier)
            if (!empty($data['refund_amount']) && (int) $data['refund_amount'] > 0) {
                Income::create([
                    'account_id'  => $data['account_id'] ?? null,
                    'amount'      => (int) $data['refund_amount'],
                    'category'    => 'Retur Pembelian',
                    'description' => "Refund retur beli {$product->name} ({$qtyReturn} {$product->unit})",
                    'date'        => $data['return_date'] . ' ' . now()->format('H:i:s'),
                ]);
            }

            return $stockReturn;
        });
    }

    /**
     * Ambil daftar retur.
     */
    public function getAll(array $filters = []): array
    {
        $query = StockReturn::with('product');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('return_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('return_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $s = addcslashes($filters['search'], '%_');
            $query->where(function ($q) use ($s) {
                $q->where('receipt_id', 'like', "%{$s}%")
                  ->orWhereHas('product', function ($pq) use ($s) {
                      $pq->where('name', 'like', "%{$s}%");
                  });
            });
        }

        $totalQty = (clone $query)->sum('qty');
        $totalValue = (clone $query)->sum('total');
        $returns = $query->latest()->paginate(20);

        return [
            'returns' => $returns,
            'totalQty' => $totalQty,
            'totalValue' => $totalValue,
        ];
    }

    /**
     * Cari data receipt untuk form retur jual.
     */
    public function getReceiptProducts(string $receiptId): array
    {
        $transactions = StockTransaction::with('product')
            ->where('receipt_id', $receiptId)
            ->where('type', 'out')
            ->get();

        if ($transactions->isEmpty()) {
            throw new \DomainException("Nota {$receiptId} tidak ditemukan.");
        }

        $products = [];
        foreach ($transactions as $trx) {
            if (!$trx->product) continue;

            // Cek sudah diretur berapa
            $returnedQty = StockReturn::where('type', 'sales')
                ->where('receipt_id', $receiptId)
                ->where('product_id', $trx->product_id)
                ->sum('qty');

            $availableForReturn = $trx->qty - $returnedQty;

            if ($availableForReturn > 0) {
                $products[] = [
                    'product_id' => $trx->product->id,
                    'name' => $trx->product->name,
                    'unit' => $trx->product->unit,
                    'sold_qty' => $trx->qty,
                    'returned_qty' => $returnedQty,
                    'available' => $availableForReturn,
                    'price' => $trx->price,
                ];
            }
        }

        $income = $transactions->first()->income;

        return [
            'receipt_id' => $receiptId,
            'date' => $transactions->first()->date,
            'products' => $products,
            'income' => $income,
        ];
    }
}
