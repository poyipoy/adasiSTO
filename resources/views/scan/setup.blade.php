<x-layouts.app :title="'Setup STO'">

<div style="max-width: 560px; margin: 0 auto;">
    @if(!$activeSto)
        <div class="card" style="border-left: 3px solid var(--danger);">
            <div class="card-title">STO Tidak Tersedia</div>
            <p style="color: var(--text-secondary); line-height: 1.6;">
                Tidak ada STO aktif yang tersedia. Silakan hubungi Admin.
            </p>
        </div>
    @else
        <form action="{{ route('scan.setup.store', [], false) }}" method="POST" id="setupForm">
            @csrf
            <div class="card">
                <div class="card-title">Setup Scan Material</div>

                <div class="form-group">
                    <label class="form-label">PIC</label>
                    <input class="form-control" value="{{ auth()->user()->name }}" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">STO Code</label>
                    <input class="form-control mono" value="{{ $activeSto->code }}" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label" for="plant_id">Plant</label>
                    <select id="plant_id" name="plant_id" class="form-control" required>
                        <option value="">Pilih Plant</option>
                        @foreach($plants as $plant)
                            <option value="{{ $plant->id }}" @selected(old('plant_id', $scanContext['plant_id'] ?? null) == $plant->id)>{{ $plant->name }}</option>
                        @endforeach
                    </select>
                    @error('plant_id')<div style="color:var(--danger);font-size:11px;margin-top:4px;">{{ $message }}</div>@enderror
                    <div id="error-plant_id" class="ajax-error" style="color:var(--danger);font-size:11px;margin-top:4px;display:none;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="location_id">Location / Rack</label>
                    <div style="display:flex;gap:6px;">
                        <select id="location_id" name="location_id" class="form-control" required data-selected="{{ old('location_id', $scanContext['location_id'] ?? '') }}" onchange="toggleDeleteLocationBtn()">
                            <option value="">Pilih Plant terlebih dahulu</option>
                        </select>
                        <button class="btn" type="button" onclick="openLocationModal()" style="min-width:64px;">+ Baru</button>
                        <button class="btn-icon" id="deleteLocationBtn" type="button" onclick="deleteSelectedLocation()" style="display:none;color:var(--danger);padding:0 8px;border:1px solid var(--border-light);background:var(--bg);border-radius:4px;" title="Hapus Location / Rack">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                    @error('location_id')<div style="color:var(--danger);font-size:11px;margin-top:4px;">{{ $message }}</div>@enderror
                    <div id="error-location_id" class="ajax-error" style="color:var(--danger);font-size:11px;margin-top:4px;display:none;"></div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;height:38px;margin-top:10px;">Start Scan</button>
        </form>
    @endif
</div>

