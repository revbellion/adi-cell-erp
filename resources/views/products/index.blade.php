@extends('layouts.app')
@section('title', 'Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Daftar Barang</h4>
    <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
        <i class="fas fa-plus me-1"></i>Tambah Barang
    </button>
</div>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Nama</th>
                        <th>Kategori</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="{{ !$product->is_active ? 'text-muted' : ($product->is_low_stock ? 'table-warning' : '') }}">
                        <td class="ps-3 fw-semibold">{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? '-' }}</td>
                        <td>{{ rp($product->purchase_price) }}</td>
                        <td>{{ rp($product->selling_price) }}</td>
                        <td>
                            <span class="fw-semibold {{ $product->is_low_stock ? 'text-danger' : '' }}">
                                {{ $product->stock }}
                            </span>
                            @if($product->is_low_stock)
                            <i class="fas fa-exclamation-triangle text-danger ms-1" style="font-size:0.75rem;"></i>
                            @endif
                        </td>
                        <td>{{ $product->unit }}</td>
                        <td class="pe-3">
                            <button type="button" class="btn btn-modern btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalEditBarang"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-category_id="{{ $product->category_id }}"
                                data-purchase_price="{{ $product->purchase_price }}"
                                data-selling_price="{{ $product->selling_price }}"
                                data-stock="{{ $product->stock }}"
                                data-stock_min="{{ $product->stock_min }}"
                                data-unit="{{ $product->unit }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            @if($product->is_active)
                            <form autocomplete="off" action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="return confirm('Nonaktifkan barang {{ $product->name }}?')">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada barang</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalTambahBarang">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('products.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Telkomsel 10K" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Harga Beli</label>
                        <input type="number" step="1" name="purchase_price" class="form-control" placeholder="0" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Harga Jual</label>
                        <input type="number" step="1" name="selling_price" class="form-control" placeholder="0" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label">Stok Awal</label>
                        <input type="number" step="1" name="stock" class="form-control" placeholder="0" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label">Stok Minimal</label>
                        <input type="number" step="1" name="stock_min" class="form-control" placeholder="0" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="unit" class="form-control" value="pcs" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalEditBarang">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formEditBarang">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="name" id="edit-name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" id="edit-category_id" class="form-select" required>
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Harga Beli</label>
                        <input type="number" step="1" name="purchase_price" id="edit-purchase_price" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Harga Jual</label>
                        <input type="number" step="1" name="selling_price" id="edit-selling_price" class="form-control" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label">Stok</label>
                        <input type="number" step="1" name="stock" id="edit-stock" class="form-control" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label">Stok Minimal</label>
                        <input type="number" step="1" name="stock_min" id="edit-stock_min" class="form-control" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="unit" id="edit-unit" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#modalEditBarang').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#edit-name').val(button.data('name'));
    $('#edit-category_id').val(button.data('category_id'));
    $('#edit-purchase_price').val(button.data('purchase_price'));
    $('#edit-selling_price').val(button.data('selling_price'));
    $('#edit-stock').val(button.data('stock'));
    $('#edit-stock_min').val(button.data('stock_min'));
    $('#edit-unit').val(button.data('unit'));
    $('#formEditBarang').attr('action', '{{ url("products") }}/' + id);
});
</script>
@endpush
