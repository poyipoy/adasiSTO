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
        <form action="{{ route('scan.setup.store') }}" method="POST" id="setupForm">
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
                        <select id="location_id" name="location_id" class="form-control" required data-selected="{{ old('location_id', $scanContext['location_id'] ?? '') }}">
                            <option value="">Pilih Plant terlebih dahulu</option>
                        </select>
                        <button class="btn" type="button" onclick="openLocationModal()" style="min-width:64px;">+ Baru</button>
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
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeLocationModal()">Batal</button>
            <button class="btn btn-primary" type="button" onclick="saveNewLocation()">Simpan</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const plantSelect = document.getElementById('plant_id');
    const locationSelect = document.getElementById('location_id');
    const locationModal = document.getElementById('locationModal');
    const newLocationName = document.getElementById('newLocationName');

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
            })
            .catch(() => {
                locationSelect.innerHTML = '<option value="">Gagal memuat location</option>';
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
        locationModal.classList.remove('active');
    }

    function saveNewLocation() {
        const name = newLocationName.value.trim();
        if (!name) {
            showToast('Nama Location / Rack wajib diisi.', 'error');
            return;
        }

        fetch('{{ route("api.locations.store") }}', {
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
            showToast(payload.message);
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
                        const scannerUrl = '{{ route("scan.scanner") }}';
                        const currentUrl = '{{ request()->url() }}';
                        
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
                        
                        // Force reload the iframe if it already existed so it gets the new STO/Location session
                        if (scannerIframeExists) {
                            const scannerIframe = window.top.document.getElementById('pane-' + scannerTabId);
                            if (scannerIframe && scannerIframe.tagName === 'IFRAME') {
                                try {
                                    scannerIframe.contentWindow.location.reload(true);
                                } catch (e) {
                                    scannerIframe.src = scannerUrl;
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
                        window.location.href = '{{ route("scan.scanner") }}';
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
