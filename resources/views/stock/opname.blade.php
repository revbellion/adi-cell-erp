@extends('layouts.app')
@section('title', 'Stok Opname')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Stok Opname</h4>
    <span class="text-muted small">Setel stok fisik sesuai hitungan</span>
</div>

<div class="card card-modern shadow-sm">
    <div class="card-body">
        {{-- Search + Category Filter --}}
        <div class="d-flex flex-wrap gap-2 mb-3">
            <div class="input-group input-group-sm" style="min-width:160px;max-width:220px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="opname-search" class="form-control" placeholder="Cari barang..." autocomplete="off">
            </div>
            <div class="d-flex flex-wrap gap-1" id="opname-category-tabs">
                <button type="button" class="btn btn-sm opname-cat-tab active" data-category="">Semua</button>
                @foreach($categories as $cat)
                <button type="button" class="btn btn-sm opname-cat-tab" data-category="{{ $cat->name }}">{{ $cat->name }}</button>
                @endforeach
            </div>
        </div>

        <form autocomplete="off" method="POST" action="{{ route('stock.opname.store') }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Barang</th>
                            <th>Kategori</th>
                            <th style="width:120px;">Stok Sistem</th>
                            <th style="width:120px;">Stok Fisik</th>
                            <th style="width:150px;">Harga Beli</th>
                            <th class="pe-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="opname-tbody">
                        @forelse($products as $product)
                        <tr data-category="{{ $product->category->name ?? '' }}" data-name="{{ $product->name }}">
                            <td class="ps-3 fw-semibold">{{ $product->name }}</td>
                            <td><span class="badge bg-light text-dark">{{ $product->category->name ?? '-' }}</span></td>
                            <td class="text-muted">{{ $product->stock }} {{ $product->unit }}</td>
                            <td>
                                <input type="number" name="items[{{ $product->id }}][qty]"
                                    value="{{ old('items.' . $product->id . '.qty', $product->stock) }}"
                                    class="form-control form-control-sm" min="0"
                                    style="width:100px;">
                                <input type="hidden" name="items[{{ $product->id }}][id]" value="{{ $product->id }}">
                            </td>
                            <td>
                                <input type="text" value="{{ rp($product->purchase_price) }}"
                                    class="form-control form-control-sm" readonly style="background:#f1f5f9;width:130px;">
                            </td>
                            <td class="pe-3">
                                <input type="text" name="items[{{ $product->id }}][description]"
                                    class="form-control form-control-sm" placeholder="Opsional">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada barang aktif</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->isNotEmpty())
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted" id="opname-row-count">{{ $products->count() }} barang ditampilkan</span>
                <div class="d-flex gap-2">
                    <a href="{{ route('stock.opname.pdf') }}" target="_blank" class="btn btn-modern btn-outline-secondary">
                        <i class="fas fa-file-pdf me-1"></i>Export PDF
                    </a>
                    <button type="submit" class="btn btn-modern btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan Stok Opname
                    </button>
                </div>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.opname-cat-tab {
    font-size: 0.72rem;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid var(--border-subtle);
    background: var(--bg-card);
    color: var(--text-muted);
    font-weight: 500;
    transition: all 0.15s;
}
.opname-cat-tab:hover {
    border-color: var(--theme-primary);
    color: var(--theme-primary);
}
.opname-cat-tab.active {
    background: var(--theme-primary);
    color: #fff;
    border-color: var(--theme-primary);
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var tbody = document.getElementById('opname-tbody');
    var elSearch = document.getElementById('opname-search');
    var elTabs = document.getElementById('opname-category-tabs');
    var elRowCount = document.getElementById('opname-row-count');
    var activeCategory = '';

    elTabs.addEventListener('click', function(e) {
        var tab = e.target.closest('.opname-cat-tab');
        if (!tab) return;
        elTabs.querySelectorAll('.opname-cat-tab').forEach(function(t) { t.classList.remove('active'); });
        tab.classList.add('active');
        activeCategory = tab.getAttribute('data-category');
        filterRows();
    });

    elSearch.addEventListener('input', function() {
        filterRows();
    });

    function filterRows() {
        var q = elSearch.value.toLowerCase();
        var rows = tbody.querySelectorAll('tr[data-name]');
        var visible = 0;
        rows.forEach(function(row) {
            var name = row.getAttribute('data-name').toLowerCase();
            var cat = row.getAttribute('data-category');
            var matchSearch = name.includes(q);
            var matchCategory = !activeCategory || cat === activeCategory;
            var show = matchSearch && matchCategory;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        elRowCount.textContent = visible + ' barang ditampilkan';
    }
})();
</script>
@endpush
