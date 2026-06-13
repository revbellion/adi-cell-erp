@extends('layouts.app')
@section('title', 'Tagihan Bulanan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0">Tagihan Bulanan</h4>
    <div class="d-flex gap-2 align-items-center">
        <form autocomplete="off" method="GET" class="d-flex gap-2 align-items-center" id="form-periode">
            <a href="{{ url('bills?period=' . \Carbon\Carbon::parse($period . '-01')->subMonth()->format('Y-m')) }}" class="btn btn-modern btn-outline-secondary btn-sm">
                <i class="fas fa-chevron-left"></i>
            </a>
            <input type="month" name="period" value="{{ $period }}" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
            <a href="{{ url('bills?period=' . \Carbon\Carbon::parse($period . '-01')->addMonth()->format('Y-m')) }}" class="btn btn-modern btn-outline-secondary btn-sm">
                <i class="fas fa-chevron-right"></i>
            </a>
        </form>
        <button type="button" class="btn btn-modern btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahTagihan">
            <i class="fas fa-plus"></i> Tambah
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@forelse($bills as $bill)
@php
$overdue = !$bill->is_paid && (int)$bill->due_day < (int)now()->format('d');
@endphp
<div class="card card-modern mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fw-semibold">{{ $bill->name }}</span>
                    @if($bill->is_paid)
                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:0.6rem;">Lunas</span>
                    @elseif($overdue)
                    <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:0.6rem;">Telat</span>
                    @else
                    <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:0.6rem;">Belum</span>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-3 small text-muted">
                    <span><i class="fas fa-tag me-1"></i>{{ $bill->category ?? '-' }}</span>
                    <span><i class="fas fa-calendar me-1"></i>Jatuh tempo {{ $bill->due_day_text }}</span>
                    @if($bill->account)
                    <span><i class="fas fa-wallet me-1"></i>{{ $bill->account->name }}</span>
                    @endif
                    <span class="fw-semibold">{{ rp($bill->amount) }}</span>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center flex-shrink-0">
                @if(!$bill->is_paid)
                <button type="button" class="btn btn-modern btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalBayarTagihan"
                    data-id="{{ $bill->id }}"
                    data-name="{{ $bill->name }}"
                    data-amount="{{ intval($bill->amount) }}"
                    data-account-id="{{ $bill->account_id }}"
                    data-action="{{ route('bills.pay', $bill->id) }}">
                    <i class="fas fa-check"></i> Bayar
                </button>
                @else
                <span class="text-success small">
                    <i class="fas fa-check-circle"></i> {{ \Carbon\Carbon::parse($bill->payment->paid_at)->format('d/m/Y H:i') }}
                </span>
                @endif
                <button type="button" class="btn btn-modern btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditTagihan"
                    data-id="{{ $bill->id }}"
                    data-name="{{ $bill->name }}"
                    data-category="{{ $bill->category }}"
                    data-account-id="{{ $bill->account_id }}"
                    data-amount="{{ intval($bill->amount) }}"
                    data-due-day="{{ $bill->due_day }}"
                    data-is-active="{{ $bill->is_active ? '1' : '0' }}">
                    <i class="fas fa-edit"></i>
                </button>
                <form autocomplete="off" action="{{ route('bills.destroy', $bill->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="return confirm('Hapus tagihan {{ $bill->name }} beserta riwayat pembayarannya?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@empty
<div class="card card-modern">
    <div class="card-body text-center text-muted py-5">
        <i class="fas fa-file-invoice fs-1 mb-3 d-block"></i>
        <p class="mb-0">Belum ada tagihan. Tambah tagihan rutin seperti listrik, wifi, dll.</p>
        <button type="button" class="btn btn-modern btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalTambahTagihan">
            <i class="fas fa-plus"></i> Tambah Tagihan
        </button>
    </div>
</div>
@endforelse

<!-- Modal Tambah -->
<div class="modal fade modal-modern" tabindex="-1" id="modalTambahTagihan">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('bills.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Tagihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Tagihan</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. PLN, WiFi Indihome, BPJS">
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="category" class="form-control" list="bill-category-list">
                    <datalist id="bill-category-list">
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="mb-3">
                    <label class="form-label">Akun Default</label>
                    <select name="account_id" class="form-select">
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Bisa diganti pas bayar nanti</small>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Nominal</label>
                        <input type="number" step="1" name="amount" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Jatuh Tempo (Tgl)</label>
                        <input type="number" step="1" name="due_day" class="form-control" required min="1" max="31" placeholder="20">
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

<!-- Modal Edit -->
<div class="modal fade modal-modern" tabindex="-1" id="modalEditTagihan">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formEditTagihan">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Tagihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Tagihan</label>
                    <input type="text" name="name" id="edit-name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="category" id="edit-category" class="form-control" list="bill-category-list-edit">
                    <datalist id="bill-category-list-edit">
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="mb-3">
                    <label class="form-label">Akun Default</label>
                    <select name="account_id" id="edit-account" class="form-select">
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Nominal</label>
                        <input type="number" step="1" name="amount" id="edit-amount" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Jatuh Tempo (Tgl)</label>
                        <input type="number" step="1" name="due_day" id="edit-due-day" class="form-control" required min="1" max="31">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="edit-is-active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="edit-is-active">Aktif</label>
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

<!-- Modal Bayar -->
<div class="modal fade modal-modern" tabindex="-1" id="modalBayarTagihan">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formBayarTagihan" novalidate>
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Bayar Tagihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="period" value="{{ $period }}">
                <div class="mb-3">
                    <label class="form-label">Tagihan</label>
                    <p class="fw-semibold mb-0" id="bayar-nama"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Periode</label>
                    <input type="month" name="period_display" value="{{ $period }}" class="form-control" disabled>
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
                    <label class="form-label">Nominal</label>
                    <input type="number" step="1" name="amount" id="bayar-nominal" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-success"><i class="fas fa-check"></i> Konfirmasi Bayar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#modalEditTagihan').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#formEditTagihan').attr('action', '{{ url("bills") }}/' + id);
    $('#edit-name').val(button.data('name'));
    $('#edit-category').val(button.data('category') || '');
    $('#edit-account').val(button.data('account-id') || '');
    $('#edit-amount').val(button.data('amount'));
    $('#edit-due-day').val(button.data('due-day'));
    $('#edit-is-active').prop('checked', button.data('is-active') == 1);
});

$('#modalBayarTagihan').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    var name = button.data('name');
    var amount = button.data('amount');
    var accountId = button.data('account-id');
    $('#formBayarTagihan').attr('action', button.data('action'));
    $('#bayar-nama').text(name);
    $('#bayar-nominal').val(amount);
    $('#formBayarTagihan select[name="account_id"]').val(accountId || '');
});
</script>
@endpush
