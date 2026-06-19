<x-layouts.app :title="'Scan History'">



<div class="enterprise-toolbar">
    <button class="btn btn-primary" type="button" onclick="loadHistory()">Refresh</button>
    <a href="{{ route('scan.scanner', [], false) }}" class="btn" id="openScannerTab">Scanner</a>
</div>

<div class="card filter-panel" style="border-top:0;">
    <div class="filter-item">
        <label class="form-label">Tanggal Awal</label>
        <input type="date" id="dateFrom" class="form-control">
    </div>
    <div class="filter-item">
        <label class="form-label">Tanggal Akhir</label>
        <input type="date" id="dateTo" class="form-control">
    </div>
    <div class="filter-item">
        <label class="form-label">Barcode</label>
        <select id="barcodeFilter" class="form-control">
            <option value="">All</option>
            @foreach(($filterOptions['barcodes'] ?? []) as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-item">
        <label class="form-label">Material</label>
        <select id="materialFilter" class="form-control">
            <option value="">All</option>
            @foreach(($filterOptions['materials'] ?? []) as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-item">
        <label class="form-label">Plant</label>
        <select id="plantFilter" class="form-control">
            <option value="">All</option>
            @foreach(($filterOptions['plants'] ?? []) as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-item">
        <label class="form-label">Location</label>
        <select id="locationFilter" class="form-control">
            <option value="">All</option>
            @foreach(($filterOptions['locations'] ?? []) as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-item filter-search">
        <label class="form-label">Search</label>
        <input type="text" id="searchInput" class="form-control" placeholder="Barcode, material, lot">
    </div>
    <div class="filter-item">
        <button class="btn btn-primary" style="width: 100%;" type="button" onclick="loadHistory()">Filter</button>
    </div>
</div>

@push('styles')
<style>
    .filter-panel {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
        align-items: flex-end;
    }
    .filter-search {
        grid-column: span 2;
    }
    @media (max-width: 768px) {
        .filter-panel {
            grid-template-columns: 1fr 1fr;
        }
        .filter-search {
            grid-column: span 2;
        }
    }
    @media (max-width: 480px) {
        .filter-panel {
            grid-template-columns: 1fr;
        }
        .filter-search {
            grid-column: span 1;
        }
    }
</style>
@endpush

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
                <th>Plant</th>
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
            plant_id: document.getElementById('plantFilter').value,
            location_id: document.getElementById('locationFilter').value,
            search: document.getElementById('searchInput').value
        });

        fetch(`/api/scan/history?${params.toString()}`, { headers: { Accept: 'application/json' } })
            .then(response => response.json())
            .then(payload => renderRows(payload.data, payload.meta))
            .catch(() => {
                const swal = window.top.Swal || Swal;
                swal.fire({
                    title: 'Gagal',
                    text: 'Gagal memuat history.',
                    icon: 'error',
                    confirmButtonColor: '#2b2d30',
                    confirmButtonText: 'Tutup'
                });
            });
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
                <td>${escapeHtml(row.plant || '-')}</td>
                <td>${escapeHtml(row.location || '-')}</td>
                <td class="mono">${escapeHtml(row.created_at)}</td>
                <td><span class="badge ${row.keterangan === 'OK' ? 'badge-valid' : 'badge-invalid'}">${escapeHtml(row.keterangan)}</span></td>
                <td><button class="btn-icon" style="color:var(--danger);padding:0 4px;" type="button" onclick="deleteScan(${row.id}, '${escapeHtml(row.barcode_material)}')" title="Delete"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg></button></td>
            </tr>
        `).join('');
    }

    function deleteScan(id, barcode) {
        const swal = window.top.Swal || Swal;
        swal.fire({
            title: 'Hapus Scan',
            html: `Yakin ingin menghapus hasil scan ini?<br><br><div style="padding: 8px; background: #f8f9fa; border: 1px dashed #adb5bd; border-radius: 4px; display: inline-block; font-family: monospace; font-size: 14px; font-weight: bold; color: #0072ce;">${barcode}</div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#b92525',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                fetch(`/api/scan/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(async response => {
                    const payload = await response.json();
                    if (!response.ok) throw payload;
                    return payload;
                })
                .then(payload => {
                    swal.fire({
                        title: 'Berhasil!',
                        text: payload.message || 'Scan berhasil dihapus.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    loadHistory(currentPage);
                })
                .catch(error => {
                    swal.fire({
                        title: 'Gagal',
                        text: error.message || 'Gagal menghapus scan.',
                        icon: 'error',
                        confirmButtonColor: '#2b2d30',
                        confirmButtonText: 'Tutup'
                    });
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => loadHistory());
</script>
@endpush

</x-layouts.app>
