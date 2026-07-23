<x-layouts.app :title="'Request QR/Barcode'">

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
</style>
@endpush

<div class="card">
    <div class="card-title">Buat Request Barcode Baru</div>
    
    <form id="requestForm" action="{{ route('api.barcode-request.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label class="form-label" for="material_code">Nama Material</label>
            <select id="material_code" name="material_code" class="form-control" required style="display:none;">
                <option value="">Pilih Material</option>
                @foreach($materials as $material)
                    <option value="{{ $material->material_code }}">{{ $material->material_name }} ({{ $material->material_code }})</option>
                @endforeach
            </select>
            <button type="button" id="materialFilterTrigger" class="form-control" onclick="openMaterialFilterModal()" style="text-align: left; background: #fff; cursor: pointer; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                Pilih Material
            </button>
        </div>
        
        <div class="form-group">
            <label class="form-label">Jenis</label>
            <div style="display: flex; gap: 16px;">
                <label style="display: flex; align-items: center; gap: 6px;">
                    <input type="radio" name="shape_code" value="RF" class="js-shape-radio" checked> Flat (RF)
                </label>
                <label style="display: flex; align-items: center; gap: 6px;">
                    <input type="radio" name="shape_code" value="RR" class="js-shape-radio"> Round (RR)
                </label>
                <label style="display: flex; align-items: center; gap: 6px;">
                    <input type="radio" name="shape_code" value="RH" class="js-shape-radio"> Hollow (RH)
                </label>
            </div>
        </div>
        
        <div class="form-group" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 100px;">
                <label class="form-label" for="thickness">Thickness</label>
                <input type="number" id="thickness" name="thickness" class="form-control dim-field" min="1">
            </div>
            <div style="flex: 1; min-width: 100px;">
                <label class="form-label" for="width">Width</label>
                <input type="number" id="width" name="width" class="form-control dim-field" min="1">
            </div>
            <div style="flex: 1; min-width: 100px;">
                <label class="form-label" for="diameter">Diameter</label>
                <input type="number" id="diameter" name="diameter" class="form-control dim-field" min="1" disabled style="background-color: #e9ecef;">
            </div>
            <div style="flex: 1; min-width: 100px;">
                <label class="form-label" for="length">Length</label>
                <input type="number" id="length" name="length" class="form-control" min="1" required>
            </div>
        </div>
        
        <div id="dimensionSuggestionBox" style="display:none; margin-top: 12px; margin-bottom: 16px; background: #f0f7ff; border: 1px solid #cce3ff; border-radius: 8px; padding: 12px 14px;"></div>
        
        <div class="form-group">
            <label class="form-label" for="lot_number">Lot Number</label>
            <input type="text" id="lot_number" name="lot_number" class="form-control" required maxlength="255">
            <div id="lotNumberSuggestionBox" style="display:none; margin-top: 8px; background: #f0f7ff; border: 1px solid #cce3ff; border-radius: 8px; padding: 10px 12px;"></div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="plant_id">Plant</label>
            <select id="plant_id" name="plant_id" class="form-control" required>
                <option value="">Pilih Plant</option>
                @foreach($plants as $plant)
                    <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="location_id">Lokasi</label>
            <select id="location_id" name="location_id" class="form-control" required style="display:none;">
                <option value="">Pilih Plant terlebih dahulu</option>
            </select>
            <button type="button" id="locationFilterTrigger" class="form-control" onclick="openLocationFilterModal()" style="text-align: left; background: #fff; cursor: pointer; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                Pilih Plant terlebih dahulu
            </button>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 10px; min-width: 150px;" id="btnSubmit">Submit Request</button>
    </form>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-title">Riwayat Request Saya</div>
    <div class="table-container">
        <table id="requests-table" class="table-enterprise" style="width:100%;">
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Jenis</th>
                    <th>Dimensi</th>
                    <th>Lot Number</th>
                    <th>Plant</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th>Dibuat Tanggal</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
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

