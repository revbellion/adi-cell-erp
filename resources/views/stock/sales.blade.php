@extends('layouts.app')
@section('title', 'Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-cash-register me-2" style="color:#f59e0b;"></i>POS Penjualan</h4>
</div>

<form autocomplete="off" method="POST" action="{{ route('stock.sales.store') }}" id="formPenjualan">
    @csrf
    <input type="hidden" name="account_id" id="pos-account" value="">
    <input type="hidden" name="date" id="pos-date" value="">

    <div class="row g-3">
        {{-- LEFT: Cart --}}
        <div class="col-lg-7">
            <div class="card card-modern shadow-sm pos-cart-card" style="min-height:500px;">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold"><i class="fas fa-shopping-cart me-2 text-warning"></i>Keranjang</span>
                    <div>
                        <select id="pos-account-select" class="form-select form-select-sm d-inline-block" style="width:auto;border-radius:8px;" required>
                            <option value="">Akun</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        <input type="date" id="pos-date-input" value="{{ date('Y-m-d') }}" class="form-control form-control-sm d-inline-block" style="width:auto;border-radius:8px;">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="pos-cart" class="p-3" style="min-height:300px;">
                        <div class="text-center text-muted py-5" id="pos-cart-empty">
                            <i class="fas fa-cart-plus fa-3x mb-3" style="opacity:0.3;"></i>
                            <p class="mb-0">Klik produk di samping untuk menambah</p>
                        </div>
                        <div id="pos-cart-items" style="display:none;"></div>
                    </div>
                </div>
                <div class="card-footer" id="pos-cart-footer" style="display:none;">
                    <div class="row g-2 align-items-center">
                        <div class="col-6">
                            <small class="text-muted">Total Item</small>
                            <span class="fw-bold ms-2" id="pos-total-item">0</span>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted">Total</small>
                            <h4 class="fw-bold mb-0" id="pos-grand-total">Rp 0</h4>
                        </div>
                    </div>
                    <div class="row g-2 mt-2 pos-cart-footer-row">
                        <div class="col-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" style="border-radius:8px 0 0 8px;">Bayar</span>
                                <input type="number" id="pos-bayar" class="form-control" placeholder="0" min="0" style="border-radius:0 8px 8px 0;">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" style="border-radius:8px 0 0 8px;">Kembali</span>
                                <input type="text" id="pos-kembali" class="form-control" readonly style="border-radius:0 8px 8px 0;background:#f1f5f9;font-weight:600;">
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <button type="submit" class="btn btn-modern btn-success w-100" id="pos-submit" disabled>
                                <i class="fas fa-check me-1"></i>Bayar Rp 0
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Product Grid --}}
        <div class="col-lg-5">
            <div class="card card-modern shadow-sm pos-grid-card" style="min-height:500px;">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold"><i class="fas fa-box me-2" style="color:var(--theme-primary);"></i>Pilih Barang</span>
                    <select id="pos-category-filter" class="form-select form-select-sm" style="width:auto;border-radius:8px;">
                        <option value="">Semua</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body p-3 pos-grid-scroll" id="pos-product-grid" style="max-height:450px;overflow-y:auto;">
                    <div class="row g-2">
                        @foreach($products as $product)
                        <div class="col-6 pos-product-item" data-category="{{ $product->category_id }}" data-id="{{ $product->id }}">
                            <button type="button" class="btn btn-modern btn-outline-secondary w-100 text-start p-3 pos-product-btn"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-price="{{ $product->selling_price }}"
                                data-unit="{{ $product->unit }}"
                                data-stock="{{ $product->stock }}"
                                style="border-radius:10px;height:100%;"
                                {{ $product->stock < 1 ? 'disabled' : '' }}>
                                <div class="fw-semibold" style="font-size:0.85rem;">{{ $product->name }}</div>
                                <div class="text-warning fw-bold mt-1">{{ rp($product->selling_price) }}</div>
                                <small class="text-muted">Stok: {{ $product->stock }} {{ $product->unit }}</small>
                                @if($product->stock < 1)
                                <div class="text-danger small mt-1"><i class="fas fa-times-circle"></i> Habis</div>
                                @endif
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- History --}}
<div class="row g-3 mt-4">
    <div class="col-12">
        <div class="card card-modern shadow-sm">
            <div class="card-header">
                <span class="fw-semibold"><i class="fas fa-history me-2"></i>Riwayat Penjualan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Nota</th>
                                <th>Tanggal</th>
                                <th>Item</th>
                                <th>Barang</th>
                                <th>Total</th>
                                <th class="pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $receipt)
                            <tr>
                                <td class="ps-3 fw-semibold" style="font-size:0.8rem;">{{ $receipt->receipt_id }}</td>
                                <td>{{ tgl($receipt->items->first()->date ?? '') }}</td>
                                <td>{{ $receipt->item_count }} item</td>
                                <td>
                                    @foreach($receipt->items as $item)
                                    <div style="font-size:0.8rem;">{{ $item->product->name ?? '-' }} <span class="text-muted">x{{ $item->qty }}</span></div>
                                    @endforeach
                                </td>
                                <td class="fw-semibold">{{ rp($receipt->total) }}</td>
                                <td class="pe-3">
                                    <a href="{{ route('stock.receipt.pdf', $receipt->receipt_id) }}" target="_blank" class="btn btn-modern btn-primary btn-sm py-0 px-2" style="font-size:0.7rem;">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <a href="{{ route('stock.receipt', $receipt->receipt_id) }}" target="_blank" class="btn btn-modern btn-success btn-sm py-0 px-2" style="font-size:0.7rem;">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if(Auth::user()->isAdmin())
                                    <form autocomplete="off" action="{{ route('stock.sales.destroy', $receipt->receipt_id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin hapus penjualan {{ $receipt->receipt_id }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-modern btn-danger btn-sm py-0 px-2" style="font-size:0.7rem;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada penjualan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
                <div>
                    <span style="font-size:0.8rem;color:var(--text-muted);">{{ $history->count() }} dari {{ $history->total() }} data</span>
                </div>
                <div>
                    <span style="font-size:0.75rem;color:var(--text-muted);">Total Penjualan</span>
                    <span class="fw-bold ms-2" style="font-size:0.95rem;color:var(--text-primary);">{{ rp($totalSales) }}</span>
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
var cart = {};
var totalRp = 0;

