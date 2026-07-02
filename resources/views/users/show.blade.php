@php
    $title = 'Detail User: ' . $user->username;
@endphp
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Detail User</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-modern btn-sm">
            <i class="fas fa-edit me-1"></i> Edit User
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card card-modern h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center"
                         style="width:80px;height:80px;background:rgba(var(--theme-primary-rgb),0.1);font-size:2rem;color:var(--theme-primary);">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <div class="text-muted mb-2" style="font-size:0.85rem;">{{ $user->username }}</div>
                <div>
                    @if($user->isAdmin())
                        <span class="badge bg-warning text-dark">Admin</span>
                    @else
                        <span class="badge bg-secondary">Kasir</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-modern">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">Informasi Akun</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:180px;">Nama Lengkap</td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Username</td>
                        <td class="fw-semibold">{{ $user->username }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td>{{ $user->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tipe</td>
                        <td>
                            @if($user->isAdmin())
                                <span class="badge bg-warning text-dark">Admin (Akses Penuh)</span>
                            @else
                                <span class="badge bg-secondary">Kasir</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Bergabung</td>
                        <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Terakhir diperbarui</td>
                        <td>{{ $user->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                    @if($user->creator)
                    <tr>
                        <td class="text-muted">Dibuat oleh</td>
                        <td>{{ $user->creator->name }} ({{ $user->creator->username }})</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        @unless($user->isAdmin())
        <div class="card card-modern mt-3">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">Akses Modul</h6>
            </div>
            <div class="card-body">
                @php
                    $userPerms = $user->permissions ?? [];
                    $labels = [];
                    foreach ($permissionKeys as $p) {
                        $labels[$p['key']] = $p['label'];
                    }
                @endphp
                @if(count($userPerms) > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($userPerms as $p)
                            <span class="badge bg-info" style="font-size:0.8rem;">{{ $labels[$p] ?? $p }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted" style="font-size:0.9rem;">Tidak ada akses modul khusus.</div>
                @endif
            </div>
        </div>
        @endunless
    </div>
</div>
@endsection
