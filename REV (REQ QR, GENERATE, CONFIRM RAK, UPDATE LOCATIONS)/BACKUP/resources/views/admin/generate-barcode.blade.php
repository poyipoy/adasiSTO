<x-layouts.app :title="'Generate Barcode'">

<div class="enterprise-toolbar">
    <button class="btn btn-icon" type="button" onclick="reloadTable()" title="Refresh">Refresh</button>
    <button class="btn btn-icon" type="button" onclick="resetFilters()" title="Reset">Reset</button>
    <div class="toolbar-sep"></div>
    <button class="btn btn-outline-primary" type="button" id="btnBatchGenerate" onclick="bulkGenerate()" title="Klik untuk info: Belum ada data Pending yang dipilih/ceklis">
        Generate Terpilih (Batch)
    </button>
    <button class="btn btn-primary" type="button" onclick="openAddBarcodeModal()" title="Tambah Barcode Request baru (status Pending)">
        + Add Barcode
    </button>
    <button class="btn btn-outline-primary" type="button" id="btnBulkPrint" onclick="bulkPrint()" title="Klik untuk info: Belum ada data Approved yang dipilih/ceklis">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Cetak Label (pdf)
    </button>
    <button class="btn btn-outline-success" type="button" id="btnBulkPrintXlsx" onclick="bulkPrintXlsx()" title="Unduh label Approved ke format Excel (.xlsx)">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Cetak Label (xlsx)
    </button>
    <button class="btn btn-outline-secondary" type="button" id="btnBulkPrintGrid" onclick="bulkPrintGrid()" title="Klik untuk info: Belum ada data Approved yang dipilih/ceklis">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        Cetak Grid 3x3 (Batch)
    </button>
</div>

{{-- Filters --}}
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
    <div style="width:130px;"><label class="form-label">Material</label>
        <select id="filterMaterial" class="form-control" style="display:none;">
            <option value="">All</option>
            @foreach($materials as $material)
                <option value="{{ $material->material_code }}">{{ $material->material_name }} ({{ $material->material_code }})</option>
            @endforeach
        </select>
        <button type="button" id="materialFilterTrigger" class="form-control" onclick="openMaterialFilterModal()" style="text-align: left; background: #fff; cursor: pointer; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">All</button>
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
    <div style="width:120px;">
        <label class="form-label">Status</label>
        <select id="filterStatus" class="form-control">
            <option value="" selected>All</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>
    <button class="btn btn-primary" type="button" onclick="reloadTable()">Filter</button>
</div>

{{-- DataTable --}}
<div class="table-container" style="border-top:0;">
    <table id="generateBarcodeTable" class="table-enterprise" style="width:100%;">
        <thead>
            <tr>
                <th style="width:32px;"><input type="checkbox" id="checkAll" title="Pilih semua"></th>
                <th>No</th>
                <th>Material</th>
                <th>Shape</th>
                <th>Size</th>
                <th>Lot Number</th>
                <th>Plant / Location</th>
                <th>Requester</th>
                <th>Status</th>
                <th>Barcode Generated</th>
                <th>Tanggal</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>

{{-- ─── MODAL: Cek Validitas ─── --}}
<div class="modal-overlay" id="validateModal">
    <div class="modal-content" style="max-width:520px;">
        <div class="modal-header">
            <strong>Cek Validitas Barcode</strong>
            <button class="btn-icon" type="button" onclick="closeModal('validateModal')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="validateModalBody">
            <div class="loading-equalizer" style="margin:20px auto;display:flex;">
                <div class="bar"></div><div class="bar"></div><div class="bar"></div>
                <div class="bar"></div><div class="bar"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeModal('validateModal')">Tutup</button>
        </div>
    </div>
</div>

{{-- ─── MODAL: Generate ─── --}}
<div class="modal-overlay" id="generateModal">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <strong>Generate Barcode</strong>
            <button class="btn-icon" type="button" onclick="closeModal('generateModal')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="generateModalInfo" style="background:#f4f8ff;border:1px solid #c3d6f7;padding:10px;margin-bottom:12px;font-size:12px;"></div>
            <div class="form-group">
                <label class="form-label" for="inputQty">Qty (pcs)</label>
                <input type="number" id="inputQty" class="form-control" value="1" readonly style="background: var(--bg-secondary); cursor: not-allowed; font-weight: 700; color: var(--primary);">
            </div>
            <div id="generateModalError" style="display:none;color:var(--danger);font-size:12px;margin-top:6px;"></div>
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeModal('generateModal')">Batal</button>
            <button class="btn btn-primary" type="button" id="btnConfirmGenerate" onclick="confirmGenerate()">
                Generate &amp; Approve
            </button>
        </div>
    </div>
