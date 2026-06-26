<x-layouts.app :title="'Admin Dashboard'">
@push('head-scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
@endpush

@push('styles')
<style>
    /* Kerangka Utama */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .dashboard-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--text);
    }
    .dashboard-actions {
        display: flex;
        gap: 10px;
    }
    
    /* Pill Navigation */
    .pill-nav {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 10px;
        margin-bottom: 24px;
        scrollbar-width: none; /* Firefox */
    }
    .pill-nav::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }
    .pill {
        padding: 8px 24px;
        border-radius: 50px;
        background: var(--bg-color);
        border: 1px solid var(--border);
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 14px;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .pill.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(0, 114, 206, 0.2);
    }
    .pill:hover:not(.active) {
        background: var(--border-light);
    }
    
    /* Grid Layout */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 2.1fr 1fr;
        gap: 24px;
        align-items: start;
    }
    @media(max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* Left Column */
    .left-col {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    .activity-charts {
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 24px;
    }
    @media(max-width: 1200px) {
        .activity-charts {
            grid-template-columns: 1fr;
        }
    }
    
    /* Right Column */
    .right-col {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    
    /* 2x2 Grid untuk Overview */
    .overview-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .stat-card {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .stat-card-title {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 8px;
        font-weight: 500;
    }
    .stat-card-value {
        font-size: 26px;
        font-weight: 700;
        color: var(--text);
    }
    .stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
        font-size: 18px;
    }
    
    /* Donut Chart Card */
    .usage-card {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        align-items: center;
    }
    .usage-chart {
        width: 150px;
        height: 150px;
        position: relative;
    }
    .usage-stats {
        flex: 1;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .usage-stat-item {
        display: flex;
        flex-direction: column;
    }
    .usage-stat-value {
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
    }
    .usage-stat-label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
        margin-top: 2px;
    }
    
    .chart-container {
        position: relative;
        height: 200px;
        width: 100%;
    }

    .scrollable-wrapper {
        height: 250px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 10px;
        margin-bottom: 10px;
    }
    
    /* Scrollbar styling untuk wrapper */
    .scrollable-wrapper::-webkit-scrollbar {
        width: 6px;
    }
    .scrollable-wrapper::-webkit-scrollbar-track {
        background: var(--bg-color);
        border-radius: 10px;
    }
    .scrollable-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .scrollable-wrapper::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        .dashboard-title {
            font-size: 20px;
        }
        .dashboard-actions {
            width: 100%;
        }
        .dashboard-actions .btn {
            flex: 1;
        }
        .overview-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .stat-card {
            padding: 14px;
        }
        .stat-card-value {
            font-size: 22px;
        }
        .usage-card {
            flex-direction: column;
            align-items: center;
        }
        .usage-chart {
            width: 120px;
            height: 120px;
        }
        .usage-stats {
            width: 100%;
        }
        .chart-container {
            height: 180px;
        }
    }
</style>
@endpush

<div class="dashboard-header">
    <div class="dashboard-title">Dashboard Overview <span style="color:var(--text-muted);font-weight:500;font-size:22px;margin-left:5px;">({{ count($scanPerPlant) }})</span></div>
    <div class="dashboard-actions">
        <button class="btn" style="border-radius:50px; background:var(--bg-color); border:1px solid var(--border); color:var(--text);" onclick="window.location.reload()"><i class='bx bx-refresh'></i> Refresh</button>
        <button class="btn" style="border-radius:50px; background:var(--bg-color); border:1px solid var(--border); color:var(--text);" onclick="document.getElementById('filterModal').classList.add('active')"><i class='bx bx-filter'></i> Filter by</button>
    </div>
</div>

<div class="pill-nav">
    <a href="{{ route('admin.dashboard', collect(request()->query())->except('plant_id')->toArray()) }}" class="pill {{ !request('plant_id') ? 'active' : '' }}" style="text-decoration:none;">All Plants</a>
    @foreach($scanPerPlant as $plant)
        @php $pId = is_array($plant) ? $plant['id'] : $plant->plant_id; @endphp
        <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['plant_id' => $pId])) }}" class="pill {{ request('plant_id') == $pId ? 'active' : '' }}" style="text-decoration:none;">{{ is_array($plant) ? $plant['name'] : $plant->name }}</a>
    @endforeach
</div>

