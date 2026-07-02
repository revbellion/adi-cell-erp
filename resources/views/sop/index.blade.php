@php $title = 'SOP Tutup Buku Bulanan' @endphp
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">SOP Tutup Buku Bulanan</h4>
    <a href="{{ asset('docs/06_SOP_TUTUP_BUKU.md') }}" target="_blank" class="btn btn-modern btn-secondary btn-sm">
        <i class="fas fa-download me-1"></i>Download Markdown
    </a>
</div>

<div class="alert alert-info d-flex align-items-center gap-2 py-2 px-3 mb-4" style="font-size:0.9rem;">
    <i class="fas fa-info-circle"></i>
    Dilaksanakan setiap akhir bulan atau maksimal tanggal 5 bulan berikutnya.
    Centang setiap langkah setelah selesai.
</div>

<div class="row g-3" id="sop-checklist">
    {{-- Step 1: Persiapan --}}
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:0.9rem;display:flex;align-items:center;justify-content:center;">1</span>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Persiapan</h5>
                        <p class="text-muted mb-0" style="font-size:0.85rem;">Informasikan ke seluruh kasir/karyawan, siapkan alat tulis untuk stok opname.</p>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input sop-check" id="check-1" onchange="saveCheck(1)">
                        <label class="form-check-label" for="check-1">Selesai</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 2: Stok Opname --}}
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:0.9rem;display:flex;align-items:center;justify-content:center;">2</span>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Stok Opname</h5>
                        <p class="text-muted mb-1" style="font-size:0.85rem;">Menu: <strong>Inventaris → Stok Opname</strong></p>
                        <ol style="font-size:0.85rem;padding-left:1.2rem;margin-bottom:0;">
                            <li>Input stok fisik sesuai hasil hitungan manual.</li>
                            <li>Selisih otomatis tercatat sebagai <strong>Stok Opname Plus</strong> (income) atau <strong>Stok Opname Minus</strong> (expense).</li>
                        </ol>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input sop-check" id="check-2" onchange="saveCheck(2)">
                        <label class="form-check-label" for="check-2">Selesai</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 3: Opname Saldo PPOB & E-Wallet --}}
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:0.9rem;display:flex;align-items:center;justify-content:center;">3</span>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Opname Saldo PPOB & E-Wallet</h5>
                        <p class="text-muted mb-1" style="font-size:0.85rem;">Menu: <strong>Akun → Opname Saldo</strong></p>
                        <ol style="font-size:0.85rem;padding-left:1.2rem;margin-bottom:0;">
                            <li>Pilih akun PPOB / E-Wallet.</li>
                            <li>Input saldo fisik/aktual (cek dari aplikasi).</li>
                            <li>Selisih otomatis dibuatkan mutasi penyesuaian ke akun Kas.</li>
                        </ol>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input sop-check" id="check-3" onchange="saveCheck(3)">
                        <label class="form-check-label" for="check-3">Selesai</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 4: Cash Counter --}}
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:0.9rem;display:flex;align-items:center;justify-content:center;">4</span>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Cash Counter</h5>
                        <p class="text-muted mb-1" style="font-size:0.85rem;">Menu: <strong>Kas → Cash Counter</strong></p>
                        <ol style="font-size:0.85rem;padding-left:1.2rem;margin-bottom:0;">
                            <li>Input target amount (saldo kas sistem).</li>
                            <li>Hitung uang fisik per pecahan.</li>
                            <li>Simpan sesi — selisih otomatis jadi <strong>Penyesuaian Kas</strong>.</li>
                        </ol>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input sop-check" id="check-4" onchange="saveCheck(4)">
                        <label class="form-check-label" for="check-4">Selesai</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 5: Verifikasi Transaksi --}}
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:0.9rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">5</span>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-2">Verifikasi Transaksi</h5>
                        <div class="row g-2">
                            <div class="col-md-3 col-6">
                                <div class="p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.05);">
                                    <div class="fw-semibold small">Pengeluaran</div>
                                    <div class="text-muted" style="font-size:0.75rem;">Menu: Keuangan → Pengeluaran</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.05);">
                                    <div class="fw-semibold small">Pendapatan</div>
                                    <div class="text-muted" style="font-size:0.75rem;">Menu: Keuangan → Pendapatan</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.05);">
                                    <div class="fw-semibold small">Mutasi</div>
                                    <div class="text-muted" style="font-size:0.75rem;">Menu: Keuangan → Mutasi</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.05);">
                                    <div class="fw-semibold small">Trans. Pending</div>
                                    <div class="text-muted" style="font-size:0.75rem;">Menu: Keuangan → Trans. Pending</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input sop-check" id="check-5" onchange="saveCheck(5)">
                        <label class="form-check-label" for="check-5">Selesai</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 6: Penagihan Piutang --}}
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:0.9rem;display:flex;align-items:center;justify-content:center;">6</span>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Penagihan Piutang</h5>
                        <p class="text-muted mb-1" style="font-size:0.85rem;">Menu: <strong>Keuangan → Piutang</strong></p>
                        <div style="font-size:0.85rem;">
                            <span class="badge bg-danger me-1">Terlewat</span> Cek tab <strong>"Terlewat"</strong> untuk piutang overdue.
                            <div class="mt-1 d-flex gap-2">
                                <span class="badge bg-success">Bayar</span> Tagih customer yang bisa bayar.
                                <span class="badge bg-secondary">Batalkan</span> Void piutang yang tidak tertagih.
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input sop-check" id="check-6" onchange="saveCheck(6)">
                        <label class="form-check-label" for="check-6">Selesai</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 7: Pembayaran Tagihan --}}
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:0.9rem;display:flex;align-items:center;justify-content:center;">7</span>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Pembayaran Tagihan</h5>
                        <p class="text-muted mb-1" style="font-size:0.85rem;">Menu: <strong>Kas → Tagihan Bulanan</strong></p>
                        <ol style="font-size:0.85rem;padding-left:1.2rem;margin-bottom:0;">
                            <li>Cek tagihan periode ini yang <strong>Belum Dibayar</strong>.</li>
                            <li>Klik <strong>Bayar</strong> untuk mencatat pembayaran.</li>
                            <li>Pastikan semua tagihan bulan ini sudah terbayar.</li>
                        </ol>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input sop-check" id="check-7" onchange="saveCheck(7)">
                        <label class="form-check-label" for="check-7">Selesai</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 8: Cetak Laporan --}}
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:0.9rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">8</span>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-2">Cetak Laporan</h5>
                        <div class="row g-2">
                            <div class="col-md-4 col-6">
                                <a href="{{ route('reports.profit-loss') }}" class="text-decoration-none">
                                    <div class="p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.05);">
                                        <div class="fw-semibold small">📊 Laba Rugi</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Laporan → Laba Rugi</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 col-6">
                                <a href="{{ route('reports.balance-sheet') }}" class="text-decoration-none">
                                    <div class="p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.05);">
                                        <div class="fw-semibold small">📋 Neraca</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Laporan → Neraca</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 col-6">
                                <a href="{{ route('summary.index') }}" class="text-decoration-none">
                                    <div class="p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.05);">
                                        <div class="fw-semibold small">📈 Ringkasan</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Keuangan → Ringkasan</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 col-6">
                                <a href="{{ route('stock.report') }}" class="text-decoration-none">
                                    <div class="p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.05);">
                                        <div class="fw-semibold small">📦 Laporan Stok</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Inventaris → Laporan Stok</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 col-6">
                                <a href="{{ route('sales-report.index') }}" class="text-decoration-none">
                                    <div class="p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.05);">
                                        <div class="fw-semibold small">💰 Laporan Penjualan</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Penjualan → Laporan Penjualan</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input sop-check" id="check-8" onchange="saveCheck(8)">
                        <label class="form-check-label" for="check-8">Selesai</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 9: Modal Awal Bulan Baru --}}
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:0.9rem;display:flex;align-items:center;justify-content:center;">9</span>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Set Modal Awal Bulan Baru</h5>
                        <p class="text-muted mb-1" style="font-size:0.85rem;">Menu: <strong>Akun → Modal Awal</strong></p>
                        <ol style="font-size:0.85rem;padding-left:1.2rem;margin-bottom:0;">
                            <li>Pilih periode bulan berikutnya (YYYY-MM).</li>
                            <li>Isi saldo awal per akun berdasarkan Neraca akhir bulan ini.</li>
                            <li>Klik <strong>Simpan</strong>.</li>
                        </ol>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input sop-check" id="check-9" onchange="saveCheck(9)">
                        <label class="form-check-label" for="check-9">Selesai</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 10: Backup Database --}}
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:0.9rem;display:flex;align-items:center;justify-content:center;">10</span>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Backup Database</h5>
                        <p class="text-muted mb-1" style="font-size:0.85rem;">Menu: <strong>Pengaturan → Backup DB</strong></p>
                        <ol style="font-size:0.85rem;padding-left:1.2rem;margin-bottom:0;">
                            <li>Klik <strong>Backup</strong> — file SQL akan terdownload.</li>
                            <li>Simpan di folder arsip bulanan.</li>
                        </ol>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input sop-check" id="check-10" onchange="saveCheck(10)">
                        <label class="form-check-label" for="check-10">Selesai</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Progress --}}