</div>

{{-- ─── MODAL: Tolak ─── --}}
<div class="modal-overlay" id="rejectModal">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <strong>Tolak Request</strong>
            <button class="btn-icon" type="button" onclick="closeModal('rejectModal')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="rejectModalInfo" style="margin-bottom:10px;font-size:12px;"></div>
            <div class="form-group">
                <label class="form-label" for="inputRejectReason">Alasan Penolakan <span style="color:var(--danger);">*</span></label>
                <textarea id="inputRejectReason" class="form-control" rows="3" placeholder="Wajib diisi (min. 5 karakter)" style="resize:vertical;"></textarea>
            </div>
            <div id="rejectModalError" style="display:none;color:var(--danger);font-size:12px;margin-top:6px;"></div>
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeModal('rejectModal')">Batal</button>
            <button class="btn btn-danger" type="button" id="btnConfirmReject" onclick="confirmReject()">
                Tolak Request
            </button>
        </div>
    </div>
</div>

{{-- ─── MODAL: Hasil Generate ─── --}}
<div class="modal-overlay" id="resultModal">
    <div class="modal-content" style="max-width:480px;">
        <div class="modal-header">
            <strong>Barcode Berhasil Di-Generate</strong>
            <button class="btn-icon" type="button" onclick="closeModal('resultModal')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="resultModalBody"></div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeModal('resultModal');reloadTable();">Tutup</button>
            <a class="btn btn-primary" id="btnPrintLabel" href="#" target="_blank" style="margin-right:5px;">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Label PDF
            </a>
            <a class="btn btn-success" id="btnPrintLabelXlsx" href="#" target="_blank">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Cetak Label (xlsx)
            </a>
        </div>
    </div>
</div>

{{-- ─── MODAL: Add Barcode ─── --}}
<div class="modal-overlay" id="addBarcodeModal">
    <div class="modal-content" style="max-width:540px;">
        <div class="modal-header">
            <strong>+ Add Barcode Request</strong>
            <button class="btn-icon" type="button" onclick="closeAddBarcodeModal()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            {{-- Material --}}
            <div class="form-group">
                <label class="form-label" for="abMaterialCode">Material <span style="color:var(--danger);">*</span></label>
                <select id="abMaterialCode" class="form-control">
                    <option value="">-- Pilih Material --</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->material_code }}">{{ $material->material_code }} - {{ $material->material_name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Jenis (Shape) --}}
            <div class="form-group">
                <label class="form-label">Jenis <span style="color:var(--danger);">*</span></label>
                <div style="display:flex;gap:20px;">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="radio" name="abShapeCode" id="abShapeRF" value="RF" checked onchange="abToggleDimensions()"> Flat (RF)
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="radio" name="abShapeCode" id="abShapeRR" value="RR" onchange="abToggleDimensions()"> Round (RR)
                    </label>
                </div>
            </div>
            {{-- Dimensions --}}
            <div class="form-group" style="display:flex;gap:10px;flex-wrap:wrap;">
                <div id="abThicknessWrap" style="flex:1;min-width:90px;">
                    <label class="form-label" for="abThickness">Thickness</label>
                    <input type="number" id="abThickness" class="form-control" min="1" placeholder="mm">
                </div>
                <div id="abWidthWrap" style="flex:1;min-width:90px;">
                    <label class="form-label" for="abWidth">Width</label>
                    <input type="number" id="abWidth" class="form-control" min="1" placeholder="mm">
                </div>
                <div id="abDiameterWrap" style="flex:1;min-width:90px;display:none;">
                    <label class="form-label" for="abDiameter">Diameter</label>
                    <input type="number" id="abDiameter" class="form-control" min="1" placeholder="mm" disabled>
                </div>
                <div style="flex:1;min-width:90px;">
                    <label class="form-label" for="abLength">Length <span style="color:var(--danger);">*</span></label>
                    <input type="number" id="abLength" class="form-control" min="1" placeholder="mm">
                </div>
            </div>
            {{-- Lot Number --}}
            <div class="form-group">
                <label class="form-label" for="abLotNumber">Lot Number <span style="color:var(--danger);">*</span></label>
                <input type="text" id="abLotNumber" class="form-control" maxlength="255" placeholder="Contoh: LOTXXX">
            </div>
            {{-- Plant & Location --}}
            <div class="form-group" style="display:flex;gap:10px;flex-wrap:wrap;">
                <div style="flex:1;min-width:140px;">
                    <label class="form-label" for="abPlantId">Plant <span style="color:var(--danger);">*</span></label>
                    <select id="abPlantId" class="form-control" onchange="abLoadLocations()">
                        <option value="">-- Pilih Plant --</option>
                        @foreach($plants as $plant)
                            <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;min-width:140px;">
                    <label class="form-label" for="abLocationId">Lokasi <span style="color:var(--danger);">*</span></label>
                    <select id="abLocationId" class="form-control" disabled>
                        <option value="">-- Pilih Plant dulu --</option>
                    </select>
                </div>
            </div>
            {{-- Error area --}}
            <div id="addBarcodeError" style="display:none;background:#fff0f0;border:1px solid #f5c6c6;padding:8px 12px;font-size:12px;color:var(--danger);border-radius:3px;margin-top:4px;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" onclick="closeAddBarcodeModal()">Batal</button>
            <button type="submit" class="btn btn-primary" id="addBarcodeSubmitBtn">Simpan</button>
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
</style>
@endpush

