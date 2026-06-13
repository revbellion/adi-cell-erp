@extends('layouts.app')
@section('title', 'Piutang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Piutang</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('receivables.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-modern btn-success">
            <i class="fas fa-file-excel me-1"></i>Export
        </a>
        <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPiutang">
            <i class="fas fa-plus me-1"></i>Tambah Piutang
        </button>
    </div>
</div>

<form method="GET" action="{{ route('receivables.index') }}" class="row g-2 mb-4">
    <div class="col-auto">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" style="width:auto;">
    </div>
    <div class="col-auto">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" style="width:auto;">
    </div>
    <div class="col-auto">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama/HP..." style="width:150px;">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-modern btn-primary btn-sm"><i class="fas fa-search me-1"></i>Cari</button>
        <a href="{{ route('receivables.index') }}" class="btn btn-modern btn-secondary btn-sm"><i class="fas fa-times me-1"></i>Reset</a>
    </div>
</form>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="px-3 pt-3">
            <ul class="nav nav-tabs border-0">
                <li class="nav-item">
                    <a class="nav-link border-0 fw-semibold {{ request('status') == '' ? 'active' : '' }}" 
                       href="{{ route('receivables.index') }}">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link border-0 fw-semibold {{ request('status') == 'unpaid' ? 'active' : '' }}" 
                       href="{{ route('receivables.index', ['status' => 'unpaid']) }}">Unpaid</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link border-0 fw-semibold {{ request('status') == 'paid' ? 'active' : '' }}" 
                       href="{{ route('receivables.index', ['status' => 'paid']) }}">Paid</a>
                </li>
            </ul>
        </div>
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Tanggal</th>
                        <th>Nama</th>
                        <th>No. HP</th>
                        <th>Total</th>
                        <th>Jatuh Tempo</th>
                        <th>Sisa</th>
                        <th>Status</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivables as $receivable)
                    <tr>
                        <td class="ps-3">{{ tgl($receivable->date) }}</td>
                        <td class="fw-semibold">{{ $receivable->name }}</td>
                        <td>{{ $receivable->phone ?? '-' }}</td>
                        <td class="fw-semibold">{{ rp($receivable->amount) }}</td>
                        <td>{{ tgl($receivable->due_date) }}</td>
                        <td class="fw-semibold">{{ rp($receivable->remaining) }}</td>
                        <td>{!! $receivable->status_badge !!}</td>
                        <td class="pe-3">
                            @if($receivable->status == 'unpaid')
                            <button type="button" class="btn btn-modern btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditPiutang"
                                data-id="{{ $receivable->id }}"
                                data-name="{{ $receivable->name }}"
                                data-phone="{{ $receivable->phone }}"
                                data-amount="{{ $receivable->amount }}"
                                data-date="{{ $receivable->date->format('Y-m-d') }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-modern btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalBayarPiutang"
                                data-id="{{ $receivable->id }}"
                                data-name="{{ $receivable->name }}"
                                data-amount="{{ $receivable->remaining }}">
                                <i class="fas fa-check me-1"></i>Bayar
                            </button>

                            @if($receivable->phone)
                            <a href="{{ route('receivables.whatsapp', $receivable->id) }}" class="btn btn-modern btn-warning btn-sm" target="_blank">
                                <i class="fab fa-whatsapp me-1"></i>WA
                            </a>
                            @else
                            <button class="btn btn-modern btn-warning btn-sm" disabled title="No HP tidak tersedia">
                                <i class="fab fa-whatsapp me-1"></i>WA
                            </button>
                            @endif
                            @endif

                            <form action="{{ route('receivables.destroy', $receivable->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="return confirm('Hapus piutang ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada data piutang</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($receivables->hasPages())
    <div class="card-footer bg-white">
        <div class="pagination-modern">{{ $receivables->links() }}</div>
    </div>
    @endif
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalTambahPiutang">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('receivables.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Bayar</label>
                    <input type="number" step="1" name="amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                    <small class="text-muted">Jatuh tempo otomatis +3 hari</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalEditPiutang">
    <div class="modal-dialog">
        <form method="POST" action="" class="modal-content" id="formEditPiutang">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" id="edit-receivable-name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="phone" id="edit-receivable-phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Bayar</label>
                    <input type="number" step="1" name="amount" id="edit-receivable-amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" id="edit-receivable-date" class="form-control" required>
                    <small class="text-muted">Jatuh tempo otomatis +3 hari</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalBayarPiutang">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('receivables.pay') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Bayar Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="receivable_id" id="pay-receivable-id">
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <p class="fw-semibold mb-0" id="pay-name"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Akun</label>
                    <select name="account_id" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jumlah Bayar</label>
                    <input type="number" step="1" name="amount" id="pay-amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-success">Bayar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#modalBayarPiutang').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    $('#pay-receivable-id').val(button.data('id'));
    $('#pay-name').text(button.data('name'));
    $('#pay-amount').val(button.data('amount'));
});

$('#modalEditPiutang').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    $('#edit-receivable-name').val(button.data('name'));
    $('#edit-receivable-phone').val(button.data('phone') || '');
    $('#edit-receivable-amount').val(button.data('amount'));
    $('#edit-receivable-date').val(button.data('date'));
    $('#formEditPiutang').attr('action', '{{ url("receivables") }}/' + button.data('id'));
});
</script>
@endpush