@push('scripts')
<script>
    const plantSelect = document.getElementById('plant_id');
    const locationSelect = document.getElementById('location_id');
    let requestsTable;

    // --- Material Filter Modal JS Logic ---
    const materialSelect = document.getElementById('material_code');
    const materialFilterTrigger = document.getElementById('materialFilterTrigger');
    const materialFilterModal = document.getElementById('materialFilterModalContainer');
    const materialFilterSearchInput = document.getElementById('materialFilterSearchInput');
    const materialFilterList = document.getElementById('materialFilterList');

    function syncMaterialFilterList() {
        if (!materialFilterList) return;
        materialFilterList.innerHTML = '';
        let selectedText = 'Pilih Material';

        Array.from(materialSelect.options).forEach(opt => {
            if (opt.value === "") return;
            
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

    materialFilterModal.addEventListener('click', function(e) {
        if (e.target === this) closeMaterialFilterModal();
    });

    // Initialize Material filter list
    syncMaterialFilterList();


    // --- Location Filter Modal JS Logic ---
    const locationFilterTrigger = document.getElementById('locationFilterTrigger');
    const locationFilterModal = document.getElementById('locationFilterModalContainer');
    const locationFilterSearchInput = document.getElementById('locationFilterSearchInput');
    const locationFilterList = document.getElementById('locationFilterList');

    function syncLocationFilterList() {
        if (!locationFilterList) return;
        locationFilterList.innerHTML = '';
        let selectedText = 'Pilih Lokasi';

        if (locationSelect.options.length === 1 && locationSelect.options[0].value === "") {
            locationFilterTrigger.textContent = locationSelect.options[0].text;
            return;
        }

        Array.from(locationSelect.options).forEach(opt => {
            if (opt.value === "") return;
            
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
        if (!plantSelect.value) {
            showToast('Pilih Plant terlebih dahulu.', 'error');
            return;
        }
        if (locationSelect.options.length <= 1) {
            showToast('Lokasi kosong atau sedang dimuat.', 'error');
            return;
        }
        
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

    locationFilterModal.addEventListener('click', function(e) {
        if (e.target === this) closeLocationFilterModal();
    });


    // --- Location Loading Logic ---
    function loadLocations() {
        const plantId = plantSelect.value;
        locationSelect.innerHTML = '<option value="">Memuat...</option>';
        syncLocationFilterList();

        if (!plantId) {
            locationSelect.innerHTML = '<option value="">Pilih Plant terlebih dahulu</option>';
            syncLocationFilterList();
            return;
        }

        fetch(`/api/locations?plant_id=${plantId}`, { headers: { Accept: 'application/json' } })
            .then(response => response.json())
            .then(payload => {
                locationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';
                payload.data.forEach(location => {
                    const option = new Option(location.name, location.id);
                    locationSelect.appendChild(option);
                });
                syncLocationFilterList();
            })
            .catch(() => {
                locationSelect.innerHTML = '<option value="">Gagal memuat lokasi</option>';
                syncLocationFilterList();
            });
    }

    plantSelect?.addEventListener('change', loadLocations);

    // Dimension toggle logic
    document.querySelectorAll('.js-shape-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const shapeVal = this.value;
            const thickness = document.getElementById('thickness');
            const width = document.getElementById('width');
            const diameter = document.getElementById('diameter');
            
            thickness.disabled = false;
            thickness.style.backgroundColor = '';
            thickness.required = true;
            
            width.disabled = false;
            width.style.backgroundColor = '';
            width.required = true;
            
            diameter.disabled = false;
            diameter.style.backgroundColor = '';
            diameter.required = true;
            
            if (shapeVal === 'RF' || shapeVal === 'RH') {
                diameter.disabled = true;
                diameter.value = '';
                diameter.style.backgroundColor = '#e9ecef';
                diameter.required = false;
            } else if (shapeVal === 'RR') {
                thickness.disabled = true;
                thickness.value = '';
                thickness.style.backgroundColor = '#e9ecef';
                thickness.required = false;
                
                width.disabled = true;
                width.value = '';
                width.style.backgroundColor = '#e9ecef';
                width.required = false;
            }
        });
    });

    // Trigger initial state
    document.querySelector('.js-shape-radio:checked').dispatchEvent(new Event('change'));

    // --- Auto-Suggestion Dimensi & Lot Number (Pilar 3) ---
    function fetchDimensionSuggestions() {
        const materialCode = materialSelect.value;
        const suggestionBox = document.getElementById('dimensionSuggestionBox');
        const lotBox = document.getElementById('lotNumberSuggestionBox');
        if (!materialCode) {
            if (suggestionBox) suggestionBox.style.display = 'none';
            if (lotBox) lotBox.style.display = 'none';
            return;
        }

        fetch(`/api/barcode-request/suggestions?material_code=${encodeURIComponent(materialCode)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(payload => {
            if (!payload.success) return;

            // Jika ada suggestion terbaru, otomatis pilih shape_code dan isi form (termasuk lot_number jika kosong)
            if (payload.suggestion) {
                applyDimensionSuggestion(payload.suggestion);
            }

            // Tampilkan riwayat dimensi di suggestionBox
            if (payload.history && payload.history.length > 0) {
                let html = '<div style="font-weight: 600; font-size: 13px; color: var(--primary); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Riwayat dimensi material ini (klik untuk otomatis mengisi):</div>';
                html += '<div style="display: flex; gap: 8px; flex-wrap: wrap;">';
                payload.history.forEach(item => {
                    const shapeName = item.shape_code === 'RF' ? 'Flat' : (item.shape_code === 'RH' ? 'Hollow' : 'Round');
                    const dimDesc = (item.shape_code === 'RF' || item.shape_code === 'RH')
                        ? `T: ${item.thickness || '-'}, W: ${item.width || '-'}, L: ${item.length || '-'}`
                        : `D: ${item.diameter || '-'}, L: ${item.length || '-'}`;
                    const jsonStr = encodeURIComponent(JSON.stringify(item));
                    html += `<button type="button" class="btn btn-sm" style="background: #fff; border: 1px solid #b6effb; color: #0d6efd; font-size: 12px; padding: 4px 10px; border-radius: 20px; transition: all 0.2s; cursor: pointer;" onclick="applyDimensionSuggestion(JSON.parse(decodeURIComponent('${jsonStr}')))" onmouseover="this.style.background='#d0ebff'" onmouseout="this.style.background='#fff'">
                        <strong>${shapeName}</strong> (${dimDesc})
                    </button>`;
                });
                html += '</div>';
                suggestionBox.innerHTML = html;
                suggestionBox.style.display = 'block';
            } else {
                suggestionBox.style.display = 'none';
            }

            // Tampilkan riwayat lot number di lotNumberSuggestionBox
            if (lotBox) {
                if (payload.lot_history && payload.lot_history.length > 0) {
                    let lotHtml = '<div style="font-weight: 600; font-size: 13px; color: var(--primary); margin-bottom: 6px; display: flex; align-items: center; gap: 6px;"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg> Riwayat Lot Number material ini (klik untuk otomatis mengisi):</div>';
                    lotHtml += '<div style="display: flex; gap: 8px; flex-wrap: wrap;">';
                    payload.lot_history.forEach(lot => {
                        const escLot = encodeURIComponent(lot);
                        lotHtml += `<button type="button" class="btn btn-sm" style="background: #fff; border: 1px solid #b6effb; color: #0d6efd; font-size: 12px; padding: 4px 10px; border-radius: 20px; transition: all 0.2s; cursor: pointer;" onclick="document.getElementById('lot_number').value = decodeURIComponent('${escLot}')" onmouseover="this.style.background='#d0ebff'" onmouseout="this.style.background='#fff'">
                            <strong>${lot}</strong>
                        </button>`;
                    });
                    lotHtml += '</div>';
                    lotBox.innerHTML = lotHtml;
                    lotBox.style.display = 'block';
                } else {
                    lotBox.style.display = 'none';
                }
            }
        })
        .catch(err => console.error('Gagal memuat saran dimensi dan lot:', err));
    }

    function applyDimensionSuggestion(item) {
        if (!item || !item.shape_code) return;
        
        const shapeRadio = document.querySelector(`.js-shape-radio[value="${item.shape_code}"]`);
        if (shapeRadio) {
            shapeRadio.checked = true;
            shapeRadio.dispatchEvent(new Event('change'));
        }

        if (item.shape_code === 'RF' || item.shape_code === 'RH') {
            if (item.thickness) document.getElementById('thickness').value = item.thickness;
            if (item.width) document.getElementById('width').value = item.width;
        } else if (item.shape_code === 'RR') {
            if (item.diameter) document.getElementById('diameter').value = item.diameter;
        }
        if (item.length) document.getElementById('length').value = item.length;
        if (item.lot_number) document.getElementById('lot_number').value = item.lot_number;
    }

    materialSelect.addEventListener('change', fetchDimensionSuggestions);

    // Form submit
    document.getElementById('requestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btnSubmit');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            const payload = await response.json();
            if (!response.ok) throw payload;
            return payload;
        })
        .then(payload => {
            showToast(payload.message || 'Request berhasil dibuat', 'success');
            
            // Reset form but keep plant if possible
            const plantId = plantSelect.value;
            this.reset();
            plantSelect.value = plantId;
            document.querySelector('.js-shape-radio:checked').dispatchEvent(new Event('change'));
            
            // Sync triggers back
            syncMaterialFilterList();
            loadLocations();
            
            if (requestsTable) requestsTable.ajax.reload(null, false);
        })
        .catch(error => {
            const msg = error.message || Object.values(error.errors || {})[0]?.[0] || 'Terjadi kesalahan.';
            showToast(msg, 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });

    // DataTable init
    $(document).ready(function() {
        requestsTable = $('#requests-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("api.barcode-request") }}',
            order: [[7, 'desc']],
            columns: [
                { data: 'material_info', name: 'material_name' },
                { data: 'shape_name', name: 'shape_name' },
                { data: null, searchable: false, orderable: false, render: function(data, type, row) {
                    if (row.shape_code === 'RF' || row.shape_code === 'RH') return `${row.thickness} x ${row.width} x ${row.length}`;
                    if (row.shape_code === 'RR') return `${row.diameter} x ${row.length}`;
                    return '-';
                }},
                { data: 'lot_number', name: 'lot_number' },
                { data: 'plant_name', name: 'plant.name' },
                { data: 'location_name', name: 'location.name' },
                { data: 'status', name: 'status', render: function(data) {
                    let badgeClass = data === 'pending' ? 'badge-warning' : (data === 'approved' ? 'badge-success' : 'badge-danger');
                    return `<span class="badge ${badgeClass}" style="padding: 4px 8px; border-radius: 4px;">${data}</span>`;
                }},
                { data: 'created_at', name: 'created_at' },
                { data: null, searchable: false, orderable: false, render: function(data, type, row) {
                    if (row.status === 'pending') {
                        return `<button class="btn btn-sm btn-danger" onclick="cancelRequest(${row.id})" style="padding: 2px 8px; font-size: 12px; border: 1px solid #dc3545; background: #fff; color: #dc3545; border-radius: 4px;">Batalkan</button>`;
                    }
                    return '';
                }}
            ],
            language: { emptyTable: 'Belum ada request barcode.' }
        });
    });

    window.cancelRequest = function(id) {
        if (typeof confirmAction === 'function') {
            confirmAction('Batalkan request ini?', () => doCancel(id));
        } else {
            if (confirm('Batalkan request ini?')) doCancel(id);
        }
    };
    
    function doCancel(id) {
        fetch(`/api/barcode-request/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(async response => {
            const payload = await response.json();
            if (!response.ok) throw payload;
            return payload;
        })
        .then(payload => {
            showToast(payload.message || 'Request dibatalkan', 'success');
            if (requestsTable) requestsTable.ajax.reload(null, false);
        })
        .catch(error => {
            showToast(error.message || 'Gagal membatalkan request', 'error');
        });
    }
</script>
@endpush

</x-layouts.app>