@push('scripts')
<script>
const ROUTES = {
    datatable:  '{{ route('admin.api.generate-barcode') }}',
    validate:   (id) => `/admin/api/generate-barcode/${id}/validate`,
    generate:   (id) => `/admin/api/generate-barcode/${id}/generate`,
    reject:         (id) => `/admin/api/generate-barcode/${id}/reject`,
    label:          (id) => `/admin/generate-barcode/${id}/label`,
    labelXlsx:      (id) => `/admin/generate-barcode/${id}/label-xlsx`,
    labelBulk:      '{{ route('admin.generate-barcode.label-bulk') }}',
    labelBulkXlsx:  '{{ route('admin.generate-barcode.label-bulk-xlsx') }}',
    batchGenerate:  '{{ route('admin.api.generate-barcode.batch-generate') }}',
    batchPrintGrid: '{{ route('admin.generate-barcode.batch-print-grid') }}',
    addBarcode:         '{{ route('admin.api.generate-barcode.store') }}',
    locationsByPlant:   '{{ route('admin.api.generate-barcode.locations-by-plant') }}',
};

const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let currentRequestId = null;
let gbTable = null;

// ─── DataTable Init ───
document.addEventListener('DOMContentLoaded', function () {
    gbTable = $('#generateBarcodeTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: ROUTES.datatable,
            data: function (d) {
                return {
                    filter_plant: $('#filterPlant').val(),
                    filter_material: $('#filterMaterial').val(),
                    filter_location: $('#filterLocation').val(),
                    filter_status: $('#filterStatus').val()
                };
            },
        },
        language: { processing: '<div class="loading-equalizer"><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div></div>' },
        columns: [
            { data: null, orderable: false, render: (d) => (d.status === 'approved' || d.status === 'pending') ? `<input type="checkbox" class="row-check" value="${d.id}" data-id="${d.id}" data-status="${d.status}">` : '' },
            { data: 'no', orderable: false },
            { data: null, render: (d) => `<strong>${d.material_code}</strong><br><span style="color:var(--text-secondary);font-size:11px;">${d.material_name}</span>` },
            { data: 'shape_name' },
            { data: 'size', className: 'mono' },
            { data: 'lot_number', className: 'mono' },
            { data: null, render: (d) => `${d.plant}<br><span style="color:var(--text-secondary);font-size:11px;">${d.location}</span>` },
            { data: 'requester' },
            { data: 'status', render: (d) => statusBadge(d) },
            { data: null, render: (d) => d.generated_barcode_material
                ? `<span class="mono" style="font-size:10px;">${d.generated_barcode_material}</span>`
                : '<span style="color:var(--text-muted);">-</span>' },
            { data: 'created_at' },
            { data: null, orderable: false, render: (d) => actionButtons(d) },
        ],
        pageLength: 25,
        order: [[1, 'desc']],
        drawCallback: function () {
            updateBulkPrintBtn();
        },
    });

    // Check-all
    document.getElementById('checkAll').addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
        updateBulkPrintBtn();
    });

    $('#filterPlant, #filterMaterial, #filterLocation, #filterStatus').on('change', function() {
        gbTable.ajax.reload();
    });
});

function statusBadge(status) {
    const map = {
        pending:  '<span class="badge badge-pending">Pending</span>',
        approved: '<span class="badge badge-valid">Approved</span>',
        rejected: '<span class="badge badge-invalid">Rejected</span>',
    };
    return map[status] || status;
}

