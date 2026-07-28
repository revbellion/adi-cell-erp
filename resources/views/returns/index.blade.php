@extends('layouts.app')
@section('title', 'Retur Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-1">
    <div>
        <h4 class="fw-bold mb-1">Retur Barang</h4>
        <p class="text-muted mb-0" style="font-size:0.8rem;">Catat retur penjualan (dari customer) dan retur pembelian (ke supplier)</p>
    </div>
    <div class="d-flex gap-2 page-header-actions">
        <button type="button" class="btn btn-modern btn-warning" data-bs-toggle="modal" data-bs-target="#modalReturJual">
            <i class="fas fa-undo-alt me-1"></i>Retur Penjualan
        </button>
        <button type="button" class="btn btn-modern btn-info" data-bs-toggle="modal" data-bs-target="#modalReturBeli">
            <i class="fas fa-truck me-1"></i>Retur Pembelian
        </button>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('returns.index') }}" class="row g-2 mb-4 filter-form">
    <div class="col-auto">
        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
    </div>
    <div class="col-auto">
        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
    </div>
    <div class="col-auto">
        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Tipe</option>
            <option value="sales" {{ ($filters['type'] ?? '') === 'sales' ? 'selected' : '' }}>Retur Jual</option>
            <option value="purchase" {{ ($filters['type'] ?? '') === 'purchase' ? 'selected' : '' }}>Retur Beli</option>
        </select>
    </div>
    <div class="col-auto">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Cari nota/produk..." style="width:150px;" oninput="clearTimeout(this._timer); this._timer=setTimeout(()=>this.form.submit(),500)">
    </div>
    <div class="col-auto">
        <a href="{{ route('returns.index') }}" class="btn btn-modern btn-secondary btn-sm"><i class="fas fa-times me-1"></i>Reset</a>
    </div>
</form>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3 sortable" data-sort="date">Tanggal</th>
                        <th>Tipe</th>
                        <th>No Nota</th>
                        <th>Produk</th>
                        <th class="sortable" data-sort="number">Qty</th>
                        <th class="sortable" data-sort="number">Total</th>
                        <th>Alasan</th>
                        <th class="pe-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr>
                        <td class="ps-3">{{ tgl($return->return_date) }}</td>
                        <td>{!! $return->type_badge !!}</td>
                        <td style="font-size:0.8rem;">{{ $return->receipt_id ?? '-' }}</td>
                        <td class="fw-semibold">{{ $return->product?->name ?? 'Produk dihapus' }}</td>
                        <td class="fw-semibold">{{ $return->qty }} {{ $return->product?->unit ?? 'pcs' }}</td>
                        <td class="fw-semibold">{{ rp($return->total) }}</td>
                        <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $return->reason ?? '-' }}</td>
                        <td class="pe-3">
                            <span class="badge bg-success">Tercatat</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada data retur</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ $returns->total() }} data retur</span>
        </div>
        <div class="d-flex gap-4">
            <div>
                <span style="font-size:0.75rem;color:var(--text-muted);">Total Qty</span>
                <span class="fw-bold ms-2" style="font-size:0.95rem;">{{ number_format($totalQty, 0, ',', '.') }}</span>
            </div>
            <div>
                <span style="font-size:0.75rem;color:var(--text-muted);">Total Nilai</span>
                <span class="fw-bold ms-2" style="font-size:0.95rem;color:var(--theme-primary);">{{ rp($totalValue) }}</span>
            </div>
        </div>
    </div>
    @if ($returns->hasPages())
    <div class="card-footer bg-white">
        <div class="pagination-modern">{{ $returns->links() }}</div>
    </div>
    @endif
</div>

