@extends('layouts.app')
@section('title', 'Piutang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Piutang</h4>
    <div class="d-flex gap-2 page-header-actions">
        <button type="button" class="btn btn-modern btn-success" id="btn-batch-bayar" style="display:none;" onclick="openBatchBayar()">
            <i class="fas fa-check-double me-1"></i>Bayar Semua (<span id="batch-count">0</span>) - <span id="batch-total-btn">Rp 0</span>
        </button>
        <a href="{{ route('receivables.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-modern btn-success">
            <i class="fas fa-file-excel me-1"></i>Export
        </a>
        <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPiutang">
            <i class="fas fa-plus me-1"></i>Tambah Piutang
        </button>
    </div>
</div>

<form autocomplete="off" method="GET" action="{{ route('receivables.index') }}" class="row g-2 mb-4 filter-form">
    <div class="col-auto">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
    </div>
    <div class="col-auto">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
    </div>
    <div class="col-auto">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama/HP..." style="width:150px;" oninput=" clearTimeout(this._timer); this._timer=setTimeout(()=>this.form.submit(),500)">
    </div>
    <div class="col-auto">
        <a href="{{ route('receivables.index') }}" class="btn btn-modern btn-secondary btn-sm"><i class="fas fa-times me-1"></i>Reset</a>
    </div>
</form>

<div class="bulk-action-bar mb-3 d-none" id="bulkActionBar">
    <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.08);border:1px solid rgba(var(--theme-primary-rgb),0.2);">
        <span class="fw-semibold" style="font-size:0.85rem;"><span id="bulkCount">0</span> dipilih</span>
        <span class="fw-bold" style="font-size:0.85rem;color:var(--theme-primary);" id="bulkTotal"></span>
        <form autocomplete="off" id="bulkDeleteForm" method="POST" action="{{ route('receivables.bulk-delete') }}" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="event.preventDefault(); confirmDelete('Hapus data yang dipilih?').then(ok => ok && this.closest('form').submit());">
                <i class="fas fa-trash me-1"></i>Hapus
            </button>
        </form>
        <button type="button" class="btn btn-modern btn-secondary btn-sm" onclick="clearBulkSelection()">
            <i class="fas fa-times me-1"></i>Batal
        </button>
    </div>
