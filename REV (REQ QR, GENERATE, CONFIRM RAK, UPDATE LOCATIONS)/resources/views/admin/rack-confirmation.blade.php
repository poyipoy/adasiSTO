<x-layouts.app :title="'Konfirmasi Rak'">

<div class="enterprise-toolbar">
    <button class="btn btn-icon" type="button" onclick="reloadTable()" title="Refresh">Refresh</button>
    <button class="btn btn-icon" type="button" onclick="resetFilters()" title="Reset">Reset</button>
    <button class="btn btn-secondary" type="button" onclick="exportExcel()" title="Export Laporan CSV/Excel">
        <i class='bx bx-export'></i> Export CSV/Excel
    </button>
</div>

{{-- ─── Filters ─── --}}
<div class="card" style="border-top:0;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div style="width:160px;">
        <label class="form-label">Plant</label>
        <select id="filterPlant" class="form-control">
            <option value="">All Plant</option>
            @foreach($plants as $plant)
                <option value="{{ $plant->id }}">{{ $plant->name }}</option>
            @endforeach
        </select>
    </div>
    <div style="width:160px;">
        <label class="form-label">Status Konfirmasi</label>
        <select id="filterStatus" class="form-control">
            <option value="">All Status</option>
            <option value="confirmed">Terkonfirmasi</option>
            <option value="unconfirmed">Belum Dikonfirmasi</option>
        </select>
    </div>
    <button class="btn btn-primary" type="button" onclick="reloadTable()">Filter</button>
</div>

{{-- ─── DataTable ─── --}}
<div class="table-container" style="border-top:0;">
    <table id="rackConfirmationTable" class="table-enterprise" style="width:100%;">
        <thead>
            <tr>
                <th>No</th>
                <th>Lokasi</th>
                <th>Plant</th>
                <th>Total Barcode</th>
                <th>Status Konfirmasi</th>
                <th>Dikonfirmasi Oleh</th>
                <th>Waktu Konfirmasi</th>
                <th>Catatan</th>
                <th>Aksi</th>
            </tr>
        </thead>
    </table>
</div>

{{-- ─── MODAL: Konfirmasi Rak ─── --}}
<div class="modal-overlay" id="confirmModal">
    <div class="modal-content" style="max-width:440px;">
        <div class="modal-header">
            <strong>Konfirmasi Rak</strong>
            <button class="btn-icon" type="button" onclick="closeModal('confirmModal')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            {{-- Info box: acuan admin di lapangan --}}
            <div id="confirmInfo" style="
                background: #f0f7ff;
                border: 1px solid #c3d6f7;
                border-radius: 4px;
                padding: 12px 14px;
                margin-bottom: 14px;
            ">
                <div style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">Nama Lokasi</div>
                <div id="confirmLocationName" style="font-size:14px;font-weight:700;color:var(--primary);"></div>
                <div style="margin-top:10px;padding-top:10px;border-top:1px solid #dde6f7;">
                    <div style="font-size:11px;color:var(--text-secondary);">Menurut sistem, lokasi ini seharusnya memiliki:</div>
                    <div id="confirmBarcodeCount" style="font-size:20px;font-weight:800;color:var(--primary);margin-top:2px;"></div>
                    <div style="font-size:11px;color:var(--text-secondary);">barcode &mdash; pastikan sudah dicek fisik sebelum konfirmasi.</div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="inputConfirmNote">Catatan Cek Fisik <span style="color:var(--text-secondary);font-weight:400;">(opsional)</span></label>
                <textarea id="inputConfirmNote" class="form-control" rows="3" placeholder="Misal: Sesuai, semua barang ada di rak..." style="resize:vertical;"></textarea>
            </div>
            <div id="confirmModalError" style="display:none;color:var(--danger);font-size:12px;margin-top:6px;"></div>
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeModal('confirmModal')">Batal</button>
            <button class="btn btn-primary" type="button" id="btnDoConfirm" onclick="doConfirm()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Konfirmasi Rak
            </button>
        </div>
    </div>
</div>

