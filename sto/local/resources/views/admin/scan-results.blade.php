<x-layouts.app :title="'All Scan Results'">

<div class="enterprise-toolbar">
    <button class="btn btn-primary" type="button" onclick="openCreate()">Add Scan</button>
    <button class="btn btn-icon" type="button" onclick="reloadTable()" title="Refresh">Refresh</button>
    <button class="btn btn-icon" type="button" onclick="resetFilters()" title="Reset">Reset</button>
    <div class="toolbar-sep"></div>
    <button class="btn btn-success" type="button" id="exportExcel" onclick="queueExport('excel')">Export Excel</button>
    <button class="btn" type="button" id="exportPdf" onclick="queueExport('pdf')">Export PDF</button>
</div>

<div class="card" style="border-top:0;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div style="width:130px;"><label class="form-label">Plant</label><select id="filterPlant" class="form-control"><option value="">All</option>@foreach($plants as $plant)<option value="{{ $plant->id }}">{{ $plant->name }}</option>@endforeach</select></div>
    <div style="width:130px;"><label class="form-label">Location</label><select id="filterLocation" class="form-control"><option value="">All</option>@foreach($locations as $location)<option value="{{ $location->name }}">{{ $location->name }}</option>@endforeach</select></div>
    <div style="width:130px;"><label class="form-label">User</label><select id="filterUser" class="form-control"><option value="">All</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
    <div style="width:130px;"><label class="form-label">Material</label><select id="filterMaterial" class="form-control"><option value="">All</option>@foreach($materials as $material)<option value="{{ $material->material_code }}">{{ $material->material_code }}</option>@endforeach</select></div>
    <div style="width:130px;"><label class="form-label">Date From</label><input type="date" id="filterDateFrom" class="form-control"></div>
    <div style="width:130px;"><label class="form-label">Date To</label><input type="date" id="filterDateTo" class="form-control"></div>
    <button class="btn btn-primary" type="button" onclick="reloadTable()">Filter</button>
</div>

<div id="inlineEditorError" class="inline-editor-error"></div>

<div class="table-container" style="border-top:0;">
    <table id="adminScanTable" class="table-enterprise" style="width:100%;">
        <colgroup>
            <col style="width:3%;">
            <col style="width:12%;">
            <col style="width:6%;">
            <col style="width:6%;">
            <col style="width:4%;">
            <col style="width:4%;">
            <col style="width:4%;">
            <col style="width:4%;">
            <col style="width:6%;">
            <col style="width:4%;">
            <col style="width:7%;">
            <col style="width:7%;">
            <col style="width:6%;">
            <col style="width:11%;">
            <col style="width:8%;">
            <col style="width:5%;">
        </colgroup>
        <thead>
            <tr>
                <th>No</th><th>QR code</th><th>Material</th><th>Shape</th><th>T</th><th>W</th><th>D</th><th>L</th><th>Lot</th><th>Qty</th><th>User</th><th>Plant</th><th>Location</th><th>Time</th><th>Status</th><th>Action</th>
            </tr>
        </thead>
    </table>
</div>

<div class="modal-overlay" id="duplicateModal">
    <div class="modal-content">
        <div class="modal-header"><strong>Warning</strong></div>
        <div class="modal-body">QR code sudah pernah discan sebelumnya. Tetap simpan?</div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeDuplicateModal()">Batal</button>
            <button class="btn btn-primary" type="button" onclick="saveScanResult(true)">Ya</button>
        </div>
    </div>
</div>