function actionButtons(d) {
    let btns = '';
    if (d.status === 'pending') {
        btns += `<button class="btn" style="font-size:11px;padding:2px 7px;margin-right:2px;" onclick="openValidate(${d.id})">Cek Validitas</button>`;
        btns += `<button class="btn btn-primary" style="font-size:11px;padding:2px 7px;margin-right:2px;" onclick="openGenerate(${d.id},'${escHtml(d.material_code)}','${escHtml(d.lot_number)}','${escHtml(d.size)}')">Generate</button>`;
        btns += `<button class="btn btn-danger" style="font-size:11px;padding:2px 7px;" onclick="openReject(${d.id},'${escHtml(d.material_code)}','${escHtml(d.lot_number)}')">Tolak</button>`;
    } else if (d.status === 'approved') {
        btns += `<a class="btn btn-primary" style="font-size:11px;padding:2px 7px;margin-right:2px;" href="${ROUTES.label(d.id)}" target="_blank">Cetak PDF</a>`;
        btns += `<a class="btn btn-success" style="font-size:11px;padding:2px 7px;" href="${ROUTES.labelXlsx(d.id)}" target="_blank">Cetak Excel</a>`;
    }
    return btns;
}

function escHtml(s) {
    return String(s).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function reloadTable() {
    if (gbTable) gbTable.ajax.reload(null, false);
}

function resetFilters() {
    document.getElementById('filterPlant').value    = '';
    document.getElementById('filterMaterial').value = '';
    document.getElementById('filterStatus').value   = '';
    reloadTable();
}

// ─── Bulk Print & Batch Actions ───
function updateBulkPrintBtn() {
    const checkedPending = document.querySelectorAll('.row-check:checked[data-status="pending"]').length;
    const checkedApproved = document.querySelectorAll('.row-check:checked[data-status="approved"]').length;

    const btnBatchGen = document.getElementById('btnBatchGenerate');
    if (btnBatchGen) {
        btnBatchGen.className = checkedPending > 0 ? 'btn btn-primary' : 'btn btn-outline-primary';
        btnBatchGen.style.opacity = checkedPending > 0 ? '1' : '0.85';
        btnBatchGen.title = checkedPending > 0 
            ? `Generate ${checkedPending} request terpilih` 
            : 'Klik untuk info: Belum ada data berstatus Pending yang dipilih/ceklis';
        btnBatchGen.innerHTML = checkedPending > 0
            ? 'Generate Terpilih (' + checkedPending + ')'
            : 'Generate Terpilih (Batch)';
    }

    const btnPrint = document.getElementById('btnBulkPrint');
    if (btnPrint) {
        btnPrint.className = checkedApproved > 0 ? 'btn btn-primary' : 'btn btn-outline-primary';
        btnPrint.style.opacity = checkedApproved > 0 ? '1' : '0.85';
        btnPrint.title = checkedApproved > 0 
            ? `Cetak ${checkedApproved} label terpilih` 
            : 'Klik untuk info: Belum ada data berstatus Approved yang dipilih/ceklis';
        btnPrint.innerHTML = checkedApproved > 0
            ? `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> Cetak Label (pdf) (${checkedApproved})`
            : `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> Cetak Label (pdf)`;
    }

    const btnPrintGrid = document.getElementById('btnBulkPrintGrid');
    if (btnPrintGrid) {
        btnPrintGrid.className = checkedApproved > 0 ? 'btn btn-secondary' : 'btn btn-outline-secondary';
        btnPrintGrid.style.opacity = checkedApproved > 0 ? '1' : '0.85';
        btnPrintGrid.title = checkedApproved > 0 
            ? `Cetak Grid 3x3 untuk ${checkedApproved} label terpilih` 
            : 'Klik untuk info: Belum ada data berstatus Approved yang dipilih/ceklis';
        btnPrintGrid.innerHTML = checkedApproved > 0
            ? `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg> Cetak Grid 3x3 (${checkedApproved})`
            : `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg> Cetak Grid 3x3 (Batch)`;
    }

    const btnPrintXlsx = document.getElementById('btnBulkPrintXlsx');
    if (btnPrintXlsx) {
        btnPrintXlsx.className = checkedApproved > 0 ? 'btn btn-success' : 'btn btn-outline-success';
        btnPrintXlsx.style.opacity = checkedApproved > 0 ? '1' : '0.85';
        btnPrintXlsx.title = checkedApproved > 0 
            ? `Cetak ${checkedApproved} label terpilih ke format Excel (.xlsx)` 
            : 'Unduh semua label Approved sesuai filter ke format Excel (.xlsx)';
        btnPrintXlsx.innerHTML = checkedApproved > 0
            ? `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Cetak Label (xlsx) (${checkedApproved})`
            : `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Cetak Label (xlsx)`;
    }
}

function bulkPrint() {
    const ids = Array.from(document.querySelectorAll('.row-check:checked[data-status="approved"]')).map(cb => cb.value);
    if (!ids.length) {
        Swal.fire({
            icon: 'info',
            title: 'Belum Ada Label Terpilih',
            html: 'Silakan <b>centang (ceklis)</b> kotak pada kolom kiri minimal satu data request yang berstatus <b style="color:#059669;">Approved</b> di tabel bawah terlebih dahulu untuk dicetak.',
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#1F5FA6'
        });
        return;
    }

    confirmAction(`Cetak ${ids.length} label QR sekaligus?`, function () {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ROUTES.labelBulk;
        form.target = '_blank';

        const csrf = document.createElement('input');
        csrf.type  = 'hidden';
        csrf.name  = '_token';
        csrf.value = CSRF;
        form.appendChild(csrf);

        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'ids[]';
            inp.value = id;
            form.appendChild(inp);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    });
}

function bulkPrintGrid() {
    const ids = Array.from(document.querySelectorAll('.row-check:checked[data-status="approved"]')).map(cb => cb.value);
    if (!ids.length) {
        Swal.fire({
            icon: 'info',
            title: 'Belum Ada Label Terpilih',
            html: 'Silakan <b>centang (ceklis)</b> kotak pada kolom kiri minimal satu data request yang berstatus <b style="color:#059669;">Approved</b> di tabel bawah terlebih dahulu untuk dicetak format <b>Grid 3x3</b>.',
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#1F5FA6'
        });
        return;
    }

    confirmAction(`Cetak ${ids.length} label dalam format Grid 3x3 A4?`, function () {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ROUTES.batchPrintGrid;
        form.target = '_blank';

        const csrf = document.createElement('input');
        csrf.type  = 'hidden';
        csrf.name  = '_token';
        csrf.value = CSRF;
        form.appendChild(csrf);

        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'ids[]';
            inp.value = id;
            form.appendChild(inp);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    });
}

function bulkPrintXlsx() {
    const ids = Array.from(document.querySelectorAll('.row-check:checked[data-status="approved"]')).map(cb => cb.value);
    
    if (ids.length > 0) {
        confirmAction(`Cetak ${ids.length} label QR terpilih ke format Excel (.xlsx)?`, function () {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = ROUTES.labelBulkXlsx;
            form.target = '_blank';

            const csrf = document.createElement('input');
            csrf.type  = 'hidden';
            csrf.name  = '_token';
            csrf.value = CSRF;
            form.appendChild(csrf);

            ids.forEach(id => {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = 'ids[]';
                inp.value = id;
                form.appendChild(inp);
            });

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        });
    } else {
        const plant    = document.getElementById('filterPlant').value;
        const material = document.getElementById('filterMaterial').value;
        const status   = document.getElementById('filterStatus').value;
        
        confirmAction(`Belum ada kotak centang dipilih. Unduh seluruh label Approved sesuai filter aktif ke format Excel (.xlsx)?`, function () {
            const url = `${ROUTES.labelBulkXlsx}?filter_plant=${encodeURIComponent(plant)}&filter_material=${encodeURIComponent(material)}&filter_status=${encodeURIComponent(status)}`;
            window.open(url, '_blank');
        });
    }
}

function bulkGenerate() {
    const pendingIds = Array.from(document.querySelectorAll('.row-check:checked[data-status="pending"]')).map(cb => cb.value);
    if (!pendingIds.length) {
        Swal.fire({
            icon: 'info',
            title: 'Belum Ada Request Terpilih',
            html: 'Silakan <b>centang (ceklis)</b> kotak pada kolom kiri minimal satu data request yang berstatus <b style="color:#d97706;">Pending</b> di tabel bawah sebelum melakukan Generate Batch.',
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#1F5FA6'
        });
        return;
    }

    Swal.fire({
        title: 'Batch Generate Barcode',
        text: `Proses generate untuk ${pendingIds.length} request terpilih`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Proses Batch (Qty 1)',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#1F5FA6'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('btnBatchGenerate').disabled = true;
            fetch(ROUTES.batchGenerate, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ ids: pendingIds, qty: 1 })
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('btnBatchGenerate').disabled = false;
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 2500, showConfirmButton: false });
                    reloadTable();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal memproses batch.' });
                }
            })
            .catch(() => {
                document.getElementById('btnBatchGenerate').disabled = false;
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.' });
            });
        }
    });
}

