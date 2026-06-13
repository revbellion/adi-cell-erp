@extends('layouts.app')
@section('title', 'Stok Opname')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-clipboard-list me-2" style="color:var(--theme-primary);"></i>Stok Opname</h4>
    <span class="text-muted small">Setel stok fisik + estimasi harga beli awal</span>
</div>

<div class="card card-modern shadow-sm">
    <div class="card-body">
        <form autocomplete="off" method="POST" action="{{ route('stock.opname.store') }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Barang</th>
                            <th>Kategori</th>
                            <th style="width:120px;">Stok Fisik</th>
                            <th style="width:150px;">Estimasi Harga Beli</th>
                            <th class="pe-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>
                                <input type="number" name="items[{{ $product->id }}][qty]"
                                    value="{{ old('items.' . $product->id . '.qty', $product->stock) }}"
                                    class="form-control form-control-sm" min="0"
                                    style="width:100px;">
                                <input type="hidden" name="items[{{ $product->id }}][id]" value="{{ $product->id }}">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $product->id }}][price]"
                                    value="{{ old('items.' . $product->id . '.price', $product->purchase_price) }}"
                                    class="form-control form-control-sm" min="0" style="width:130px;">
                            </td>
                            <td class="pe-3">
                                <input type="text" name="items[{{ $product->id }}][desc]"
                                    class="form-control form-control-sm" placeholder="Opsional">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada barang aktif</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->isNotEmpty())
            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-modern btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Stok Opname
                </button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection
