<x-layouts.app :title="'Generate Barcode'">

@push('styles')
<style>
    /* --- Searchable Filter Modal --- */
    .srch-filter-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1050;
        display: flex; align-items: flex-end; justify-content: center;
    }
    .srch-filter-content {
        background: #fff; width: 100%; max-width: 500px; max-height: 85vh;
        border-radius: 16px 16px 0 0;
        display: flex; flex-direction: column;
        animation: slideUp 0.3s ease-out;
    }
    @media (min-width: 768px) {
        .srch-filter-overlay { align-items: center; }
        .srch-filter-content { border-radius: 12px; max-height: 80vh; animation: fadeIn 0.2s ease-out; }
    }
    .srch-filter-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 16px; border-bottom: 1px solid var(--border-light);
    }
    .srch-filter-search {
        padding: 12px 16px; border-bottom: 1px solid var(--border-light); background: #fafbfc;
    }
    .srch-filter-list { flex: 1; overflow-y: auto; padding: 8px 16px 16px 16px; }
    .srch-filter-item {
        padding: 12px; border-bottom: 1px solid var(--border-light);
        cursor: pointer; font-size: 14px; font-weight: 500; color: var(--text);
        transition: background 0.1s;
    }
    .srch-filter-item:last-child { border-bottom: none; }
    .srch-filter-item:hover { background: #f4f8ff; }
    .srch-filter-item.active {
        background: var(--primary); color: #fff;
        border-radius: 6px; border-bottom: none; margin-bottom: 1px; font-weight: 600;
    }
    .srch-filter-trigger {
        text-align: left; background: #fff; cursor: pointer;
        color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .srch-filter-trigger.has-value { color: var(--primary); font-weight: 600; }

    .barcode-validation-modal {
        width: min(640px, calc(100vw - 24px));
        max-width: 640px;
    }
    .barcode-validation-modal .modal-body {
        max-height: min(70vh, 620px);
        overflow-y: auto;
    }
    .barcode-validation-loading {
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .barcode-validation-modal .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    .barcode-validation-summary {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        margin-bottom: 14px;
        border: 1px solid var(--border);
        border-left-width: 4px;
        background: #f7f9fb;
    }
    .barcode-validation-summary.is-valid {
        border-left-color: var(--success);
        background: #f0fdf4;
    }
    .barcode-validation-summary.is-invalid {
        border-left-color: var(--danger);
        background: #fff5f5;
    }
    .barcode-validation-summary-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: grid;
        place-items: center;
        font-size: 28px;
    }
    .barcode-validation-summary.is-valid .barcode-validation-summary-icon,
    .barcode-validation-summary.is-valid .barcode-validation-status {
        color: var(--success);
    }
    .barcode-validation-summary.is-invalid .barcode-validation-summary-icon,
    .barcode-validation-summary.is-invalid .barcode-validation-status {
        color: var(--danger);
    }
    .barcode-validation-status {
        display: block;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.3;
    }
    .barcode-validation-summary-text {
        margin-top: 2px;
        color: var(--text-secondary);
        font-size: 12px;
    }
    .barcode-validation-code {
        padding: 11px 14px;
        margin-bottom: 14px;
        border: 1px solid #334155;
        background: #17212b;
        color: #d9f99d;
        font-family: Consolas, "Courier New", monospace;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }
    .barcode-validation-code span {
        display: block;
        margin-bottom: 3px;
        color: #a7b4c3;
        font-family: Inter, "Segoe UI", sans-serif;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .barcode-validation-code strong {
        display: block;
        color: #d9f99d;
        font: inherit;
    }
    .barcode-validation-section-title {
        margin: 0 0 8px;
        color: var(--text);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .barcode-validation-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 20px;
        margin-bottom: 16px;
        border-top: 1px solid var(--border-light);
    }
    .barcode-validation-meta-item {
        min-width: 0;
        padding: 8px 0;
        border-bottom: 1px solid var(--border-light);
    }
    .barcode-validation-meta-label {
        display: block;
        margin-bottom: 2px;
        color: var(--text-secondary);
        font-size: 11px;
    }
    .barcode-validation-meta-value {
        display: block;
        color: var(--text);
        font-size: 12px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }
    .barcode-validation-meta-value.is-mono {
        font-family: Consolas, "Courier New", monospace;
    }
    .barcode-validation-checks {
        border-top: 1px solid var(--border-light);
    }
    .barcode-validation-check {
        display: grid;
        grid-template-columns: 24px minmax(0, 1fr) auto;
        gap: 8px;
        align-items: start;
        padding: 9px 0;
        border-bottom: 1px solid var(--border-light);
    }
    .barcode-validation-check-icon {
        font-size: 19px;
        line-height: 1;
    }
    .barcode-validation-check.is-valid .barcode-validation-check-icon,
    .barcode-validation-check.is-valid .barcode-validation-check-state {
        color: var(--success);
    }
    .barcode-validation-check.is-invalid .barcode-validation-check-icon,
    .barcode-validation-check.is-invalid .barcode-validation-check-state {
        color: var(--danger);
    }
    .barcode-validation-check-label {
        color: var(--text);
        font-size: 12px;
        font-weight: 600;
    }
    .barcode-validation-check-detail {
        margin-top: 2px;
        color: var(--text-secondary);
        font-size: 11px;
        line-height: 1.4;
    }
    .barcode-validation-check-state {
        padding-top: 1px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .barcode-validation-error {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 14px;
        border: 1px solid #fecaca;
        border-left: 4px solid var(--danger);
        background: #fff5f5;
        color: var(--danger);
        font-size: 12px;
        line-height: 1.5;
    }
    .barcode-validation-error > i {
        padding-top: 1px;
        font-size: 20px;
    }
    .barcode-validation-error p {
        margin: 3px 0 0;
    }
    @media (max-width: 640px) {
        .barcode-validation-meta {
            grid-template-columns: minmax(0, 1fr);
        }
        .barcode-validation-check {
            grid-template-columns: 24px minmax(0, 1fr);
        }
        .barcode-validation-check-state {
            grid-column: 2;
        }
    }
</style>
@endpush

<div class="enterprise-toolbar">
    @if(auth()->user()->isAdmin())
        <button class="btn btn-primary" type="button" onclick="openAddBarcodeModal()">Add Barcode</button>
    @endif
    <button class="btn btn-icon" type="button" onclick="reloadTable()" title="Refresh">Refresh</button>
    <button class="btn btn-icon" type="button" onclick="resetFilters()" title="Reset">Reset</button>
    <div class="toolbar-sep"></div>
    <button class="btn btn-outline-primary" type="button" id="btnBatchGenerate" onclick="bulkGenerate()" title="Klik untuk info: Belum ada data Pending yang dipilih/ceklis">
        Generate Terpilih (Batch)
    </button>
    <button class="btn btn-outline-primary" type="button" id="btnBulkPrint" onclick="bulkPrint()" title="Klik untuk info: Belum ada data Approved yang dipilih/ceklis">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Cetak Label Terpilih
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
    <div style="width:130px;">
        <label class="form-label">Location</label>
        <select id="filterLocation" style="display:none;">
            <option value="">All</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}">{{ $location->name }}</option>
            @endforeach
        </select>
        <button type="button" id="filterLocationTrigger" class="form-control srch-filter-trigger" onclick="openSrchFilterModal('location')">All</button>
    </div>
    <div style="width:140px;">
        <label class="form-label">Requester</label>
        <select id="filterRequester" style="display:none;">
            <option value="">All</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
        <button type="button" id="filterRequesterTrigger" class="form-control srch-filter-trigger" onclick="openSrchFilterModal('requester')">All</button>
    </div>
    <div style="width:150px;">
        <label class="form-label">Material</label>
        <select id="filterMaterial" style="display:none;">
            <option value="">All</option>
            @foreach($materials as $material)
                <option value="{{ $material->material_code }}">{{ $material->material_code }} - {{ $material->material_name }}</option>
            @endforeach
        </select>
        <button type="button" id="filterMaterialTrigger" class="form-control srch-filter-trigger" onclick="openSrchFilterModal('material')">All</button>
    </div>
    <div style="width:120px;">
        <label class="form-label">Status</label>
        <select id="filterStatus" class="form-control">
            <option value="" selected>All</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="printed">Printed</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>
    <button class="btn btn-primary" type="button" onclick="reloadTable()">Filter</button>
</div>

{{-- ─── Modal: Searchable Filter Location ─── --}}
<div id="srchFilterLocationModal" class="srch-filter-overlay" style="display:none;">
    <div class="srch-filter-content">
        <div class="srch-filter-header">
            <h3 style="margin:0;font-size:16px;font-weight:700;">Filter Location</h3>
            <button type="button" class="btn-icon" onclick="closeSrchFilterModal('location')">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="srch-filter-search">
            <input type="text" id="srchFilterLocationInput" class="form-control" placeholder="Cari lokasi..." oninput="srchFilterSearch('location', this.value)">
        </div>
        <div class="srch-filter-list" id="srchFilterLocationList"></div>
    </div>
</div>

{{-- ─── Modal: Searchable Filter Requester ─── --}}
<div id="srchFilterRequesterModal" class="srch-filter-overlay" style="display:none;">
    <div class="srch-filter-content">
        <div class="srch-filter-header">
            <h3 style="margin:0;font-size:16px;font-weight:700;">Filter Requester</h3>
            <button type="button" class="btn-icon" onclick="closeSrchFilterModal('requester')">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="srch-filter-search">
            <input type="text" id="srchFilterRequesterInput" class="form-control" placeholder="Cari requester..." oninput="srchFilterSearch('requester', this.value)">
        </div>
        <div class="srch-filter-list" id="srchFilterRequesterList"></div>
    </div>
</div>

{{-- ─── Modal: Searchable Filter Material ─── --}}
<div id="srchFilterMaterialModal" class="srch-filter-overlay" style="display:none;">
    <div class="srch-filter-content">
        <div class="srch-filter-header">
            <h3 style="margin:0;font-size:16px;font-weight:700;">Filter Material</h3>
            <button type="button" class="btn-icon" onclick="closeSrchFilterModal('material')">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="srch-filter-search">
            <input type="text" id="srchFilterMaterialInput" class="form-control" placeholder="Cari material..." oninput="srchFilterSearch('material', this.value)">
        </div>
        <div class="srch-filter-list" id="srchFilterMaterialList"></div>
    </div>
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
                <th>Jumlah Label</th>
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

@if(auth()->user()->isAdmin())
<div class="modal-overlay" id="addBarcodeModal">
    <div class="modal-content" style="max-width:720px;">
        <div class="modal-header">
            <strong>Add Barcode</strong>
            <button class="btn-icon" type="button" onclick="closeModal('addBarcodeModal')">&times;</button>
        </div>
        <form id="addBarcodeForm">
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
                    <div class="form-group">
                        <label class="form-label" for="addMaterial">Material</label>
                        <select id="addMaterial" name="material_code" class="form-control" required>
                            <option value="">Pilih Material</option>
                            @foreach($materials as $material)
                                <option value="{{ $material->material_code }}">{{ $material->material_code }} - {{ $material->material_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="addShape">Shape</label>
                        <select id="addShape" name="shape_code" class="form-control" required onchange="syncAddDimensions()">
                            <option value="RF">FLAT (RF)</option>
                            <option value="RR">ROUND (RR)</option>
                            <option value="RH">HOLLOW (RH)</option>
                        </select>
                    </div>
                    <div class="form-group js-add-flat"><label class="form-label">Thickness</label><input name="thickness" type="number" min="1" class="form-control"></div>
                    <div class="form-group js-add-flat"><label class="form-label">Width</label><input name="width" type="number" min="1" class="form-control"></div>
                    <div class="form-group js-add-round" style="display:none;"><label class="form-label">Diameter</label><input name="diameter" type="number" min="1" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Length</label><input name="length" type="number" min="1" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Lot Number</label><input name="lot_number" type="text" maxlength="255" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Jumlah Label</label><input name="print_count" type="number" min="1" value="1" class="form-control" required><small style="color:var(--text-muted);">Qty QR selalu 1.</small></div>
                    <div class="form-group">
                        <label class="form-label" for="addPlant">Plant</label>
                        <select id="addPlant" name="plant_id" class="form-control" required onchange="syncAddLocations()">
                            <option value="">Pilih Plant</option>
                            @foreach($plants as $plant)<option value="{{ $plant->id }}">{{ $plant->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="addLocation">Location</label>
                        <select id="addLocation" name="location_id" class="form-control" required
                            data-searchable-location data-search-title="Pilih Location">
                            <option value="">Pilih Plant terlebih dahulu</option>
                        </select>
                    </div>
                </div>
                <div id="addBarcodeError" style="display:none;color:var(--danger);font-size:12px;margin-top:8px;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn" type="button" onclick="closeModal('addBarcodeModal')">Batal</button>
                <button class="btn btn-primary" type="submit" id="btnSaveBarcode">Save</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ─── MODAL: Cek Validitas ─── --}}
<div class="modal-overlay" id="validateModal">
    <div class="modal-content barcode-validation-modal" role="dialog" aria-modal="true" aria-labelledby="validateModalTitle">
        <div class="modal-header">
            <strong id="validateModalTitle">Cek Validitas Barcode</strong>
            <button class="btn-icon" type="button" onclick="closeModal('validateModal')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="validateModalBody">
            <div class="barcode-validation-loading">
                <div class="loading-equalizer">
                    <div class="bar"></div><div class="bar"></div><div class="bar"></div>
                    <div class="bar"></div><div class="bar"></div>
                </div>
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
            <a class="btn btn-primary" id="btnPrintLabel" href="#" target="_blank">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Label PDF
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
const ROUTES = {
    datatable:  '{{ route('admin.api.generate-barcode') }}',
    store:      '{{ route('admin.api.generate-barcode.store') }}',
    validate:   (id) => `/admin/api/generate-barcode/${id}/validate`,
    generate:   (id) => `/admin/api/generate-barcode/${id}/generate`,
    label:          (id) => `/admin/generate-barcode/${id}/label`,
    labelBulk:      '{{ route('admin.generate-barcode.label-bulk') }}',
    batchGenerate:  '{{ route('admin.api.generate-barcode.batch-generate') }}',
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
                d.filter_plant     = document.getElementById('filterPlant').value;
                d.filter_location  = document.getElementById('filterLocation').value;
                d.filter_requester = document.getElementById('filterRequester').value;
                d.filter_material  = document.getElementById('filterMaterial').value;
                d.filter_status    = document.getElementById('filterStatus').value;
            },
        },
        language: { processing: '<div class="loading-equalizer"><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div></div>' },
        columns: [
            { data: null, orderable: false, render: (d) => ['pending', 'approved', 'printed'].includes(d.status) ? `<input type="checkbox" class="row-check" value="${d.id}" data-id="${d.id}" data-status="${d.status}">` : '' },
            { data: 'no', orderable: false },
            { data: null, render: (d) => `<strong>${d.material_code}</strong><br><span style="color:var(--text-secondary);font-size:11px;">${d.material_name}</span>` },
            { data: 'shape_name' },
            { data: 'size', className: 'mono' },
            { data: 'lot_number', className: 'mono' },
            { data: 'print_count' },
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

    document.querySelector('#generateBarcodeTable').addEventListener('change', function (e) {
        if (e.target.classList.contains('row-check')) updateBulkPrintBtn();
    });
});

function statusBadge(status) {
    const map = {
        pending:  '<span class="badge badge-pending">Pending</span>',
        approved: '<span class="badge badge-valid">Approved</span>',
        printed:  '<span class="badge badge-valid">Printed</span>',
        rejected: '<span class="badge badge-invalid">Rejected</span>',
    };
    return map[status] || status;
}

function actionButtons(d) {
    let btns = '';
    if (d.status === 'pending') {
        btns += `<button class="btn" style="font-size:11px;padding:2px 7px;margin-right:2px;" onclick="openValidate(${d.id})">Cek Validitas</button>`;
        btns += `<button class="btn btn-primary" style="font-size:11px;padding:2px 7px;margin-right:2px;" onclick="openGenerate(${d.id},'${escHtml(d.material_code)}','${escHtml(d.lot_number)}','${escHtml(d.size)}')">Generate</button>`;
    } else if (d.status === 'approved' || d.status === 'printed') {
        btns += `<a class="btn btn-primary" style="font-size:11px;padding:2px 7px;" href="${ROUTES.label(d.id)}" target="_blank">${d.status === 'printed' ? 'Reprint' : 'Cetak Label'}</a>`;
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
    document.getElementById('filterPlant').value  = '';
    document.getElementById('filterStatus').value = '';
    _srchFilterReset('location');
    _srchFilterReset('requester');
    _srchFilterReset('material');
    reloadTable();
}

function openAddBarcodeModal() {
    const form = document.getElementById('addBarcodeForm');
    form.reset();
    document.getElementById('addBarcodeError').style.display = 'none';
    syncAddDimensions();
    syncAddLocations();
    openModal('addBarcodeModal');
}

function syncAddDimensions() {
    const isRound = document.getElementById('addShape')?.value === 'RR';
    document.querySelectorAll('.js-add-flat').forEach(el => el.style.display = isRound ? 'none' : 'block');
    document.querySelectorAll('.js-add-round').forEach(el => el.style.display = isRound ? 'block' : 'none');
}

let addLocationRequest = null;

async function syncAddLocations() {
    const plantId = document.getElementById('addPlant')?.value || '';
    const select = document.getElementById('addLocation');
    if (!select) return;

    addLocationRequest?.abort();
    select.replaceChildren(new Option(
        plantId ? 'Memuat Location...' : 'Pilih Plant terlebih dahulu',
        '',
    ));
    select.disabled = true;
    window.StoSearchableLocationSelect?.refresh(select);

    if (!plantId) return;

    const requestController = new AbortController();
    addLocationRequest = requestController;

    try {
        const url = new URL(@json(route('admin.api.generate-barcode.locations')), window.location.origin);
        url.searchParams.set('plant_id', plantId);
        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
            signal: requestController.signal,
        });
        const payload = await response.json();
        if (!response.ok) throw payload;
        if (document.getElementById('addPlant')?.value !== plantId) return;

        select.replaceChildren(new Option(
            payload.data.length ? 'Pilih Location' : 'Location tidak tersedia',
            '',
        ));
        payload.data.forEach(location => {
            select.appendChild(new Option(location.name, location.id));
        });
        select.disabled = payload.data.length === 0;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    } catch (error) {
        if (error.name === 'AbortError') return;
        select.replaceChildren(new Option('Gagal memuat Location', ''));
        select.disabled = true;
        window.StoSearchableLocationSelect?.refresh(select);
        showToast(error.message || 'Location gagal dimuat.', 'error');
    } finally {
        if (addLocationRequest === requestController) addLocationRequest = null;
    }
}

document.getElementById('addBarcodeForm')?.addEventListener('submit', async function (event) {
    event.preventDefault();
    const button = document.getElementById('btnSaveBarcode');
    const errorBox = document.getElementById('addBarcodeError');
    button.disabled = true;
    errorBox.style.display = 'none';

    try {
        const response = await fetch(ROUTES.store, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(Object.fromEntries(new FormData(this).entries())),
        });
        const payload = await response.json();
        if (!response.ok) throw payload;
        closeModal('addBarcodeModal');
        showToast(payload.message, 'success');
        reloadTable();
    } catch (error) {
        errorBox.textContent = error.message || Object.values(error.errors || {})[0]?.[0] || 'Gagal menambahkan barcode.';
        errorBox.style.display = 'block';
    } finally {
        button.disabled = false;
    }
});

// ─── Searchable Filter Modals ───
const _srchFilterCfg = {
    location: {
        selectId: 'filterLocation', triggerId: 'filterLocationTrigger',
        modalId: 'srchFilterLocationModal', inputId: 'srchFilterLocationInput',
        listId: 'srchFilterLocationList', placeholder: 'All',
    },
    requester: {
        selectId: 'filterRequester', triggerId: 'filterRequesterTrigger',
        modalId: 'srchFilterRequesterModal', inputId: 'srchFilterRequesterInput',
        listId: 'srchFilterRequesterList', placeholder: 'All',
    },
    material: {
        selectId: 'filterMaterial', triggerId: 'filterMaterialTrigger',
        modalId: 'srchFilterMaterialModal', inputId: 'srchFilterMaterialInput',
        listId: 'srchFilterMaterialList', placeholder: 'All',
    },
};

function _srchFilterBuildList(type) {
    const cfg = _srchFilterCfg[type];
    const select = document.getElementById(cfg.selectId);
    const list = document.getElementById(cfg.listId);
    list.innerHTML = '';
    Array.from(select.options).forEach(opt => {
        const div = document.createElement('div');
        div.className = 'srch-filter-item' + (opt.selected ? ' active' : '');
        div.dataset.value = opt.value;
        div.dataset.label = opt.text;
        div.textContent = opt.value === '' ? ' All ' : opt.text;
        div.onclick = () => srchFilterSelect(type, opt.value, opt.value === '' ? cfg.placeholder : opt.text);
        list.appendChild(div);
    });
}

function openSrchFilterModal(type) {
    const cfg = _srchFilterCfg[type];
    _srchFilterBuildList(type);
    const modal = document.getElementById(cfg.modalId);
    modal.style.display = 'flex';
    const input = document.getElementById(cfg.inputId);
    input.value = '';
    srchFilterSearch(type, '');
    input.focus();
    setTimeout(() => {
        const active = modal.querySelector('.srch-filter-item.active');
        if (active) active.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }, 50);
}

function closeSrchFilterModal(type) {
    document.getElementById(_srchFilterCfg[type].modalId).style.display = 'none';
}

function srchFilterSelect(type, value, label) {
    const cfg = _srchFilterCfg[type];
    const select = document.getElementById(cfg.selectId);
    select.value = value;
    const trigger = document.getElementById(cfg.triggerId);
    trigger.textContent = label;
    trigger.classList.toggle('has-value', value !== '');
    closeSrchFilterModal(type);
}

function srchFilterSearch(type, query) {
    const lq = query.toLowerCase();
    document.querySelectorAll(`#${_srchFilterCfg[type].listId} .srch-filter-item`).forEach(item => {
        item.style.display = item.dataset.label.toLowerCase().includes(lq) ? '' : 'none';
    });
}

function _srchFilterReset(type) {
    const cfg = _srchFilterCfg[type];
    const select = document.getElementById(cfg.selectId);
    select.value = '';
    const trigger = document.getElementById(cfg.triggerId);
    trigger.textContent = cfg.placeholder;
    trigger.classList.remove('has-value');
}

['location', 'requester', 'material'].forEach(type => {
    const modal = document.getElementById(_srchFilterCfg[type].modalId);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeSrchFilterModal(type); });
});

