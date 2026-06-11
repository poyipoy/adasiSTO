<x-layouts.app :title="'Admin Dashboard'">

<style>
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-top: 3px solid var(--primary);
        padding: 12px;
        display: flex;
        flex-direction: column;
    }
    .stat-card:nth-child(2) { border-top-color: var(--success); }
    .stat-card:nth-child(3) { border-top-color: var(--warning); }
    .stat-card:nth-child(4) { border-top-color: var(--danger); }

    .stat-label {
        font-size: 11px;
        color: var(--text-secondary);
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.3px;
        margin-bottom: 6px;
    }
    .stat-value {
        font-size: 26px;
        font-weight: 700;
        color: var(--text);
        line-height: 1.2;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }
    .quick-links {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
    }
    .quick-link-card {
        background: var(--surface);
        border: 1px solid var(--border);
        padding: 12px;
        text-align: center;
        text-decoration: none;
        color: var(--text);
        transition: all 0.15s;
    }
    .quick-link-card:hover {
        background: var(--row-hover);
        border-color: var(--primary);
        text-decoration: none;
    }
    .quick-link-icon {
        font-size: 20px;
        margin-bottom: 4px;
    }
    .quick-link-title {
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .quick-link-desc {
        font-size: 10px;
        color: var(--text-muted);
        margin-top: 4px;
    }
</style>

{{-- Stats Grid --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Scan Keseluruhan</div>
        <div class="stat-value" data-count="{{ $totalScanAll }}">{{ $totalScanAll }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Scan Hari Ini</div>
        <div class="stat-value" data-count="{{ $totalScanToday }}">{{ $totalScanToday }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">User Aktif</div>
        <div class="stat-value" data-count="{{ $totalUsers }}">{{ $totalUsers }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Plant</div>
        <div class="stat-value" data-count="{{ $totalPlants }}">{{ $totalPlants }}</div>
    </div>
</div>

{{-- Charts --}}
<div class="chart-grid">
    {{-- Scan Per User --}}
    <div class="card">
        <div class="card-title">Scan Per User</div>
        <canvas id="chartPerUser" height="180"></canvas>
    </div>

    {{-- Scan Per Plant --}}
    <div class="card">
        <div class="card-title">Scan Per Plant</div>
        <canvas id="chartPerPlant" height="180"></canvas>
    </div>
</div>

{{-- Trend Chart --}}
<div class="card" style="margin-bottom: 12px;">
    <div class="card-title">Trend Scan 7 Hari Terakhir</div>
    <canvas id="chartTrend" height="80"></canvas>
</div>

{{-- Quick Links --}}
<div class="quick-links">
    <a href="{{ route('admin.scan-results') }}" class="quick-link-card">
        <div class="quick-link-icon">📋</div>
        <div class="quick-link-title">Monitoring Scan</div>
        <div class="quick-link-desc">Lihat semua data scan</div>
    </a>
    <a href="{{ route('admin.barcode-overview') }}" class="quick-link-card">
        <div class="quick-link-icon">🔍</div>
        <div class="quick-link-title">Overview Barcode</div>
        <div class="quick-link-desc">Grouping barcode sama</div>
    </a>
    <a href="{{ route('admin.scan-results.export') }}" class="quick-link-card">
        <div class="quick-link-icon">📥</div>
        <div class="quick-link-title">Export Excel</div>
        <div class="quick-link-desc">Download semua data</div>
    </a>
    <a href="{{ route('admin.master.materials') }}" class="quick-link-card">
        <div class="quick-link-icon">⚙️</div>
        <div class="quick-link-title">Master Data</div>
        <div class="quick-link-desc">Kelola data referensi</div>
    </a>
</div>

@push('scripts')
<script>
    const chartColors = {
        primary: '#0072ce',
        success: '#22a06b',
        warning: '#e5a100',
        danger: '#d92d20',
        info: '#0d47a1',
        purple: '#673ab7',
    };

    Chart.defaults.color = '#808b99';
    Chart.defaults.borderColor = '#e0e3e8';
    Chart.defaults.font.family = "'Inter', -apple-system, sans-serif";
    Chart.defaults.font.size = 10;

    // Scan Per User (Horizontal Bar)
    const scanPerUser = @json($scanPerUser);
    new Chart(document.getElementById('chartPerUser'), {
        type: 'bar',
        data: {
            labels: scanPerUser.map(d => d.name),
            datasets: [{
                data: scanPerUser.map(d => d.total),
                backgroundColor: [chartColors.primary, chartColors.purple, chartColors.success, chartColors.warning, chartColors.danger, chartColors.info],
                borderRadius: 2,
                barThickness: 16,
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { display: false } }
            }
        }
    });

    // Scan Per Plant (Doughnut)
    const scanPerPlant = @json($scanPerPlant);
    new Chart(document.getElementById('chartPerPlant'), {
        type: 'doughnut',
        data: {
            labels: scanPerPlant.map(d => d.name),
            datasets: [{
                data: scanPerPlant.map(d => d.total),
                backgroundColor: [chartColors.primary, chartColors.success, chartColors.warning],
                borderWidth: 0,
                cutout: '70%',
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle' } }
            }
        }
    });

    // Trend (Line)
    const scanTrend = @json($scanTrend);
    new Chart(document.getElementById('chartTrend'), {
        type: 'line',
        data: {
            labels: scanTrend.map(d => d.date),
            datasets: [{
                data: scanTrend.map(d => d.total),
                borderColor: chartColors.primary,
                backgroundColor: 'rgba(0, 114, 206, 0.05)',
                fill: true,
                tension: 0, // ERPs usually have straight lines, not curved
                pointBackgroundColor: chartColors.primary,
                pointRadius: 3,
                pointHoverRadius: 5,
                borderWidth: 2
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
</script>
@endpush

</x-layouts.app>
