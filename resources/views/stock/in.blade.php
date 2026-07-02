@extends('layouts.app')
@section('title', 'Stok Masuk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-arrow-down me-2" style="color:#10b981;"></i>Stok Masuk</h4>
</div>

<form autocomplete="off" method="POST" action="{{ route('stock.in.store') }}" id="form-stock-in">
    @csrf
    <input type="hidden" name="account_id" id="in-account-hidden" value="">
    <input type="hidden" name="date" id="in-date-hidden" value="">
    <div id="in-cart-inputs"></div>

    <div class="row g-3 mb-4">
        {{-- Left Panel: Product Selection --}}
        <div class="col-lg-7">
            <div class="card card-modern shadow-sm">
                <div class="card-header">
                    <span class="fw-semibold"><i class="fas fa-plus-circle me-2 text-success"></i>Tambah ke Keranjang</span>
                </div>
                <div class="card-body">
                    {{-- Search --}}
                    <div class="mb-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="in-product-search" class="form-control" placeholder="Cari barang..." autocomplete="off">
                            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalQuickAdd" title="Tambah Barang Baru">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Shared Fields --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small">Akun Pembayaran</label>
                            <select id="in-account" class="form-select form-select-sm" required>
                                <option value="">Pilih akun</option>
                                @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $account->name === 'Cash' ? 'selected' : '' }}>{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Tanggal</label>
                            <input type="date" id="in-date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    {{-- Product Grid --}}
                    <div class="mb-2 d-flex flex-wrap gap-1" id="in-category-tabs">
                        <button type="button" class="btn btn-sm in-cat-tab active" data-category="">Semua</button>
                        @foreach($categories as $cat)
                        <button type="button" class="btn btn-sm in-cat-tab" data-category="{{ $cat->name }}">{{ $cat->name }}</button>
                        @endforeach
                    </div>
                    <div class="in-product-grid" id="in-product-grid">
                        @foreach($products as $product)
                        <div class="in-product-card" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->purchase_price }}" data-unit="{{ $product->unit }}" data-stock="{{ $product->stock }}" data-category="{{ $product->category->name ?? '' }}">
                            <div class="in-product-name">{{ $product->name }}</div>
                            <div class="in-product-price">{{ rp($product->purchase_price) }}</div>
                            <div class="in-product-stock">Stok: {{ $product->stock }} {{ $product->unit }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Panel: Cart --}}
        <div class="col-lg-5">
            <div class="card card-modern shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="fas fa-shopping-cart me-2"></i>Keranjang Stok Masuk</span>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 d-none" id="in-clear-all" onclick="inClearAll()" title="Hapus semua">
                            <i class="fas fa-trash-alt" style="font-size:0.7rem;"></i>
                        </button>
                        <span class="badge bg-success" id="in-cart-count">0</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    {{-- Empty State --}}
                    <div id="in-cart-empty" class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        <span>Klik barang untuk menambah ke keranjang</span>
                    </div>
                    {{-- Cart Items --}}
                    <div id="in-cart-body" class="d-none" style="max-height:400px;overflow-y:auto;"></div>
                    {{-- Cart Footer --}}
                    <div id="in-cart-footer" class="d-none" style="border-top:2px solid var(--border-subtle);">
                        <div class="px-3 py-2 d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-4">
                                <div>
                                    <span class="small" style="color:var(--text-muted);">Total Qty</span>
                                    <span class="fw-bold ms-2" style="color:var(--text-primary);" id="in-total-qty">0</span>
                                </div>
                                <div>
                                    <span class="small" style="color:var(--text-muted);">Total Nilai</span>
                                    <span class="fw-bold ms-2" style="color:var(--text-primary);" id="in-total-value">Rp 0</span>
                                </div>
                            </div>
                        </div>
                        <div class="px-3 pb-3">
                            <button type="submit" class="btn btn-modern btn-success w-100" id="in-submit-btn" disabled>
                                <i class="fas fa-save me-1"></i>Simpan Stok Masuk
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Modal Tambah Barang --}}
<div class="modal fade" id="modalQuickAdd" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-quick-add" autocomplete="off">
                <div class="modal-header">
                    <h6 class="modal-title fw-semibold"><i class="fas fa-plus-circle me-2 text-success"></i>Tambah Barang Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="100" placeholder="Contoh: Telkomsel 10K">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Pilih</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" required maxlength="20" placeholder="pcs" value="pcs">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                            <input type="number" name="purchase_price" class="form-control" required min="0" placeholder="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                            <input type="number" name="selling_price" class="form-control" required min="0" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm" id="btn-quick-add-submit">
                        <i class="fas fa-save me-1"></i>Simpan & Tambah ke Keranjang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Riwayat --}}
