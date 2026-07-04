@extends('layouts.app')
@section('title', 'Riwayat ' . $customer->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">Riwayat Transaksi</h4>
        <p class="text-muted mb-0" style="font-size:0.8rem;">
            <a href="{{ route('customers.index') }}" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Pelanggan</a>
            <span class="mx-2">/</span>
            {{ $customer->name }}
        </p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('receivables.index', ['search' => $customer->name]) }}" class="btn btn-modern btn-primary btn-sm">
            <i class="fas fa-file-invoice-dollar me-1"></i>Lihat Piutang
        </a>
    </div>
</div>

{{-- Info Pelanggan --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3" style="font-size:0.85rem;color:var(--text-muted);">DATA PELANGGAN</h6>
                <table style="font-size:0.9rem;">
                    <tr>
                        <td class="text-muted pe-3" style="white-space:nowrap;">Nama</td>
                        <td class="fw-semibold">{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pe-3">Telepon</td>
                        <td>{{ $customer->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pe-3">Email</td>
                        <td>{{ $customer->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pe-3" style="vertical-align:top;">Alamat</td>
                        <td>{{ $customer->address ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card card-modern shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Total Piutang</div>
                        <div class="fw-bold mt-1" style="font-size:1.1rem;color:var(--theme-primary);">{{ rp($totalPiutang) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-modern shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Sudah Dibayar</div>
                        <div class="fw-bold mt-1" style="font-size:1.1rem;color:#10b981;">{{ rp($totalPaid) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-modern shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Sisa Piutang</div>
                        <div class="fw-bold mt-1" style="font-size:1.1rem;color:{{ $sisaPiutang > 0 ? '#ef4444' : '#10b981' }};">{{ rp($sisaPiutang) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Riwayat Transaksi --}}
<div class="card card-modern shadow-sm">
    <div class="card-header bg-transparent py-2">
        <span class="fw-semibold" style="font-size:0.85rem;">Riwayat Piutang & Pembayaran</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3 sortable" data-sort="date">Tanggal</th>
                        <th>Jatuh Tempo</th>
                        <th>Piutang</th>
                        <th>Sudah Dibayar</th>
                        <th>Sisa</th>
                        <th>Status</th>
                        <th class="pe-3">Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivables as $receivable)
                    <tr class="{{ $receivable->status === 'unpaid' && $receivable->due_date?->startOfDay() < now()->startOfDay() ? 'table-danger' : ($receivable->status === 'paid' ? '' : '') }}">
                        <td class="ps-3">{{ tgl($receivable->date) }}</td>
                        <td>{{ tgl($receivable->due_date) }}</td>
                        <td class="fw-semibold">{{ rp($receivable->amount) }}</td>
                        <td>{{ rp($receivable->receivablePayments->sum('amount')) }}</td>
                        <td class="fw-bold {{ $receivable->remaining > 0 ? 'text-danger' : 'text-success' }}">{{ rp($receivable->remaining) }}</td>
                        <td>{!! $receivable->status_badge !!}</td>
                        <td class="pe-3">
                            @if($receivable->receivablePayments->count() > 0)
                                <button type="button" class="btn btn-modern btn-info btn-sm"
                                    data-bs-toggle="popover"
                                    data-bs-trigger="focus"
                                    title="Pembayaran"
                                    data-bs-html="true"
                                    data-bs-content="@foreach($receivable->receivablePayments as $pay){{ tgl($pay->date) }}: {{ rp($pay->amount) }}<br>@endforeach">
                                    <i class="fas fa-list"></i> {{ $receivable->receivablePayments->count() }}x
                                </button>
                            @else
                                <span class="text-muted" style="font-size:0.8rem;">Belum ada</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada transaksi piutang</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($receivables->hasPages())
<div class="d-flex justify-content-center mt-3">
    {{ $receivables->links() }}
</div>
@endif

@if($customer->notes)
<div class="card card-modern shadow-sm mt-3">
    <div class="card-body">
        <h6 class="fw-bold mb-2" style="font-size:0.8rem;color:var(--text-muted);">CATATAN</h6>
        <p class="mb-0" style="font-size:0.9rem;">{{ $customer->notes }}</p>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (el) {
        return new bootstrap.Popover(el, { container: 'body' });
    });
});
</script>
@endpush
