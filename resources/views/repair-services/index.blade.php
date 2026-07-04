@extends('layouts.app')
@section('title', 'Jasa Servis')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Jasa Servis</h4>
    <div class="d-flex gap-2 page-header-actions">
        <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i>Tambah Service
        </button>
    </div>
</div>

<form autocomplete="off" method="GET" action="{{ route('repair-services.index') }}" class="row g-2 mb-4 filter-form">
    <div class="col-auto">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
    </div>
    <div class="col-auto">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
    </div>
    <div class="col-auto">
        <select name="device_type" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <option value="">Semua Device</option>
            @foreach($deviceTypes as $key => $label)
            <option value="{{ $key }}" {{ request('device_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari pelanggan..." style="width:180px;" oninput="clearTimeout(this._timer); this._timer=setTimeout(()=>this.form.submit(),500)">
    </div>
    <div class="col-auto">
        <a href="{{ route('repair-services.index') }}" class="btn btn-modern btn-secondary btn-sm"><i class="fas fa-times me-1"></i>Reset</a>
    </div>
</form>

<div class="bulk-action-bar mb-3 d-none" id="bulkActionBar">
    <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.08);border:1px solid rgba(var(--theme-primary-rgb),0.2);">
        <span class="fw-semibold" style="font-size:0.85rem;"><span id="bulkCount">0</span> dipilih</span>
        <span class="fw-bold" style="font-size:0.85rem;color:var(--theme-primary);" id="bulkTotal"></span>
        <form autocomplete="off" id="bulkDeleteForm" method="POST" action="{{ route('repair-services.bulk-delete') }}" style="display:inline;">
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
                        <th class="sortable" data-sort="string">Pelanggan</th>
                        <th class="sortable" data-sort="string">Device</th>
                        <th class="sortable" data-sort="string">Keluhan</th>
                        <th class="sortable" data-sort="number">Jasa</th>
                        <th class="sortable" data-sort="number">Sparepart</th>
                        <th class="sortable" data-sort="number">Total</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                    <tr>
                        <td class="ps-3">
                            <input type="checkbox" class="form-check-input bulk-select-item" value="{{ $service->id }}" data-amount="{{ $service->total }}">
                        </td>
                        <td>{{ tgl($service->date) }}</td>
                        <td>
                            <div class="fw-semibold">{{ $service->customer_name }}</div>
                            @if($service->customer_phone)
                            <small class="text-muted">{{ $service->customer_phone }}</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($service->device_type) {
                                    'hp' => 'background:#eff6ff;color:#2563eb;',
                                    'laptop' => 'background:#f0fdf4;color:#16a34a;',
                                    default => 'background:#f3f4f6;color:#374151;',
                                };
                                $deviceLabel = $deviceTypes[$service->device_type] ?? $service->device_type;
                            @endphp
                            <span class="badge badge-status" style="{{ $badgeClass }}">{{ $deviceLabel }}</span>
                            @if($service->device_model)
                            <br><small class="text-muted">{{ $service->device_model }}</small>
                            @endif
                        </td>
                        <td>
                            @if($service->issue_description)
                            <span style="font-size:0.85rem;">{{ Str::limit($service->issue_description, 40) }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ rp($service->service_fee) }}</td>
                        <td>
                            @if($service->sparepart_cost > 0)
                            <span>{{ rp($service->sparepart_cost) }}</span>
                            @if($service->sparepart_description)
                            <br><small class="text-muted">{{ $service->sparepart_description }}</small>
                            @endif
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ rp($service->total) }}</td>
                        <td class="pe-3">
                            <button type="button" class="btn btn-modern btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit"
                                data-id="{{ $service->id }}"
                                data-date="{{ $service->date->format('Y-m-d') }}"
                                data-customer-name="{{ $service->customer_name }}"
                                data-customer-phone="{{ $service->customer_phone }}"
                                data-device-type="{{ $service->device_type }}"
                                data-device-model="{{ $service->device_model }}"
                                data-issue-description="{{ $service->issue_description }}"
                                data-service-fee="{{ $service->service_fee }}"
                                data-sparepart-cost="{{ $service->sparepart_cost }}"
                                data-sparepart-description="{{ $service->sparepart_description }}"
                                data-account-id="{{ $service->account_id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form autocomplete="off" action="{{ route('repair-services.destroy', $service->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="event.preventDefault(); confirmDelete('Hapus data service ini?').then(ok => ok && this.form.submit());">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada data service</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ $services->count() }} dari {{ $services->total() }} data</span>
        </div>
        <div>
            <span style="font-size:0.75rem;color:var(--text-muted);">Total Pendapatan Service</span>
            <span class="fw-bold ms-2" style="font-size:0.95rem;color:var(--text-primary);">{{ rp($totalAmount) }}</span>
        </div>
    </div>
    @if ($services->hasPages())
    <div class="card-footer bg-white">
        <div class="pagination-modern">{{ $services->links() }}</div>
    </div>
    @endif
</div>

{{-- Modal Tambah --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalTambah">
    <div class="modal-dialog modal-lg">
        <form autocomplete="off" method="POST" action="{{ route('repair-services.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Akun</label>
                        <select name="account_id" class="form-select" required>
                            <option value="">Pilih Akun</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ isset($defaultAccount) && $defaultAccount->id == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Pelanggan</label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">No HP</label>
                        <input type="text" name="customer_phone" class="form-control" placeholder="08xxx">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipe Device</label>
                        <select name="device_type" class="form-select" required>
                            <option value="">Pilih</option>
                            @foreach($deviceTypes as $key => $label)
                            <option value="{{ $key }}" {{ $key == 'hp' ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Tipe / Brand</label>
                        <input type="text" name="device_model" class="form-control" placeholder="Contoh: Samsung A15, Lenovo Thinkpad">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keluhan</label>
                    <textarea name="issue_description" class="form-control" rows="2" placeholder="Deskripsi kerusakan..."></textarea>
                </div>
                <div class="border-top pt-3 mt-2">
                    <h6 class="fw-semibold mb-3" style="font-size:0.9rem;">Biaya</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Biaya Jasa</label>
                            <input type="number" step="1" name="service_fee" class="form-control" required min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Biaya Sparepart</label>
                            <input type="number" step="1" name="sparepart_cost" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total</label>
                            <input type="text" class="form-control" id="totalDisplay" readonly style="background:#f9fafb;font-weight:700;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan Sparepart</label>
                        <input type="text" name="sparepart_description" class="form-control" placeholder="Contoh: Ganti LCD, Ganti Baterai">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalEdit">
    <div class="modal-dialog modal-lg">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formEdit">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" id="edit-date" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Akun</label>
                        <select name="account_id" id="edit-account-id" class="form-select" required>
                            <option value="">Pilih Akun</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Pelanggan</label>
                        <input type="text" name="customer_name" id="edit-customer-name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">No HP</label>
                        <input type="text" name="customer_phone" id="edit-customer-phone" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipe Device</label>
                        <select name="device_type" id="edit-device-type" class="form-select" required>
                            <option value="">Pilih</option>
                            @foreach($deviceTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Tipe / Brand</label>
                        <input type="text" name="device_model" id="edit-device-model" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keluhan</label>
                    <textarea name="issue_description" id="edit-issue-description" class="form-control" rows="2"></textarea>
                </div>
                <div class="border-top pt-3 mt-2">
                    <h6 class="fw-semibold mb-3" style="font-size:0.9rem;">Biaya</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Biaya Jasa</label>
                            <input type="number" step="1" name="service_fee" id="edit-service-fee" class="form-control" required min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Biaya Sparepart</label>
                            <input type="number" step="1" name="sparepart_cost" id="edit-sparepart-cost" class="form-control" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total</label>
                            <input type="text" class="form-control" id="edit-total-display" readonly style="background:#f9fafb;font-weight:700;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan Sparepart</label>
                        <input type="text" name="sparepart_description" id="edit-sparepart-description" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-calculate total on create modal
document.querySelector('#modalTambah [name="service_fee"]')?.addEventListener('input', updateTotalCreate);
document.querySelector('#modalTambah [name="sparepart_cost"]')?.addEventListener('input', updateTotalCreate);

function updateTotalCreate() {
    var jasa = parseInt(document.querySelector('#modalTambah [name="service_fee"]').value) || 0;
    var spare = parseInt(document.querySelector('#modalTambah [name="sparepart_cost"]').value) || 0;
    document.getElementById('totalDisplay').value = 'Rp ' + (jasa + spare).toLocaleString('id-ID');
}

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
    $('#edit-customer-name').val(button.data('customer-name'));
    $('#edit-customer-phone').val(button.data('customer-phone'));
    $('#edit-device-type').val(button.data('device-type'));
    $('#edit-device-model').val(button.data('device-model'));
    $('#edit-issue-description').val(button.data('issue-description'));
    $('#edit-service-fee').val(button.data('service-fee'));
    $('#edit-sparepart-cost').val(button.data('sparepart-cost') || 0);
    $('#edit-sparepart-description').val(button.data('sparepart-description'));

    var jasa = parseInt(button.data('service-fee')) || 0;
    var spare = parseInt(button.data('sparepart-cost')) || 0;
    $('#edit-total-display').val('Rp ' + (jasa + spare).toLocaleString('id-ID'));

    $('#formEdit').attr('action', '{{ url("repair-services") }}/' + id);
});

// Auto-calculate total on edit modal
$('#modalEdit').on('change', '[name="service_fee"], [name="sparepart_cost"]', function() {
    var jasa = parseInt($('#edit-service-fee').val()) || 0;
    var spare = parseInt($('#edit-sparepart-cost').val()) || 0;
    $('#edit-total-display').val('Rp ' + (jasa + spare).toLocaleString('id-ID'));
});
</script>
@endpush
