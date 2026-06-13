<x-layouts.app :title="'Scan History'">

@push('styles')
<style>
    .mobile-only { display: none; }
    @media (max-width: 768px) {
        .desktop-only { display: none !important; }
        .mobile-only { display: block; }
        .filter-bar { flex-direction: column !important; align-items: stretch !important; gap: 8px !important; }
        .filter-bar > div { width: 100% !important; }
        .filter-bar .btn { width: 100%; justify-content: center; margin-top: 4px; }
    }
</style>
@endpush

<div class="enterprise-toolbar">
    <button class="btn btn-primary" type="button" onclick="loadHistory()">Refresh</button>
    <a href="{{ route('scan.scanner') }}" class="btn">Scanner</a>
</div>

<div class="card filter-bar" style="border-top:0;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div style="width:150px;">
        <label class="form-label">Tanggal Awal</label>
        <input type="date" id="dateFrom" class="form-control">
    </div>
    <div style="width:150px;">
        <label class="form-label">Tanggal Akhir</label>
        <input type="date" id="dateTo" class="form-control">
    </div>
    <div style="min-width:220px;flex:1;">
        <label class="form-label">Search</label>
        <input type="text" id="searchInput" class="form-control" placeholder="Barcode, material, lot">
    </div>
    <button class="btn btn-primary" type="button" onclick="loadHistory()">Filter</button>
</div>

<div class="table-container desktop-only" style="border-top:0;">
    <table class="table-enterprise">
        <thead>
            <tr>
                <th>No</th>
                <th>Barcode</th>
                <th>Material</th>
                <th>Shape</th>
                <th>Lot</th>
                <th>Qty</th>
                <th>STO</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="historyRows"></tbody>
    </table>
</div>

<div id="historyCards" class="mobile-only" style="margin-top: 12px;"></div>

@push('scripts')
<script>
    let currentPage = 1;

    function loadHistory(page = 1) {
        currentPage = page;
        const params = new URLSearchParams({
            page,
            per_page: 25,
            date_from: document.getElementById('dateFrom').value,
            date_to: document.getElementById('dateTo').value,
            search: document.getElementById('searchInput').value
        });

        fetch(`/api/scan/history?${params.toString()}`, { headers: { Accept: 'application/json' } })
            .then(response => response.json())
            .then(payload => renderRows(payload.data, payload.meta))
            .catch(() => showToast('Gagal memuat history.', 'error'));
    }

    function renderRows(rows, meta) {
        const tbody = document.getElementById('historyRows');
        const cards = document.getElementById('historyCards');
        
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;color:var(--text-muted);padding:16px;">Belum ada hasil scan.</td></tr>';
            cards.innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:16px;">Belum ada hasil scan.</div>';
            return;
        }

        tbody.innerHTML = rows.map((row, index) => `
            <tr>
                <td>${meta.total - ((meta.page - 1) * meta.per_page) - index}</td>
                <td class="mono">${row.barcode_material}</td>
                <td>${row.material_name}</td>
                <td>${row.shape_name}</td>
                <td class="mono">${row.lot_number}</td>
                <td>${row.qty}</td>
                <td class="mono">${row.sto_code}</td>
                <td class="mono">${row.created_at}</td>
                <td><span class="badge ${row.keterangan === 'OK' ? 'badge-valid' : 'badge-invalid'}">${row.keterangan}</span></td>
                <td>
                    <button class="btn-icon" style="color:var(--danger);padding:4px;" type="button" onclick="deleteScan(${row.id})" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                    </button>
                </td>
            </tr>
        `).join('');

        cards.innerHTML = rows.map((row) => `
            <div class="card" style="margin-bottom:8px;padding:12px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;gap:8px;">
                    <div style="min-width:0;">
                        <div class="mono" style="font-weight:700;color:var(--primary);word-break:break-all;">${row.barcode_material}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">${row.material_name}</div>
                    </div>
                    <span class="badge ${row.keterangan === 'OK' ? 'badge-valid' : 'badge-invalid'}">${row.keterangan}</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:flex-end;">
                    <div style="font-size:11px;color:var(--text-secondary);">
                        <div><strong>Lot:</strong> <span class="mono">${row.lot_number}</span></div>
                        <div><strong>Qty:</strong> ${row.qty}</div>
                        <div><strong>Time:</strong> ${row.created_at}</div>
                    </div>
                    <button class="btn-icon" style="color:var(--danger);padding:8px;" type="button" onclick="deleteScan(${row.id})" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                    </button>
                </div>
            </div>
        `).join('');
    }

    function deleteScan(id) {
        if (!confirm('Hapus scan ini?')) return;

        fetch(`/api/scan/${id}`, {
            method: 'DELETE',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(async response => {
            const payload = await response.json();
            if (!response.ok) throw payload;
            return payload;
        })
        .then(payload => {
            showToast(payload.message);
            loadHistory(currentPage);
        })
        .catch(error => showToast(error.message || 'Gagal menghapus scan.', 'error'));
    }

    document.addEventListener('DOMContentLoaded', () => loadHistory());
</script>
@endpush

</x-layouts.app>
