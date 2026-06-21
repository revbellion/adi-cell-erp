@extends('layouts.app')
@section('title', 'Opname Saldo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-calculator me-2" style="color:#8b5cf6;"></i>Opname Saldo PPOB & E-Wallet</h4>
    <div class="d-flex gap-2">
        <form autocomplete="off" method="GET" action="{{ route('opname-saldo.index') }}" class="d-flex gap-2">
            <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" style="width:auto;">
            <button type="submit" class="btn btn-modern btn-primary btn-sm"><i class="fas fa-sync me-1"></i>Refresh</button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show alert-modern py-2 px-3 mb-4" role="alert">
    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show alert-modern py-2 px-3 mb-4" role="alert">
    <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
@endif

<form autocomplete="off" method="POST" action="{{ route('opname-saldo.store') }}" id="opnameForm">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">
    
    <div class="card card-modern shadow-sm mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-list" style="color:#8b5cf6;"></i>
                <span class="fw-semibold">Saldo Akun PPOB & E-Wallet</span>
            </div>
            <button type="submit" class="btn btn-modern btn-primary">
                <i class="fas fa-save me-1"></i>Simpan Opname
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Akun</th>
                            <th>Tipe</th>
                            <th class="text-end">Saldo Sistem</th>
                            <th class="text-end" style="width:200px;">Saldo Aktual</th>
                            <th class="text-end">Selisih (Omzet)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($balances as $accountId => $data)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $data['account']->name }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($data['account']->type) }}</span></td>
                            <td class="text-end">{{ rp($data['balance']) }}</td>
                            <td class="text-end">
                                <input type="number" 
                                    name="accounts[{{ $accountId }}]" 
                                    class="form-control form-control-sm text-end saldo-aktual" 
                                    style="width:180px;display:inline-block;"
                                    data-account="{{ $accountId }}"
                                    data-saldo-sistem="{{ $data['balance'] }}"
                                    placeholder="{{ rp($data['balance']) }}"
                                    oninput="hitungSelisih()">
                            </td>
                            <td class="text-end fw-semibold selisih" data-account="{{ $accountId }}">
                                <span style="color:var(--text-muted);">-</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Tidak ada akun PPOB/E-Wallet aktif</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(count($balances) > 0)
    <div class="card card-modern shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-0">Ringkasan Opname</h5>
                    <p class="text-muted mb-0" style="font-size:0.85rem;">Tanggal: {{ tgl($date) }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex justify-content-end align-items-center gap-4">
                        <div>
                            <div style="font-size:0.75rem;color:var(--text-muted);">Total Selisih (Omzet)</div>
                            <div class="fw-bold" style="font-size:1.25rem;color:#10b981;" id="totalSelisih">Rp 0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</form>

@if($history->count() > 0)
<div class="card card-modern shadow-sm">
    <div class="card-header d-flex align-items-center">
        <i class="fas fa-history me-2" style="color:#8b5cf6;"></i>
        <span class="fw-semibold">Riwayat Opname</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Tanggal</th>
                        <th>Akun</th>
                        <th class="text-end">Saldo Sistem</th>
                        <th class="text-end">Saldo Aktual</th>
                        <th class="text-end">Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $item)
                    <tr>
                        <td class="ps-3">{{ tgl($item->opname_date) }}</td>
                        <td>{{ $item->account->name ?? '-' }}</td>
                        <td class="text-end">{{ rp($item->opening_balance) }}</td>
                        <td class="text-end">{{ rp($item->closing_balance) }}</td>
                        <td class="text-end fw-semibold" style="color:{{ $item->difference >= 0 ? '#10b981' : '#ef4444' }}">
                            {{ $item->difference >= 0 ? '+' : '' }}{{ rp($item->difference) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function hitungSelisih() {
    let totalSelisih = 0;
    const inputs = document.querySelectorAll('.saldo-aktual');
    
    inputs.forEach(input => {
        const accountId = input.dataset.account;
        const saldoSistem = parseInt(input.dataset.saldoSistem) || 0;
        const saldoAktual = parseInt(input.value) || 0;
        const selisih = saldoSistem - saldoAktual;
        
        const selisihEl = document.querySelector(`.selisih[data-account="${accountId}"]`);
        if (input.value !== '') {
            selisihEl.innerHTML = `<span style="color:${selisih >= 0 ? '#10b981' : '#ef4444'}">${selisih >= 0 ? '+' : ''}Rp ${selisih.toLocaleString('id-ID')}</span>`;
            totalSelisih += selisih;
        } else {
            selisihEl.innerHTML = '<span style="color:var(--text-muted);">-</span>';
        }
    });
    
    document.getElementById('totalSelisih').textContent = 'Rp ' + totalSelisih.toLocaleString('id-ID');
}
</script>
@endpush
@endsection