</div>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="px-3 pt-3 filter-pills-wrapper">
            <div class="filter-pills">
                <a class="filter-pill {{ request('status') == 'all' ? 'active' : '' }} pill-all" 
                   href="{{ route('receivables.index', ['status' => 'all']) }}">
                    <i class="fas fa-list-ul"></i> Semua
                </a>
                <a class="filter-pill {{ request('status') == 'unpaid' || request('status') == '' ? 'active' : '' }} pill-unpaid" 
                   href="{{ route('receivables.index', ['status' => 'unpaid']) }}">
                    <i class="fas fa-clock"></i> Belum Lunas
                </a>
                <a class="filter-pill {{ request('status') == 'paid' ? 'active' : '' }} pill-paid" 
                   href="{{ route('receivables.index', ['status' => 'paid']) }}">
                    <i class="fas fa-check-circle"></i> Lunas
                </a>
                <a class="filter-pill {{ request('status') == 'overdue' ? 'active' : '' }} pill-overdue" 
                   href="{{ route('receivables.index', ['status' => 'overdue']) }}">
                    <i class="fas fa-exclamation-triangle"></i> Telat
                </a>
                <a class="filter-pill {{ request('status') == 'voided' ? 'active' : '' }} pill-voided" 
                   href="{{ route('receivables.index', ['status' => 'voided']) }}">
                    <i class="fas fa-ban"></i> Batal
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:40px;"><input type="checkbox" class="form-check-input bulk-select-all" id="check-all" onclick="toggleAll(this)"></th>
                        <th class="sortable" data-sort="date">Tanggal</th>
                        <th class="sortable" data-sort="string">Nama</th>
                        <th class="sortable" data-sort="string">No. HP</th>
                        <th class="sortable" data-sort="number">Total</th>
                        <th class="sortable" data-sort="date">Jatuh Tempo</th>
                        <th class="sortable" data-sort="number">Sisa</th>
                        <th class="sortable" data-sort="string">Status</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivables as $receivable)
                    <tr>
                        <td class="ps-3">
                            @if($receivable->status !== 'paid')
                            <input type="checkbox" class="form-check-input bulk-select-item piutang-check" value="{{ $receivable->id }}" data-amount="{{ $receivable->remaining }}" data-remaining="{{ $receivable->remaining }}" onchange="updateBatch()">
                            @endif
                        </td>
                        <td>{{ tgl($receivable->date) }}</td>
                        <td class="fw-semibold">{{ $receivable->name }}</td>
                        <td>{{ $receivable->phone ?? '-' }}</td>
                        <td class="fw-semibold">{{ rp($receivable->amount) }}</td>
                        <td>{{ tgl($receivable->due_date) }}</td>
                        <td class="fw-semibold">{{ rp($receivable->remaining) }}</td>
                        <td>{!! $receivable->status_badge !!}</td>
                        <td class="pe-3">
                            @if($receivable->status == 'unpaid')
                            <button type="button" class="btn btn-modern btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahNominal"
                                data-id="{{ $receivable->id }}"
                                data-name="{{ $receivable->name }}"
                                data-amount="{{ $receivable->amount }}">
                                <i class="fas fa-plus-circle"></i>
                            </button>
                            <button type="button" class="btn btn-modern btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditPiutang"
                                data-id="{{ $receivable->id }}"
                                data-name="{{ $receivable->name }}"
                                data-phone="{{ $receivable->phone }}"
                                data-amount="{{ $receivable->amount }}"
                                data-date="{{ $receivable->date->format('Y-m-d') }}"
                                data-customer-id="{{ $receivable->customer_id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-modern btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalBayarPiutang"
                                data-id="{{ $receivable->id }}"
                                data-name="{{ $receivable->name }}"
                                data-amount="{{ $receivable->remaining }}">
                                <i class="fas fa-check me-1"></i>Bayar
                            </button>
                            @endif

                            @if($receivable->status !== 'paid')
                            <form autocomplete="off" action="{{ route('receivables.destroy', $receivable->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="event.preventDefault(); confirmDelete('Hapus piutang ini?').then(ok => ok && this.form.submit());">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @if($receivable->status === 'unpaid')
                            <form autocomplete="off" action="{{ route('receivables.void', $receivable->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-modern btn-secondary btn-sm" onclick="event.preventDefault(); confirmAction('Batalkan piutang ini?', 'Status akan diubah menjadi Dibatalkan.').then(ok => ok && this.form.submit());">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            @endif
                            @else
                            <button type="button" class="btn btn-modern btn-secondary btn-sm" disabled title="Piutang lunas tidak bisa dihapus">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada data piutang</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ $receivables->count() }} dari {{ $receivables->total() }} data</span>
        </div>
        <div class="d-flex gap-4">
            <div>
                <span style="font-size:0.75rem;color:var(--text-muted);">Total Piutang</span>
                <span class="fw-bold ms-2" style="font-size:0.95rem;color:var(--text-primary);">{{ rp($totalAmount) }}</span>
            </div>
            <div>
                <span style="font-size:0.75rem;color:var(--text-muted);">Sisa</span>
                <span class="fw-bold ms-2" style="font-size:0.95rem;color:var(--text-primary);">{{ rp($totalRemaining) }}</span>
            </div>
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
        <form autocomplete="off" method="POST" action="{{ route('receivables.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Pelanggan</label>
                    <select name="customer_id" class="form-select" id="tambah-customer-select">
                        <option value="">Tanpa Pelanggan / Ketik Manual</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->phone }}" data-address="{{ $customer->address }}" data-email="{{ $customer->email }}">{{ $customer->name }} {{ $customer->phone ? '- ' . $customer->phone : '' }}</option>
                        @endforeach
                    </select>
                    <div id="tambah-customer-info" class="mt-2 p-2 rounded-3 d-none" style="background:var(--border-subtle);font-size:0.85rem;">
                        <div id="tambah-customer-address" class="text-muted"></div>
                        <div id="tambah-customer-email" class="text-muted"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" id="tambah-name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="phone" id="tambah-phone" class="form-control" placeholder="08xxx">
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
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Batal</button>
                <button type="submit" class="btn btn-modern btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalEditPiutang">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formEditPiutang">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Pelanggan</label>
                    <select name="customer_id" class="form-select" id="edit-customer-select">
                        <option value="">Tanpa Pelanggan</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->phone }}" data-address="{{ $customer->address }}" data-email="{{ $customer->email }}">{{ $customer->name }} {{ $customer->phone ? '- ' . $customer->phone : '' }}</option>
                        @endforeach
                    </select>
                    <div id="edit-customer-info" class="mt-2 p-2 rounded-3 d-none" style="background:var(--border-subtle);font-size:0.85rem;">
                        <div id="edit-customer-address" class="text-muted"></div>
                        <div id="edit-customer-email" class="text-muted"></div>
                    </div>
                </div>
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
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Batal</button>
                <button type="submit" class="btn btn-modern btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalBayarPiutang">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('receivables.pay') }}" class="modal-content" id="formBayarPiutang">
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
                        @if($account->type !== 'ppob')
                        <option value="{{ $account->id }}" {{ $account->name === 'Cash' ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jumlah Bayar</label>
                    <input type="number" step="1" name="amount" id="pay-amount" class="form-control" required>
                    <small class="text-muted">Sisa hutang: <span id="pay-remaining" class="fw-semibold"></span></small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Batal</button>
                <button type="submit" class="btn btn-modern btn-success"><i class="fas fa-check me-1"></i>Bayar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalTambahNominal">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formTambahNominal">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Nominal Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <p class="fw-semibold mb-0" id="tambah-nominal-name"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nominal Saat Ini</label>
                    <p class="fw-semibold mb-0" id="tambah-nominal-current"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tambah Nominal</label>
                    <input type="number" step="1" name="amount" class="form-control" required min="1" placeholder="Rp">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Batal</button>
                <button type="submit" class="btn btn-modern btn-info"><i class="fas fa-plus-circle me-1"></i>Tambah</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Batch Bayar -->
<div class="modal fade modal-modern" tabindex="-1" id="modalBatchBayar">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('receivables.batch-pay') }}" class="modal-content" id="formBatchBayar">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-check-double me-2"></i>Bayar Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Piutang yang dipilih</label>
                    <div id="batch-list" class="p-2 rounded" style="background:var(--border-subtle);max-height:150px;overflow-y:auto;font-size:0.85rem;"></div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-muted">Total piutang:</span>
                        <span class="fw-bold" id="batch-total">Rp 0</span>
                    </div>
                </div>
                <input type="hidden" name="receivable_ids" id="batch-ids">
                <div class="mb-3">
                    <label class="form-label">Akun</label>
                    <select name="account_id" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        @if($account->type !== 'ppob')
                        <option value="{{ $account->id }}" {{ $account->name === 'Cash' ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Batal</button>
                <button type="submit" class="btn btn-modern btn-success"><i class="fas fa-check me-1"></i>Bayar Semua</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Bulk delete selection
document.querySelectorAll('.bulk-select-item').forEach(function(cb) {
    cb.addEventListener('change', updateBulkBar);
});

function updateBulkBar() {
    var checked = document.querySelectorAll('.bulk-select-item:checked');
    var count = checked.length;
    var bar = document.getElementById('bulkActionBar');
    if (!bar) return;
    
    if (count > 0) {
        bar.classList.remove('d-none');
        document.getElementById('bulkCount').textContent = count;
        
        var total = 0;
        checked.forEach(function(cb) { total += parseInt(cb.dataset.amount) || 0; });
        document.getElementById('bulkTotal').textContent = total > 0 ? 'Total: Rp ' + total.toLocaleString('id-ID') : '';
        
        var ids = [];
        checked.forEach(function(cb) { ids.push(cb.value); });
        var form = document.getElementById('bulkDeleteForm');
        form.querySelectorAll('input[name="ids[]"]').forEach(function(el) { el.remove(); });
        ids.forEach(function(id) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
    } else {
        bar.classList.add('d-none');
    }
}

function clearBulkSelection() {
    document.querySelectorAll('.bulk-select-item, .bulk-select-all').forEach(function(cb) {
        cb.checked = false;
    });
    updateBulkBar();
}

$('#modalBayarPiutang').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var remaining = parseInt(button.data('amount')) || 0;
    $('#pay-receivable-id').val(button.data('id'));
    $('#pay-name').text(button.data('name'));
    $('#pay-amount').val(remaining).attr('max', remaining);
    $('#pay-remaining').text('Rp ' + remaining.toLocaleString('id-ID'));
});

$('#formBayarPiutang').on('submit', function(e) {
    var amount = parseInt($('#pay-amount').val()) || 0;
    var max = parseInt($('#pay-amount').attr('max')) || 0;
    if (amount > max) {
        e.preventDefault();
        alert('Jumlah bayar tidak boleh melebihi sisa piutang!');
    }
});

$('#modalTambahNominal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    $('#tambah-nominal-name').text(button.data('name'));
    $('#tambah-nominal-current').text('Rp ' + parseInt(button.data('amount')).toLocaleString('id-ID'));
    $('#formTambahNominal').attr('action', '{{ url("receivables") }}/' + button.data('id') + '/add-nominal');
});