<div class="modal-overlay" id="locationModal">
    <div class="modal-content">
        <div class="modal-header">
            <strong>Tambah Location / Rack</strong>
            <button class="btn-icon" type="button" onclick="closeLocationModal()">X</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label" for="newLocationName">Nama Location / Rack</label>
                <input id="newLocationName" class="form-control" maxlength="100" autocomplete="off" placeholder="Contoh: CT01 / Rack A1">
            </div>
            <div class="form-group" style="margin-top:10px;">
                <label class="form-label">Scan QR / Barcode Rack</label>
                <div style="display:flex;gap:6px;">
                    <button class="btn" type="button" id="showLocationCameraBtn" onclick="showLocationCamera()" style="flex:1;">Show Scanner</button>
                    <button class="btn" type="button" id="hideLocationCameraBtn" onclick="hideLocationCamera()" style="display:none;flex:1;">Hide Scanner</button>
                </div>
                <div id="locationReaderWrap" style="display:none;margin-top:8px;">
                    <div id="locationReader" style="min-height:210px;border:1px solid var(--border);background:#fafbfc;"></div>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:6px;">
                    Hasil scan akan otomatis mengisi nama Location / Rack.
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeLocationModal()">Batal</button>
            <button class="btn btn-primary" type="button" onclick="saveNewLocation()">Simpan</button>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script>
    const plantSelect = document.getElementById('plant_id');
    const locationSelect = document.getElementById('location_id');
    const locationModal = document.getElementById('locationModal');
    const newLocationName = document.getElementById('newLocationName');
    const locationReaderWrap = document.getElementById('locationReaderWrap');
    const locationReader = document.getElementById('locationReader');
    const showLocationCameraBtn = document.getElementById('showLocationCameraBtn');
    const hideLocationCameraBtn = document.getElementById('hideLocationCameraBtn');
    let locationQrScanner = null;
    let locationCameraRunning = false;
    let locationScanLocked = false;

    function loadLocations() {
        const plantId = plantSelect.value;
        const selected = locationSelect.dataset.selected;
        locationSelect.innerHTML = '<option value="">Memuat...</option>';

        if (!plantId) {
            locationSelect.innerHTML = '<option value="">Pilih Plant terlebih dahulu</option>';
            return;
        }

        fetch(`/api/locations?plant_id=${plantId}`, { headers: { Accept: 'application/json' } })
            .then(response => response.json())
            .then(payload => {
                locationSelect.innerHTML = '<option value="">Pilih Location / Rack</option>';
                payload.data.forEach(location => appendLocationOption(location, String(location.id) === String(selected)));
                toggleDeleteLocationBtn();
            })
            .catch(() => {
                locationSelect.innerHTML = '<option value="">Gagal memuat location</option>';
                toggleDeleteLocationBtn();
            });
    }

    function toggleDeleteLocationBtn() {
        const deleteBtn = document.getElementById('deleteLocationBtn');
        if (locationSelect.value) {
            deleteBtn.style.display = 'inline-flex';
        } else {
            deleteBtn.style.display = 'none';
        }
    }

    function deleteSelectedLocation() {
        const id = locationSelect.value;
        if (!id) return;

        const locationName = locationSelect.options[locationSelect.selectedIndex].text;
        const swal = window.top.Swal || Swal;

        swal.fire({
            title: 'Hapus Location / Rack',
            html: `Yakin ingin menghapus lokasi <strong>${locationName}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#b92525',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                fetch(`/api/locations/${id}`, {
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
                        text: payload.message || 'Lokasi berhasil dihapus.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    locationSelect.dataset.selected = '';
                    loadLocations();
                })
                .catch(error => {
                    swal.fire({
                        title: 'Gagal',
                        text: error.message || 'Gagal menghapus lokasi.',
                        icon: 'error',
                        confirmButtonColor: '#2b2d30',
                        confirmButtonText: 'Tutup'
                    });
                });
            }
        });
    }

    function appendLocationOption(location, selected = true) {
        const option = new Option(location.name, location.id);
        option.selected = selected;
        locationSelect.appendChild(option);
    }

    function openLocationModal() {
        if (!plantSelect.value) {
            showToast('Pilih Plant terlebih dahulu.', 'error');
            return;
        }

        newLocationName.value = '';
        locationModal.classList.add('active');
        setTimeout(() => newLocationName.focus(), 50);
    }

    function closeLocationModal() {
        hideLocationCamera();
        locationModal.classList.remove('active');
    }

    function renderLocationCameraError(message) {
        locationReader.innerHTML = `<div style="padding:28px 16px;text-align:center;color:var(--text-muted);">${message}</div>`;
        showToast(message, 'error');
    }

    function showLocationCamera() {
        locationReaderWrap.style.display = 'block';
        showLocationCameraBtn.style.display = 'none';
        hideLocationCameraBtn.style.display = 'inline-flex';

        if (!window.isSecureContext) {
            renderLocationCameraError('Kamera hanya bisa dipakai melalui HTTPS atau localhost. Gunakan URL HTTPS untuk testing di HP.');
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            renderLocationCameraError('Browser tidak menyediakan akses kamera. Isi manual atau gunakan scanner gun.');
            return;
        }

        if (!window.Html5Qrcode) {
            renderLocationCameraError('Library kamera belum tersedia. Periksa asset aplikasi atau buka ulang halaman.');
            return;
        }

        if (!locationQrScanner) {
            locationQrScanner = new Html5Qrcode('locationReader');
        }

        if (locationCameraRunning) return;

        locationQrScanner.start(
            { facingMode: 'environment' },
            { fps: 8, qrbox: { width: 220, height: 160 } },
            decodedText => {
                if (locationScanLocked) return;

                const rackCode = String(decodedText || '').trim();
                if (!rackCode) return;

                locationScanLocked = true;
                newLocationName.value = rackCode;
                showToast('Location / Rack berhasil terbaca.');
                hideLocationCamera();
                setTimeout(() => {
                    locationScanLocked = false;
                    newLocationName.focus();
                }, 600);
            },
            () => { }
        ).then(() => {
            locationCameraRunning = true;
        }).catch(error => {
            let message = 'Kamera tidak tersedia. Isi manual atau gunakan scanner gun.';

            if (error && error.name === 'NotAllowedError') {
                message = 'Izin kamera ditolak. Aktifkan permission kamera di browser.';
            } else if (error && error.name === 'NotFoundError') {
                message = 'Kamera tidak ditemukan di perangkat ini.';
            } else if (error && error.name === 'NotReadableError') {
                message = 'Kamera sedang dipakai aplikasi lain atau tidak bisa dibuka.';
            }

            renderLocationCameraError(message);
        });
    }

    function hideLocationCamera() {
        locationReaderWrap.style.display = 'none';
        showLocationCameraBtn.style.display = 'inline-flex';
        hideLocationCameraBtn.style.display = 'none';

        if (!locationQrScanner || !locationCameraRunning) return Promise.resolve();

        return locationQrScanner.stop()
            .then(() => {
                locationCameraRunning = false;
                if (typeof locationQrScanner.clear === 'function') {
                    locationQrScanner.clear();
                }
            })
            .catch(() => {
                locationCameraRunning = false;
            });
    }

    function saveNewLocation() {
        const name = newLocationName.value.trim();
        if (!name) {
            showToast('Nama Location / Rack wajib diisi.', 'error');
            return;
        }

        fetch('{{ route("api.locations.store", [], false) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ plant_id: plantSelect.value, name })
        })
        .then(async response => {
            const payload = await response.json();
            if (!response.ok) throw payload;
            return payload;
        })
        .then(payload => {
            appendLocationOption(payload.data, true);
            locationSelect.value = payload.data.id;
            locationSelect.dataset.selected = payload.data.id;
            closeLocationModal();
            toggleDeleteLocationBtn();
            const swal = window.top.Swal || Swal;
            swal.fire({
                title: 'Berhasil!',
                text: payload.message || 'Lokasi berhasil ditambahkan.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        })
        .catch(error => {
            const message = error.message || Object.values(error.errors || {})[0]?.[0] || 'Gagal menambah Location / Rack.';
            showToast(message, 'error');
        });
    }

    plantSelect?.addEventListener('change', () => {
        locationSelect.dataset.selected = '';
        loadLocations();
    });
    if (plantSelect?.value) loadLocations();

    const setupForm = document.getElementById('setupForm');
    if (setupForm) {
        setupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Hide previous errors
            document.querySelectorAll('.ajax-error').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Menyimpan...';
            submitBtn.disabled = true;

            fetch(this.action, {
                method: this.method,
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;

                if (response.ok || response.redirected) {
                    if (window.top && window.top.tabManager) {
                        const scannerUrl = '{{ route("scan.scanner", [], false) }}';
                        const currentUrl = '{{ request()->getRequestUri() }}';
                        
                        // Determine the exact ID generated by tabManager
                        let safeIdStr = scannerUrl.replace(/[^a-zA-Z0-9]/g, '');
                        if (safeIdStr.length > 20) safeIdStr = safeIdStr.substring(safeIdStr.length - 20);
                        const scannerTabId = 'tab-' + safeIdStr;
                        
                        const scannerIframeExists = !!window.top.document.getElementById('pane-' + scannerTabId);
                        
                        // Open scanner tab using the global function
                        if (typeof window.top.openWorkspaceTab === 'function') {
                            window.top.openWorkspaceTab(scannerUrl, 'Scanner');
                        } else {
                            window.top.tabManager.openTab('tab-scanscanner', 'Scanner', scannerUrl);
                        }
                        
                        // Update scanner data dynamically without reloading if it already existed
                        if (scannerIframeExists) {
                            const scannerIframe = window.top.document.getElementById('pane-' + scannerTabId);
                            if (scannerIframe) {
                                try {
                                    const plantId = document.getElementById('plant_id').value;
                                    const plantName = document.getElementById('plant_id').options[document.getElementById('plant_id').selectedIndex].text;
                                    const locationId = document.getElementById('location_id').value;
                                    const locationName = document.getElementById('location_id').options[document.getElementById('location_id').selectedIndex].text;
                                    const locationsHtml = document.getElementById('location_id').innerHTML;

                                    const targetWindow = scannerIframe.tagName === 'IFRAME' ? scannerIframe.contentWindow : window.top;

                                    if (typeof targetWindow.updateSetupData === 'function') {
                                        targetWindow.updateSetupData(plantId, plantName, locationId, locationsHtml);
                                    } else {
                                        // Fallback to reload if function not found
                                        if (scannerIframe.tagName === 'IFRAME') {
                                            targetWindow.location.reload(true);
                                        } else {
                                            targetWindow.location.href = scannerUrl;
                                        }
                                    }
                                } catch (e) {
                                    if (scannerIframe && scannerIframe.tagName === 'IFRAME') {
                                        scannerIframe.src = scannerUrl;
                                    } else {
                                        window.top.location.href = scannerUrl;
                                    }
                                }
                            }
                        }

                        // Determine the current setup tab ID generated by tabManager
                        let currentSafeId = currentUrl.replace(/[^a-zA-Z0-9]/g, '');
                        if (currentSafeId.length > 20) currentSafeId = currentSafeId.substring(currentSafeId.length - 20);
                        const currentTabId = 'tab-' + currentSafeId;
                        
                        // Close the setup tab
                        if (currentTabId && currentTabId !== scannerTabId) {
                            window.top.tabManager.closeTab(currentTabId);
                        }
                    } else {
                        window.location.href = '{{ route("scan.scanner", [], false) }}';
                    }
                } else if (response.status === 422) {
                    const data = await response.json();
                    if (data.errors) {
                        for (const [key, messages] of Object.entries(data.errors)) {
                            const errorEl = document.getElementById('error-' + key);
                            if (errorEl) {
                                errorEl.textContent = messages[0];
                                errorEl.style.display = 'block';
                            }
                        }
                    }
                } else {
                    const payload = await response.json().catch(() => ({}));
                    showToast(payload.message || 'Terjadi kesalahan saat menyimpan.', 'error');
                }
            })
            .catch(error => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                showToast('Gagal terhubung ke server.', 'error');
            });
        });
    }
</script>
@endpush

</x-layouts.app>