// ─── Modal helpers ───
function openModal(id) {
    document.getElementById(id).classList.add('active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

// ─── Cek Validitas ───
function openValidate(requestId) {
    currentRequestId = requestId;
    document.getElementById('validateModalBody').innerHTML =
        '<div class="loading-equalizer" style="margin:20px auto;display:flex;"><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div></div>';
    openModal('validateModal');

    fetch(ROUTES.validate(requestId), {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
    })
        .then(r => r.json())
        .then(data => renderValidateResult(data))
        .catch(() => {
            document.getElementById('validateModalBody').innerHTML =
                '<p style="color:var(--danger);">Gagal memuat validasi.</p>';
        });
}

function renderValidateResult(data) {
    const r = data.request_data || {};
    let html = `
        <div style="background:#f4f8ff;border:1px solid #c3d6f7;padding:8px 10px;margin-bottom:10px;font-size:12px;">
            <strong>${r.material_code} — ${r.material_name}</strong>
            &nbsp;|&nbsp; ${r.shape_name} &nbsp;|&nbsp; <span class="mono">${r.size}</span>
            &nbsp;|&nbsp; Lot: <span class="mono">${r.lot_number}</span>
            &nbsp;|&nbsp; Plant: ${r.plant || '-'} &nbsp;/&nbsp; ${r.location || '-'}
        </div>
    `;

    if (data.barcode_material) {
        html += `<div style="background:#1a1a2e;color:#4ade80;font-family:monospace;padding:8px 12px;font-size:13px;margin-bottom:10px;border-radius:2px;">
            ${data.barcode_material}
        </div>`;
    }

    html += '<div style="display:flex;flex-direction:column;gap:5px;">';
    (data.checks || []).forEach(c => {
        const icon = c.ok ? '✅' : '❌';
        html += `<div style="display:flex;gap:8px;align-items:flex-start;font-size:12px;">
            <span>${icon}</span>
            <div><strong>${c.label}</strong><br><span style="color:var(--text-secondary);">${c.detail}</span></div>
        </div>`;
    });
    html += '</div>';

    document.getElementById('validateModalBody').innerHTML = html;
}

// ─── Generate ───
function openGenerate(requestId, materialCode, lotNumber, size) {
    currentRequestId = requestId;
    document.getElementById('generateModalInfo').innerHTML =
        `<div style="display:flex; justify-content:center; padding:5px 0;"><div class="loading-equalizer"><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div></div></div>`;
    document.getElementById('inputQty').value = 1;
    document.getElementById('generateModalError').style.display = 'none';
    document.getElementById('btnConfirmGenerate').disabled = true;
    openModal('generateModal');

    fetch(ROUTES.validate(requestId), {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const r = data.request_data || {};
                const detail = `${r.material_name || ''} ${r.shape_name || ''} ${r.size || ''}`.replace(/\s+/g, ' ').trim().toUpperCase();

                document.getElementById('generateModalInfo').innerHTML = `
                    <div style="font-size:15px; font-weight:bold; font-family:monospace; color:var(--primary); margin-bottom:4px; text-align:center; letter-spacing:0.5px;">
                        ${data.barcode_material}
                    </div>
                    <div style="font-size:13px; color:var(--text-secondary); font-family:monospace; text-align:center; font-weight:bold; margin-bottom:2px;">
                        ${lotNumber}
                    </div>
                    <div style="font-size:11px; color:var(--text-muted); font-family:monospace; text-align:center;">
                        ${detail}
                    </div>
                `;
                document.getElementById('btnConfirmGenerate').disabled = false;
            } else {
                document.getElementById('generateModalInfo').innerHTML = `<div style="color:var(--danger); font-size:12px; text-align:center;">${data.errors.join(', ')}</div>`;
            }
        })
        .catch(() => {
            document.getElementById('generateModalInfo').innerHTML = '<div style="color:var(--danger); font-size:12px; text-align:center;">Gagal memuat preview barcode</div>';
        });
}

function confirmGenerate() {
    const qty = 1;

    confirmAction(`Generate barcode dan set status ke Approved?`, function () {
        document.getElementById('btnConfirmGenerate').disabled = true;

        fetch(ROUTES.generate(currentRequestId), {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ qty: 1 }),
        })
            .then(r => r.json())
            .then(data => {
                document.getElementById('btnConfirmGenerate').disabled = false;
                if (data.success) {
                    closeModal('generateModal');
                    showGenerateResult(data);
                } else {
                    showModalError('generateModalError', data.message || 'Generate gagal.');
                }
            })
            .catch(() => {
                document.getElementById('btnConfirmGenerate').disabled = false;
                showModalError('generateModalError', 'Terjadi error. Silakan coba lagi.');
            });
    });
}

