@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Laporan Laba Rugi</h4>
        <p class="text-muted mb-0" style="font-size:0.8rem;">Pendapatan, HPP, dan biaya operasional per periode</p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('reports.profit-loss') }}" class="row g-2 mb-4 filter-form">
    <div class="col-auto">
        <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
            @foreach($availablePeriods as $p)
            <option value="{{ $p }}" {{ $selectedPeriod === $p ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromFormat('Y-m', $p)->translatedFormat('F Y') }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <a href="{{ route('reports.profit-loss') }}" class="btn btn-modern btn-secondary btn-sm"><i class="fas fa-calendar-alt me-1"></i>Bulan Ini</a>
    </div>
</form>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Total Pendapatan</div>
                <div class="fw-bold mt-1" style="font-size:1.2rem;color:var(--theme-primary);">{{ rp($summary['total_revenue']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">HPP</div>
                <div class="fw-bold mt-1" style="font-size:1.2rem;color:#f59e0b;">{{ rp($summary['total_hpp']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Laba Kotor</div>
                <div class="fw-bold mt-1" style="font-size:1.2rem;color:{{ $summary['laba_kotor'] >= 0 ? '#10b981' : '#ef4444' }};">{{ rp($summary['laba_kotor']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Laba Bersih</div>
                <div class="fw-bold mt-1" style="font-size:1.2rem;color:{{ $summary['laba_bersih'] >= 0 ? '#10b981' : '#ef4444' }};">{{ rp($summary['laba_bersih']) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Laporan Detail --}}
<div class="card card-modern shadow-sm mb-4">
    <div class="card-header bg-transparent py-2">
        <span class="fw-semibold" style="font-size:0.85rem;">
            Laporan Laba Rugi — {{ \Carbon\Carbon::parse($dateStart)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($dateEnd)->translatedFormat('d F Y') }}
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <tbody>
                    {{-- ═══ PENDAPATAN ═══ --}}
                    <tr class="table-active">
                        <td class="ps-3 fw-bold" colspan="2" style="font-size:0.9rem;">PENDAPATAN</td>
                    </tr>
                    @foreach($revenueByCategory as $item)
                    <tr>
                        <td class="ps-4">{{ $item->category }}</td>
                        <td class="pe-3 text-end fw-semibold">{{ rp($item->total) }}</td>
                    </tr>
                    @endforeach
                    @if($otherIncomeByCategory->count() > 0)
                    <tr>
                        <td class="ps-4 text-muted">Pendapatan Lainnya (sistem)</td>
                        <td class="pe-3 text-end fw-semibold text-muted">{{ rp($summary['total_other_income']) }}</td>
                    </tr>
                    @endif
                    <tr style="border-top:2px solid var(--border-subtle);">
                        <td class="ps-3 fw-bold">TOTAL PENDAPATAN</td>
                        <td class="pe-3 text-end fw-bold" style="font-size:1rem;color:var(--theme-primary);">{{ rp($summary['total_revenue']) }}</td>
                    </tr>

                    {{-- ═══ HPP ═══ --}}
                    <tr class="table-active">
                        <td class="ps-3 fw-bold" colspan="2" style="font-size:0.9rem;">HARGA POKOK PENJUALAN (HPP)</td>
                    </tr>
                    @forelse($hppByCategory as $item)
                    <tr>
                        <td class="ps-4">{{ $item->category?->name ?? 'Tanpa Kategori' }}</td>
                        <td class="pe-3 text-end fw-semibold">{{ rp($item->total_hpp) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td class="ps-4 text-muted" colspan="2">Tidak ada data HPP periode ini</td>
                    </tr>
                    @endforelse
                    <tr style="border-top:2px solid var(--border-subtle);">
                        <td class="ps-3 fw-bold">TOTAL HPP</td>
                        <td class="pe-3 text-end fw-bold" style="font-size:1rem;color:#f59e0b;">{{ rp($summary['total_hpp']) }}</td>
                    </tr>

                    {{-- ═══ LABA KOTOR ═══ --}}
                    <tr style="border-top:2px solid var(--border-subtle);background:rgba(var(--theme-primary-rgb),0.04);">
                        <td class="ps-3 fw-bold" style="font-size:0.95rem;">LABA KOTOR</td>
                        <td class="pe-3 text-end fw-bold" style="font-size:1.1rem;color:{{ $summary['laba_kotor'] >= 0 ? '#10b981' : '#ef4444' }};">{{ rp($summary['laba_kotor']) }}</td>
                    </tr>

                    {{-- ═══ BIAYA OPERASIONAL ═══ --}}
                    <tr class="table-active">
                        <td class="ps-3 fw-bold" colspan="2" style="font-size:0.9rem;">BIAYA OPERASIONAL</td>
                    </tr>
                    @forelse($expensesByCategory as $item)
                    <tr>
                        <td class="ps-4">{{ $item->category }}</td>
                        <td class="pe-3 text-end fw-semibold text-danger">({{ rp($item->total) }})</td>
                    </tr>
                    @empty
                    <tr>
                        <td class="ps-4 text-muted" colspan="2">Tidak ada biaya operasional periode ini</td>
                    </tr>
                    @endforelse
                    <tr style="border-top:2px solid var(--border-subtle);">
                        <td class="ps-3 fw-bold">TOTAL BIAYA OPERASIONAL</td>
                        <td class="pe-3 text-end fw-bold" style="font-size:1rem;color:#ef4444;">({{ rp($summary['total_expenses']) }})</td>
                    </tr>

                    {{-- ═══ LABA BERSIH ═══ --}}
                    <tr style="border-top:2px solid var(--border-subtle);background:rgba(var(--theme-primary-rgb),0.04);">
                        <td class="ps-3 fw-bold" style="font-size:1rem;">LABA BERSIH</td>
                        <td class="pe-3 text-end fw-bold" style="font-size:1.2rem;color:{{ $summary['laba_bersih'] >= 0 ? '#10b981' : '#ef4444' }};">{{ rp($summary['laba_bersih']) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══ DIVISI JASA ═══ --}}
@php
    $cetakProfit = $cetakRevenue - $cetakExpense;
    $servisProfit = $servisRevenue - $servisExpense;
@endphp
<div class="card card-modern shadow-sm mb-4">
    <div class="card-header bg-transparent py-2">
        <span class="fw-semibold" style="font-size:0.85rem;">DIVISI JASA</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:40%;"></th>
                        <th class="text-end fw-semibold" style="font-size:0.8rem;">Jasa Cetak</th>
                        <th class="pe-3 text-end fw-semibold" style="font-size:0.8rem;">Jasa Servis</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="ps-3">Pendapatan</td>
                        <td class="text-end fw-semibold" style="color:var(--theme-primary);">{{ rp($cetakRevenue) }}</td>
                        <td class="pe-3 text-end fw-semibold" style="color:var(--theme-primary);">{{ rp($servisRevenue) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-3">Biaya</td>
                        <td class="text-end text-danger">{{ $cetakExpense > 0 ? '(' . rp($cetakExpense) . ')' : rp(0) }}</td>
                        <td class="pe-3 text-end text-danger">{{ $servisExpense > 0 ? '(' . rp($servisExpense) . ')' : rp(0) }}</td>
                    </tr>
                    <tr style="border-top:2px solid var(--border-subtle);background:rgba(var(--theme-primary-rgb),0.04);">
                        <td class="ps-3 fw-bold">Laba / (Rugi)</td>
                        <td class="text-end fw-bold" style="color:{{ $cetakProfit >= 0 ? '#10b981' : '#ef4444' }};">{{ rp($cetakProfit) }}</td>
                        <td class="pe-3 text-end fw-bold" style="color:{{ $servisProfit >= 0 ? '#10b981' : '#ef4444' }};">{{ rp($servisProfit) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Info Tambahan --}}
<div class="row g-3">
    <div class="col-md-4">
        <div class="card card-modern shadow-sm">
            <div class="card-body text-center py-2">
                <div style="font-size:0.7rem;color:var(--text-muted);">Total Penjualan (omset)</div>
                <div class="fw-bold" style="font-size:0.95rem;">{{ rp($summary['total_selling']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-modern shadow-sm">
            <div class="card-body text-center py-2">
                <div style="font-size:0.7rem;color:var(--text-muted);">Laba Kotor dari Penjualan</div>
                <div class="fw-bold" style="font-size:0.95rem;">{{ rp($summary['total_profit_hpp']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-modern shadow-sm">
            <div class="card-body text-center py-2">
                <div style="font-size:0.7rem;color:var(--text-muted);">Item Terjual</div>
                <div class="fw-bold" style="font-size:0.95rem;">{{ number_format($summary['total_qty'], 0, ',', '.') }} pcs</div>
            </div>
        </div>
    </div>
</div>
@endsection
