@extends('layouts.app')
@section('title', 'Jasa Cetak')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Jasa Cetak</h4>
    <div class="d-flex gap-2 page-header-actions">
        <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i>Tambah Pesanan
        </button>
    </div>
</div>

<form autocomplete="off" method="GET" action="{{ route('print-orders.index') }}" class="row g-2 mb-4 filter-form">
    <div class="col-auto">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
    </div>
    <div class="col-auto">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
    </div>
    <div class="col-auto">
        <select name="service_type" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <option value="">Semua Layanan</option>
            @foreach($serviceTypes as $key => $label)
            <option value="{{ $key }}" {{ request('service_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari..." style="width:150px;" oninput="clearTimeout(this._timer); this._timer=setTimeout(()=>this.form.submit(),500)">
    </div>
    <div class="col-auto">
        <a href="{{ route('print-orders.index') }}" class="btn btn-modern btn-secondary btn-sm"><i class="fas fa-times me-1"></i>Reset</a>
    </div>
</form>

<div class="bulk-action-bar mb-3 d-none" id="bulkActionBar">
    <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.08);border:1px solid rgba(var(--theme-primary-rgb),0.2);">
        <span class="fw-semibold" style="font-size:0.85rem;"><span id="bulkCount">0</span> dipilih</span>
        <span class="fw-bold" style="font-size:0.85rem;color:var(--theme-primary);" id="bulkTotal"></span>
        <form autocomplete="off" id="bulkDeleteForm" method="POST" action="{{ route('print-orders.bulk-delete') }}" style="display:inline;">
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
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:40px;"><input type="checkbox" class="form-check-input bulk-select-all"></th>
                        <th class="sortable" data-sort="date">Tanggal</th>
                        <th class="sortable" data-sort="string">Akun</th>
                        <th class="sortable" data-sort="string">Layanan</th>
                        <th class="sortable" data-sort="number">Jumlah</th>
                        <th class="sortable" data-sort="number">Harga Satuan</th>
                        <th class="sortable" data-sort="number">Total</th>
                        <th class="sortable" data-sort="string">Keterangan</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="ps-3">
                            <input type="checkbox" class="form-check-input bulk-select-item" value="{{ $order->id }}" data-amount="{{ $order->total }}">
                        </td>
                        <td>{{ tgl($order->date) }}</td>
                        <td>{{ $order->account->name ?? '-' }}</td>
                        <td>
                            @php
                                $badgeClass = match($order->service_type) {
                                    'cetak_foto' => 'background:#fff7ed;color:#c2410c;',
                                    'fotokopi' => 'background:#f0fdf4;color:#16a34a;',
                                    'laminating' => 'background:#eef2ff;color:#4f46e5;',
                                    'print' => 'background:#eff6ff;color:#2563eb;',
                                    default => 'background:#f3f4f6;color:#374151;',
                                };
                                $serviceLabel = $serviceTypes[$order->service_type] ?? $order->service_type;
                            @endphp
                            <span class="badge badge-status" style="{{ $badgeClass }}">{{ $serviceLabel }}</span>
                        </td>
                        <td class="fw-semibold">{{ number_format($order->quantity, 0, ',', '.') }} lbr</td>
                        <td>{{ rp($order->price_per_unit) }}</td>
                        <td class="fw-semibold">{{ rp($order->total) }}</td>
                        <td>{{ $order->description ?? '-' }}</td>
                        <td class="pe-3">
                            <button type="button" class="btn btn-modern btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit"
                                data-id="{{ $order->id }}"
                                data-date="{{ $order->date->format('Y-m-d') }}"
                                data-service-type="{{ $order->service_type }}"
                                data-quantity="{{ $order->quantity }}"
                                data-price-per-unit="{{ $order->price_per_unit }}"
                                data-description="{{ $order->description }}"
                                data-account-id="{{ $order->account_id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form autocomplete="off" action="{{ route('print-orders.destroy', $order->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="event.preventDefault(); confirmDelete('Hapus pesanan ini?').then(ok => ok && this.form.submit());">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada data pesanan jasa cetak</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ $orders->count() }} dari {{ $orders->total() }} data</span>
        </div>
        <div class="d-flex gap-4">
            <div>
                <span style="font-size:0.75rem;color:var(--text-muted);">Total Lembar</span>
                <span class="fw-bold ms-2" style="font-size:0.95rem;color:var(--text-primary);">{{ number_format($totalQty, 0, ',', '.') }}</span>
            </div>
            <div>
                <span style="font-size:0.75rem;color:var(--text-muted);">Total Pendapatan</span>
                <span class="fw-bold ms-2" style="font-size:0.95rem;color:var(--text-primary);">{{ rp($totalAmount) }}</span>
            </div>
        </div>
    </div>
    @if ($orders->hasPages())
    <div class="card-footer bg-white">
        <div class="pagination-modern">{{ $orders->links() }}</div>
    </div>
    @endif
</div>

{{-- Modal Tambah --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalTambah">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('print-orders.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Pesanan Jasa Cetak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Akun</label>
                    <select name="account_id" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ isset($defaultAccount) && $defaultAccount->id == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jenis Layanan</label>
                    <select name="service_type" class="form-select" required>
                        <option value="">Pilih Layanan</option>
                        @foreach($serviceTypes as $key => $label)
                        <option value="{{ $key }}" {{ $key == 'print' ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jumlah Lembar</label>
                        <input type="number" step="1" name="quantity" class="form-control" required min="1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Harga Satuan</label>
                        <input type="number" step="1" name="price_per_unit" class="form-control" required min="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Batal</button>
                <button type="submit" class="btn btn-modern btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalEdit">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formEdit">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Pesanan Jasa Cetak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" id="edit-date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Akun</label>
                    <select name="account_id" id="edit-account-id" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jenis Layanan</label>
                    <select name="service_type" id="edit-service-type" class="form-select" required>
                        <option value="">Pilih Layanan</option>
                        @foreach($serviceTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jumlah Lembar</label>
                        <input type="number" step="1" name="quantity" id="edit-quantity" class="form-control" required min="1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Harga Satuan</label>
                        <input type="number" step="1" name="price_per_unit" id="edit-price-per-unit" class="form-control" required min="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" id="edit-description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Batal</button>
                <button type="submit" class="btn btn-modern btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Bulk selection
document.querySelector('.bulk-select-all')?.addEventListener('change', function() {
    var checked = this.checked;
    document.querySelectorAll('.bulk-select-item').forEach(function(cb) {
        cb.checked = checked;
    });
    updateBulkBar();
});

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

$('#modalEdit').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#edit-date').val(button.data('date'));
    $('#edit-account-id').val(button.data('account-id'));
    $('#edit-service-type').val(button.data('service-type'));
    $('#edit-quantity').val(button.data('quantity'));
    $('#edit-price-per-unit').val(button.data('price-per-unit'));
    $('#edit-description').val(button.data('description'));
    $('#formEdit').attr('action', '{{ url("print-orders") }}/' + id);
});
</script>
@endpush