function showGenerateResult(data) {
    document.getElementById('resultModalBody').innerHTML = `
        <p style="margin-bottom:10px;color:var(--success);font-weight:600;">Barcode berhasil dibuat!</p>
        <div style="background:#1a1a2e;color:#4ade80;font-family:monospace;padding:10px 14px;font-size:13px;margin-bottom:10px;border-radius:2px;">
            ${data.full_barcode}
        </div>
        <p style="font-size:12px;color:var(--text-secondary);">Klik "Cetak Label PDF" atau "Cetak Label (xlsx)" untuk membuka/mengunduh label siap cetak.</p>
    `;
    document.getElementById('btnPrintLabel').href = data.label_url;
    if (document.getElementById('btnPrintLabelXlsx')) {
        document.getElementById('btnPrintLabelXlsx').href = data.label_xlsx_url || (data.label_url ? data.label_url.replace('/label', '/label-xlsx') : '#');
    }
    openModal('resultModal');
    reloadTable();
}

// ─── Reject ───
function openReject(requestId, materialCode, lotNumber) {
    currentRequestId = requestId;
    document.getElementById('rejectModalInfo').innerHTML =
        `Tolak request barcode: <strong>${materialCode}</strong> / Lot <span class="mono">${lotNumber}</span>`;
    document.getElementById('inputRejectReason').value = '';
    document.getElementById('rejectModalError').style.display = 'none';
    document.getElementById('btnConfirmReject').disabled = false;
    openModal('rejectModal');
}