{{-- ═══ MODAL RETUR PENJUALAN ═══ --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalReturJual">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('returns.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-undo-alt me-2"></i>Retur Penjualan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Cari No Nota</label>
                    <div class="input-group">
                        <input type="text" name="receipt_id" id="retur-receipt" class="form-control" placeholder="Masukkan no nota..." required>
                        <button type="button" class="btn btn-modern btn-primary" id="btn-cari-nota" onclick="cariNota()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <small class="text-muted">Contoh: INV-20260627-XXXXXX</small>
                </div>

                <div id="retur-nota-info" class="d-none">
                    <div class="p-3 rounded mb-3" style="background:var(--border-subtle);">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold" id="retur-nota-display"></span>
                            <span class="text-muted" id="retur-nota-date" style="font-size:0.85rem;"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Produk</label>
                        <select name="product_id" id="retur-product" class="form-select" required>
                            <option value="">Pilih produk</option>
                        </select>
                    </div>
                    <div class="mb-3" id="retur-product-info" class="d-none" style="font-size:0.85rem;color:var(--text-muted);"></div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah Retur</label>
                        <input type="number" name="qty" id="retur-qty" class="form-control" min="1" required>
                        <small class="text-muted" id="retur-qty-info">Maksimal: </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Retur</label>
                        <input type="date" name="return_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan</label>
                        <textarea name="reason" class="form-control" rows="2"></textarea>
                    </div>
                    <hr>
                    <h6 class="fw-semibold mb-2">Refund (jika ada)</h6>
                    <div class="mb-3">
                        <label class="form-label">Nilai Refund</label>
                        <input type="number" name="refund_amount" class="form-control" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Akun Refund</label>
                        <select name="account_id" class="form-select">
                            <option value="">Pilih Akun (opsional)</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="retur-loading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Mencari nota...</p>
                </div>
                <div id="retur-nota-error" class="alert alert-danger py-2 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Batal</button>
                <button type="submit" class="btn btn-modern btn-warning" id="btn-submit-retur-jual" disabled>
                    <i class="fas fa-undo-alt me-1"></i>Catat Retur
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ MODAL RETUR PEMBELIAN ═══ --}}
<div class="modal fade modal-modern" tabindex="-1" id="modalReturBeli">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('returns.store-purchase') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-truck me-2"></i>Retur Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Produk <span class="text-danger">*</span></label>
                    <select name="product_id" class="form-select" required>
                        <option value="">Pilih Produk</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" data-stock="{{ $product->stock }}">{{ $product->name }} (Stok: {{ $product->stock }} {{ $product->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jumlah Retur <span class="text-danger">*</span></label>
                    <input type="number" name="qty" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal Retur</label>
                    <input type="date" name="return_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alasan</label>
                    <textarea name="reason" class="form-control" rows="2"></textarea>
                </div>
                <hr>
                <h6 class="fw-semibold mb-2">Refund dari Supplier (jika ada)</h6>
                <div class="mb-3">
                    <label class="form-label">Nilai Refund</label>
                    <input type="number" name="refund_amount" class="form-control" min="0" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Akun Refund</label>
                    <select name="account_id" class="form-select">
                        <option value="">Pilih Akun (opsional)</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Batal</button>
                <button type="submit" class="btn btn-modern btn-info">
                    <i class="fas fa-check me-1"></i>Catat Retur
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let returProducts = [];

function cariNota() {
    var receiptId = document.getElementById('retur-receipt').value.trim();
    if (!receiptId) {
        Swal.fire('Peringatan', 'Masukkan no nota terlebih dahulu.', 'warning');
        return;
    }

    var infoDiv = document.getElementById('retur-nota-info');
    var loadingDiv = document.getElementById('retur-loading');
    var errorDiv = document.getElementById('retur-nota-error');
    var submitBtn = document.getElementById('btn-submit-retur-jual');

    infoDiv.classList.add('d-none');
    errorDiv.classList.add('d-none');
    loadingDiv.classList.remove('d-none');
    submitBtn.disabled = true;

    fetch('{{ route("returns.get-receipt") }}?receipt_id=' + encodeURIComponent(receiptId))
        .then(function(res) { return res.json(); })
        .then(function(data) {
            loadingDiv.classList.add('d-none');

            if (data.error) {
                errorDiv.textContent = data.error;
                errorDiv.classList.remove('d-none');
                return;
            }

            returProducts = data.products || [];

            document.getElementById('retur-nota-display').textContent = data.receipt_id;
            document.getElementById('retur-nota-date').textContent = data.date ? new Date(data.date).toLocaleDateString('id-ID') : '';

            var select = document.getElementById('retur-product');
            select.innerHTML = '<option value="">Pilih produk</option>';
            returProducts.forEach(function(p) {
                var opt = document.createElement('option');
                opt.value = p.product_id;
                opt.textContent = p.name + ' (bisa retur: ' + p.available + ' ' + p.unit + ')';
                opt.dataset.available = p.available;
                opt.dataset.unit = p.unit;
                select.appendChild(opt);
            });

            infoDiv.classList.remove('d-none');
        })
        .catch(function(err) {
            loadingDiv.classList.add('d-none');
            errorDiv.textContent = 'Gagal memuat data nota.';
            errorDiv.classList.remove('d-none');
        });
}

document.getElementById('retur-product')?.addEventListener('change', function() {
    var selected = this.options[this.selectedIndex];
    var qtyInput = document.getElementById('retur-qty');
    var qtyInfo = document.getElementById('retur-qty-info');
    var submitBtn = document.getElementById('btn-submit-retur-jual');

    if (selected.value) {
        var available = parseInt(selected.dataset.available) || 0;
        qtyInput.max = available;
        qtyInfo.textContent = 'Maksimal: ' + available + ' ' + (selected.dataset.unit || 'pcs');
        submitBtn.disabled = false;
    } else {
        qtyInput.max = 999999;
        qtyInfo.textContent = '';
        submitBtn.disabled = true;
    }
});

// Auto-fill qty max when modal opens
document.getElementById('modalReturJual')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('retur-nota-info').classList.add('d-none');
    document.getElementById('retur-nota-error').classList.add('d-none');
    document.getElementById('retur-loading').classList.add('d-none');
    document.getElementById('btn-submit-retur-jual').disabled = true;
});
</script>
@endpush
