## 🔍 Hasil Scan Konsistensi Icon & Button

### ❌ Ketidakonsistenan Ditemukan

---

#### 1. Icon Delete: `fa-trash-alt` vs `fa-trash`

| File | Baris | Icon | Tombol |
|------|-------|------|--------|
| `resources/views/backups/index.blade.php` | 70 | `fa-trash-alt` | Reset Semua Data |
| `resources/views/stock/in.blade.php` | 78 | `fa-trash-alt` | Hapus item keranjang |

→ Semua halaman lain (incomes, mutations, expenses, products, etc.) pakai `fa-trash`  
→ **Saran:** Seragamkan ke `fa-trash`

---

#### 2. Icon Edit: `fa-pen` vs `fa-edit`

| File | Baris | Icon | Tombol |
|------|-------|------|--------|
| `resources/views/reports/balance-sheet.blade.php` | 171 | `fa-pen` | Edit modal awal |

→ Semua halaman lain (20+ pages) pakai `fa-edit`  
→ **Saran:** Ganti ke `fa-edit` biar seragam

---

#### 3. Spasi Icon Tidak Konsisten pada Tombol Berteks

Beberapa tombol pake `me-1` di iconnya, beberapa tidak:

| File | Baris | Icon | Tombol |
|------|-------|------|--------|
| `resources/views/bills/index.blade.php` | 58 | `<i class="fas fa-check"></i> Bayar` | ❌ Tanpa `me-1` |
| `resources/views/bills/index.blade.php` | 73 | `<i class="fas fa-edit"></i>` | ❌ Tanpa `me-1` padahal ada teks "Edit" |
| `resources/views/accounts/index.blade.php` | 53 | `<i class="fas fa-edit"></i>` | ❌ Tanpa `me-1` |
| `resources/views/customers/index.blade.php` | 75 | `<i class="fas fa-edit"></i>` | ❌ Tanpa `me-1` |
| `resources/views/incomes/index.blade.php` | 101 | `<i class="fas fa-edit"></i>` | ❌ Tanpa `me-1` |
| `resources/views/mutations/index.blade.php` | 96 | `<i class="fas fa-edit"></i>` | ❌ Tanpa `me-1` |
| `resources/views/expenses/index.blade.php` | 97 | `<i class="fas fa-edit"></i>` | ❌ Tanpa `me-1` |
| `resources/views/product-categories/index.blade.php` | 54 | `<i class="fas fa-edit"></i>` | ❌ Tanpa `me-1` |
| dst (hampir semua tombol edit) | — | — | ❌ Tanpa `me-1` |

→ Tapi ini sebenarnya **sudah konsisten** karena tombol edit hanya icon saja (tanpa teks), jadi gapapa tanpa `me-1`.  
→ **Kecuali** bills punya teks "Edit" setelah icon — perlu dicek.

---

#### 4. Warna Button untuk Aksi "Selesaikan" / "Bayar" Tidak Konsisten

| File | Baris | Tombol | Warna |
|------|-------|--------|-------|
| `bills/index.blade.php` | 245 | Konfirmasi Bayar | ✅ `btn-success` |
| `dashboard/index.blade.php` | 588 | Konfirmasi Bayar | ✅ `btn-success` |
| `receivables/index.blade.php` | 129, 321, 394 | Bayar | ✅ `btn-success` |
| `pending-transactions/index.blade.php` | 294 | Selesaikan | ❌ **`btn-primary`** (beda sendiri!) |

→ **Saran:** Ganti pending-transactions:294 ke `btn-success` biar seragam dengan tombol bayar/konfirmasi lainnya

---

#### 5. Dashboard: Tombol Simpan Pengeluaran Warna Merah

| File | Baris | Tombol | Warna |
|------|-------|--------|-------|
| `dashboard/index.blade.php` | 449 | Simpan (modal pengeluaran cepat) | ❌ **`btn-danger`** |

→ Tombol **Simpan** di modal lain warnanya `btn-primary` (incomes, mutations, accounts, dll)  
→ Warna merah biasanya untuk **Hapus**  
→ **Saran:** Ganti ke `btn-primary`

---

#### 6. Backups: Tombol Download via `btn-primary`

Ini sebenarnya bukan error, tapi `btn-primary` untuk Download Backup vs kebanyakan Export di halaman lain pakai `btn-success`.  
→ Minor — optional.

---

#### 7. Stock In: Icon `fa-plus-circle` vs `fa-plus`

| File | Baris | Icon |
|------|-------|------|
| `stock/in.blade.php` | 20 | `fa-plus-circle` (Tambah ke Keranjang) |
| Semua halaman lain | — | `fa-plus` (Tambah) |

→ Minor — `fa-plus-circle` di stock in punya konteks berbeda (tambah ke keranjang, bukan tambah data).

---

### ✅ Sudah Konsisten (Good)

| Pola | Status |
|------|--------|
| `fa-plus` untuk tombol Tambah | ✅ Semua seragam |
| `fa-edit` untuk tombol Edit | ✅ 95% seragam (kecuali 1 `fa-pen`) |
| `fa-trash` untuk tombol Hapus | ✅ Kecuali 2 file |
| `fa-file-excel` untuk Export | ✅ Semua seragam |
| `fa-times` + "Reset" untuk filter reset | ✅ Semua seragam |
| `fa-save` untuk tombol Simpan | ✅ Semua seragam |
| `btn-modern` class | ✅ Semua seragam |
| Tombol Batal di modal | ✅ Semua `btn-secondary` seragam |
| Bulk delete button | ✅ Semua `btn-danger` + `fa-trash` seragam |
| Edit button `btn-warning` | ✅ Semua seragam |

---

### 📋 Ringkasan Prioritas Perbaikan

| # | Issue | Lokasi | Fix |
|---|-------|--------|-----|
| 1 | `fa-trash-alt` → `fa-trash` | backups:70, stock/in:78 | Ganti icon |
| 2 | `fa-pen` → `fa-edit` | reports/balance-sheet:171 | Ganti icon |
| 3 | Warna "Selesaikan" pending | pending-transactions:294 | `btn-primary` → `btn-success` |
| 5 | Warna **Simpan** merah | `dashboard:449` | `btn-danger` → `btn-primary` |

---

### ➕ Action Button Tanpa Icon Sama Sekali

Berikut button/link yang punya teks tapi **tanpa icon**:

| # | File | Baris | Tombol | Saran Icon |
|---|------|-------|--------|-----------|
| A | `resources/views/dashboard/kasir.blade.php` | 77 | **Stok Masuk** (`btn-warning`) | Tambah `fa-warehouse` atau `fa-boxes` |
| B | `resources/views/reports/balance-sheet.blade.php` | 18 | **Hari Ini** (link filter) | Tambah `fa-calendar-day` |
| C | `resources/views/reports/profit-loss.blade.php` | 22 | **Bulan Ini** (link filter) | Tambah `fa-calendar-alt` |

> **Catatan:** Tombol **"Batal"** di modal konsisten **tidak** pakai icon di semua halaman — jadi itu ok/sengaja.
