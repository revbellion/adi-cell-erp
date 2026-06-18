@php
    $title = 'Profil Akun';
@endphp
@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card card-modern">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div style="width:72px;height:72px;border-radius:50%;background:var(--theme-primary);display:flex;align-items:center;justify-content:center;margin:0 auto;">
                        <i class="fas fa-user" style="font-size:1.8rem;color:#fff;"></i>
                    </div>
                    <h5 class="fw-bold mt-3 mb-1" style="color:var(--text-primary);">{{ $user->name }}</h5>
                    <span class="badge rounded-pill" style="background:{{ $user->isAdmin() ? '#f59e0b' : '#3b82f6' }};font-size:0.75rem;">
                        {{ $user->isAdmin() ? 'Admin' : 'Kasir' }}
                    </span>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border-subtle);">
                        <span style="font-size:0.8rem;color:var(--text-muted);">Username</span>
                        <span style="font-size:0.85rem;font-weight:600;color:var(--text-primary);">{{ $user->username }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border-subtle);">
                        <span style="font-size:0.8rem;color:var(--text-muted);">Nama</span>
                        <span style="font-size:0.85rem;font-weight:600;color:var(--text-primary);">{{ $user->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span style="font-size:0.8rem;color:var(--text-muted);">Bergabung</span>
                        <span style="font-size:0.85rem;font-weight:600;color:var(--text-primary);">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                </div>

                @if(!$user->isAdmin())
                    <div>
                        <div style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.03em;color:var(--text-muted);margin-bottom:0.5rem;">Akses Modul</div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($user->permissions ?? [] as $perm)
                                <span class="badge rounded-pill" style="background:rgba(59,130,246,0.1);color:var(--theme-primary);font-size:0.7rem;font-weight:500;padding:0.3em 0.7em;">
                                    {{ $permissionLabels[$perm] ?? $perm }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-modern">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color:var(--text-primary);font-size:0.95rem;">
                    <i class="fas fa-key me-2" style="color:var(--theme-primary);"></i> Ubah Password
                </h5>

                <form method="POST" action="{{ route('profile.password') }}" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control" required autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control" minlength="6" required autocomplete="off">
                        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem;">Minimal 6 karakter</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control" minlength="6" required autocomplete="off">
                    </div>

                    <button type="submit" class="btn btn-modern btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
