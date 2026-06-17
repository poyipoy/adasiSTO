<x-layouts.app :title="'Material Double'">

<div class="enterprise-toolbar">
    <button class="btn btn-icon" type="button" onclick="reloadMaterialDouble()" title="Refresh">Refresh</button>
    <button class="btn btn-icon" type="button" onclick="resetMaterialDoubleFilters()" title="Reset">Reset</button>
    <div class="toolbar-sep"></div>
    <button class="btn btn-success" type="button" id="exportExcel" onclick="queueExport()">Export Excel</button>
</div>

<div class="card" style="border-top:0;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div style="width:130px;">
        <label class="form-label">Plant</label>
        <select id="filterPlant" class="form-control">
            <option value="">All</option>
            @foreach($plants as $plant)
                <option value="{{ $plant->id }}">{{ $plant->name }}</option>
            @endforeach
        </select>
    </div>
    <div style="width:130px;">
        <label class="form-label">Location</label>
        <select id="filterLocation" class="form-control">
            <option value="">All</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}">{{ $location->name }}</option>
            @endforeach
        </select>
    </div>
    <div style="width:130px;">
        <label class="form-label">Material</label>
        <select id="filterMaterial" class="form-control">
            <option value="">All</option>
            @foreach($materials as $material)
                <option value="{{ $material->material_code }}">{{ $material->material_code }} - {{ $material->material_name }}</option>
            @endforeach
        </select>
    </div>
    <div style="width:130px;">
        <label class="form-label">Date From</label>
        <input type="date" id="filterDateFrom" class="form-control">
    </div>
    <div style="width:130px;">
        <label class="form-label">Date To</label>
        <input type="date" id="filterDateTo" class="form-control">
    </div>
    <button class="btn btn-primary" type="button" onclick="reloadMaterialDouble()">Filter</button>
</div>

<div class="table-container" style="border-top:0;">
    <table id="materialDoubleTable" class="table-enterprise" style="width:100%;">
        <thead>
            <tr>
                <th>No</th>
                <th>QR Code</th>
                <th>Material</th>
                <th>Shape</th>
                <th>Size</th>
                <th>Plant</th>
                <th>Location</th>
                <th>Duplicate</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>

<div class="modal-overlay" id="duplicateDetailModal">
    <div class="modal-content material-double-modal">
        <div class="modal-header">
            <strong>Duplicate Detail</strong>
            <button class="btn-icon" type="button" onclick="closeDuplicateDetailModal()">X</button>
        </div>
        <div class="modal-body">
            <div id="duplicateDetailTitle" class="mono" style="font-weight:700;color:var(--primary);margin-bottom:8px;"></div>
            <div class="table-container" style="border-top:1px solid var(--border-light);">
                <table id="duplicateDetailTable" class="table-enterprise" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>No</th>
                            <th>QR Code</th>
                            <th>Material</th>
                            <th>Shape</th>
                            <th>User</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeDuplicateDetailModal()">Batal</button>
            <button class="btn btn-danger" type="button" onclick="deleteSelectedDuplicateRows()">Delete Selected</button>
        </div>
    </div>
</div>

@push('styles')
<style>
    #materialDoubleTable th:first-child,
    #materialDoubleTable td:first-child,
    #materialDoubleTable th:nth-child(7),
    #materialDoubleTable td:nth-child(7),
    #duplicateDetailTable th:first-child,
    #duplicateDetailTable td:first-child,
    #duplicateDetailTable th:nth-child(2),
    #duplicateDetailTable td:nth-child(2) {
        text-align: center;
    }

    .material-double-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .material-double-actions .btn {
        height: 26px;
        min-height: 26px;
        padding: 0 8px;
        font-size: 11px;
    }

    .material-double-modal {
        width: min(940px, calc(100vw - 28px));
        max-width: 940px;
    }

    .duplicate-select {
        width: 16px;
        height: 16px;
    }
</style>
@endpush

