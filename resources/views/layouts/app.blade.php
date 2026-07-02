<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | ADI CELL POS</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('logo.png') }}" alt="Logo" style="width:45px;height:45px;object-fit:contain;flex-shrink:0;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.2));">
        <span>ADI CELL POS</span>
    </div>
    <div class="sidebar-nav">
        @if(Auth::user()->hasPermission(config('permissions.DASHBOARD')))
        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="fas fa-chart-pie"></i>
                <span class="nav-label">Dashboard</span>
            </a>
        </div>
        @endif

        {{-- ═══ PENJUALAN (POS + Stok) ═══ --}}
        @php
            $penjualanActive = request()->routeIs('stock.sales') || request()->routeIs('stock.in') || request()->routeIs('stock.opname') || request()->routeIs('sales-report.*') || request()->routeIs('customers.*') || request()->routeIs('returns.*') || request()->routeIs('print-orders.*') || request()->routeIs('repair-services.*');
        @endphp
        @if(Auth::user()->hasPermission(config('permissions.POS')) || Auth::user()->hasPermission(config('permissions.CUSTOMERS')) || Auth::user()->hasPermission(config('permissions.RETURNS')) || Auth::user()->hasPermission(config('permissions.PRINT_ORDERS')) || Auth::user()->hasPermission(config('permissions.REPAIR_SERVICES')))
        <div class="nav-item">
            <a class="nav-link group-header {{ $penjualanActive ? 'active' : '' }}"
               data-bs-toggle="collapse" data-bs-target="#collapsePenjualan"
               onclick="return false;" role="button" aria-expanded="{{ $penjualanActive ? 'true' : 'false' }}">
                <i class="fas fa-shopping-cart"></i>
                <span class="nav-label">Penjualan</span>
                <i class="fas fa-chevron-down caret {{ $penjualanActive ? 'open' : '' }}"></i>
            </a>
            <div class="collapse submenu {{ $penjualanActive ? 'show' : '' }}" id="collapsePenjualan">
                @if(Auth::user()->hasPermission(config('permissions.POS')))
                <a class="nav-link sub-link {{ request()->routeIs('stock.sales') ? 'active' : '' }}" href="{{ route('stock.sales') }}">
                    <i class="fas fa-cart-plus"></i>
                    <span class="nav-label">POS (Jual Barang)</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.PRINT_ORDERS')))
                <a class="nav-link sub-link {{ request()->routeIs('print-orders.*') ? 'active' : '' }}" href="{{ route('print-orders.index') }}">
                    <i class="fas fa-print"></i>
                    <span class="nav-label">Jasa Cetak</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.REPAIR_SERVICES')))
                <a class="nav-link sub-link {{ request()->routeIs('repair-services.*') ? 'active' : '' }}" href="{{ route('repair-services.index') }}">
                    <i class="fas fa-tools"></i>
                    <span class="nav-label">Jasa Servis</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.CUSTOMERS')))
                <a class="nav-link sub-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                    <i class="fas fa-users"></i>
                    <span class="nav-label">Pelanggan</span>
                    @if(isset($activeCustomerCount) && $activeCustomerCount > 0)
                        <span class="badge bg-primary rounded-pill" style="font-size:0.6rem;padding:0.2em 0.5em;">{{ $activeCustomerCount }}</span>
                    @endif
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.RETURNS')))
                <a class="nav-link sub-link {{ request()->routeIs('returns.*') ? 'active' : '' }}" href="{{ route('returns.index') }}">
                    <i class="fas fa-undo-alt"></i>
                    <span class="nav-label">Retur</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.STOCK_IN')))
                <a class="nav-link sub-link {{ request()->routeIs('stock.in') ? 'active' : '' }}" href="{{ route('stock.in') }}">
                    <i class="fas fa-arrow-down"></i>
                    <span class="nav-label">Stok Masuk</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.STOCK_OPNAME')))
                <a class="nav-link sub-link {{ request()->routeIs('stock.opname') ? 'active' : '' }}" href="{{ route('stock.opname') }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-label">Stok Opname</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.SALES_REPORT')))
                <a class="nav-link sub-link {{ request()->routeIs('sales-report.*') ? 'active' : '' }}" href="{{ route('sales-report.index') }}">
                    <i class="fas fa-chart-line"></i>
                    <span class="nav-label">Laporan Penjualan</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ═══ KEUANGAN ═══ --}}
        @php
            $keuanganActive = request()->routeIs('mutations.*') || request()->routeIs('expenses.*') || request()->routeIs('incomes.*') || request()->routeIs('receivables.*') || request()->routeIs('pending.*') || request()->routeIs('summary.*') || request()->routeIs('reports.*') || request()->routeIs('hpp-records.*');
        @endphp
        @if(Auth::user()->hasPermission(config('permissions.MUTATIONS')) || Auth::user()->hasPermission(config('permissions.EXPENSES')) || Auth::user()->hasPermission(config('permissions.INCOMES')) || Auth::user()->hasPermission(config('permissions.RECEIVABLES')) || Auth::user()->hasPermission(config('permissions.REPORTS')) || Auth::user()->hasPermission(config('permissions.STOCK_REPORT')))
        <div class="nav-item">
            <a class="nav-link group-header {{ $keuanganActive ? 'active' : '' }}"
               data-bs-toggle="collapse" data-bs-target="#collapseKeuangan"
               onclick="return false;" role="button" aria-expanded="{{ $keuanganActive ? 'true' : 'false' }}">
                <i class="fas fa-money-bill-wave"></i>
                <span class="nav-label">Keuangan</span>
                <i class="fas fa-chevron-down caret {{ $keuanganActive ? 'open' : '' }}"></i>
            </a>
            <div class="collapse submenu {{ $keuanganActive ? 'show' : '' }}" id="collapseKeuangan">
                @if(Auth::user()->hasPermission(config('permissions.MUTATIONS')))
                <a class="nav-link sub-link {{ request()->routeIs('mutations.*') ? 'active' : '' }}" href="{{ route('mutations.index') }}">
                    <i class="fas fa-arrow-right-arrow-left"></i>
                    <span class="nav-label">Mutasi</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.EXPENSES')))
                <a class="nav-link sub-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                    <i class="fas fa-minus-circle"></i>
                    <span class="nav-label">Pengeluaran</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.INCOMES')))
                <a class="nav-link sub-link {{ request()->routeIs('incomes.*') ? 'active' : '' }}" href="{{ route('incomes.index') }}">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span class="nav-label">Pendapatan</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.RECEIVABLES')))
                <a class="nav-link sub-link {{ request()->routeIs('receivables.*') ? 'active' : '' }}" href="{{ route('receivables.index') }}">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span class="nav-label">Piutang</span>
                    @if(isset($unpaidPiutangCount) && $unpaidPiutangCount > 0)
                        <span class="badge bg-danger rounded-pill" style="font-size:0.6rem;padding:0.2em 0.5em;">{{ $unpaidPiutangCount }}</span>
                    @endif
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.PENDING')))
                <a class="nav-link sub-link {{ request()->routeIs('pending.*') ? 'active' : '' }}" href="{{ route('pending.index') }}">
                    <i class="fas fa-clock"></i>
                    <span class="nav-label">Transaksi Pending</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.SUMMARY')))
                <a class="nav-link sub-link {{ request()->routeIs('summary.*') ? 'active' : '' }}" href="{{ route('summary.index') }}">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-label">Ringkasan</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.REPORTS')))
                <a class="nav-link sub-link {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}" href="{{ route('reports.profit-loss') }}">
                    <i class="fas fa-balance-scale"></i>
                    <span class="nav-label">Laba Rugi</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('reports.balance-sheet') ? 'active' : '' }}" href="{{ route('reports.balance-sheet') }}">
                    <i class="fas fa-book"></i>
                    <span class="nav-label">Neraca</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.STOCK_REPORT')))
                <a class="nav-link sub-link {{ request()->routeIs('hpp-records.*') ? 'active' : '' }}" href="{{ route('hpp-records.index') }}">
                    <i class="fas fa-cubes"></i>
                    <span class="nav-label">Laporan Divisi</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ═══ KAS ═══ --}}
        @php
            $kasActive = request()->routeIs('cash-counter.*') || request()->routeIs('bills.*');
        @endphp
        @if(Auth::user()->hasPermission(config('permissions.CASH_COUNTER')) || Auth::user()->hasPermission(config('permissions.BILLS')))
        <div class="nav-item">
            <a class="nav-link group-header {{ $kasActive ? 'active' : '' }}"
               data-bs-toggle="collapse" data-bs-target="#collapseKas"
               onclick="return false;" role="button" aria-expanded="{{ $kasActive ? 'true' : 'false' }}">
                <i class="fas fa-cash-register"></i>
                <span class="nav-label">Kas</span>
                <i class="fas fa-chevron-down caret {{ $kasActive ? 'open' : '' }}"></i>
            </a>
            <div class="collapse submenu {{ $kasActive ? 'show' : '' }}" id="collapseKas">
                @if(Auth::user()->hasPermission(config('permissions.CASH_COUNTER')))
                <a class="nav-link sub-link {{ request()->routeIs('cash-counter.*') ? 'active' : '' }}" href="{{ route('cash-counter.index') }}">
                    <i class="fas fa-calculator"></i>
                    <span class="nav-label">Cash Counter</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.BILLS')))
                <a class="nav-link sub-link {{ request()->routeIs('bills.*') ? 'active' : '' }}" href="{{ route('bills.index') }}">
                    <i class="fas fa-file-invoice"></i>
                    <span class="nav-label">Tagihan</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ═══ INVENTARIS ═══ --}}
        @php
            $inventarisActive = request()->routeIs('products.*') || request()->routeIs('product-categories.*') || request()->routeIs('stock.report');
        @endphp
        @if(Auth::user()->hasPermission(config('permissions.PRODUCTS')))
        <div class="nav-item">
            <a class="nav-link group-header {{ $inventarisActive ? 'active' : '' }}"
               data-bs-toggle="collapse" data-bs-target="#collapseInventaris"
               onclick="return false;" role="button" aria-expanded="{{ $inventarisActive ? 'true' : 'false' }}">
                <i class="fas fa-boxes-stacked"></i>
                <span class="nav-label">Inventaris</span>
                <i class="fas fa-chevron-down caret {{ $inventarisActive ? 'open' : '' }}"></i>
            </a>
            <div class="collapse submenu {{ $inventarisActive ? 'show' : '' }}" id="collapseInventaris">
                @if(Auth::user()->hasPermission(config('permissions.PRODUCTS')))
                <a class="nav-link sub-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <i class="fas fa-cube"></i>
                    <span class="nav-label">Barang</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.CATEGORIES')))
                <a class="nav-link sub-link {{ request()->routeIs('product-categories.*') ? 'active' : '' }}" href="{{ route('product-categories.index') }}">
                    <i class="fas fa-tag"></i>
                    <span class="nav-label">Kategori</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.STOCK_REPORT')))
                <a class="nav-link sub-link {{ request()->routeIs('stock.report') ? 'active' : '' }}" href="{{ route('stock.report') }}">
                    <i class="fas fa-warehouse"></i>
                    <span class="nav-label">Laporan Stok</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ═══ AKUN ═══ --}}
        @php
            $akunActive = request()->routeIs('accounts.*') || request()->routeIs('opening-balances.*') || request()->routeIs('opname-saldo.*');
        @endphp
        @if(Auth::user()->hasPermission(config('permissions.ACCOUNTS')) || Auth::user()->hasPermission(config('permissions.STOCK_REPORT')))
        <div class="nav-item">
            <a class="nav-link group-header {{ $akunActive ? 'active' : '' }}"
               data-bs-toggle="collapse" data-bs-target="#collapseAkun"
               onclick="return false;" role="button" aria-expanded="{{ $akunActive ? 'true' : 'false' }}">
                <i class="fas fa-wallet"></i>
                <span class="nav-label">Akun</span>
                <i class="fas fa-chevron-down caret {{ $akunActive ? 'open' : '' }}"></i>
            </a>
            <div class="collapse submenu {{ $akunActive ? 'show' : '' }}" id="collapseAkun">
                <a class="nav-link sub-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}" href="{{ route('accounts.index') }}">
                    <i class="fas fa-university"></i>
                    <span class="nav-label">Akun Keuangan</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('opening-balances.*') ? 'active' : '' }}" href="{{ route('opening-balances.index') }}">
                    <i class="fas fa-coins"></i>
                    <span class="nav-label">Modal Awal</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('opname-saldo.*') ? 'active' : '' }}" href="{{ route('opname-saldo.index') }}">
                    <i class="fas fa-clipboard-check"></i>
                    <span class="nav-label">Opname Saldo</span>
                </a>
            </div>
        </div>
        @endif

        {{-- ═══ PENGATURAN ═══ --}}
        @if(Auth::user()->isAdmin())
        @php
            $pengaturanActive = request()->routeIs('backups.*') || request()->routeIs('users.*');
        @endphp
        <div class="nav-item">
            <a class="nav-link group-header {{ $pengaturanActive ? 'active' : '' }}"
               data-bs-toggle="collapse" data-bs-target="#collapsePengaturan"
               onclick="return false;" role="button" aria-expanded="{{ $pengaturanActive ? 'true' : 'false' }}">
                <i class="fas fa-cog"></i>
                <span class="nav-label">Pengaturan</span>
                <i class="fas fa-chevron-down caret {{ $pengaturanActive ? 'open' : '' }}"></i>
            </a>
            <div class="collapse submenu {{ $pengaturanActive ? 'show' : '' }}" id="collapsePengaturan">
                <a class="nav-link sub-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <i class="fas fa-user-shield"></i>
                    <span class="nav-label">Kelola User</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('backups.*') ? 'active' : '' }}" href="{{ route('backups.index') }}">
                    <i class="fas fa-database"></i>
                    <span class="nav-label">Backup DB</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('sop.*') ? 'active' : '' }}" href="{{ route('sop.index') }}">
                    <i class="fas fa-book"></i>
                    <span class="nav-label">SOP Tutup Buku</span>
                </a>
            </div>
        </div>
        @endif
    </div>
    <div class="sidebar-footer">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <a href="{{ route('profile.index') }}" style="color:rgba(255,255,255,0.8);text-decoration:none;font-size:0.75rem;font-weight:600;">{{ Auth::user()->name }}</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt me-1"></i>Keluar</button>
            </form>
        </div>
        <span>ADI CELL POS &copy; {{ date('Y') }}</span>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="main-content">
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm d-lg-none border-0" onclick="toggleSidebar()" style="font-size:1.2rem;">
                <i class="fas fa-bars"></i>
            </button>
            <span class="topbar-brand">@yield('title', 'ADI CELL POS')</span>
        </div>
    </div>

    <div class="page-content">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script>