// ─── Bulk Print & Batch Actions ───
function updateBulkPrintBtn() {
    const checkedPending = document.querySelectorAll('.row-check:checked[data-status="pending"]').length;
    const checkedApproved = document.querySelectorAll('.row-check:checked[data-status="approved"], .row-check:checked[data-status="printed"]').length;

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
            ? `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> Cetak Label Terpilih (${checkedApproved})`
            : `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> Cetak Label Terpilih`;
    }

}

function bulkPrint() {
    const ids = Array.from(document.querySelectorAll('.row-check:checked[data-status="approved"], .row-check:checked[data-status="printed"]')).map(cb => cb.value);
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
function validationLoadingMarkup() {
    return `
        <div class="barcode-validation-loading" role="status" aria-live="polite">
            <div>
                <div class="loading-equalizer" aria-hidden="true">
                    <div class="bar"></div><div class="bar"></div><div class="bar"></div>
                    <div class="bar"></div><div class="bar"></div>
                </div>
                <span class="sr-only">Memuat hasil validasi barcode</span>
            </div>
        </div>
    `;
}

function escapeValidationHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function validationErrorMarkup(message) {
    return `
        <div class="barcode-validation-error" role="alert">
            <i class="bx bx-error-circle" aria-hidden="true"></i>
            <div>
                <strong>Validasi tidak dapat dimuat</strong>
                <p>${escapeValidationHtml(message || 'Terjadi kesalahan saat memuat validasi barcode.')}</p>
            </div>
        </div>
    `;
}

function validationResponseMessage(payload, fallback) {
    if (payload?.message) {
        return payload.message;
    }

    if (payload?.errors && typeof payload.errors === 'object') {
        const firstError = Object.values(payload.errors).flat().find(Boolean);
        if (firstError) {
            return firstError;
        }
    }

    return fallback;
}

async function openValidate(requestId) {
    currentRequestId = requestId;
    const modalBody = document.getElementById('validateModalBody');
    modalBody.innerHTML = validationLoadingMarkup();
    openModal('validateModal');

    try {
        const response = await fetch(ROUTES.validate(requestId), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        });
        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(validationResponseMessage(
                payload,
                `Validasi gagal diproses (HTTP ${response.status}).`,
            ));
        }

        renderValidateResult(payload);
    } catch (error) {
        modalBody.innerHTML = validationErrorMarkup(error.message);
    }
}