@push('scripts')
<script>
    let materialDoubleTable;
    let duplicateDetailTable;
    let activeDuplicateGroup = null;
    const selectedDuplicateIds = new Set();
    const materialDoubleCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    let exportPollingTimer = null;
    let exportPollingFailureCount = 0;
    const maxExportPollingFailures = 3;
    const pendingAutoDownloadExportIds = new Set();
    const autoDownloadedExportIds = new Set();
    
    const exportQueueUrl = '{{ route("admin.api.material-double.export.queue", [], false) }}';
    const exportStatusUrl = '{{ route("admin.api.material-double.export.status", [], false) }}';

    function materialDoubleFilters() {
        return {
            plant_id: $('#filterPlant').val(),
            location_id: $('#filterLocation').val(),
            material_code: $('#filterMaterial').val(),
            date_from: $('#filterDateFrom').val(),
            date_to: $('#filterDateTo').val(),
        };
    }

    function groupPayload(row) {
        return Object.assign(materialDoubleFilters(), {
            barcode_material: row.barcode_material,
            plant_id: row.plant_id,
            location_id: row.location_id,
        });
    }

    function requestHeaders() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': materialDoubleCsrfToken
        };
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

    function escapeAttr(value) {
        return escapeHtml(value);
    }

    function reloadMaterialDouble() {
        updateExportLinks();
        materialDoubleTable.ajax.reload(null, false);
    }

    function resetMaterialDoubleFilters() {
        $('#filterSto,#filterPlant,#filterLocation,#filterMaterial,#filterDateFrom,#filterDateTo').val('');
        reloadMaterialDouble();
    }

    function renderAction(row) {
        if (row.is_validated) {
            return `<div class="material-double-actions" 
                data-barcode="${escapeAttr(row.barcode_material)}" 
                data-plantid="${row.plant_id}" 
                data-locationid="${row.location_id}" 
                data-plant="${escapeAttr(row.plant)}" 
                data-location="${escapeAttr(row.location)}">
                <button class="btn btn-success" type="button" disabled style="opacity: 0.6; cursor: not-allowed;">Valid</button>
                <button class="btn btn-danger" type="button" disabled style="opacity: 0.6; cursor: not-allowed;">Tidak Valid</button>
            </div>`;
        }

        return `<div class="material-double-actions" 
            data-barcode="${escapeAttr(row.barcode_material)}" 
            data-plantid="${row.plant_id}" 
            data-locationid="${row.location_id}" 
            data-plant="${escapeAttr(row.plant)}" 
            data-location="${escapeAttr(row.location)}">
            <button class="btn btn-success" type="button" onclick="validateDuplicateGroup(this)">Valid</button>
            <button class="btn btn-danger" type="button" onclick="openDuplicateDetail(this)">Tidak Valid</button>
        </div>`;
    }

    function rowFromAction(button) {
        const wrapper = $(button).closest('.material-double-actions');
        return {
            barcode_material: wrapper.attr('data-barcode'),
            plant_id: wrapper.attr('data-plantid'),
            location_id: wrapper.attr('data-locationid'),
            plant: wrapper.attr('data-plant'),
            location: wrapper.attr('data-location')
        };
    }

    function validateDuplicateGroup(button) {
        const row = rowFromAction(button);

        Swal.fire({
            title: 'Apakah Anda yakin?',
            html: `Anda akan mensahkan / memvalidasi duplicate untuk barcode <b>${escapeHtml(row.barcode_material)}</b>.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Validasi',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch('{{ route("admin.api.material-double.validate", [], false) }}', {
                method: 'POST',
                headers: requestHeaders(),
                body: JSON.stringify(groupPayload(row))
            })
                .then(async response => {
                    const payload = await response.json();
                    if (!response.ok) throw payload;
                    return payload;
                })
                .then(payload => {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: payload.message || 'Duplicate QR berhasil diverifikasi.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    reloadMaterialDouble();
                })
                .catch(error => {
                    Swal.fire('Gagal', error.message || 'Gagal memverifikasi duplicate QR.', 'error');
                });
        });
    }

    function openDuplicateDetail(button) {
        activeDuplicateGroup = rowFromAction(button);
        selectedDuplicateIds.clear();
        $('#duplicateDetailTitle').text(`${activeDuplicateGroup.barcode_material} - ${activeDuplicateGroup.plant} / ${activeDuplicateGroup.location}`);
        $('#duplicateDetailModal').addClass('active');

        if (duplicateDetailTable) {
            duplicateDetailTable.ajax.reload();
            return;
        }

        duplicateDetailTable = $('#duplicateDetailTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.api.material-double.detail", [], false) }}',
                data: d => Object.assign(d, groupPayload(activeDuplicateGroup))
            },
            order: [],
            pageLength: 25,
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    render: id => `<input class="duplicate-select" type="checkbox" value="${id}" ${selectedDuplicateIds.has(Number(id)) ? 'checked' : ''}>`
                },
                { data: 'no', orderable: false },
                { data: 'barcode_material', className: 'mono' },
                { data: 'material_name' },
                { data: 'shape_name' },
                { data: 'user_name' },
            ],
            language: { emptyTable: 'Tidak ada data duplicate ditemukan.' }
        });
    }

    function closeDuplicateDetailModal() {
        $('#duplicateDetailModal').removeClass('active');
        activeDuplicateGroup = null;
        selectedDuplicateIds.clear();
    }

    function deleteSelectedDuplicateRows() {
        if (!activeDuplicateGroup) return;

        const ids = Array.from(selectedDuplicateIds);
        if (!ids.length) {
            Swal.fire('Pilih data', 'Pilih minimal satu data duplicate untuk dihapus.', 'warning');
            return;
        }

        if (ids.length >= activeDuplicateGroup.duplicate_count) {
            Swal.fire('Peringatan', 'Anda tidak bisa menghapus semua data sekaligus. Sisakan minimal 1 data utama.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Apakah Anda yakin ingin menghapus data yang dipilih?',
            text: 'Data yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#b92525',
            reverseButtons: true
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch('{{ route("admin.api.material-double.delete-selected", [], false) }}', {
                method: 'DELETE',
                headers: requestHeaders(),
                body: JSON.stringify(Object.assign(groupPayload(activeDuplicateGroup), { ids }))
            })
                .then(async response => {
                    const payload = await response.json();
                    if (!response.ok) throw payload;
                    return payload;
                })
                .then(payload => {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: payload.message || 'Data duplicate terpilih berhasil dihapus.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    closeDuplicateDetailModal();
                    reloadMaterialDouble();
                    reloadAllScanResultsTab();
                })
                .catch(error => {
                    Swal.fire('Gagal', error.message || 'Gagal menghapus data duplicate.', 'error');
                });
        });
    }

    function reloadAllScanResultsTab() {
        if (!window.top || !window.top.document) return;

        const scanResultsUrl = '{{ route("admin.scan-results", [], false) }}';
        let safeIdStr = scanResultsUrl.replace(/[^a-zA-Z0-9]/g, '');
        if (safeIdStr.length > 20) safeIdStr = safeIdStr.substring(safeIdStr.length - 20);
        const pane = window.top.document.getElementById('pane-tab-' + safeIdStr);

        if (!pane || pane.tagName !== 'IFRAME' || !pane.contentWindow) return;

        if (typeof pane.contentWindow.reloadTable === 'function') {
            pane.contentWindow.reloadTable();
        } else if (pane.contentWindow.adminTable) {
            pane.contentWindow.adminTable.ajax.reload(null, false);
        } else {
            pane.contentWindow.location.reload();
        }
    }

    function updateExportLinks() {
        refreshExportStatus();
    }

    function queueExport() {
        setExportButtonsDisabled(true);

        fetch(exportQueueUrl, {
            method: 'POST',
            headers: requestHeaders(),
            body: JSON.stringify(materialDoubleFilters()),
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw data;
            return data;
        })
        .then(payload => {
            if (payload.data?.id) {
                pendingAutoDownloadExportIds.add(Number(payload.data.id));
            }

            Swal.fire({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 3000,
                icon: 'success',
                title: payload.message
            });
            exportPollingFailureCount = 0;
            refreshExportStatus();
            startExportPolling();
        })
        .catch(error => {
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 3000,
                icon: 'error',
                title: error.message || 'Export gagal dimulai.'
            });
        })
        .finally(() => setExportButtonsDisabled(false));
    }

    function refreshExportStatus() {
        fetch(exportStatusUrl, { headers: { Accept: 'application/json' } })
            .then(async response => {
                const payload = await response.json();
                if (!response.ok) throw payload;
                return payload;
            })
            .then(payload => {
                exportPollingFailureCount = 0;
                const exports = payload.data || [];
                triggerAutoDownloads(exports);

                const waitingForAutoDownload = exports.some(item => {
                    const id = Number(item.id);

                    return pendingAutoDownloadExportIds.has(id)
                        && ['queued', 'processing'].includes(item.status);
                });

                if (waitingForAutoDownload) {
                    startExportPolling();
                } else {
                    stopExportPolling();
                }
            })
            .catch(() => handleExportPollingFailure());
    }

    function handleExportPollingFailure() {
        if (pendingAutoDownloadExportIds.size === 0) {
            stopExportPolling();
            return;
        }

        exportPollingFailureCount++;

        if (exportPollingFailureCount < maxExportPollingFailures) {
            return;
        }

        stopExportPolling();
        pendingAutoDownloadExportIds.clear();
        exportPollingFailureCount = 0;
        Swal.fire({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 3000,
            icon: 'error',
            title: 'Status export gagal dimuat. Silakan coba export ulang.'
        });
    }

    function triggerAutoDownloads(exports) {
        exports.forEach(item => {
            const id = Number(item.id);

            if (!pendingAutoDownloadExportIds.has(id)) {
                return;
            }

            if (item.status === 'failed') {
                pendingAutoDownloadExportIds.delete(id);
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 3000,
                    icon: 'error',
                    title: item.message || 'Export gagal diproses.'
                });
                return;
            }

            if (item.status !== 'completed' || !item.download_url || autoDownloadedExportIds.has(id)) {
                return;
            }

            pendingAutoDownloadExportIds.delete(id);
            autoDownloadedExportIds.add(id);
            autoDownloadExport(item.download_url);
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 3000,
                icon: 'success',
                title: 'Export selesai. Download dimulai.'
            });
        });
    }

    function autoDownloadExport(downloadUrl) {
        let frame = document.getElementById('exportAutoDownloadFrame');

        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = 'exportAutoDownloadFrame';
            frame.hidden = true;
            frame.style.display = 'none';
            document.body.appendChild(frame);
        }

        frame.src = downloadUrl;
    }

    function startExportPolling() {
        if (exportPollingTimer) return;
        exportPollingTimer = setInterval(() => refreshExportStatus(), 3000);
    }

    function stopExportPolling() {
        if (!exportPollingTimer) return;
        clearInterval(exportPollingTimer);
        exportPollingTimer = null;
    }

    function setExportButtonsDisabled(disabled) {
        const btn = document.getElementById('exportExcel');
        if (btn) btn.disabled = disabled;
    }

    $(document).ready(function() {
        materialDoubleTable = $('#materialDoubleTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.api.material-double", [], false) }}',
                data: d => Object.assign(d, materialDoubleFilters())
            },
            order: [],
            pageLength: 25,
            columns: [
                { data: 'no', orderable: false },
                { data: 'barcode_material', className: 'mono' },
                { data: 'material_name' },
                { data: 'shape_name' },
                { data: 'size' },
                { data: 'plant' },
                { data: 'location' },
                { data: 'duplicate_count' },
                { 
                    data: 'is_validated', 
                    render: (val, type, row) => {
                        if (val) {
                            return `<div style="display:flex;flex-direction:column;gap:2px;">
                                <span class="badge" style="background:#28a745;color:#fff;width:fit-content;">Valid</span>
                                <small style="color:var(--text-secondary);font-size:10px;">Oleh: ${escapeHtml(row.validated_by_name)}</small>
                                <small style="color:var(--text-secondary);font-size:10px;">${escapeHtml(row.validated_at)}</small>
                            </div>`;
                        }
                        return '<span class="badge" style="background:#ffc107;color:#000;">Menunggu</span>';
                    } 
                },
                { data: null, orderable: false, searchable: false, render: row => renderAction(row) },
            ],
            language: { emptyTable: 'Tidak ada material double ditemukan.' }
        });

        $('#duplicateDetailTable').on('change', '.duplicate-select', function() {
            const id = Number(this.value);
            if (this.checked) {
                selectedDuplicateIds.add(id);
            } else {
                selectedDuplicateIds.delete(id);
            }
        });
        
        updateExportLinks();
    });
</script>
@endpush

</x-layouts.app>
