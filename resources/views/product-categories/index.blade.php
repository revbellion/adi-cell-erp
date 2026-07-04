@extends('layouts.app')
@section('title', 'Kategori Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Kategori Barang</h4>
    <div class="page-header-actions">
        <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
            <i class="fas fa-plus me-1"></i>Tambah Kategori
        </button>
    </div>
</div>

<div class="bulk-action-bar mb-3 d-none" id="bulkActionBar">
    <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.08);border:1px solid rgba(var(--theme-primary-rgb),0.2);">
        <span class="fw-semibold" style="font-size:0.85rem;"><span id="bulkCount">0</span> dipilih</span>
        <span class="fw-bold" style="font-size:0.85rem;color:var(--theme-primary);" id="bulkTotal"></span>
        <form autocomplete="off" id="bulkDeleteForm" method="POST" action="{{ route('product-categories.bulk-delete') }}" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="event.preventDefault(); confirmDelete('Hapus data yang dipilih?').then(ok => ok && this.closest('form').submit());">
                <i class="fas fa-trash me-1"></i>Hapus
            </button>
        </form>
        <button type="button" class="btn btn-modern btn-secondary btn-sm" onclick="clearBulkSelection()">
            <i class="fas fa-times me-1"></i>Batal
        </button>
    </div>
</div>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:40px;"><input type="checkbox" class="form-check-input bulk-select-all"></th>
                        <th class="ps-3">Nama</th>
                        <th>Jumlah Barang</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td class="ps-3"><input type="checkbox" class="form-check-input bulk-select-item" value="{{ $category->id }}"></td>
                        <td class="ps-3 fw-semibold">{{ $category->name }}</td>
                        <td>{{ $category->products_count }} barang</td>
                        <td class="pe-3">
                            <button type="button" class="btn btn-modern btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalEditKategori"
                                data-id="{{ $category->id }}"
                                data-name="{{ $category->name }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form autocomplete="off" action="{{ route('product-categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="event.preventDefault(); confirmDelete('Hapus kategori {{ $category->name }}?').then(ok => ok && this.form.submit());">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada kategori</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div>
            <span style="font-size:0.8rem;color:var(--text-muted);">Total {{ $totalCategories }} kategori</span>
        </div>
        <div></div>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalTambahKategori">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="{{ route('product-categories.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Kategori</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Pulsa" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-modern btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalEditKategori">
    <div class="modal-dialog">
        <form autocomplete="off" method="POST" action="" class="modal-content" id="formEditKategori">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Kategori</label>
                    <input type="text" name="name" id="edit-kategori-name" class="form-control" required>
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
// Bulk selection
document.querySelector('.bulk-select-all')?.addEventListener('change', function() {
    var checked = this.checked;
    document.querySelectorAll('.bulk-select-item').forEach(function(cb) {
        cb.checked = checked;
    });
    updateBulkBar();
});

document.querySelectorAll('.bulk-select-item').forEach(function(cb) {
    cb.addEventListener('change', updateBulkBar);
});

function updateBulkBar() {
    var checked = document.querySelectorAll('.bulk-select-item:checked');
    var count = checked.length;
    var bar = document.getElementById('bulkActionBar');
    if (!bar) return;
    
    if (count > 0) {
        bar.classList.remove('d-none');
        document.getElementById('bulkCount').textContent = count;
        
        var ids = [];
        checked.forEach(function(cb) { ids.push(cb.value); });
        var form = document.getElementById('bulkDeleteForm');
        form.querySelectorAll('input[name="ids[]"]').forEach(function(el) { el.remove(); });
        ids.forEach(function(id) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
    } else {
        bar.classList.add('d-none');
    }
}

function clearBulkSelection() {
    document.querySelectorAll('.bulk-select-item, .bulk-select-all').forEach(function(cb) {
        cb.checked = false;
    });
    updateBulkBar();
}

$('#modalEditKategori').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#edit-kategori-name').val(button.data('name'));
    $('#formEditKategori').attr('action', '{{ url("product-categories") }}/' + id);
});
</script>
@endpush
