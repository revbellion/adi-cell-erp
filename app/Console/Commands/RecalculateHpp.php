<?php

namespace App\Console\Commands;

use App\Models\HppRecord;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateHpp extends Command
{
    protected $signature = 'hpp:recalculate';
    protected $description = 'Hapus & hitung ulang semua HPP records berdasarkan FIFO';

    public function handle(): int
    {
        if (!$this->confirm('Ini akan menghapus semua HPP records dan menghitung ulang. Lanjut?')) {
            return 0;
        }

        // 1. Reset remaining_qty ke qty penuh
        DB::statement("UPDATE stock_transactions SET remaining_qty = qty WHERE type IN ('opname', 'in')");

        $this->info('remaining_qty direset.');

        // 2. Ambil semua sale receipt (urut dari tertua)
        $receipts = StockTransaction::where('type', 'out')
            ->whereNotNull('receipt_id')
            ->select('receipt_id', 'date')
            ->distinct()
            ->orderBy('date')
            ->orderBy('receipt_id')
            ->pluck('receipt_id');

        $this->info('Ditemukan ' . $receipts->count() . ' receipt.');

        $bar = $this->output->createProgressBar($receipts->count());
        $bar->start();

        $errors = [];

        foreach ($receipts as $receiptId) {
            // 3. Hapus HppRecord lama untuk receipt ini
            HppRecord::where('receipt_id', $receiptId)->delete();

            // 4. Ambil transaksi sale untuk receipt ini
            $outTransactions = StockTransaction::where('receipt_id', $receiptId)
                ->where('type', 'out')
                ->get();

            if ($outTransactions->isEmpty()) {
                $bar->advance();
                continue;
            }

            // Ambil income_id dari transaksi pertama
            $incomeId = $outTransactions->first()->income_id;

            foreach ($outTransactions as $trx) {
                $product = Product::find($trx->product_id);
                if (!$product) continue;

                $qtyToSell = $trx->qty;
                $sellingPrice = $trx->price;
                $fifoBatches = [];
                $hppAmount = 0;

                // FIFO FIFO: ambil batch stok masuk (opname + in)
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

                    $batch->decrement('remaining_qty', $consumed);

                    $fifoBatches[] = [
                        'stock_transaction_id' => $batch->id,
                        'qty'                  => $consumed,
                        'price'                => $batch->price,
                    ];
                }

                if ($qtyToSell > 0) {
                    $errors[] = "Receipt {$receiptId}: product {$product->name} kelebihan {$qtyToSell} qty tanpa HPP";
                }

                $date = $trx->date ?? $outTransactions->first()->date ?? now();

                // Cek diskon dari income
                $discountRatio = 1;
                if ($incomeId) {
                    $income = \App\Models\Income::find($incomeId);
                    if ($income && $income->discount > 0) {
                        $totalSale = $outTransactions->sum(fn($t) => $t->qty * $t->price);
                        $discountRatio = $totalSale > 0
                            ? ($totalSale - $income->discount) / $totalSale
                            : 1;
                    }
                }

                $sellingAmount = (int) round($trx->qty * $sellingPrice * $discountRatio);

                HppRecord::create([
                    'date'                => $date,
                    'product_category_id' => $product->category_id,
                    'product_id'          => $product->id,
                    'income_id'           => $incomeId,
                    'receipt_id'          => $receiptId,
                    'qty'                 => $trx->qty,
                    'hpp_amount'          => $hppAmount,
                    'fifo_batches'        => $fifoBatches,
                    'selling_amount'      => $sellingAmount,
                    'profit_amount'       => $sellingAmount - $hppAmount,
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Selesai! HPP dihitung ulang untuk ' . $receipts->count() . ' receipt.');

        if (!empty($errors)) {
            $this->warn('Warning:');
            foreach ($errors as $e) {
                $this->warn("  - {$e}");
            }
        }

        return 0;
    }
}
