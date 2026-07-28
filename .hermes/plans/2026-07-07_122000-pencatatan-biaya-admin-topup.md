# Pencatatan Biaya Admin Topup (Digipos) — Implementation Plan

**Goal:** Memberikan solusi pencatatan mutasi topup saldo Digipos yang ada biaya admin 2.500, agar saldo akun dan laporan tetap akurat.

**Architecture:** Memanfaatkan sistem **Mutasi** (transfer antar akun) + **Biaya** (expense) yang sudah ada, dengan tambahan kategori expense baru. Tidak perlu tabel/relasi baru.

**Tech Stack:** Laravel 13, MySQL, Blade, Bootstrap 5

---

## Latar Belakang

**Kasus:** Topup saldo Digipos — misal isi 202.500, tapi karena biaya admin 2.500, saldo Digipos yang masuk cuma 200.000.

**Masalah:** Kalau dicatat mutasi 202.500 dari Cash → Digipos, saldo Digipos akan naik 202.500 (tidak akurat, karena realnya cuma 200.000). Kalau dicatat 200.000 saja, maka ada uang 2.500 yang tidak tercatat kemana perginya.

---

## Analisis Sistem Saat Ini

### Model yang relevan:
| Model | Fungsi | Field penting |
|-------|--------|---------------|
| `Mutation` | Transfer antar akun | `from_account_id`, `to_account_id`, `amount` |
| `Expense` | Biaya/pengeluaran | `account_id`, `category`, `amount` |
| `Income` | Pendapatan | `account_id`, `category`, `amount` |

### Kategori Expense yang sudah ada (di `config/categories.php`):
- **User:** Listrik, Air, Sewa, Gaji, Transportasi, dll.
- **System (cash_movement):** `Biaya MDR` (pnl: true), `Cash Keluar`, `Prive`, dll.

`Biaya MDR` sudah persis seperti biaya admin — bedanya MDR untuk transaksi kartu, sementara ini untuk topup.

### Account:
- Ada akun `Cash` (default), `BCA`, dll — diatur lewat `config/accounts.php`
- Tidak ada akun khusus untuk "fee/administration" — biaya dicatat via Expense model per akun

---

## Opsi Pencatatan

### Opsi A: Mutasi Gross + Expense (✅ Direkomendasikan)

**Cara:**
1. **Mutasi:** 202.500 dari Cash → Digipos (full amount, deskripsi: "Topup saldo Digipos")
2. **Expense:** 2.500 kategori "Biaya MDR" (atau "Biaya Admin Topup"), akun = Cash/Digipos

**Hasil di sistem:**
- Cash: -202.500
- Digipos: +200.000 (202.500 masuk via mutasi, 2.500 keluar via expense)
- Expense: +2.500 (tertrack sebagai biaya)

**Pro:** Akurat nutasi cash, akurat saldo Digipos, expense terpisah untuk laporan laba-rugi.

**Kontra:** Perlu 2 entry manual (mutasi + expense).

### Opsi B: Dua Mutasi

**Cara:**
1. Mutasi: 202.500 dari Cash → Digipos (deskripsi: "Topup + biaya admin")
2. Mutasi: 2.500 dari Digipos → akun "Biaya Admin" (virtual account)

**Pro:** Semua tercatat sebagai mutasi.
**Kontra:** Perlu akun virtual "Biaya Admin". Expense tidak terlihat di laporan biaya.

### Opsi C: Fitur Baru — Fee otomatis di form Mutasi (Future)

Menambahkan field `admin_fee` di form Mutasi. Saat simpan, sistem otomatis:
1. Buat Mutasi (gross) dari Akun Asal → Akun Tujuan
2. Buat Expense (admin_fee) dari Akun Tujuan dengan kategori Biaya Admin

**Pro:** Satu form, dua catatan otomatis.
**Kontra:** Perlu pengembangan lebih besar (model, form, controller).

---

## Task Plan (Opsi A + Persiapan Opsi C)

### Task 1: Tambah Kategori Expense "Biaya Admin Topup"

**Objective:** Menambahkan kategori expense khusus untuk biaya admin topup (mirip Biaya MDR).

**Files:**
- Modify: `config/categories.php:31` (tambah kategori setelah Biaya MDR)

**Step 1:** Edit `config/categories.php`

Di bagian `'expense' → 'system'`, tambahkan setelah `'Biaya MDR'`:

```php
['key' => 'Biaya Admin Topup', 'pnl' => true, 'filter' => 'cash_movement'],
```

Detail:
- `pnl` => `true`: biaya ini masuk perhitungan laba-rugi (real expense)
- `filter` => `cash_movement`: muncul di tab Mutasi, bukan biaya operasional biasa

