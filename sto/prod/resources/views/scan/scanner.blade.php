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
            text-align: left;
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
        .select-barcode-candidate {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            min-height: 32px;
            border: 1px solid var(--border-light);
            background: #fafbfc;
            color: var(--primary);
            font-weight: 700;
            padding: 0 8px;
            cursor: pointer;
        }
        .select-barcode-empty {
            color: var(--text-secondary);
            font-size: 12px;
            padding: 6px 0;
            text-align: center;
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
            .scanner-mode-toggle {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
            }
            .scanner-mode-toggle button {
                width: 100%;
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

        /* --- Location Filter Modal --- */
        .location-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .location-modal-content {
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
            .location-modal-overlay {
                align-items: center;
            }
            .location-modal-content {
                border-radius: 12px;
                max-height: 80vh;
                animation: fadeIn 0.2s ease-out;
            }
        }
        .location-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid var(--border-light);
        }
        .btn-close-modal {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 4px;
        }
        .location-modal-search {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-light);
            background: #fafbfc;
        }
        .location-modal-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px 16px 16px 16px;
        }
        .location-modal-item {
            padding: 12px;
            border-bottom: 1px solid var(--border-light);
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            transition: background 0.1s;
        }
        .location-modal-item:last-child {
            border-bottom: none;
        }
        .location-modal-item:active {
            background: #f0f0f0;
        }
        .location-modal-item.active {
            background: var(--primary);
            color: #fff;
            border-radius: 6px;
            border-bottom: none;
            margin-bottom: 1px;
        }
        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
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
                    <button type="button" id="locationTrigger" class="scanner-location-select" onclick="openLocationModal()">{{ $location->name }}</button>
                    <input type="hidden" id="locationId" value="{{ $location->id }}">
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
            <div class="scanner-mode-toggle" role="group" aria-label="Scanner Mode">
                <button type="button" id="autoScanModeBtn" class="active" onclick="setScannerMode('auto')">Auto Scan</button>
                <button type="button" id="selectBarcodeModeBtn" onclick="setScannerMode('select')">Select Barcode</button>
            </div>
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



    <div id="locationFilterModal" class="location-modal-overlay" style="display: none;">
        <div class="location-modal-content">
            <div class="location-modal-header">
                <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Select Location</h3>
                <button type="button" class="btn-close-modal" onclick="closeLocationModal()">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="location-modal-search">
                <input type="text" id="locationSearchInput" class="form-control" placeholder="Cari lokasi..." oninput="filterLocations(this.value)">
            </div>
            <div class="location-modal-list" id="locationModalList">
                @foreach($locations as $loc)
                    <div class="location-modal-item {{ $loc->id === $location->id ? 'active' : '' }}" 
                         data-id="{{ $loc->id }}" 
                         data-name="{{ $loc->name }}"
                         onclick="selectLocation('{{ $loc->id }}', '{{ $loc->name }}')">
                        {{ $loc->name }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
        <script>
            let pendingQr = '';
            let pendingSource = 'manual';
            let scanningLocked = false;
            let html5Scanner = null;
            let cameraRunning = false;
            let scannerMode = 'auto';
            let selectBarcodeDetector = null;
            let selectBarcodeStream = null;
            let selectBarcodeVideo = null;
            let selectBarcodeCanvas = null;
            let selectBarcodeOverlay = null;
            let selectBarcodeLoopId = null;
            let selectBarcodeDetecting = false;
            let selectBarcodeCandidates = [];
            let currentRecentPage = {{ $recentMeta['page'] }};
            let recentLastPage = {{ max($recentMeta['last_page'], 1) }};
            const initialRecentMeta = @json($recentMeta);
            // Tracks barcodes flagged as duplicates — always show warning, never auto-submit
            const duplicateFlaggedBarcodes = new Set();

            // --- Location Filter Logic ---
            function openLocationModal() {
                const modal = document.getElementById('locationFilterModal');
                modal.style.display = 'flex';
                const input = document.getElementById('locationSearchInput');
                input.value = '';
                filterLocations('');
                input.focus();

                setTimeout(() => {
                    const activeItem = modal.querySelector('.location-modal-item.active');
                    if (activeItem) {
                        activeItem.scrollIntoView({ block: 'center', behavior: 'smooth' });
                    }
                }, 50);
            }

            function closeLocationModal() {
                document.getElementById('locationFilterModal').style.display = 'none';
            }

            function selectLocation(id, name) {
                document.getElementById('locationId').value = id;
                document.getElementById('locationTrigger').textContent = name;
                
                const items = document.querySelectorAll('.location-modal-item');
                items.forEach(item => {
                    if (item.getAttribute('data-id') === String(id)) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });

                closeLocationModal();
                updateLocationSession(id);
            }

            function filterLocations(query) {
                const lowerQuery = query.toLowerCase();
                const items = document.querySelectorAll('.location-modal-item');
                items.forEach(item => {
                    const name = item.getAttribute('data-name').toLowerCase();
                    if (name.includes(lowerQuery)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }

            document.getElementById('locationFilterModal').addEventListener('click', function(e) {
                if (e.target === this) closeLocationModal();
            });

            function updateLocationSession(newLocationId) {
                const plantId = document.getElementById('plantId').value;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                fetch('{{ route("scan.setup.store") }}', {
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

                const locationIdInput = document.getElementById('locationId');
                if (locationIdInput) {
                    locationIdInput.value = newLocationId;
                }

                if (locationsHtml) {
                    const temp = document.createElement('select');
                    temp.innerHTML = locationsHtml;
                    const listContainer = document.getElementById('locationModalList');
                    if (listContainer) {
                        listContainer.innerHTML = '';
                        let selectedName = '';
                        Array.from(temp.options).forEach(opt => {
                            const id = opt.value;
                            const name = opt.text;
                            const isActive = (id == newLocationId);
                            if (isActive) selectedName = name;
                            
                            const div = document.createElement('div');
                            div.className = 'location-modal-item' + (isActive ? ' active' : '');
                            div.setAttribute('data-id', id);
                            div.setAttribute('data-name', name);
                            div.onclick = function() { selectLocation(id, name); };
                            div.textContent = name;
                            listContainer.appendChild(div);
                        });
                        
                        const trigger = document.getElementById('locationTrigger');
                        if (trigger && selectedName) trigger.textContent = selectedName;
                    }
                }

                if (typeof loadRecent === 'function') {
                    loadRecent(1);
                }
            };

            function submitScan(forceSave = false, qrText = null, source = 'manual') {
                if (scanningLocked) return;
                scanningLocked = true;
                typingSpeedIsHuman = false;

                const qrInput = document.getElementById('qrInput');
                const qr = (qrText || qrInput.value).trim();
                if (!qr) {
                    scanningLocked = false;
                    return;
                }

                pendingQr = qr;
                pendingSource = source;

                fetch('{{ route("api.scan.store") }}', {
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
                        // Scan saved successfully — remove from flagged set
                        duplicateFlaggedBarcodes.delete(qr);

                        document.getElementById('counterToday').textContent = String(parseInt(document.getElementById('counterToday').textContent || '0') + 1);
                        loadRecent(1);
                        qrInput.value = '';
                        qrInput.focus();
                        
                        showToast(payload.message || 'Scan berhasil disimpan.', 'success');
                        scanningLocked = false;
                    })
                    .catch(error => {
                        if (error.duplicate) {
                            // Mark this barcode as flagged — future scans always show warning
                            duplicateFlaggedBarcodes.add(qr);
                            // Clear the input BEFORE showing SweetAlert so gun scanner
                            // cannot re-fire auto-submit while the dialog is open
                            qrInput.value = '';
                            scanningLocked = false;

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
                                    confirmDuplicate();
                                } else {
                                    // User cancelled — keep the barcode flagged, clear focus
                                    qrInput.focus();
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

            // Show duplicate warning popup for a barcode that is already flagged
            function showDuplicateWarningFor(barcode) {
                const swal = window.top.Swal || Swal;
                const qrInput = document.getElementById('qrInput');
                qrInput.value = '';
                swal.fire({
                    title: 'Warning',
                    html: `Barcode <b>${barcode}</b> sudah pernah discan sebelumnya. Tetap simpan?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        pendingQr = barcode;
                        confirmDuplicate();
                    } else {
                        qrInput.focus();
                    }
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

                fetch(`{{ route("api.scan.recent") }}?page=${targetPage}`, {
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

            function setScannerMode(mode) {
                if (!['auto', 'select'].includes(mode) || scannerMode === mode) return;

                const readerVisible = document.getElementById('readerWrap').style.display !== 'none';
                hideCamera();
                scannerMode = mode;
                updateScannerModeButtons();

                if (readerVisible) {
                    setTimeout(() => showCamera(), 180);
                }
            }

            function updateScannerModeButtons() {
                document.getElementById('autoScanModeBtn')?.classList.toggle('active', scannerMode === 'auto');
                document.getElementById('selectBarcodeModeBtn')?.classList.toggle('active', scannerMode === 'select');
            }

            let lastScannedText = '';
            let lastScannedTime = 0;

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

                if (scannerMode === 'select') {
                    showSelectBarcodeCamera();
                    return;
                }

                if (!window.Html5Qrcode) {
                    renderCameraError('Library kamera belum tersedia. Periksa asset aplikasi atau buka ulang halaman.');
                    return;
                }

                if (!html5Scanner) {
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

                    html5Scanner = new Html5Qrcode('reader', {
                        formatsToSupport: formats,
                        verbose: false,
                        useBarCodeDetectorIfSupported: false
                    });
                }

                if (cameraRunning) return;

                html5Scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    decodedText => {
                        if (scanningLocked) return;

                        const now = Date.now();
                        if (decodedText === lastScannedText && (now - lastScannedTime) < 3000) {
                            return;
                        }

                        lastScannedText = decodedText;
                        lastScannedTime = now;

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
                stopSelectBarcodeCamera();

                if (!html5Scanner || !cameraRunning) return;

                html5Scanner.stop().then(() => {
                    cameraRunning = false;
                }).catch(() => {
                    cameraRunning = false;
                });
            }

            async function showSelectBarcodeCamera() {
                if (!('BarcodeDetector' in window)) {
                    renderCameraError('Browser ini belum mendukung Select Barcode. Mode dikembalikan ke Auto Scan.');
                    scannerMode = 'auto';
                    updateScannerModeButtons();
                    setTimeout(() => showCamera(), 200);
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

                    selectBarcodeDetector = new BarcodeDetector({ formats });
                    selectBarcodeStream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { ideal: 'environment' } },
                        audio: false
                    });

                    const reader = document.getElementById('reader');
                    reader.innerHTML = `
                        <div class="select-barcode-shell">
                            <video id="selectBarcodeVideo" autoplay muted playsinline></video>
                            <canvas id="selectBarcodeCanvas"></canvas>
                            <div id="selectBarcodeOverlay" class="select-barcode-tap-layer"></div>
                        </div>
                        <div id="selectBarcodeCandidates" class="select-barcode-candidates">
                            <div class="select-barcode-empty">Arahkan kamera ke barcode, lalu tap label barcode pada tampilan kamera.</div>
                        </div>
                    `;

                    selectBarcodeVideo = document.getElementById('selectBarcodeVideo');
                    selectBarcodeCanvas = document.getElementById('selectBarcodeCanvas');
                    selectBarcodeOverlay = document.getElementById('selectBarcodeOverlay');
                    selectBarcodeVideo.srcObject = selectBarcodeStream;
                    selectBarcodeCanvas.addEventListener('click', handleSelectBarcodeCanvasClick);
                    await selectBarcodeVideo.play();
                    cameraRunning = true;
                    selectBarcodeTick();
                } catch (error) {
                    stopSelectBarcodeCamera();
                    renderCameraError('Select Barcode tidak tersedia di browser ini. Mode dikembalikan ke Auto Scan.');
                    scannerMode = 'auto';
                    updateScannerModeButtons();
                    setTimeout(() => showCamera(), 200);
                }
            }

            function stopSelectBarcodeCamera() {
                const hadSelectStream = !!selectBarcodeStream;

                if (selectBarcodeLoopId) {
                    cancelAnimationFrame(selectBarcodeLoopId);
                    selectBarcodeLoopId = null;
                }

                if (selectBarcodeStream) {
                    selectBarcodeStream.getTracks().forEach(track => track.stop());
                }

                selectBarcodeStream = null;
                selectBarcodeVideo = null;
                selectBarcodeCanvas = null;
                selectBarcodeOverlay = null;
                selectBarcodeDetector = null;
                selectBarcodeCandidates = [];
                selectBarcodeDetecting = false;

                if (hadSelectStream) {
                    cameraRunning = false;
                }

                if (hadSelectStream && scannerMode !== 'select') {
                    const reader = document.getElementById('reader');
                    if (reader) reader.innerHTML = '';
                }
            }

            function selectBarcodeTick() {
                if (!selectBarcodeStream || !selectBarcodeVideo || !selectBarcodeDetector) return;

                if (selectBarcodeDetecting || selectBarcodeVideo.readyState < 2) {
                    selectBarcodeLoopId = requestAnimationFrame(selectBarcodeTick);
                    return;
                }

                selectBarcodeDetecting = true;
                selectBarcodeDetector.detect(selectBarcodeVideo)
                    .then(detections => {
                        selectBarcodeCandidates = detections
                            .filter(item => item.rawValue)
                            .map(item => ({
                                value: item.rawValue,
                                box: item.boundingBox || null
                            }));
                        drawSelectBarcodeOverlay();
                        renderSelectBarcodeChoices();
                    })
                    .catch(() => {})
                    .finally(() => {
                        selectBarcodeDetecting = false;
                        selectBarcodeLoopId = requestAnimationFrame(selectBarcodeTick);
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

            function drawSelectBarcodeOverlay() {
                if (!selectBarcodeCanvas || !selectBarcodeVideo) return;

                const width = selectBarcodeVideo.videoWidth || selectBarcodeVideo.clientWidth || 320;
                const height = selectBarcodeVideo.videoHeight || selectBarcodeVideo.clientHeight || 240;
                selectBarcodeCanvas.width = width;
                selectBarcodeCanvas.height = height;

                const ctx = selectBarcodeCanvas.getContext('2d');
                ctx.clearRect(0, 0, width, height);
                ctx.lineWidth = 3;
                ctx.font = '700 16px Inter, sans-serif';

                selectBarcodeCandidates.forEach((candidate, index) => {
                    const box = candidate.box || { x: 12, y: 12 + (index * 44), width: Math.min(width - 24, 280), height: 34 };
                    const color = getBarcodeColor(candidate.value);

                    ctx.strokeStyle = color.border;
                    ctx.fillStyle = color.fill;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);
                    ctx.fillRect(box.x, box.y, box.width, box.height);
                });
            }

            function renderSelectBarcodeChoices() {
                const hint = document.getElementById('selectBarcodeCandidates');

                const uniqueValues = [...new Set(selectBarcodeCandidates.map(candidate => candidate.value))];
                renderSelectBarcodeTapLabels(uniqueValues);

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

            function renderSelectBarcodeTapLabels(uniqueValues) {
                if (!selectBarcodeOverlay || !selectBarcodeCanvas) return;

                if (!uniqueValues.length) {
                    selectBarcodeOverlay.innerHTML = '';
                    return;
                }

                const canvasRect = selectBarcodeCanvas.getBoundingClientRect();
                const canvasWidth = selectBarcodeCanvas.width || canvasRect.width || 1;
                const canvasHeight = selectBarcodeCanvas.height || canvasRect.height || 1;
                const scaleX = canvasRect.width / canvasWidth;
                const scaleY = canvasRect.height / canvasHeight;
                const usedValues = new Set();

                selectBarcodeOverlay.innerHTML = selectBarcodeCandidates
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
                                onclick="selectDetectedBarcode(decodeURIComponent('${encodeURIComponent(candidate.value)}'))">
                                ${escapeHtml(candidate.value)}
                            </button>
                        `;
                    })
                    .join('');
            }

            function handleSelectBarcodeCanvasClick(event) {
                if (!selectBarcodeCanvas || !selectBarcodeCandidates.length) return;

                const rect = selectBarcodeCanvas.getBoundingClientRect();
                const x = (event.clientX - rect.left) * (selectBarcodeCanvas.width / rect.width);
                const y = (event.clientY - rect.top) * (selectBarcodeCanvas.height / rect.height);
                const selected = selectBarcodeCandidates.find(candidate => {
                    if (!candidate.box) return false;

                    return x >= candidate.box.x
                        && x <= candidate.box.x + candidate.box.width
                        && y >= candidate.box.y - 30
                        && y <= candidate.box.y + candidate.box.height;
                });

                if (selected) {
                    selectDetectedBarcode(selected.value);
                }
            }

            function selectDetectedBarcode(value) {
                if (!value || scanningLocked) return;

                document.getElementById('qrInput').value = value;
                submitScan(false, value, 'camera-select');
            }

            let manualScanTimeout = null;
            let lastKeyTime = Date.now();
            let typingSpeedIsHuman = false;

            document.getElementById('qrInput').addEventListener('keydown', event => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (manualScanTimeout) clearTimeout(manualScanTimeout);
                    submitScan(false);
                    return;
                }

                if (event.key.length === 1) {
                    const now = Date.now();
                    const diff = now - lastKeyTime;
                    const val = event.target.value;
                    // Tingkatkan batas kecepatan ke 100ms untuk mengakomodasi scanner yang lambat
                    if (val.length > 0 && diff > 100) {
                        typingSpeedIsHuman = true;
                    }
                    lastKeyTime = now;
                }
            });

            document.getElementById('qrInput').addEventListener('input', event => {
                if (manualScanTimeout) clearTimeout(manualScanTimeout);
                
                const val = event.target.value.trim();
                
                if (val.length === 0) {
                    typingSpeedIsHuman = false;
                    return;
                }

                // If this barcode is already flagged as duplicate, always show warning
                if (duplicateFlaggedBarcodes.has(val)) {
                    showDuplicateWarningFor(val);
                    return;
                }

                // Auto submit jika input dari scanner gun (cepat, bukan manusia)
                if (val.length > 2 && !typingSpeedIsHuman) {
                    manualScanTimeout = setTimeout(() => {
                        submitScan(false);
                    }, 400); // 400ms buffer supaya tidak terpotong kalau ada jeda transfer data usb
                }
            });

            // Global listener untuk Scanner Gun
            // Jika user men-scan tapi fokus kursor tidak di input box, otomatis pindahkan kursor
            document.addEventListener('keydown', function(event) {
                if (document.body.classList.contains('swal2-shown') || scanningLocked) return;
                
                const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
                if (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select') return;
                
                if (event.ctrlKey || event.altKey || event.metaKey) return;
                
                const qrInput = document.getElementById('qrInput');
                if (!qrInput) return;

                if (event.key === 'Enter') {
                    const currentVal = qrInput.value.trim();
                    if (currentVal.length > 0) {
                        event.preventDefault();
                        // If flagged duplicate, show warning instead of auto-submitting
                        if (duplicateFlaggedBarcodes.has(currentVal)) {
                            showDuplicateWarningFor(currentVal);
                        } else {
                            submitScan(false);
                        }
                    }
                    return;
                }

                if (event.key.length === 1) {
                    qrInput.focus();
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                updateRecentPagination(initialRecentMeta);
                document.getElementById('qrInput').focus();
            });
        </script>
    @endpush

</x-layouts.app>
