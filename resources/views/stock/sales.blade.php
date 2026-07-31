@extends('layouts.app')
@section('title', 'POS Penjualan')

@section('content')
<form autocomplete="off" method="POST" action="{{ route('stock.sales.store') }}" id="formPenjualan">
    @csrf
    <input type="hidden" name="account_id" id="pos-account" value="">
    <input type="hidden" name="date" id="pos-date" value="">
    <input type="hidden" name="discount" id="pos-discount-hidden" value="0">
    <input type="hidden" name="paid_amount" id="pos-paid-hidden" value="0">
    <input type="hidden" name="change_amount" id="pos-change-hidden" value="0">

    <div class="pos-layout">
        {{-- LEFT: Products --}}
        <div class="pos-products">
            <div class="pos-products-header">
                <div class="pos-search-wrap">
                    <i class="fas fa-search pos-search-icon"></i>
                    <input type="text" id="pos-search" class="pos-search-input" placeholder="Cari barang...">
                </div>
                <div class="pos-account-date">
                    <select id="pos-account-select" class="pos-select" required>
                        <option value="">Akun</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ $account->name === 'Cash' ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                    <input type="date" id="pos-date-input" value="{{ date('Y-m-d') }}" class="pos-input-date">
                </div>
            </div>

            <div class="pos-category-tabs" id="pos-category-tabs">
                <button type="button" class="pos-cat-tab active" data-cat="">Semua</button>
                @foreach($categories as $cat)
                <button type="button" class="pos-cat-tab" data-cat="{{ $cat->id }}">{{ $cat->name }}</button>
                @endforeach
            </div>

            <div class="pos-product-grid" id="pos-product-grid">
                @foreach($products as $product)
                <div class="pos-product-card {{ $product->stock < 1 ? 'pos-out-of-stock' : '' }}"
                    data-category="{{ $product->category_id }}"
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-price="{{ $product->selling_price }}"
                    data-unit="{{ $product->unit }}"
                    data-stock="{{ $product->stock }}">
                    <div class="pos-product-name">{{ $product->name }}</div>
                    <div class="pos-product-price">{{ rp($product->selling_price) }}</div>
                    <div class="pos-product-stock">
                        @if($product->stock > 0)
                            Stok: {{ $product->stock }} {{ $product->unit }}
                        @else
                            <span class="text-danger">Habis</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- RIGHT: Cart --}}
        <div class="pos-cart">
            <div class="pos-cart-header">
                <div class="pos-cart-title">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Keranjang</span>
                    <span class="pos-cart-count" id="pos-cart-count">0</span>
                </div>
            </div>

            <div class="pos-cart-body" id="pos-cart-body">
                <div class="pos-cart-empty" id="pos-cart-empty">
                    <i class="fas fa-cart-plus"></i>
                    <p>Klik barang untuk menambah</p>
                </div>
                <div class="pos-cart-items" id="pos-cart-items"></div>
            </div>

            <div class="pos-cart-footer" id="pos-cart-footer" style="display:none;">
                <div class="pos-cart-summary">
                    <div class="pos-summary-row">
                        <span>Subtotal</span>
                        <span id="pos-subtotal">Rp 0</span>
                    </div>
                    <div class="pos-summary-row">
                        <span>Diskon</span>
                        <input type="number" id="pos-diskon" placeholder="0" min="0" value="0" style="min-width:80px;max-width:120px;width:100%;text-align:right;border:1px solid #ddd;border-radius:6px;padding:4px 8px;font-size:0.9rem;">
                    </div>
                    <div class="pos-summary-row pos-summary-total">
                        <span>Total</span>
                        <span id="pos-grand-total">Rp 0</span>
                    </div>
                </div>

                <div class="pos-cart-bayar">
                    <div class="pos-bayar-input">
                        <label>Bayar</label>
                        <input type="number" id="pos-bayar" placeholder="0" min="0">
                    </div>
                    <div class="pos-bayar-input">
                        <label>Kembali</label>
                        <input type="text" id="pos-kembali" readonly value="Rp 0">
                    </div>
                </div>

                <button type="submit" class="pos-btn-bayar" id="pos-submit" disabled>
                    <i class="fas fa-check"></i> BAYAR
                </button>
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
                                <th class="ps-3 sortable" data-sort="string">Nota</th>
                                <th class="sortable" data-sort="date">Tanggal</th>
                                <th class="sortable" data-sort="number">Item</th>
                                <th class="sortable" data-sort="string">Barang</th>
                                <th class="sortable" data-sort="number">Total</th>
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
                                    @if(Auth::user()->hasPermission(config('permissions.RETURNS')))
                                    <a href="{{ route('returns.index', ['search' => $receipt->receipt_id]) }}" class="btn btn-modern btn-warning btn-sm py-0 px-2" style="font-size:0.7rem;" title="Retur">
                                        <i class="fas fa-undo-alt"></i>
                                    </a>
                                    @endif
                                    @if(Auth::user()->isAdmin())
                                    <form autocomplete="off" action="{{ route('stock.sales.destroy', $receipt->receipt_id) }}" method="POST" class="d-inline"
                                        onsubmit="event.preventDefault(); confirmDelete('Yakin hapus penjualan {{ $receipt->receipt_id }}?').then(ok => ok && this.submit());">
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

