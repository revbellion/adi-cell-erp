# Plan: Rename Akun "Dalam Perjalanan" ke "Transit" atau "Pending"

## Context
User ingin mengubah nama akun "Dalam Perjalanan" menjadi "Transit" atau "Pending" tanpa merusak alur yang sudah ada.

## Analisis
Setelah investigasi, ditemukan bahwa:

1. **Nama akun di-config**: `config/accounts.php` line 6
   ```php
   'in_transit_name' => env('ACCOUNT_IN_TRANSIT_NAME', 'Dalam Perjalanan'),
   ```

2. **Semua code pakai config**: Tidak ada hardcode "Dalam Perjalanan" di code
   - `PendingTransactionService.php` → `config('accounts.in_transit_name')`
   - `DashboardService.php` → `config('accounts.in_transit_name')`
   - `BalanceSheetService.php` → `config('accounts.in_transit_name')`
   - Semua migration → `config('accounts.in_transit_name')`

3. **Database record**: Nama di tabel `accounts` perlu di-update

## Solusi

### Step 1: Update Config
**File:** `config/accounts.php`

Ubah default value:
```php
'in_transit_name' => env('ACCOUNT_IN_TRANSIT_NAME', 'Transit'),
```

Atau bisa juga override via `.env`:
```
ACCOUNT_IN_TRANSIT_NAME=Transit
```

### Step 2: Migration untuk Update Database
**File baru:** `database/migrations/2026_07_03_190000_rename_in_transit_account.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('accounts')
            ->where('name', 'Dalam Perjalanan')
            ->update(['name' => 'Transit']);
    }

    public function down(): void
    {
        DB::table('accounts')
            ->where('name', 'Transit')
            ->update(['name' => 'Dalam Perjalanan']);
    }
};
```

## Dampak
- ✅ **Tidak merusak alur** - semua code pakai config, bukan hardcode
- ✅ **Backward compatible** - migration punya rollback
- ✅ **Flexible** - bisa diubah via `.env` tanpa deploy
- ⚠️ **Breaking change** - nama akun berubah di UI dan laporan

## Verification
1. Jalankan migration
2. Cek dashboard → card "IN PROCESS" masih muncul
3. Cek pending transactions → EDC/Transfer masih jalan
4. Cek balance sheet → akun transit masih terhitung
5. Cek laporan → nama akun berubah jadi "Transit"

## Rekomendasi Nama
- **"Transit"** - lebih profesional, umum di akuntansi
- **"Pending"** - lebih deskriptif untuk transaksi yang belum selesai
- **"Dalam Perjalanan"** - tetap bisa dipakai jika user prefer bahasa Indonesia

Pilih salah satu sesuai preferensi user.