$('#modalEditPiutang').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    $('#edit-receivable-name').val(button.data('name'));
    $('#edit-receivable-phone').val(button.data('phone'));
    $('#edit-receivable-amount').val(button.data('amount'));
    $('#edit-receivable-date').val(button.data('date'));
    $('#formEditPiutang').attr('action', '{{ url("receivables") }}/' + button.data('id'));
    var selected = document.getElementById('edit-customer-select');
    selected.value = button.data('customer-id') || '';
    fillCustomerInfo(selected, 'edit');
});

// Batch payment functions
function toggleAll(el) {
    document.querySelectorAll('.piutang-check').forEach(function(cb) {
        cb.checked = el.checked;
    });
    updateBatch();
}

function updateBatch() {
    var checked = document.querySelectorAll('.piutang-check:checked');
    var count = checked.length;
    var total = 0;
    var ids = [];
    var listHtml = '';

    checked.forEach(function(cb) {
        var remaining = parseInt(cb.dataset.remaining) || 0;
        var name = cb.closest('tr').querySelector('td:nth-child(4)').textContent.trim();
        total += remaining;
        ids.push(cb.value);
        listHtml += '<div class="d-flex justify-content-between"><span>' + name + '</span><span class="fw-semibold">' + formatRupiah(remaining) + '</span></div>';
    });

    document.getElementById('batch-count').textContent = count;
    document.getElementById('batch-total').textContent = formatRupiah(total);
    document.getElementById('batch-total-btn').textContent = formatRupiah(total);
    document.getElementById('batch-ids').value = ids.join(',');
    document.getElementById('batch-list').innerHTML = listHtml || '<div class="text-muted text-center">Tidak ada piutang dipilih</div>';
    document.getElementById('btn-batch-bayar').style.display = count > 0 ? '' : 'none';
    document.getElementById('check-all').checked = count === document.querySelectorAll('.piutang-check').length;
}

