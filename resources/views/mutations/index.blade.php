@extends('layouts.app')
@section('title', 'Mutasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Mutasi</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('mutations.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-modern btn-success">
            <i class="fas fa-file-excel me-1"></i>Export
        </a>
        <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahMutasi">
            <i class="fas fa-plus me-1"></i>Tambah Mutasi
        </button>
    </div>
</div>

<form autocomplete="off" method="GET" action="{{ route('mutations.index') }}" class="row g-2 mb-4 filter-form">
    <div class="col-auto">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" style="width:auto;">
    </div>
    <div class="col-auto">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" style="width:auto;">
    </div>
    <div class="col-auto">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari..." style="width:150px;">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-modern btn-primary btn-sm"><i class="fas fa-search me-1"></i>Cari</button>
        <a href="{{ route('mutations.index') }}" class="btn btn-modern btn-secondary btn-sm"><i class="fas fa-times me-1"></i>Reset</a>
    </div>
</form>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Tanggal</th>
                        <th>Dari</th>
                        <th>Ke</th>
                        <th>Nominal</th>
                        <th>Keterangan</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutations as $mutasi)
                    <tr>
                        <td class="ps-3">{{ tgl($mutasi->date) }}</td>
                        <td>{{ $mutasi->fromAccount->name ?? '-' }}</td>
                        <td>{{ $mutasi->toAccount->name ?? '-' }}</td>
                        <td class="fw-semibold">{{ rp($mutasi->amount) }}</td>
                        <td>{{ $mutasi->description ?? '-' }}</td>
                        <td class="pe-3">
                            <button type="button" class="btn btn-modern btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditMutasi"
                                data-id="{{ $mutasi->id }}"
                                data-date="{{ $mutasi->date->format('Y-m-d') }}"
                                data-from_account_id="{{ $mutasi->from_account_id }}"
                                data-to_account_id="{{ $mutasi->to_account_id }}"
                                data-amount="{{ $mutasi->amount }}"
                                data-description="{{ $mutasi->description }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form autocomplete="off" action="{{ route('mutations.destroy', $mutasi->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="event.preventDefault(); confirmDelete('Hapus mutasi ini?').then(ok => ok && this.form.submit());">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada data mutasi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ $mutations->count() }} dari {{ $mutations->total() }} data</span>
        </div>
        <div>
            <span style="font-size:0.75rem;color:var(--text-muted);">Total Mutasi</span>
            <span class="fw-bold ms-2" style="font-size:0.95rem;color:var(--text-primary);">{{ rp($totalAmount) }}</span>
        </div>
    </div>
    @if ($mutations->hasPages())
    <div class="card-footer bg-white">
        <div class="pagination-modern">{{ $mutations->links() }}</div>
    </div>
    @endif
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalTambahMutasi">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('mutations.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Mutasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dari Akun</label>
                    <select name="from_account_id" id="add-mutation-from" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ke Akun</label>
                    <select name="to_account_id" id="add-mutation-to" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nominal</label>
                    <input type="number" step="1" name="amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalEditMutasi">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formEditMutasi">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Mutasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" id="edit-mutation-date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dari Akun</label>
                    <select name="from_account_id" id="edit-mutation-from" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ke Akun</label>
                    <select name="to_account_id" id="edit-mutation-to" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nominal</label>
                    <input type="number" step="1" name="amount" id="edit-mutation-amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" id="edit-mutation-description" class="form-control" rows="2"></textarea>
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
// Fungsi untuk dropdown exclusion
function setupDropdownExclusion(fromId, toId) {
    var fromSelect = document.getElementById(fromId);
    var toSelect = document.getElementById(toId);
    
    if (fromSelect) {
        fromSelect.addEventListener('change', function() {
            var selected = this.value;
            toSelect.querySelectorAll('option').forEach(function(opt) {
                if (opt.value === selected || opt.value === '') {
                    opt.style.display = 'none';
                } else {
                    opt.style.display = '';
                }
            });
            if (toSelect.value === selected) {
                toSelect.value = '';
            }
        });
    }
    
    if (toSelect) {
        toSelect.addEventListener('change', function() {
            var selected = this.value;
            fromSelect.querySelectorAll('option').forEach(function(opt) {
                if (opt.value === selected || opt.value === '') {
                    opt.style.display = 'none';
                } else {
                    opt.style.display = '';
                }
            });
            if (fromSelect.value === selected) {
                fromSelect.value = '';
            }
        });
    }
}

// Setup untuk modalTambahMutasi
setupDropdownExclusion('add-mutation-from', 'add-mutation-to');

// Setup untuk modalEditMutasi
setupDropdownExclusion('edit-mutation-from', 'edit-mutation-to');

// Reset dropdown saat modal edit dibuka
$('#modalEditMutasi').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    var fromId = button.data('from_account_id');
    var toId = button.data('to_account_id');
    
    // Reset semua opsi visible dulu
    $('#edit-mutation-from option, #edit-mutation-to option').each(function() {
        $(this).show();
    });
    
    // Set nilai
    $('#edit-mutation-date').val(button.data('date'));
    $('#edit-mutation-from').val(fromId);
    $('#edit-mutation-to').val(toId);
    $('#edit-mutation-amount').val(button.data('amount'));
    $('#edit-mutation-description').val(button.data('description'));
    $('#formEditMutasi').attr('action', '{{ url("mutations") }}/' + id);
    
    // Trigger exclusion
    $('#edit-mutation-from').trigger('change');
});
</script>
@endpush
