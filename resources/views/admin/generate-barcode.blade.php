<x-layouts.app :title="'Generate Barcode'">

<div class="enterprise-toolbar">
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
    <div style="width:150px;">
        <label class="form-label">Material</label>
        <select id="filterMaterial" class="form-control">
            <option value="">All</option>
            @foreach($materials as $material)
                <option value="{{ $material->material_code }}">{{ $material->material_code }}</option>
            @endforeach
        </select>
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
    validate:   (id) => `/admin/api/generate-barcode/${id}/validate`,
    generate:   (id) => `/admin/api/generate-barcode/${id}/generate`,
    reject:         (id) => `/admin/api/generate-barcode/${id}/reject`,
    label:          (id) => `/admin/generate-barcode/${id}/label`,
    labelBulk:      '{{ route('admin.generate-barcode.label-bulk') }}',
    batchGenerate:  '{{ route('admin.api.generate-barcode.batch-generate') }}',
    batchPrintGrid: '{{ route('admin.generate-barcode.batch-print-grid') }}',
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
                d.filter_plant    = document.getElementById('filterPlant').value;
                d.filter_material = document.getElementById('filterMaterial').value;
                d.filter_status   = document.getElementById('filterStatus').value;
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

    document.querySelector('#generateBarcodeTable').addEventListener('change', function (e) {
        if (e.target.classList.contains('row-check')) updateBulkPrintBtn();
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
        btns += `<a class="btn btn-primary" style="font-size:11px;padding:2px 7px;" href="${ROUTES.label(d.id)}" target="_blank">Cetak Label</a>`;
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
            ? `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> Cetak Label Terpilih (${checkedApproved})`
            : `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> Cetak Label Terpilih`;
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
            : `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg> Cetak Grid 3x3 (Batch)`;
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
        <p style="font-size:12px;color:var(--text-secondary);">Klik "Cetak Label PDF" untuk membuka label siap cetak di tab baru.</p>
    `;
    document.getElementById('btnPrintLabel').href = data.label_url;
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

</script>
@endpush

</x-layouts.app>
