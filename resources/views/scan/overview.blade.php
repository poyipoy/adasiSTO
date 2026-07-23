<x-layouts.app :title="'Overview'">
    @push('head-scripts')
        <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    @endpush

    @push('styles')
        <style>
            .overview-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
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

            .dashboard-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 24px;
                margin-top: 24px;
            }

            .card {
                background: #fff;
                border: 1px solid var(--border);
                border-radius: 16px;
                padding: 24px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            }

            .card-title {
                font-size: 18px;
                font-weight: 700;
                color: var(--text);
                margin-bottom: 24px;
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

            @media (max-width: 900px) {
                .overview-grid {
                    grid-template-columns: 1fr 1fr;
                }

                .stat-card-location {
                    grid-column: 1 / -1;
                }

                .dashboard-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 560px) {
                .overview-grid {
                    grid-template-columns: 1fr 1fr;
                }

                .stat-card {
                    padding: 14px;
                }

                .stat-card-value {
                    font-size: 22px;
                }

                .card {
                    padding: 16px;
                }

                .chart-container {
                    height: 180px;
                }
            }
        </style>
    @endpush

    <div class="enterprise-toolbar" style="margin-bottom: 16px; display: flex; justify-content: flex-end;">
        <button class="btn"
            style="border-radius:50px; background:var(--bg-color); border:1px solid var(--border); color:var(--text);"
            type="button" onclick="refreshScannerOverview()">
            <i class='bx bx-refresh'></i> Refresh
        </button>
    </div>

    <div class="overview-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(0, 114, 206, 0.1); color: var(--primary);">
                <i class='bx bx-scan'></i>
            </div>
            <div class="stat-card-title">Total Today</div>
            <div class="stat-card-value" data-card="total_today">{{ number_format($scanOverview['total_today']) }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(34, 160, 107, 0.1); color: var(--success);">
                <i class='bx bx-check-circle'></i>
            </div>
            <div class="stat-card-title">Valid Scans</div>
            <div class="stat-card-value" data-card="valid_scans">{{ number_format($scanOverview['valid_scans']) }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(229, 161, 0, 0.1); color: var(--warning);">
                <i class='bx bx-copy'></i>
            </div>
            <div class="stat-card-title">Duplicate Scans</div>
            <div class="stat-card-value" data-card="duplicate_scans">
                {{ number_format($scanOverview['duplicate_scans']) }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(217, 45, 32, 0.1); color: var(--danger);">
                <i class='bx bx-x-circle'></i>
            </div>
            <div class="stat-card-title">Invalid Scans</div>
            <div class="stat-card-value" data-card="invalid_scans">{{ number_format($scanOverview['invalid_scans']) }}
            </div>
        </div>

        <div class="stat-card stat-card-location">
            <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                <i class='bx bx-current-location'></i>
            </div>
            <div class="stat-card-title">Lokasi Terkonfirmasi</div>
            <div class="stat-card-value">
                <span
                    data-card="locations_confirmed">{{ number_format($scanOverview['locations_confirmed'] ?? 0) }}</span>
                <span style="font-size: 18px; font-weight: 500; color: var(--text-muted);">/ <span
                        data-card="locations_total">{{ number_format($scanOverview['locations_total'] ?? 0) }}</span></span>
            </div>
            @php
                $conf = $scanOverview['locations_confirmed'] ?? 0;
                $tot = $scanOverview['locations_total'] ?? 0;
                $locPercentage = $tot > 0 ? round(($conf / $tot) * 100, 1) : 0;
                if ($locPercentage < 50) {
                    $locColorClass = 'bg-danger';
                    $locTextColorClass = 'text-danger';
                } elseif ($locPercentage < 100) {
                    $locColorClass = 'bg-warning';
                    $locTextColorClass = 'text-warning';
                } else {
                    $locColorClass = 'bg-success';
                    $locTextColorClass = 'text-success';
                }
            @endphp
            <div
                style="margin-top: 12px; display: flex; align-items: center; justify-content: space-between; font-size: 13px; font-weight: 600;">
                <div class="progress"
                    style="height: 6px; flex-grow: 1; margin-right: 12px; background-color: var(--border-color); border-radius: 4px;">
                    <div class="progress-bar {{ $locColorClass }}" id="locationsProgressBar" role="progressbar"
                        style="width: {{ $locPercentage }}%; border-radius: 4px;" aria-valuenow="{{ $locPercentage }}"
                        aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <span class="{{ $locTextColorClass }}" id="locationsProgressText">{{ $locPercentage }}%</span>
            </div>
        </div>
    </div>

    @if($validatorOverview)
        <div style="margin-top:24px; margin-bottom: 16px; font-size: 18px; font-weight: 700; color: var(--text);"><i
                class='bx bx-check-double' style="margin-right: 6px; color: var(--primary);"></i> Validator Overview</div>
        <div class="overview-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(0, 114, 206, 0.1); color: var(--primary);">
                    <i class='bx bx-barcode'></i>
                </div>
                <div class="stat-card-title">Total Barcode</div>
                <div class="stat-card-value" data-validator-card="total_barcode">
                    {{ number_format($validatorOverview['total_barcode']) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(34, 160, 107, 0.1); color: var(--success);">
                    <i class='bx bx-check-shield'></i>
                </div>
                <div class="stat-card-title">Valid</div>
                <div class="stat-card-value" data-validator-card="valid">{{ number_format($validatorOverview['valid']) }}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(229, 161, 0, 0.1); color: var(--warning);">
                    <i class='bx bx-error-circle'></i>
                </div>
                <div class="stat-card-title">Need Check</div>
                <div class="stat-card-value" data-validator-card="need_check">
                    {{ number_format($validatorOverview['need_check']) }}</div>
            </div>
        </div>
    @endif

    <div class="dashboard-grid">
        <div class="card">
            <div class="card-title"><i class='bx bx-calendar' style="margin-right: 8px; color: var(--primary);"></i>
                Scan per Day</div>
            <div class="chart-container">
                <canvas id="scanDayChart"></canvas>
            </div>
        </div>

        @if($validatorOverview)
            <div class="card">
                <div class="card-title"><i class='bx bx-check-shield' style="margin-right: 8px; color: var(--success);"></i>
                    Validation by Validator</div>
                <div class="scrollable-wrapper">
                    <div class="chart-container" id="validatorUserChartContainer">
                        <canvas id="validatorUserChart"></canvas>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            Chart.defaults.font.family = "'Inter', 'Segoe UI', Arial, sans-serif";
            Chart.defaults.font.size = 11;
            Chart.defaults.color = '#64748b';

            const stoColors = ['#0072ce', '#eab308', '#22c55e', '#ef4444', '#8b5cf6', '#06b6d4'];
            let dayChart;
            let validatorChart;

            function formatNumber(value) {
                return new Intl.NumberFormat('id-ID').format(Number(value || 0));
            }

            function buildBarChart(canvasId, labels, values, indexAxis = 'y', colorIndex = 0) {
                const element = document.getElementById(canvasId);
                if (!element) return null;

                return new Chart(element, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{ data: values, backgroundColor: stoColors[colorIndex], borderRadius: 6 }]
                    },
                    options: {
                        indexAxis,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { display: false } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            }

            function buildLineChart(canvasId, labels, values) {
                const element = document.getElementById(canvasId);
                if (!element) return null;

                return new Chart(element, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            borderColor: '#0072ce',
                            backgroundColor: 'rgba(0,114,206,0.05)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 2,
                            pointBackgroundColor: '#0072ce'
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { borderDash: [4, 4], color: '#e2e8f0' } },
                            x: { grid: { display: false } }
                        },
                        maintainAspectRatio: false
                    }
                });
            }

            function updateChart(chart, rows) {
                if (!chart) return;
                chart.data.labels = rows.map(item => item.name || item.date);
                chart.data.datasets[0].data = rows.map(item => item.total);
                chart.update();
            }

            function renderOverview(payload) {
                const scan = payload.scan_overview || {};
                Object.keys(scan).forEach(key => {
                    const target = document.querySelector(`[data-card="${key}"]`);
                    if (target) target.textContent = formatNumber(scan[key]);
                });

                // Update locations progress bar
                const confirmed = Number(scan['locations_confirmed'] || 0);
                const total = Number(scan['locations_total'] || 0);
                const percentage = total > 0 ? (confirmed / total * 100).toFixed(1) : 0;

                const progressBar = document.getElementById('locationsProgressBar');
                const progressText = document.getElementById('locationsProgressText');

                if (progressBar && progressText) {
                    progressBar.style.width = `${percentage}%`;
                    progressBar.setAttribute('aria-valuenow', percentage);
                    progressText.textContent = `${percentage}%`;

                    progressBar.classList.remove('bg-danger', 'bg-warning', 'bg-success');
                    progressText.classList.remove('text-danger', 'text-warning', 'text-success');

                    if (percentage < 50) {
                        progressBar.classList.add('bg-danger');
                        progressText.classList.add('text-danger');
                    } else if (percentage < 100) {
                        progressBar.classList.add('bg-warning');
                        progressText.classList.add('text-warning');
                    } else {
                        progressBar.classList.add('bg-success');
                        progressText.classList.add('text-success');
                    }
                }

                const validator = payload.validator_overview || {};
                Object.keys(validator).forEach(key => {
                    const target = document.querySelector(`[data-validator-card="${key}"]`);
                    if (target) target.textContent = formatNumber(validator[key]);
                });

                updateChart(dayChart, payload.scan_per_day || []);

                const valOverview = payload.validation_by_scanner || [];
                if (validatorChart && valOverview.length) {
                    document.getElementById('validatorUserChartContainer').style.height = Math.max(200, valOverview.length * 35) + 'px';
                    updateChart(validatorChart, valOverview);
                }
            }

            let isRefreshing = false;
            function refreshScannerOverview() {
                if (isRefreshing) return;
                isRefreshing = true;

                const btn = document.querySelector('.enterprise-toolbar button');
                const icon = btn ? btn.querySelector('.bx-refresh') : null;
                if (icon) icon.classList.add('bx-spin');
                if (btn) btn.disabled = true;

                // Tampilkan global loader (bar biru bergerak) milik parent window/workspace jika ada
                const parentLoader = window.top && window.top.document ? window.top.document.getElementById('globalLoader') : null;
                if (parentLoader) {
                    parentLoader.style.display = 'flex';
                }

                fetch('{{ route("api.scan.overview") }}', { headers: { Accept: 'application/json' } })
                    .then(async response => {
                        const payload = await response.json();
                        if (!response.ok) throw payload;
                        return payload;
                    })
                    .then(payload => {
                        renderOverview(payload.data || {});
                    })
                    .catch(error => {
                        if (typeof showToast === 'function') {
                            showToast(error.message || 'Gagal memuat overview.', 'error');
                        } else {
                            console.error('Error refreshing overview:', error);
                        }
                    })
                    .finally(() => {
                        isRefreshing = false;
                        if (icon) icon.classList.remove('bx-spin');
                        if (btn) btn.disabled = false;

                        // Sembunyikan kembali global loader
                        if (parentLoader) {
                            parentLoader.style.display = 'none';
                        }
                    });
            }

            document.addEventListener('DOMContentLoaded', () => {
                const dayData = @json($scanPerDay);
                const valData = @json($validationByScanner);

                dayChart = buildLineChart(
                    'scanDayChart',
                    dayData.map(d => d.date),
                    dayData.map(d => d.total)
                );

                if (document.getElementById('validatorUserChartContainer')) {
                    document.getElementById('validatorUserChartContainer').style.height = Math.max(200, valData.length * 35) + 'px';
                    validatorChart = buildBarChart(
                        'validatorUserChart',
                        valData.map(d => d.name),
                        valData.map(d => d.total),
                        'y',
                        2 // matches stoColors[2] for green
                    );
                }

                setInterval(refreshScannerOverview, 30000);
            });
        </script>
    @endpush

</x-layouts.app>