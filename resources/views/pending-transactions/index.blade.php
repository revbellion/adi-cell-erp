@extends('layouts.app')
@section('title', 'Transaksi Pending')

@push('styles')
<style>
.btn-tindakan {
    transition: all 0.2s;
    border: 2px solid transparent;
}
.btn-tindakan:hover {
    transform: translateY(-1px);
}
.btn-tindakan.active {
    transform: translateY(0);
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
}
.btn-tindakan.active.btn-success {
    background: #059669 !important;
    border-color: #059e0b !important;
}
.btn-tindakan.active.btn-danger {
    background: #dc2626 !important;
    border-color: #ef4444 !important;
}
</style>
@endpush

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
                        <td>
                            {!! $pending->status_badge !!}
                            @if($pending->status === 'pending')
                                @if($pending->type === 'transfer')
                                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:0.65rem;">BCA ✓</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:0.65rem;">Cash ✓</span>
                                @endif
                            @endif
                        </td>
                        <td>{{ $pending->completedAccount?->name ?? '-' }}</td>
                        <td class="pe-3">
                            @if($pending->status === 'pending')
                                @if($pending->type === 'transfer')
                                <button type="button" class="btn btn-modern btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalComplete"
                                    data-id="{{ $pending->id }}"
                                    data-description="{{ $pending->description }}"
                                    data-amount="{{ $pending->amount }}"
                                    data-type="{{ $pending->type }}"
                                    data-action="keluar">
                                    <i class="fas fa-arrow-up me-1"></i>Cash Keluar
                                </button>
                                @else
                                <button type="button" class="btn btn-modern btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalComplete"
                                    data-id="{{ $pending->id }}"
                                    data-description="{{ $pending->description }}"
                                    data-amount="{{ $pending->amount }}"
                                    data-type="{{ $pending->type }}"
                                    data-action="masuk">
                                    <i class="fas fa-arrow-down me-1"></i>Uang Masuk
                                </button>
                                @endif
                            @endif
                            <span class="text-success small me-2">
                                @if($pending->completed_date) <i class="fas fa-check-circle"></i> {{ tgl($pending->completed_date) }} @endif
                            </span>
                            <form autocomplete="off" action="{{ route('pending.destroy', $pending->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="event.preventDefault(); confirmDelete('Hapus transaksi pending ini?').then(ok => ok && this.form.submit());">
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
                    <select name="type" id="pending-type" class="form-select" required onchange="toggleBankField()">
                        <option value="">Pilih Tipe</option>
                        <option value="edc">EDC</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="mb-3" id="bank-type-field" style="display:none;">
                    <label class="form-label">Jenis Bank</label>
                    <select name="bank_type" id="bank-type" class="form-select" onchange="hitungMDR()">
                        <option value="bca">BCA (MDR 0,15%)</option>
                        <option value="non_bca">Non-BCA (MDR 1%)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <input type="text" name="description" class="form-control" required placeholder="Contoh: Customer A - EDC">
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Transaksi</label>
                    <input type="number" step="1" name="amount" id="pending-amount" class="form-control" required placeholder="0" oninput="hitungMDR()">
                </div>
                <div id="mdr-info" class="mb-3 p-3 rounded" style="background:var(--border-subtle); display:none;">
                    <div class="d-flex justify-content-between mb-2">
                        <span style="font-size:0.85rem;">Total Transaksi:</span>
                        <span class="fw-semibold" id="mdr-total">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="font-size:0.85rem;">MDR (<span id="mdr-rate-display">0,15%</span>):</span>
                        <span class="fw-semibold text-danger" id="mdr-amount">- Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between" style="border-top:1px solid var(--border-subtle); padding-top:8px;">
                        <span style="font-size:0.85rem; font-weight:600;">Bersih Diterima:</span>
                        <span class="fw-bold" style="color:#10b981; font-size:1.1rem;" id="mdr-net">Rp 0</span>
                    </div>
                    <input type="hidden" name="mdr_rate" id="mdr-rate-input">
                    <input type="hidden" name="mdr_amount" id="mdr-amount-input">
                    <input type="hidden" name="net_amount" id="mdr-net-input">
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
            <input type="hidden" name="completed_type" id="completed-type">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i><span id="modal-title">Selesaikan Transaksi</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Transaksi</label>
                    <p class="fw-semibold mb-0" id="complete-description"></p>
                    <p class="text-muted" style="font-size:0.85rem;">Nominal: <span id="complete-amount" class="fw-bold"></span></p>
                </div>
                <div class="mb-3" id="akun-tujuan-section">
                    <label class="form-label">Akun Tujuan <span class="text-danger">*</span></label>
                    <select name="completed_account_id" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" data-type="{{ $account->type }}">{{ $account->name }} ({{ ucfirst($account->type) }})</option>
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
                <button type="submit" class="btn btn-modern btn-primary" id="btn-submit"><i class="fas fa-check me-1"></i>Selesaikan</button>
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
    var action = button.data('action');

    $('#formComplete').attr('action', '{{ url("pending") }}/' + id + '/complete');
    $('#complete-description').text(description);
    $('#complete-amount').text('Rp ' + amount.toLocaleString('id-ID'));
    $('#completed-type').val(action);

    // Set title dan info berdasarkan tipe
    if (type === 'transfer') {
        $('#modal-title').text('Cash Keluar');
        $('#btn-submit').html('<i class="fas fa-arrow-up me-1"></i>Catat Cash Keluar');
    } else {
        $('#modal-title').text('Uang Masuk');
        $('#btn-submit').html('<i class="fas fa-arrow-down me-1"></i>Catat Uang Masuk');
    }

    // Filter akun berdasarkan tipe
    var select = $('select[name="completed_account_id"]');
    select.find('option').show();
    if (type === 'transfer') {
        // Transfer: pilih akun cash
        select.find('option[data-type="cash"]').prop('selected', true);
    } else {
        // EDC/QRIS: pilih akun bank
        select.find('option[data-type="bank"]').prop('selected', true);
    }
    select.trigger('change');
});

// Fungsi toggle bank type field untuk EDC
function toggleBankField() {
    var type = document.getElementById('pending-type').value;
    var bankField = document.getElementById('bank-type-field');
    var mdrInfo = document.getElementById('mdr-info');
    
    if (type === 'edc') {
        bankField.style.display = 'block';
        mdrInfo.style.display = 'block';
        hitungMDR();
    } else {
        bankField.style.display = 'none';
        mdrInfo.style.display = 'none';
    }
}

// Fungsi hitung MDR
function hitungMDR() {
    var type = document.getElementById('pending-type').value;
    if (type !== 'edc') return;
    
    var bankType = document.getElementById('bank-type').value;
    var amount = parseInt(document.getElementById('pending-amount').value) || 0;
    var mdrRate = bankType === 'bca' ? 0.15 : 1.00;
    var mdrAmount = Math.round(amount * mdrRate / 100);
    var netAmount = amount - mdrAmount;
    
    document.getElementById('mdr-total').textContent = 'Rp ' + amount.toLocaleString('id-ID');
    document.getElementById('mdr-rate-display').textContent = mdrRate + '%';
    document.getElementById('mdr-amount').textContent = '- Rp ' + mdrAmount.toLocaleString('id-ID');
    document.getElementById('mdr-net').textContent = 'Rp ' + netAmount.toLocaleString('id-ID');
    
    document.getElementById('mdr-rate-input').value = mdrRate;
    document.getElementById('mdr-amount-input').value = mdrAmount;
    document.getElementById('mdr-net-input').value = netAmount;
}
</script>
@endpush
@endsection