function formatRp(n) {
    return 'Rp ' + n.toLocaleString('id-ID');
}

function renderCart() {
    var container = document.getElementById('pos-cart-items');
    var empty = document.getElementById('pos-cart-empty');
    var footer = document.getElementById('pos-cart-footer');
    var ids = Object.keys(cart);

    if (ids.length === 0) {
        empty.style.display = '';
        container.style.display = 'none';
        footer.style.display = 'none';
        document.getElementById('pos-submit').disabled = true;
        return;
    }

    empty.style.display = 'none';
    container.style.display = '';
    footer.style.display = '';

    var html = '';
    var total = 0;
    var itemCount = 0;

    ids.forEach(function (id) {
        var item = cart[id];
        var subtotal = item.qty * item.price;
        total += subtotal;
        itemCount += item.qty;

        html += '<div class="d-flex align-items-center gap-2 mb-2 p-2 rounded-3" style="background:rgba(255,255,255,0.04);border:1px solid var(--border-subtle);">';
        html += '<div class="flex-grow-1"><div class="fw-semibold" style="font-size:0.85rem;">' + item.name + '</div>';
        html += '<small class="text-muted">' + formatRp(item.price) + ' / ' + item.unit + ' (sisa ' + (item.stock - item.qty) + ')</small></div>';
        html += '<div class="d-flex align-items-center gap-1"><button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 pos-qty-minus" data-id="' + id + '" style="border-radius:6px;font-size:0.7rem;">−</button>';
        html += '<span class="fw-bold px-2" style="min-width:24px;text-align:center;">' + item.qty + '</span>';
        html += '<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 pos-qty-plus" data-id="' + id + '" style="border-radius:6px;font-size:0.7rem;">+</button></div>';
        html += '<div class="fw-bold text-end" style="min-width:80px;">' + formatRp(subtotal) + '</div>';
        html += '<button type="button" class="btn btn-sm btn-danger py-0 px-2 pos-remove" data-id="' + id + '" style="border-radius:8px;font-size:0.7rem;"><i class="fas fa-times"></i></button>';
        html += '<input type="hidden" name="items[' + id + '][product_id]" value="' + id + '">';
        html += '<input type="hidden" name="items[' + id + '][qty]" value="' + item.qty + '">';
        html += '<input type="hidden" name="items[' + id + '][price]" value="' + item.price + '">';
        html += '</div>';
    });

    container.innerHTML = html;
    totalRp = total;
    document.getElementById('pos-total-item').textContent = itemCount;
    document.getElementById('pos-grand-total').textContent = formatRp(total);
    document.getElementById('pos-submit').innerHTML = '<i class="fas fa-check me-1"></i>Bayar ' + formatRp(total);
    document.getElementById('pos-submit').disabled = false;
    hitungKembali();
}

