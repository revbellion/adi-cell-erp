@php
    $title = isset($user) ? 'Edit User' : 'Tambah User';
    $isEditing = isset($user);
    $defaultPermissions = ['pos', 'stock_in', 'stock_opname'];
    $checkedPerms = old('permissions', $user->permissions ?? ($isEditing ? [] : $defaultPermissions));
    $isAdmin = old('is_admin', $user->is_admin ?? false);
@endphp
@extends('layouts.app')

@section('content')
<h4 class="fw-bold mb-0">{{ $title }}</h4>

<div class="card card-modern">
    <div class="card-body">
        <form method="POST" action="{{ $isEditing ? route('users.update', $user) : route('users.store') }}" autocomplete="off">
            @csrf
            @if($isEditing) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username ?? '') }}" required autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password {{ $isEditing ? '(kosongkan jika tidak diubah)' : '' }}</label>
                    <input type="password" name="password" class="form-control" {{ $isEditing ? '' : 'required' }} minlength="6" autocomplete="off">
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label d-block">Hak Akses</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="is_admin_toggle" {{ $isAdmin ? 'checked' : '' }} onchange="togglePermissionSection()">
                    <label class="form-check-label fw-semibold" for="is_admin_toggle">Admin (Akses Penuh)</label>
                </div>
            </div>

            <div id="permission-section" style="display:{{ $isAdmin ? 'none' : 'block' }}">
                <div class="mt-3">
                    <label class="form-label d-block">Akses Modul</label>
                    <div class="text-muted mb-2" style="font-size:0.8rem;">Pilih modul yang bisa diakses user ini.</div>
                    <div class="row g-2">
                        @foreach($permissionKeys as $perm)
                            <div class="col-md-4">
                                <label class="d-flex align-items-center gap-2" style="cursor:pointer;font-size:0.85rem;color:var(--text-primary);">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm['key'] }}"
                                        {{ in_array($perm['key'], $checkedPerms) ? 'checked' : '' }}>
                                    {{ $perm['label'] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-modern">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-modern">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePermissionSection() {
    var isAdmin = document.getElementById('is_admin_toggle').checked;
    document.getElementById('permission-section').style.display = isAdmin ? 'none' : 'block';
}
</script>
@endsection
