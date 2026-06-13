@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalCepatPendapatan">
            <i class="fas fa-plus me-1"></i>Pendapatan
        </button>
        <button type="button" class="btn btn-modern btn-danger" data-bs-toggle="modal" data-bs-target="#modalCepatPengeluaran">
            <i class="fas fa-minus me-1"></i>Pengeluaran
        </button>
        <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalCepatTransfer">
            <i class="fas fa-arrow-right-arrow-left me-1"></i>Transfer
        </button>
    </div>
    <form autocomplete="off" method="GET" action="{{ route('dashboard') }}">
        <input type="month" name="period" value="{{ $period }}" onchange="this.form.submit()"
               class="form-control form-control-sm" style="width:auto;display:inline-block;border-radius:8px;">
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid var(--theme-primary);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">TOTAL EQUITY</p>
                        <h4 class="fw-bold mb-0">{{ rp($totalEquity) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#eff6ff;">
                        <i class="fas fa-landmark" style="color:var(--theme-primary);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #10b981;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">PIUTANG BELUM DIBAYAR</p>
                        <h4 class="fw-bold mb-0">{{ rp($totalReceivable) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#ecfdf5;">
                        <i class="fas fa-hand-holding-usd" style="color:#10b981;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #f59e0b;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">PENGELUARAN BULAN INI</p>
                        <h4 class="fw-bold mb-0">{{ rp($totalExpense) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#fffbeb;">
                        <i class="fas fa-shopping-cart" style="color:#f59e0b;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid {{ $profitBersih >= 0 ? '#10b981' : '#ef4444' }};">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">PROFIT BERSIH</p>
                        <h4 class="fw-bold mb-0">{{ rp($profitBersih) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#{{ $profitBersih >= 0 ? 'ecfdf5' : 'fef2f2' }};">
                        <i class="fas {{ $profitBersih >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}" style="color:{{ $profitBersih >= 0 ? '#10b981' : '#ef4444' }};"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #2563eb;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">SALDO BCA</p>
                        <h4 class="fw-bold mb-0">{{ rp($bcaBalance) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#eff6ff;">
                        <i class="fas fa-university" style="color:#2563eb;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #f59e0b;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">AVG HARIAN</p>
                        <h4 class="fw-bold mb-0">{{ rp($avgDaily) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#fffbeb;">
                        <i class="fas fa-calendar-day" style="color:#f59e0b;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #06b6d4;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">OMSET BULAN INI</p>
                        <h4 class="fw-bold mb-0">{{ rp($totalIncome) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#ecfeff;">
                        <i class="fas fa-chart-line" style="color:#06b6d4;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #14b8a6;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">SALDO CASH</p>
                        <h4 class="fw-bold mb-0">{{ rp($cashBalance) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#f0fdfa;">
                        <i class="fas fa-money-bill-wave" style="color:#14b8a6;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@if($totalStockValue > 0 || $lowStockCount > 0)
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #8b5cf6;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">NILAI STOK</p>
                        <h4 class="fw-bold mb-0">{{ rp($totalStockValue) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#f5f3ff;">
                        <i class="fas fa-box" style="color:#8b5cf6;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid {{ $lowStockCount > 0 ? '#ef4444' : '#10b981' }};">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">BARANG HAMPIR HABIS</p>
                        <h4 class="fw-bold mb-0 {{ $lowStockCount > 0 ? 'text-danger' : '' }}">{{ $lowStockCount }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:{{ $lowStockCount > 0 ? '#fef2f2' : '#ecfdf5' }};">
                        <i class="fas {{ $lowStockCount > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle' }}" style="color:{{ $lowStockCount > 0 ? '#ef4444' : '#10b981' }};"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #f59e0b;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">PEMBELIAN STOK</p>
                        <h4 class="fw-bold mb-0">{{ rp($stockPurchasePeriod) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#fffbeb;">
                        <i class="fas fa-arrow-down" style="color:#f59e0b;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid {{ $stockSalePeriod >= $stockPurchasePeriod ? '#10b981' : '#ef4444' }};">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">PENJUALAN STOK</p>
                        <h4 class="fw-bold mb-0">{{ rp($stockSalePeriod) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#{{ $stockSalePeriod >= $stockPurchasePeriod ? 'ecfdf5' : 'fef2f2' }};">
                        <i class="fas fa-arrow-up" style="color:{{ $stockSalePeriod >= $stockPurchasePeriod ? '#10b981' : '#ef4444' }};"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@php $bs = $billSummary; @endphp
@if($bs['total'] > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card card-modern shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-file-invoice me-2" style="color:#8b5cf6;"></i>
                    <span class="fw-semibold">Tagihan Bulan Ini</span>
                    <span class="badge {{ $bs['unpaid'] > 0 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success' }} ms-2" style="font-size:0.65rem;">
                        {{ $bs['paid'] }}/{{ $bs['total'] }} selesai
                    </span>
                </div>
                <a href="{{ route('bills.index', ['period' => $period]) }}" class="btn btn-modern btn-sm btn-outline-secondary">Kelola</a>
            </div>
            <div class="card-body py-2">
                <div class="row g-2">
                    @foreach($bs['bills'] as $bill)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3 {{ $bill->is_paid ? 'bg-success bg-opacity-10' : '' }}" style="{{ !$bill->is_paid ? 'background:rgba(255,255,255,0.03);' : '' }}">
                            @if($bill->is_paid)
                            <i class="fas fa-check-circle text-success"></i>
                            @else
                            <i class="far fa-circle text-warning"></i>
                            @endif
                            <div class="small">
                                <div class="fw-semibold" style="font-size:0.8rem;">{{ $bill->name }}</div>
                                <div class="text-muted" style="font-size:0.7rem;">{{ rp($bill->payment->amount ?? $bill->amount) }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card card-modern shadow-sm">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-wallet me-2" style="color:var(--theme-primary);"></i>
                <span class="fw-semibold">Saldo Digital</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Akun</th>
                            <th class="text-end pe-3">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account)
                        <tr>
                            <td class="ps-3">{{ $account->name }}</td>
                            <td class="text-end pe-3 fw-semibold {{ in_array($account->type, ['ewallet','ppob']) && $account->balance < 250000 ? 'text-danger' : '' }}">
                                @if(in_array($account->type, ['ewallet','ppob']) && $account->balance < 250000)
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                @endif
                                {{ rp($account->balance) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-chart-line me-2" style="color:var(--theme-primary);"></i>
                <span class="fw-semibold">Tren 6 Bulan</span>
            </div>
            <div class="card-body d-flex align-items-center">
                <canvas id="chartTren" height="160"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-chart-pie me-2" style="color:#f59e0b;"></i>
                <span class="fw-semibold">Pengeluaran Bulan Ini</span>
            </div>
            <div class="card-body d-flex align-items-center">
                <canvas id="chartPie" height="160"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-4">
        <div class="card card-modern shadow-sm">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-exchange-alt me-2" style="color:#10b981;"></i>
                <span class="fw-semibold">Mutasi Terakhir</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Dari</th>
                            <th>Ke</th>
                            <th class="text-end pe-3">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentMutasi as $mutasi)
                        <tr>
                            <td class="ps-3">{{ tgl($mutasi->date) }}</td>
                            <td>{{ $mutasi->fromAccount->name ?? '-' }}</td>
                            <td>{{ $mutasi->toAccount->name ?? '-' }}</td>
                            <td class="text-end pe-3 fw-semibold">{{ rp($mutasi->amount) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-modern shadow-sm">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-chart-line me-2" style="color:#f59e0b;"></i>
                <span class="fw-semibold">Profit 7 Hari</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th class="text-end">Omset</th>
                            <th class="text-end">Expense</th>
                            <th class="text-end pe-3">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyProfits as $day)
                        <tr>
                            <td class="ps-3">{{ \Carbon\Carbon::parse($day['date'])->isoFormat('D MMM') }}</td>
                            <td class="text-end fw-semibold">{{ rp($day['income']) }}</td>
                            <td class="text-end fw-semibold">{{ rp($day['expense']) }}</td>
                            <td class="text-end pe-3 fw-semibold" style="color:{{ $day['profit'] >= 0 ? '#10b981' : '#ef4444' }};">
                                {{ $day['profit'] >= 0 ? '+' : '' }}{{ rp($day['profit']) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalCepatPendapatan">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('incomes.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Catat Pendapatan</h5>
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
                        @foreach($accountList as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="category" class="form-control" list="income-cat-cepat">
                    <datalist id="income-cat-cepat">
                        @foreach($incomeCategories as $cat)
                        <option value="{{ $cat }}">
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
                <button type="submit" class="btn btn-modern btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalCepatPengeluaran">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('expenses.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Catat Pengeluaran</h5>
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
                        @foreach($accountList as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="category" class="form-control" list="category-list-cepat" required>
                    <datalist id="category-list-cepat">
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
                <button type="submit" class="btn btn-modern btn-danger">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalCepatTransfer">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('mutations.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-arrow-right-arrow-left me-2"></i>Transfer Cepat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Dari Akun</label>
                    <select name="from_account_id" class="form-select" required>
                        <option value="">Pilih akun</option>
                        @foreach($accountList as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ke Akun</label>
                    <select name="to_account_id" class="form-select" required>
                        <option value="">Pilih akun</option>
                        @foreach($accountList as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Saldo Awal</label>
                    <input type="number" id="saldoAwal" class="form-control" placeholder="Pilih akun dulu" readonly style="background:#f1f5f9;">
                </div>
                <div class="mb-3">
                    <label class="form-label">Saldo Akhir</label>
                    <input type="number" id="saldoAkhir" class="form-control" placeholder="Misal 50000" required>
                </div>
                <input type="hidden" name="amount" id="transferAmount">
                <div class="mb-3 text-end small fw-semibold" id="transferDisplay" style="color:var(--theme-primary);">
                    Jumlah transfer: Rp 0
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
                <input type="hidden" name="description" value="Transfer cepat">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary">Transfer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var ctxTren = document.getElementById('chartTren');
    if (ctxTren) {
        new Chart(ctxTren, {
            type: 'line',
            data: {
                labels: @json($chartMonths['labels']),
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: @json($chartMonths['incomes']),
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,0.1)',
                        fill: true,
                        tension: 0.3,
                    },
                    {
                        label: 'Pengeluaran',
                        data: @json($chartMonths['expenses']),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239,68,68,0.1)',
                        fill: true,
                        tension: 0.3,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: '#64748b', font: { size: 12 } },
                    },
                },
                scales: {
                    x: {
                        ticks: { color: '#64748b' },
                        grid: { display: false },
                    },
                    y: {
                        ticks: { color: '#64748b', callback: function (v) { return 'Rp' + v.toLocaleString('id-ID'); } },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                    },
                },
            },
        });
    }

    var ctxPie = document.getElementById('chartPie');
    if (ctxPie) {
        var pieLabels = @json($expenseCategories->keys());
        var pieData = @json($expenseCategories->values());
        var colors = ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316'];

        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: pieLabels.length ? pieLabels : ['Belum ada data'],
                datasets: [{
                    data: pieLabels.length ? pieData : [1],
                    backgroundColor: colors.slice(0, pieLabels.length || 1),
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#64748b', font: { size: 10 }, boxWidth: 12, padding: 8 },
                    },
                },
                cutout: '65%',
            },
        });
    }

    var balances = @json($accountBalances);
    var elFrom = document.querySelector('#modalCepatTransfer select[name="from_account_id"]');
    var elSaldoAwal = document.getElementById('saldoAwal');
    var elSaldoAkhir = document.getElementById('saldoAkhir');
    var elAmount = document.getElementById('transferAmount');
    var elDisplay = document.getElementById('transferDisplay');

    function hitungTransfer() {
        var awal = parseInt(elSaldoAwal.value) || 0;
        var akhir = parseInt(elSaldoAkhir.value) || 0;
        if (akhir < 0 || akhir > awal) {
            elAmount.value = '';
            elDisplay.textContent = 'Saldo akhir tidak valid';
            return;
        }
        var amount = awal - akhir;
        elAmount.value = amount;
        elDisplay.textContent = 'Jumlah transfer: Rp ' + amount.toLocaleString('id-ID');
    }

    if (elFrom) {
        elFrom.addEventListener('change', function () {
            var balance = balances[this.value] || 0;
            elSaldoAwal.value = balance;
            elSaldoAkhir.value = '';
            elAmount.value = '';
            elDisplay.textContent = 'Jumlah transfer: Rp 0';
        });
    }

    if (elSaldoAkhir) {
        elSaldoAkhir.addEventListener('input', hitungTransfer);
    }
});
</script>
@endpush
