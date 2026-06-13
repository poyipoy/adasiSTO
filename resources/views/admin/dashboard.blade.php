<x-layouts.app :title="'Admin Dashboard'">

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-bottom:12px;">
    <div class="card" style="border-top:3px solid var(--primary);"><div class="card-title">Total Scan Today</div><div style="font-size:26px;font-weight:700;">{{ $totalScanToday }}</div></div>
    <div class="card" style="border-top:3px solid var(--success);"><div class="card-title">Total Scan Month</div><div style="font-size:26px;font-weight:700;">{{ $totalScanMonth }}</div></div>
    <div class="card" style="border-top:3px solid var(--success);"><div class="card-title">Total Valid</div><div style="font-size:26px;font-weight:700;">{{ $totalValid }}</div></div>
    <div class="card" style="border-top:3px solid var(--warning);"><div class="card-title">Total Duplicate</div><div style="font-size:26px;font-weight:700;">{{ $totalDuplicate }}</div></div>
    <div class="card" style="border-top:3px solid var(--danger);"><div class="card-title">Total Invalid</div><div style="font-size:26px;font-weight:700;">{{ $totalInvalid }}</div></div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:12px;margin-bottom:12px;">
    <div class="card"><div class="card-title">Scan per User</div><canvas id="chartUser" height="150"></canvas></div>
    <div class="card"><div class="card-title">Scan per Plant</div><canvas id="chartPlant" height="150"></canvas></div>
</div>

<div class="card" style="margin-bottom:12px;">
    <div class="card-title">Scan per Day</div>
    <canvas id="chartDay" height="70"></canvas>
</div>

<div class="card">
    <div class="card-title">Latest Scan</div>
    <div class="table-container">
        <table class="table-enterprise">
            <thead>
                <tr><th>Barcode</th><th>Material</th><th>User</th><th>Plant</th><th>Location/Rack</th><th>STO</th><th>Time</th></tr>
            </thead>
            <tbody>
                @forelse($latestScan as $scan)
                    <tr>
                        <td class="mono">{{ $scan->barcode_material }}</td>
                        <td>{{ $scan->material_name }}</td>
                        <td>{{ $scan->user->name ?? '-' }}</td>
                        <td>{{ $scan->plant->name ?? '-' }}</td>
                        <td>{{ $scan->location->name ?? '-' }}</td>
                        <td class="mono">{{ $scan->sto_code }}</td>
                        <td class="mono">{{ $scan->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);">Tidak ada data ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($latestScan->hasPages() || $latestScan->total() > 0)
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding-top:10px;border-top:1px solid var(--border-light);font-size:12px;color:var(--text-secondary);">
            <div>
                Showing {{ $latestScan->firstItem() ?? 0 }} to {{ $latestScan->lastItem() ?? 0 }} of {{ $latestScan->total() }} entries
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                @if($latestScan->onFirstPage())
                    <button class="btn" type="button" disabled>Previous</button>
                @else
                    <a class="btn" href="{{ $latestScan->previousPageUrl() }}">Previous</a>
                @endif

                <span class="mono" style="padding:5px 10px;border:1px solid var(--border);background:#fff;color:var(--text);">
                    Page {{ $latestScan->currentPage() }} / {{ max($latestScan->lastPage(), 1) }}
                </span>

                @if($latestScan->hasMorePages())
                    <a class="btn" href="{{ $latestScan->nextPageUrl() }}">Next</a>
                @else
                    <button class="btn" type="button" disabled>Next</button>
                @endif
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    Chart.defaults.font.family = "'Inter', 'Segoe UI', Arial, sans-serif";
    Chart.defaults.font.size = 10;
    Chart.defaults.color = '#525e6c';
    const colors = ['#0072ce', '#22a06b', '#e5a100', '#d92d20', '#4b5563', '#005fa8'];
    const scanPerUser = @json($scanPerUser);
    const scanPerPlant = @json($scanPerPlant);
    const scanPerDay = @json($scanPerDay);

    new Chart(document.getElementById('chartUser'), {
        type: 'bar',
        data: { labels: scanPerUser.map(item => item.name), datasets: [{ data: scanPerUser.map(item => item.total), backgroundColor: colors }] },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
    });
    new Chart(document.getElementById('chartPlant'), {
        type: 'doughnut',
        data: { labels: scanPerPlant.map(item => item.name), datasets: [{ data: scanPerPlant.map(item => item.total), backgroundColor: colors }] },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
    new Chart(document.getElementById('chartDay'), {
        type: 'line',
        data: { labels: scanPerDay.map(item => item.date), datasets: [{ data: scanPerDay.map(item => item.total), borderColor: '#0072ce', backgroundColor: 'rgba(0,114,206,0.08)', fill: true, tension: 0 }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
</script>
@endpush

</x-layouts.app>
