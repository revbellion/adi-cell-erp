@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Atur Modal Awal</h4>
</div>

<div class="card card-modern shadow-sm">
    <div class="card-body">
        <form autocomplete="off" method="GET" action="{{ route('opening-balances.index') }}" class="row g-2 align-items-center mb-4">
            <div class="col-auto">
                <label class="fw-semibold" style="color:#374151;">Periode:</label>
            </div>
            <div class="col-auto">
                <input type="month" name="period" value="{{ $period }}" onchange="this.form.submit()" class="form-control form-control-sm" style="width:auto;border-radius:8px;">
            </div>
        </form>

        <form autocomplete="off" method="POST" action="{{ route('opening-balances.store') }}">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">

            <div class="table-responsive">
                <table class="table table-modern mb-3">
                    <thead>
                        <tr>
                            <th class="ps-3">Akun</th>
                            <th class="pe-3">Saldo Modal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $account->name }}</td>
                            <td class="pe-3">
                                <input type="number" step="1" name="balances[{{ $account->id }}]" value="{{ $openingBalances[$account->id]->amount ?? 0 }}"
                                       class="form-control form-control-sm" style="max-width:200px;border-radius:8px;">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-modern btn-primary">
                <i class="fas fa-save me-1"></i>Simpan
            </button>
        </form>
    </div>
</div>
@endsection
