# SOP Tutup Buku Bulanan — ADI CELL POS

> Dokumen ini berisi Standar Operasional Prosedur (SOP) untuk penutupan periode
> akuntansi bulanan. Dilaksanakan setiap akhir bulan atau maksimal tanggal 5
> bulan berikutnya.

---

## Daftar Isi

1. [Persiapan](#1-persiapan)
2. [Stok Opname](#2-stok-opname)
3. [Opname Saldo PPOB & E-Wallet](#3-opname-saldo-ppob--e-wallet)
4. [Cash Counter](#4-cash-counter)
5. [Verifikasi Transaksi](#5-verifikasi-transaksi)
6. [Penagihan Piutang](#6-penagihan-piutang)
7. [Pembayaran Tagihan](#7-pembayaran-tagihan)
8. [Cetak Laporan](#8-cetak-laporan)
9. [Set Modal Awal Bulan Baru](#9-set-modal-awal-bulan-baru)
10. [Backup Database](#10-backup-database)
11. [Checklist Akhir](#11-checklist-akhir)

---

## 1. Persiapan

**Waktu:** H-1 akhir bulan atau hari kerja terakhir bulan berjalan.

1. Pastikan semua transaksi harian sudah dicatat.
2. Informasikan ke seluruh kasir/karyawan bahwa akan dilakukan tutup buku.
3. Siapkan alat tulis untuk stok opname fisik (jika perlu print daftar barang).

---

## 2. Stok Opname

**Menu:** `Inventaris → Stok Opname`

**Tujuan:** Menyamakan stok fisik dengan stok di sistem.

### Langkah:

1. Buka halaman **Stok Opname**.
2. Sistem akan menampilkan daftar semua produk beserta stok sistem saat ini.
3. Input **Stok Fisik** untuk setiap produk sesuai hasil hitungan manual.
4. Klik **Simpan**.
5. Sistem otomatis mencatat selisih sebagai:
   - **Stok Opname Plus** (income) — jika stok fisik > stok sistem.
   - **Stok Opname Minus** (expense) — jika stok fisik < stok sistem.

> **Tips:** Jika stok opname sudah pernah dilakukan bulan lalu, cukup opname
> untuk produk yang bergerak cepat (fast-moving) atau yang sering tidak akurat.

---

## 3. Opname Saldo PPOB & E-Wallet

**Menu:** `Akun → Opname Saldo`

**Tujuan:** Merekoniliasi saldo akun PPOB (Pulsa, Listrik, BPJS, dll.) dan
E-Wallet dengan saldo fisik/aktual.

### Langkah:

1. Buka halaman **Opname Saldo**.
2. Pilih akun PPOB atau E-Wallet yang akan diopname.
3. Sistem akan menampilkan **Saldo Sistem** (perhitungan dari mutasi).
4. Input **Saldo Fisik/Aktual** (cek dari aplikasi atau saldo fisik).
5. Sistem otomatis menghitung selisih dan membuat **Mutasi Penyesuaian**
   antara akun tersebut dengan akun Kas.

> **Frekuensi:** Wajib dilakukan setiap akhir bulan. Untuk EDC sebaiknya
> dilakukan setiap minggu.

---

## 4. Cash Counter

**Menu:** `Kas → Cash Counter`

**Tujuan:** Menghitung dan memverifikasi uang fisik di kasir.

### Langkah:

1. Buka halaman **Cash Counter**.
2. Masukkan **Target Amount** (saldo kas dari sistem).
3. Hitung uang fisik per pecahan dan input jumlahnya.
4. Klik **Simpan Sesi**.
5. Jika ada selisih antara target dan jumlah fisik:
   - Sistem otomatis membuat **Penyesuaian Kas** (income/expense).
   - Investigasi penyebab selisih sebelum menyetujui penyesuaian.

> **Frekuensi:** Harian (setiap shift) dan wajib dilakukan saat tutup buku.

---

## 5. Verifikasi Transaksi

**Tujuan:** Memastikan semua transaksi bulan ini lengkap dan benar.

### 5.1 Cek Pengeluaran

**Menu:** `Keuangan → Pengeluaran`
- Filter: pilih bulan ini.
- Pastikan tidak ada biaya operasional yang terlewat.
- Kategori **Biaya Real** harus sesuai dengan pengeluaran aktual.
- Kategori **Mutasi** (Stok Masuk, Piutang, Cash Keluar, Biaya MDR) sudah
  sinkron dengan modul terkait.

### 5.2 Cek Pendapatan

**Menu:** `Keuangan → Pendapatan`
- Filter: pilih bulan ini.
- Pastikan semua pemasukan sudah tercatat.
- Tab **Pendapatan Real** = penjualan + jasa + pendapatan lainnya.
- Tab **Mutasi** = Piutang, Transfer Masuk, Pending.

### 5.3 Cek Mutasi

**Menu:** `Keuangan → Mutasi`
- Pastikan semua transfer antar akun sudah benar.
- Tidak ada mutasi menggantung (belum diverifikasi).

### 5.4 Cek Transaksi Pending

**Menu:** `Keuangan → Transaksi Pending`
- Selesaikan semua transaksi pending (EDC, Transfer) yang sudah settlement.
- Transaksi yang masih pending di akhir bulan harus diverifikasi fisiknya.

---

## 6. Penagihan Piutang

**Menu:** `Keuangan → Piutang`

**Tujuan:** Menagih piutang yang jatuh tempo sebelum tutup buku.

### Langkah:

1. Buka halaman **Piutang**.
2. Klik tab **Terlewat** (overdue) untuk lihat piutang yang melewati jatuh tempo.
3. Lakukan penagihan:
   - ✅ **Bayar** — klik tombol hijau "Bayar" jika customer membayar.
     Bisa dicicil (parsial) atau lunas sekaligus.
   - ✅ **Bayar Semua** — centang beberapa piutang, lunasi massal.
4. Jika piutang tidak tertagih:
   - ❌ **Batalkan** — klik tombol "Batalkan" untuk void piutang.
     - Sistem akan menghapus Expense terkait (uang keluar dikembalikan).
     - Status menjadi **Dibatalkan**.

### Filter Status:
| Tab | Menampilkan |
|-----|-------------|
| **Semua** | Semua piutang |
| **Belum** | Unpaid (belum dibayar) |
| **Terlewat** | Unpaid + melewati jatuh tempo |
| **Lunas** | Sudah dibayar penuh |
| **Batal** | Dibatalkan (voided) |

---

## 7. Pembayaran Tagihan

**Menu:** `Kas → Tagihan`

**Tujuan:** Membayar tagihan rutin bulanan (sewa, listrik, dll.).

### Langkah:

1. Buka halaman **Tagihan Bulanan**.
2. Cek daftar tagihan periode ini yang **Belum Dibayar**.
3. Klik **Bayar** untuk mencatat pembayaran:
   - Pilih akun sumber pembayaran.
   - Sistem otomatis mencatat expense sesuai kategori tagihan.
4. Pastikan semua tagihan bulan ini sudah terbayar.

---

## 8. Cetak Laporan

**Tujuan:** Dokumentasi posisi keuangan akhir bulan.

### 8.1 Laporan Laba Rugi

**Menu:** `Laporan → Laba Rugi`
- Pilih periode: **bulan berjalan**.
- Export Excel untuk arsip.
- Cek komponen:
  - **Pendapatan** — revenue dari penjualan + jasa.
  - **HPP** — harga pokok penjualan.
  - **Laba Kotor** = Pendapatan − HPP.
  - **Biaya Operasional** — pengeluaran real (bukan mutasi).
  - **Laba Bersih** = Laba Kotor − Biaya Operasional.

### 8.2 Neraca

**Menu:** `Laporan → Neraca`
- Pilih tanggal: **hari terakhir bulan ini**.
- Verifikasi **Balance Check** harus hijau ✅ (ASET = Kewajiban + Ekuitas).
- Cek komponen:

| Komponen | Keterangan |
|----------|------------|
| **ASET LANCAR** | |
| ┣ Cash | Saldo akun tunai |
| ┣ Bank | Saldo akun bank |
| ┣ E-Wallet | Saldo akun e-wallet |
| ┣ Piutang | Total piutang unpaid |
| ┗ Persediaan | Nilai stok barang |
| **KEWAJIBAN** | Rp 0 (modul hutang belum tersedia) |
| **EKUITAS** | |
| ┣ Modal Awal | Total opening balance |
| ┣ Laba Ditahan | Profit awal tahun s.d. sebelum bulan ini |
| ┗ Laba Periode | Profit bulan berjalan |

### 8.3 Ringkasan Bulanan

**Menu:** `Keuangan → Ringkasan`
- Atur jumlah bulan = 1 atau 3 untuk lihat ringkasan.
- Export jika diperlukan.

### 8.4 Laporan Stok

**Menu:** `Inventaris → Laporan Stok`
- Cek total nilai stok akhir bulan.
- Catat produk yang mendekati expired date.

### 8.5 Laporan Penjualan

**Menu:** `Penjualan → Laporan Penjualan`
- Export Excel laporan penjualan bulanan.
- Cek profit penjualan per produk/kategori.

---

## 9. Set Modal Awal Bulan Baru

**Menu:** `Akun → Modal Awal`

**Tujuan:** Memindahkan saldo akhir bulan menjadi modal awal bulan berikutnya.

### Langkah:

1. Buka halaman **Modal Awal**.
2. Pilih periode: **bulan berikutnya** (YYYY-MM).
3. Isi saldo awal setiap akun berdasarkan **Neraca** tanggal akhir bulan ini.
   - Contoh: Neraca 30 Juni 2026 menunjukkan saldo Kas Rp 5.000.000.
   - Maka isi Modal Awal Juli 2026 untuk akun Kas sebesar Rp 5.000.000.
4. Simpan.

> **⚠️ Penting:** Jika Modal Awal bulan baru tidak diisi, sistem akan
> menggunakan data bulan sebelumnya untuk perhitungan saldo. Namun tetap
> disarankan diisi manual untuk akurasi.

---

## 10. Backup Database

**Menu:** `Pengaturan → Backup DB`

### Langkah:

1. Buka halaman **Backup Database**.
2. Klik tombol **Backup**.
3. File SQL akan terdownload dengan format: `backup-YYYY-MM-DD.sql`
4. Simpan file backup di folder arsip dengan struktur:
   ```
   📁 Arsip Keuangan/
   └── 📁 2026/
       ├── 📁 Juni/
       │   ├── backup-2026-06-30.sql
       │   ├── Laporan Laba Rugi Juni 2026.xlsx
       │   └── Neraca 30 Juni 2026.xlsx
       └── 📁 Juli/
           └── ...
   ```

> **⚠️ Disclaimer:** Fitur **Reset Data** akan menghapus semua transaksi.
> Jangan diklik! Hanya digunakan untuk reset total jika memulai dari awal.

---

## 11. Checklist Akhir

Gunakan checklist ini untuk memastikan tidak ada langkah yang terlewat.

```
☐ Stok Opname — stok fisik = sistem
☐ Opname Saldo PPOB & E-Wallet — saldo fisik = sistem
☐ Cash Counter — uang fisik = saldo kas sistem
☐ Verifikasi Pengeluaran — semua biaya tercatat
☐ Verifikasi Pendapatan — semua income tercatat
☐ Verifikasi Mutasi — transfer antar akun benar
☐ Transaksi Pending — sudah diselesaikan
☐ Piutang — ditagih / dibatalkan
☐ Tagihan Bulanan — sudah dibayar
☐ Laporan Laba Rugi — dicetak & diarsip
☐ Neraca — balance check ✅
☐ Modal Awal bulan baru — sudah diisi
☐ Backup Database — file .sql tersimpan

Tanggal: _____________  Disetujui: _____________
```

---

## Revisi Dokumen

| Versi | Tanggal | Perubahan | Oleh |
|-------|---------|-----------|------|
| 1.0 | 28 Juni 2026 | Dokumen awal | — |
