@extends('layouts.app')
@section('title', 'Laporan Penjualan')

@push('styles')
<style>
    .chart-container { position: relative; height: 280px; }
    .stat-card { transition: transform 0.15s ease; }
    .stat-card:hover { transform: translateY(-2px); }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Laporan Penjualan</h4>
    <a href="{{ route('sales-report.export', request()->query()) }}" class="btn btn-modern btn-success btn-sm">
        <i class="fas fa-file-excel me-1"></i>Export Excel
    </a>
</div>

{{-- Filter --}}
<div class="card card-modern shadow-sm mb-4">
    <div class="card-body py-3">
        <form autocomplete="off" method="GET" action="{{ route('sales-report.index') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Dari</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Sampai</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Kategori</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryFilter == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Produk</label>
                <select name="product" class="form-select form-select-sm">
                    <option value="">Semua Produk</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ $productFilter == $prod->id ? 'selected' : '' }}>{{ $prod->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Metode Bayar</label>
                <select name="account" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ $accountFilter == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-modern btn-primary btn-sm">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('sales-report.index') }}" class="btn btn-modern btn-secondary btn-sm">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4 col-lg-2">
        <div class="card card-modern shadow-sm h-100 stat-card">
            <div class="card-body text-center">
                <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px;">Total Omzet</div>
                <div class="fw-bold" style="font-size:1.1rem;color:#3b82f6;">{{ rp($summary['total_revenue']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card card-modern shadow-sm h-100 stat-card">
            <div class="card-body text-center">
                <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px;">Total HPP</div>
                <div class="fw-bold" style="font-size:1.1rem;color:#f59e0b;">{{ rp($summary['total_hpp']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card card-modern shadow-sm h-100 stat-card">
            <div class="card-body text-center">
                <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px;">Total Profit</div>
                <div class="fw-bold" style="font-size:1.1rem;color:#10b981;">{{ rp($summary['total_profit']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card card-modern shadow-sm h-100 stat-card">
            <div class="card-body text-center">
                <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px;">Transaksi</div>
                <div class="fw-bold" style="font-size:1.1rem;color:#8b5cf6;">{{ $summary['total_transactions'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card card-modern shadow-sm h-100 stat-card">
            <div class="card-body text-center">
                <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px;">Rata-rata/Transaksi</div>
                <div class="fw-bold" style="font-size:1.1rem;color:#06b6d4;">{{ rp($summary['avg_transaction']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card card-modern shadow-sm h-100 stat-card">
            <div class="card-body text-center">
                <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px;">Total Qty Terjual</div>
                <div class="fw-bold" style="font-size:1.1rem;color:#ec4899;">{{ $summary['total_qty'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card card-modern shadow-sm">
            <div class="card-header">
                <h6 class="fw-semibold mb-0"><i class="fas fa-chart-area me-2" style="color:#3b82f6;"></i>Tren Penjualan Harian</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-modern shadow-sm">
            <div class="card-header">
                <h6 class="fw-semibold mb-0"><i class="fas fa-chart-pie me-2" style="color:#8b5cf6;"></i>Penjualan per Kategori</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Top Products & Payment Methods --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card card-modern shadow-sm">
            <div class="card-header">
                <h6 class="fw-semibold mb-0"><i class="fas fa-trophy me-2" style="color:#f59e0b;"></i>Produk Terlaris</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Produk</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Omzet</th>
                                <th class="text-end pe-3">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $idx => $product)
                            <tr>
                                <td class="ps-3">
                                    @if($idx === 0) <i class="fas fa-medal" style="color:#f59e0b;"></i>
                                    @elseif($idx === 1) <i class="fas fa-medal" style="color:#94a3b8;"></i>
                                    @elseif($idx === 2) <i class="fas fa-medal" style="color:#cd7f32;"></i>
                                    @else {{ $idx + 1 }}
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $product['name'] }}</td>
                                <td class="text-center">{{ $product['qty'] }}</td>
                                <td class="text-end">{{ rp($product['revenue']) }}</td>
                                <td class="text-end pe-3 fw-semibold" style="color:#10b981;">{{ rp($product['profit']) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-modern shadow-sm">
            <div class="card-header">
                <h6 class="fw-semibold mb-0"><i class="fas fa-credit-card me-2" style="color:#06b6d4;"></i>Penjualan per Metode Bayar</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Metode</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-center">Transaksi</th>
                                <th class="text-end pe-3">Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesByAccount as $acc)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $acc['name'] }}</td>
                                <td class="text-center"><span class="badge bg-secondary">{{ ucfirst($acc['type']) }}</span></td>
                                <td class="text-center">{{ $acc['transactions'] }}</td>
                                <td class="text-end pe-3 fw-semibold" style="color:#3b82f6;">{{ rp($acc['revenue']) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Detail per Struk --}}
<div class="card card-modern shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="fw-semibold mb-0"><i class="fas fa-receipt me-2" style="color:#8b5cf6;"></i>Detail Transaksi</h6>
        <span class="badge bg-primary">{{ $receipts->count() }} struk</span>
    </div>
    <div class="card-body p-0">
        @if($receipts->count() > 0)
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">No. Struk</th>
                        <th>Tanggal</th>
                        <th>Metode Bayar</th>
                        <th class="text-center">Items</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Omzet</th>
                        <th class="text-end">HPP</th>
                        <th class="text-end pe-3">Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipts as $receipt)
                    <tr>
                        <td class="ps-3 fw-semibold">
                            <a data-bs-toggle="collapse" href="#receipt{{ $receipt['receipt_id'] }}" role="button" style="color:var(--theme-primary);text-decoration:none;">
                                <i class="fas fa-chevron-down me-1" style="font-size:0.7rem;"></i>{{ $receipt['receipt_id'] ?? 'Manual' }}
                            </a>
                        </td>
                        <td>{{ $receipt['date']->format('d/m/Y H:i') }}</td>
                        <td><span class="badge bg-secondary">{{ $receipt['account'] }}</span></td>
                        <td class="text-center">{{ $receipt['items']->count() }}</td>
                        <td class="text-center">{{ $receipt['total_qty'] }}</td>
                        <td class="text-end fw-semibold" style="color:#3b82f6;">{{ rp($receipt['total_revenue']) }}</td>
                        <td class="text-end" style="color:#f59e0b;">{{ rp($receipt['total_hpp']) }}</td>
                        <td class="text-end pe-3 fw-bold" style="color:#10b981;">{{ rp($receipt['total_profit']) }}</td>
                    </tr>
                    <tr>
                        <td colspan="8" class="p-0 border-0">
                            <div class="collapse" id="receipt{{ $receipt['receipt_id'] }}">
                                <div class="p-3" style="background:var(--bg-secondary);">
                                    <table class="table table-sm table-borderless mb-0" style="font-size:0.85rem;">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Harga</th>
                                                <th class="text-end">Subtotal</th>
                                                <th class="text-end">Profit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($receipt['items'] as $item)
                                            <tr>
                                                <td>{{ $item->product->name ?? '-' }} <span class="badge bg-secondary" style="font-size:0.65rem;">{{ $item->category->name ?? '-' }}</span></td>
                                                <td class="text-center">{{ $item->qty }}</td>
                                                <td class="text-end">{{ rp((int) round($item->selling_amount / max($item->qty, 1))) }}</td>
                                                <td class="text-end fw-semibold">{{ rp($item->selling_amount) }}</td>
                                                <td class="text-end" style="color:#10b981;">{{ rp($item->profit_amount) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-5">
            <i class="fas fa-inbox" style="font-size:2rem;margin-bottom:8px;display:block;"></i>
            Tidak ada data penjualan di periode ini
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
const dailyData = @json($dailySales);
const categoryData = @json($salesByCategory);

// Daily sales chart
if (dailyData.length > 0) {
    const ctx1 = document.getElementById('dailyChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: dailyData.map(d => d.label),
            datasets: [
                {
                    label: 'Omzet',
                    data: dailyData.map(d => d.revenue),
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 4,
                    order: 2,
                },
                {
                    label: 'Profit',
                    data: dailyData.map(d => d.profit),
                    type: 'line',
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                    order: 1,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) { return 'Rp ' + (v / 1000) + 'K'; }
                    }
                }
            }
        }
    });
}

// Category pie chart
if (categoryData.length > 0) {
    const colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#f97316','#14b8a6','#6366f1'];
    const ctx2 = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(c => c.name),
            datasets: [{
                data: categoryData.map(c => c.revenue),
                backgroundColor: colors.slice(0, categoryData.length),
                borderWidth: 2,
                borderColor: 'var(--bg-card, #fff)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 10 } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.label + ': Rp ' + ctx.parsed.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
}
</script>
@endpush
