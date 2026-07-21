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
            <select id="filterLocation" class="form-control" style="display:none;">
                <option value="">All</option>
                @foreach($locations as $location)
                    <option value="{{ $location->name }}">{{ $location->name }}</option>
                @endforeach
            </select>
            <button type="button" id="locationFilterTrigger" class="form-control" onclick="openLocationFilterModal()" style="text-align: left; background: #fff; cursor: pointer; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">All</button>
        </div>
        <div style="width:130px;">
            <label class="form-label">Material</label>
            <select id="filterMaterial" class="form-control" style="display:none;">
                <option value="">All</option>
                @foreach($materials as $material)
                    <option value="{{ $material->material_code }}">{{ $material->material_name }} ({{ $material->material_code }})</option>
                @endforeach
            </select>
            <button type="button" id="materialFilterTrigger" class="form-control" onclick="openMaterialFilterModal()" style="text-align: left; background: #fff; cursor: pointer; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">All</button>
        </div>
        <div style="width:130px;">
            <label class="form-label">Status</label>
            <select id="filterStatus" class="form-control">
                <option value="">All</option>
                <option value="valid">Valid</option>
                <option value="need_check">Need Check</option>
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
                <div id="duplicateDetailTitle" class="mono"
                    style="font-weight:700;color:var(--primary);margin-bottom:8px;"></div>
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
                                <th>Waktu Scan</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" type="button" onclick="closeDuplicateDetailModal()">Batal</button>
                <button id="deleteSelectedDuplicateBtn" class="btn btn-danger" type="button"
                    onclick="deleteSelectedDuplicateRows()">Delete Selected</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="materialDoubleScanModal">
        <div class="modal-content material-double-scan-modal">
            <div class="modal-header">
                <strong>Scan Material Double</strong>
                <button class="btn-icon" type="button" onclick="closeMaterialDoubleScanModal()">X</button>
            </div>
            <div class="modal-body">
                <div class="material-double-scan-context">
                    <div>
                        <span>QR Code</span>
                        <strong class="mono" id="scanGroupBarcode">-</strong>
                    </div>
                    <div>
                        <span>Plant</span>
                        <strong id="scanGroupPlant">-</strong>
                    </div>
                    <div>
                        <span>Location</span>
                        <strong id="scanGroupLocation">-</strong>
                    </div>
                </div>
                <div class="scan-header"
                    style="display:flex;justify-content:space-between;gap:8px;align-items:center;margin:10px 0;">
                    <div class="scanner-mode-toggle" role="group" aria-label="Scanner Mode">
                        <button type="button" id="materialDoubleAutoScanModeBtn" class="active"
                            onclick="setMaterialDoubleScannerMode('auto')">Auto Scan</button>
                        <button type="button" id="materialDoubleSelectBarcodeModeBtn"
                            onclick="setMaterialDoubleScannerMode('select')">Select Barcode</button>
                    </div>
                    <div class="camera-buttons" style="display:flex;gap:6px;">
                        <button class="btn" type="button" id="showMaterialDoubleCameraBtn"
                            onclick="showMaterialDoubleScanCamera()">Show Camera</button>
                        <button class="btn" type="button" id="hideMaterialDoubleCameraBtn"
                            onclick="hideMaterialDoubleScanCamera()" style="display:none;">Hide Camera</button>
                    </div>
                </div>
                <div id="materialDoubleScanReaderWrap" style="display:none;margin-bottom:10px;">
                    <div id="materialDoubleScanReader"
                        style="min-height:220px;border:1px solid var(--border);background:#fafbfc;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="materialDoubleScanQr">QR Final</label>
                    <input id="materialDoubleScanQr" class="form-control mono" placeholder="Masukkan QR Code"
                        autocomplete="off">
                </div>
                <div style="font-size:11px;color:var(--text-secondary);margin-top:4px;">
                    Plant dan Location mengikuti baris Material Double yang dipilih. PIC mengikuti user yang melakukan
                    scan.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" type="button" onclick="closeMaterialDoubleScanModal()">Batal</button>
                <button class="btn btn-primary" type="button" onclick="submitMaterialDoubleScan(false)">Simpan
                    Scan</button>
            </div>
        </div>
    </div>

    <!-- Material Filter Modal -->
    <div id="materialFilterModalContainer" class="custom-filter-overlay" style="display: none;">
        <div class="custom-filter-content">
            <div class="custom-filter-header">
                <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Select Material</h3>
                <button type="button" class="btn-icon" onclick="closeMaterialFilterModal()">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="custom-filter-search">
                <input type="text" id="materialFilterSearchInput" class="form-control" placeholder="Cari material..." oninput="filterMaterials(this.value)">
            </div>
            <div class="custom-filter-list" id="materialFilterList">
                <!-- Dynamically populated via JS -->
            </div>
        </div>
    </div>

    <!-- Location Filter Modal -->
    <div id="locationFilterModalContainer" class="custom-filter-overlay" style="display: none;">
        <div class="custom-filter-content">
            <div class="custom-filter-header">
                <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Select Location</h3>
                <button type="button" class="btn-icon" onclick="closeLocationFilterModal()">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="custom-filter-search">
                <input type="text" id="locationFilterSearchInput" class="form-control" placeholder="Cari lokasi..." oninput="filterLocations(this.value)">
            </div>
            <div class="custom-filter-list" id="locationFilterList">
                <!-- Dynamically populated via JS -->
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* --- Custom Search Filter Modal --- */
            .custom-filter-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1050;
                display: flex;
                align-items: flex-end;
                justify-content: center;
            }
            .custom-filter-content {
                background: #fff;
                width: 100%;
                max-width: 500px;
                max-height: 85vh;
                border-radius: 16px 16px 0 0;
                display: flex;
                flex-direction: column;
                animation: slideUp 0.3s ease-out;
            }
            @media (min-width: 768px) {
                .custom-filter-overlay { align-items: center; }
                .custom-filter-content {
                    border-radius: 12px;
                    max-height: 80vh;
                    animation: fadeIn 0.2s ease-out;
                }
            }
            .custom-filter-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 16px;
                border-bottom: 1px solid var(--border-light);
            }
            .custom-filter-search {
                padding: 12px 16px;
                border-bottom: 1px solid var(--border-light);
                background: #fafbfc;
            }
            .custom-filter-list {
                flex: 1;
                overflow-y: auto;
                padding: 8px 16px 16px 16px;
            }
            .custom-filter-item {
                padding: 12px;
                border-bottom: 1px solid var(--border-light);
                cursor: pointer;
                font-size: 14px;
                font-weight: 600;
                color: var(--text);
                transition: background 0.1s;
            }
            .custom-filter-item:last-child { border-bottom: none; }
            .custom-filter-item:active { background: #f0f0f0; }
            .custom-filter-item.active {
                background: var(--primary);
                color: #fff;
                border-radius: 6px;
                border-bottom: none;
                margin-bottom: 1px;
            }

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
                flex-wrap: wrap;
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

            .material-double-scan-modal {
                width: min(620px, calc(100vw - 28px));
                max-width: 620px;
            }

            .material-double-scan-context {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px;
                padding: 10px;
                border: 1px solid var(--border-light);
                background: #fafbfc;
            }

            .material-double-scan-context span {
                display: block;
                color: var(--text-secondary);
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .material-double-scan-context strong {
                display: block;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: 12px;
            }

            @media (max-width: 768px) {
                .material-double-scan-context {
                    grid-template-columns: 1fr;
                }

                .scan-header {
                    flex-direction: column !important;
                    align-items: flex-start !important;
                    gap: 8px !important;
                }

                .scan-header .camera-buttons {
                    width: 100%;
                }

                .scan-header .camera-buttons .btn {
                    width: 100%;
                    justify-content: center;
                }

                .scanner-mode-toggle {
                    width: 100%;
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                }

                .scanner-mode-toggle button {
                    width: 100%;
                }

                .material-double-actions {
                    flex-wrap: wrap;
                    gap: 4px;
                }

                .material-double-actions .btn {
                    flex: 1 1 auto;
                    min-width: 60px;
                    justify-content: center;
                }

                #materialDoubleTable {
                    table-layout: auto;
                }
            }

            /* Scanner Mode & Select Barcode Styles */
            .scanner-mode-toggle {
                display: inline-flex;
                align-items: center;
                border: 1px solid var(--border);
                background: #fff;
                min-height: 30px;
            }

            .scanner-mode-toggle button {
                border: 0;
                background: transparent;
                color: var(--text-secondary);
                font-size: 11px;
                font-weight: 700;
                padding: 0 10px;
                height: 28px;
                cursor: pointer;
            }

            .scanner-mode-toggle button.active {
                background: var(--primary);
                color: #fff;
            }

            .select-barcode-shell {
                position: relative;
                width: 100%;
                min-height: 260px;
                background: #111;
                overflow: hidden;
            }

            .select-barcode-shell video,
            .select-barcode-shell canvas {
                width: 100%;
                min-height: 260px;
                display: block;
            }

            .select-barcode-shell video {
                object-fit: cover;
            }

            .select-barcode-shell canvas {
                position: absolute;
                inset: 0;
                height: 100%;
                cursor: pointer;
                z-index: 2;
            }

            .select-barcode-tap-layer {
                position: absolute;
                inset: 0;
                z-index: 3;
                pointer-events: none;
            }

            .select-barcode-tap-label {
                position: absolute;
                min-height: 30px;
                max-width: calc(100% - 16px);
                border: 1px solid rgba(255, 255, 255, 0.9);
                background: var(--primary);
                color: #fff;
                border-radius: 3px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.28);
                cursor: pointer;
                font-size: 11px;
                font-weight: 800;
                overflow: hidden;
                padding: 0 8px;
                pointer-events: auto;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .select-barcode-candidates {
                display: grid;
                gap: 6px;
                padding: 8px;
                border: 1px solid var(--border-light);
                border-top: 0;
                background: #fff;
            }

            .select-barcode-empty {
                color: var(--text-secondary);
                font-size: 12px;
                padding: 6px 0;
                text-align: center;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
        <script>
            let materialDoubleTable;
            let duplicateDetailTable;
            let activeDuplicateGroup = null;
            let activeMaterialDoubleScanGroup = null;
            let materialDoubleScanScanner = null;
            let materialDoubleScanCameraRunning = false;
            let materialDoubleScanLocked = false;
            let materialDoubleScannerMode = 'auto';
            let materialDoubleSelectBarcodeDetector = null;
            let materialDoubleSelectBarcodeStream = null;
            let materialDoubleSelectBarcodeVideo = null;
            let materialDoubleSelectBarcodeCanvas = null;
            let materialDoubleSelectBarcodeOverlay = null;
            let materialDoubleSelectBarcodeLoopId = null;
            let materialDoubleSelectBarcodeDetecting = false;
            let materialDoubleSelectBarcodeCandidates = [];
            const selectedDuplicateIds = new Set();
            const materialDoubleCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            let exportPollingTimer = null;
            let exportPollingFailureCount = 0;
            const maxExportPollingFailures = 3;
            const pendingAutoDownloadExportIds = new Set();
            const autoDownloadedExportIds = new Set();

            const exportQueueUrl = '{{ route("admin.api.material-double.export.queue") }}';
            const exportStatusUrl = '{{ route("admin.api.material-double.export.status") }}';

            function materialDoubleFilters() {
                return {
                    plant_id: $('#filterPlant').val(),
                    location_name: $('#filterLocation').val(),
                    material_code: $('#filterMaterial').val(),
                    date_from: $('#filterDateFrom').val(),
                    date_to: $('#filterDateTo').val(),
                    status: $('#filterStatus').val(),
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
                $('#filterPlant,#filterLocation,#filterMaterial,#filterDateFrom,#filterDateTo,#filterStatus').val('');
                reloadMaterialDouble();
            }

            function renderAction(row) {
                if (row.is_validated) {
                    return `<div class="material-double-actions" 
                    data-barcode="${escapeAttr(row.barcode_material)}" 
                    data-plantid="${row.plant_id}" 
                    data-locationid="${row.location_id}" 
                    data-plant="${escapeAttr(row.plant)}" 
                    data-location="${escapeAttr(row.location)}"
                    data-duplicate-count="${row.duplicate_count}">
                    <button class="btn btn-primary" type="button" onclick="openMaterialDoubleScan(this)">Scan</button>
                    <button class="btn btn-success" type="button" disabled style="opacity: 0.6; cursor: not-allowed;">Valid</button>
                    <button class="btn btn-secondary" type="button" onclick="openDuplicateDetail(this, true)">Detail</button>
                </div>`;
                }

                return `<div class="material-double-actions" 
                data-barcode="${escapeAttr(row.barcode_material)}" 
                data-plantid="${row.plant_id}" 
                data-locationid="${row.location_id}" 
                data-plant="${escapeAttr(row.plant)}" 
                data-location="${escapeAttr(row.location)}"
                data-duplicate-count="${row.duplicate_count}">
                <button class="btn btn-primary" type="button" onclick="openMaterialDoubleScan(this)">Scan</button>
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
                    location: wrapper.attr('data-location'),
                    duplicate_count: Number(wrapper.attr('data-duplicate-count') || 0)
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

                    fetch('{{ route("admin.api.material-double.validate") }}', {
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

            function openDuplicateDetail(button, readOnly = false) {
                activeDuplicateGroup = rowFromAction(button);
                selectedDuplicateIds.clear();
                duplicateDetailReadOnly = readOnly;

                $('#duplicateDetailTitle').text(`${activeDuplicateGroup.barcode_material} - ${activeDuplicateGroup.plant} / ${activeDuplicateGroup.location}`);
                $('#duplicateDetailModal').addClass('active');

                if (duplicateDetailReadOnly) {
                    $('#deleteSelectedDuplicateBtn').hide();
                } else {
                    $('#deleteSelectedDuplicateBtn').show();
                }

                if (duplicateDetailTable) {
                    duplicateDetailTable.ajax.reload();
                    return;
                }

                duplicateDetailTable = $('#duplicateDetailTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("admin.api.material-double.detail") }}',
                        data: d => Object.assign(d, groupPayload(activeDuplicateGroup))
                    },
                    order: [],
                    pageLength: 25,
                    columns: [
                        {
                            data: 'id',
                            orderable: false,
                            render: id => {
                                if (duplicateDetailReadOnly) {
                                    return `<input type="checkbox" disabled>`;
                                }
                                return `<input class="duplicate-select" type="checkbox" value="${id}" ${selectedDuplicateIds.has(Number(id)) ? 'checked' : ''}>`;
                            }
                        },
                        { data: 'no', orderable: false },
                        { data: 'barcode_material', className: 'mono' },
                        { data: 'material_name' },
                        { data: 'shape_name' },
                        { data: 'user_name' },
                        { data: 'scan_time', className: 'mono' },
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

                    fetch('{{ route("admin.api.material-double.delete-selected") }}', {
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
                                text: payload.message || 'Data duplicate terpilih berhasil dihapus dan duplicate QR berhasil diverifikasi.',
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

            function openMaterialDoubleScan(button) {
                activeMaterialDoubleScanGroup = rowFromAction(button);
                $('#scanGroupBarcode').text(activeMaterialDoubleScanGroup.barcode_material || '-');
                $('#scanGroupPlant').text(activeMaterialDoubleScanGroup.plant || '-');
                $('#scanGroupLocation').text(activeMaterialDoubleScanGroup.location || '-');
                $('#materialDoubleScanQr').val('');
                $('#materialDoubleScanModal').addClass('active');
                setTimeout(() => $('#materialDoubleScanQr').trigger('focus'), 50);
            }

            function closeMaterialDoubleScanModal() {
                hideMaterialDoubleScanCamera();
                $('#materialDoubleScanModal').removeClass('active');
                activeMaterialDoubleScanGroup = null;
                materialDoubleScanLocked = false;
            }

            function renderMaterialDoubleScanCameraError(message) {
                $('#materialDoubleScanReader').html(`<div style="padding:32px;text-align:center;color:var(--text-muted);">${escapeHtml(message)}</div>`);
                Swal.fire('Kamera tidak tersedia', message, 'error');
            }

            function setMaterialDoubleScannerMode(mode) {
                if (!['auto', 'select'].includes(mode) || materialDoubleScannerMode === mode) return;

                const readerVisible = $('#materialDoubleScanReaderWrap').is(':visible');
                hideMaterialDoubleScanCamera();
                materialDoubleScannerMode = mode;

                $('#materialDoubleAutoScanModeBtn').toggleClass('active', materialDoubleScannerMode === 'auto');
                $('#materialDoubleSelectBarcodeModeBtn').toggleClass('active', materialDoubleScannerMode === 'select');

                if (readerVisible) {
                    setTimeout(() => showMaterialDoubleScanCamera(), 180);
                }
            }

            function showMaterialDoubleScanCamera() {
                $('#materialDoubleScanReaderWrap').show();
                $('#showMaterialDoubleCameraBtn').hide();
                $('#hideMaterialDoubleCameraBtn').css('display', 'inline-flex');

                if (!window.isSecureContext) {
                    renderMaterialDoubleScanCameraError('Kamera hanya bisa dipakai melalui HTTPS atau localhost.');
                    return;
                }

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    renderMaterialDoubleScanCameraError('Browser tidak menyediakan akses kamera.');
                    return;
                }

                if (materialDoubleScannerMode === 'select') {
                    showMaterialDoubleSelectBarcodeCamera();
                    return;
                }

                if (!window.Html5Qrcode) {
                    renderMaterialDoubleScanCameraError('Library kamera belum tersedia. Periksa asset aplikasi atau buka ulang halaman.');
                    return;
                }

                if (!materialDoubleScanScanner) {
                    const formats = window.Html5QrcodeSupportedFormats ? [
                        Html5QrcodeSupportedFormats.QR_CODE,
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.CODE_39,
                        Html5QrcodeSupportedFormats.CODE_93,
                        Html5QrcodeSupportedFormats.CODABAR,
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.ITF,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E,
                        Html5QrcodeSupportedFormats.UPC_EAN_EXTENSION
                    ] : undefined;

                    materialDoubleScanScanner = new Html5Qrcode('materialDoubleScanReader', {
                        formatsToSupport: formats,
                        verbose: false,
                        useBarCodeDetectorIfSupported: false
                    });
                }

                if (materialDoubleScanCameraRunning) return;

                materialDoubleScanScanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    decodedText => {
                        if (materialDoubleScanLocked) return;
                        $('#materialDoubleScanQr').val(decodedText);
                        submitMaterialDoubleScan(false, decodedText, 'camera');
                    },
                    () => { }
                ).then(() => {
                    materialDoubleScanCameraRunning = true;
                }).catch(() => {
                    renderMaterialDoubleScanCameraError('Kamera tidak tersedia. Gunakan input manual.');
                });
            }

            function hideMaterialDoubleScanCamera() {
                $('#materialDoubleScanReaderWrap').hide();
                $('#showMaterialDoubleCameraBtn').css('display', 'inline-flex');
                $('#hideMaterialDoubleCameraBtn').hide();
                stopMaterialDoubleSelectBarcodeCamera();

                if (!materialDoubleScanScanner || !materialDoubleScanCameraRunning) return;

                materialDoubleScanScanner.stop().then(() => {
                    materialDoubleScanCameraRunning = false;
                }).catch(() => {
                    materialDoubleScanCameraRunning = false;
                });
            }

            async function showMaterialDoubleSelectBarcodeCamera() {
                if (!('BarcodeDetector' in window)) {
                    renderMaterialDoubleScanCameraError('Browser ini belum mendukung Select Barcode. Mode dikembalikan ke Auto Scan.');
                    materialDoubleScannerMode = 'auto';
                    $('#materialDoubleAutoScanModeBtn').addClass('active');
                    $('#materialDoubleSelectBarcodeModeBtn').removeClass('active');
                    setTimeout(() => showMaterialDoubleScanCamera(), 200);
                    return;
                }

                try {
                    const preferredFormats = ['qr_code', 'code_128', 'code_39', 'code_93', 'codabar', 'ean_13', 'ean_8', 'itf', 'data_matrix'];
                    let formats = preferredFormats;

                    if (typeof BarcodeDetector.getSupportedFormats === 'function') {
                        const supportedFormats = await BarcodeDetector.getSupportedFormats();
                        formats = preferredFormats.filter(format => supportedFormats.includes(format));
                    }

                    if (!formats.length) {
                        throw new Error('Tidak ada format barcode yang didukung browser.');
                    }

                    materialDoubleSelectBarcodeDetector = new BarcodeDetector({ formats });
                    materialDoubleSelectBarcodeStream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { ideal: 'environment' } },
                        audio: false
                    });

                    const reader = document.getElementById('materialDoubleScanReader');
                    reader.innerHTML = `
                    <div class="select-barcode-shell">
                        <video id="materialDoubleSelectBarcodeVideo" autoplay muted playsinline></video>
                        <canvas id="materialDoubleSelectBarcodeCanvas"></canvas>
                        <div id="materialDoubleSelectBarcodeOverlay" class="select-barcode-tap-layer"></div>
                    </div>
                    <div id="materialDoubleSelectBarcodeCandidates" class="select-barcode-candidates">
                        <div class="select-barcode-empty">Arahkan kamera ke barcode, lalu tap label barcode pada tampilan kamera.</div>
                    </div>
                `;

                    materialDoubleSelectBarcodeVideo = document.getElementById('materialDoubleSelectBarcodeVideo');
                    materialDoubleSelectBarcodeCanvas = document.getElementById('materialDoubleSelectBarcodeCanvas');
                    materialDoubleSelectBarcodeOverlay = document.getElementById('materialDoubleSelectBarcodeOverlay');
                    materialDoubleSelectBarcodeVideo.srcObject = materialDoubleSelectBarcodeStream;
                    materialDoubleSelectBarcodeCanvas.addEventListener('click', handleMaterialDoubleSelectBarcodeCanvasClick);
                    await materialDoubleSelectBarcodeVideo.play();
                    materialDoubleScanCameraRunning = true;
                    materialDoubleSelectBarcodeTick();
                } catch (error) {
                    stopMaterialDoubleSelectBarcodeCamera();
                    renderMaterialDoubleScanCameraError('Select Barcode tidak tersedia di browser ini. Mode dikembalikan ke Auto Scan.');
                    materialDoubleScannerMode = 'auto';
                    $('#materialDoubleAutoScanModeBtn').addClass('active');
                    $('#materialDoubleSelectBarcodeModeBtn').removeClass('active');
                    setTimeout(() => showMaterialDoubleScanCamera(), 200);
                }
            }

            function stopMaterialDoubleSelectBarcodeCamera() {
                const hadSelectStream = !!materialDoubleSelectBarcodeStream;

                if (materialDoubleSelectBarcodeLoopId) {
                    cancelAnimationFrame(materialDoubleSelectBarcodeLoopId);
                    materialDoubleSelectBarcodeLoopId = null;
                }

                if (materialDoubleSelectBarcodeStream) {
                    materialDoubleSelectBarcodeStream.getTracks().forEach(track => track.stop());
                }

                materialDoubleSelectBarcodeStream = null;
                materialDoubleSelectBarcodeVideo = null;
                materialDoubleSelectBarcodeCanvas = null;
                materialDoubleSelectBarcodeOverlay = null;
                materialDoubleSelectBarcodeDetector = null;
                materialDoubleSelectBarcodeCandidates = [];
                materialDoubleSelectBarcodeDetecting = false;

                if (hadSelectStream) {
                    materialDoubleScanCameraRunning = false;
                }

                if (hadSelectStream && materialDoubleScannerMode !== 'select') {
                    const reader = document.getElementById('materialDoubleScanReader');
                    if (reader) reader.innerHTML = '';
                }
            }

            function materialDoubleSelectBarcodeTick() {
                if (!materialDoubleSelectBarcodeStream || !materialDoubleSelectBarcodeVideo || !materialDoubleSelectBarcodeDetector) return;

                if (materialDoubleSelectBarcodeDetecting || materialDoubleSelectBarcodeVideo.readyState < 2) {
                    materialDoubleSelectBarcodeLoopId = requestAnimationFrame(materialDoubleSelectBarcodeTick);
                    return;
                }

                materialDoubleSelectBarcodeDetecting = true;
                materialDoubleSelectBarcodeDetector.detect(materialDoubleSelectBarcodeVideo)
                    .then(detections => {
                        materialDoubleSelectBarcodeCandidates = detections
                            .filter(item => item.rawValue)
                            .map(item => ({
                                value: item.rawValue,
                                box: item.boundingBox || null
                            }));
                        drawMaterialDoubleSelectBarcodeOverlay();
                        renderMaterialDoubleSelectBarcodeChoices();
                    })
                    .catch(() => { })
                    .finally(() => {
                        materialDoubleSelectBarcodeDetecting = false;
                        materialDoubleSelectBarcodeLoopId = requestAnimationFrame(materialDoubleSelectBarcodeTick);
                    });
            }

            const BARCODE_COLORS = [
                { border: '#0b7bd3', fill: 'rgba(11, 123, 211, 0.16)', btn: '#0b7bd3' },
                { border: '#10b981', fill: 'rgba(16, 185, 129, 0.16)', btn: '#10b981' },
                { border: '#f59e0b', fill: 'rgba(245, 158, 11, 0.16)', btn: '#f59e0b' },
                { border: '#8b5cf6', fill: 'rgba(139, 92, 246, 0.16)', btn: '#8b5cf6' },
                { border: '#ef4444', fill: 'rgba(239, 68, 68, 0.16)', btn: '#ef4444' },
                { border: '#ec4899', fill: 'rgba(236, 72, 153, 0.16)', btn: '#ec4899' },
                { border: '#14b8a6', fill: 'rgba(20, 184, 166, 0.16)', btn: '#14b8a6' },
            ];

            function getBarcodeColor(value) {
                let hash = 0;
                for (let i = 0; i < value.length; i++) {
                    hash = value.charCodeAt(i) + ((hash << 5) - hash);
                }
                return BARCODE_COLORS[Math.abs(hash) % BARCODE_COLORS.length];
            }

            function drawMaterialDoubleSelectBarcodeOverlay() {
                if (!materialDoubleSelectBarcodeCanvas || !materialDoubleSelectBarcodeVideo) return;

                const width = materialDoubleSelectBarcodeVideo.videoWidth || materialDoubleSelectBarcodeVideo.clientWidth || 320;
                const height = materialDoubleSelectBarcodeVideo.videoHeight || materialDoubleSelectBarcodeVideo.clientHeight || 240;
                materialDoubleSelectBarcodeCanvas.width = width;
                materialDoubleSelectBarcodeCanvas.height = height;

                const ctx = materialDoubleSelectBarcodeCanvas.getContext('2d');
                ctx.clearRect(0, 0, width, height);
                ctx.lineWidth = 3;
                ctx.font = '700 16px Inter, sans-serif';

                materialDoubleSelectBarcodeCandidates.forEach((candidate, index) => {
                    const box = candidate.box || { x: 12, y: 12 + (index * 44), width: Math.min(width - 24, 280), height: 34 };
                    const color = getBarcodeColor(candidate.value);

                    ctx.strokeStyle = color.border;
                    ctx.fillStyle = color.fill;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);
                    ctx.fillRect(box.x, box.y, box.width, box.height);
                });
            }

            function renderMaterialDoubleSelectBarcodeChoices() {
                const hint = document.getElementById('materialDoubleSelectBarcodeCandidates');

                const uniqueValues = [...new Set(materialDoubleSelectBarcodeCandidates.map(candidate => candidate.value))];
                renderMaterialDoubleSelectBarcodeTapLabels(uniqueValues);

                if (!uniqueValues.length) {
                    if (hint) {
                        hint.innerHTML = '<div class="select-barcode-empty">Arahkan kamera ke barcode, lalu tap label barcode pada tampilan kamera.</div>';
                    }
                    return;
                }

                if (hint) {
                    hint.innerHTML = '<div class="select-barcode-empty">Barcode terdeteksi. Tap label barcode langsung pada tampilan kamera.</div>';
                }
            }

            function renderMaterialDoubleSelectBarcodeTapLabels(uniqueValues) {
                if (!materialDoubleSelectBarcodeOverlay || !materialDoubleSelectBarcodeCanvas) return;

                if (!uniqueValues.length) {
                    materialDoubleSelectBarcodeOverlay.innerHTML = '';
                    return;
                }

                const canvasRect = materialDoubleSelectBarcodeCanvas.getBoundingClientRect();
                const canvasWidth = materialDoubleSelectBarcodeCanvas.width || canvasRect.width || 1;
                const canvasHeight = materialDoubleSelectBarcodeCanvas.height || canvasRect.height || 1;
                const scaleX = canvasRect.width / canvasWidth;
                const scaleY = canvasRect.height / canvasHeight;
                const usedValues = new Set();

                materialDoubleSelectBarcodeOverlay.innerHTML = materialDoubleSelectBarcodeCandidates
                    .filter(candidate => {
                        if (usedValues.has(candidate.value)) return false;
                        usedValues.add(candidate.value);
                        return uniqueValues.includes(candidate.value);
                    })
                    .map((candidate, index) => {
                        const fallbackBox = {
                            x: 12,
                            y: 46 + (index * 40),
                            width: Math.min(canvasWidth - 24, 280),
                            height: 34
                        };
                        const box = candidate.box || fallbackBox;
                        const left = Math.max(8, Math.round(box.x * scaleX));
                        const top = Math.max(8, Math.round((box.y - 34) * scaleY));
                        const maxWidth = Math.max(96, Math.min(
                            Math.round((box.width || 180) * scaleX) + 28,
                            Math.round(canvasRect.width - left - 8)
                        ));

                        const color = getBarcodeColor(candidate.value);

                        return `
                        <button
                            class="select-barcode-tap-label mono"
                            type="button"
                            style="left:${left}px;top:${top}px;max-width:${maxWidth}px;background:${color.btn};border-color:${color.btn};"
                            onclick="materialDoubleSelectDetectedBarcode(decodeURIComponent('${encodeURIComponent(candidate.value)}'))">
                            ${escapeHtml(candidate.value)}
                        </button>
                    `;
                    })
                    .join('');
            }

            function handleMaterialDoubleSelectBarcodeCanvasClick(event) {
                if (!materialDoubleSelectBarcodeCanvas || !materialDoubleSelectBarcodeCandidates.length) return;

                const rect = materialDoubleSelectBarcodeCanvas.getBoundingClientRect();
                const x = (event.clientX - rect.left) * (materialDoubleSelectBarcodeCanvas.width / rect.width);
                const y = (event.clientY - rect.top) * (materialDoubleSelectBarcodeCanvas.height / rect.height);
                const selected = materialDoubleSelectBarcodeCandidates.find(candidate => {
                    if (!candidate.box) return false;

                    return x >= candidate.box.x
                        && x <= candidate.box.x + candidate.box.width
                        && y >= candidate.box.y - 30
                        && y <= candidate.box.y + candidate.box.height;
                });

                if (selected) {
                    materialDoubleSelectDetectedBarcode(selected.value);
                }
            }

            function materialDoubleSelectDetectedBarcode(value) {
                if (!value || materialDoubleScanLocked) return;

                $('#materialDoubleScanQr').val(value);
                submitMaterialDoubleScan(false, value, 'camera-select');
            }

            function submitMaterialDoubleScan(forceSave = false, qrText = null, source = 'manual') {
                if (!activeMaterialDoubleScanGroup || materialDoubleScanLocked) return;

                const qr = String(qrText || $('#materialDoubleScanQr').val() || '').trim();
                if (!qr) {
                    Swal.fire('QR kosong', 'Input QR final terlebih dahulu.', 'warning');
                    return;
                }

                materialDoubleScanLocked = true;

                fetch('{{ route("admin.api.material-double.scan") }}', {
                    method: 'POST',
                    headers: requestHeaders(),
                    body: JSON.stringify(Object.assign(groupPayload(activeMaterialDoubleScanGroup), {
                        qr,
                        scan_source: source,
                        force_save: !!forceSave
                    }))
                })
                    .then(async response => {
                        const payload = await response.json();
                        if (!response.ok) throw payload;
                        return payload;
                    })
                    .then(payload => {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: payload.message || 'Scan Material Double berhasil disimpan.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        closeMaterialDoubleScanModal();
                        reloadMaterialDouble();
                        reloadAllScanResultsTab();
                    })
                    .catch(error => {
                        if (error.duplicate) {
                            Swal.fire({
                                title: 'Warning',
                                html: error.message || 'Barcode sudah pernah discan sebelumnya. Tetap simpan?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Simpan',
                                cancelButtonText: 'Batal',
                                reverseButtons: true
                            }).then(result => {
                                materialDoubleScanLocked = false;
                                if (result.isConfirmed) {
                                    submitMaterialDoubleScan(true, qr, source);
                                } else {
                                    $('#materialDoubleScanQr').val('');
                                }
                            });
                            return;
                        }

                        Swal.fire('Gagal', error.message || 'Scan Material Double gagal.', 'error').then(() => {
                            materialDoubleScanLocked = false;
                            $('#materialDoubleScanQr').val('');
                        });
                    });
            }

            function reloadAllScanResultsTab() {
                if (!window.top || !window.top.document) return;

                const scanResultsUrl = '{{ route("admin.scan-results") }}';
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

            $(document).ready(function () {
                materialDoubleTable = $('#materialDoubleTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("admin.api.material-double") }}',
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
                                return '<span class="badge" style="background:#ffc107;color:#000;">Need Check</span>';
                            }
                        },
                        { data: null, orderable: false, searchable: false, render: row => renderAction(row) },
                    ],
                    language: { emptyTable: 'Tidak ada material double ditemukan.' }
                });

                $('#duplicateDetailTable').on('change', '.duplicate-select', function () {
                    const id = Number(this.value);
                    if (this.checked) {
                        selectedDuplicateIds.add(id);
                    } else {
                        selectedDuplicateIds.delete(id);
                    }
                });

                let materialDoubleManualScanTimeout = null;
                let materialDoubleLastKeyTime = Date.now();
                let materialDoubleTypingSpeedIsHuman = false;

                $('#materialDoubleScanQr').on('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        if (materialDoubleManualScanTimeout) clearTimeout(materialDoubleManualScanTimeout);
                        submitMaterialDoubleScan(false);
                        return;
                    }

                    if (event.key.length === 1) {
                        const now = Date.now();
                        const diff = now - materialDoubleLastKeyTime;
                        const val = event.target.value;
                        if (val.length > 0 && diff > 50) {
                            materialDoubleTypingSpeedIsHuman = true;
                        }
                        materialDoubleLastKeyTime = now;
                    }
                });

                $('#materialDoubleScanQr').on('input', function (event) {
                    if (materialDoubleManualScanTimeout) clearTimeout(materialDoubleManualScanTimeout);

                    const val = event.target.value.trim();

                    if (val.length === 0) {
                        materialDoubleTypingSpeedIsHuman = false;
                        return;
                    }

                    // Auto submit jika input dari scanner gun (cepat, bukan manusia)
                    if (val.length > 2 && !materialDoubleTypingSpeedIsHuman) {
                        materialDoubleManualScanTimeout = setTimeout(() => {
                            submitMaterialDoubleScan(false);
                        }, 250);
                    }
                });

                // Global listener untuk Scanner Gun di modal Material Double
                document.addEventListener('keydown', function (event) {
                    const modal = document.getElementById('materialDoubleScanModal');
                    if (!modal || !modal.classList.contains('active')) return;
                    if (document.body.classList.contains('swal2-shown') || materialDoubleScanLocked) return;

                    const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
                    if (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select') return;

                    if (event.ctrlKey || event.altKey || event.metaKey) return;

                    const qrInput = document.getElementById('materialDoubleScanQr');
                    if (qrInput && event.key.length === 1) {
                        qrInput.focus();
                    }
                });

                updateExportLinks();
            });

    // --- Searchable Modal JS Logic ---
    
    const materialSelect = document.getElementById('filterMaterial');
    const materialFilterTrigger = document.getElementById('materialFilterTrigger');
    const materialFilterModal = document.getElementById('materialFilterModalContainer');
    const materialFilterSearchInput = document.getElementById('materialFilterSearchInput');
    const materialFilterList = document.getElementById('materialFilterList');

    function syncMaterialFilterList() {
        if (!materialFilterList) return;
        materialFilterList.innerHTML = '';
        let selectedText = 'All';

        Array.from(materialSelect.options).forEach(opt => {
            const id = opt.value;
            const name = opt.text;
            const isActive = opt.selected;
            if (isActive) selectedText = name;
            
            const div = document.createElement('div');
            div.className = 'custom-filter-item' + (isActive ? ' active' : '');
            div.setAttribute('data-id', id);
            div.setAttribute('data-name', name);
            div.onclick = function() { selectMaterialFilter(id, name); };
            div.textContent = name;
            materialFilterList.appendChild(div);
        });
        
        materialFilterTrigger.textContent = selectedText;
    }

    function openMaterialFilterModal() {
        materialFilterModal.style.display = 'flex';
        materialFilterSearchInput.value = '';
        filterMaterials('');
        materialFilterSearchInput.focus();

        setTimeout(() => {
            const activeItem = materialFilterModal.querySelector('.custom-filter-item.active');
            if (activeItem) activeItem.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }, 50);
    }

    function closeMaterialFilterModal() {
        materialFilterModal.style.display = 'none';
    }

    function selectMaterialFilter(id, name) {
        materialSelect.value = id;
        materialFilterTrigger.textContent = name;
        
        const items = materialFilterModal.querySelectorAll('.custom-filter-item');
        items.forEach(item => {
            if (item.getAttribute('data-id') === String(id)) item.classList.add('active');
            else item.classList.remove('active');
        });

        closeMaterialFilterModal();
        materialSelect.dispatchEvent(new Event('change'));
    }

    function filterMaterials(query) {
        const lowerQuery = query.toLowerCase();
        const items = materialFilterModal.querySelectorAll('.custom-filter-item');
        items.forEach(item => {
            const name = item.getAttribute('data-name').toLowerCase();
            if (name.includes(lowerQuery)) item.style.display = 'block';
            else item.style.display = 'none';
        });
    }

    if (materialFilterModal) {
        materialFilterModal.addEventListener('click', function(e) {
            if (e.target === this) closeMaterialFilterModal();
        });
        syncMaterialFilterList();
    }

    const locationSelect = document.getElementById('filterLocation');
    const locationFilterTrigger = document.getElementById('locationFilterTrigger');
    const locationFilterModal = document.getElementById('locationFilterModalContainer');
    const locationFilterSearchInput = document.getElementById('locationFilterSearchInput');
    const locationFilterList = document.getElementById('locationFilterList');

    function syncLocationFilterList() {
        if (!locationFilterList) return;
        locationFilterList.innerHTML = '';
        let selectedText = 'All';

        Array.from(locationSelect.options).forEach(opt => {
            const id = opt.value;
            const name = opt.text;
            const isActive = opt.selected;
            if (isActive) selectedText = name;
            
            const div = document.createElement('div');
            div.className = 'custom-filter-item' + (isActive ? ' active' : '');
            div.setAttribute('data-id', id);
            div.setAttribute('data-name', name);
            div.onclick = function() { selectLocationFilter(id, name); };
            div.textContent = name;
            locationFilterList.appendChild(div);
        });
        
        locationFilterTrigger.textContent = selectedText;
    }

    function openLocationFilterModal() {
        locationFilterModal.style.display = 'flex';
        locationFilterSearchInput.value = '';
        filterLocations('');
        locationFilterSearchInput.focus();

        setTimeout(() => {
            const activeItem = locationFilterModal.querySelector('.custom-filter-item.active');
            if (activeItem) activeItem.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }, 50);
    }

    function closeLocationFilterModal() {
        locationFilterModal.style.display = 'none';
    }

    function selectLocationFilter(id, name) {
        locationSelect.value = id;
        locationFilterTrigger.textContent = name;
        
        const items = locationFilterModal.querySelectorAll('.custom-filter-item');
        items.forEach(item => {
            if (item.getAttribute('data-id') === String(id)) item.classList.add('active');
            else item.classList.remove('active');
        });

        closeLocationFilterModal();
        locationSelect.dispatchEvent(new Event('change'));
    }

    function filterLocations(query) {
        const lowerQuery = query.toLowerCase();
        const items = locationFilterModal.querySelectorAll('.custom-filter-item');
        items.forEach(item => {
            const name = item.getAttribute('data-name').toLowerCase();
            if (name.includes(lowerQuery)) item.style.display = 'block';
            else item.style.display = 'none';
        });
    }

    if (locationFilterModal) {
        locationFilterModal.addEventListener('click', function(e) {
            if (e.target === this) closeLocationFilterModal();
        });
        syncLocationFilterList();
    }

    // On Plant change, filter the locations
    document.getElementById('filterPlant').addEventListener('change', function() {
        const plantId = this.value;
        
        if (!plantId) {
            // reset to all locations
            fetch('{{ route('api.locations') }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(locations => {
                locationSelect.innerHTML = '<option value="">All</option>';
                locations.forEach(loc => {
                    const opt = document.createElement('option');
                    opt.value = loc.name;
                    opt.textContent = loc.name;
                    locationSelect.appendChild(opt);
                });
                syncLocationFilterList();
            });
        } else {
            fetch('{{ route('api.locations') }}?plant_id=' + plantId, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(locations => {
                locationSelect.innerHTML = '<option value="">All</option>';
                locations.forEach(loc => {
                    const opt = document.createElement('option');
                    opt.value = loc.name;
                    opt.textContent = loc.name;
                    locationSelect.appendChild(opt);
                });
                syncLocationFilterList();
            });
        }
    });
</script>
@endpush

</x-layouts.app>