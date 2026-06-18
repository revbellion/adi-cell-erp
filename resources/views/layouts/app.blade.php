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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('logo.png') }}" alt="Logo" style="width:45px;height:45px;object-fit:contain;flex-shrink:0;">
        <span>ADI CELL | POS</span>
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

        @php
            $hasTransaksi = Auth::user()->hasPermission(config('permissions.MUTATIONS')) || Auth::user()->hasPermission(config('permissions.EXPENSES')) || Auth::user()->hasPermission(config('permissions.INCOMES')) || Auth::user()->hasPermission(config('permissions.RECEIVABLES')) || Auth::user()->hasPermission(config('permissions.SUMMARY'));
        @endphp
        @if($hasTransaksi)
        @php
            $transaksiActive = request()->routeIs('mutations.*') || request()->routeIs('expenses.*') || request()->routeIs('incomes.*') || request()->routeIs('receivables.*') || request()->routeIs('summary.*');
        @endphp
        <div class="nav-item">
            <a class="nav-link group-header {{ $transaksiActive ? 'active' : '' }}"
               data-bs-toggle="collapse" data-bs-target="#collapseTransaksi"
               onclick="return false;" role="button" aria-expanded="{{ $transaksiActive ? 'true' : 'false' }}">
                <i class="fas fa-exchange-alt"></i>
                <span class="nav-label">Transaksi</span>
                <i class="fas fa-chevron-down caret {{ $transaksiActive ? 'open' : '' }}"></i>
            </a>
            <div class="collapse submenu {{ $transaksiActive ? 'show' : '' }}" id="collapseTransaksi">
                @if(Auth::user()->hasPermission(config('permissions.MUTATIONS')))
                <a class="nav-link sub-link {{ request()->routeIs('mutations.*') ? 'active' : '' }}" href="{{ route('mutations.index') }}">
                    <i class="fas fa-arrow-right-arrow-left"></i>
                    <span class="nav-label">Mutasi</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.EXPENSES')))
                <a class="nav-link sub-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                    <i class="fas fa-shopping-cart"></i>
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
                    <i class="fas fa-book"></i>
                    <span class="nav-label">Piutang</span>
                    @if(isset($unpaidPiutangCount) && $unpaidPiutangCount > 0)
                        <span class="badge bg-danger rounded-pill" style="font-size:0.6rem;padding:0.2em 0.5em;">{{ $unpaidPiutangCount }}</span>
                    @endif
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.SUMMARY')))
                <a class="nav-link sub-link {{ request()->routeIs('summary.*') ? 'active' : '' }}" href="{{ route('summary.index') }}">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-label">Ringkasan</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        @if(Auth::user()->hasPermission(config('permissions.CASH_COUNTER')))
        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('cash-counter.*') ? 'active' : '' }}" href="{{ route('cash-counter.index') }}">
                <i class="fas fa-calculator"></i>
                <span class="nav-label">Cash Counter</span>
            </a>
        </div>
        @endif

        @if(Auth::user()->hasPermission(config('permissions.BILLS')))
        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('bills.*') ? 'active' : '' }}" href="{{ route('bills.index') }}">
                <i class="fas fa-file-invoice"></i>
                <span class="nav-label">Tagihan</span>
            </a>
        </div>
        @endif

        @php
            $stokActive = request()->routeIs('product-categories.*') || request()->routeIs('products.*') || request()->routeIs('stock.*');
        @endphp
        <div class="nav-item">
            <a class="nav-link group-header {{ $stokActive ? 'active' : '' }}"
               data-bs-toggle="collapse" data-bs-target="#collapseStok"
               onclick="return false;" role="button" aria-expanded="{{ $stokActive ? 'true' : 'false' }}">
                <i class="fas fa-box"></i>
                <span class="nav-label">Stok Barang</span>
                <i class="fas fa-chevron-down caret {{ $stokActive ? 'open' : '' }}"></i>
            </a>
            <div class="collapse submenu {{ $stokActive ? 'show' : '' }}" id="collapseStok">
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
                @if(Auth::user()->hasPermission(config('permissions.STOCK_IN')))
                <a class="nav-link sub-link {{ request()->routeIs('stock.in') ? 'active' : '' }}" href="{{ route('stock.in') }}">
                    <i class="fas fa-arrow-down"></i>
                    <span class="nav-label">Stok Masuk</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.POS')))
                <a class="nav-link sub-link {{ request()->routeIs('stock.sales') ? 'active' : '' }}" href="{{ route('stock.sales') }}">
                    <i class="fas fa-arrow-up"></i>
                    <span class="nav-label">Penjualan</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.STOCK_OPNAME')))
                <a class="nav-link sub-link {{ request()->routeIs('stock.opname') ? 'active' : '' }}" href="{{ route('stock.opname') }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-label">Stok Opname</span>
                </a>
                @endif
                @if(Auth::user()->hasPermission(config('permissions.STOCK_REPORT')))
                <a class="nav-link sub-link {{ request()->routeIs('stock.report') ? 'active' : '' }}" href="{{ route('stock.report') }}">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-label">Laporan</span>
                </a>
                @endif
            </div>
        </div>

        @if(Auth::user()->hasPermission(config('permissions.ACCOUNTS')))
        <div class="nav-item">
            <a class="nav-link group-header {{ request()->routeIs('accounts.*') || request()->routeIs('opening-balances.*') ? 'active' : '' }}"
               data-bs-toggle="collapse" data-bs-target="#collapseAkun"
               onclick="return false;" role="button" aria-expanded="{{ request()->routeIs('accounts.*') || request()->routeIs('opening-balances.*') ? 'true' : 'false' }}">
                <i class="fas fa-wallet"></i>
                <span class="nav-label">Akun</span>
                <i class="fas fa-chevron-down caret {{ request()->routeIs('accounts.*') || request()->routeIs('opening-balances.*') ? 'open' : '' }}"></i>
            </a>
            <div class="collapse submenu {{ request()->routeIs('accounts.*') || request()->routeIs('opening-balances.*') ? 'show' : '' }}" id="collapseAkun">
                <a class="nav-link sub-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}" href="{{ route('accounts.index') }}">
                    <i class="fas fa-wallet"></i>
                    <span class="nav-label">Akun Keuangan</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('opening-balances.*') ? 'active' : '' }}" href="{{ route('opening-balances.index') }}">
                    <i class="fas fa-coins"></i>
                    <span class="nav-label">Modal Awal</span>
                </a>
            </div>
        </div>
        @endif
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
                    <i class="fas fa-users"></i>
                    <span class="nav-label">Kelola User</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('backups.*') ? 'active' : '' }}" href="{{ route('backups.index') }}">
                    <i class="fas fa-database"></i>
                    <span class="nav-label">Backup DB</span>
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
        <button class="dark-mode-btn" onclick="toggleDarkMode()" title="Toggle dark mode">
            <i class="fas fa-moon" id="darkModeIcon"></i>
        </button>
        <div class="theme-dots">
            <span class="theme-dot" style="background:linear-gradient(135deg,#1e3a5f,#3b82f6)" data-theme="" title="Biru (Default)"></span>
            <span class="theme-dot" style="background:linear-gradient(135deg,#064e3b,#34d399)" data-theme="emerald" title="Hijau"></span>
            <span class="theme-dot" style="background:linear-gradient(135deg,#2e1065,#a78bfa)" data-theme="purple" title="Ungu"></span>
            <span class="theme-dot" style="background:linear-gradient(135deg,#4c0519,#fb7185)" data-theme="rose" title="Merah"></span>
            <span class="theme-dot" style="background:linear-gradient(135deg,#431407,#fb923c)" data-theme="orange" title="Orange"></span>
            <span class="theme-dot" style="background:linear-gradient(135deg,#0c0c1d,#22d3ee)" data-theme="cyan" title="Cyan"></span>
        </div>
        <span>ADI CELL &copy; {{ date('Y') }}</span>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="main-content">
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm d-lg-none border-0" onclick="toggleSidebar()" style="font-size:1.2rem;">
                <i class="fas fa-bars"></i>
            </button>
            <span class="topbar-brand">@yield('title', 'ADI CELL | Cash Tracker')</span>
        </div>
    </div>

    <div class="page-content">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show alert-modern py-2 px-3 mb-4" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show alert-modern py-2 px-3 mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
