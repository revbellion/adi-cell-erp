@extends('layouts.app')
@section('title', 'Ringkasan Bulanan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Ringkasan Bulanan</h4>
    <form method="GET" action="{{ route('summary.index') }}" class="d-flex align-items-center gap-2">
        <label class="form-label mb-0 text-muted" style="font-size:0.85rem;">Tampilkan</label>
        <select name="months" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <option value="3" {{ $months == 3 ? 'selected' : '' }}>3 Bulan</option>
            <option value="6" {{ $months == 6 ? 'selected' : '' }}>6 Bulan</option>
            <option value="12" {{ $months == 12 ? 'selected' : '' }}>12 Bulan</option>
        </select>
    </form>
</div>

<div class="card card-modern shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Bulan</th>
                        <th>Omset</th>
                        <th>Expense</th>
                        <th class="pe-3">Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $row)
                    <tr>
                        <td class="ps-3 fw-medium">
                            <a data-bs-toggle="collapse" href="#collapseMonth{{ $loop->index }}" role="button" aria-expanded="false" style="color:var(--theme-primary);text-decoration:none;">
                                <i class="fas fa-chevron-down me-1" style="font-size:0.7rem;"></i>{{ $row['label'] }}
                            </a>
                        </td>
                        <td class="fw-semibold" style="color:#16a34a;">{{ rp($row['income']) }}</td>
                        <td class="fw-semibold" style="color:#dc2626;">{{ rp($row['expense']) }}</td>
                        <td class="pe-3 fw-bold {{ $row['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $row['profit'] >= 0 ? '+' : '-' }}{{ rp(abs($row['profit'])) }}
                        </td>
                    </tr>
                    <tr class="collapse-row">
                        <td colspan="4" class="p-0 border-0">
                            <div class="collapse" id="collapseMonth{{ $loop->index }}">
                                <div class="p-3" style="background:#f8fafc;">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <h6 class="fw-semibold mb-2" style="color:#16a34a;font-size:0.8rem;">PENDAPATAN PER KATEGORI</h6>
                                            @if(count($row['income_categories']) > 0)
                                            <table class="table table-sm table-borderless mb-0" style="font-size:0.8rem;">
                                                @foreach($row['income_categories'] as $cat => $total)
                                                <tr>
                                                    <td style="color:#374151;">{{ $cat }}</td>
                                                    <td class="fw-semibold text-end" style="color:#16a34a;">{{ rp($total) }}</td>
                                                </tr>
                                                @endforeach
                                            </table>
                                            @else
                                            <p class="text-muted mb-0" style="font-size:0.8rem;">Tidak ada data</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="fw-semibold mb-2" style="color:#dc2626;font-size:0.8rem;">EXPENSE PER KATEGORI</h6>
                                            @if(count($row['expense_categories']) > 0)
                                            <table class="table table-sm table-borderless mb-0" style="font-size:0.8rem;">
                                                @foreach($row['expense_categories'] as $cat => $total)
                                                <tr>
                                                    <td style="color:#374151;">{{ $cat }}</td>
                                                    <td class="fw-semibold text-end" style="color:#dc2626;">{{ rp($total) }}</td>
                                                </tr>
                                                @endforeach
                                            </table>
                                            @else
                                            <p class="text-muted mb-0" style="font-size:0.8rem;">Tidak ada data</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
