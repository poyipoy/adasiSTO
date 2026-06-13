<x-layouts.app :title="'Scan History'">



<div class="enterprise-toolbar">
    <button class="btn btn-primary" type="button" onclick="loadHistory()">Refresh</button>
    <a href="{{ route('scan.scanner') }}" class="btn" id="openScannerTab">Scanner</a>
</div>

<div class="card" style="border-top:0;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div style="width:150px;">
        <label class="form-label">Tanggal Awal</label>
        <input type="date" id="dateFrom" class="form-control">
    </div>
    <div style="width:150px;">
        <label class="form-label">Tanggal Akhir</label>
        <input type="date" id="dateTo" class="form-control">
    </div>
    <div style="min-width:180px;flex:1;">
        <label class="form-label">Barcode</label>
        <select id="barcodeFilter" class="form-control">
            <option value="">All</option>
            @foreach(($filterOptions['barcodes'] ?? []) as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div style="min-width:180px;flex:1;">
        <label class="form-label">Material</label>
        <select id="materialFilter" class="form-control">
            <option value="">All</option>
            @foreach(($filterOptions['materials'] ?? []) as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div style="min-width:160px;flex:1;">
        <label class="form-label">Location</label>
        <select id="locationFilter" class="form-control">
            <option value="">All</option>
            @foreach(($filterOptions['locations'] ?? []) as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div style="min-width:220px;flex:1;">
        <label class="form-label">Search</label>
        <input type="text" id="searchInput" class="form-control" placeholder="Barcode, material, lot">
    </div>
    <button class="btn btn-primary" type="button" onclick="loadHistory()">Filter</button>
</div>

<div class="table-container" style="border-top:0;">
    <table class="table-enterprise">
        <thead>
            <tr>
                <th>No</th>
                <th>Barcode</th>
                <th>Material</th>
                <th>Shape</th>
                <th>Lot</th>
                <th>Qty</th>
                <th>Location</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="historyRows"></tbody>
    </table>
</div>



@push('scripts')
<script>
    let currentPage = 1;

    document.getElementById('openScannerTab')?.addEventListener('click', function(event) {
        const url = this.href;
        const title = this.textContent.trim() || 'Scanner';

        try {
            const workspaceWindow = window.parent && window.parent !== window ? window.parent : window;
            if (typeof workspaceWindow.openWorkspaceTab === 'function') {
                event.preventDefault();
                workspaceWindow.openWorkspaceTab(url, title);
            }
        } catch (error) {
            // Keep the normal link fallback if the parent frame cannot be reached.
        }
    });

    function loadHistory(page = 1) {
        currentPage = page;
        const params = new URLSearchParams({
            page,
            per_page: 25,
            date_from: document.getElementById('dateFrom').value,
            date_to: document.getElementById('dateTo').value,
            barcode_material: document.getElementById('barcodeFilter').value,
            material_code: document.getElementById('materialFilter').value,
            location_id: document.getElementById('locationFilter').value,
            search: document.getElementById('searchInput').value
        });

        fetch(`/api/scan/history?${params.toString()}`, { headers: { Accept: 'application/json' } })
            .then(response => response.json())
            .then(payload => renderRows(payload.data, payload.meta))
            .catch(() => showToast('Gagal memuat history.', 'error'));
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }

    function renderRows(rows, meta) {
        const tbody = document.getElementById('historyRows');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;color:var(--text-muted);padding:16px;">Belum ada hasil scan.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map((row, index) => `
            <tr>
                <td>${meta.total - ((meta.page - 1) * meta.per_page) - index}</td>
                <td class="mono">${escapeHtml(row.barcode_material)}</td>
                <td>${escapeHtml(row.material_name)}</td>
                <td>${escapeHtml(row.shape_name)}</td>
                <td class="mono">${escapeHtml(row.lot_number)}</td>
                <td>${escapeHtml(row.qty)}</td>
                <td>${escapeHtml(row.location || '-')}</td>
                <td class="mono">${escapeHtml(row.created_at)}</td>
                <td><span class="badge ${row.keterangan === 'OK' ? 'badge-valid' : 'badge-invalid'}">${escapeHtml(row.keterangan)}</span></td>
                <td><button class="btn-icon" type="button" onclick="deleteScan(${row.id})" title="Delete">Delete</button></td>
            </tr>
        `).join('');
    }

    function deleteScan(id) {
        if (!confirm('Hapus scan ini?')) return;

        fetch(`/api/scan/${id}`, {
            method: 'DELETE',
            headers: { Accept: 'application/json' }
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