@push('styles')
<style>
    #adminScanTable {
        table-layout: fixed;
    }

    #adminScanTable th,
    #adminScanTable td {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #adminScanTable th:first-child,
    #adminScanTable td:first-child,
    #adminScanTable th:nth-child(5),
    #adminScanTable td:nth-child(5),
    #adminScanTable th:nth-child(6),
    #adminScanTable td:nth-child(6),
    #adminScanTable th:nth-child(7),
    #adminScanTable td:nth-child(7),
    #adminScanTable th:nth-child(8),
    #adminScanTable td:nth-child(8),
    #adminScanTable th:nth-child(10),
    #adminScanTable td:nth-child(10),
    #adminScanTable th:nth-child(15),
    #adminScanTable td:nth-child(15) {
        text-align: center;
        padding-left: 4px;
        padding-right: 4px;
    }

    #adminScanTable th:nth-child(16),
    #adminScanTable td:nth-child(16) {
        overflow: visible;
        text-overflow: clip;
        padding-left: 6px;
        padding-right: 6px;
    }

    #adminScanTable th:nth-child(14),
    #adminScanTable td:nth-child(14) {
        padding-right: 12px;
    }

    #adminScanTable th:nth-child(15),
    #adminScanTable td:nth-child(15) {
        padding-left: 8px;
    }

    #adminScanTable th.sorting,
    #adminScanTable th.sorting_asc,
    #adminScanTable th.sorting_desc {
        padding-right: 18px !important;
        background-position: right 2px center !important;
    }

    .scan-time {
        display: block;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .scan-row-actions {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        white-space: nowrap;
    }

    .scan-row-actions .btn-icon {
        padding: 2px 0;
        font-size: 12px;
    }

    .scan-row-actions .js-row-delete {
        color: var(--danger);
    }

    #adminScanTable td:nth-child(15) .status-badge {
        max-width: 100%;
        overflow: hidden;
        text-overflow: clip;
        white-space: nowrap;
        letter-spacing: 0;
        padding-left: 5px;
        padding-right: 5px;
    }

    #adminScanTable td:nth-child(15) .status-badge:not(.badge-valid) {
        font-size: 9px;
        padding-left: 4px;
        padding-right: 4px;
    }

    #adminScanTable .inline-scan-editor td {
        background: #f8fafc;
        border-top: 1px solid var(--primary);
        border-bottom: 1px solid var(--primary);
        padding: 3px 4px;
        vertical-align: top;
        white-space: normal;
    }

    .inline-editor-label {
        color: var(--primary);
        font-weight: 700;
        white-space: nowrap;
    }

    .inline-input,
    .inline-select {
        width: 100%;
        min-width: 0;
        max-width: 100%;
        height: 26px;
        padding: 2px 4px;
        font-size: 11px;
    }

    .inline-select {
        appearance: none;
        -webkit-appearance: none;
        background-image:
            linear-gradient(45deg, transparent 50%, var(--text) 50%),
            linear-gradient(135deg, var(--text) 50%, transparent 50%);
        background-position:
            calc(100% - 11px) 7px,
            calc(100% - 6px) 7px;
        background-repeat: no-repeat;
        background-size: 5px 5px, 5px 5px;
        padding-right: 18px;
    }

    .inline-select::-ms-expand {
        display: none;
    }

    .inline-input.compact {
        min-width: 0;
    }

    .inline-input.wide,
    .inline-select.wide {
        min-width: 0;
    }

    .inline-actions {
        display: flex;
        gap: 4px;
        min-width: 0;
    }

    .inline-actions .btn {
        flex: 1 1 0;
        min-width: 0;
        padding: 3px 5px;
        font-size: 11px;
        white-space: nowrap;
    }

    .inline-editor-error {
        display: none;
        background: #fff7f7;
        border: 1px solid #f3b4b0;
        color: var(--danger);
        font-size: 12px;
        font-weight: 600;
        padding: 6px 10px;
    }

    .inline-editor-error.active {
        display: block;
    }

    .inline-field.is-invalid {
        border-color: var(--danger);
        box-shadow: 0 0 0 1px rgba(217, 45, 32, 0.12);
    }

    @media (max-width: 768px) {
        #adminScanTable {
            table-layout: auto;
        }
        #adminScanTable colgroup {
            display: none;
        }
        .inline-input,
        .inline-select {
            height: 36px;
            padding: 6px 8px;
            font-size: 13px;
        }
        .inline-actions .btn {
            padding: 6px 8px;
            font-size: 12px;
            min-height: 36px;
        }
        .scan-row-actions {
            flex-wrap: wrap;
            gap: 4px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let adminTable;
    let activeEditor = null;
    let pendingCreatePayload = null;
    let suppressOutsideUntil = 0;
    let exportPollingTimer = null;
    let exportPollingFailureCount = 0;
    const maxExportPollingFailures = 3;
    const pendingAutoDownloadExportIds = new Set();
    const autoDownloadedExportIds = new Set();
    window.scanResultRows = {};

    const materialNames = @json($materials->mapWithKeys(fn($material) => [$material->material_code => $material->material_name]));
    const userOptions = @json($users->map(fn($user) => ['id' => $user->id, 'label' => $user->name])->values());
    const plantOptions = @json($plants->map(fn($plant) => ['id' => $plant->id, 'label' => $plant->name])->values());
    const materialOptions = @json($materials->map(fn($material) => ['id' => $material->material_code, 'label' => $material->material_name])->values());
    const keteranganOptions = @json(collect($keteranganList)->map(fn($name) => ['id' => $name, 'label' => $name])->values());
    const shapeCodeOptions = [{ id: 'RF', label: 'Flat' }, { id: 'RR', label: 'Round' }];
    const scanResultsCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const exportQueueUrlTemplate = @json(route('admin.export.scan-results.queue', ['format' => '__FORMAT__']));
    const exportStatusUrl = @json(route('admin.export.scan-results.status'));

    function filters() {
        return {
            plant_id: $('#filterPlant').val(),
            location_name: $('#filterLocation').val(),
            user_id: $('#filterUser').val(),
            material_code: $('#filterMaterial').val(),
            date_from: $('#filterDateFrom').val(),
            date_to: $('#filterDateTo').val(),
        };
    }

    function updateExportLinks() {
        refreshExportStatus();
    }

    $(document).ready(function() {
        adminTable = $('#adminScanTable').DataTable({
            processing: true,
            serverSide: true,
            scrollX: false,
            autoWidth: false,
            ajax: { url: '{{ route("admin.api.scan-results") }}', data: d => Object.assign(d, filters()) },
            order: [],
            pageLength: 25,
            columnDefs: [
                { targets: 0, width: '3%', className: 'dt-center' },
                { targets: 1, width: '12%' },
                { targets: [2, 3], width: '6%' },
                { targets: [4, 5, 6, 7, 9], width: '4%', className: 'dt-center' },
                { targets: 8, width: '6%' },
                { targets: 10, width: '7%' },
                { targets: [11, 12], width: '6%' },
                { targets: 13, width: '11%' },
                { targets: 14, width: '8%', className: 'dt-center' },
                { targets: 15, width: '9%', orderable: false },
            ],
            columns: [
                { data: 'no', orderable: false }, { data: 'barcode_material'}, { data: 'material_name' }, { data: 'shape_name' },
                { data: 'thickness', render: d => d ?? '-' }, { data: 'width', render: d => d ?? '-' }, { data: 'diameter', render: d => d ?? '-' }, { data: 'length' },
                { data: 'lot_number'}, { data: 'qty' }, { data: 'user' }, { data: 'plant' }, { data: 'location_name' }, { data: 'created_at', render: d => `<span class="scan-time" title="${escapeAttr(d)}">${escapeHtml(d)}</span>` },
                { data: 'keterangan', render: d => `<span class="badge status-badge ${d === 'OK' ? 'badge-valid' : 'badge-invalid'}">${escapeHtml(d)}</span>` },
                { data: null, orderable: false, render: row => {
                    window.scanResultRows[row.id] = row;
                    return `<div class="scan-row-actions"><button class="btn-icon js-row-edit" type="button" data-scan-id="${row.id}" onclick="openEdit(${row.id})">Edit</button><button class="btn-icon js-row-delete" type="button" onclick="deleteRow(${row.id}, '${escapeHtml(row.barcode_material)}')">Delete</button></div>`;
                }}
            ],
            language: { emptyTable: 'Tidak ada data ditemukan.' }
        });
        updateExportLinks();

        $(document).on('mousedown', function(event) {
            if (!activeEditor || Date.now() < suppressOutsideUntil || $('#duplicateModal').hasClass('active')) return;

            const target = $(event.target);
            if (target.closest('.inline-scan-editor,#duplicateModal,.js-row-edit,.js-row-delete,.enterprise-toolbar,.swal2-container').length) {
                return;
            }

            if (!closeActiveEditor(true)) {
                suppressOutsideUntil = Date.now() + 250;
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });

    function reloadTable(confirmClose = true) {
        if (confirmClose && !closeActiveEditor(true, () => reloadTable(false))) {
            suppressOutsideUntil = Date.now() + 250;
            return;
        }

        updateExportLinks();
        adminTable.ajax.reload();
    }

    function resetFilters() {
        if (!closeActiveEditor(true, () => resetFilters())) {
            suppressOutsideUntil = Date.now() + 250;
            return;
        }

        $('#filterSto,#filterPlant,#filterLocation,#filterUser,#filterMaterial,#filterDateFrom,#filterDateTo').val('');
        adminTable.order([]).search('').page('first');
        reloadTable(false);
    }

    function openCreate() {
        if (!closeActiveEditor(true, () => openCreate())) {
            suppressOutsideUntil = Date.now() + 250;
            return;
        }

        const createData = defaultCreateData();
        const mainRow = $(inlineEditorRow('create', createData));
        const tbody = $('#adminScanTable tbody');

        tbody.prepend(mainRow);
        activeEditor = { mode: 'create', id: null, mainRow, originalPayload: payloadSnapshot(createData) };
        attachInlineEvents();
        mainRow.find('[data-field="barcode_material"]').trigger('focus');
    }

    function openEdit(id) {
        const row = window.scanResultRows[id];
        if (!row) return;

        if (!closeActiveEditor(true, () => openEdit(id))) {
            suppressOutsideUntil = Date.now() + 250;
            return;
        }

        const parentRow = $(`button.js-row-edit[data-scan-id="${id}"]`).closest('tr');
        if (!parentRow.length) return;

        const normalized = normalizeRow(row);
        const mainRow = $(inlineEditorRow('edit', normalized));

        parentRow.after(mainRow);
        activeEditor = { mode: 'edit', id, mainRow, originalPayload: payloadSnapshot(normalized) };
        attachInlineEvents();
        mainRow.find('[data-field="barcode_material"]').trigger('focus');
    }

    function closeDuplicateModal() { $('#duplicateModal').removeClass('active'); pendingCreatePayload = null; }

    function saveScanResult(forceSave) {
        if (!activeEditor && !pendingCreatePayload) return;

        const mode = activeEditor?.mode || 'create';
        const id = activeEditor?.id;
        const payload = forceSave && pendingCreatePayload ? pendingCreatePayload : scanPayload();
        payload.force_save = !!forceSave;
        clearInlineErrors();

        fetch(mode === 'edit' ? `/admin/api/scan-results/${id}` : '{{ route("admin.api.scan-results.store") }}', {
            method: mode === 'edit' ? 'PUT' : 'POST',
            headers: requestHeaders(),
            body: JSON.stringify(payload)
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw data;
            return data;
        })
        .then(data => {
            closeDuplicateModal();
            clearActiveEditor();
            showToast(data.message);
            reloadTable(false);
        })
        .catch(error => {
            if (error.duplicate && mode === 'create') {
                pendingCreatePayload = payload;
                $('#duplicateModal').addClass('active');
                return;
            }

            showInlineError(error);
        });
    }

    function scanPayload() {
        if (!activeEditor) return pendingCreatePayload || {};

        return {
            user_id: fieldValue('user_id'),
            plant_id: fieldValue('plant_id'),
            location_name: fieldValue('location_name'),
            barcode_raw: fieldValue('barcode_raw') || fieldValue('barcode_material'),
            barcode_material: fieldValue('barcode_material'),
            lot_number: fieldValue('lot_number'),
            qty: fieldValue('qty'),
            material_code: fieldValue('material_code'),
            material_name: fieldValue('material_name'),
            shape_code: fieldValue('shape_code'),
            shape_name: fieldValue('shape_name'),
            thickness: valueOrNull('thickness'),
            width: valueOrNull('width'),
            diameter: valueOrNull('diameter'),
            length: fieldValue('length'),
            keterangan: fieldValue('keterangan'),
            scan_source: fieldValue('scan_source') || 'admin',
            created_at: fieldValue('created_at'),
        };
    }

    function valueOrNull(field) {
        const value = fieldValue(field);
        return value === '' ? null : value;
    }

    function errorMessage(error) {
        if (error.message) return error.message;
        const first = Object.values(error.errors || {})[0];
        return first?.[0] || 'Data tidak valid.';
    }

    function showInlineError(error) {
        const messages = error.errors
            ? Object.entries(error.errors).map(([field, values]) => {
                activeEditor?.mainRow.find(`[data-field="${field}"]`).addClass('is-invalid');
                return values[0];
            })
            : [errorMessage(error)];

        $('#inlineEditorError').html(messages.map(escapeHtml).join('<br>')).addClass('active');
        showToast(messages[0] || 'Data tidak valid.', 'error');
    }

    function clearInlineErrors() {
        activeEditor?.mainRow.find('.inline-field').removeClass('is-invalid');
        $('#inlineEditorError').removeClass('active').empty();
    }

    function requestHeaders() {
        const headers = { 'Content-Type': 'application/json', Accept: 'application/json' };

        if (scanResultsCsrfToken) {
            headers['X-CSRF-TOKEN'] = scanResultsCsrfToken;
        }

        return headers;
    }

    function queueExport(format) {
        if (!closeActiveEditor(true, () => queueExport(format))) {
            suppressOutsideUntil = Date.now() + 250;
            return;
        }

        setExportButtonsDisabled(true);

        fetch(exportQueueUrlTemplate.replace('__FORMAT__', format), {
            method: 'POST',
            headers: requestHeaders(),
            body: JSON.stringify(filters()),
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
        document.getElementById('exportExcel').disabled = disabled;
        document.getElementById('exportPdf').disabled = disabled;
    }

    function nowForInput() {
        const date = new Date();
        const pad = value => String(value).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
    }

    function toDateTimeLocal(value) {
        return value ? value.replace(' ', 'T') : nowForInput();
    }

    function inlineEditorRow(mode, data) {
        const label = mode === 'create' ? '+' : 'Edit';

        return `
            <tr class="inline-scan-editor" data-mode="${mode}">
                <td class="inline-editor-label">${label}</td>
                <td>${textField('barcode_material', data.barcode_material, 'wide mono')}</td>
                <td>
                    ${selectField('material_code', materialOptions, data.material_code, 'wide js-material-select')}
                    ${hiddenField('material_name', data.material_name)}
                </td>
                <td>${selectField('shape_code', shapeCodeOptions, data.shape_code, 'js-shape-select')}${hiddenField('shape_name', data.shape_name)}</td>
                <td>${numberField('thickness', data.thickness)}</td>
                <td>${numberField('width', data.width)}</td>
                <td>${numberField('diameter', data.diameter)}</td>
                <td>${numberField('length', data.length)}</td>
                <td>${textField('lot_number', data.lot_number, 'wide')}</td>
                <td>${numberField('qty', data.qty, 'compact')}</td>
                <td>${selectField('user_id', userOptions, data.user_id, 'wide')}</td>
                <td>${selectField('plant_id', plantOptions, data.plant_id, 'wide')}</td>
                <td>${textField('location_name', data.location_name, 'wide')}</td>
                <td>${dateTimeField('created_at', data.created_at)}</td>
                <td>${selectField('keterangan', keteranganOptions, data.keterangan, 'wide')}</td>
                <td>
                    ${hiddenField('barcode_raw', data.barcode_raw)}
                    ${hiddenField('scan_source', data.scan_source || 'admin')}
                    <div class="inline-actions">
                        <button class="btn btn-primary" type="button" onclick="saveScanResult(false)">Save</button>
                        <button class="btn" type="button" onclick="closeActiveEditor(true)">Cancel</button>
                    </div>
                </td>
            </tr>`;
    }

    function textField(field, value = '', extraClass = '') {
        return `<input class="form-control inline-field inline-input ${extraClass}" data-field="${field}" value="${escapeAttr(value)}" maxlength="255">`;
    }

    function hiddenField(field, value = '') {
        return `<input class="inline-field" data-field="${field}" type="hidden" value="${escapeAttr(value)}">`;
    }

    function numberField(field, value = '', extraClass = '') {
        return `<input class="form-control inline-field inline-input ${extraClass}" data-field="${field}" type="number" min="1" value="${escapeAttr(value)}">`;
    }

    function dateTimeField(field, value = '') {
        return `<input class="form-control inline-field inline-input wide" data-field="${field}" type="datetime-local" step="1" value="${escapeAttr(value)}">`;
    }

    function selectField(field, options, value = '', extraClass = '') {
        const optionHtml = ['<option value=""></option>']
            .concat(options.map(option => `<option value="${escapeAttr(option.id)}"${String(option.id) === String(value ?? '') ? ' selected' : ''}>${escapeHtml(option.label)}</option>`))
            .join('');

        return `<select class="form-control inline-field inline-select ${extraClass}" data-field="${field}">${optionHtml}</select>`;
    }

    function attachInlineEvents() {
        activeEditor.mainRow.find('.js-material-select').on('change', function() {
            const value = this.value;
            const materialNameField = activeEditor.mainRow.find('[data-field="material_name"]');

            materialNameField.val(materialNames[value] || '');
        });

        const shapeSelect = activeEditor.mainRow.find('.js-shape-select');
        shapeSelect.on('change', function() {
            const shapeVal = this.value;
            activeEditor.mainRow.find('[data-field="shape_name"]').val(shapeVal === 'RR' ? 'Round' : (shapeVal === 'RF' ? 'Flat' : ''));
            
            const thickness = activeEditor.mainRow.find('[data-field="thickness"]');
            const width = activeEditor.mainRow.find('[data-field="width"]');
            const diameter = activeEditor.mainRow.find('[data-field="diameter"]');
            
            thickness.prop('disabled', false).css('background', '');
            width.prop('disabled', false).css('background', '');
            diameter.prop('disabled', false).css('background', '');
            
            if (shapeVal === 'RF') {
                diameter.prop('disabled', true).val('').css('background', '#e9ecef');
            } else if (shapeVal === 'RR') {
                thickness.prop('disabled', true).val('').css('background', '#e9ecef');
                width.prop('disabled', true).val('').css('background', '#e9ecef');
            }
        });
        
        // Trigger initial shape logic
        shapeSelect.trigger('change');

        activeEditor.mainRow.find('.inline-field').on('input change', clearInlineErrors);
    }

    function closeActiveEditor(confirmDirty = true, onConfirm = null) {
        if (!activeEditor) return true;

        if (confirmDirty && isEditorDirty()) {
            Swal.fire({
                title: 'Batalkan perubahan?',
                text: 'Data scan yang belum disimpan akan hilang.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, batalkan',
                cancelButtonText: 'Kembali edit',
                background: '#ffffff',
                color: '#1f2937'
            }).then((result) => {
                if (result.isConfirmed) {
                    clearActiveEditor();
                    if (onConfirm) onConfirm();
                }
            });
            return false;
        }

        clearActiveEditor();
        return true;
    }

    function clearActiveEditor() {
        activeEditor?.mainRow.remove();
        activeEditor = null;
        pendingCreatePayload = null;
        clearInlineErrors();
    }

    function isEditorDirty() {
        if (!activeEditor) return false;

        return JSON.stringify(payloadSnapshot(scanPayload())) !== JSON.stringify(activeEditor.originalPayload);
    }

    function emptyPayload() {
        return payloadSnapshot({
            user_id: '',
            plant_id: '',
            location_name: '',
            barcode_raw: '',
            barcode_material: '',
            lot_number: '',
            qty: '',
            material_code: '',
            material_name: '',
            shape_code: '',
            shape_name: '',
            thickness: null,
            width: null,
            diameter: null,
            length: '',
            keterangan: '',
            scan_source: '',
            created_at: '',
        });
    }

    function defaultCreateData() {
        return {
            scan_source: 'admin',
        };
    }

    function normalizeRow(row) {
        return {
            user_id: row.user_id ?? '',
            plant_id: row.plant_id ?? '',
            location_name: row.location_name ?? '',
            barcode_raw: row.barcode_raw ?? '',
            barcode_material: row.barcode_material ?? '',
            lot_number: row.lot_number ?? '',
            qty: row.qty ?? '',
            material_code: row.material_code ?? '',
            material_name: row.material_name ?? '',
            shape_code: row.shape_code ?? '',
            shape_name: row.shape_name ?? '',
            thickness: row.thickness ?? null,
            width: row.width ?? null,
            diameter: row.diameter ?? null,
            length: row.length ?? '',
            keterangan: row.keterangan ?? '',
            scan_source: row.scan_source ?? '',
            created_at: toDateTimeLocal(row.created_at),
        };
    }

    function payloadSnapshot(payload) {
        const normalized = {};

        Object.keys(emptyPayloadFields()).forEach(field => {
            normalized[field] = payload[field] === null || payload[field] === undefined ? '' : String(payload[field]);
        });

        return normalized;
    }

    function emptyPayloadFields() {
        return {
            user_id: '', plant_id: '', location_name: '', barcode_raw: '', barcode_material: '',
            lot_number: '', qty: '', material_code: '', material_name: '', shape_code: '', shape_name: '',
            thickness: '', width: '', diameter: '', length: '', keterangan: '', scan_source: '', created_at: '',
        };
    }

    function fieldValue(field) {
        return activeEditor?.mainRow.find(`[data-field="${field}"]`).val() ?? '';
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
    }

    function escapeAttr(value) {
        return escapeHtml(value);
    }

    function deleteRow(id, barcode) {
        confirmAction(`Apakah Anda yakin ingin menghapus data scan <b>${barcode}</b>?`, () => {
            clearActiveEditor();
            fetch(`/admin/api/scan-results/${id}`, { method: 'DELETE', headers: requestHeaders() })
                .then(r => r.json()).then(payload => { if (payload.success) { showToast(payload.message); reloadTable(); } else showToast(payload.message || 'Gagal hapus', 'error'); });
        });
    }
</script>
@endpush

</x-layouts.app>