<div class="dashboard-grid">
    <!-- Left Column -->
    <div class="left-col">
        <div class="card" style="padding: 24px; border-radius: 16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
                <div class="card-title" style="margin:0; font-size: 18px;"><i class='bx bx-line-chart' style="margin-right: 8px; color: var(--primary);"></i> Activity Overview</div>
                <div style="display:flex; gap:10px;">
                    <button class="btn btn-sm" style="background:var(--bg-color); border:1px solid var(--border); color:var(--text); border-radius:8px;" onclick="window.location.href='{{ route('admin.scan-results') }}'"><i class='bx bx-list-ul'></i> View History</button>
                </div>
            </div>
            
            <div class="activity-charts">
                <div>
                    <div style="font-size:14px; font-weight:600; color:var(--text); margin-bottom:12px;">Scan per Day</div>
                    <div class="chart-container">
                        <canvas id="chartDay"></canvas>
                    </div>
                </div>
                <div>
                    <div style="font-size:14px; font-weight:600; color:var(--text); margin-bottom:12px;">Scan per User</div>
                    <div class="scrollable-wrapper">
                        <div class="chart-container" id="chartUserContainer">
                            <canvas id="chartUser"></canvas>
                        </div>
                    </div>
                </div>
                <div>
                    <div style="font-size:14px; font-weight:600; color:var(--text); margin-bottom:12px;">Validation by Validator</div>
                    <div class="scrollable-wrapper">
                        <div class="chart-container" id="chartValidatorContainer">
                            <canvas id="chartValidator"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="border-radius: 16px;">
            <div class="card-title" style="font-size: 18px;"><i class='bx bx-list-ul' style="margin-right: 8px; color: var(--text);"></i> Latest Scan</div>
            <div class="table-container">
                <table class="table-enterprise" id="dashboardDataTable" style="width: 100%;">
                    <thead>
                        <tr><th>Barcode</th><th>Material</th><th>User</th><th>Plant</th><th>Location</th><th>STO</th><th>Time</th></tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Right Column -->
    <div class="right-col">
        <div class="card" style="padding: 24px; border-radius: 16px;">
            <div class="card-title" style="margin-bottom: 24px; font-size: 18px;"><i class='bx bx-buildings' style="margin-right: 8px; color: var(--warning);"></i> Plant Usage</div>
            <div class="usage-card">
                <div class="usage-chart">
                    <canvas id="chartPlant"></canvas>
                    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); text-align:center;">
                        <div style="font-size:24px; font-weight:700; color:var(--text);">{{ count($scanPerPlant) }}</div>
                        <div style="font-size:11px; color:var(--text-muted); font-weight:500;">Plants Used</div>
                    </div>
                </div>
                <div class="usage-stats">
                    <div class="usage-stat-item">
                        <span class="usage-stat-value">{{ number_format($totalScanMonth) }}</span>
                        <span class="usage-stat-label">Total Month</span>
                    </div>
                    <div class="usage-stat-item">
                        <span class="usage-stat-value">{{ number_format($totalScanToday) }}</span>
                        <span class="usage-stat-label">Total Today</span>
                    </div>
                    <div class="usage-stat-item">
                        <span class="usage-stat-value">{{ number_format($totalValid) }}</span>
                        <span class="usage-stat-label">Valid Scans</span>
                    </div>
                    <div class="usage-stat-item">
                        <span class="usage-stat-value">{{ count($scanPerUser) }}</span>
                        <span class="usage-stat-label">Active Users</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="background: transparent; box-shadow: none; padding: 0;">
            <div class="card-title" style="margin-bottom: 16px; padding-left: 4px; font-size: 18px;"><i class='bx bx-scan' style="margin-right: 6px; color: var(--primary);"></i> Scan Overview</div>
            <div class="overview-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(0, 114, 206, 0.1); color: var(--primary);">
                        <i class='bx bx-scan'></i>
                    </div>
                    <div class="stat-card-title">Total Today</div>
                    <div class="stat-card-value">{{ number_format($totalScanToday) }}</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(34, 160, 107, 0.1); color: var(--success);">
                        <i class='bx bx-check-circle'></i>
                    </div>
                    <div class="stat-card-title">Valid Scans</div>
                    <div class="stat-card-value">{{ number_format($totalValid) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(229, 161, 0, 0.1); color: var(--warning);">
                        <i class='bx bx-copy'></i>
                    </div>
                    <div class="stat-card-title">Duplicate Scans</div>
                    <div class="stat-card-value">{{ number_format($totalDuplicate) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(217, 45, 32, 0.1); color: var(--danger);">
                        <i class='bx bx-x-circle'></i>
                    </div>
                    <div class="stat-card-title">Invalid Scans</div>
                    <div class="stat-card-value">{{ number_format($totalInvalid) }}</div>
                </div>
            </div>
        </div>

        <div class="card" style="background: transparent; box-shadow: none; padding: 0;">
            <div class="card-title" style="margin-bottom: 16px; padding-left: 4px; font-size: 18px;"><i class='bx bx-check-double' style="margin-right: 6px; color: var(--success);"></i> Validator Overview</div>
            <div class="overview-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(0, 114, 206, 0.1); color: var(--primary);">
                        <i class='bx bx-barcode'></i>
                    </div>
                    <div class="stat-card-title">Total Barcode</div>
                    <div class="stat-card-value">{{ number_format($validatorOverview['total_barcode']) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(34, 160, 107, 0.1); color: var(--success);">
                        <i class='bx bx-check-shield'></i>
                    </div>
                    <div class="stat-card-title">Valid</div>
                    <div class="stat-card-value">{{ number_format($validatorOverview['valid']) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(229, 161, 0, 0.1); color: var(--warning);">
                        <i class='bx bx-error-circle'></i>
                    </div>
                    <div class="stat-card-title">Need Check</div>
                    <div class="stat-card-value">{{ number_format($validatorOverview['need_check']) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Filter -->
<div id="filterModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Filter Dashboard</h3>
            <button type="button" class="btn-close" onclick="document.getElementById('filterModal').classList.remove('active')">&times;</button>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}">
            @if(request('plant_id'))
                <input type="hidden" name="plant_id" value="{{ request('plant_id') }}">
            @endif
            <div class="modal-body">
                <div class="form-group">
                    <label>Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('filterModal').classList.remove('active')">Batal</button>
                <a href="{{ route('admin.dashboard', collect(request()->query())->except(['date_from', 'date_to'])->toArray()) }}" class="btn btn-outline" style="color:var(--danger);border-color:var(--danger);">Reset</a>
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        $('#dashboardDataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.api.dashboard.latest-scan") }}',
                type: 'GET',
                data: function(d) {
                    if (urlParams.has('plant_id')) d.plant_id = urlParams.get('plant_id');
                    if (urlParams.has('date_from')) d.date_from = urlParams.get('date_from');
                    if (urlParams.has('date_to')) d.date_to = urlParams.get('date_to');
                }
            },
            columns: [
                { data: 'barcode_material', render: function(data) { return '<span class="mono" style="color:var(--primary); font-weight:600;">'+(data||'')+'</span>'; } },
                { data: 'material_name', render: function(data) { return data||'-'; } },
                { data: 'user', render: function(data) { return data||'-'; } },
                { data: 'plant', render: function(data) { return data||'-'; } },
                { data: 'location_name', render: function(data) { return data||'-'; } },
                { data: 'sto_code', render: function(data) { return '<span class="mono">'+(data||'-')+'</span>'; } },
                { data: 'created_at', render: function(data) { 
                    if (!data) return '-';
                    return '<span class="mono" style="color:var(--text-muted);">'+data.substring(0, 16)+'</span>';
                } }
            ],
            ordering: false,
            pageLength: 50,
            lengthChange: false,
            language: {
                search: "",
                searchPlaceholder: "Search latest scans...",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            },
            dom: '<"top"f>rt<"bottom"ip><"clear">',
        });
    });

    Chart.defaults.font.family = "'Inter', 'Segoe UI', Arial, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#64748b';
    const colors = ['#eec226', '#e5e7eb', '#cbd5e1', '#94a3b8', '#64748b']; // Adapting IronNest palette for donut if needed, or stick to STO primary
    const stoColors = ['#0072ce', '#eab308', '#22c55e', '#ef4444', '#8b5cf6', '#06b6d4'];
    
    const scanPerUser = @json($scanPerUser);
    const scanPerPlant = @json($scanPerPlant);
    const scanPerDay = @json($scanPerDay);
    const validationByScanner = @json($validationByScanner);

    // Calculate dynamic height for user charts (min 200px or 35px per user)
    const chartUserHeight = Math.max(200, scanPerUser.length * 35);
    document.getElementById('chartUserContainer').style.height = chartUserHeight + 'px';

    new Chart(document.getElementById('chartUser'), {
        type: 'bar',
        data: { labels: scanPerUser.map(item => item.name), datasets: [{ data: scanPerUser.map(item => item.total), backgroundColor: stoColors[0], borderRadius: 6 }] },
        options: { 
            indexAxis: 'y', 
            plugins: { legend: { display: false } }, 
            scales: { 
                x: { beginAtZero: true, grid: { display: false } },
                y: { grid: { display: false } }
            },
            maintainAspectRatio: false
        }
    });

    if (document.getElementById('chartValidator')) {
        const chartValidatorHeight = Math.max(200, validationByScanner.length * 35);
        document.getElementById('chartValidatorContainer').style.height = chartValidatorHeight + 'px';

        new Chart(document.getElementById('chartValidator'), {
            type: 'bar',
            data: { labels: validationByScanner.map(item => item.name), datasets: [{ data: validationByScanner.map(item => item.total), backgroundColor: stoColors[2], borderRadius: 6 }] },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { display: false } },
                    y: { grid: { display: false } }
                },
                maintainAspectRatio: false
            }
        });
    }

    new Chart(document.getElementById('chartPlant'), {
        type: 'doughnut',
        data: { labels: scanPerPlant.map(item => item.name), datasets: [{ data: scanPerPlant.map(item => item.total), backgroundColor: stoColors, borderWidth: 0 }] },
        options: { 
            plugins: { legend: { display: false } },
            cutout: '80%',
            maintainAspectRatio: false
        }
    });

    new Chart(document.getElementById('chartDay'), {
        type: 'line',
        data: { labels: scanPerDay.map(item => item.date), datasets: [{ data: scanPerDay.map(item => item.total), borderColor: '#0072ce', backgroundColor: 'rgba(0,114,206,0.05)', fill: true, tension: 0.4, borderWidth: 3, pointRadius: 2, pointBackgroundColor: '#0072ce' }] },
        options: { 
            plugins: { legend: { display: false } }, 
            scales: { 
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { borderDash: [4, 4], color: '#e2e8f0' } },
                x: { grid: { display: false } }
            },
            maintainAspectRatio: false
        }
    });
</script>
@endpush

</x-layouts.app>