function confirmReject() {
    const reason = document.getElementById('inputRejectReason').value.trim();
    if (reason.length < 5) {
        showModalError('rejectModalError', 'Alasan penolakan harus diisi (min. 5 karakter).');
        return;
    }

    confirmAction('Tolak request barcode ini?', function () {
        document.getElementById('btnConfirmReject').disabled = true;

        fetch(ROUTES.reject(currentRequestId), {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ rejection_reason: reason }),
        })
            .then(r => r.json())
            .then(data => {
                document.getElementById('btnConfirmReject').disabled = false;
                if (data.success) {
                    closeModal('rejectModal');
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 2000, showConfirmButton: false });
                    reloadTable();
                } else {
                    showModalError('rejectModalError', data.message || 'Gagal menolak.');
                }
            })
            .catch(() => {
                document.getElementById('btnConfirmReject').disabled = false;
                showModalError('rejectModalError', 'Terjadi error. Silakan coba lagi.');
            });
    });
}

function showModalError(elId, msg) {
    const el = document.getElementById(elId);
    el.textContent = msg;
    el.style.display = 'block';
}

// ─── Add Barcode Modal ───
function openAddBarcodeModal() {
    document.getElementById('abMaterialCode').value = '';
    document.getElementById('abShapeRF').checked = true;
    document.getElementById('abThickness').value = '';
    document.getElementById('abWidth').value     = '';
    document.getElementById('abDiameter').value  = '';
    document.getElementById('abLength').value    = '';
    document.getElementById('abLotNumber').value = '';
    document.getElementById('abPlantId').value   = '';
    const locationSel = document.getElementById('abLocationId');
    locationSel.innerHTML = '<option value="">-- Pilih Plant dulu --</option>';
    locationSel.disabled  = true;
    abToggleDimensions();
    document.getElementById('addBarcodeError').style.display = 'none';
    document.getElementById('addBarcodeSubmitBtn').disabled = false;
    openModal('addBarcodeModal');
}

function closeAddBarcodeModal() {
    closeModal('addBarcodeModal');
}

function abToggleDimensions() {
    const isRF = document.getElementById('abShapeRF').checked;
    const thicknessWrap = document.getElementById('abThicknessWrap');
    const widthWrap     = document.getElementById('abWidthWrap');
    const diameterWrap  = document.getElementById('abDiameterWrap');
    const thickness     = document.getElementById('abThickness');
    const width         = document.getElementById('abWidth');
    const diameter      = document.getElementById('abDiameter');

    if (isRF) {
        thicknessWrap.style.display = '';
        widthWrap.style.display     = '';
        diameterWrap.style.display  = 'none';
        thickness.disabled = false;
        width.disabled     = false;
        diameter.disabled  = true;
        diameter.value     = '';
    } else {
        thicknessWrap.style.display = 'none';
        widthWrap.style.display     = 'none';
        diameterWrap.style.display  = '';
        thickness.disabled = true;
        width.disabled     = true;
        diameter.disabled  = false;
        thickness.value    = '';
        width.value        = '';
    }
}

