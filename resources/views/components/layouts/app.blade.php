<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'STO' }} — Scan To Office</title>
    <meta name="description" content="Sistem Stock Opname Material berbasis QR Code / Barcode — PT Astra Daido Steel Indonesia">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            /* Infor ERP palette */
            --topbar-bg: #1d252c;
            --topbar-height: 40px;
            --sidebar-bg: #2f3640;
            --sidebar-hover: #3d4654;
            --sidebar-active: rgba(0,114,206,0.15);
            --sidebar-width: 220px;
            --sidebar-border: #3d4654;
            --workspace-bg: #f0f0f0;
            --surface: #ffffff;
            --border: #bfc4ce;
            --border-light: #e0e3e8;
            --row-hover: #e8f0fe;
            --row-selected: #d3e3fd;
            --primary: #0072ce;
            --primary-dark: #005fa8;
            --text: #252a31;
            --text-secondary: #525e6c;
            --text-muted: #808b99;
            --success: #22a06b;
            --warning: #e5a100;
            --danger: #d92d20;
        }

        html { font-size: 13px; }

        body {
            font-family: 'Inter', "Segoe UI", -apple-system, sans-serif;
            background: var(--workspace-bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }

        /* ─── TOPBAR ─── */
        .topbar {
            height: var(--topbar-height);
            background: var(--topbar-bg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 200;
            border-bottom: 1px solid #151a20;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .topbar-logo {
            height: 22px;
            display: block;
        }

        .topbar-divider {
            width: 1px;
            height: 20px;
            background: #4a5568;
        }

        .topbar-app {
            font-size: 13px;
            font-weight: 600;
            color: #e2e8f0;
            white-space: nowrap;
        }

        .topbar-app small {
            font-weight: 400;
            color: #8896a6;
            margin-left: 4px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .topbar-btn {
            background: none;
            border: none;
            color: #8896a6;
            padding: 6px 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            border-radius: 2px;
        }
        .topbar-btn:hover { background: rgba(255,255,255,0.08); color: #e2e8f0; }
        .topbar-btn svg { width: 16px; height: 16px; }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            color: #cdd5e0;
            font-size: 12px;
            border-left: 1px solid #4a5568;
            margin-left: 4px;
        }

        .topbar-avatar {
            width: 22px; height: 22px;
            background: #4a5568;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            color: #e2e8f0;
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            color: #cdd5e0;
            padding: 4px;
            cursor: pointer;
        }

        /* ─── SIDEBAR ─── */
        .sidebar {
            position: fixed;
            top: var(--topbar-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--topbar-height));
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            z-index: 100;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.2s ease;
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: #4a5568; border-radius: 2px; }

        .nav-group { padding: 6px 0; border-bottom: 1px solid var(--sidebar-border); }
        .nav-group:last-child { border-bottom: none; }

        .nav-group-title {
            padding: 8px 14px 4px;
            font-size: 10px;
            font-weight: 700;
            color: #6b7a8d;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 14px;
            font-size: 13px;
            color: #bcc5d0;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.15s;
        }
        .nav-item:hover {
            background: var(--sidebar-hover);
            color: #ffffff;
            text-decoration: none;
        }
        .nav-item.active {
            background: var(--sidebar-active);
            color: #ffffff;
            border-left-color: var(--primary);
        }
        .nav-item svg { width: 16px; height: 16px; color: #7b8a9a; flex-shrink: 0; }
        .nav-item.active svg, .nav-item:hover svg { color: var(--primary); }

        /* ─── MAIN CONTENT ─── */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            min-height: calc(100vh - var(--topbar-height));
            display: flex;
            flex-direction: column;
            transition: margin-left 0.2s ease;
        }

        /* ─── PAGE TAB BAR ─── */
        .page-tab-bar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: stretch;
            padding: 0;
            height: 32px;
        }

        .page-tab {
            display: flex;
            align-items: center;
            padding: 0 14px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            border-bottom: 2px solid transparent;
            border-right: 1px solid var(--border-light);
            cursor: default;
            white-space: nowrap;
        }
        .page-tab.active {
            color: var(--text);
            border-bottom-color: var(--primary);
            background: #fafbfc;
            font-weight: 600;
        }

        /* ─── PAGE CONTENT ─── */
        .page-content { padding: 12px; flex: 1; }

        /* ─── TOOLBAR ─── */
        .enterprise-toolbar {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 4px 8px;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .toolbar-sep {
            width: 1px;
            height: 20px;
            background: var(--border);
            margin: 0 4px;
        }

        /* ─── BUTTONS ─── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 2px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
            font-family: inherit;
            line-height: 1.4;
        }
        .btn:hover { background: #edf0f4; text-decoration: none; }
        .btn svg { width: 14px; height: 14px; }

        .btn-primary { background: var(--primary); border-color: var(--primary-dark); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); color: #fff; }

        .btn-success { background: var(--success); border-color: #1a8a59; color: #fff; }
        .btn-success:hover { background: #1a8a59; color: #fff; }

        .btn-danger { background: var(--danger); border-color: #b91c1c; color: #fff; }
        .btn-danger:hover { background: #b91c1c; color: #fff; }

        .btn-icon {
            padding: 4px;
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: 2px;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-icon svg {
            width: 16px;
            height: 16px;
        }
        .btn-icon:hover { background: #edf0f4; border-color: var(--border); color: var(--text); }

        /* ─── TABLE (Enterprise) ─── */
        .table-container {
            background: var(--surface);
            border: 1px solid var(--border);
            overflow-x: auto;
        }

        .table-enterprise {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            background: #fff;
        }
        .table-enterprise th {
            background: #f4f5f7;
            border-bottom: 2px solid var(--border);
            font-weight: 600;
            text-align: left;
            padding: 6px 10px;
            white-space: nowrap;
            color: var(--text);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .table-enterprise td {
            border-bottom: 1px solid var(--border-light);
            padding: 5px 10px;
            white-space: nowrap;
            color: var(--text);
        }
        .table-enterprise tr:hover td { background: var(--row-hover); }
        .table-enterprise tr.selected td { background: var(--row-selected); }

        /* DataTables override */
        .dataTables_wrapper { font-size: 12px; }
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            padding: 6px 10px;
            color: var(--text) !important;
        }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--border);
            padding: 3px 6px;
            border-radius: 2px;
            font-size: 12px;
            outline: none;
            background: #fff;
        }
        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus { border-color: var(--primary); }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 2px 8px !important;
            margin: 0 1px;
            border: 1px solid var(--border) !important;
            border-radius: 2px;
            background: #fff !important;
            color: var(--text) !important;
            font-size: 11px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--row-selected) !important;
            border-color: var(--primary) !important;
            color: var(--primary-dark) !important;
            font-weight: 600;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
            background: #edf0f4 !important;
        }
        table.dataTable thead th { border-bottom: 2px solid var(--border) !important; }
        table.dataTable.no-footer { border-bottom: 1px solid var(--border) !important; }

        /* ─── CARDS ─── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 12px;
        }
        .card-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ─── FORMS ─── */
        .form-group { margin-bottom: 10px; }
        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 3px;
        }
        .form-control {
            width: 100%;
            padding: 5px 8px;
            border: 1px solid var(--border);
            border-radius: 2px;
            font-size: 12px;
            font-family: inherit;
            outline: none;
            background: #fff;
            color: var(--text);
            transition: border-color 0.15s;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 1px rgba(0,114,206,0.15); }
        select.form-control { padding-right: 24px; }

        /* ─── BADGES ─── */
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 2px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.6;
        }
        .badge-valid { background: #c8e6c9; color: #1b5e20; }
        .badge-invalid { background: #ffcdd2; color: #b71c1c; }
        .badge-duplicate { background: #fff9c4; color: #795548; }
        .badge-info { background: #bbdefb; color: #0d47a1; }

        /* ─── MODAL ─── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1000;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 80px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s;
        }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-content {
            background: #fff;
            border: 1px solid var(--border);
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .modal-header {
            padding: 8px 12px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f4f5f7;
        }
        .modal-body { padding: 12px; }
        .modal-footer {
            padding: 8px 12px;
            border-top: 1px solid var(--border);
            background: #f4f5f7;
            display: flex;
            justify-content: flex-end;
            gap: 6px;
        }

        /* ─── TOAST ─── */
        .toast-container {
            position: fixed;
            bottom: 16px;
            right: 16px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .toast {
            background: var(--surface);
            border: 1px solid var(--border);
            border-left: 3px solid var(--primary);
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 220px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            animation: toastIn 0.2s ease;
        }
        .toast-success { border-left-color: var(--success); }
        .toast-error { border-left-color: var(--danger); }
        @keyframes toastIn {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* ─── UTILITY ─── */
        .mono { font-family: "Consolas", "Courier New", monospace; }
        @keyframes fadeInUp {
            from { transform: translateY(8px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* ─── INLINE EDIT GRID ─── */
        .inline-edit-wrap {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 14px 18px;
            margin: 4px 0;
        }
        .inline-edit-grid {
            display: grid;
            gap: 10px 14px;
        }
        .inline-edit-grid .ie-field label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .inline-edit-grid .ie-field input,
        .inline-edit-grid .ie-field select {
            width: 100%;
            box-sizing: border-box;
            padding: 5px 8px;
            font-size: 13px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background: #fff;
            transition: border-color 0.15s;
        }
        .inline-edit-grid .ie-field input:focus,
        .inline-edit-grid .ie-field select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(59,130,246,0.15);
        }
        .inline-edit-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .hamburger { display: flex; }
            .page-tab-bar { display: none; }
            .topbar-user span { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>
    {{-- ═══ TOPBAR ═══ --}}
    <header class="topbar">
        <div class="topbar-left">
            <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <img src="{{ asset('assets/images/logo-adasi.png') }}" alt="ADASI" class="topbar-logo">
            <div class="topbar-divider"></div>
            <div class="topbar-app">STO<small>Scan To Office</small></div>
        </div>
        <div class="topbar-right">
            <div class="topbar-user">
                <div class="topbar-avatar">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
                <span>{{ auth()->user()->name ?? 'Guest' }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;display:flex;">
                @csrf
                <button type="submit" class="topbar-btn" title="Logout">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </header>

    {{-- ═══ SIDEBAR ═══ --}}
    <aside class="sidebar" id="sidebar">
        @if(auth()->user()->isAdmin())
        <div class="nav-group">
            <div class="nav-group-title">Dashboard</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Overview
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-group-title">STO Result</div>
            <a href="{{ route('admin.scan-results') }}" class="nav-item {{ request()->routeIs('admin.scan-results*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                All Scan Results
            </a>
            <a href="{{ route('admin.barcode-overview') }}" class="nav-item {{ request()->routeIs('admin.barcode-overview*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Material Summary
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-group-title">Master Data</div>
            <a href="{{ route('admin.master.plants') }}" class="nav-item {{ request()->routeIs('admin.master.plants') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Master Plant
            </a>
            <a href="{{ route('admin.master.materials') }}" class="nav-item {{ request()->routeIs('admin.master.materials') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Master Material
            </a>
            <a href="{{ route('admin.master.keterangan') }}" class="nav-item {{ request()->routeIs('admin.master.keterangan') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                Master Keterangan
            </a>
        </div>
        @else
        <div class="nav-group">
            <div class="nav-group-title">Scan Material</div>
            <a href="{{ route('scan.setup') }}" class="nav-item {{ request()->routeIs('scan.setup') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Setup STO
            </a>
            <a href="{{ route('scan.index') }}" class="nav-item {{ request()->routeIs('scan.index') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                Scanner
            </a>
            <a href="{{ route('scan.results') }}" class="nav-item {{ request()->routeIs('scan.results') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Scan History
            </a>
        </div>
        @endif
    </aside>

    {{-- ═══ MAIN WORKSPACE ═══ --}}
    <main class="main-wrapper">
        <div class="page-tab-bar">
            <div class="page-tab active">{{ $title ?? 'Workspace' }}</div>
        </div>
        <div class="page-content">
            {{ $slot }}
        </div>
    </main>

    {{-- Toast --}}
    <div class="toast-container" id="toastContainer"></div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            const icon = type === 'success'
                ? '<svg width="14" height="14" fill="none" stroke="#22a06b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>'
                : '<svg width="14" height="14" fill="none" stroke="#d92d20" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            toast.innerHTML = `${icon} <span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.2s';
                setTimeout(() => toast.remove(), 200);
            }, 3000);
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('active');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
