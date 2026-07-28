# 🔴 Bug Report — Cash Tracker (ADI CELL POS)

Hasil audit kode sebelum deploy — ditemukan beberapa bug yang perlu diperbaiki.

---

## 🔴 KRITIS — Wajib Diperbaiki Sebelum Deploy

### B1. Backup/Restore Gagal di Server (Hardcoded Windows Path)
**File:** `app/Http/Controllers/BackupController.php` (line 24, 64)

```php
$mysqlDir = env('DB_MYSQL_DIR', 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin');
```

**Masalah:** Path hardcoded `C:\laragon\...` → hanya jalan di PC kamu. Di Hostinger/Linux/macOS bakal error karena `mysqldump.exe` atau `mysql.exe` gak ditemukan.

**Fix:** Gunakan `mysqldump` dari system PATH (bukan path absolut):
```php
$mysqlDump = env('DB_MYSQLDUMP_PATH', 'mysqldump');
$mysqlClient = env('DB_MYSQL_CLIENT', 'mysql');
```
dan di server Hostinger, mysqldump tinggal panggil tanpa path.

---

### B2. Duplicate Key Description — Informasi Diskon Hilang
**File:** `app/Services/StockService.php` (line 93–101)

```php
$desc = 'Pembelian ' . $product->name . ' (' . $item['qty'] . ' ' . $product->unit . ')';
if ($discount > 0) {
    $desc .= ' — diskon Rp ' . number_format($discount, 0, ',', '.');
}

Expense::create([
    ...
    'description'         => $desc,                    // ✅ Baris 97 — bener
    'description'         => 'Pembelian ' . $product->name . ' (...)',  // ❌ Baris 98 — timpa baris 97!
    ...
]);
```

**Masalah:** Array key `description` dobel. PHP pake yang terakhir, jadi `$desc` yang udah include diskon **selalu ketimpa**. Diskon gak tercatat di description.

**Fix:** Hapus baris 98 (`'description' => 'Pembelian ' . ...`), biarkan cuma baris 97 (`$desc`).

---

## 🟡 SEDANG — Perlu Diperbaiki

### B3. account_id NULL di CashCounter
**File:** `app/Services/CashCounterService.php` (line 42)

```php
'account_id' => $data['account_id'] ?? null,
```

Kalau `account_id` null, dan kolom `account_id` di tabel Income/Expense gak nullable, bakal kena **FK constraint error**. Sama di StockService line 332, 348.

**Fix:** Pastikan validasi account_id di request required untuk cash counter.

---

### B4. Eager Loading N+1 Potensial
**File:** Beberapa service

`MutationService::getAll()` — pake `with('fromAccount', 'toAccount')` ✅ ok
Tapi beberapa query lain mungkin ada N+1. Perlu dicek dengan Laravel Debugbar.

---

## 🟢 RENDAH — Saran

### B5. Tambah Validasi Bulk Delete
Bulk delete di beberapa controller (Income, Mutation, Expense) loop satu-satu tanpa validasi batch. Ok sih karena exception handling, tapi bisa lambat untuk 100+ item. Bisa pakai `whereIn()->delete()` langsung dengan pengecekan `source` dulu.

---

## ✅ SUDAH BAIK (No Issue)

| Area | Keterangan |
|------|-----------|
| Auth & middleware | ✅ Rate limiter, permission check, session regenerate |
| XSS Protection | ✅ Blade pake `{{ }}` (auto-escaped) |
| CSRF | ✅ Default Laravel |
| SQL Injection | ✅ Pake Eloquent, parameter binding |
| Input Validation | ✅ Form Request & validasi di controller |
| Error Pages | ✅ 403, 404, 419, 500, 503 custom |
| Security Headers | ✅ X-Frame-Options, X-Content-Type-Options, Referrer-Policy |

---

### Prioritas Fix

| Priority | Bug | File | Effort |
|----------|-----|------|--------|
| 🔴 P1 | B1 — Backup path windows | BackupController.php | 5 menit |
| 🔴 P1 | B2 — Duplicate description | StockService.php:98 | 1 menit |
| 🟡 P2 | B3 — account_id null | CashCounterService.php | 5 menit |
