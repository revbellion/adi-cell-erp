@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Selamat datang, {{ Auth::user()->name }}</h4>
    <span class="text-muted small">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #10b981;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">PENJUALAN HARI INI</p>
                        <h4 class="fw-bold mb-0">{{ rp($todayRevenue) }}</h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#ecfdf5;">
                        <i class="fas fa-cash-register" style="color:#10b981;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #3b82f6;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;letter-spacing:0.03em;">BARANG TERJUAL</p>
                        <h4 class="fw-bold mb-0">{{ $todayItemsSold }} <span class="text-muted" style="font-size:0.7rem;">pcs</span></h4>
                    </div>
                    <div class="rounded-3 p-2" style="background:#eff6ff;">
                        <i class="fas fa-shopping-bag" style="color:#3b82f6;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
</div>

@if($lowStockCount > 0)
<div class="alert alert-warning alert-modern py-2 px-3 mb-4 d-flex align-items-center gap-2" role="alert">
    <i class="fas fa-exclamation-triangle"></i>
    <span>Terdapat <strong>{{ $lowStockCount }} barang</strong> dengan stok menipis. Segera lakukan stok masuk.</span>
    <a href="{{ route('stock.in') }}" class="btn btn-modern btn-sm btn-warning ms-auto">Stok Masuk</a>
</div>
@endif

<div class="card card-modern shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <i class="fas fa-receipt me-2" style="color:#10b981;"></i>
            <span class="fw-semibold">Penjualan Hari Ini</span>
            <span class="badge bg-secondary ms-2" style="font-size:0.65rem;">{{ $todayCount }} transaksi</span>
        </div>
        <a href="{{ route('stock.sales') }}" class="btn btn-modern btn-sm btn-success">
            <i class="fas fa-plus me-1"></i>POS
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-modern mb-0">
            <thead>
                <tr>
                    <th>Nota</th>
                    <th>Item</th>
                    <th class="text-end pe-3">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentReceipts as $receipt)
                    <tr>
                        <td class="ps-3">{{ $receipt->receipt_id }}</td>
                        <td>{{ $receipt->items }} barang</td>
                        <td class="text-end pe-3 fw-semibold">{{ rp($receipt->total) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Belum ada penjualan hari ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
