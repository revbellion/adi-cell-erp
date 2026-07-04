@extends('layouts.app')
@section('title', 'Laporan Stok')

@section('content')
<h4 class="fw-bold mb-4"><i class="fas fa-chart-bar me-2" style="color:var(--theme-primary);"></i>Laporan Stok Barang</h4>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid var(--theme-primary);">
            <div class="card-body">
                <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;">NILAI TOTAL STOK</p>
                <h4 class="fw-bold mb-0">{{ rp($totalStockValue) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #10b981;">
            <div class="card-body">
                <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;">TOTAL PENJUALAN</p>
                <h4 class="fw-bold mb-0">{{ rp($totalSale) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid #f59e0b;">
            <div class="card-body">
                <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;">HPP (HARGA POKOK)</p>
                <h4 class="fw-bold mb-0">{{ rp($totalHpp) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card shadow-sm" style="border-left: 4px solid {{ ($totalSale - $totalHpp) >= 0 ? '#10b981' : '#ef4444' }};">
            <div class="card-body">
                <p class="text-muted small fw-semibold mb-1" style="font-size:0.75rem;">LABA KOTOR</p>
                <h4 class="fw-bold mb-0">{{ rp($totalSale - $totalHpp) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="card card-modern shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="fw-semibold"><i class="fas fa-box me-2" style="color:var(--theme-primary);"></i>Daftar Barang
                    @if($lowStockProducts->count() > 0)
                    <span class="badge bg-danger bg-opacity-10 text-danger ms-2">
                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $lowStockProducts->count() }} barang hampir habis
                    </span>
                    @endif
                </span>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form autocomplete="off" method="GET" action="{{ route('stock.report') }}" class="d-flex align-items-center gap-1">
                        <div class="input-group input-group-sm" style="width:180px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Cari barang...">
                        </div>
                        <select name="category_id" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-filter"></i></button>
                        @if(!empty($filters['search']) || !empty($filters['category_id']))
                        <a href="{{ route('stock.report') }}" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="fas fa-times"></i></a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Nama</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Harga Beli</th>
                                <th>Harga Jual</th>
                                <th class="pe-3">Nilai Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr class="{{ $product->is_low_stock ? 'table-warning' : '' }}">
                                <td class="ps-3 fw-semibold">{{ $product->name }}</td>
                                <td>{{ $product->category->name ?? '-' }}</td>
                                <td>
                                    <span class="{{ $product->is_low_stock ? 'text-danger fw-bold' : '' }}">
                                        {{ $product->stock }} {{ $product->unit }}
                                    </span>
                                    @if($product->is_low_stock)
                                    <i class="fas fa-exclamation-triangle text-danger ms-1"></i>
                                    @endif
                                </td>
                                <td>{{ rp($product->purchase_price) }}</td>
                                <td>{{ rp($product->selling_price) }}</td>
                                <td class="pe-3 fw-semibold">{{ rp($product->stock_value) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Tidak ada barang ditemukan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($products->hasPages())
            <div class="card-footer bg-white">
                <div class="pagination-modern">{{ $products->links() }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
