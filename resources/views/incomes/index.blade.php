@extends('layouts.app')
@section('title', 'Pendapatan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Pendapatan</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('incomes.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-modern btn-success">
            <i class="fas fa-file-excel me-1"></i>Export
        </a>
        <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPendapatan">
            <i class="fas fa-plus me-1"></i>Tambah Pendapatan
        </button>
    </div>
</div>

<form method="GET" action="{{ route('incomes.index') }}" class="row g-2 mb-4">
    <div class="col-auto">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" style="width:auto;">
    </div>
    <div class="col-auto">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" style="width:auto;">
    </div>
    <div class="col-auto">
        <select name="category" class="form-select form-select-sm" style="width:auto;">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari..." style="width:150px;">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-modern btn-primary btn-sm"><i class="fas fa-search me-1"></i>Cari</button>
                <a href="{{ route('incomes.index') }}" class="btn btn-modern btn-secondary btn-sm"><i class="fas fa-times me-1"></i>Reset</a>
    </div>
</form>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Tanggal</th>
                        <th>Akun</th>
                        <th>Kategori</th>
                        <th>Nominal</th>
                        <th>Keterangan</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incomes as $income)
                    <tr>
                        <td class="ps-3">{{ tgl($income->date) }}</td>
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
                            <button type="button" class="btn btn-modern btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditPendapatan"
                                data-id="{{ $income->id }}"
                                data-date="{{ $income->date->format('Y-m-d') }}"
                                data-category="{{ $income->category }}"
                                data-amount="{{ $income->amount }}"
                                data-description="{{ $income->description }}"
                                data-account-id="{{ $income->account_id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('incomes.destroy', $income->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="return confirm('Hapus pendapatan ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada data pendapatan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($incomes->hasPages())
    <div class="card-footer bg-white">
        <div class="pagination-modern">{{ $incomes->links() }}</div>
    </div>
    @endif
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalTambahPendapatan">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('incomes.store') }}" class="modal-content">
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
                    <input type="text" name="category" class="form-control" list="income-category-list">
                    <datalist id="income-category-list">
                        @foreach($categories as $category)
                        <option value="{{ $category }}">
                        @endforeach
                    </datalist>
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

<div class="modal fade modal-modern" tabindex="-1" id="modalEditPendapatan">
    <div class="modal-dialog">
        <form method="POST" action="" class="modal-content" id="formEditPendapatan">
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
                    <input type="text" name="category" id="edit-income-category" class="form-control" list="income-category-list-edit">
                    <datalist id="income-category-list-edit">
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
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
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#modalEditPendapatan').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#edit-income-date').val(button.data('date'));
    $('#edit-income-account').val(button.data('account-id'));
    $('#edit-income-category').val(button.data('category'));
    $('#edit-income-amount').val(button.data('amount'));
    $('#edit-income-description').val(button.data('description'));
    $('#formEditPendapatan').attr('action', '{{ url("incomes") }}/' + id);
});
</script>
@endpush
