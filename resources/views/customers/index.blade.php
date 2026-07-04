@extends('layouts.app')
@section('title', 'Pelanggan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-1">
    <div>
        <h4 class="fw-bold mb-1">Pelanggan</h4>
        <p class="text-muted mb-0" style="font-size:0.8rem;">Database pelanggan untuk mencatat piutang dan riwayat transaksi</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i>Tambah Pelanggan
        </button>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('customers.index') }}" class="row g-2 mb-4 filter-form">
    <div class="col-md-5">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama, telepon, email..." value="{{ $filters['search'] ?? '' }}" onkeyup="delaySubmit(this.form, 500)">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
        </select>
    </div>
    <div class="col-md-2">
        <a href="{{ route('customers.index') }}" class="btn btn-modern btn-secondary btn-sm w-100">Reset</a>
    </div>
</form>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Nama</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr class="{{ !$customer->is_active ? 'table-warning' : '' }}">
                        <td class="ps-3 fw-semibold">
                            <a href="{{ route('customers.history', $customer->id) }}" class="text-decoration-none">
                                {{ $customer->name }}
                            </a>
                        </td>
                        <td>{{ $customer->phone ?? '-' }}</td>
                        <td>{{ $customer->email ?? '-' }}</td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $customer->address ?? '-' }}</td>
                        <td>
                            @if($customer->is_active)
                                <span class="badge badge-status" style="background:#ecfdf5;color:#10b981;">Aktif</span>
                            @else
                                <span class="badge badge-status" style="background:#fef2f2;color:#ef4444;">Nonaktif</span>
                            @endif
                        </td>
                        <td class="pe-3">
                            <button type="button" class="btn btn-modern btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalEdit"
                                data-id="{{ $customer->id }}"
                                data-name="{{ $customer->name }}"
                                data-phone="{{ $customer->phone }}"
                                data-email="{{ $customer->email }}"
                                data-address="{{ $customer->address }}"
                                data-notes="{{ $customer->notes }}">
                                <i class="fas fa-edit"></i>
                            </button>

                            @if($customer->is_active)
                            <form autocomplete="off" action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm"
                                    onclick="event.preventDefault(); confirmDelete('Nonaktifkan pelanggan {{ $customer->name }}?').then(ok => ok && this.form.submit());">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            @endif

                            <a href="{{ route('customers.history', $customer->id) }}" class="btn btn-modern btn-info btn-sm">
                                <i class="fas fa-history"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada pelanggan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div>
            <span style="font-size:0.8rem;color:var(--text-muted);">Total {{ $totalCustomers }} pelanggan</span>
        </div>
        <div>
            <span style="font-size:0.75rem;color:var(--text-muted);">Aktif</span>
            <span class="fw-bold ms-2" style="font-size:0.95rem;color:var(--text-primary);">{{ $totalActive }}</span>
        </div>
    </div>
</div>

@if($customers->hasPages())
<div class="d-flex justify-content-center mt-3">
    {{ $customers->links() }}
</div>
@endif

{{-- Modal Tambah --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalTambah">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('customers.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nomor Telepon</label>
                    <input type="text" name="phone" class="form-control" placeholder="08xxx">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
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
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formEdit">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="edit-name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nomor Telepon</label>
                    <input type="text" name="phone" id="edit-phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="edit-email" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" id="edit-address" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" id="edit-notes" class="form-control" rows="2"></textarea>
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
$('#modalEdit').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#formEdit').attr('action', '/customers/' + id);
    $('#edit-name').val(button.data('name'));
    $('#edit-phone').val(button.data('phone'));
    $('#edit-email').val(button.data('email'));
    $('#edit-address').val(button.data('address'));
    $('#edit-notes').val(button.data('notes'));
});

function delaySubmit(form, ms) {
    clearTimeout(window._searchTimer);
    window._searchTimer = setTimeout(function() { form.submit(); }, ms);
}
</script>
@endpush
