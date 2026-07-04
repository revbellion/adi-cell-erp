# Plan: Sembunyikan Transfer dari UI + Update Card IN PROCESS

## Context

Tipe "Transfer" di modul Pending Transactions sudah bisa ditangani lewat modul Mutasi manual. Untuk menyederhanakan UI, opsi "Transfer" akan disembunyikan dari dropdown, tetapi kode backend tetap ada untuk backward compatibility dengan data existing.

Card "IN PROCESS" di dashboard perlu diupdate untuk menyertakan tipe `tf_masuk` di perhitungan Cash In Process.

## Perubahan

### 1. File: `resources/views/pending-transactions/index.blade.php`

**Filter dropdown (line 54)**
```diff
 <select name="type" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
     <option value="">Semua Tipe</option>
     <option value="edc" {{ request('type') == 'edc' ? 'selected' : '' }}>EDC</option>
-    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
     <option value="tf_masuk" {{ request('type') == 'tf_masuk' ? 'selected' : '' }}>TF Masuk</option>
 </select>
```

**Form create dropdown (line 214)**
```diff
 <select name="type" id="pending-type" class="form-select" required onchange="toggleBankField()">
     <option value="">Pilih Tipe</option>
     <option value="edc">EDC</option>
-    <option value="transfer">Transfer</option>
     <option value="tf_masuk">TF Masuk</option>
 </select>
```

### 2. File: `app/Services/DashboardService.php`

**Update cashInProcess query (line 137-140)**
```diff
-        // Cash In Process = transfer pending (BCA sudah terima, cash belum diserahkan)
+        // Cash In Process = transfer + tf_masuk pending (BCA sudah terima, cash belum diserahkan)
         $cashInProcess = \App\Models\PendingTransaction::where('status', 'pending')
-            ->where('type', 'transfer')
+            ->whereIn('type', ['transfer', 'tf_masuk'])
             ->sum('amount') ?? 0;
```

## Tidak Diubah (Backward Compatibility)

| File | Alasan |
|------|--------|
| `PendingTransactionService.php` | Kode handle Transfer tetap ada untuk existing records |
| `StorePendingTransactionRequest.php` | Validasi tetap terima `transfer` |
| `PendingTransaction.php` | Label `transfer` tetap ada |
| View (tombol complete, badge status) | Existing Transfer records tetap bisa complete/cancel |

## Efek

- Dropdown create: hanya **EDC** dan **TF Masuk**
- Dropdown filter: hanya **EDC** dan **TF Masuk**
- Data Transfer existing: tetap tampil, bisa complete/cancel/delete
- Tidak bisa create Transfer baru dari UI
- Card IN Process: Cash In Process sekarang mencakup `transfer` + `tf_masuk` pending

## Verifikasi

1. Buka halaman Pending Transactions
2. Klik "Tambah Pending" -> dropdown hanya ada EDC dan TF Masuk
3. Filter dropdown -> hanya ada EDC dan TF Masuk
4. Data Transfer existing masih tampil dan bisa di-complete/cancel
5. Dashboard -> card IN Process -> kolom Cash menampilkan total transfer + tf_masuk pending
6. Syntax check: `php -l` semua file PHP
7. View cache: `php artisan view:cache`

## Status

- [x] Hapus Transfer dari filter dropdown
- [x] Hapus Transfer dari form create dropdown
- [x] Update DashboardService.php: cashInProcess query include `tf_masuk`
- [x] Syntax check passed
- [x] Tambah `min-height: 110px` ke `.stat-card` di `public/css/app.css`

**Selesai.**