@push('styles')
<style>
/* POS Layout */
.pos-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 1rem;
    min-height: calc(100vh - 200px);
}

/* Products Panel */
.pos-products {
    display: flex;
    flex-direction: column;
    background: var(--bg-card);
    border-radius: 12px;
    border: 1px solid var(--border-subtle);
    overflow: hidden;
}

.pos-products-header {
    padding: 1rem;
    display: flex;
    gap: 0.75rem;
    align-items: center;
    border-bottom: 1px solid var(--border-subtle);
}

.pos-search-wrap {
    flex: 1;
    position: relative;
}

.pos-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 0.85rem;
}

.pos-search-input {
    width: 100%;
    padding: 0.5rem 0.75rem 0.5rem 2.2rem;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    font-size: 0.85rem;
    background: var(--bg-card);
    color: var(--text-primary);
    outline: none;
    transition: border-color 0.2s;
}

.pos-search-input:focus {
    border-color: var(--theme-primary);
}

.pos-account-date {
    display: flex;
    gap: 0.5rem;
}

.pos-select, .pos-input-date {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    font-size: 0.8rem;
    background: var(--bg-card);
    color: var(--text-primary);
    outline: none;
}

.pos-select:focus, .pos-input-date:focus {
    border-color: var(--theme-primary);
}

/* Category Tabs */
.pos-category-tabs {
    display: flex;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    overflow-x: auto;
    border-bottom: 1px solid var(--border-subtle);
    scrollbar-width: none;
}

.pos-category-tabs::-webkit-scrollbar {
    display: none;
}

.pos-cat-tab {
    padding: 0.4rem 1rem;
    border: 1px solid var(--border-subtle);
    border-radius: 20px;
    background: transparent;
    color: var(--text-muted);
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.2s;
}

.pos-cat-tab:hover {
    border-color: var(--theme-primary);
    color: var(--theme-primary);
}

.pos-cat-tab.active {
    background: var(--theme-primary);
    border-color: var(--theme-primary);
    color: #fff;
}

/* Product Grid */
.pos-product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 0.75rem;
    padding: 1rem;
    overflow-y: auto;
    max-height: calc(100vh - 280px);
}

.pos-product-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 0.85rem;
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
}

.pos-product-card:hover {
    border-color: var(--theme-primary);
    box-shadow: 0 2px 8px rgba(var(--theme-primary-rgb), 0.15);
    transform: translateY(-1px);
}

.pos-product-card.pos-out-of-stock {
    opacity: 0.4;
    cursor: not-allowed;
}

.pos-product-card.pos-out-of-stock:hover {
    transform: none;
    box-shadow: none;
    border-color: var(--border-subtle);
}

.pos-product-name {
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--text-primary);
    margin-bottom: 0.35rem;
    line-height: 1.2;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pos-product-price {
    font-weight: 800;
    font-size: 0.9rem;
    color: var(--theme-primary);
    margin-bottom: 0.25rem;
}

.pos-product-stock {
    font-size: 0.7rem;
    color: var(--text-muted);
}

/* Cart Panel */
.pos-cart {
    display: flex;
    flex-direction: column;
    background: var(--bg-card);
    border-radius: 12px;
    border: 1px solid var(--border-subtle);
    position: sticky;
    top: 1rem;
    max-height: calc(100vh - 2rem);
}

.pos-cart-header {
    padding: 1rem;
    border-bottom: 1px solid var(--border-subtle);
}

.pos-cart-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
    font-size: 1rem;
    color: var(--text-primary);
}

.pos-cart-title i {
    color: var(--theme-primary);
}

.pos-cart-count {
    background: var(--theme-primary);
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.5rem;
    border-radius: 10px;
    margin-left: auto;
}

