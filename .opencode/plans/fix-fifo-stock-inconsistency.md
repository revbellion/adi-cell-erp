# Plan: Fix FIFO Stock Inconsistency

## Context
Error terjadi saat penjualan: "Stok Lanyard Gambar tidak mencukupi (FIFO). Tersedia: 0"

Analisis StockService.php menunjukkan ada 2 validasi terpisah:
1. **Line 124-129:** Cek `$product->stock` (kolom di tabel products)
2. **Line 132-141:** Cek `SUM(remaining_qty)` dari stock_transactions (FIFO batches)

Error muncul dari validasi kedua, yang berarti:
- `$product->stock` > 0 (lulus validasi 1)
- `SUM(remaining_qty)` = 0 (gagal validasi 2)

Ini adalah **inkonsistensi data** antara kolom `stock` di tabel products dengan `remaining_qty` di stock_transactions.

## Root Cause
Kemungkinan besar disebabkan oleh:
1. **Data existing sebelum fitur FIFO ditambahkan** - stock_transactions lama tidak punya `remaining_qty` yang terisi
2. **Transaksi dihapus manual** di database tanpa restore remaining_qty
3. **Bug di deleteSale()** - method line 235-281 restore remaining_qty, tapi mungkin ada edge case yang tidak terhandle

## Solusi yang Dipilih: Opsi A - Hapus Validasi FIFO Terpisah

**Alasan:**
- Validasi `$product->stock` sudah cukup untuk memastikan stok tersedia
- Validasi FIFO tambahan hanya menyebabkan error false positive
- FIFO calculation di line 182-228 sudah handle kasus remaining_qty = 0 dengan baik
- Lebih simple dan tidak perlu sinkronisasi data

## Implementasi

### Step 1: Fix StockService.php

**File:** `app/Services/StockService.php`

**Perubahan di method `recordSale()` (line 106-233):**

Hapus validasi FIFO terpisah (line 131-141):
```php
// HAPUS CODE INI:
// Validasi FIFO: pastikan remaining_qty cukup
$totalRemaining = StockTransaction::where('product_id', $product->id)
    ->where('type', 'in')
    ->where('remaining_qty', '>', 0)
    ->sum('remaining_qty');

if ($totalRemaining < $item['qty']) {
    throw new \InvalidArgumentException(
        'Stok ' . $product->name . ' tidak mencukupi (FIFO). Tersedia: ' . $totalRemaining
    );
}
```

**Pertahankan:**
- Validasi `$product->stock` (line 124-129) - sudah cukup
- FIFO calculation logic (line 182-228) - sudah handle remaining_qty = 0

### Step 2: Buat Artisan Command untuk Sync Data (Optional)

**File baru:** `app/Console/Commands/SyncStockRemainingQty.php`

Command ini untuk sinkronisasi data existing jika diperlukan:
```bash
php artisan stock:sync-remaining-qty
```

Logic:
1. Loop semua produk aktif
2. Hitung total stock dari transactions (type='in')
3. Hitung total remaining_qty dari transactions
4. Jika ada selisih, log warning
5. Tampilkan summary produk yang inkonsisten

**Catatan:** Ini optional, karena setelah fix validasi, penjualan akan jalan normal.

### Step 3: Preventive Measures

**Tambahkan logging di StockService.php:**

Di method `recordSale()`, setelah FIFO calculation (line 213), tambahkan:
```php
// Log warning jika ada inkonsistensi
if ($qtyToSell > 0) {
    \Log::warning('FIFO inconsistency detected', [
        'product_id' => $product->id,
        'product_name' => $product->name,
        'qty_to_sell' => $trx['data']['qty'],
        'remaining_after_fifo' => $qtyToSell,
        'product_stock' => $product->stock,
    ]);
}
```

Ini membantu detect inkonsistensi di masa depan tanpa blocking penjualan.

## File yang Perlu Diubah

1. **`app/Services/StockService.php`**
   - Hapus validasi FIFO terpisah (line 131-141)
   - Tambah logging untuk inkonsistensi (optional)

2. **`app/Console/Commands/SyncStockRemainingQty.php`** (optional)
   - Buat command baru untuk sync data existing

## Verification

1. **Test penjualan produk yang sebelumnya error:**
   - Coba jual "Lanyard Gambar" yang stock > 0 tapi remaining_qty = 0
   - Seharusnya berhasil tanpa error

2. **Test penjualan produk dengan stock = 0:**
   - Seharusnya error dengan pesan yang jelas: "Stok X tidak mencukupi. Tersedia: 0"

3. **Test void/delete sale:**
   - Hapus penjualan dan cek remaining_qty di-restore dengan benar
   - Cek stock di products table juga di-restore

4. **Test edge case:**
   - Jual produk dengan stock > 0 tapi semua remaining_qty sudah terpakai
   - Seharusnya berhasil, FIFO calculation akan handle dengan qty yang tersedia

5. **Cek log:**
   - Setelah beberapa penjualan, cek Laravel log untuk warning inkonsistensi
   - Jika ada warning, berarti ada produk yang perlu di-sync manual

## Rollback Plan

Jika ada masalah setelah deploy:
1. Revert perubahan di StockService.php
2. Validasi FIFO terpisah akan aktif kembali
3. Produk yang inkonsisten akan error lagi, tapi sistem kembali ke state sebelumnya

## Timeline

- **Step 1:** Fix validasi (5 menit) - CRITICAL, unblock penjualan
- **Step 2:** Buat sync command (15 menit) - OPTIONAL
- **Step 3:** Tambah logging (5 menit) - RECOMMENDED

**Total: 25 menit untuk implementasi lengkap**
