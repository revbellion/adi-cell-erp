@extends('layouts.app')
@section('title', 'Transaksi Pending')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-clock me-2" style="color:#f59e0b;"></i>Transaksi Pending</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('pending.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-modern btn-success">
            <i class="fas fa-file-excel me-1"></i>Export
        </a>
        <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPending">
            <i class="fas fa-plus me-1"></i>Tambah Pending
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show alert-modern py-2 px-3 mb-4" role="alert">
    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show alert-modern py-2 px-3 mb-4" role="alert">
    <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
@endif

<form autocomplete="off" method="GET" action="{{ route('pending.index') }}" class="row g-2 mb-4 filter-form">
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm" style="width:auto;">
            <option value="">Semua Status</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
        </select>
    </div>
    <div class="col-auto">
        <select name="type" class="form-select form-select-sm" style="width:auto;">
            <option value="">Semua Tipe</option>
            <option value="edc" {{ request('type') == 'edc' ? 'selected' : '' }}>EDC</option>
            <option value="qris" {{ request('type') == 'qris' ? 'selected' : '' }}>QRIS</option>
            <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
            <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Lainnya</option>
        </select>
    </div>
    <div class="col-auto">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari deskripsi..." style="width:150px;">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-modern btn-primary btn-sm"><i class="fas fa-search me-1"></i>Cari</button>
        <a href="{{ route('pending.index') }}" class="btn btn-modern btn-secondary btn-sm"><i class="fas fa-times me-1"></i>Reset</a>
    </div>
</form>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Tanggal</th>
                        <th>Tipe</th>
                        <th>Deskripsi</th>
                        <th class="text-end">Nominal</th>
                        <th>Status</th>
                        <th>Akun Tujuan</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendings as $pending)
                    <tr>
                        <td class="ps-3">{{ tgl($pending->pending_date) }}</td>
                        <td><span class="badge bg-info">{{ $pending->type_label }}</span></td>
                        <td>{{ $pending->description }}</td>
                        <td class="text-end fw-semibold">{{ rp($pending->amount) }}</td>
                        <td>{!! $pending->status_badge !!}</td>
                        <td>{{ $pending->completedAccount?->name ?? '-' }}</td>
                        <td class="pe-3">
                            @if($pending->status === 'pending')
                            <button type="button" class="btn btn-modern btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalComplete"
                                data-id="{{ $pending->id }}"
                                data-description="{{ $pending->description }}"
                                data-amount="{{ $pending->amount }}"
                                data-type="{{ $pending->type }}">
                                <i class="fas fa-check me-1"></i>Selesai
                            </button>
                            @endif
                            <span class="text-success small me-2">
                                @if($pending->completed_date) <i class="fas fa-check-circle"></i> {{ tgl($pending->completed_date) }} @endif
                            </span>
                            <form autocomplete="off" action="{{ route('pending.destroy', $pending->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="return confirm('Hapus transaksi pending ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada transaksi pending</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ $pendings->count() }} dari {{ $pendings->total() }} data</span>
        </div>
        <div class="d-flex gap-4">
            <div>
                <span style="font-size:0.75rem;color:var(--text-muted);">Total Pending</span>
                <span class="fw-bold ms-2" style="font-size:0.95rem;color:#f59e0b;">{{ rp($totalPending) }}</span>
            </div>
            <div>
                <span style="font-size:0.75rem;color:var(--text-muted);">Total Selesai</span>
                <span class="fw-bold ms-2" style="font-size:0.95rem;color:#10b981;">{{ rp($totalCompleted) }}</span>
            </div>
        </div>
    </div>
    @if($pendings->hasPages())
    <div class="card-footer bg-white">
        <div class="pagination-modern">{{ $pendings->links() }}</div>
    </div>
    @endif
</div>

<!-- Modal Tambah Pending -->
<div class="modal fade modal-modern" tabindex="-1" id="modalTambahPending">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('pending.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-clock me-2"></i>Tambah Transaksi Pending</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tipe</label>
                    <select name="type" class="form-select" required>
                        <option value="">Pilih Tipe</option>
                        <option value="edc">EDC</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <input type="text" name="description" class="form-control" required placeholder="Contoh: Customer A - EDC">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nominal</label>
                    <input type="number" step="1" name="amount" class="form-control" required placeholder="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="pending_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Complete -->
<div class="modal fade modal-modern" tabindex="-1" id="modalComplete">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formComplete">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i>Selesaikan Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="completed_type" id="completed-type">
                <div class="mb-3">
                    <label class="form-label">Transaksi</label>
                    <p class="fw-semibold mb-0" id="complete-description"></p>
                    <p class="text-muted" style="font-size:0.85rem;">Nominal: <span id="complete-amount" class="fw-bold"></span></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tindakan</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-modern btn-success flex-fill" onclick="setType('masuk')">
                            <i class="fas fa-arrow-down me-1"></i>Uang Masuk
                        </button>
                        <button type="button" class="btn btn-modern btn-danger flex-fill" onclick="setType('keluar')">
                            <i class="fas fa-arrow-up me-1"></i>Cash Keluar
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Akun Tujuan</label>
                    <select name="completed_account_id" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }} ({{ ucfirst($account->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="completed_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary"><i class="fas fa-check me-1"></i>Selesaikan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$('#modalComplete').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    var description = button.data('description');
    var amount = button.data('amount');
    var type = button.data('type');

    $('#formComplete').attr('action', '{{ url("pending") }}/' + id + '/complete');
    $('#complete-description').text(description);
    $('#complete-amount').text('Rp ' + amount.toLocaleString('id-ID'));
    $('#completed-type').val('');

    // Reset button states
    $('#modalComplete .btn-success').removeClass('active');
    $('#modalComplete .btn-danger').removeClass('active');
});

function setType(type) {
    $('#completed-type').val(type);
    if (type === 'masuk') {
        $('#modalComplete .btn-success').addClass('active');
        $('#modalComplete .btn-danger').removeClass('active');
    } else {
        $('#modalComplete .btn-danger').addClass('active');
        $('#modalComplete .btn-success').removeClass('active');
    }
}
</script>
@endpush
@endsection