/* Cart Body */
.pos-cart-body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    min-height: 200px;
}

.pos-cart-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 180px;
    color: var(--text-muted);
    font-size: 0.85rem;
}

.pos-cart-empty i {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
    opacity: 0.3;
}

.pos-cart-items {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.pos-cart-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.6rem;
    border-radius: 8px;
    background: rgba(var(--theme-primary-rgb), 0.03);
    border: 1px solid var(--border-subtle);
}

.pos-cart-item-info {
    flex: 1;
    min-width: 0;
}

.pos-cart-item-name {
    font-weight: 600;
    font-size: 0.8rem;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pos-cart-item-price {
    font-size: 0.7rem;
    color: var(--text-muted);
}

.pos-cart-item-qty {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.pos-qty-btn {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border-subtle);
    border-radius: 6px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.1s;
    padding: 0;
}

.pos-qty-btn:hover {
    background: var(--border-subtle);
}

.pos-qty-btn.pos-qty-minus:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.pos-qty-btn.pos-qty-plus:hover {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.pos-qty-num {
    font-weight: 700;
    font-size: 0.85rem;
    min-width: 20px;
    text-align: center;
}

.pos-cart-item-subtotal {
    font-weight: 700;
    font-size: 0.8rem;
    color: var(--text-primary);
    min-width: 70px;
    text-align: right;
}

.pos-cart-item-remove {
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: 6px;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    font-size: 0.65rem;
    cursor: pointer;
    transition: all 0.1s;
    padding: 0;
}

.pos-cart-item-remove:hover {
    background: #ef4444;
    color: #fff;
}

/* Cart Footer */
.pos-cart-footer {
    border-top: 1px solid var(--border-subtle);
    padding: 1rem;
}

.pos-cart-summary {
    margin-bottom: 0.75rem;
}

.pos-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.3rem 0;
    font-size: 0.85rem;
    color: var(--text-muted);
}

.pos-summary-total {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--text-primary);
    border-top: 1px solid var(--border-subtle);
    padding-top: 0.5rem;
    margin-top: 0.25rem;
}

/* Bayar Section */
.pos-cart-bayar {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.pos-bayar-input label {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}

.pos-bayar-input input {
    width: 100%;
    padding: 0.5rem 0.6rem;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    background: var(--bg-card);
    color: var(--text-primary);
    outline: none;
}

.pos-bayar-input input:focus {
    border-color: var(--theme-primary);
}

.pos-bayar-input input[readonly] {
    background: rgba(var(--theme-primary-rgb), 0.05);
    color: var(--theme-primary);
}

/* Bayar Button */
.pos-btn-bayar {
    width: 100%;
    padding: 0.85rem;
    border: none;
    border-radius: 10px;
    background: var(--theme-primary);
    color: #fff;
    font-size: 1rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    letter-spacing: 0.05em;
}

.pos-btn-bayar:hover:not(:disabled) {
    filter: brightness(1.1);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(var(--theme-primary-rgb), 0.3);
}

.pos-btn-bayar:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* Responsive */
@media (max-width: 992px) {
    .pos-layout {
        grid-template-columns: 1fr;
    }

    .pos-cart {
        position: static;
        max-height: none;
    }

    .pos-product-grid {
        max-height: 400px;
    }
}

@media (max-width: 576px) {
    .pos-products-header {
        flex-direction: column;
    }

    .pos-account-date {
        width: 100%;
    }

    .pos-select, .pos-input-date {
        flex: 1;
    }

    .pos-product-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }
}
</style>
@endpush

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
    var countEl = document.getElementById('pos-cart-count');
    var ids = Object.keys(cart);

    if (ids.length === 0) {
        empty.style.display = '';
        container.style.display = 'none';
        footer.style.display = 'none';
        countEl.textContent = '0';
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

        html += '<div class="pos-cart-item">';
        html += '<div class="pos-cart-item-info">';
        html += '<div class="pos-cart-item-name">' + item.name + '</div>';
        html += '<div class="pos-cart-item-price">' + formatRp(item.price) + ' / ' + item.unit + '</div>';
        html += '</div>';
        html += '<div class="pos-cart-item-qty">';
        html += '<button type="button" class="pos-qty-btn pos-qty-minus" data-id="' + id + '">−</button>';
        html += '<span class="pos-qty-num">' + item.qty + '</span>';
        html += '<button type="button" class="pos-qty-btn pos-qty-plus" data-id="' + id + '">+</button>';
        html += '</div>';
        html += '<div class="pos-cart-item-subtotal">' + formatRp(subtotal) + '</div>';
        html += '<button type="button" class="pos-cart-item-remove pos-remove" data-id="' + id + '"><i class="fas fa-times"></i></button>';
        html += '<input type="hidden" name="items[' + id + '][product_id]" value="' + id + '">';
        html += '<input type="hidden" name="items[' + id + '][qty]" value="' + item.qty + '">';
        html += '<input type="hidden" name="items[' + id + '][price]" value="' + item.price + '">';
        html += '</div>';
    });

    container.innerHTML = html;
    totalRp = total;
    countEl.textContent = itemCount;
    document.getElementById('pos-subtotal').textContent = formatRp(total);
    updateGrandTotal();
    document.getElementById('pos-submit').disabled = false;
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
    var bayar = numVal(document.getElementById('pos-bayar'));
    var diskon = numVal(document.getElementById('pos-diskon'));
    var grandTotal = totalRp - diskon;
    if (grandTotal < 0) grandTotal = 0;
    var kembali = bayar - grandTotal;
    var el = document.getElementById('pos-kembali');
    document.getElementById('pos-paid-hidden').value = bayar;
    if (bayar === 0) {
        el.value = 'Rp 0';
        document.getElementById('pos-change-hidden').value = 0;
    } else if (kembali >= 0) {
        el.value = formatRp(kembali);
        document.getElementById('pos-change-hidden').value = kembali;
    } else {
        el.value = 'Kurang ' + formatRp(Math.abs(kembali));
        document.getElementById('pos-change-hidden').value = 0;
    }
}