**Step 2:** Verifikasi

Cek bahwa kategori muncul di dropdown form expense:
- Buka halaman Pengeluaran → Tambah → lihat opsi kategori

---

### Task 2: Verifikasi Flow Manual (User Guide)

**Objective:** Memastikan user bisa mencatat topup dengan biaya admin via 2 entry (Mutasi + Expense).

**Langkah-langkah untuk user:**

Contoh: Topup Digipos 202.500 dari Cash, admin 2.500, net 200.000 ke Digipos.

**Step 1: Catat Mutasi (gross)**
- Menu: **Mutasi → Tambah Mutasi**
- Tanggal: [tanggal topup]
- Dari Akun: **Cash**
- Ke Akun: **Digipos**
- Nominal: **202500**
- Keterangan: "Topup saldo Digipos (termasuk admin 2500)"
- Simpan

**Step 2: Catat Biaya Admin**
- Menu: **Pengeluaran → Tambah**
- Tanggal: [tanggal topup]
- Akun: **Digipos** (biaya admin dikurangkan dari saldo Digipos)
- Kategori: **Biaya Admin Topup** (atau Biaya MDR)
- Nominal: **2500**
- Keterangan: "Biaya admin topup Digipos"
- Simpan

**Verifikasi:**
- Saldo Cash: berkurang 202.500 ✅
- Saldo Digipos: bertambah 200.000 (202.500 - 2.500) ✅
- Laporan Laba-Rugi: ada biaya admin 2.500 ✅

---

### Task 3: (Opsional) Fitur Fee Otomatis di Form Mutasi

**Objective:** Menambahkan field `admin_fee` di form Mutasi agar user cukup input sekali.

**Files:**
- Create: `database/migrations/xxxx_add_admin_fee_to_mutations_table.php`
- Modify: `app/Models/Mutation.php` (tambah fillable `admin_fee`)
- Modify: `app/Http/Requests/StoreMutationRequest.php` (tambah validasi)
- Modify: `app/Http/Requests/UpdateMutationRequest.php` (tambah validasi)
- Modify: `app/Services/MutationService.php` (buat expense otomatis)
- Modify: `resources/views/mutations/index.blade.php` (tambah field fee di modal)
- Modify: `config/categories.php` (pastikan kategori sudah ada)

**Step 1: Migration**

```php
Schema::table('mutations', function (Blueprint $table) {
    $table->integer('admin_fee')->nullable()->after('amount');
});
```

**Step 2: Update Mutation model**

```php
protected $fillable = [
    'date', 'from_account_id', 'to_account_id', 'amount', 'admin_fee',
    'description', 'source', 'receivable_id',
];
```

**Step 3: Update MutationService::create()**

Saat `admin_fee` terisi:
1. Simpan mutation seperti biasa
2. Otomatis buat Expense dengan:
   - `account_id` = `to_account_id` (akun tujuan)
   - `category` = "Biaya Admin Topup"
   - `amount` = `admin_fee`
   - `date` = sama dengan mutation
   - `description` = "Biaya admin: " . $data['description']

**Step 4: Update form mutasi**

Tambah field di modal:
```html
<div class="mb-3">
    <label class="form-label">Biaya Admin (opsional)</label>
    <input type="number" step="1" name="admin_fee" class="form-control" placeholder="0">
    <small class="text-muted">Jika diisi, otomatis dicatat sebagai expense Biaya Admin Topup</small>
</div>
```

**Risiko:** Perubahan migration, perlu `php artisan migrate`. Juga perlu handle update/delete mutation — jika admin_fee diubah, expense terkait harus diupdate juga.

---

## Rekomendasi

| Task | Prioritas | Effort |
|------|-----------|--------|
| Task 1: Tambah kategori "Biaya Admin Topup" | 🔴 Tinggi | 1 menit |
| Task 2: Dokumentasi flow manual | 🟡 Sedang | 5 menit |
| Task 3: Fitur admin_fee otomatis | 🟢 Rendah | 30-60 menit |

**Saran:**
- **Sekarang:** Kerjakan Task 1 + 2 → user bisa langsung pakai secara manual
- **Nanti:** Task 3 kalau dirasa manual terlalu ribet

---

## Verifikasi

Setelah implementasi:
1. Buka halaman Pengeluaran → lihat apakah kategori "Biaya Admin Topup" muncul
2. Catat mutasi 202.500 Cash → Digipos
3. Catat expense 2.500 kategori Biaya Admin Topup, akun Digipos
4. Cek dashboard/saldo: Cash -202.500, Digipos +200.000
5. Cek laporan laba-rugi: ada biaya admin 2.500
