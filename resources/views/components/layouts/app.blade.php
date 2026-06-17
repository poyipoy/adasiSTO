<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'STO' }} - Scan To Office</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-adasi.png') }}?v=2">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logo-adasi.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logo-adasi.png') }}?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/adasi-splash.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        if (window.self !== window.top) {
            document.documentElement.classList.add('is-iframe');
        }
    </script>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --topbar-bg: #1d252c;
            --topbar-height: 44px;
            --workspace-headbar-height: 40px;
            --sidebar-bg: #161719;
            --sidebar-hover: #2a2b2f;
            --sidebar-active: #3d3d42;
            --sidebar-width: 280px;
            --sidebar-border: #2f3035;
            --sidebar-text: #e2e8f0;
            --sidebar-icon: #cdd5e0;
            --workspace-bg: #e1e6eb;
            --surface: #ffffff;
            --border: #cdd5e0;
            --border-light: #e2e8f0;
            --row-hover: #f0f0f0;
            --row-selected: #d3e3fd;
            --primary: #0066d4;
            --primary-dark: #0054b3;
            --text: #1a1a1a;
            --text-secondary: #5c5c5c;
            --text-muted: #808b99;
            --success: #197f4c;
            --warning: #f28b00;
            --danger: #b92525;
            --tab-bg: #e1e6eb;
            --tab-active-bg: #ffffff;
            --tab-active-border: #0066d4;
            --table-header-bg: #f5f6f8;
            --table-header-text: #5c5c5c;
        }

        body {
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
            font-size: 13px;
            background: var(--workspace-bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        a {
            color: var(--primary);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .topbar {
            height: var(--topbar-height);
            background: var(--topbar-bg);
            color: #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            border-bottom: 1px solid #151a20;
        }

        .topbar-left,
        .topbar-right {
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
            white-space: nowrap;
        }

        .topbar-app small {
            color: #8896a6;
            font-weight: 400;
            margin-left: 4px;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #cdd5e0;
            font-size: 12px;
        }

        .topbar-avatar {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #4a5568;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
        }

        .topbar-btn,
        .hamburger {
            background: none;
            border: none;
            color: #cdd5e0;
            cursor: pointer;
            padding: 5px 7px;
            display: inline-flex;
            align-items: center;
            border-radius: 2px;
        }

        .topbar-btn:hover,
        .hamburger:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .hamburger {
            display: inline-flex;
        }

        .workspace-headbar {
            position: fixed;
            top: var(--topbar-height);
            left: 0;
            right: 0;
            height: var(--workspace-headbar-height);
            z-index: 190;
            display: flex;
            align-items: stretch;
            background: #2f3339;
            border-bottom: 1px solid #20252b;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.18);
        }

        .workspace-headbar .hamburger {
            width: 48px;
            min-width: 48px;
            height: 100%;
            justify-content: center;
            border-radius: 0;
            border-right: 1px solid #4a4f57;
            color: #f1f5f9;
        }

        .sidebar {
            position: fixed;
            top: calc(var(--topbar-height) + var(--workspace-headbar-height));
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--topbar-height) - var(--workspace-headbar-height));
            background: var(--sidebar-bg);
            border-right: 1px solid #111214;
            z-index: 100;
            overflow-y: auto;
            transition:
                transform 1s ease,
                width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #3b3c42;
            border-radius: 8px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #101113;
        }

        body.sidebar-collapsed .sidebar {
            width: 60px;
        }

        body.sidebar-collapsed .sidebar-section-label,
        body.sidebar-collapsed .sidebar-chevron,
        body.sidebar-collapsed .nav-item span {
            max-width: 0;
            opacity: 0;
            pointer-events: none;
            transform: translateX(-4px);
        }

        body.sidebar-collapsed .sidebar-chevron {
            width: 0;
            margin-left: 0;
        }

        body.sidebar-collapsed .sidebar-menu {
            padding: 8px 0;
        }

        body.sidebar-collapsed .sidebar-section {
            margin-bottom: 2px;
        }

        body.sidebar-collapsed .sidebar-section-toggle,
        body.sidebar-collapsed .nav-item {
            width: 60px;
            overflow: hidden;
        }

        body.sidebar-collapsed .sidebar-section-toggle {
            background: transparent;
        }


        .sidebar-menu {
            padding: 8px 10px 12px;
            transition: padding 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-section {
            margin-bottom: 3px;
        }

        .sidebar-section-toggle {
            width: 100%;
            height: 40px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px 0 17px;
            border: 0;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-radius: 0;
            background: transparent;
            color: var(--sidebar-text);
            cursor: pointer;
            font: inherit;
            text-align: left;
            transition:
                width 2s cubic-bezier(0.4, 0, 0.2, 1),
                height 2s cubic-bezier(0.4, 0, 0.2, 1),
                background 0.15s ease,
                color 0.15s ease;
        }

        .sidebar-section-toggle:hover {
            background: var(--sidebar-hover);
            border-left-color: rgba(255, 255, 255, 0.5);
        }

        .sidebar-section-toggle:hover .sidebar-section-label,
        .nav-item:hover span {
            text-decoration: none;
            box-shadow: inset 0 -1px 0 currentColor;
        }

        .sidebar-section.is-floating-open>.sidebar-section-toggle {
            background: var(--sidebar-active);
            color: var(--sidebar-text);
            border-left-color: var(--primary);
        }

        .sidebar-section-icon,
        .nav-item svg {
            width: 18px;
            height: 18px;
            color: var(--sidebar-icon);
            flex-shrink: 0;
        }

        .sidebar-section-label {
            display: inline-block;
            flex: 0 1 auto;
            min-width: 0;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 14px;
            font-weight: 500;
            padding-bottom: 2px;
            opacity: 1;
            transform: translateX(0);
            transition:
                max-width 1s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 1s ease,
                transform 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-chevron {
            width: 14px;
            height: 14px;
            max-width: 14px;
            margin-left: auto;
            color: var(--sidebar-icon);
            opacity: 1;
            transition:
                transform 0.15s ease,
                max-width 1s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 1s ease;
            flex-shrink: 0;
        }

        .sidebar-section.is-open .sidebar-chevron {
            transform: rotate(180deg);
        }

        .sidebar-section-items {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 0.3s ease-in-out;
            padding: 0;
        }

        .sidebar-section.is-open .sidebar-section-items {
            grid-template-rows: 1fr;
        }

        body.sidebar-collapsed .sidebar-section.is-open .sidebar-section-items {
            grid-template-rows: 0fr;
        }

        .sidebar-section-items-inner {
            overflow: hidden;
            padding: 4px 0 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 34px;
            padding: 7px 10px 7px 38px;
            font-size: 13px;
            color: var(--sidebar-text);
            border-left: 3px solid transparent;
            border-radius: 0;
            text-decoration: none;
        }

        .nav-item:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text);
            text-decoration: none;
        }

        .nav-item.active {
            background: var(--sidebar-active);
            color: var(--sidebar-text);
            border-left-color: var(--primary);
        }

        .nav-item span {
            display: inline-block;
            flex: 0 1 auto;
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding-bottom: 2px;
            opacity: 1;
            transform: translateX(0);
            transition:
                max-width 1s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 1s ease,
                transform 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-item.active svg,
        .nav-item:hover svg {
            color: var(--primary);
        }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: calc(var(--topbar-height) + var(--workspace-headbar-height));
            min-height: calc(100vh - var(--topbar-height) - var(--workspace-headbar-height));
            display: flex;
            flex-direction: column;
            transition: margin-left 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.sidebar-collapsed .main-wrapper {
            margin-left: 60px;
        }

        /* --- TAB SYSTEM --- */
        .page-tab-bar {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: flex-end;
            gap: 6px;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 4px 8px 0 8px;
        }

        .page-tab-bar::-webkit-scrollbar {
            height: 0;
        }

        .page-tab {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 16px;
            align-items: center;
            gap: 8px;
            flex: 1 1 140px;
            height: 34px;
            min-width: 96px;
            max-width: 320px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            background: var(--tab-bg);
            border: 1px solid var(--border);
            border-bottom: 3px solid transparent;
            border-radius: 4px 4px 0 0;
            white-space: nowrap;
            cursor: pointer;
            user-select: none;
            overflow: hidden;
        }

        .page-tab.active {
            color: var(--text);
            background: var(--tab-active-bg);
            border-bottom-color: var(--tab-active-border);
        }

        .page-tab:hover:not(.active) {
            background: var(--row-hover);
        }

        .page-tab>span:first-of-type {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .page-tab-recent {
            display: flex;
            flex: 0 0 112px;
            min-width: 112px;
            max-width: 112px;
            color: #fff;
            background: #3b3d43;
            border-color: #3b3d43;
            border-radius: 0;
            font-weight: 700;
        }

        .page-tab-recent.active {
            color: #fff;
            background: #3b3d43;
            border-bottom-color: var(--primary);
        }

        .page-tab-recent:hover:not(.active) {
            background: #474a52;
        }

        .page-tab-recent svg {
            color: #fff;
            width: 15px !important;
            height: 15px !important;
        }

        .tab-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            color: var(--text-muted);
            justify-self: end;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.12s ease, background 0.12s ease, color 0.12s ease;
        }

        .page-tab:hover .tab-close,
        .page-tab:focus-within .tab-close {
            opacity: 1;
            pointer-events: auto;
        }

        .tab-close:hover {
            background: #e2e8f0;
            color: var(--danger);
        }

        .tab-close svg {
            width: 10px;
            height: 10px;
        }

        @media (hover: none) {
            .tab-close {
                opacity: 1;
                pointer-events: auto;
            }
        }

        .page-content {
            padding: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        @keyframes fadeInTab {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .tab-pane {
            display: none;
            flex: 1;
            width: 100%;
            height: 100%;
            border: none;
        }

        .tab-pane.active {
            display: block;
            animation: fadeInTab 0.6s ease-out forwards;
        }

        .recent-list-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            color: var(--text);
            text-decoration: none;
            cursor: pointer;
            border-bottom: 1px solid var(--border-light);
        }

        .recent-list-item:last-child {
            border-bottom: none;
        }

        .recent-list-item:hover {
            background: var(--row-hover);
            text-decoration: none;
            color: var(--text);
        }

        .recent-list-item svg {
            width: 18px;
            height: 18px;
            color: var(--text-secondary);
        }

        /* --- LOADING EQUALIZER --- */
        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .loading-equalizer {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 4px;
            height: 30px;
        }

        .loading-equalizer .bar {
            width: 5px;
            background-color: var(--primary);
            animation: equalize 1s infinite ease-in-out;
        }

        .loading-equalizer .bar:nth-child(1) {
            height: 10px;
            animation-delay: -0.4s;
        }

        .loading-equalizer .bar:nth-child(2) {
            height: 20px;
            animation-delay: -0.2s;
        }

        .loading-equalizer .bar:nth-child(3) {
            height: 30px;
            animation-delay: 0s;
        }

        .loading-equalizer .bar:nth-child(4) {
            height: 20px;
            animation-delay: -0.2s;
        }

        .loading-equalizer .bar:nth-child(5) {
            height: 10px;
            animation-delay: -0.4s;
        }

        @keyframes equalize {

            0%,
            100% {
                height: 5px;
            }

            50% {
                height: 30px;
            }
        }

        .loading-text {
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
        }

        /* --- IFRAME MODE HIDING --- */
        html.is-iframe .topbar,
        html.is-iframe .workspace-headbar,
        html.is-iframe .sidebar,
        html.is-iframe .page-tab-bar,
        html.is-iframe #pane-tab-recent {
            display: none !important;
        }

        html.is-iframe .main-wrapper {
            margin: 0 !important;
            min-height: 100vh !important;
        }

        html.is-iframe,
        html.is-iframe body {
            height: 100%;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        html.is-iframe .page-content {
            padding: 12px;
        }

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
            font-family: inherit;
            line-height: 1.4;
        }

        .btn:hover {
            background: #edf0f4;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary-dark);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            color: #fff;
        }

        .btn-success {
            background: var(--success);
            border-color: #1a8a59;
            color: #fff;
        }

        .btn-danger {
            background: var(--danger);
            border-color: #b91c1c;
            color: #fff;
        }

        .btn-icon {
            padding: 4px;
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: 2px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-icon svg,
        .btn svg,
        .topbar-btn svg,
        .hamburger svg {
            width: 16px;
            height: 16px;
        }

        .btn-icon:hover {
            background: var(--row-hover);
            border-color: var(--border);
            color: var(--text);
        }

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

        .form-group {
            margin-bottom: 10px;
        }

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
            background: var(--surface);
            color: var(--text);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 1px rgba(0, 114, 206, 0.15);
        }

        .table-container {
            background: var(--surface);
            border: 1px solid var(--border);
            overflow-x: auto;
        }

        .table-enterprise {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            background: var(--surface);
        }

        .table-enterprise th {
            background: var(--table-header-bg);
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            text-align: left;
            padding: 8px 12px;
            white-space: nowrap;
            color: var(--table-header-text);
            font-size: 12px;
        }

        .table-enterprise td {
            border-bottom: 1px solid var(--border-light);
            padding: 6px 12px;
            white-space: nowrap;
            color: var(--text);
        }

        .table-enterprise tr:hover td {
            background: var(--row-hover);
        }

        .dataTables_wrapper {
            font-size: 12px;
        }

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

        .badge-valid {
            background: #dcfce7;
            color: #166534;
        }

        .badge-invalid {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-duplicate {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background: #bbdefb;
            color: #0d47a1;
        }

        .badge-pending {
            background: #e5e7eb;
            color: #4b5563;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 1000;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 80px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-content {
            background: #fff;
            border: 1px solid var(--border);
            width: 100%;
            max-width: 480px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .modal-header,
        .modal-footer {
            padding: 8px 12px;
            background: #f4f5f7;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }

        .modal-footer {
            border-top: 1px solid var(--border);
            border-bottom: 0;
            justify-content: flex-end;
        }

        .modal-body {
            padding: 12px;
        }

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
            min-width: 220px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .toast-success {
            border-left-color: var(--success);
        }

        .toast-error {
            border-left-color: var(--danger);
        }

        .mono {
            font-family: Consolas, "Courier New", monospace;
        }

        @media (max-width: 768px) {
            :root {
                --topbar-height: 50px;
                --workspace-headbar-height: 42px;
            }

            .modal-overlay {
                align-items: center;
                padding-top: 0;
                padding-left: 16px;
                padding-right: 16px;
            }

            .sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 90;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s ease;
            }

            .sidebar-overlay.active {
                opacity: 1;
                pointer-events: auto;
            }

            .sidebar {
                transform: translateX(-100%);
                /* Animasi lebih cepat saat sidebar ditutup di HP */
                transition: transform 0.25s ease-in;
            }

            .sidebar.open {
                transform: translateX(0);
                /* Animasi sedikit lebih santai saat sidebar dibuka di HP */
                transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .hamburger {
                display: inline-flex;
            }

            .topbar-user span {
                display: none;
            }

            .topbar-app small {
                display: none;
            }

            /* --- Mobile font size --- */
            body {
                font-size: 14px;
            }

            /* --- Topbar: taller, bigger touch targets --- */
            .topbar {
                padding: 0 10px;
            }

            .topbar-btn,
            .hamburger {
                padding: 10px;
                min-width: 40px;
                min-height: 40px;
            }

            .workspace-headbar .hamburger {
                min-width: 44px;
                width: 44px;
            }

            .page-tab-bar {
                gap: 4px;
                padding-left: 6px;
                padding-right: 6px;
            }

            .page-tab {
                flex-basis: 128px;
                min-width: 112px;
                max-width: 220px;
                height: 34px;
                font-size: 12px;
            }

            .page-tab-recent {
                flex: 0 0 104px;
                min-width: 104px;
                max-width: 104px;
            }

            /* --- Sidebar nav items: bigger tap targets --- */
            .sidebar {
                width: 50vw;
                min-width: 200px;
            }

            .sidebar-menu {
                padding: 8px 10px 12px;
            }

            .sidebar-section-toggle {
                min-height: 44px;
            }

            .nav-item {
                padding: 11px 12px 11px 40px;
                font-size: 14px;
            }

            /* --- Buttons: bigger touch targets --- */
            .btn {
                padding: 8px 14px;
                font-size: 13px;
                min-height: 40px;
            }

            .btn-icon {
                padding: 8px;
                min-width: 40px;
                min-height: 40px;
            }

            .btn-icon svg,
            .btn svg {
                width: 18px;
                height: 18px;
            }

            /* --- Form controls: bigger tap targets --- */
            .form-control {
                padding: 10px 12px;
                font-size: 14px;
                min-height: 40px;
            }

            .form-label {
                font-size: 12px;
                margin-bottom: 4px;
            }

            /* --- Badges: slightly larger --- */
            .badge {
                font-size: 11px;
                padding: 2px 8px;
            }

            /* --- Toast: full-width at bottom --- */
            .toast-container {
                left: 16px;
                right: 16px;
            }

            .toast {
                min-width: unset;
            }

            /* --- Modal: closer to top, side margins --- */
            .modal-overlay {
                padding-top: 40px;
                align-items: flex-start;
            }

            .modal-content {
                margin: 0 12px;
                max-width: calc(100vw - 24px);
            }

            .modal-header,
            .modal-footer {
                padding: 10px 14px;
            }

            .modal-body {
                padding: 14px;
            }

            /* --- Card: comfortable padding --- */
            .card {
                padding: 14px;
            }

            /* --- Page content: tighter padding on mobile --- */
            .page-content {
                padding: 8px;
            }

            /* --- DataTables: mobile adjustments --- */
            .dataTables_wrapper .dataTables_filter input,
            .dataTables_wrapper .dataTables_length select {
                padding: 6px 8px;
                font-size: 13px;
                min-height: 36px;
            }
        }

        .dataTables_wrapper .dataTables_processing {
            background: rgba(255, 255, 255, 0.8) !important;
            border: none !important;
            box-shadow: none !important;
            z-index: 99 !important;
            height: 100% !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .dataTables_wrapper .dataTables_processing>div {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .dataTables_wrapper .dataTables_processing>div:last-child {
            width: auto !important;
            height: auto !important;
            margin: 0 !important;
        }

        .dataTables_wrapper .dataTables_processing>div:last-child>div {
            position: static !important;
            width: auto !important;
            height: auto !important;
            border-radius: 0 !important;
            background: transparent !important;
            animation: none !important;
        }

        .dataTables_wrapper .dataTables_processing .loading-equalizer .bar {
            position: static !important;
            width: 6px !important;
            border-radius: 0 !important;
            background: var(--primary) !important;
            animation: equalize 0.8s ease-in-out infinite !important;
        }

        /* --- FLOATING SIDEBAR MENU --- */
        .floating-menu {
            position: fixed;
            z-index: 9999;
            background: #1b1b1b;
            border: 1px solid #111;
            width: 250px;
            box-shadow: -4px 0 12px rgba(255, 255, 255, 0.25), 4px 4px 12px rgba(0, 0, 0, 0.5);
            display: none;
            font-size: 13px;
        }

        .floating-menu-header {
            padding: 12px 16px;
            color: #fff;
            background: #303136;
            border-bottom: 1px solid #333;
        }

        .floating-menu-body {
            padding: 4px 0;
            display: flex;
            flex-direction: column;
        }

        .floating-menu-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }

        .floating-menu-item:hover {
            background: #2a2a2a;
            color: #fff;
            text-decoration: none;
        }

        .floating-menu-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .floating-menu-arrow {
            color: #888;
            flex-shrink: 0;
            width: 14px;
            height: 14px;
            stroke-width: 2.5;
        }
    </style>
    @stack('styles')
</head>

<body>
    @include('partials.adasi-splash')

    @php($currentSto = app(\App\Services\STOService::class)->active())
    <header class="topbar">
        <div class="topbar-left">
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
            <form id="logoutForm" method="POST" action="{{ route('logout', [], false) }}" style="margin:0;display:flex;">
                @csrf
                <button type="submit" class="topbar-btn" title="Logout">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                </button>
            </form>
        </div>
    </header>

    <div class="workspace-headbar">
        <button class="hamburger" type="button" onclick="toggleSidebar()" title="Menu">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <div class="page-tab-bar" id="pageTabBar">
            <div class="page-tab page-tab-recent" id="tab-recent" data-id="tab-recent">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Recent</span>
            </div>
            <div class="page-tab active"
                data-id="tab-{{ substr(preg_replace('/[^a-zA-Z0-9]/', '', request()->getRequestUri()), -20) }}"
                data-url="{{ request()->getRequestUri() }}"
                id="tab-{{ substr(preg_replace('/[^a-zA-Z0-9]/', '', request()->getRequestUri()), -20) }}">
                <span>{{ $title ?? 'Workspace' }}</span>
                <span class="tab-close" title="Close">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </span>
            </div>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <aside class="sidebar" id="sidebar">
        <nav class="sidebar-menu" aria-label="Main navigation">
            @if(auth()->user()->isAdmin())
                <div class="sidebar-section {{ request()->routeIs('admin.dashboard') ? 'is-open has-active' : '' }}">
                    <button type="button" class="sidebar-section-toggle" data-sidebar-toggle title="Dashboard">
                        <svg class="sidebar-section-icon" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                        <span class="sidebar-section-label">Dashboard</span>
                        <svg class="sidebar-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="sidebar-section-items">
                        <div class="sidebar-section-items-inner">
                        <a href="{{ route('admin.dashboard', [], false) }}"
                            class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Overview">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"></path>
                            </svg>
                            <span>Overview</span>
                        </a>
                        </div>
                    </div>
                </div>
                <div
                    class="sidebar-section {{ request()->routeIs('admin.scan-results*') || request()->routeIs('admin.material-summary*') || request()->routeIs('admin.material-double*') ? 'is-open has-active' : '' }}">
                    <button type="button" class="sidebar-section-toggle" data-sidebar-toggle title="STO Result">
                        <svg class="sidebar-section-icon" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6m4 6V7m4 10v-4M5 21h14M5 3v18">
                            </path>
                        </svg>
                        <span class="sidebar-section-label">STO Result</span>
                        <svg class="sidebar-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="sidebar-section-items">
                        <div class="sidebar-section-items-inner">
                        <a href="{{ route('admin.scan-results', [], false) }}"
                            class="nav-item {{ request()->routeIs('admin.scan-results*') ? 'active' : '' }}"
                            title="All Scan Results">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"></path>
                            </svg>
                            <span>All Scan Results</span>
                        </a>
                        <a href="{{ route('admin.material-summary', [], false) }}"
                            class="nav-item {{ request()->routeIs('admin.material-summary*') ? 'active' : '' }}"
                            title="Material Summary">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707L13.293 3.293A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>Material Summary</span>
                        </a>
                        <a href="{{ route('admin.material-double', [], false) }}"
                            class="nav-item {{ request()->routeIs('admin.material-double*') ? 'active' : '' }}"
                            title="Material Double">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7h8M8 12h8M8 17h5M4 5a2 2 0 012-2h10l4 4v12a2 2 0 01-2 2H6a2 2 0 01-2-2V5z">
                                </path>
                            </svg>
                            <span>Material Double</span>
                        </a>
                        </div>
                    </div>
                </div>
                <div
                    class="sidebar-section {{ request()->routeIs('admin.master-sto') || request()->routeIs('admin.master-plant') || request()->routeIs('admin.master-material') || request()->routeIs('admin.master-keterangan') || request()->routeIs('admin.users') ? 'is-open has-active' : '' }}">
                    <button type="button" class="sidebar-section-toggle" data-sidebar-toggle title="Master Data">
                        <svg class="sidebar-section-icon" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5h14v14H5zM9 9h6M9 13h6M9 17h3">
                            </path>
                        </svg>
                        <span class="sidebar-section-label">Master Data</span>
                        <svg class="sidebar-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="sidebar-section-items">
                        <div class="sidebar-section-items-inner">
                        <a href="{{ route('admin.master-sto', [], false) }}"
                            class="nav-item {{ request()->routeIs('admin.master-sto') ? 'active' : '' }}"
                            title="Master STO"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                                </path>
                            </svg> <span>Master STO</span></a>
                        <a href="{{ route('admin.master-plant', [], false) }}"
                            class="nav-item {{ request()->routeIs('admin.master-plant') ? 'active' : '' }}"
                            title="Master Plant"><svg fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1v1H9V7zm5 0h1v1h-1V7zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1z">
                                </path>
                            </svg> <span>Master Plant</span></a>
                        <a href="{{ route('admin.master-material', [], false) }}"
                            class="nav-item {{ request()->routeIs('admin.master-material') ? 'active' : '' }}"
                            title="Master Material"><svg fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg> <span>Master Material</span></a>
                        <a href="{{ route('admin.master-keterangan', [], false) }}"
                            class="nav-item {{ request()->routeIs('admin.master-keterangan') ? 'active' : '' }}"
                            title="Master Keterangan"><svg fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z">
                                </path>
                            </svg> <span>Master Keterangan</span></a>
                        <a href="{{ route('admin.users', [], false) }}"
                            class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}"
                            title="User Management"><svg fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg> <span>User Management</span></a>
                        </div>
                    </div>
                </div>
            @else
                <div
                    class="sidebar-section {{ request()->routeIs('scan.setup') || request()->routeIs('scan.scanner') || request()->routeIs('scan.history') ? 'is-open has-active' : '' }}">
                    <button type="button" class="sidebar-section-toggle" data-sidebar-toggle title="Scan Material">
                        <svg class="sidebar-section-icon" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01"></path>
                        </svg>
                        <span class="sidebar-section-label">Scan Material</span>
                        <svg class="sidebar-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="sidebar-section-items">
                        <div class="sidebar-section-items-inner">
                        <a href="{{ route('scan.setup', [], false) }}"
                            class="nav-item {{ request()->routeIs('scan.setup') ? 'active' : '' }}" title="Setup STO"><svg
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                </path>
                            </svg> <span>Setup STO</span></a>
                        <a href="{{ route('scan.scanner', [], false) }}"
                            class="nav-item {{ request()->routeIs('scan.scanner') ? 'active' : '' }}" title="Scanner"><svg
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                </path>
                            </svg> <span>Scanner</span></a>
                        <a href="{{ route('scan.history', [], false) }}"
                            class="nav-item {{ request()->routeIs('scan.history') ? 'active' : '' }}"
                            title="Scan History"><svg fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg> <span>Scan History</span></a>
                        </div>
                    </div>
                </div>
            @endif
        </nav>
    </aside>

    <div id="floating-sidebar-menu" class="floating-menu">
        <div class="floating-menu-header"></div>
        <div class="floating-menu-body"></div>
    </div>

    <main class="main-wrapper">
        <div class="page-content" id="pageContentContainer">
            <div class="loading-overlay" id="globalLoader" style="display: none;">
                <div class="loading-equalizer">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
                <div class="loading-text">Loading</div>
            </div>
            <div class="tab-pane active"
                id="pane-tab-{{ substr(preg_replace('/[^a-zA-Z0-9]/', '', request()->getRequestUri()), -20) }}"
                style="padding: 12px;">
                {{ $slot }}
            </div>

            <div class="tab-pane" id="pane-tab-recent" style="padding: 16px; background: var(--workspace-bg);">
                <div style="background:#fff;border:1px solid var(--border);max-width:960px;margin:0 auto;">
                    <div
                        style="padding:12px 16px;border-bottom:1px solid var(--border-light);font-size:16px;font-weight:600;color:var(--text);">
                        Frequently Used
                    </div>
                    <div id="recentMenuContainer" style="display:none;"></div>
                    <div id="recentMenuGrid"
                        style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;padding:12px;">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="toast-container" id="toastContainer"></div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });

        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                processing: `
                <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px;">
                    <div class="loading-equalizer">
                        <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                    </div>
                    <div class="loading-text" style="color:var(--primary); font-size:13px; font-weight:600;">Loading</div>
                </div>`
            },
            processing: true
        });

        const nativeFetch = window.fetch.bind(window);
        window.fetch = function (resource, options = {}) {
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
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Berhasil!' : 'Perhatian!',
                text: message,
                timer: 1000,
                showConfirmButton: false,
                background: '#ffffff',
                color: '#1f2937'
            });
        }

        function confirmAction(message, callback) {
            Swal.fire({
                title: 'Konfirmasi',
                html: message,
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

        window.addEventListener('click', function (event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('active');
            }
        });

        function toggleSidebar() {
            if (window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.toggle('open');
                const overlay = document.getElementById('sidebarOverlay');
                if (overlay) overlay.classList.toggle('active');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
            }
        }

        // TAB SYSTEM MANAGER
        if (window.self === window.top) {
            const tabManager = {
                init() {
                    this.bindSidebarToggles();
                    this.bindSidebarLinks();
                    this.bindTabEvents();
                    this.renderRecentMenu();
                },

                saveRecentMenu(menu) {
                    try {
                        let recent = JSON.parse(localStorage.getItem('recentMenus')) || [];
                        if (!Array.isArray(recent)) recent = [];
                        recent = recent.filter(m => m.url !== menu.url);
                        recent.unshift(menu);
                        if (recent.length > 20) recent.pop();
                        localStorage.setItem('recentMenus', JSON.stringify(recent));
                    } catch (e) {
                        console.error('Error saving recent menu:', e);
                        localStorage.removeItem('recentMenus');
                    }
                },

                getRecentMenus() {
                    try {
                        let recent = JSON.parse(localStorage.getItem('recentMenus')) || [];
                        return Array.isArray(recent) ? recent : [];
                    } catch (e) {
                        return [];
                    }
                },

                renderRecentMenu() {
                    const recents = this.getRecentMenus();
                    const container = $('#recentMenuContainer');
                    const gridContainer = $('#recentMenuGrid');

                    if (recents.length === 0) {
                        container.html('<div style="padding: 10px 20px; color: var(--text-muted); font-size: 12px;">No recent menus</div>');
                        gridContainer.html('<div style="color: var(--text-muted); font-size: 14px;">Belum ada menu yang diakses.</div>');
                        return;
                    }

                    container.empty();
                    gridContainer.empty();

                    recents.forEach(r => {
                        // For dropdown
                        container.append(`
                            <a href="${r.url}" class="recent-list-item" data-title="${r.title}">
                                ${r.iconSvg}
                                <span>${r.title}</span>
                            </a>
                        `);

                        // For grid
                        gridContainer.append(`
                            <a href="${r.url}" class="recent-list-item-grid" data-title="${r.title}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px 16px; background: var(--surface); border: 1px solid var(--border-light); border-radius: 12px; text-decoration: none; color: var(--text); transition: all 0.2s; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(0,114,206,0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                                    ${r.iconSvg.replace('width="16"', 'width="24"').replace('height="16"', 'height="24"')}
                                </div>
                                <span style="font-weight: 600; font-size: 14px; text-align: center;">${r.title}</span>
                            </a>
                        `);
                    });
                },

                bindSidebarToggles() {
                    $(document).on('click', '[data-sidebar-toggle]', function (e) {
                        if (document.body.classList.contains('sidebar-collapsed')) {
                            e.preventDefault();
                            e.stopPropagation();

                            const $toggle = $(this);
                            const $section = $toggle.closest('.sidebar-section');
                            const $menu = $('#floating-sidebar-menu');

                            if ($section.hasClass('is-floating-open')) {
                                $menu.hide();
                                $section.removeClass('is-floating-open');
                                return false;
                            }

                            const rect = $toggle[0].getBoundingClientRect();
                            const title = $toggle.find('.sidebar-section-label').text();
                            const $items = $toggle.siblings('.sidebar-section-items').find('a.nav-item');

                            $menu.find('.floating-menu-header').text(title);

                            const $body = $menu.find('.floating-menu-body').empty();
                            $items.each(function () {
                                const $a = $(this);
                                const text = $a.find('span').text() || $a.text();
                                const href = $a.attr('href');
                                const iconSvg = $a.find('svg').prop('outerHTML') || '';

                                const newLink = $(`<a href="${href}" class="floating-menu-item" data-title="${text}"></a>`);

                                newLink.html(`
                                    <span class="floating-menu-text">${text}</span>
                                    <svg class="floating-menu-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                                `);

                                newLink.data('original-icon', iconSvg);
                                $body.append(newLink);
                            });

                             const left = rect.right; // Align directly next to the sidebar
                            const top = rect.top;

                            $menu.css({
                                top: top + 'px',
                                left: left + 'px',
                                display: 'block'
                            });

                            $('.sidebar-section').removeClass('is-floating-open');
                            $section.addClass('is-floating-open');

                            return false;
                        }

                        $(this).closest('.sidebar-section').toggleClass('is-open');
                    });

                    // Hide floating menu when clicking outside
                    $(document).on('click', function (e) {
                        if (!$(e.target).closest('#floating-sidebar-menu').length && !$(e.target).closest('[data-sidebar-toggle]').length) {
                            $('#floating-sidebar-menu').hide();
                            $('.sidebar-section').removeClass('is-floating-open');
                        }
                    });
                },

                bindSidebarLinks() {
                    $(document).on('click', '.sidebar a.nav-item', function (e) {
                        const url = tabManager.workspaceUrl($(this).attr('href'));
                        const title = $(this).find('span').text().trim() || $(this).text().trim();
                        const iconSvg = $(this).find('svg').prop('outerHTML') || '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';

                        if (!url || url === '#' || url.startsWith('javascript:')) return;

                        e.preventDefault();

                        let safeIdStr = url.replace(/[^a-zA-Z0-9]/g, '');
                        if (safeIdStr.length > 20) safeIdStr = safeIdStr.substring(safeIdStr.length - 20);
                        const tabId = 'tab-' + safeIdStr;

                        tabManager.saveRecentMenu({ title, url, iconSvg });
                        tabManager.renderRecentMenu();

                        tabManager.openTab(tabId, title, url);

                        $('.sidebar a.nav-item').removeClass('active');
                        $(this).addClass('active');
                        $('.sidebar-section').removeClass('has-active');
                        $(this).closest('.sidebar-section').addClass('is-open has-active');

                        if (window.innerWidth <= 768) {
                            const sidebar = document.getElementById('sidebar');
                            const overlay = document.getElementById('sidebarOverlay');
                            if (sidebar.classList.contains('open')) {
                                sidebar.classList.remove('open');
                                if (overlay) overlay.classList.remove('active');
                            }
                        }
                    });
                },

                bindTabEvents() {
                    $(document).on('click', '.page-tab', function () {
                        const id = $(this).data('id');
                        tabManager.switchTo(id);
                        
                        if (window.innerWidth <= 768) {
                            const sidebar = document.getElementById('sidebar');
                            const overlay = document.getElementById('sidebarOverlay');
                            if (sidebar && sidebar.classList.contains('open')) {
                                sidebar.classList.remove('open');
                                if (overlay) overlay.classList.remove('active');
                            }
                        }
                    });

                    $(document).on('click', '.tab-close', function (e) {
                        e.stopPropagation();
                        const id = $(this).closest('.page-tab').data('id');
                        tabManager.closeTab(id);
                    });

                    $(document).on('click', '.recent-list-item, .recent-list-item-grid', function (e) {
                        e.preventDefault();
                        const url = tabManager.workspaceUrl($(this).attr('href'));
                        const title = $(this).data('title');
                        const iconSvg = $(this).find('svg').prop('outerHTML') || '';

                        let safeIdStr = url.replace(/[^a-zA-Z0-9]/g, '');
                        if (safeIdStr.length > 20) safeIdStr = safeIdStr.substring(safeIdStr.length - 20);
                        const tabId = 'tab-' + safeIdStr;

                        tabManager.saveRecentMenu({ title, url, iconSvg });
                        tabManager.renderRecentMenu();
                        tabManager.openTab(tabId, title, url);

                        $('.sidebar a.nav-item').removeClass('active');
                        const $targetMenu = tabManager.findSidebarMenuByUrl(url);
                        $targetMenu.addClass('active');
                        $('.sidebar-section').removeClass('has-active');
                        $targetMenu.closest('.sidebar-section').addClass('is-open has-active');
                    });

                    $(document).on('click', '.floating-menu-item', function (e) {
                        e.preventDefault();
                        const url = tabManager.workspaceUrl($(this).attr('href'));
                        const title = $(this).data('title');
                        const iconSvg = $(this).data('original-icon');

                        let safeIdStr = url.replace(/[^a-zA-Z0-9]/g, '');
                        if (safeIdStr.length > 20) safeIdStr = safeIdStr.substring(safeIdStr.length - 20);
                        const tabId = 'tab-' + safeIdStr;

                        tabManager.saveRecentMenu({ title, url, iconSvg });
                        tabManager.renderRecentMenu();
                        tabManager.openTab(tabId, title, url);

                        $('.sidebar a.nav-item').removeClass('active');
                        const $targetMenu = tabManager.findSidebarMenuByUrl(url);
                        $targetMenu.addClass('active');
                        $('.sidebar-section').removeClass('has-active');
                        $targetMenu.closest('.sidebar-section').addClass('is-open has-active');

                        $('#floating-sidebar-menu').hide();
                        $('.sidebar-section').removeClass('is-floating-open');
                    });
                },

                openTab(id, title, url) {
                    const workspaceUrl = this.workspaceUrl(url);

                    if ($(`#${id}`).length) {
                        this.switchTo(id);
                        return;
                    }

                    const safeTitle = this.escapeHtml(title);
                    const safeUrl = this.escapeAttribute(workspaceUrl);
                    const tabHtml = `
                        <div class="page-tab" id="${id}" data-id="${id}" data-url="${safeUrl}">
                            <span>${safeTitle}</span>
                            <span class="tab-close" title="Close">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </span>
                        </div>
                    `;
                    $('#pageTabBar').append(tabHtml);

                    $('#globalLoader').show();

                    const paneHtml = `
                        <iframe class="tab-pane" id="pane-${id}" src="${safeUrl}" frameborder="0" allow="camera; microphone; fullscreen" style="width:100%; height:100%;"></iframe>
                    `;
                    $('#pageContentContainer').append(paneHtml);

                    this.switchTo(id);

                    $(`#pane-${id}`).on('load', function () {
                        $('#globalLoader').hide();
                    });
                },

                switchTo(id) {
                    $('.page-tab').removeClass('active');
                    $('.tab-pane').removeClass('active');

                    $(`#${id}`).addClass('active');
                    $(`#pane-${id}`).addClass('active');
                    this.syncSidebarForTab(id);
                    this.syncBrowserUrlForTab(id);
                },

                syncBrowserUrlForTab(id) {
                    if (id === 'tab-recent') {
                        return;
                    }

                    const tabUrl = this.getTabUrl(id);
                    if (!tabUrl) {
                        return;
                    }

                    try {
                        const parsed = new URL(tabUrl, window.location.origin);
                        if (parsed.origin === window.location.origin) {
                            window.history.replaceState({}, '', `${parsed.pathname}${parsed.search}${parsed.hash}`);
                        }
                    } catch (e) {
                        // Ignore invalid or external tab URLs.
                    }
                },

                syncSidebarForTab(id) {
                    $('.sidebar a.nav-item').removeClass('active');
                    $('.sidebar-section').removeClass('has-active');

                    if (id === 'tab-recent') {
                        return;
                    }

                    const tabUrl = this.getTabUrl(id);
                    if (!tabUrl) {
                        return;
                    }

                    const $targetMenu = this.findSidebarMenuByUrl(tabUrl);

                    if (!$targetMenu.length) {
                        return;
                    }

                    $targetMenu.addClass('active');
                    $targetMenu.closest('.sidebar-section').addClass('is-open has-active');
                },

                getTabUrl(id) {
                    const $tab = $(`#${id}`);
                    const tabUrl = $tab.attr('data-url');
                    if (tabUrl) {
                        return tabUrl;
                    }

                    const $pane = $(`#pane-${id}`);
                    if ($pane.is('iframe')) {
                        return $pane.attr('src') || '';
                    }

                    return '';
                },

                workspaceUrl(url) {
                    if (!url) {
                        return '';
                    }

                    try {
                        const parsed = new URL(url, window.location.href);
                        const localHosts = ['localhost', '127.0.0.1', 'adasi_sto.test'];

                        if (parsed.origin === window.location.origin || localHosts.includes(parsed.hostname)) {
                            return `${parsed.pathname}${parsed.search}${parsed.hash}`;
                        }

                        return parsed.href;
                    } catch (e) {
                        return String(url);
                    }
                },

                normalizeUrl(url) {
                    if (!url) {
                        return '';
                    }

                    try {
                        const parsed = new URL(this.workspaceUrl(url), window.location.href);
                        return `${parsed.pathname.replace(/\/$/, '')}${parsed.search}`;
                    } catch (e) {
                        return String(url).replace(/\/$/, '');
                    }
                },

                findSidebarMenuByUrl(url) {
                    const normalizedUrl = this.normalizeUrl(url);

                    return $('.sidebar a.nav-item').filter((_, item) => {
                        return this.normalizeUrl($(item).attr('href')) === normalizedUrl;
                    }).first();
                },

                escapeHtml(value) {
                    return String(value ?? '').replace(/[&<>"']/g, char => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    }[char]));
                },

                escapeAttribute(value) {
                    return this.escapeHtml(value);
                },

                closeTab(id) {
                    const $tab = $(`#${id}`);
                    const isActive = $tab.hasClass('active');

                    $(`#pane-${id}`).remove();
                    $tab.remove();

                    if (isActive) {
                        const $lastTab = $('.page-tab').last();
                        if ($lastTab.length) {
                            this.switchTo($lastTab.data('id'));
                        } else {
                            this.switchTo('tab-recent');
                        }
                    }
                }
            };
            window.tabManager = tabManager;

            window.openWorkspaceTab = function (url, title, iconSvg = '') {
                url = tabManager.workspaceUrl(url);

                if (!url || url === '#' || url.startsWith('javascript:')) {
                    return;
                }

                const defaultIconSvg = '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01"></path></svg>';
                let safeIdStr = url.replace(/[^a-zA-Z0-9]/g, '');
                if (safeIdStr.length > 20) safeIdStr = safeIdStr.substring(safeIdStr.length - 20);
                const tabId = 'tab-' + safeIdStr;

                tabManager.saveRecentMenu({ title, url, iconSvg: iconSvg || defaultIconSvg });
                tabManager.renderRecentMenu();
                tabManager.openTab(tabId, title, url);

                $('.sidebar a.nav-item').removeClass('active');
                const $targetMenu = tabManager.findSidebarMenuByUrl(url);
                $targetMenu.addClass('active');
                $('.sidebar-section').removeClass('has-active');
                $targetMenu.closest('.sidebar-section').addClass('is-open has-active');

                if (window.innerWidth <= 768) {
                    document.getElementById('sidebar')?.classList.remove('open');
                }
            };

            $(document).ready(function () {
                tabManager.init();

                $('#logoutForm').on('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi',
                        text: 'Apakah Anda yakin ingin keluar dari aplikasi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#2b2d30',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Keluar',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });
        }
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
