<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'STO - Scan To Office' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-adasi.png') }}?v=2">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logo-adasi.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logo-adasi.png') }}?v=2">
    <meta name="description" content="Sistem Stock Opname Material berbasis QR Code / Barcode">

    {{-- Google Fonts --}}
    <link rel="stylesheet" href="{{ asset('vendor/@fontsource/inter/index.css') }}">

    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">

    {{-- Chart.js --}}
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary: #0a0e1a;
            --bg-secondary: #111827;
            --bg-card: rgba(17, 24, 39, 0.8);
            --bg-card-hover: rgba(30, 41, 59, 0.9);
            --border-color: rgba(99, 102, 241, 0.15);
            --border-glow: rgba(99, 102, 241, 0.3);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-primary: #6366f1;
            --accent-secondary: #8b5cf6;
            --accent-gradient: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #f43f5e;
            --info: #3b82f6;
            --sidebar-width: 260px;
            --shadow-glow: 0 0 20px rgba(99, 102, 241, 0.15);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ═══ SIDEBAR ═══ */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, rgba(17, 24, 39, 0.95), rgba(10, 14, 26, 0.98));
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border-color);
            z-index: 50;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
            color: white;
            box-shadow: var(--shadow-glow);
        }

        .sidebar-brand .brand-text {
            font-size: 1.1rem;
            font-weight: 700;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-brand .brand-sub {
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 400;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0.75rem;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            padding: 0 0.75rem;
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.75rem;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 2px;
        }

        .nav-link:hover {
            background: rgba(99, 102, 241, 0.08);
            color: var(--text-primary);
        }

        .nav-link.active {
            background: rgba(99, 102, 241, 0.12);
            color: var(--accent-primary);
            box-shadow: inset 3px 0 0 var(--accent-primary);
        }

        .nav-link svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
            color: white;
        }

        .user-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-role {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: capitalize;
        }

        /* ═══ MAIN CONTENT ═══ */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .topbar {
            height: 60px;
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .topbar-title {
            font-size: 1rem;
            font-weight: 600;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            padding: 0.5rem;
        }

        .page-content {
            padding: 1.5rem;
            max-width: 1400px;
        }

        /* ═══ CARDS ═══ */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            border-color: var(--border-glow);
            box-shadow: var(--shadow-glow);
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover::before { opacity: 1; }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--border-glow);
            box-shadow: var(--shadow-glow);
        }

        .stat-card .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            font-size: 1.2rem;
        }

        .stat-card .stat-icon.indigo { background: rgba(99, 102, 241, 0.15); color: #818cf8; }
        .stat-card .stat-icon.green { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .stat-card .stat-icon.amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .stat-card .stat-icon.rose { background: rgba(244, 63, 94, 0.15); color: #fb7185; }
        .stat-card .stat-icon.blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.25rem;
        }

        .stat-card .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ═══ BUTTONS ═══ */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: rgba(99, 102, 241, 0.1);
            color: var(--accent-primary);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(99, 102, 241, 0.2);
        }

        .btn-danger {
            background: rgba(244, 63, 94, 0.1);
            color: var(--danger);
            border: 1px solid rgba(244, 63, 94, 0.2);
        }

        .btn-danger:hover {
            background: rgba(244, 63, 94, 0.2);
        }

        .btn-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .btn-success:hover {
            background: rgba(16, 185, 129, 0.2);
        }

        .btn-sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.75rem;
            border-radius: 8px;
        }

        /* ═══ FORMS ═══ */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
        }

        .form-control {
            width: 100%;
            padding: 0.65rem 0.9rem;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        select.form-control option {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        /* ═══ DATATABLES ═══ */
        .dataTables_wrapper {
            color: var(--text-secondary) !important;
            font-size: 0.85rem;
        }

        table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
        }

        table.dataTable thead th {
            background: rgba(99, 102, 241, 0.06) !important;
            color: var(--text-secondary) !important;
            border-bottom: 1px solid var(--border-color) !important;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding: 0.75rem 0.6rem !important;
            white-space: nowrap;
        }

        table.dataTable tbody td {
            padding: 0.65rem 0.6rem !important;
            border-bottom: 1px solid rgba(99, 102, 241, 0.06) !important;
            color: var(--text-primary) !important;
            font-size: 0.8rem !important;
            vertical-align: middle;
        }

        table.dataTable tbody tr {
            background: transparent !important;
            transition: background 0.15s ease;
        }

        table.dataTable tbody tr:hover {
            background: rgba(99, 102, 241, 0.04) !important;
        }

        .dataTables_length select,
        .dataTables_filter input {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 8px !important;
            color: var(--text-primary) !important;
            padding: 0.4rem 0.6rem !important;
            font-size: 0.8rem !important;
            outline: none !important;
        }

        .dataTables_filter input:focus {
            border-color: var(--accent-primary) !important;
        }

        .dataTables_info,
        .dataTables_length label,
        .dataTables_filter label {
            color: var(--text-muted) !important;
            font-size: 0.8rem !important;
        }

        .dataTables_paginate .paginate_button {
            background: transparent !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 8px !important;
            color: var(--text-secondary) !important;
            padding: 0.3rem 0.6rem !important;
            margin: 0 2px !important;
            font-size: 0.8rem !important;
        }

        .dataTables_paginate .paginate_button.current {
            background: var(--accent-gradient) !important;
            border-color: transparent !important;
            color: white !important;
        }

        .dataTables_paginate .paginate_button:hover:not(.current) {
            background: rgba(99, 102, 241, 0.1) !important;
            color: var(--accent-primary) !important;
        }

        /* ═══ BADGE ═══ */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-success { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .badge-warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .badge-danger { background: rgba(244, 63, 94, 0.15); color: #fb7185; }
        .badge-info { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }

        /* ═══ TOAST ═══ */
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 100;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .toast {
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            backdrop-filter: blur(20px);
            animation: slideInRight 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 280px;
        }

        .toast-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }

        .toast-error {
            background: rgba(244, 63, 94, 0.15);
            border: 1px solid rgba(244, 63, 94, 0.3);
            color: #fb7185;
        }

        /* ═══ MODAL ═══ */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 60;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-content {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            transform: scale(0.95);
            transition: transform 0.25s ease;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0.25rem;
        }

        /* ═══ FILTER BAR ═══ */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
            align-items: flex-end;
        }

        .filter-bar .form-group {
            margin-bottom: 0;
            min-width: 150px;
        }

        /* ═══ ANIMATIONS ═══ */
        @keyframes slideInRight {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeInUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 8px rgba(99, 102, 241, 0.3); }
            50% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.6); }
        }

        .animate-in {
            animation: fadeInUp 0.5s ease forwards;
        }

        .animate-delay-1 { animation-delay: 0.1s; opacity: 0; }
        .animate-delay-2 { animation-delay: 0.2s; opacity: 0; }
        .animate-delay-3 { animation-delay: 0.3s; opacity: 0; }
        .animate-delay-4 { animation-delay: 0.4s; opacity: 0; }

        /* ═══ RESPONSIVE ═══ */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .hamburger {
                display: block;
            }

            .page-content {
                padding: 1rem;
            }

            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filter-bar {
                flex-direction: column;
            }

            .filter-bar .form-group {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ═══ SCROLLBAR ═══ */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.2);
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.4); }

        /* ═══ MONOSPACE DATA ═══ */
        .mono { font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ═══ CHART CONTAINERS ═══ */
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">S</div>
            <div>
                <div class="brand-text">STO System</div>
                <div class="brand-sub">Scan To Office</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            @if(auth()->user()->isAdmin())
            <div class="nav-section">
                <div class="nav-section-title">Dashboard</div>
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Monitoring</div>
                <a href="{{ route('admin.scan-results') }}" class="nav-link {{ request()->routeIs('admin.scan-results*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Hasil Scan
                </a>
                <a href="{{ route('admin.barcode-overview') }}" class="nav-link {{ request()->routeIs('admin.barcode-overview*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Overview Barcode
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Master Data</div>
                <a href="{{ route('admin.master.plants') }}" class="nav-link {{ request()->routeIs('admin.master.plants') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Master Plant
                </a>
                <a href="{{ route('admin.master.materials') }}" class="nav-link {{ request()->routeIs('admin.master.materials') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Master Material
                </a>
                <a href="{{ route('admin.master.keterangan') }}" class="nav-link {{ request()->routeIs('admin.master.keterangan') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    Master Keterangan
                </a>
            </div>
            @else
            <div class="nav-section">
                <div class="nav-section-title">Scanner</div>
                <a href="{{ route('scan.setup') }}" class="nav-link {{ request()->routeIs('scan.setup') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Setup STO
                </a>
                <a href="{{ route('scan.index') }}" class="nav-link {{ request()->routeIs('scan.index') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Scanner
                </a>
                <a href="{{ route('scan.results') }}" class="nav-link {{ request()->routeIs('scan.results') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Hasil Scan
                </a>
            </div>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->role }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin-top: 0.5rem;">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <main class="main-content">
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h1 class="topbar-title">{{ $title ?? 'Dashboard' }}</h1>
            </div>
            <div class="topbar-actions">
                <span style="font-size: 0.75rem; color: var(--text-muted);">{{ now()->format('d M Y, H:i') }}</span>
            </div>
        </header>

        <div class="page-content">
            {{ $slot }}
        </div>
    </main>

    {{-- Toast Container --}}
    <div class="toast-container" id="toastContainer"></div>

    {{-- Sidebar overlay for mobile --}}
    <div id="sidebarOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:45;" onclick="document.getElementById('sidebar').classList.remove('open'); this.style.display='none';"></div>

    {{-- jQuery & DataTables --}}
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    {{-- SweetAlert2 --}}
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script>
        // CSRF token for AJAX
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        // SweetAlert Notification
        function showToast(message, type = 'success') {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Berhasil!' : 'Perhatian!',
                text: message,
                timer: 3000,
                showConfirmButton: false,
                background: '#ffffff',
                color: '#1f2937'
            });
        }

        // Global Confirm Action
        function confirmAction(message, callback) {
            Swal.fire({
                title: 'Konfirmasi',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                background: '#ffffff',
                color: '#1f2937'
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        }

        // Mobile sidebar
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        document.querySelector('.hamburger')?.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
        });

        // Close sidebar on link click (mobile)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('open');
                    overlay.style.display = 'none';
                }
            });
        });

        // Count-up animation for stat values
        function animateCount(element, target) {
            let current = 0;
            const increment = target / 40;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current).toLocaleString();
                }
            }, 25);
        }

        document.querySelectorAll('.stat-value[data-count]').forEach(el => {
            animateCount(el, parseInt(el.dataset.count));
        });

        // Global leave confirmation for back button / browser close
        const globalLeaveConfirm = {
            enabled: false,
            message: 'Apakah yakin ingin keluar?',
            dirtyCheck: null,
        };

        function registerGlobalLeaveConfirmation(checkDirty, message = 'Apakah yakin ingin keluar?') {
            globalLeaveConfirm.enabled = true;
            globalLeaveConfirm.dirtyCheck = checkDirty;
            globalLeaveConfirm.message = message;

            if (!history.state || !history.state.leaveConfirmRegistered) {
                history.replaceState({ ...history.state, leaveConfirmRegistered: true }, '');
                history.pushState({ leaveConfirmRegistered: true }, '');
            }
        }

        function unregisterGlobalLeaveConfirmation() {
            globalLeaveConfirm.enabled = false;
            globalLeaveConfirm.dirtyCheck = null;
        }

        function shouldConfirmLeave() {
            return globalLeaveConfirm.enabled
                && typeof globalLeaveConfirm.dirtyCheck === 'function'
                && globalLeaveConfirm.dirtyCheck();
        }

        window.addEventListener('beforeunload', event => {
            if (!shouldConfirmLeave()) return;

            event.preventDefault();
            event.returnValue = globalLeaveConfirm.message;
            return globalLeaveConfirm.message;
        });

        window.addEventListener('popstate', event => {
            if (!shouldConfirmLeave()) return;

            const confirmed = window.confirm(globalLeaveConfirm.message);
            if (!confirmed) {
                history.pushState({ leaveConfirmRegistered: true }, '');
            } else {
                unregisterGlobalLeaveConfirmation();
            }
        });

        registerGlobalLeaveConfirmation(() => true);
    </script>

    @stack('scripts')

    @if(session('success'))
    <script>showToast('{{ session('success') }}', 'success');</script>
    @endif
    @if(session('error'))
    <script>showToast('{{ session('error') }}', 'error');</script>
    @endif
</body>
</html>
