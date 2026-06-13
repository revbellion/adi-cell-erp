@extends('layouts.app')
@section('title', 'Backup Database')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Backup Database</h4>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card card-modern shadow-sm">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-download me-2" style="color:var(--theme-primary);"></i>
                <span class="fw-semibold">Download Backup</span>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Download file SQL seluruh database. Simpan file ini di tempat aman.</p>
                <a href="{{ route('backups.download') }}" class="btn btn-modern btn-primary"
                   onclick="return confirm('Download backup database sekarang?')">
                    <i class="fas fa-download me-1"></i>Download Backup
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-modern shadow-sm border-{{ session('error') ? 'danger' : 'warning' }}">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-upload me-2" style="color:#f59e0b;"></i>
                <span class="fw-semibold">Restore Database</span>
            </div>
            <div class="card-body">
                <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:0.85rem;border-radius:8px;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Perhatian!</strong> Semua data saat ini akan diganti dengan data dari file backup.
                </div>
                <form method="POST" action="{{ route('backups.restore') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Pilih File Backup (.sql)</label>
                        <input type="file" name="backup_file" class="form-control" accept=".sql,.txt" required>
                    </div>
                    <button type="submit" class="btn btn-modern btn-warning"
                            onclick="return confirm('YAKIN ingin restore database? Semua data saat ini akan hilang!')">
                        <i class="fas fa-upload me-1"></i>Restore Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card card-modern shadow-sm border-danger">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-trash-alt me-2" style="color:#ef4444;"></i>
                <span class="fw-semibold">Reset Semua Data</span>
            </div>
            <div class="card-body">
                <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:0.85rem;border-radius:8px;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Bahaya!</strong> Aksi ini akan menghapus SELURUH data transaksi (pendapatan, pengeluaran, mutasi, piutang, modal awal). Struktur akun tetap aman.
                </div>
                <form method="POST" action="{{ route('backups.reset') }}" onsubmit="return confirmReset()">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Ketik <strong>RESET</strong> untuk konfirmasi:</label>
                        <input type="text" name="confirm" class="form-control" style="max-width:200px;" required>
                    </div>
                    <button type="submit" class="btn btn-modern btn-danger">
                        <i class="fas fa-trash-alt me-1"></i>Reset Semua Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function confirmReset() {
    var val = prompt('Ketik RESET untuk konfirmasi penghapusan semua data:');
    if (val === 'RESET') return true;
    alert('Konfirmasi gagal. Harap ketik "RESET" dengan benar.');
    return false;
}
</script>
@endpush
@endsection
