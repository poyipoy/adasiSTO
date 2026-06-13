<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'STO' }} - Scan To Office</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --topbar-bg: #1d252c;
            --topbar-height: 42px;
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
        body {
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
            font-size: 13px;
            background: var(--workspace-bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }
        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .topbar {
            height: var(--topbar-height);
            background: var(--topbar-bg);
            color: #e2e8f0;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            border-bottom: 1px solid #151a20;
        }
        .topbar-left, .topbar-right { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .topbar-logo { height: 22px; display: block; }
        .topbar-divider { width: 1px; height: 20px; background: #4a5568; }
        .topbar-app { font-size: 13px; font-weight: 600; white-space: nowrap; }
        .topbar-app small { color: #8896a6; font-weight: 400; margin-left: 4px; }
        .topbar-user { display: flex; align-items: center; gap: 6px; color: #cdd5e0; font-size: 12px; }
        .topbar-avatar {
            width: 22px; height: 22px; border-radius: 50%;
            background: #4a5568; display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 600;
        }
        .topbar-btn, .hamburger {
            background: none; border: none; color: #cdd5e0; cursor: pointer;
            padding: 5px 7px; display: inline-flex; align-items: center; border-radius: 2px;
        }
        .topbar-btn:hover, .hamburger:hover { background: rgba(255,255,255,0.08); }
        .hamburger { display: none; }
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
            transition: transform 0.2s ease;
        }
        .nav-group { padding: 6px 0; border-bottom: 1px solid var(--sidebar-border); }
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
            border-left: 3px solid transparent;
            text-decoration: none;
        }
        .nav-item:hover { background: var(--sidebar-hover); color: #fff; text-decoration: none; }
        .nav-item.active { background: var(--sidebar-active); color: #fff; border-left-color: var(--primary); }
        .nav-item svg { width: 16px; height: 16px; color: #7b8a9a; flex-shrink: 0; }
        .nav-item.active svg, .nav-item:hover svg { color: var(--primary); }
        .main-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            min-height: calc(100vh - var(--topbar-height));
            display: flex;
            flex-direction: column;
        }
        .page-tab-bar {
            height: 32px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: stretch;
        }
        .page-tab {
            display: flex;
            align-items: center;
            padding: 0 14px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            border-bottom: 2px solid var(--primary);
            background: #fafbfc;
            border-right: 1px solid var(--border-light);
            white-space: nowrap;
        }
        .page-content { padding: 12px; flex: 1; }
        .enterprise-toolbar {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 4px 8px;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        .toolbar-sep { width: 1px; height: 20px; background: var(--border); margin: 0 4px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 4px;
            padding: 4px 10px; font-size: 12px; font-weight: 500; border-radius: 2px;
            border: 1px solid var(--border); background: var(--surface); color: var(--text);
            cursor: pointer; text-decoration: none; font-family: inherit; line-height: 1.4;
        }
        .btn:hover { background: #edf0f4; text-decoration: none; }
        .btn-primary { background: var(--primary); border-color: var(--primary-dark); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); color: #fff; }
        .btn-success { background: var(--success); border-color: #1a8a59; color: #fff; }
        .btn-danger { background: var(--danger); border-color: #b91c1c; color: #fff; }
        .btn-icon {
            padding: 4px; background: transparent; border: 1px solid transparent;
            color: var(--text-muted); cursor: pointer; border-radius: 2px;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-icon svg, .btn svg, .topbar-btn svg, .hamburger svg { width: 16px; height: 16px; }
        .btn-icon:hover { background: #edf0f4; border-color: var(--border); color: var(--text); }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 12px;
        }
        .card-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
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
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 1px rgba(0,114,206,0.15); }
        .table-container { background: var(--surface); border: 1px solid var(--border); overflow-x: auto; }
        .table-enterprise { width: 100%; border-collapse: collapse; font-size: 12px; background: #fff; }
        .table-enterprise th {
            background: #f4f5f7; border-bottom: 2px solid var(--border);
            font-weight: 600; text-align: left; padding: 6px 10px;
            white-space: nowrap; color: var(--text); font-size: 11px; text-transform: uppercase;
        }
        .table-enterprise td { border-bottom: 1px solid var(--border-light); padding: 5px 10px; white-space: nowrap; color: var(--text); }
        .table-enterprise tr:hover td { background: var(--row-hover); }
        .dataTables_wrapper { font-size: 12px; }
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { padding: 6px 10px; color: var(--text) !important; }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--border); padding: 3px 6px; border-radius: 2px; font-size: 12px; outline: none; background: #fff;
        }
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
        .badge-valid { background: #dcfce7; color: #166534; }
        .badge-invalid { background: #fee2e2; color: #991b1b; }
        .badge-duplicate { background: #fef3c7; color: #92400e; }
        .badge-info { background: #bbdefb; color: #0d47a1; }
        .badge-pending { background: #e5e7eb; color: #4b5563; }
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1000;
            display: flex; align-items: flex-start; justify-content: center; padding-top: 80px;
            opacity: 0; pointer-events: none; transition: opacity 0.15s;
        }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-content { background: #fff; border: 1px solid var(--border); width: 100%; max-width: 480px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); }
        .modal-header, .modal-footer { padding: 8px 12px; background: #f4f5f7; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 6px; }
        .modal-footer { border-top: 1px solid var(--border); border-bottom: 0; justify-content: flex-end; }
        .modal-body { padding: 12px; }
        .toast-container { position: fixed; bottom: 16px; right: 16px; z-index: 2000; display: flex; flex-direction: column; gap: 6px; }
        .toast {
            background: var(--surface); border: 1px solid var(--border); border-left: 3px solid var(--primary);
            padding: 8px 12px; font-size: 12px; font-weight: 500; min-width: 220px; box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .toast-success { border-left-color: var(--success); }
        .toast-error { border-left-color: var(--danger); }
        .mono { font-family: Consolas, "Courier New", monospace; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .hamburger { display: inline-flex; }
            .page-tab-bar { display: none; }
            .topbar-user span { display: none; }
            .topbar-app small { display: none; }
            
            /* Mobile optimizations */
            body { font-size: 14px; }
            .topbar { height: 50px; padding: 0 16px; }
            .sidebar { top: 50px; height: calc(100vh - 50px); }
            .main-wrapper { margin-top: 50px; min-height: calc(100vh - 50px); }
            
            .nav-item { padding: 12px 16px; font-size: 14px; }
            .nav-group-title { padding: 12px 16px 4px; font-size: 11px; }
            
            .btn { padding: 8px 14px; font-size: 13px; min-height: 40px; border-radius: 4px; }
            .btn-icon { padding: 8px; min-width: 40px; min-height: 40px; }
            .form-control { padding: 10px 12px; font-size: 14px; min-height: 40px; border-radius: 4px; }
            .form-label { font-size: 12px; margin-bottom: 6px; }
            .badge { font-size: 11px; padding: 2px 8px; }
            
            .card { padding: 14px; border-radius: 6px; margin-bottom: 12px; }
            .page-content { padding: 10px; }
            
            .modal-overlay { padding-top: 40px; align-items: center; }
            .modal-content { margin: 0 16px; border-radius: 8px; }
            .modal-body { padding: 16px; font-size: 14px; line-height: 1.5; }
            .modal-header, .modal-footer { padding: 12px 16px; }
            
            .toast-container { left: 16px; right: 16px; bottom: 16px; }
            .toast { width: 100%; min-width: 0; padding: 12px 16px; font-size: 13px; border-radius: 6px; }
            
            .enterprise-toolbar { flex-direction: column; align-items: stretch; gap: 8px; padding: 12px; }
            .enterprise-toolbar .btn { width: 100%; justify-content: center; }
            .enterprise-toolbar .toolbar-sep { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php($currentSto = app(\App\Services\STOService::class)->active())
    <header class="topbar">
        <div class="topbar-left">
            <button class="hamburger" type="button" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <img src="{{ asset('assets/images/logo-adasi.png') }}" alt="ADASI" class="topbar-logo">
            <div class="topbar-divider"></div>
            <div class="topbar-app">STO<small>Scan To Office</small></div>
            <div class="topbar-divider"></div>
            <div class="topbar-app"><small>Current STO:</small> {{ $currentSto?->code ?? '-' }}</div>
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
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"></path></svg>
                    All Scan Results
                </a>
                <a href="{{ route('admin.material-summary') }}" class="nav-item {{ request()->routeIs('admin.material-summary*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707L13.293 3.293A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Material Summary
                </a>
            </div>
            <div class="nav-group">
                <div class="nav-group-title">Master Data</div>
                <a href="{{ route('admin.master-sto') }}" class="nav-item {{ request()->routeIs('admin.master-sto') ? 'active' : '' }}">Master STO</a>
                <a href="{{ route('admin.master-plant') }}" class="nav-item {{ request()->routeIs('admin.master-plant') ? 'active' : '' }}">Master Plant</a>
                <a href="{{ route('admin.master-material') }}" class="nav-item {{ request()->routeIs('admin.master-material') ? 'active' : '' }}">Master Material</a>
                <a href="{{ route('admin.master-keterangan') }}" class="nav-item {{ request()->routeIs('admin.master-keterangan') ? 'active' : '' }}">Master Keterangan</a>
                <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">User Management</a>
            </div>
        @else
            <div class="nav-group">
                <div class="nav-group-title">Scan Material</div>
                <a href="{{ route('scan.setup') }}" class="nav-item {{ request()->routeIs('scan.setup') ? 'active' : '' }}">Setup STO</a>
                <a href="{{ route('scan.scanner') }}" class="nav-item {{ request()->routeIs('scan.scanner') ? 'active' : '' }}">Scanner</a>
                <a href="{{ route('scan.history') }}" class="nav-item {{ request()->routeIs('scan.history') ? 'active' : '' }}">Scan History</a>
            </div>
        @endif
    </aside>

    <main class="main-wrapper">
        <div class="page-tab-bar">
            <div class="page-tab">{{ $title ?? 'Workspace' }}</div>
        </div>
        <div class="page-content">
            {{ $slot }}
        </div>
    </main>

    <div class="toast-container" id="toastContainer"></div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });

        const nativeFetch = window.fetch.bind(window);
        window.fetch = function(resource, options = {}) {
            const method = (options.method || 'GET').toUpperCase();
            const unsafeMethod = !['GET', 'HEAD', 'OPTIONS'].includes(method);

            if (unsafeMethod) {
                const headers = new Headers(options.headers || {});
                if (!headers.has('X-CSRF-TOKEN')) {
                    headers.set('X-CSRF-TOKEN', csrfToken);
                }
                if (!headers.has('Accept')) {
                    headers.set('Accept', 'application/json');
                }
                options.headers = headers;
                options.credentials = options.credentials || 'same-origin';
            }

            return nativeFetch(resource, options);
        };

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('active');
            }
        });
    </script>
    @stack('scripts')

    @if(session('success'))
        <script>showToast(@json(session('success')));</script>
    @endif
    @if(session('error'))
        <script>showToast(@json(session('error')), 'error');</script>
    @endif
</body>
</html>
