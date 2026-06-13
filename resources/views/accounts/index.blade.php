@extends('layouts.app')
@section('title', 'Akun')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Kelola Akun</h4>
    <button type="button" class="btn btn-modern btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahAkun">
        <i class="fas fa-plus me-1"></i>Tambah Akun
    </button>
</div>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Nama</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $account->name }}</td>
                        <td>
                            <span class="badge badge-status"
                                  style="background:#eff6ff;color:var(--theme-primary);text-transform:capitalize;">
                                {{ $account->type }}
                            </span>
                        </td>
                        <td>
                            @if($account->is_active)
                                <span class="badge badge-status" style="background:#ecfdf5;color:#10b981;">Aktif</span>
                            @else
                                <span class="badge badge-status" style="background:#fef2f2;color:#ef4444;">Nonaktif</span>
                            @endif
                        </td>
                        <td class="pe-3">
                            <button type="button" class="btn btn-modern btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalEditAkun"
                                data-id="{{ $account->id }}"
                                data-name="{{ $account->name }}"
                                data-type="{{ $account->type }}"
                                data-active="{{ $account->is_active ? '1' : '0' }}">
                                <i class="fas fa-edit"></i>
                            </button>

                            @if($account->is_active)
                            <form action="{{ route('accounts.destroy', $account->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="return confirm('Nonaktifkan akun {{ $account->name }}?')">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada akun</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade modal-modern" tabindex="-1" id="modalTambahAkun">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('accounts.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipe</label>
                    <select name="type" class="form-select" required>
                        <option value="">Pilih Tipe</option>
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="ppob">PPOB</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="tambah-active" checked>
                        <label class="form-check-label" for="tambah-active">Aktif</label>
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

<div class="modal fade modal-modern" tabindex="-1" id="modalEditAkun">
    <div class="modal-dialog">
        <form method="POST" action="" class="modal-content" id="formEditAkun">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" id="edit-name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipe</label>
                    <select name="type" id="edit-type" class="form-select" required>
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="ppob">PPOB</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="edit-active">
                        <label class="form-check-label" for="edit-active">Aktif</label>
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
$('#modalEditAkun').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#formEditAkun').attr('action', '/accounts/' + id);
    $('#edit-name').val(button.data('name'));
    $('#edit-type').val(button.data('type'));
    $('#edit-active').prop('checked', button.data('active') === 1);
});
</script>
@endpush
