<x-layouts.app :title="'Scanner'">

    @push('styles')
    <style>
        .scanner-info-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(180px, 0.65fr);
            gap: 12px;
            align-items: start;
        }
        .scanner-info-column {
            display: grid;
            gap: 6px;
            min-width: 0;
        }
        .scanner-info-item {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            font-size: 12px;
        }
        .scanner-info-label {
            min-width: 62px;
            color: var(--text-secondary);
            font-weight: 700;
        }
        .scanner-info-value {
            min-width: 0;
            color: var(--text);
            font-weight: 600;
        }
        .scanner-location-select {
            border: none;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
            color: var(--primary);
            font-weight: 700;
            padding: 0;
            cursor: pointer;
            outline: none;
            min-width: 0;
            max-width: 100%;
        }
        .recent-main-line {
            display: grid;
            grid-template-columns: 30px max-content;
            column-gap: 8px;
            align-items: baseline;
        }
        .recent-number {
            min-width: 30px;
            color: var(--text);
            font-weight: 700;
            font-size: 12px;
        }
        .recent-barcode {
            min-width: 0;
            white-space: nowrap;
            overflow: visible;
            text-overflow: clip;
        }
        .recent-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
            gap: 4px;
            flex: 0 0 auto;
            min-width: 90px;
        }
        .recent-actions-main {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }
        .recent-action-meta {
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        html.is-iframe .recent-main-line {
            display: grid;
            grid-template-columns: 38px max-content;
            column-gap: 8px;
            align-items: baseline;
        }
        html.is-iframe .recent-number {
            grid-column: 1;
            padding-top: 1px;
        }
        html.is-iframe .recent-barcode {
            grid-column: 2;
            min-width: 0;
            overflow: visible;
            text-overflow: clip;
            white-space: nowrap;
        }
        html.is-iframe .recent-row .recent-detail {
            padding-left: 46px !important;
        }
        @media (max-width: 768px) {
            /* --- Info bar: keep left/right split on mobile --- */
            .scanner-info-grid {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
                gap: 10px !important;
            }
            .scanner-info-column { gap: 5px; }
            .scanner-info-item {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr);
                align-items: baseline;
                gap: 4px;
                font-size: 12px;
            }
            .scanner-info-label {
                min-width: 0;
                white-space: nowrap;
            }
            .scanner-info-value {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .scanner-info-column-right .scanner-info-item {
                grid-template-columns: auto minmax(0, 1fr);
            }
            .scanner-info-column-right .scanner-info-value,
            .scanner-info-column-right .scanner-location-select {
                justify-self: start;
            }

            /* --- Scan QR header: stack button below title --- */
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

            /* --- QR Input: full-width, save below --- */
            .qr-input-row {
                flex-direction: column !important;
                gap: 8px !important;
            }
            .qr-input-row .btn {
                width: 100%;
                justify-content: center;
                min-height: 44px;
            }
            .qr-input-row .form-control {
                font-size: 15px;
            }

            /* --- Recent scan rows: text wrapping --- */
            .recent-row .mono {
                word-break: normal;
            }
            .recent-row .recent-detail {
                word-break: break-word;
                white-space: normal;
            }
            .recent-main-line {
                display: grid;
                grid-template-columns: 38px max-content;
                column-gap: 8px;
                align-items: baseline;
            }
            .recent-number {
                grid-column: 1;
                padding-top: 1px;
            }
            .recent-barcode {
                grid-column: 2;
                min-width: 0;
                word-break: normal;
                white-space: nowrap;
                overflow: visible;
                text-overflow: clip;
            }
            .recent-row .recent-detail {
                padding-left: 46px !important;
            }
            .recent-actions {
                min-width: 82px;
            }
            .recent-action-meta {
                font-size: 10.5px;
            }
            .recent-pagination {
                flex-direction: column;
                align-items: stretch !important;
                gap: 8px !important;
            }
            .recent-pagination .btn {
                width: 100%;
                justify-content: center;
            }

            /* --- Delete icon: bigger tap area --- */
            .recent-row .btn-icon {
                min-width: 40px;
                min-height: 40px;
                padding: 8px !important;
            }
            .recent-row .btn-icon svg {
                width: 18px;
                height: 18px;
            }

            /* --- Camera reader: taller --- */
            #reader {
                min-height: 280px !important;
            }

            /* --- Location select in info bar --- */
            .scanner-location-select {
                display: inline-block;
                width: auto;
                min-width: 70px;
                font-size: 13px;
                padding: 0;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }
    </style>
    @endpush

    <div class="card" style="margin-bottom: 10px; border-left: 3px solid var(--primary);">
                    <div class="scanner-info-grid">
                        <div class="scanner-info-column">
                <div class="scanner-info-item">
                    <span class="scanner-info-label">STO:</span>
                    <span class="scanner-info-value">{{ $activeSto->code }}</span>
                </div>
                <div class="scanner-info-item">
                    <span class="scanner-info-label">PIC:</span>
                    <span class="scanner-info-value">{{ auth()->user()->name }}</span>
                </div>
                <div class="scanner-info-item">
                    <span class="scanner-info-label">Plant:</span>
                    <span class="scanner-info-value" id="plantNameDisplay">{{ $plant->name }}</span>
                </div>
            </div>
                        <div class="scanner-info-column scanner-info-column-right">
                <div class="scanner-info-item">
                    <span class="scanner-info-label">Location:</span>
                    <select id="locationId" class="scanner-location-select" onchange="updateLocationSession(this.value)">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loc->id === $location->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="scanner-info-item">
                    <span class="scanner-info-label">Total Scan per Rak:</span>
                    <span class="scanner-info-value" id="counterToday">{{ $totalToday }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:12px;">
        <div class="scan-header" style="display:flex;justify-content:space-between;gap:8px;align-items:center;margin-bottom:8px;">
            <div class="card-title" style="margin:0;">Scan QR / Barcode</div>
            <div class="camera-buttons" style="display:flex;gap:6px;">
                <button class="btn" type="button" id="showCameraBtn" onclick="showCamera()">Show Camera</button>
                <button class="btn" type="button" id="hideCameraBtn" onclick="hideCamera()" style="display:none;">Hide
                    Camera</button>
            </div>
        </div>

        <div id="readerWrap" style="display:none;margin-bottom:10px;">
            <div id="reader" style="min-height:220px;border:1px solid var(--border);background:#fafbfc;"></div>
        </div>

        <div class="form-group">
            <label class="form-label" for="qrInput">Manual / Scanner Gun Input</label>
            <div class="qr-input-row" style="display:flex;gap:6px;">
                <input id="qrInput" class="form-control mono" placeholder="Masukkan nomor QR code atau barcode di sini"
                    autocomplete="off">
                <button class="btn btn-primary" type="button" onclick="submitScan(false)">Save</button>
            </div>
        </div>

        <input type="hidden" id="plantId" value="{{ $plant->id }}">
    </div>

    <div class="card">
        <div class="card-title">Hasil Scan Terbaru</div>
        <div id="recentList">
            @forelse($recentScans as $scan)
                @php($recentNumber = $recentMeta['total'] - (($recentMeta['page'] - 1) * $recentMeta['per_page']) - $loop->index)
                <div class="recent-row" id="scan-row-{{ $scan->id }}"
                    style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--border-light);">
                    <div style="flex:1;min-width:0;">
                        <div class="recent-main-line">
                            <span class="recent-number">{{ $recentNumber }}</span>
                            <span class="recent-barcode mono" style="font-weight:700;color:var(--primary);">{{ $scan->barcode_material }}</span>
                        </div>
                        <div class="recent-detail" style="font-size:11px;color:var(--text-secondary);padding-left:38px;">
                                    {{ $scan->material_name }} - {{ $scan->shape_name }} - {{ $scan->size }} - {{ $scan->qty }} pcs - {{ $scan->lot_number }}
                        </div>
                    </div>
                    <div class="recent-actions">
                        <div class="recent-actions-main">
                            <span
                                class="badge {{ $scan->keterangan === 'OK' ? 'badge-valid' : 'badge-invalid' }}">{{ $scan->keterangan }}</span>
                            <button class="btn-icon" style="color:var(--danger);padding:0 4px;" type="button"
                                onclick="confirmDeleteScan({{ $scan->id }}, '{{ $scan->barcode_material }}')" title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                        <div class="recent-action-meta mono">{{ $scan->location?->name }} &bull; {{ $scan->created_at?->format('H:i:s') }}</div>
                    </div>
                </div>
            @empty
                <div id="emptyRecent" style="padding:16px;color:var(--text-muted);text-align:center;">Belum ada hasil scan.
                </div>
            @endforelse
        </div>
        <div id="recentPagination" class="recent-pagination"
            style="display:{{ $recentMeta['total'] > 0 ? 'flex' : 'none' }}; flex-direction:row !important; flex-wrap:nowrap !important; justify-content:space-between; align-items:center; padding-top:16px; border-top:1px solid var(--border-light); margin-top:8px;">
            <div style="font-size:13px; color:var(--text-secondary); font-weight:500; white-space:nowrap; flex-shrink:1; overflow:hidden; text-overflow:ellipsis;">
                <span id="recentPageInfo">Page {{ $recentMeta['page'] }} of {{ max($recentMeta['last_page'], 1) }}</span>
            </div>
            <div style="display:flex; flex-direction:row; gap:8px; flex-shrink:0;">
                <button class="btn" type="button" id="recentPrevBtn" onclick="loadRecent(currentRecentPage - 1)" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; min-height: 36px; padding: 0; border-radius: 8px; background: #fff; border: 1px solid var(--border); color: var(--text);">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button class="btn" type="button" id="recentNextBtn" onclick="loadRecent(currentRecentPage + 1)" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; min-height: 36px; padding: 0; border-radius: 8px; background: #fff; border: 1px solid var(--border); color: var(--text);">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>
    </div>



    @push('scripts')
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script>
            let pendingQr = '';
            let pendingSource = 'manual';
            let scanningLocked = false;
            let html5Scanner = null;
            let cameraRunning = false;
            let currentRecentPage = {{ $recentMeta['page'] }};
            let recentLastPage = {{ max($recentMeta['last_page'], 1) }};
            const initialRecentMeta = @json($recentMeta);

            function updateLocationSession(newLocationId) {
                const plantId = document.getElementById('plantId').value;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                fetch('{{ route("scan.setup.store", [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        plant_id: plantId,
                        location_id: newLocationId
                    })
                })
                    .then(async response => {
                        const payload = await response.json();
                        if (!response.ok) throw payload;
                        return payload;
                    })
                    .then(() => {
                        loadRecent(1);
                        document.getElementById('qrInput').focus();
                    })
                    .catch(error => {
                        showToast(error.message || 'Gagal mengganti filter location.', 'error');
                    });
            }

            window.updateSetupData = function(newPlantId, newPlantName, newLocationId, locationsHtml) {
                document.getElementById('plantId').value = newPlantId;
                
                const plantNameDisplay = document.getElementById('plantNameDisplay');
                if (plantNameDisplay) plantNameDisplay.textContent = newPlantName;

                const locationSelect = document.getElementById('locationId');
                if (locationSelect && locationsHtml) {
                    locationSelect.innerHTML = locationsHtml;
                    locationSelect.value = newLocationId;
                } else if (locationSelect) {
                    locationSelect.value = newLocationId;
                }

                if (typeof loadRecent === 'function') {
                    loadRecent(1);
                }
            };

            function submitScan(forceSave = false, qrText = null, source = 'manual') {
                if (scanningLocked) return;
                scanningLocked = true;

                const qrInput = document.getElementById('qrInput');
                const qr = (qrText || qrInput.value).trim();
                if (!qr) {
                    scanningLocked = false;
                    return;
                }

                pendingQr = qr;
                pendingSource = source;

                fetch('{{ route("api.scan.store", [], false) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({
                        qr,
                        plant_id: document.getElementById('plantId').value,
                        location_id: document.getElementById('locationId').value,
                        scan_source: source,
                        force_save: forceSave
                    })
                })
                    .then(async response => {
                        const payload = await response.json();
                        if (!response.ok) throw payload;
                        return payload;
                    })
                    .then(payload => {

                        document.getElementById('counterToday').textContent = String(parseInt(document.getElementById('counterToday').textContent || '0') + 1);
                        loadRecent(1);
                        qrInput.value = '';
                        qrInput.focus();
                        
                        showToast(payload.message || 'Scan berhasil disimpan.', 'success');
                        scanningLocked = false;
                    })
                    .catch(error => {
                        if (error.duplicate) {
                            const swal = window.top.Swal || Swal;
                            swal.fire({
                                title: 'Warning',
                                html: error.message || 'Barcode sudah pernah discan sebelumnya. Tetap simpan?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Simpan',
                                cancelButtonText: 'Batal',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    scanningLocked = false;
                                    confirmDuplicate();
                                } else {
                                    qrInput.value = '';
                                    qrInput.focus();
                                    scanningLocked = false;
                                }
                            });
                            return;
                        }
                        const swal = window.top.Swal || Swal;
                        swal.fire({
                            title: 'Gagal',
                            text: error.message || 'Scan gagal.',
                            icon: 'error',
                            confirmButtonColor: '#2b2d30',
                            confirmButtonText: 'Tutup'
                        }).then(() => {
                            qrInput.value = '';
                            qrInput.focus();
                            scanningLocked = false;
                        });
                    });
            }

            function confirmDuplicate() {
                submitScan(true, pendingQr, pendingSource);
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

            function recentSummary(data) {
                return `${data.material_name} - ${data.shape_name} - ${data.display_size} - ${data.qty} pcs - ${data.lot_number}`;
            }

            function recentTime(value) {
                const match = String(value ?? '').match(/(\d{2}:\d{2}:\d{2})/);
                return match ? match[1] : value;
            }

            function recentRowHtml(data, rowNumber) {
                const statusClass = data.keterangan === 'OK' ? 'badge-valid' : 'badge-invalid';

                return `
                <div class="recent-row" id="scan-row-${data.id}" style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--border-light);">
                    <div style="flex:1;min-width:0;">
                        <div class="recent-main-line">
                            <span class="recent-number">${rowNumber}</span>
                            <span class="recent-barcode mono" style="font-weight:700;color:var(--primary);">${escapeHtml(data.barcode_material)}</span>
                        </div>
                        <div class="recent-detail" style="font-size:11px;color:var(--text-secondary);padding-left:38px;">${escapeHtml(recentSummary(data))}</div>
                    </div>
                    <div class="recent-actions">
                        <div class="recent-actions-main">
                            <span class="badge ${statusClass}">${escapeHtml(data.keterangan)}</span>
                            <button class="btn-icon" style="color:var(--danger);padding:0 4px;" type="button" onclick="confirmDeleteScan(${data.id}, '${escapeHtml(data.barcode_material)}')" title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                            </button>
                        </div>
                        <div class="recent-action-meta mono">${escapeHtml(data.location)} &bull; ${escapeHtml(recentTime(data.created_at))}</div>
                    </div>
                </div>
            `;
            }

            function renderRecentRows(rows, meta) {
                const recentList = document.getElementById('recentList');

                if (!rows.length) {
                    recentList.innerHTML = '<div id="emptyRecent" style="padding:16px;color:var(--text-muted);text-align:center;">Belum ada hasil scan.</div>';
                    return;
                }

                recentList.innerHTML = rows.map((row, index) => {
                    const rowNumber = (meta.total || 0) - (((meta.page || 1) - 1) * (meta.per_page || 50)) - index;
                    return recentRowHtml(row, rowNumber);
                }).join('');
            }

            function updateRecentPagination(meta) {
                currentRecentPage = meta.page || 1;
                recentLastPage = Math.max(meta.last_page || 1, 1);

                const pagination = document.getElementById('recentPagination');
                const pageInfo = document.getElementById('recentPageInfo');
                const prevBtn = document.getElementById('recentPrevBtn');
                const nextBtn = document.getElementById('recentNextBtn');

                pagination.style.display = meta.total > 0 ? 'flex' : 'none';
                pageInfo.textContent = `Page ${currentRecentPage} of ${recentLastPage}`;
                prevBtn.disabled = currentRecentPage <= 1;
                nextBtn.disabled = currentRecentPage >= recentLastPage;
            }

            function loadRecent(page = 1) {
                const targetPage = Math.max(parseInt(page, 10) || 1, 1);

                fetch(`{{ route("api.scan.recent", [], false) }}?page=${targetPage}`, {
                    headers: { Accept: 'application/json' }
                })
                    .then(async response => {
                        const payload = await response.json();
                        if (!response.ok) throw payload;
                        return payload;
                    })
                    .then(payload => {
                        if (!payload.data.length && payload.meta.total > 0 && payload.meta.page > payload.meta.last_page) {
                            loadRecent(payload.meta.last_page);
                            return;
                        }

                        renderRecentRows(payload.data, payload.meta);
                        updateRecentPagination(payload.meta);
                        
                        if (payload.total_today !== undefined) {
                            const counter = document.getElementById('counterToday');
                            if (counter) counter.textContent = payload.total_today;
                        }
                    })
                    .catch(error => {
                        showToast(error.message || 'Gagal memuat hasil scan terbaru.', 'error');
                    });
            }

            let scanToDelete = null;

            function confirmDeleteScan(id, barcode) {
                scanToDelete = id;
                const swal = window.top.Swal || Swal;
                swal.fire({
                    title: 'Hapus Scan?',
                    html: `Apakah Anda yakin ingin menghapus data scan <b>${escapeHtml(barcode)}</b>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#b92525',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        performDelete();
                    } else {
                        scanToDelete = null;
                        document.getElementById('qrInput').focus();
                    }
                });
            }

            function performDelete() {
                if (!scanToDelete) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const idToDel = scanToDelete;

                fetch(`/api/scan/${idToDel}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) throw data;
                        return data;
                    })
                    .then(data => {
                        showToast(data.message);
                        const counter = document.getElementById('counterToday');
                        if (counter && parseInt(counter.textContent) > 0) {
                            counter.textContent = String(parseInt(counter.textContent) - 1);
                        }
                        loadRecent(currentRecentPage);
                    })
                    .catch(error => {
                        showToast(error.message || 'Gagal menghapus data', 'error');
                    });
            }

            function renderCameraError(message) {
                document.getElementById('reader').innerHTML = `<div style="padding:32px;text-align:center;color:var(--text-muted);">${escapeHtml(message)}</div>`;
                showToast(message, 'error');
            }

            function showCamera() {
                document.getElementById('readerWrap').style.display = 'block';
                document.getElementById('showCameraBtn').style.display = 'none';
                document.getElementById('hideCameraBtn').style.display = 'inline-flex';

                if (!window.isSecureContext) {
                    renderCameraError('Kamera hanya bisa dipakai melalui HTTPS atau localhost. Gunakan URL HTTPS untuk testing di HP.');
                    return;
                }

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    renderCameraError('Browser tidak menyediakan akses kamera. Gunakan input manual atau scanner gun.');
                    return;
                }

                if (!window.Html5Qrcode) {
                    renderCameraError('Library kamera belum tersedia. Periksa koneksi internet atau buka ulang halaman.');
                    return;
                }

                if (!html5Scanner) {
                    html5Scanner = new Html5Qrcode('reader');
                }

                if (cameraRunning) return;

                html5Scanner.start(
                    { facingMode: 'environment' },
                    { fps: 8, qrbox: { width: 220, height: 220 } },
                    decodedText => {
                        if (scanningLocked) return;
                        document.getElementById('qrInput').value = decodedText;
                        submitScan(false, decodedText, 'camera');
                    },
                    () => { }
                ).then(() => {
                    cameraRunning = true;
                }).catch(error => {
                    let message = 'Kamera tidak tersedia. Gunakan input manual atau scanner gun.';

                    if (error && error.name === 'NotAllowedError') {
                        message = 'Izin kamera ditolak. Aktifkan permission kamera di browser.';
                    } else if (error && error.name === 'NotFoundError') {
                        message = 'Kamera tidak ditemukan di perangkat ini.';
                    } else if (error && error.name === 'NotReadableError') {
                        message = 'Kamera sedang dipakai aplikasi lain atau tidak bisa dibuka.';
                    }

                    renderCameraError(message);
                });
            }

            function hideCamera() {
                document.getElementById('readerWrap').style.display = 'none';
                document.getElementById('showCameraBtn').style.display = 'inline-flex';
                document.getElementById('hideCameraBtn').style.display = 'none';

                if (!html5Scanner || !cameraRunning) return;

                html5Scanner.stop().then(() => {
                    cameraRunning = false;
                }).catch(() => {
                    cameraRunning = false;
                });
            }

            let manualScanTimeout = null;
            document.getElementById('qrInput').addEventListener('input', event => {
                const val = event.target.value.trim();
                // Format QR is `<barcode>|<lot>|<qty>` so it must contain '|'
                if (val.includes('|') && val.length > 5) {
                    if (manualScanTimeout) clearTimeout(manualScanTimeout);
                    manualScanTimeout = setTimeout(() => {
                        submitScan(false);
                    }, 200);
                }
            });

            document.getElementById('qrInput').addEventListener('keydown', event => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (manualScanTimeout) clearTimeout(manualScanTimeout);
                    submitScan(false);
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                updateRecentPagination(initialRecentMeta);
                document.getElementById('qrInput').focus();
            });
        </script>
    @endpush

</x-layouts.app>
