@extends('layouts.app')
@section('title', 'Stok Masuk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-arrow-down me-2" style="color:#10b981;"></i>Stok Masuk</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card card-modern shadow-sm">
            <div class="card-header">
                <span class="fw-semibold"><i class="fas fa-plus-circle me-2 text-success"></i>Tambah Stok Masuk</span>
            </div>
            <div class="card-body">
                <form autocomplete="off" method="POST" action="{{ route('stock.in.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Barang</label>
                        <select name="product_id" id="in-product" class="form-select" required>
                            <option value="">Pilih barang</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}" data-unit="{{ $product->unit }}">
                                {{ $product->name }} (stok: {{ $product->stock }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Jumlah</label>
                            <input type="number" step="1" name="qty" id="in-qty" class="form-control" placeholder="1" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Satuan</label>
                            <input type="text" id="in-unit" class="form-control" readonly style="background:#f1f5f9;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Beli Satuan</label>
                        <input type="number" step="1" name="price" id="in-price" class="form-control" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total</label>
                        <input type="text" id="in-total" class="form-control" readonly style="background:#f1f5f9;font-weight:600;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Akun Pembayaran</label>
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
                    <button type="submit" class="btn btn-modern btn-success w-100">
                        <i class="fas fa-save me-1"></i>Simpan Stok Masuk
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card card-modern shadow-sm">
            <div class="card-header">
                <span class="fw-semibold"><i class="fas fa-history me-2"></i>Riwayat Stok Masuk</span>
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
                                <th class="pe-3">Keterangan</th>
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
                                <td class="pe-3">{{ $trx->description ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada stok masuk</td>
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
var elInProduct = document.getElementById('in-product');
var elInQty = document.getElementById('in-qty');
var elInPrice = document.getElementById('in-price');
var elInUnit = document.getElementById('in-unit');
var elInTotal = document.getElementById('in-total');

function hitungTotal() {
    var qty = parseInt(elInQty.value) || 0;
    var price = parseInt(elInPrice.value) || 0;
    elInTotal.value = 'Rp ' + (qty * price).toLocaleString('id-ID');
}

elInProduct?.addEventListener('change', function () {
    var opt = this.options[this.selectedIndex];
    elInPrice.value = opt.getAttribute('data-price') || 0;
    elInUnit.value = opt.getAttribute('data-unit') || '';
    hitungTotal();
});

elInQty?.addEventListener('input', hitungTotal);
elInPrice?.addEventListener('input', hitungTotal);
</script>
@endpush