<div class="card card-modern mt-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold" id="progress-text">0 dari 10 langkah selesai</span>
            <span class="text-muted ms-2" style="font-size:0.85rem;">Centang tiap langkah setelah selesai</span>
        </div>
        <div style="width:200px;">
            <div class="progress" style="height:8px;">
                <div class="progress-bar bg-success" id="progress-bar" role="progressbar" style="width:0%;"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Load saved state
document.addEventListener('DOMContentLoaded', function() {
    for (var i = 1; i <= 10; i++) {
        var saved = localStorage.getItem('sop-check-' + i);
        var el = document.getElementById('check-' + i);
        if (el && saved === 'true') {
            el.checked = true;
        }
    }
    updateProgress();
});

function saveCheck(id) {
    var el = document.getElementById('check-' + id);
    localStorage.setItem('sop-check-' + id, el.checked);
    updateProgress();
}

function updateProgress() {
    var checked = 0;
    for (var i = 1; i <= 10; i++) {
        var el = document.getElementById('check-' + i);
        if (el && el.checked) checked++;
    }
    var pct = Math.round((checked / 10) * 100);
    document.getElementById('progress-text').textContent = checked + ' dari 10 langkah selesai';
    document.getElementById('progress-bar').style.width = pct + '%';

    // If all done, fire confetti-ish celebration
    if (checked === 10) {
        document.getElementById('progress-text').innerHTML = '🎉 Semua langkah selesai! Tutup buku berhasil!';
    }
}
</script>
@endpush
@endsection
