@extends('layouts.app')
@section('title', 'Laporan Divisi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Laporan Divisi</h4>
</div>

{{-- Filter --}}
<div class="card card-modern shadow-sm mb-4">
    <div class="card-body py-3">
        <form autocomplete="off" method="GET" action="{{ route('hpp-records.index') }}" class="row g-2 align-items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">
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
                    <option value="">Semua Divisi</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryFilter == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Cari Produk</label>
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Nama produk...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-modern btn-primary btn-sm">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('hpp-records.index') }}" class="btn btn-modern btn-secondary btn-sm">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tab Navigasi --}}
<ul class="nav nav-tabs border-0 mb-4">
    <li class="nav-item">
        <a class="nav-link border-0 fw-semibold {{ $tab === 'divisions' ? 'active' : '' }}"
           href="{{ route('hpp-records.index', array_merge(request()->except('tab', 'page'), ['tab' => 'divisions'])) }}">
            <i class="fas fa-layer-group me-1"></i>Per Divisi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link border-0 fw-semibold {{ $tab === 'receipts' ? 'active' : '' }}"
           href="{{ route('hpp-records.index', array_merge(request()->except('tab', 'page'), ['tab' => 'receipts'])) }}">
            <i class="fas fa-file-invoice me-1"></i>Per Nota
        </a>
    </li>
</ul>

{{-- Ringkasan Total --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Total Penjualan</div>
                <div class="fw-bold mt-1" style="font-size:1.1rem;color:#3b82f6;">{{ rp($summary['total_selling']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Total HPP</div>
                <div class="fw-bold mt-1" style="font-size:1.1rem;color:#f59e0b;">{{ rp($summary['total_hpp']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Total Profit</div>
                <div class="fw-bold mt-1" style="font-size:1.1rem;color:#10b981;">{{ rp($summary['total_profit']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Margin Rata-rata</div>
                <div class="fw-bold mt-1" style="font-size:1.1rem;color:#8b5cf6;">{{ $divisionSummary['grandTotal']['margin'] }}%</div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ TAB: PER DIVISI ═══ --}}
@if($tab === 'divisions')
<div class="card card-modern shadow-sm mb-4">
    <div class="card-header bg-transparent py-2">
        <span class="fw-semibold" style="font-size:0.85rem;">
            <i class="fas fa-layer-group me-1"></i>Ringkasan per Divisi
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Divisi</th>
                        <th class="text-end">Terjual</th>
                        <th class="text-end">Pendapatan</th>
                        <th class="text-end">Modal (HPP)</th>
                        <th class="text-end">Laba Kotor</th>
                        <th class="text-end">Margin</th>
                        <th class="text-end">Beli Stok</th>
                        <th class="text-end pe-3">Nilai Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($divisionSummary['divisions'] as $div)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $div['name'] }}</td>
                        <td class="text-end">{{ number_format($div['qty'], 0, ',', '.') }} pcs</td>
                        <td class="text-end fw-semibold" style="color:#3b82f6;">{{ rp($div['selling']) }}</td>
                        <td class="text-end" style="color:#f59e0b;">{{ rp($div['hpp']) }}</td>
                        <td class="text-end fw-semibold" style="color:{{ $div['profit'] >= 0 ? '#10b981' : '#ef4444' }};">{{ rp($div['profit']) }}</td>
                        <td class="text-end fw-semibold">{{ $div['margin'] }}%</td>
                        <td class="text-end">{{ rp($div['purchase']) }}</td>
                        <td class="text-end pe-3 fw-semibold">{{ rp($div['stock_value']) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada data divisi di periode ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div class="fw-bold">TOTAL {{ $divisionSummary['grandTotal']['qty'] }} item</div>
        <div class="d-flex gap-4 flex-wrap">
            <span>Pendapatan: <strong style="color:#3b82f6;">{{ rp($divisionSummary['grandTotal']['selling']) }}</strong></span>
            <span>HPP: <strong style="color:#f59e0b;">{{ rp($divisionSummary['grandTotal']['hpp']) }}</strong></span>
            <span>Laba: <strong style="color:{{ $divisionSummary['grandTotal']['profit'] >= 0 ? '#10b981' : '#ef4444' }};">{{ rp($divisionSummary['grandTotal']['profit']) }}</strong></span>
            <span>Margin: <strong>{{ $divisionSummary['grandTotal']['margin'] }}%</strong></span>
            <span>Beli Stok: <strong>{{ rp($divisionSummary['grandTotal']['purchase']) }}</strong></span>
            <span>Nilai Stok: <strong>{{ rp($divisionSummary['grandTotal']['stock_value']) }}</strong></span>
        </div>
    </div>
</div>
@endif

{{-- ═══ TAB: PER NOTA ═══ --}}
@if($tab === 'receipts')
    @if($receipts->count() > 0)
        @foreach($receipts as $receipt)
        <div class="card card-modern shadow-sm mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-file-invoice" style="color:#8b5cf6;"></i>
                    <span class="fw-semibold" style="font-size:0.9rem;">{{ $receipt['receipt_id'] ?? 'Manual' }}</span>
                    <span class="text-muted" style="font-size:0.8rem;">{{ tgl($receipt['date']) }}</span>
                </div>
                <div class="d-flex gap-3" style="font-size:0.8rem;">
                    <span>Jual: <strong>{{ rp($receipt['total_selling']) }}</strong></span>
                    <span>HPP: <strong style="color:#f59e0b;">{{ rp($receipt['total_hpp']) }}</strong></span>
                    <span>Profit: <strong style="color:#10b981;">{{ rp($receipt['total_profit']) }}</strong></span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Produk</th>
                                <th>Divisi</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">HPP/pc</th>
                                <th class="text-end">Jual/pc</th>
                                <th class="text-end">Total HPP</th>
                                <th class="text-end">Total Jual</th>
                                <th class="text-end pe-3">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receipt['items'] as $idx => $item)
                            <tr>
                                <td class="ps-3">{{ $idx + 1 }}</td>
                                <td class="fw-semibold">{{ $item->product->name ?? '-' }}</td>
                                <td><span class="badge bg-secondary">{{ $item->category->name ?? '-' }}</span></td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end">{{ rp($item->hpp_amount / max($item->qty, 1)) }}</td>
                                <td class="text-end">{{ rp($item->selling_amount / max($item->qty, 1)) }}</td>
                                <td class="text-end">{{ rp($item->hpp_amount) }}</td>
                                <td class="text-end">{{ rp($item->selling_amount) }}</td>
                                <td class="text-end pe-3 fw-semibold" style="color:#10b981;">{{ rp($item->profit_amount) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    @else
    <div class="card card-modern shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-inbox" style="font-size:2rem;margin-bottom:8px;display:block;"></i>
            Tidak ada catatan HPP di periode ini
        </div>
    </div>
    @endif
@endif
@endsection