function renderValidateResult(data) {
    const requestData = data.request_data || {};
    const isValid = Boolean(data.success);
    const checks = Array.isArray(data.checks) ? data.checks : [];
    const failedChecks = checks.filter(check => !check.ok).length;
    const summaryClass = isValid ? 'is-valid' : 'is-invalid';
    const summaryIcon = isValid ? 'bx-check-circle' : 'bx-x-circle';
    const statusText = isValid ? 'VALID' : 'TIDAK VALID';
    const summaryText = isValid
        ? 'Barcode memenuhi seluruh pemeriksaan yang dipersyaratkan.'
        : `${failedChecks || 1} pemeriksaan belum memenuhi persyaratan.`;
    const material = [requestData.material_code, requestData.material_name].filter(Boolean).join(' - ') || '-';
    const barcodeMaterial = data.barcode_material || requestData.barcode_material || '-';
    const metadata = [
        ['Material', material, false],
        ['Shape', requestData.shape_name || '-', false],
        ['Size', requestData.size || '-', true],
        ['Lot', requestData.lot_number || '-', true],
        ['Jumlah Label', `${requestData.print_count || 1} label`, false],
        ['Plant', requestData.plant || '-', false],
        ['Location', requestData.location || '-', false],
    ];

    const metadataHtml = metadata.map(([label, value, isMono]) => `
        <div class="barcode-validation-meta-item">
            <span class="barcode-validation-meta-label">${escapeValidationHtml(label)}</span>
            <span class="barcode-validation-meta-value${isMono ? ' is-mono' : ''}">
                ${escapeValidationHtml(value)}
            </span>
        </div>
    `).join('');

    const checksHtml = checks.length
        ? checks.map(check => {
            const checkValid = Boolean(check.ok);
            const checkClass = checkValid ? 'is-valid' : 'is-invalid';
            const checkIcon = checkValid ? 'bx-check-circle' : 'bx-x-circle';

            return `
                <div class="barcode-validation-check ${checkClass}">
                    <i class="bx ${checkIcon} barcode-validation-check-icon" aria-hidden="true"></i>
                    <div>
                        <div class="barcode-validation-check-label">${escapeValidationHtml(check.label || 'Pemeriksaan')}</div>
                        <div class="barcode-validation-check-detail">${escapeValidationHtml(check.detail || '-')}</div>
                    </div>
                    <span class="barcode-validation-check-state">${checkValid ? 'VALID' : 'TIDAK VALID'}</span>
                </div>
            `;
        }).join('')
        : `
            <div class="barcode-validation-check is-invalid">
                <i class="bx bx-error-circle barcode-validation-check-icon" aria-hidden="true"></i>
                <div>
                    <div class="barcode-validation-check-label">Detail pemeriksaan tidak tersedia</div>
                    <div class="barcode-validation-check-detail">Backend tidak mengembalikan daftar pemeriksaan.</div>
                </div>
                <span class="barcode-validation-check-state">TIDAK TERSEDIA</span>
            </div>
        `;

    const html = `
        <section class="barcode-validation-summary ${summaryClass}" aria-live="polite">
            <i class="bx ${summaryIcon} barcode-validation-summary-icon" aria-hidden="true"></i>
            <div>
                <div class="barcode-validation-status">${statusText}</div>
                <div class="barcode-validation-summary-text">${escapeValidationHtml(summaryText)}</div>
            </div>
        </section>

        <div class="barcode-validation-code">
            <span>Barcode Material</span>
            <strong>${escapeValidationHtml(barcodeMaterial)}</strong>
        </div>

        <h3 class="barcode-validation-section-title">Informasi Barcode</h3>
        <div class="barcode-validation-meta">${metadataHtml}</div>

        <h3 class="barcode-validation-section-title">Checklist Validasi</h3>
        <div class="barcode-validation-checks">${checksHtml}</div>
    `;

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
        <p style="font-size:12px;color:var(--text-secondary);">Klik "Cetak Label PDF" untuk membuka label siap cetak di tab baru.</p>
    `;
    document.getElementById('btnPrintLabel').href = data.label_url;
    openModal('resultModal');
    reloadTable();
}

function showModalError(elId, msg) {
    const el = document.getElementById(elId);
    el.textContent = msg;
    el.style.display = 'block';
}

</script>
@endpush

</x-layouts.app>