// Fungsi notifikasi
function showToast(message, type = 'success') {
    Swal.fire({
        title: type === 'success' ? 'Berhasil!' : type === 'error' ? 'Gagal!' : 'Perhatian!',
        text: message,
        icon: type,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    });
}

// Fungsi konfirmasi hapus
function confirmDelete(message = 'Apakah Anda yakin ingin menghapus?') {
    return new Promise((resolve) => {
        Swal.fire({
            title: message,
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            resolve(result.isConfirmed);
        });
    });
}

// Fungsi konfirmasi umum
function confirmAction(title, text = '') {
    return new Promise((resolve) => {
        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        }).then((result) => {
            resolve(result.isConfirmed);
        });
    });
}

// Tampilkan notifikasi dari session
@if(session('success'))
    showToast(@json(session('success')), 'success');
@endif

@if(session('error'))
    showToast(@json(session('error')), 'error');
@endif

@if(session('warning'))
    showToast(@json(session('warning')), 'warning');
@endif

// Client-side table sorting
document.querySelectorAll('th.sortable').forEach(function(th) {
    th.addEventListener('click', function() {
        var table = this.closest('table');
        var tbody = table.querySelector('tbody');
        var col = Array.from(this.parentNode.children).indexOf(this);
        var type = this.dataset.sort || 'string';
        var asc = !this.classList.contains('asc');

        this.parentNode.querySelectorAll('th.sortable').forEach(function(h) { h.classList.remove('asc', 'desc'); });
        this.classList.add(asc ? 'asc' : 'desc');

        var rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort(function(a, b) {
            var cellA = a.children[col] ? a.children[col].textContent.trim() : '';
            var cellB = b.children[col] ? b.children[col].textContent.trim() : '';
            var valA, valB;

            if (type === 'number') {
                valA = parseInt(cellA.replace(/[^0-9-]/g, '')) || 0;
                valB = parseInt(cellB.replace(/[^0-9-]/g, '')) || 0;
            } else if (type === 'date') {
                valA = cellA;
                valB = cellB;
            } else {
                valA = cellA.toLowerCase();
                valB = cellB.toLowerCase();
            }

            if (valA < valB) return asc ? -1 : 1;
            if (valA > valB) return asc ? 1 : -1;
            return 0;
        });

        rows.forEach(function(row) { tbody.appendChild(row); });
    });
});
// ponytail: disable submit button on any form submit, prevents double-click
document.addEventListener('submit', function(e) {
    const btn = e.submitter || e.target.querySelector('button[type="submit"]');
    if (btn) btn.disabled = true;
});
</script>
@stack('scripts')
</body>
</html>
