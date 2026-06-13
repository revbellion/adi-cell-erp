<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | Cash Tracker</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
    :root {
        --theme-primary: #3b82f6;
        --theme-primary-hover: #2563eb;
        --theme-primary-rgb: 59, 130, 246;
        --theme-primary-light: #60a5fa;
        --bg-body: #f0f2f5;
        --bg-card: #fff;
        --text-primary: #1e293b;
        --text-muted: #64748b;
        --border-subtle: rgba(0,0,0,0.05);
        --border-table: #e2e8f0;
        --table-header-bg: #f8fafc;
        --table-row-hover: #f8fafc;
        --topbar-bg: #fff;
        --topbar-border: #e5e7eb;
        --modal-overlay: rgba(0,0,0,0.15);
        --card-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        --card-shadow-hover: 0 4px 12px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04);
        --stat-card-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        --stat-card-shadow-hover: 0 8px 24px rgba(0,0,0,0.1);
    }

    .dark-mode {
        --bg-body: #0f172a;
        --bg-card: #273548;
        --text-primary: #ffffff;
        --text-muted: #94a3b8;
        --border-subtle: rgba(255,255,255,0.08);
        --border-table: #3b4a5c;
        --table-header-bg: #1e293b;
        --table-row-hover: #1e293b;
        --topbar-bg: #1e293b;
        --topbar-border: #3b4a5c;
        --modal-overlay: rgba(0,0,0,0.4);
        --card-shadow: 0 1px 3px rgba(0,0,0,0.3), 0 1px 2px rgba(0,0,0,0.2);
        --card-shadow-hover: 0 4px 12px rgba(0,0,0,0.4), 0 2px 4px rgba(0,0,0,0.3);
        --stat-card-shadow: 0 1px 3px rgba(0,0,0,0.3), 0 1px 2px rgba(0,0,0,0.2);
        --stat-card-shadow-hover: 0 8px 24px rgba(0,0,0,0.4);
        --bs-body-color: #ffffff;
        --bs-card-color: #ffffff;
        --bs-table-color: #ffffff;
        --bs-table-striped-color: #ffffff;
        --bs-table-hover-color: #ffffff;
        --bs-body-color-rgb: 255, 255, 255;
    }

    * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
    body { background: var(--bg-body); color: var(--text-primary); }

    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 260px;
        height: 100vh;
        background: linear-gradient(180deg, #1e3a5f 0%, #1e40af 40%, #3b82f6 100%);
        z-index: 1030;
        display: flex;
        flex-direction: column;
        transition: width 0.3s, transform 0.3s;
    }
    .sidebar-brand {
        padding: 1.25rem 1rem 0.75rem;
        font-weight: 800;
        font-size: 1rem;
        color: #fff;
        letter-spacing: -0.5px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: padding 0.3s;
    }
    .sidebar-brand i { min-width: 18px; text-align: center; font-size: 1.1rem; flex-shrink: 0; }
    .sidebar-nav { flex: 1; overflow-y: auto; padding: 0.75rem 0.75rem; }
    .sidebar-nav .nav-item { margin-bottom: 2px; }
    .sidebar-nav .nav-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 0.85rem;
        color: rgba(255,255,255,0.7);
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.15s;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }
    .sidebar-nav .nav-link:hover {
        color: #fff;
        background: rgba(255,255,255,0.1);
    }
    .sidebar-nav .nav-link.active {
        color: #fff;
        background: rgba(255,255,255,0.18);
        font-weight: 600;
    }
    .sidebar-nav .nav-link i { width: 18px; text-align: center; font-size: 0.95rem; flex-shrink: 0; }
    .sidebar-nav .nav-link .nav-label { flex: 1; overflow: hidden; text-overflow: ellipsis; transition: opacity 0.2s; }
    .sidebar-nav .nav-link .caret { font-size: 0.65rem; transition: transform 0.2s; flex-shrink: 0; }
    .sidebar-nav .nav-link .caret.open { transform: rotate(180deg); }
    .sidebar-nav .group-header { padding: 0.55rem 0.85rem; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em; color: rgba(255,255,255,0.45); font-weight: 600; margin-top: 4px; }
    .sidebar-nav .group-header:hover { color: rgba(255,255,255,0.7); background: transparent; }
    .sidebar-nav .submenu .nav-link { padding-left: 2.55rem; font-size: 0.82rem; }
    .sidebar-nav .submenu .nav-link.active { background: rgba(255,255,255,0.12); }
    .sidebar-nav .submenu .nav-link i { width: 16px; font-size: 0.8rem; }
    .sidebar-footer {
        padding: 0.5rem;
        border-top: 1px solid rgba(255,255,255,0.08);
        color: rgba(255,255,255,0.4);
        font-size: 0.65rem;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        transition: padding 0.3s;
    }

    .main-content {
        margin-left: 260px;
        min-height: 100vh;
        transition: margin-left 0.3s;
    }

    .topbar {
        background: var(--topbar-bg);
        border-bottom: 1px solid var(--topbar-border);
        padding: 0.7rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 1020;
    }
    .topbar-brand {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text-primary);
    }
    .topbar-period select { border-radius: 8px; font-size: 0.8rem; }

    .page-content { padding: 1.5rem; }

    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        z-index: 1025;
    }

    @media (max-width: 991.98px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        .sidebar-overlay.show { display: block; }
        .main-content { margin-left: 0; }
    }

    .card-modern {
        background: var(--bg-card);
        border: none;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        transition: box-shadow 0.2s;
    }
    .card-modern:hover {
        box-shadow: var(--card-shadow-hover);
    }
    .card-modern .card-header {
        background: var(--bg-card);
        border-bottom: 1px solid var(--border-subtle);
        border-radius: 12px 12px 0 0 !important;
        padding: 0.875rem 1.25rem;
    }
    .card-modern .card-footer {
        background: var(--bg-card);
    }

    .stat-card {
        background: var(--bg-card);
        border: none;
        border-radius: 14px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: var(--stat-card-shadow);
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--stat-card-shadow-hover) !important;
    }

    .table-modern { font-size: 0.875rem; color: var(--text-primary); }
    .table-modern thead th {
        background: var(--table-header-bg);
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid var(--border-table);
        padding: 0.75rem 1rem;
    }
    .table-modern tbody td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-subtle);
        vertical-align: middle;
    }
    .table-modern tbody tr:hover { background: var(--table-row-hover); }
    .table-modern tbody tr:last-child td { border-bottom: none; }

    .badge-status { font-size: 0.75rem; font-weight: 600; padding: 0.3em 0.7em; border-radius: 20px; }

    .btn-modern {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8125rem;
        padding: 0.4rem 0.9rem;
        transition: all 0.15s;
    }
    .btn-modern.btn-primary { background: var(--theme-primary); border-color: var(--theme-primary); }
    .btn-modern.btn-primary:hover { background: var(--theme-primary-hover); border-color: var(--theme-primary-hover); transform: translateY(-1px); }
    .btn-modern.btn-success { background: #10b981; border-color: #10b981; }
    .btn-modern.btn-success:hover { background: #059669; border-color: #059669; transform: translateY(-1px); }
    .btn-modern.btn-danger { background: #ef4444; border-color: #ef4444; }
    .btn-modern.btn-danger:hover { background: #dc2626; border-color: #dc2626; }
    .btn-modern.btn-warning { background: #f59e0b; border-color: #f59e0b; color: #fff; }
    .btn-modern.btn-warning:hover { background: #d97706; border-color: #d97706; color: #fff; }
    .btn-modern.btn-secondary { background: #6b7280; border-color: #6b7280; color: #fff; }
    .btn-modern.btn-secondary:hover { background: #4b5563; border-color: #4b5563; }

    .modal-modern .modal-content {
        background: var(--bg-card);
        border: none;
        border-radius: 16px;
        box-shadow: 0 20px 60px var(--modal-overlay);
    }
    .modal-modern .modal-header {
        border-bottom: 1px solid var(--border-subtle);
        border-radius: 16px 16px 0 0;
        padding: 1.25rem;
    }
    .modal-modern .modal-body { padding: 1.25rem; }
    .modal-modern .modal-footer {
        border-top: 1px solid var(--border-subtle);
        border-radius: 0 0 16px 16px;
        padding: 1rem 1.25rem;
    }
    .modal-modern .form-label { font-weight: 600; font-size: 0.8125rem; color: var(--text-primary); margin-bottom: 0.35rem; }
    .modal-modern .form-control,
    .modal-modern .form-select {
        background: var(--bg-card);
        color: var(--text-primary);
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    .dark-mode .modal-modern .form-control,
    .dark-mode .modal-modern .form-select {
        background: #0f172a;
        border-color: #334155;
        color: #f1f5f9;
    }
    .modal-modern .form-control:focus,
    .modal-modern .form-select:focus {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 3px rgba(var(--theme-primary-rgb), 0.1);
    }

    .alert-modern { border: none; border-radius: 10px; font-size: 0.875rem; }

    .pagination-modern .page-link {
        background: var(--bg-card);
        border: none;
        border-radius: 8px;
        margin: 0 2px;
        color: var(--theme-primary);
        font-weight: 500;
    }
    .pagination-modern .page-item.active .page-link {
        background: var(--theme-primary);
        color: #fff;
    }

    .dark-mode-btn {
        background: none;
        border: none;
        color: rgba(255,255,255,0.4);
        cursor: pointer;
        padding: 0.35rem 0.5rem;
        border-radius: 8px;
        transition: all 0.15s;
        font-size: 0.9rem;
    }
    .dark-mode-btn:hover { color: #fff; background: rgba(255,255,255,0.1); }

    .dark-mode .sidebar {
        background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    }
    .dark-mode .sidebar-overlay { background: var(--modal-overlay); }
    .dark-mode .text-muted { color: var(--text-muted) !important; }
    .dark-mode .text-success { color: #22c55e !important; }
    .dark-mode .text-danger { color: #ef4444 !important; }
    .dark-mode .bg-white { background: var(--bg-card) !important; }
    .dark-mode .shadow-sm { box-shadow: var(--card-shadow) !important; }
    .dark-mode .card-footer.bg-white { background: var(--bg-card) !important; }
    .dark-mode .card-modern,
    .dark-mode .stat-card {
        border: 1px solid #3b4a5c;
    }

    .dark-mode .badge-status[style*="background:#f0fdf4"] {
        background: rgba(22,163,74,0.15) !important;
        color: #4ade80 !important;
    }
    .dark-mode .badge-status[style*="background:#eff6ff"] {
        background: rgba(var(--theme-primary-rgb), 0.15) !important;
        color: var(--theme-primary-light) !important;
    }
    .dark-mode .badge-status[style*="background:#ecfdf5"] {
        background: rgba(16,185,129,0.15) !important;
        color: #34d399 !important;
    }
    .dark-mode .badge-status[style*="background:#fef2f2"] {
        background: rgba(239,68,68,0.15) !important;
        color: #f87171 !important;
    }

    .dark-mode .stat-card[style*="border-left: 4px solid"] { border-left-color: currentColor; }
    .dark-mode .rounded-3[style*="background:#"] { background: rgba(255,255,255,0.08) !important; }
    .dark-mode .rounded-3[style*="background:#"] i { opacity: 0.8; }

    .dark-mode .table-modern tbody td { border-bottom-color: var(--border-table); }
    .dark-mode .modal-backdrop.show { opacity: 0.6; }
    .dark-mode input::placeholder { color: var(--text-muted); }
    .dark-mode select option { background: #1e293b; color: #f1f5f9; }
    .dark-mode .pagination-modern .page-item.active .page-link { background: var(--theme-primary-hover); }
    .dark-mode .modal-modern .form-control,
    .dark-mode .modal-modern .form-select { background: #0f172a; border-color: #334155; color: #f1f5f9; }
    .dark-mode .form-control,
    .dark-mode .form-select {
        background: #0f172a;
        border-color: #334155;
        color: #ffffff;
    }
    .dark-mode .form-control:focus,
    .dark-mode .form-select:focus {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--theme-primary-rgb), 0.15);
    }
    .dark-mode .nav-tabs .nav-link { color: var(--text-muted) !important; }
    .dark-mode .nav-tabs .nav-link.active { color: var(--theme-primary-light) !important; border-bottom-color: var(--theme-primary-light) !important; }
    .dark-mode [style*="background:#f8fafc"] { background: #1e293b !important; }
    .dark-mode [style*="color:#374151"] { color: var(--text-primary) !important; }

    /* Theme: Emerald */
    .theme-emerald .sidebar {
        background: linear-gradient(180deg, #064e3b 0%, #059669 40%, #34d399 100%);
    }
    .dark-mode.theme-emerald .sidebar {
        background: linear-gradient(180deg, #022c22 0%, #065f46 40%, #059669 100%);
    }
    /* Theme: Purple */
    .theme-purple .sidebar {
        background: linear-gradient(180deg, #2e1065 0%, #7c3aed 40%, #a78bfa 100%);
    }
    .dark-mode.theme-purple .sidebar {
        background: linear-gradient(180deg, #1a0533 0%, #5b21b6 40%, #8b5cf6 100%);
    }
    /* Theme: Rose */
    .theme-rose .sidebar {
        background: linear-gradient(180deg, #4c0519 0%, #e11d48 40%, #fb7185 100%);
    }
    .dark-mode.theme-rose .sidebar {
        background: linear-gradient(180deg, #2d0a13 0%, #9f1239 40%, #e11d48 100%);
    }
    /* Theme: Orange */
    .theme-orange .sidebar {
        background: linear-gradient(180deg, #431407 0%, #ea580c 40%, #fb923c 100%);
    }
    .dark-mode.theme-orange .sidebar {
        background: linear-gradient(180deg, #270c03 0%, #c2410c 40%, #ea580c 100%);
    }
    /* Theme: Cyan */
    .theme-cyan .sidebar {
        background: linear-gradient(180deg, #0c0c1d 0%, #0891b2 40%, #22d3ee 100%);
    }
    .dark-mode.theme-cyan .sidebar {
        background: linear-gradient(180deg, #0a0a1a 0%, #0e7490 40%, #06b6d4 100%);
    }
    .theme-emerald {
        --theme-primary: #10b981;
        --theme-primary-hover: #059669;
        --theme-primary-rgb: 16, 185, 129;
        --theme-primary-light: #34d399;
    }
    .theme-purple {
        --theme-primary: #8b5cf6;
        --theme-primary-hover: #7c3aed;
        --theme-primary-rgb: 139, 92, 246;
        --theme-primary-light: #a78bfa;
    }
    .theme-rose {
        --theme-primary: #f43f5e;
        --theme-primary-hover: #e11d48;
        --theme-primary-rgb: 244, 63, 94;
        --theme-primary-light: #fb7185;
    }
    .theme-orange {
        --theme-primary: #f97316;
        --theme-primary-hover: #ea580c;
        --theme-primary-rgb: 249, 115, 22;
        --theme-primary-light: #fb923c;
    }
    .theme-cyan {
        --theme-primary: #06b6d4;
        --theme-primary-hover: #0891b2;
        --theme-primary-rgb: 6, 182, 212;
        --theme-primary-light: #22d3ee;
    }

    .theme-dots {
        display: flex;
        gap: 4px;
        padding: 4px 0;
        justify-content: center;
        flex-wrap: wrap;
    }
    .theme-dot {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.15s;
        flex-shrink: 0;
    }
    .theme-dot:hover { transform: scale(1.3); }
    .theme-dot.active { border-color: #fff; box-shadow: 0 0 0 1px rgba(0,0,0,0.3); }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('logo.png') }}" alt="Logo" style="width:45px;height:45px;object-fit:contain;flex-shrink:0;">
        <span>ADI CELL | Cash Tracker</span>
    </div>
    <div class="sidebar-nav">
        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="fas fa-chart-pie"></i>
                <span class="nav-label">Dashboard</span>
            </a>
        </div>

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
                <a class="nav-link sub-link {{ request()->routeIs('mutations.*') ? 'active' : '' }}" href="{{ route('mutations.index') }}">
                    <i class="fas fa-arrow-right-arrow-left"></i>
                    <span class="nav-label">Mutasi</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="nav-label">Pengeluaran</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('incomes.*') ? 'active' : '' }}" href="{{ route('incomes.index') }}">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span class="nav-label">Pendapatan</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('receivables.*') ? 'active' : '' }}" href="{{ route('receivables.index') }}">
                    <i class="fas fa-book"></i>
                    <span class="nav-label">Piutang</span>
                    @if(isset($unpaidPiutangCount) && $unpaidPiutangCount > 0)
                        <span class="badge bg-danger rounded-pill" style="font-size:0.6rem;padding:0.2em 0.5em;">{{ $unpaidPiutangCount }}</span>
                    @endif
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('summary.*') ? 'active' : '' }}" href="{{ route('summary.index') }}">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-label">Ringkasan</span>
                </a>
            </div>
        </div>

        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('bills.*') ? 'active' : '' }}" href="{{ route('bills.index') }}">
                <i class="fas fa-file-invoice"></i>
                <span class="nav-label">Tagihan</span>
            </a>
        </div>

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
                <a class="nav-link sub-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <i class="fas fa-cube"></i>
                    <span class="nav-label">Barang</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('product-categories.*') ? 'active' : '' }}" href="{{ route('product-categories.index') }}">
                    <i class="fas fa-tag"></i>
                    <span class="nav-label">Kategori</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('stock.in') ? 'active' : '' }}" href="{{ route('stock.in') }}">
                    <i class="fas fa-arrow-down"></i>
                    <span class="nav-label">Stok Masuk</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('stock.sales') ? 'active' : '' }}" href="{{ route('stock.sales') }}">
                    <i class="fas fa-arrow-up"></i>
                    <span class="nav-label">Penjualan</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('stock.opname') ? 'active' : '' }}" href="{{ route('stock.opname') }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-label">Stok Opname</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('stock.report') ? 'active' : '' }}" href="{{ route('stock.report') }}">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-label">Laporan</span>
                </a>
            </div>
        </div>

        @php
            $pengaturanActive = request()->routeIs('opening-balances.*') || request()->routeIs('accounts.*') || request()->routeIs('backups.*');
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
                <a class="nav-link sub-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}" href="{{ route('accounts.index') }}">
                    <i class="fas fa-wallet"></i>
                    <span class="nav-label">Akun</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('backups.*') ? 'active' : '' }}" href="{{ route('backups.index') }}">
                    <i class="fas fa-database"></i>
                    <span class="nav-label">Backup DB</span>
                </a>
                <a class="nav-link sub-link {{ request()->routeIs('opening-balances.*') ? 'active' : '' }}" href="{{ route('opening-balances.index') }}">
                    <i class="fas fa-coins"></i>
                    <span class="nav-label">Modal Awal</span>
                </a>
            </div>
        </div>
    </div>
    <div class="sidebar-footer">
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
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    var isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark);
    document.getElementById('darkModeIcon').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
}

function setTheme(theme) {
    document.body.classList.remove('theme-emerald', 'theme-purple', 'theme-rose', 'theme-orange', 'theme-cyan');
    if (theme) {
        document.body.classList.add('theme-' + theme);
    }
    localStorage.setItem('theme', theme);
    document.querySelectorAll('.theme-dot').forEach(function(dot) {
        dot.classList.toggle('active', dot.getAttribute('data-theme') === theme);
    });
}

(function() {
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        document.getElementById('darkModeIcon').className = 'fas fa-sun';
    }
    var savedTheme = localStorage.getItem('theme') || '';
    setTheme(savedTheme);
    document.querySelectorAll('.theme-dot').forEach(function(dot) {
        dot.addEventListener('click', function() {
            setTheme(this.getAttribute('data-theme'));
        });
    });
})();
</script>
@stack('scripts')
</body>
</html>
