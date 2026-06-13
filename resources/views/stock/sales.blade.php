@extends('layouts.app')
@section('title', 'Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-arrow-up me-2" style="color:#f59e0b;"></i>Penjualan</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card card-modern shadow-sm">
            <div class="card-header">
                <span class="fw-semibold"><i class="fas fa-cash-register me-2 text-warning"></i>Catat Penjualan</span>
            </div>
            <div class="card-body">
                <form autocomplete="off" method="POST" action="{{ route('stock.sales.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Barang</label>
                        <select name="product_id" id="sale-product" class="form-select" required>
                            <option value="">Pilih barang</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                data-price="{{ $product->selling_price }}"
                                data-unit="{{ $product->unit }}"
                                data-stock="{{ $product->stock }}">
                                {{ $product->name }} (stok: {{ $product->stock }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Jumlah</label>
                            <input type="number" step="1" name="qty" id="sale-qty" class="form-control" placeholder="1" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Satuan</label>
                            <input type="text" id="sale-unit" class="form-control" readonly style="background:#f1f5f9;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Jual Satuan</label>
                        <input type="number" step="1" name="price" id="sale-price" class="form-control" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total</label>
                        <input type="text" id="sale-total" class="form-control" readonly style="background:#f1f5f9;font-weight:600;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Akun Penerimaan</label>
                        <select name="account_id" class="form-select" required>
                            <option value="">Pilih akun</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Opsional"></textarea>
                    </div>
                    <button type="submit" class="btn btn-modern btn-warning w-100">
                        <i class="fas fa-save me-1"></i>Catat Penjualan
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card card-modern shadow-sm">
            <div class="card-header">
                <span class="fw-semibold"><i class="fas fa-history me-2"></i>Riwayat Penjualan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Tanggal</th>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                                <th>Akun</th>
                                <th>Keterangan</th>
                                <th class="pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $trx)
                            <tr>
                                <td class="ps-3">{{ tgl($trx->date) }}</td>
                                <td>{{ $trx->product->name ?? '-' }}</td>
                                <td>{{ $trx->qty }} {{ $trx->product->unit ?? '' }}</td>
                                <td class="fw-semibold">{{ rp($trx->total) }}</td>
                                <td>{{ $trx->account->name ?? '-' }}</td>
                                <td>{{ $trx->description ?? '-' }}</td>
                                <td class="pe-3">
                                    <form autocomplete="off" action="{{ route('stock.transactions.destroy', $trx->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin hapus penjualan {{ $trx->product->name ?? '' }} ({{ $trx->qty }} {{ $trx->product->unit ?? '' }})?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-modern btn-danger btn-sm py-0 px-2" style="font-size:0.7rem;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada penjualan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($history->hasPages())
            <div class="card-footer bg-white">
                <div class="pagination-modern">{{ $history->links() }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var elSaleProduct = document.getElementById('sale-product');
var elSaleQty = document.getElementById('sale-qty');
var elSalePrice = document.getElementById('sale-price');
var elSaleUnit = document.getElementById('sale-unit');
var elSaleTotal = document.getElementById('sale-total');

function hitungTotalSale() {
    var qty = parseInt(elSaleQty.value) || 0;
    var price = parseInt(elSalePrice.value) || 0;
    elSaleTotal.value = 'Rp ' + (qty * price).toLocaleString('id-ID');
}

elSaleProduct?.addEventListener('change', function () {
    var opt = this.options[this.selectedIndex];
    elSalePrice.value = opt.getAttribute('data-price') || 0;
    elSaleUnit.value = opt.getAttribute('data-unit') || '';
    hitungTotalSale();
});

elSaleQty?.addEventListener('input', hitungTotalSale);
elSalePrice?.addEventListener('input', hitungTotalSale);
</script>
@endpush