function updateGrandTotal() {
    var diskon = numVal(document.getElementById('pos-diskon'));
    var grandTotal = totalRp - diskon;
    if (grandTotal < 0) grandTotal = 0;
    document.getElementById('pos-grand-total').textContent = formatRp(grandTotal);
    document.getElementById('pos-discount-hidden').value = diskon;
    document.getElementById('pos-submit').innerHTML = '<i class="fas fa-check"></i> BAYAR ' + formatRp(grandTotal);
    hitungKembali();
}

function syncForm() {
    document.getElementById('pos-account').value = document.getElementById('pos-account-select').value;
    document.getElementById('pos-date').value = document.getElementById('pos-date-input').value;
    document.getElementById('pos-submit').disabled = !document.getElementById('pos-account-select').value || Object.keys(cart).length === 0;
}

// Product grid click
document.getElementById('pos-product-grid').addEventListener('click', function (e) {
    var card = e.target.closest('.pos-product-card');
    if (!card || card.classList.contains('pos-out-of-stock')) return;
    addToCart(parseInt(card.dataset.id), card.dataset.name, parseInt(card.dataset.price), card.dataset.unit, parseInt(card.dataset.stock));
});

// Cart events
document.getElementById('pos-cart-body').addEventListener('click', function (e) {
    var target = e.target.closest('button');
    if (!target) return;
    if (target.classList.contains('pos-qty-plus')) updateQty(parseInt(target.dataset.id), 1);
    else if (target.classList.contains('pos-qty-minus')) updateQty(parseInt(target.dataset.id), -1);
    else if (target.classList.contains('pos-remove')) removeFromCart(parseInt(target.dataset.id));
});

// Bayar input
document.getElementById('pos-bayar').addEventListener('input', hitungKembali);
document.getElementById('pos-diskon').addEventListener('input', updateGrandTotal);

// Category tabs
document.getElementById('pos-category-tabs').addEventListener('click', function (e) {
    var tab = e.target.closest('.pos-cat-tab');
    if (!tab) return;

    document.querySelectorAll('.pos-cat-tab').forEach(function (t) { t.classList.remove('active'); });
    tab.classList.add('active');

    var cat = tab.dataset.cat;
    document.querySelectorAll('.pos-product-card').forEach(function (el) {
        el.style.display = (!cat || el.dataset.category === cat) ? '' : 'none';
    });
});

// Search
document.getElementById('pos-search').addEventListener('input', function () {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.pos-product-card').forEach(function (el) {
        var match = el.dataset.name.toLowerCase().includes(q);
        var catActive = document.querySelector('.pos-cat-tab.active');
        var catMatch = !catActive || !catActive.dataset.cat || el.dataset.category === catActive.dataset.cat;
        el.style.display = (match && catMatch) ? '' : 'none';
    });
});

// Account & date change
document.getElementById('pos-account-select').addEventListener('change', syncForm);
document.getElementById('pos-date-input').addEventListener('change', syncForm);

// Form submit
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

// Auto-print after success
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