<div class="card card-modern shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold"><i class="fas fa-history me-2"></i>Riwayat Stok Masuk</span>
        <div class="d-flex align-items-center gap-2">
            <form autocomplete="off" method="GET" action="{{ route('stock.in') }}" class="d-flex align-items-center gap-1">
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control form-control-sm" style="width:140px;" placeholder="Dari">
                <span class="text-muted small">-</span>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control form-control-sm" style="width:140px;" placeholder="Sampai">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-filter"></i></button>
                @if(!empty($filters['date_from']) || !empty($filters['date_to']))
                <a href="{{ route('stock.in') }}" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="fas fa-times"></i></a>
                @endif
            </form>
            <div class="input-group input-group-sm" style="width:180px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="in-history-search" class="form-control" placeholder="Cari barang..." autocomplete="off">
            </div>
        </div>
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
                        <td class="fw-semibold">{{ rp($trx->qty * $trx->price) }}</td>
                        <td>{{ $trx->account->name ?? '-' }}</td>
                        <td>{{ $trx->description ?? '-' }}</td>
                        <td class="pe-3">
                            @if(Auth::user()->isAdmin())
                            <form autocomplete="off" action="{{ route('stock.in.destroy', $trx->id) }}" method="POST" class="d-inline"
                                onsubmit="event.preventDefault(); confirmDelete('Yakin hapus stok masuk {{ $trx->product->name ?? '' }} ({{ $trx->qty }} {{ $trx->product->unit ?? '' }})?').then(ok => ok && this.submit());">
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
                        <td colspan="7" class="text-center text-muted py-4">Belum ada stok masuk</td>
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
        <div class="d-flex gap-4">
            <div>
                <span style="font-size:0.75rem;color:var(--text-muted);">Total Qty</span>
                <span class="fw-bold ms-2" style="font-size:0.95rem;color:var(--text-primary);">{{ number_format($totalQty, 0, ',', '.') }}</span>
            </div>
            <div>
                <span style="font-size:0.75rem;color:var(--text-muted);">Total Nilai</span>
                <span class="fw-bold ms-2" style="font-size:0.95rem;color:var(--text-primary);">{{ rp($totalValue) }}</span>
            </div>
        </div>
    </div>
    @if($history->hasPages())
    <div class="card-footer bg-white">
        <div class="pagination-modern">{{ $history->links() }}</div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
/* Category Tabs */
.in-cat-tab {
    font-size: 0.72rem;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid var(--border-subtle);
    background: var(--bg-card);
    color: var(--text-muted);
    font-weight: 500;
    transition: all 0.15s;
}
.in-cat-tab:hover {
    border-color: #10b981;
    color: #10b981;
}
.in-cat-tab.active {
    background: #10b981;
    color: #fff;
    border-color: #10b981;
}

