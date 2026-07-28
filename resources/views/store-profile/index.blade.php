@extends('layouts.app')
@section('title', 'Profil Toko')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Profil Toko</h4>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-modern shadow-sm">
            <div class="card-body p-4">
                <form autocomplete="off" method="POST" action="{{ route('store-profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Toko</label>
                        <input type="text" name="store_name" class="form-control @error('store_name') is-invalid @enderror"
                               value="{{ old('store_name', $profile->store_name) }}" required maxlength="100">
                        @error('store_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" required maxlength="255">{{ old('address', $profile->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">No. Telepon</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $profile->phone) }}" required maxlength="30">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $profile->email) }}" maxlength="100">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Teks Kaki Resi</label>
                        <input type="text" name="footer_text" class="form-control @error('footer_text') is-invalid @enderror"
                               value="{{ old('footer_text', $profile->footer_text) }}" maxlength="255" placeholder="Terima kasih!">
                        <div class="form-text">Teks yang muncul di bagian bawah resi/struk.</div>
                        @error('footer_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-modern btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-modern btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Preview Resi --}}
        <div class="card card-modern shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-receipt me-2"></i>Pratinjau Resi</h6>
            </div>
            <div class="card-body p-4">
                <div style="max-width:320px;margin:0 auto;border:1px dashed #ccc;padding:16px;font-family:'Consolas','Courier New',monospace;font-size:12px;background:#fff;">
                    <div style="text-align:center;margin-bottom:8px;">
                        <strong style="font-size:16px;">{{ $profile->store_name }}</strong><br>
                        <span style="font-size:10px;color:#555;">{{ $profile->address }}</span><br>
                        <span style="font-size:10px;color:#555;">Telp: {{ $profile->phone }}</span>
                        @if($profile->email)
                            <br><span style="font-size:10px;color:#555;">{{ $profile->email }}</span>
                        @endif
                    </div>
                    <div style="border-top:1px dashed #999;margin:8px 0;"></div>
                    <div style="text-align:center;font-size:10px;">
                        {{ $profile->footer_text ?? 'Terima kasih!' }}
                    </div>
                </div>
                <p class="text-muted text-center mt-3 mb-0" style="font-size:12px;">
                    <i class="fas fa-info-circle me-1"></i>Resi akan menggunakan data di atas setelah disimpan.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
