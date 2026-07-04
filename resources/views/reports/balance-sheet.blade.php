@extends('layouts.app')
@section('title', 'Neraca')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Neraca</h4>
        <p class="text-muted mb-0" style="font-size:0.8rem;">Laporan posisi keuangan per tanggal tertentu</p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('reports.balance-sheet') }}" class="row g-2 mb-4 filter-form">
    <div class="col-auto">
        <input type="date" name="date" value="{{ $selectedDate }}" class="form-control form-control-sm" onchange="this.form.submit()" style="width:auto;">
    </div>
    <div class="col-auto">
        <a href="{{ route('reports.balance-sheet') }}" class="btn btn-modern btn-secondary btn-sm">Hari Ini</a>
    </div>
</form>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Total Aset</div>
                <div class="fw-bold mt-1" style="font-size:1.2rem;color:var(--theme-primary);">{{ rp($totalCurrentAssets) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Total Kewajiban</div>
                <div class="fw-bold mt-1" style="font-size:1.2rem;color:#f59e0b;">{{ rp($totalLiabilities) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Total Ekuitas</div>
                <div class="fw-bold mt-1" style="font-size:1.2rem;color:#10b981;">{{ rp($totalEquity) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Balance Check --}}
@if(!$balanceCheck)
<div class="alert alert-warning py-2 mb-4" style="font-size:0.85rem;">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Neraca tidak balance: Aset ({{ rp($totalCurrentAssets) }}) ≠ Kewajiban + Ekuitas ({{ rp($totalLiabilities + $totalEquity) }})
    @if($balanceDiff != 0)
    <span class="ms-2">Selisih: <strong>{{ rp(abs($balanceDiff)) }}</strong></span>
    @endif
</div>
@else
<div class="alert alert-success py-2 mb-4" style="font-size:0.85rem;">
    <i class="fas fa-check-circle me-2"></i>
    Neraca balance: Aset = Kewajiban + Ekuitas
</div>
@endif

{{-- Detail Neraca --}}
<div class="card card-modern shadow-sm mb-4">
    <div class="card-header bg-transparent py-2">
        <span class="fw-semibold" style="font-size:0.85rem;">
            Neraca — Per {{ $dateFormatted }}
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <tbody>
                    {{-- ═══ ASET ═══ --}}
                    <tr class="table-active">
                        <td class="ps-3 fw-bold" colspan="2" style="font-size:0.9rem;">ASET LANCAR</td>
                    </tr>

                    {{-- Cash --}}
                    @if(count($accounts['cash']) > 0)
                    <tr>
                        <td class="ps-4 fw-semibold" colspan="2">Kas</td>
                    </tr>
                    @foreach($accounts['cash'] as $acc)
                    <tr>
                        <td class="ps-5" style="font-size:0.85rem;">{{ $acc['name'] }}</td>
                        <td class="pe-3 text-end fw-semibold">{{ rp($acc['balance']) }}</td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- Bank --}}
                    @if(count($accounts['bank']) > 0)
                    <tr>
                        <td class="ps-4 fw-semibold" colspan="2">Bank</td>
                    </tr>
                    @foreach($accounts['bank'] as $acc)
                    <tr>
                        <td class="ps-5" style="font-size:0.85rem;">{{ $acc['name'] }}</td>
                        <td class="pe-3 text-end fw-semibold">{{ rp($acc['balance']) }}</td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- E-Wallet --}}
                    @if(count($accounts['ewallet']) > 0)
                    <tr>
                        <td class="ps-4 fw-semibold" colspan="2">E-Wallet</td>
                    </tr>
                    @foreach($accounts['ewallet'] as $acc)
                    <tr>
                        <td class="ps-5" style="font-size:0.85rem;">{{ $acc['name'] }}</td>
                        <td class="pe-3 text-end fw-semibold">{{ rp($acc['balance']) }}</td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- PPOB --}}
                    @if(count($accounts['ppob']) > 0)
                    <tr>
                        <td class="ps-4 fw-semibold" colspan="2">PPOB</td>
                    </tr>
                    @foreach($accounts['ppob'] as $acc)
                    <tr>
                        <td class="ps-5" style="font-size:0.85rem;">{{ $acc['name'] }}</td>
                        <td class="pe-3 text-end fw-semibold">{{ rp($acc['balance']) }}</td>
                    </tr>
                    @endforeach
                    @endif

                    <tr>
                        <td class="ps-4">Piutang (belum dibayar)</td>
                        <td class="pe-3 text-end fw-semibold">{{ rp($totalReceivables) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Persediaan Barang</td>
                        <td class="pe-3 text-end fw-semibold">{{ rp($totalInventory) }}</td>
                    </tr>

                    <tr style="border-top:2px solid var(--border-subtle);">
                        <td class="ps-3 fw-bold">TOTAL ASET LANCAR</td>
                        <td class="pe-3 text-end fw-bold" style="font-size:1rem;color:var(--theme-primary);">{{ rp($totalCurrentAssets) }}</td>
                    </tr>

                    {{-- ═══ KEWAJIBAN ═══ --}}
                    <tr class="table-active">
                        <td class="ps-3 fw-bold" colspan="2" style="font-size:0.9rem;">KEWAJIBAN</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-muted" colspan="2">
                            @if($totalLiabilities == 0)
                                <em>Belum ada data hutang (modul hutang belum tersedia)</em>
                            @endif
                        </td>
                    </tr>
                    <tr style="border-top:2px solid var(--border-subtle);">
                        <td class="ps-3 fw-bold">TOTAL KEWAJIBAN</td>
                        <td class="pe-3 text-end fw-bold" style="font-size:1rem;color:#f59e0b;">{{ rp($totalLiabilities) }}</td>
                    </tr>

                    {{-- ═══ EKUITAS ═══ --}}
                    <tr class="table-active">
                        <td class="ps-3 fw-bold" colspan="2" style="font-size:0.9rem;">EKUITAS</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Modal Awal
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 ms-1" style="font-size:0.65rem;" data-bs-toggle="modal" data-bs-target="#modalModalAwal">
                                <i class="fas fa-pen"></i>
                            </button>
                        </td>
                        <td class="pe-3 text-end fw-semibold">{{ rp($initialCapital) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Laba Ditahan</td>
                        <td class="pe-3 text-end fw-semibold">{{ rp($retainedEarnings) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Laba Periode Berjalan</td>
                        <td class="pe-3 text-end fw-semibold" style="color:{{ $currentProfit >= 0 ? '#10b981' : '#ef4444' }};">{{ rp($currentProfit) }}</td>
                    </tr>
                    @if($totalPrive > 0)
                    <tr>
                        <td class="ps-4">Prive (pengambilan profit)</td>
                        <td class="pe-3 text-end fw-semibold text-danger">({{ rp($totalPrive) }})</td>
                    </tr>
                    @endif
                    <tr style="border-top:2px solid var(--border-subtle);">
                        <td class="ps-3 fw-bold">TOTAL EKUITAS</td>
                        <td class="pe-3 text-end fw-bold" style="font-size:1rem;color:#10b981;">{{ rp($totalEquity) }}</td>
                    </tr>

                    {{-- ═══ TOTAL ═══ --}}
                    <tr style="border-top:2px solid var(--border-subtle);background:rgba(var(--theme-primary-rgb),0.04);">
                        <td class="ps-3 fw-bold" style="font-size:1rem;">TOTAL KEWAJIBAN & EKUITAS</td>
                        <td class="pe-3 text-end fw-bold" style="font-size:1.1rem;">{{ rp($totalLiabilities + $totalEquity) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Edit Modal Awal --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalModalAwal">
    <div class="modal-dialog modal-sm">
        <form autocomplete="off" method="POST" action="{{ route('reports.balance-sheet.initial-capital') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-coins me-2"></i>Modal Awal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="font-size:0.8rem;">Total modal awal saat bisnis pertama kali didirikan.</p>
                <div class="mb-3">
                    <label class="form-label">Nominal Modal Awal</label>
                    <input type="number" step="1" name="amount" class="form-control" required placeholder="0"
                        value="{{ $initialCapital }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