{{-- ─── MODAL: Batalkan Konfirmasi ─── --}}
<div class="modal-overlay" id="cancelModal">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <strong>Batalkan Konfirmasi</strong>
            <button class="btn-icon" type="button" onclick="closeModal('cancelModal')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="cancelInfo" style="font-size:13px;margin-bottom:12px;color:var(--text-secondary);">
                Batalkan konfirmasi untuk lokasi: <strong id="cancelLocationName" style="color:var(--danger);"></strong>
            </div>
            <div class="form-group">
                <label class="form-label" for="inputCancelNote">
                    Alasan Pembatalan / Catatan Selisih <span style="color:var(--danger);">*</span>
                </label>
                <textarea id="inputCancelNote" class="form-control" rows="3" placeholder="Wajib diisi (min. 5 karakter). Misal: Ada selisih 2 pcs pada pengecekan ulang..." style="resize:vertical;"></textarea>
            </div>
            <div id="cancelModalError" style="display:none;color:var(--danger);font-size:12px;margin-top:6px;"></div>
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeModal('cancelModal')">Batal</button>
            <button class="btn btn-danger" type="button" id="btnDoCancel" onclick="doCancel()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Batalkan Konfirmasi
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const ROUTES = {
    datatable : '{{ route('admin.api.rack-confirmation') }}',
    export    : '{{ route('admin.rack-confirmation.export') }}',
    confirm   : (id) => `{{ url('admin/api/rack-confirmation') }}/${id}/confirm`,
    cancel    : (id) => `{{ url('admin/api/rack-confirmation') }}/${id}/cancel`,
};
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// --- Helpers ---
function openModal(id) {
    document.getElementById(id).classList.add('active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}
function showModalError(id, msg) {
    const el = document.getElementById(id);
    el.textContent = msg;
    el.style.display = 'block';
}
function confirmAction(message, callback) {
    const swal = window.top?.Swal ?? window.Swal;
    if (swal) {
        swal.fire({
            title: 'Konfirmasi',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            confirmButtonColor: '#1F5FA6'
        }).then((result) => {
            if (result.isConfirmed) callback();
        });
    } else {
        if (confirm(message)) callback();
    }
}

let rcTable       = null;
let currentLocId  = null;

// ─── DataTable Init ───
document.addEventListener('DOMContentLoaded', function () {
    rcTable = $('#rackConfirmationTable').DataTable({
        processing   : true,
        serverSide   : true,
        ajax         : {
            url  : ROUTES.datatable,
            type : 'GET',
            data : function (d) {
                d.filter_plant   = document.getElementById('filterPlant').value;
                d.filter_status  = document.getElementById('filterStatus').value;
            },
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false,
              render: (d, t, r, m) => m.settings._iDisplayStart + m.row + 1 },
            { data: 'name',          name: 'locations.name' },
            { data: 'plant_name',    name: 'plants.name',      orderable: false, searchable: false },
            { data: 'total_barcode', name: 'total_barcode',    orderable: false, searchable: false,
              render: (d) => `<strong style="font-size:15px;">${d}</strong> <span style="font-size:10px;color:var(--text-secondary);">barcode</span>` },
            { data: 'status_badge',  name: 'is_confirmed',     orderable: false, searchable: false },
            { data: 'confirmed_by_name',  name: 'confirmed_by_name', orderable: false, searchable: false },
            { data: 'confirmed_at_fmt',   name: 'confirmed_at',      orderable: false, searchable: false },
            { data: 'confirmation_note_val', name: 'confirmation_note_val', orderable: false, searchable: false,
              render: (d) => d && d !== '-'
                ? `<span style="font-size:11px;color:var(--text-secondary);">${escHtml(d)}</span>`
                : '-' },
            { 
                data: null, 
                name: 'id',
                orderable: false, 
                searchable: false,
                render: function (row) {
                    const safeName = escHtml(row.name);
                    const totalBarcode = parseInt(row.total_barcode || 0);
                    if (!row.is_confirmed) {
                        return `<button class="btn btn-primary"
                            style="font-size:11px;padding:2px 8px;"
                            onclick="openConfirm(${row.id}, '${safeName}', ${totalBarcode})"
                        >Konfirmasi</button>`;
                    }
                    return `<button class="btn btn-danger"
                        style="font-size:11px;padding:2px 8px;"
                        onclick="openCancel(${row.id}, '${safeName}')"
                    >Batalkan</button>`;
                }
            },
        ],
        order     : [[1, 'asc']],
        pageLength: 25,
        language  : { processing: '<div class="loading-equalizer"><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div></div>' },
    });
});

function reloadTable() {
    if (rcTable) rcTable.ajax.reload(null, false);
}

function resetFilters() {
    document.getElementById('filterPlant').value  = '';
    document.getElementById('filterStatus').value = '';
    reloadTable();
}

function exportExcel() {
    const plant  = document.getElementById('filterPlant').value;
    const status = document.getElementById('filterStatus').value;
    const url    = `${ROUTES.export}?filter_plant=${encodeURIComponent(plant)}&filter_status=${encodeURIComponent(status)}`;
    window.location.href = url;
}

function escHtml(s) {
    return String(s ?? '')
        .replace(/'/g, "\\'")
        .replace(/"/g, '&quot;');
}

// ─── Confirm Modal ───
function openConfirm(locationId, locationName, totalBarcode) {
    currentLocId = locationId;
    document.getElementById('confirmLocationName').textContent = locationName;
    document.getElementById('confirmBarcodeCount').textContent = totalBarcode;
    document.getElementById('inputConfirmNote').value          = '';
    document.getElementById('confirmModalError').style.display = 'none';
    document.getElementById('btnDoConfirm').disabled           = false;
    openModal('confirmModal');
}

function doConfirm() {
    const note = document.getElementById('inputConfirmNote').value.trim();
    const errEl = document.getElementById('confirmModalError');
    errEl.style.display = 'none';

    confirmAction('Konfirmasi rak ini setelah pengecekan fisik?', function () {
        document.getElementById('btnDoConfirm').disabled = true;

        fetch(ROUTES.confirm(currentLocId), {
            method  : 'POST',
            headers : { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body    : JSON.stringify({ note }),
        })
            .then(r => r.json())
            .then(data => {
                document.getElementById('btnDoConfirm').disabled = false;
                if (data.success) {
                    closeModal('confirmModal');
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, showConfirmButton: false, timer: 2000 });
                    reloadTable();
                } else {
                    showModalError('confirmModalError', data.message || 'Konfirmasi gagal.');
                }
            })
            .catch(() => {
                document.getElementById('btnDoConfirm').disabled = false;
                showModalError('confirmModalError', 'Terjadi error. Silakan coba lagi.');
            });
    });
}

// ─── Cancel Modal ───
function openCancel(locationId, locationName) {
    currentLocId = locationId;
    document.getElementById('cancelLocationName').textContent = locationName;
    document.getElementById('inputCancelNote').value          = '';
    document.getElementById('cancelModalError').style.display = 'none';
    document.getElementById('btnDoCancel').disabled           = false;
    openModal('cancelModal');
}

function doCancel() {
    const note = document.getElementById('inputCancelNote').value.trim();
    const errEl = document.getElementById('cancelModalError');
    errEl.style.display = 'none';

    if (!note || note.length < 5) {
        errEl.textContent    = 'Alasan pembatalan wajib diisi (minimal 5 karakter).';
        errEl.style.display  = 'block';
        return;
    }

    confirmAction('Batalkan konfirmasi rak ini? Alasan/catatan selisih akan disimpan.', function () {
        document.getElementById('btnDoCancel').disabled = true;

        fetch(ROUTES.cancel(currentLocId), {
            method  : 'POST',
            headers : { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body    : JSON.stringify({ note }),
        })
            .then(r => r.json())
            .then(data => {
                document.getElementById('btnDoCancel').disabled = false;
                if (data.success) {
                    closeModal('cancelModal');
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, showConfirmButton: false, timer: 2000 });
                    reloadTable();
                } else {
                    showModalError('cancelModalError', data.message || 'Pembatalan gagal.');
                }
            })
            .catch(() => {
                document.getElementById('btnDoCancel').disabled = false;
                showModalError('cancelModalError', 'Terjadi error. Silakan coba lagi.');
            });
    });
}
</script>
@endpush

</x-layouts.app>