/* Product Grid */
.in-product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 6px;
    max-height: 440px;
    overflow-y: auto;
    padding: 4px 0;
}
.in-product-card {
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
    background: var(--bg-card);
}
.in-product-card:hover {
    border-color: #10b981;
    background: #f0fdf4;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(16,185,129,0.12);
}
.in-product-name {
    font-size: 0.82rem;
    font-weight: 600;
    margin-bottom: 4px;
    line-height: 1.2;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.in-product-price {
    font-size: 0.78rem;
    color: #10b981;
    font-weight: 700;
}
.in-product-stock {
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-top: 2px;
}

/* Cart Items */
.in-cart-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 8px 12px;
    border-bottom: 1px solid var(--border-subtle);
    position: relative;
}
.in-cart-item:last-child {
    border-bottom: none;
}
.in-cart-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.in-cart-item-name {
    font-weight: 600;
    font-size: 0.82rem;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.in-cart-item-remove {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
    font-size: 0.8rem;
    padding: 2px 4px;
    border-radius: 4px;
    flex-shrink: 0;
}
.in-cart-item-remove:hover {
    background: #fef2f2;
}
.in-cart-item-controls {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.in-cart-qty-btn {
    width: 24px;
    height: 24px;
    border: 1px solid var(--border-subtle);
    border-radius: 6px;
    background: var(--bg-card);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.8rem;
    color: var(--text-primary);
    transition: all 0.15s;
    flex-shrink: 0;
}
.in-cart-qty-btn:hover {
    border-color: #10b981;
    color: #10b981;
}
.in-cart-qty-val {
    min-width: 28px;
    text-align: center;
    font-weight: 700;
    font-size: 0.85rem;
}
.in-cart-price-input {
    width: 90px;
    border: 1px solid var(--border-subtle);
    border-radius: 6px;
    padding: 3px 6px;
    font-size: 0.78rem;
    text-align: right;
}
.in-cart-price-input:focus {
    outline: none;
    border-color: #10b981;
}
.in-cart-subtotal {
    font-weight: 700;
    font-size: 0.8rem;
    color: #10b981;
    min-width: 80px;
    text-align: right;
}
.in-cart-separator {
    width: 1px;
    height: 18px;
    background: var(--border-subtle);
    flex-shrink: 0;
}
.in-cart-discount-label {
    font-size: 0.7rem;
    color: #ca8a04;
    font-weight: 600;
    flex-shrink: 0;
}
.in-cart-discount-input {
    width: 60px;
    border: 1px solid #fde68a;
    border-radius: 6px;
    padding: 2px 5px;
    font-size: 0.75rem;
    text-align: right;
    color: #ca8a04;
}
.in-cart-discount-input:focus {
    outline: none;
    border-color: #ca8a04;
}
.in-cart-net-value {
    font-weight: 700;
    font-size: 0.8rem;
    color: #10b981;
    min-width: 70px;
    text-align: right;
}
.in-cart-expand-btn {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 0.75rem;
    padding: 2px 4px;
}
.in-cart-expand-btn:hover {
    color: var(--text-primary);
}
.in-cart-extra {
    display: none;
    padding-top: 6px;
}
.in-cart-extra.show {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.in-cart-extra input {
    border: 1px solid var(--border-subtle);
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 0.78rem;
    flex: 1;
    min-width: 120px;
}
.in-cart-extra input:focus {
    outline: none;
    border-color: #10b981;
}
.in-cart-unit {
    font-size: 0.78rem;
    color: var(--text-muted);
    min-width: 36px;
    text-align: center;
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var cart = {};

    var elGrid = document.getElementById('in-product-grid');
    var elSearch = document.getElementById('in-product-search');
    var elCartBody = document.getElementById('in-cart-body');
    var elCartEmpty = document.getElementById('in-cart-empty');
    var elCartFooter = document.getElementById('in-cart-footer');
    var elCartCount = document.getElementById('in-cart-count');
    var elTotalQty = document.getElementById('in-total-qty');
    var elTotalValue = document.getElementById('in-total-value');
    var elCartInputs = document.getElementById('in-cart-inputs');
    var elSubmitBtn = document.getElementById('in-submit-btn');
    var elAccount = document.getElementById('in-account');
    var elDate = document.getElementById('in-date');
    var elCategoryTabs = document.getElementById('in-category-tabs');
    var activeCategory = '';

    // --- Category Tab Filter ---
    elCategoryTabs.addEventListener('click', function(e) {
        var tab = e.target.closest('.in-cat-tab');
        if (!tab) return;

        elCategoryTabs.querySelectorAll('.in-cat-tab').forEach(function(t) { t.classList.remove('active'); });
        tab.classList.add('active');
        activeCategory = tab.getAttribute('data-category');
        filterProducts();
    });

    // --- Product Search Filter ---
    elSearch.addEventListener('input', function() {
        filterProducts();
    });

    function filterProducts() {
        var q = elSearch.value.toLowerCase();
        var cards = elGrid.querySelectorAll('.in-product-card');
        cards.forEach(function(card) {
            if (card.classList.contains('in-cart-hidden')) {
                card.style.display = 'none';
                return;
            }
            var name = card.getAttribute('data-name').toLowerCase();
            var cat = card.getAttribute('data-category');
            var matchSearch = name.includes(q);
            var matchCategory = !activeCategory || cat === activeCategory;
            card.style.display = (matchSearch && matchCategory) ? '' : 'none';
        });
    }

    // --- Hide/Show card helpers ---
    function hideCard(id) {
        var card = elGrid.querySelector('.in-product-card[data-id="' + id + '"]');
        if (card) {
            card.classList.add('in-cart-hidden');
            card.style.display = 'none';
        }
    }

    function showCard(id) {
        var card = elGrid.querySelector('.in-product-card[data-id="' + id + '"]');
        if (card) {
            card.classList.remove('in-cart-hidden');
            filterProducts();
        }
    }

    // --- Add to Cart ---
    elGrid.addEventListener('click', function(e) {
        var card = e.target.closest('.in-product-card');
        if (!card) return;

        var id = parseInt(card.getAttribute('data-id'));
        var name = card.getAttribute('data-name');
        var price = parseInt(card.getAttribute('data-price'));
        var unit = card.getAttribute('data-unit');
        var stock = parseInt(card.getAttribute('data-stock'));
        var category = card.getAttribute('data-category');

        if (cart[id]) {
            cart[id].qty++;
        } else {
            cart[id] = {
                id: id,
                name: name,
                price: price,
                unit: unit,
                stock: stock,
                qty: 1,
                description: '',
                expired_at: '',
                category: category,
                discount: 0
            };
            hideCard(id);
        }
        renderCart();
    });

    // --- Update Qty ---
    window.inUpdateQty = function(id, delta) {
        if (!cart[id]) return;
        cart[id].qty += delta;
        if (cart[id].qty < 1) {
            delete cart[id];
            showCard(id);
        }
        renderCart();
    };

    // --- Remove Item ---
    window.inRemoveItem = function(id) {
        delete cart[id];
        showCard(id);
        renderCart();
    };

    // --- Clear All Items ---
    window.inClearAll = function() {
        Object.keys(cart).forEach(function(id) {
            showCard(parseInt(id));
        });
        cart = {};
        renderCart();
    };

    // --- Toggle Extra Fields ---
    window.inToggleExtra = function(id) {
        var el = document.getElementById('in-extra-' + id);
        if (el) el.classList.toggle('show');
    };

    // --- Update Price ---
    window.inUpdatePrice = function(id, val) {
        if (!cart[id]) return;
        cart[id].price = parseInt(val) || 0;
        updateTotals();
        renderHiddenInputs();
    };

    // --- Update Discount ---
    window.inUpdateDiscount = function(id, val) {
        if (!cart[id]) return;
        cart[id].discount = parseInt(val) || 0;
        updateTotals();
        renderHiddenInputs();
    };

    // --- Update Description ---
    window.inUpdateDesc = function(id, val) {
        if (!cart[id]) return;
        cart[id].description = val;
        renderHiddenInputs();
    };

    // --- Update Expired ---
    window.inUpdateExpired = function(id, val) {
        if (!cart[id]) return;
        cart[id].expired_at = val;
        renderHiddenInputs();
    };

    // --- Render Cart ---
    function renderCart() {
        var ids = Object.keys(cart);
        var count = ids.length;

        elCartCount.textContent = count;

        var elClearAll = document.getElementById('in-clear-all');
        if (count > 0) {
            elClearAll.classList.remove('d-none');
        } else {
            elClearAll.classList.add('d-none');
        }

        if (count === 0) {
            elCartEmpty.classList.remove('d-none');
            elCartBody.classList.add('d-none');
            elCartFooter.classList.add('d-none');
            elSubmitBtn.disabled = true;
            renderHiddenInputs();
            return;
        }

        elCartEmpty.classList.add('d-none');
        elCartBody.classList.remove('d-none');
        elCartFooter.classList.remove('d-none');
        elSubmitBtn.disabled = false;

        var html = '';
        ids.forEach(function(id) {
            var item = cart[id];
            var subtotal = item.qty * item.price;
            var hasExtra = item.description || item.expired_at;

            html += '<div class="in-cart-item">';
            // Header row
            html += '<div class="in-cart-item-header">';
            html += '<div class="in-cart-item-name">' + escHtml(item.name) + '</div>';
            html += '<button type="button" class="in-cart-item-remove" onclick="inRemoveItem(' + id + ')" title="Hapus"><i class="fas fa-times"></i></button>';
            html += '</div>';
            // Controls row
            var netTotal = subtotal - item.discount;
            html += '<div class="in-cart-item-controls">';
            html += '<input type="number" class="in-cart-price-input" value="' + item.price + '" onchange="inUpdatePrice(' + id + ', this.value)" onfocus="this.select()" title="Harga beli satuan">';
            html += '<span class="in-cart-unit">' + escHtml(item.unit) + '</span>';
            html += '<button type="button" class="in-cart-qty-btn" onclick="inUpdateQty(' + id + ', -1)">−</button>';
            html += '<span class="in-cart-qty-val">' + item.qty + '</span>';
            html += '<button type="button" class="in-cart-qty-btn" onclick="inUpdateQty(' + id + ', 1)">+</button>';
            html += '<span class="in-cart-subtotal">' + formatRupiah(subtotal) + '</span>';
            html += '<span class="in-cart-separator"></span>';
            html += '<span class="in-cart-discount-label">Diskon</span>';
            html += '<input type="number" class="in-cart-discount-input" value="' + item.discount + '" min="0" onchange="inUpdateDiscount(' + id + ', this.value)" title="Diskon">';
            html += '<span class="in-cart-net-value">' + formatRupiah(netTotal) + '</span>';
            // Expand button for extra fields
            if (item.category.toLowerCase().includes('perdana') || item.description) {
                html += '<button type="button" class="in-cart-expand-btn" onclick="inToggleExtra(' + id + ')" title="Keterangan & Expired"><i class="fas fa-ellipsis-v"></i></button>';
            }
            html += '</div>';
            // Extra fields (description + expired)
            if (item.category.toLowerCase().includes('perdana') || item.description) {
                html += '<div class="in-cart-extra' + (hasExtra ? ' show' : '') + '" id="in-extra-' + id + '">';
                html += '<input type="text" placeholder="Keterangan" value="' + escAttr(item.description) + '" oninput="inUpdateDesc(' + id + ', this.value)">';
                if (item.category.toLowerCase().includes('perdana')) {
                    html += '<input type="date" placeholder="Expired" value="' + escAttr(item.expired_at) + '" oninput="inUpdateExpired(' + id + ', this.value)">';
                }
                html += '</div>';
            }
            html += '</div>';
        });

        elCartBody.innerHTML = html;
        updateTotals();
        renderHiddenInputs();
    }

    // --- Update Totals ---
    function updateTotals() {
        var totalQty = 0;
        var totalValue = 0;
        Object.keys(cart).forEach(function(id) {
            totalQty += cart[id].qty;
            totalValue += (cart[id].qty * cart[id].price) - (cart[id].discount || 0);
        });
        elTotalQty.textContent = totalQty;
        elTotalValue.textContent = formatRupiah(totalValue);
    }

    // --- Render Hidden Inputs ---
    function renderHiddenInputs() {
        var html = '';
        Object.keys(cart).forEach(function(id) {
            var item = cart[id];
            html += '<input type="hidden" name="items[' + id + '][product_id]" value="' + id + '">';
            html += '<input type="hidden" name="items[' + id + '][qty]" value="' + item.qty + '">';
            html += '<input type="hidden" name="items[' + id + '][price]" value="' + item.price + '">';
            if (item.discount > 0) {
                html += '<input type="hidden" name="items[' + id + '][discount]" value="' + item.discount + '">';
            }
            if (item.description) {
                html += '<input type="hidden" name="items[' + id + '][description]" value="' + escAttr(item.description) + '">';
            }
            if (item.expired_at) {
                html += '<input type="hidden" name="items[' + id + '][expired_at]" value="' + escAttr(item.expired_at) + '">';
            }
        });
        elCartInputs.innerHTML = html;
    }

    // --- Form Submit Guard ---
    document.getElementById('form-stock-in').addEventListener('submit', function(e) {
        if (Object.keys(cart).length === 0) {
            e.preventDefault();
            showToast('Keranjang kosong. Pilih barang terlebih dahulu.', 'warning');
            return;
        }
        // Sync shared fields to hidden inputs
        document.getElementById('in-account-hidden').value = elAccount.value;
        document.getElementById('in-date-hidden').value = elDate.value;

        if (!elAccount.value) {
            e.preventDefault();
            showToast('Pilih akun pembayaran.', 'warning');
            return;
        }
    });

    // --- Helpers ---
    function formatRupiah(num) {
        return 'Rp ' + num.toLocaleString('id-ID');
    }
    function escHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }
    function escAttr(str) {
        return (str || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // --- Quick Add Product ---
    var formQuickAdd = document.getElementById('form-quick-add');
    var btnQuickAddSubmit = document.getElementById('btn-quick-add-submit');
    var modalQuickAdd = document.getElementById('modalQuickAdd');

    formQuickAdd.addEventListener('submit', function(e) {
        e.preventDefault();
        btnQuickAddSubmit.disabled = true;
        btnQuickAddSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';

        fetch('{{ route("stock.quick-add-product") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: formQuickAdd.name.value,
                category_id: formQuickAdd.category_id.value,
                unit: formQuickAdd.unit.value,
                purchase_price: parseInt(formQuickAdd.purchase_price.value) || 0,
                selling_price: parseInt(formQuickAdd.selling_price.value) || 0
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.errors) {
                var msg = Object.values(data.errors).flat().join('\n');
                showToast(msg, 'error');
                return;
            }

            // Tambah produk baru ke grid
            var grid = document.getElementById('in-product-grid');
            var card = document.createElement('div');
            card.className = 'in-product-card';
            card.setAttribute('data-id', data.id);
            card.setAttribute('data-name', data.name);
            card.setAttribute('data-price', data.price);
            card.setAttribute('data-unit', data.unit);
            card.setAttribute('data-stock', data.stock);
            card.setAttribute('data-category', data.category);
            card.innerHTML = '<div class="in-product-name">' + escHtml(data.name) + '</div>'
                + '<div class="in-product-price">' + formatRupiah(data.price) + '</div>'
                + '<div class="in-product-stock">Stok: ' + data.stock + ' ' + escHtml(data.unit) + '</div>';
            grid.prepend(card);

            // Tambah ke cart
            cart[data.id] = {
                id: data.id,
                name: data.name,
                price: data.price,
                unit: data.unit,
                stock: data.stock,
                qty: 1,
                description: '',
                expired_at: '',
                category: data.category,
                discount: 0
            };
            hideCard(data.id);
            renderCart();

            // Reset form & tutup modal
            formQuickAdd.reset();
            bootstrap.Modal.getInstance(modalQuickAdd).hide();
            showToast('Barang "' + data.name + '" berhasil ditambahkan ke keranjang.', 'success');
        })
        .catch(function() {
            showToast('Gagal menyimpan barang. Silakan coba lagi.', 'error');
        })
        .finally(function() {
            btnQuickAddSubmit.disabled = false;
            btnQuickAddSubmit.innerHTML = '<i class="fas fa-save me-1"></i>Simpan & Tambah ke Keranjang';
        });
    });

    // Reset form saat modal ditutup
    modalQuickAdd.addEventListener('hidden.bs.modal', function() {
        formQuickAdd.reset();
    });

    // --- History Search ---
    var elHistorySearch = document.getElementById('in-history-search');
    if (elHistorySearch) {
        elHistorySearch.addEventListener('input', function() {
            var q = this.value.toLowerCase();
            var rows = document.querySelectorAll('.table-modern tbody tr');
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }
})();
</script>
@endpush
