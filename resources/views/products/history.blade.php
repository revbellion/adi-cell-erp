@extends('layouts.app')
@section('title', 'Riwayat - ' . $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="fas fa-history me-2" style="color:var(--theme-primary);"></i>Riwayat Barang
        </h4>
        <div class="mt-1">
            <span class="fw-semibold" style="font-size:1.1rem;">{{ $product->name }}</span>
            <span class="badge bg-secondary ms-2">{{ $product->category->name ?? '-' }}</span>
        </div>
    </div>
    <div class="text-end">
        <div class="text-muted small">Stok Saat Ini</div>
        <span class="fw-bold {{ $product->is_low_stock ? 'text-danger' : '' }}" style="font-size:1.5rem;">
            {{ $product->stock }} {{ $product->unit }}
        </span>
        @if($product->is_low_stock)
        <i class="fas fa-exclamation-triangle text-danger ms-1"></i>
        @endif
    </div>
</div>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Tanggal</th>
                        <th>Tipe</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Total</th>
                        <th>Akun</th>
                        <th class="pe-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                    <tr>
                        <td class="ps-3">{{ tgl($trx->date) }}</td>
                        <td>
                            @switch($trx->type)
                                @case('in')
                                    <span class="badge bg-success">Stok Masuk</span>
                                    @break
                                @case('out')
                                    <span class="badge bg-warning text-dark">Penjualan</span>
                                    @break
                                @case('opname')
                                    <span class="badge bg-info">Opname</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="fw-semibold {{ $trx->type === 'in' ? 'text-success' : 'text-danger' }}">
                            {{ $trx->type === 'in' ? '+' : '-' }}{{ $trx->qty }}
                        </td>
                        <td>{{ rp($trx->price) }}</td>
                        <td class="fw-semibold">{{ rp($trx->qty * $trx->price) }}</td>
                        <td>{{ $trx->account->name ?? '-' }}</td>
                        <td class="pe-3 text-muted" style="font-size:0.85rem;">
                            @if($trx->type === 'in')
                                Stok Masuk {{ $trx->product->name ?? '' }}
                            @elseif($trx->type === 'out')
                                {{ $trx->description ?? 'Penjualan' }}
                            @else
                                {{ $trx->description ?? 'Penyesuaian stok' }}
                            @endif
                            @if($trx->receipt_id)
                            <br><small class="text-muted">Nota: {{ $trx->receipt_id }}</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada transaksi untuk barang ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
    <div class="card-footer bg-white">
        <div class="pagination-modern">{{ $transactions->links() }}</div>
    </div>
    @endif
</div>

<div class="mt-3">
    <a href="{{ route('products.index') }}" class="btn btn-modern btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>
@endsection
