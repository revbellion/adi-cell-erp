@php
    $title = 'Pendapatan';
    $systemCategories = array_column(config('categories.income.system'), 'key');
@endphp
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Pendapatan</h4>
        <div class="page-header-actions">
            <a href="{{ route('incomes.export', request()->only(['date_from', 'date_to', 'category', 'type'])) }}" class="btn btn-modern btn-success btn-sm me-1">
                <i class="fas fa-file-excel me-1"></i>Export
            </a>
            <button type="button" class="btn btn-modern btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPendapatan">
                <i class="fas fa-plus me-1"></i>Tambah
            </button>
        </div>
    </div>

<form method="GET" action="{{ route('incomes.index') }}" autocomplete="off" class="row g-2 mb-3 align-items-center filter-form">
    <div class="col-auto">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
    </div>
    <div class="col-auto">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
    </div>
    <div class="col-auto">
        <select name="category" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari..." style="width:150px;" oninput=" clearTimeout(this._timer); this._timer=setTimeout(()=>this.form.submit(),500)">
    </div>
    <div class="col-auto">
        <a href="{{ route('incomes.index') }}" class="btn btn-modern btn-secondary btn-sm"><i class="fas fa-times me-1"></i>Reset</a>
    </div>
</form>

<div class="bulk-action-bar mb-3 d-none" id="bulkActionBar">
    <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.08);border:1px solid rgba(var(--theme-primary-rgb),0.2);">
        <span class="fw-semibold" style="font-size:0.85rem;"><span id="bulkCount">0</span> dipilih</span>
        <span class="fw-bold" style="font-size:0.85rem;color:var(--theme-primary);" id="bulkTotal"></span>
        <form autocomplete="off" method="POST" action="{{ route('incomes.bulk-delete') }}" style="display:inline;" id="bulkDeleteForm">
            @csrf
            <button type="submit" class="btn btn-modern btn-danger btn-sm"><i class="fas fa-trash me-1"></i>Hapus</button>
        </form>
        <button type="button" class="btn btn-modern btn-secondary btn-sm" onclick="clearBulkSelection()"><i class="fas fa-times me-1"></i>Batal</button>
    </div>
</div>

<div class="card card-modern">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead>
                <tr>
                    <th class="ps-3" style="width:40px;"><input type="checkbox" class="form-check-input bulk-select-all"></th>
                    <th class="sortable" data-sort="string">Tanggal</th>
                    <th class="sortable" data-sort="string">Akun</th>
                    <th class="sortable" data-sort="string">Kategori</th>
                    <th class="sortable" data-sort="number">Nominal</th>
                    <th class="sortable" data-sort="string">Keterangan</th>
                    <th class="pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incomes as $income)
                @php
                    $isSystem = in_array($income->category, $systemCategories) || $income->stock_transaction_id;
                @endphp
                <tr>
                    <td class="ps-3">
                        @if(!$isSystem)
                        <input type="checkbox" class="form-check-input bulk-select-item" value="{{ $income->id }}" data-amount="{{ $income->amount }}">
                        @endif
                    </td>
                    <td>{{ tgl($income->date) }}</td>
                    <td>{{ $income->account->name ?? '-' }}</td>
                    <td>
                        @if($income->category)
                        <span class="badge badge-status" style="background:#f0fdf4;color:#16a34a;">{{ $income->category }}</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="fw-semibold">{{ rp($income->amount) }}</td>
                    <td>{{ $income->description ?? '-' }}</td>
                    <td class="pe-3">
                        @if(!$isSystem)
                        <button type="button" class="btn btn-modern btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditPendapatan"
                            data-id="{{ $income->id }}"
                            data-date="{{ $income->date->format('Y-m-d') }}"
                            data-category="{{ $income->category }}"
                            data-amount="{{ $income->amount }}"
                            data-description="{{ $income->description }}"
                            data-account-id="{{ $income->account_id }}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form autocomplete="off" action="{{ route('incomes.destroy', $income->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="event.preventDefault(); confirmDelete('Hapus pendapatan ini?').then(ok => ok && this.form.submit());">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @else
                        <span class="badge bg-secondary" style="font-size:0.65rem;">{{ $income->category }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pendapatan.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div>
            <span style="font-size:0.8rem;color:var(--text-muted);">Menampilkan {{ $incomes->firstItem() ?? 0 }}–{{ $incomes->lastItem() ?? 0 }} dari {{ $incomes->total() }} · Total <strong>{{ rp($totalAmount) }}</strong></span>
        </div>
        <div>{{ $incomes->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalTambahPendapatan">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('incomes.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Pendapatan</h5>
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
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-select" required>
                        <option value="">Pilih Kategori</option>
                        <optgroup label="Pendapatan Real">
                            @foreach($userCategories as $cat)
                            <option value="{{ $cat['key'] }}">{{ $cat['key'] }}</option>
                            @endforeach
                        </optgroup>
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
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Batal</button>
                <button type="submit" class="btn btn-modern btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalEditPendapatan">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formEditPendapatan">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Pendapatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" id="edit-income-date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Akun</label>
                    <select name="account_id" id="edit-income-account" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category" id="edit-income-category" class="form-select" required>
                        <option value="">Pilih Kategori</option>
                        <optgroup label="Pendapatan Real">
                            @foreach($userCategories as $cat)
                            <option value="{{ $cat['key'] }}">{{ $cat['key'] }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nominal</label>
                    <input type="number" step="1" name="amount" id="edit-income-amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" id="edit-income-description" class="form-control" rows="2"></textarea>
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
document.querySelectorAll('.bulk-select-all').forEach(function(el) {
    el.addEventListener('change', function() {
        document.querySelectorAll('.bulk-select-item').forEach(function(cb) { cb.checked = this.checked; }, this);
        updateBulkBar();
    });
});
document.querySelectorAll('.bulk-select-item').forEach(function(el) {
    el.addEventListener('change', updateBulkBar);
});
function updateBulkBar() {
    var checked = document.querySelectorAll('.bulk-select-item:checked');
    var bar = document.getElementById('bulkActionBar');
    if (!bar) return;
    if (checked.length) {
        bar.classList.remove('d-none');
        document.getElementById('bulkCount').textContent = checked.length;
        var total = 0;
        checked.forEach(function(cb) { total += parseInt(cb.dataset.amount || 0); });
        document.getElementById('bulkTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
        var form = document.getElementById('bulkDeleteForm');
        form.querySelectorAll('input[name="ids[]"]').forEach(function(el) { el.remove(); });
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
            form.appendChild(inp);
        });
    } else {
        bar.classList.add('d-none');
    }
}
function clearBulkSelection() {
    document.querySelectorAll('.bulk-select-item, .bulk-select-all').forEach(function(el) { el.checked = false; });
    updateBulkBar();
}

// Modal Edit
document.querySelectorAll('[data-bs-target="#modalEditPendapatan"]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        document.getElementById('formEditPendapatan').action = '{{ route("incomes.index") }}/' + id;
        document.getElementById('edit-income-date').value = this.dataset.date;
        document.getElementById('edit-income-category').value = this.dataset.category;
        setMoney(document.getElementById('edit-income-amount'), this.dataset.amount);
        document.getElementById('edit-income-description').value = this.dataset.description || '';
        document.getElementById('edit-income-account').value = this.dataset.accountId;
    });
});
</script>
@endpush