function formatRupiah(num) {
    return 'Rp ' + num.toLocaleString('id-ID');
}

function openBatchBayar() {
    var ids = document.getElementById('batch-ids').value;
    if (!ids) return;
    new bootstrap.Modal(document.getElementById('modalBatchBayar')).show();
}

function fillCustomerInfo(select, modal) {
    var opt = select.options[select.selectedIndex];
    var nameId = modal === 'tambah' ? 'tambah-name' : 'edit-receivable-name';
    var phoneId = modal === 'tambah' ? 'tambah-phone' : 'edit-receivable-phone';
    var infoId = modal + '-customer-info';
    var addrId = modal + '-customer-address';
    var emailId = modal + '-customer-email';

    if (opt.value) {
        document.getElementById(nameId).value = opt.dataset.name;
        document.getElementById(nameId).readOnly = true;
        document.getElementById(phoneId).value = opt.dataset.phone || '';
        var info = document.getElementById(infoId);
        info.classList.remove('d-none');
        document.getElementById(addrId).textContent = opt.dataset.address ? 'Alamat: ' + opt.dataset.address : '';
        document.getElementById(emailId).textContent = opt.dataset.email ? 'Email: ' + opt.dataset.email : '';
    } else {
        document.getElementById(nameId).value = '';
        document.getElementById(nameId).readOnly = false;
        document.getElementById(phoneId).value = '';
        document.getElementById(infoId).classList.add('d-none');
    }
}

document.getElementById('tambah-customer-select')?.addEventListener('change', function() {
    fillCustomerInfo(this, 'tambah');
});
document.getElementById('edit-customer-select')?.addEventListener('change', function() {
    fillCustomerInfo(this, 'edit');
});
</script>
@endpush