function addToCart(id, name, price, unit, stock) {
    var newQty = cart[id] ? cart[id].qty + 1 : 1;
    if (newQty > stock) newQty = stock;
    cart[id] = { id: id, name: name, price: price, unit: unit, stock: stock, qty: newQty };
    renderCart();
}

function updateQty(id, delta) {
    if (!cart[id]) return;
    var newQty = cart[id].qty + delta;
    if (delta > 0 && newQty > cart[id].stock) newQty = cart[id].stock;
    if (newQty < 1) {
        delete cart[id];
    } else {
        cart[id].qty = newQty;
    }
    renderCart();
}

function removeFromCart(id) {
    delete cart[id];
    renderCart();
}

function hitungKembali() {
    var bayar = parseInt(document.getElementById('pos-bayar').value) || 0;
    var kembali = bayar - totalRp;
    document.getElementById('pos-kembali').value = kembali >= 0 ? 'Rp ' + kembali.toLocaleString('id-ID') : 'Kurang Rp ' + Math.abs(kembali).toLocaleString('id-ID');
}

// Sync selects to hidden inputs
function syncForm() {
    var accountEl = document.getElementById('pos-account-select');
    var dateEl = document.getElementById('pos-date-input');
    document.getElementById('pos-account').value = accountEl.value;
    document.getElementById('pos-date').value = dateEl.value;

    document.getElementById('pos-submit').disabled = !accountEl.value || Object.keys(cart).length === 0;
}

// Product grid click
document.getElementById('pos-product-grid').addEventListener('click', function (e) {
    var btn = e.target.closest('.pos-product-btn');
    if (!btn || btn.disabled) return;
    addToCart(parseInt(btn.dataset.id), btn.dataset.name, parseInt(btn.dataset.price), btn.dataset.unit, parseInt(btn.dataset.stock));
});

// Cart events via delegation
document.getElementById('pos-cart').addEventListener('click', function (e) {
    var target = e.target.closest('button');
    if (!target) return;
    if (target.classList.contains('pos-qty-plus')) updateQty(parseInt(target.dataset.id), 1);
    else if (target.classList.contains('pos-qty-minus')) updateQty(parseInt(target.dataset.id), -1);
    else if (target.classList.contains('pos-remove')) removeFromCart(parseInt(target.dataset.id));
});

// Bayar input
document.getElementById('pos-bayar').addEventListener('input', hitungKembali);

// Category filter
document.getElementById('pos-category-filter').addEventListener('change', function () {
    var cat = this.value;
    document.querySelectorAll('.pos-product-item').forEach(function (el) {
        el.style.display = (!cat || el.dataset.category === cat) ? '' : 'none';
    });
});

// Account & date change
document.getElementById('pos-account-select').addEventListener('change', syncForm);
document.getElementById('pos-date-input').addEventListener('change', syncForm);

// Form submit - validate
document.getElementById('formPenjualan').addEventListener('submit', function (e) {
    if (Object.keys(cart).length === 0) {
        e.preventDefault();
        alert('Keranjang masih kosong!');
        return;
    }
    if (!document.getElementById('pos-account-select').value) {
        e.preventDefault();
        alert('Pilih akun penerimaan!');
        return;
    }
    syncForm();
});

// Auto-print PDF after successful submit
@if(session('receipt_id'))
document.addEventListener('DOMContentLoaded', function () {
    var r = @json(session('receipt_id'));
    if (confirm('Cetak resi untuk ' + r + '?')) {
        window.open(@json(route('stock.receipt.pdf', session('receipt_id'))), '_blank');
    }
});
@endif
</script>
@endpush

<style>
.pos-product-btn:hover {
    border-color: var(--theme-primary);
    background: rgba(var(--theme-primary-rgb), 0.05);
}
.pos-product-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