function abLoadLocations() {
    const plantId  = document.getElementById('abPlantId').value;
    const sel      = document.getElementById('abLocationId');
    sel.innerHTML  = '<option value="">Memuat...</option>';
    sel.disabled   = true;

    if (!plantId) {
        sel.innerHTML = '<option value="">-- Pilih Plant dulu --</option>';
        return;
    }

    fetch(`${ROUTES.locationsByPlant}?plant_id=${encodeURIComponent(plantId)}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    })
        .then(r => r.json())
        .then(locations => {
            if (!locations.length) {
                sel.innerHTML = '<option value="">Tidak ada lokasi aktif</option>';
                return;
            }
            sel.innerHTML = '<option value="">-- Pilih Lokasi --</option>';
            locations.forEach(loc => {
                const opt = document.createElement('option');
                opt.value       = loc.id;
                opt.textContent = loc.name;
                sel.appendChild(opt);
            });
            sel.disabled = false;
        })
        .catch(() => {
            sel.innerHTML = '<option value="">Gagal memuat lokasi</option>';
        });
}

document.getElementById('addBarcodeSubmitBtn').addEventListener('click', function() {
    const errEl   = document.getElementById('addBarcodeError');
    const btn     = document.getElementById('addBarcodeSubmitBtn');
    errEl.style.display = 'none';

    const shapeCode    = document.querySelector('input[name="abShapeCode"]:checked')?.value || '';
    const materialCode = document.getElementById('abMaterialCode').value.trim();
    const length       = document.getElementById('abLength').value.trim();
    const lotNumber    = document.getElementById('abLotNumber').value.trim();
    const plantId      = document.getElementById('abPlantId').value;
    const locationId   = document.getElementById('abLocationId').value;
    const thickness    = shapeCode === 'RF' ? document.getElementById('abThickness').value.trim() : null;
    const width        = shapeCode === 'RF' ? document.getElementById('abWidth').value.trim()     : null;
    const diameter     = shapeCode === 'RR' ? document.getElementById('abDiameter').value.trim()  : null;

    const errors = [];
    if (!materialCode) errors.push('Material wajib dipilih.');
    if (!shapeCode)    errors.push('Jenis wajib dipilih.');
    if (shapeCode === 'RF') {
        if (!thickness || isNaN(thickness) || Number(thickness) < 1) errors.push('Thickness wajib diisi (> 0).');
        if (!width     || isNaN(width)     || Number(width)     < 1) errors.push('Width wajib diisi (> 0).');
    }
    if (shapeCode === 'RR') {
        if (!diameter  || isNaN(diameter)  || Number(diameter)  < 1) errors.push('Diameter wajib diisi (> 0).');
    }
    if (!length    || isNaN(length)    || Number(length)    < 1) errors.push('Length wajib diisi (> 0).');
    if (!lotNumber) errors.push('Lot Number wajib diisi.');
    if (!plantId)   errors.push('Plant wajib dipilih.');
    if (!locationId) errors.push('Lokasi wajib dipilih.');

    if (errors.length) {
        errEl.innerHTML = errors.map(e => `<div>${escHtml(e)}</div>`).join('');
        errEl.style.display = 'block';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Memproses...';

    fetch(ROUTES.addBarcode, {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            material_code: materialCode,
            shape_code:    shapeCode,
            thickness:     thickness ? Number(thickness) : null,
            width:         width     ? Number(width)     : null,
            diameter:      diameter  ? Number(diameter)  : null,
            length:        Number(length),
            lot_number:    lotNumber,
            plant_id:      Number(plantId),
            location_id:   Number(locationId),
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 2000, showConfirmButton: false });
            setTimeout(() => closeAddBarcodeModal(), 1500);
            gbTable.ajax.reload();
        } else {
            if (data.errors) {
                const msgs = Object.values(data.errors).flat();
                errEl.innerHTML = msgs.map(e => `<div>${escHtml(e)}</div>`).join('');
            } else {
                errEl.textContent = data.message || 'Gagal menyimpan.';
            }
            errEl.style.display = 'block';
        }
    })
    .catch(err => {
        Swal.fire('Error', err.message || 'Gagal menyimpan.', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Simpan';
    });
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

document.getElementById('filterPlant').addEventListener('change', function() {
    const plantId = this.value;
    
    fetch('{{ route('admin.api.generate-barcode.locations-by-plant') }}?plant_id=' + plantId, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(locations => {
        // Update hidden select options (no change event fired - just DOM update)
        const currentVal = locationSelect.value;
        locationSelect.innerHTML = '<option value="">All</option>';
        locations.forEach(loc => {
            const opt = document.createElement('option');
            opt.value = loc.name;
            opt.textContent = loc.name;
            locationSelect.appendChild(opt);
        });
        // Restore selection if still valid, otherwise reset
        if (Array.from(locationSelect.options).some(o => o.value === currentVal)) {
            locationSelect.value = currentVal;
        } else {
            locationSelect.value = '';
        }
        syncLocationFilterList();
        // Reload table with new filter context
        if (gbTable) gbTable.ajax.reload(null, false);
    });
});

</script>
@endpush

</x-layouts.app>
