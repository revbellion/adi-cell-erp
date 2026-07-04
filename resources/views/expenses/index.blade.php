@php
    $title = 'Pengeluaran';
    $systemCategories = array_column(config('categories.expense.system'), 'key');
@endphp
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Pengeluaran</h4>
        <div class="page-header-actions">
            <a href="{{ route('expenses.export', request()->only(['date_from', 'date_to', 'category', 'type'])) }}" class="btn btn-modern btn-success btn-sm me-1">
                <i class="fas fa-file-excel me-1"></i>Export
            </a>
            <button type="button" class="btn btn-modern btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPengeluaran">
                <i class="fas fa-plus me-1"></i>Tambah
            </button>
        </div>
    </div>

<form method="GET" action="{{ route('expenses.index') }}" autocomplete="off" class="row g-2 mb-3 align-items-center filter-form">
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
        <a href="{{ route('expenses.index') }}" class="btn btn-modern btn-secondary btn-sm"><i class="fas fa-times me-1"></i>Reset</a>
    </div>
</form>

<div class="bulk-action-bar mb-3 d-none" id="bulkActionBar">
    <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.08);border:1px solid rgba(var(--theme-primary-rgb),0.2);">
        <span class="fw-semibold" style="font-size:0.85rem;"><span id="bulkCount">0</span> dipilih</span>
        <span class="fw-bold" style="font-size:0.85rem;color:var(--theme-primary);" id="bulkTotal"></span>
        <form autocomplete="off" method="POST" action="{{ route('expenses.bulk-delete') }}" style="display:inline;" id="bulkDeleteForm">
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
                @forelse($expenses as $expense)
                @php
                    $isSystem = in_array($expense->category, $systemCategories) || $expense->stock_transaction_id || $expense->billPayment;
                @endphp
                <tr>
                    <td class="ps-3">
                        @if(!$isSystem)
                        <input type="checkbox" class="form-check-input bulk-select-item" value="{{ $expense->id }}" data-amount="{{ $expense->amount }}">
                        @endif
                    </td>
                    <td>{{ tgl($expense->date) }}</td>
                    <td>{{ $expense->account->name ?? '-' }}</td>
                    <td>
                        <span class="badge badge-status" style="background:#eff6ff;color:var(--theme-primary);">{{ $expense->category ?? '-' }}</span>
                    </td>
                    <td class="fw-semibold">{{ rp($expense->amount) }}</td>
                    <td>{{ $expense->description ?? '-' }}</td>
                    <td class="pe-3">
                        @if(!$isSystem)
                        <button type="button" class="btn btn-modern btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditPengeluaran"
                            data-id="{{ $expense->id }}"
                            data-date="{{ $expense->date->format('Y-m-d') }}"
                            data-account_id="{{ $expense->account_id }}"
                            data-category="{{ $expense->category }}"
                            data-amount="{{ $expense->amount }}"
                            data-description="{{ $expense->description }}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form autocomplete="off" action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="event.preventDefault(); confirmDelete('Hapus pengeluaran ini?').then(ok => ok && this.form.submit());">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @else
                        <span class="badge bg-secondary" style="font-size:0.65rem;">{{ $expense->category }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pengeluaran.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div>
            <span style="font-size:0.8rem;color:var(--text-muted);">Menampilkan {{ $expenses->firstItem() ?? 0 }}–{{ $expenses->lastItem() ?? 0 }} dari {{ $expenses->total() }} · Total <strong>{{ rp($totalAmount) }}</strong></span>
        </div>
        <div>{{ $expenses->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalTambahPengeluaran">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('expenses.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Pengeluaran</h5>
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
                        <optgroup label="Biaya Real">
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
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalEditPengeluaran">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formEditPengeluaran">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" id="edit-expense-date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Akun</label>
                    <select name="account_id" id="edit-expense-account_id" class="form-select" required>
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category" id="edit-expense-category" class="form-select" required>
                        <option value="">Pilih Kategori</option>
                        <optgroup label="Biaya Real">
                            @foreach($userCategories as $cat)
                            <option value="{{ $cat['key'] }}">{{ $cat['key'] }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nominal</label>
                    <input type="number" step="1" name="amount" id="edit-expense-amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" id="edit-expense-description" class="form-control" rows="2"></textarea>
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

// Modal Edit — isi data
document.querySelectorAll('[data-bs-target="#modalEditPengeluaran"]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        document.getElementById('formEditPengeluaran').action = '{{ route("expenses.index") }}/' + id;
        document.getElementById('edit-expense-date').value = this.dataset.date;
        document.getElementById('edit-expense-account_id').value = this.dataset.account_id;
        document.getElementById('edit-expense-category').value = this.dataset.category;
        document.getElementById('edit-expense-amount').value = this.dataset.amount;
        document.getElementById('edit-expense-description').value = this.dataset.description || '';
    });
});
</script>
@endpush
