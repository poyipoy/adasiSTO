<x-layouts.app :title="'Konfirmasi Rack'">

@push('styles')
<style>
    .rack-action-list {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        min-width: 132px;
    }
    .rack-action-list .btn {
        min-height: 28px;
        padding: 4px 9px;
        white-space: nowrap;
    }
    .rack-action-list .btn:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
    .rack-disabled-action {
        display: inline-flex;
    }
    .rack-action-modal {
        width: min(440px, calc(100vw - 24px));
        max-width: 440px;
    }
    .rack-action-info {
        padding: 11px 12px;
        margin-bottom: 12px;
        border: 1px solid #bfd3ec;
        border-left: 3px solid var(--primary);
        background: #f3f7fc;
        color: var(--text);
        font-size: 12px;
        line-height: 1.5;
    }
    .rack-action-info strong {
        display: block;
        margin-bottom: 2px;
        font-size: 13px;
    }
    .rack-cancel-message {
        margin: 0 0 12px;
        color: var(--text-secondary);
        font-size: 12px;
        line-height: 1.5;
    }
    .rack-form-error {
        display: none;
        margin-top: 8px;
        color: var(--danger);
        font-size: 12px;
        line-height: 1.4;
    }
</style>
@endpush

<div class="enterprise-toolbar">
    <button class="btn btn-icon" type="button" onclick="reloadTable()">Refresh</button>
    <button class="btn btn-icon" type="button" onclick="resetFilters()">Reset</button>
    @if(auth()->user()->isAdmin())
        <button class="btn btn-secondary" type="button" onclick="exportReport()">Export CSV</button>
    @endif
</div>

@if(!$activeSto)
    <div class="card" style="border-left:3px solid var(--danger);color:var(--danger);">
        {{ \App\Services\ActiveStoService::NO_ACTIVE_STO_MESSAGE }}
    </div>
@endif

<div class="card" style="border-top:0;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div style="width:180px;">
        <label class="form-label">Plant</label>
        <select id="filterPlant" class="form-control">
            <option value="">All Plant</option>
            @foreach($plants as $plant)<option value="{{ $plant->id }}">{{ $plant->name }}</option>@endforeach
        </select>
    </div>
    <div style="width:180px;">
        <label class="form-label">Status</label>
        <select id="filterStatus" class="form-control">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="finished">Finished</option>
            <option value="validated">Validated</option>
            <option value="confirmed">Confirmed</option>
        </select>
    </div>
    <button class="btn btn-primary" type="button" onclick="reloadTable()">Filter</button>
</div>

<div class="table-container" style="border-top:0;">
    <table id="rackConfirmationTable" class="table-enterprise" style="width:100%;">
        <thead><tr>
            <th>No</th><th>Location</th><th>Plant</th><th>Total Scan</th><th>Status</th>
            <th>Action By</th><th>Action At</th><th>Note</th><th>Action</th>
        </tr></thead>
    </table>
</div>

<div class="modal-overlay" id="transitionModal">
    <div class="modal-content rack-action-modal" role="dialog" aria-modal="true" aria-labelledby="transitionTitle">
        <div class="modal-header">
            <strong id="transitionTitle">Aksi Rack</strong>
            <button class="btn-icon" type="button" onclick="closeRackModal('transitionModal')" aria-label="Tutup modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="transitionInfo" class="rack-action-info"></div>
            <div class="form-group"><label class="form-label">Note (opsional)</label><textarea id="transitionNote" class="form-control" rows="3" maxlength="500"></textarea></div>
            <div id="transitionError" class="rack-form-error" role="alert"></div>
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeRackModal('transitionModal')">Batal</button>
            <button class="btn btn-primary" id="btnTransition" type="button" onclick="submitTransition()">Simpan</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="cancelRackModal">
    <div class="modal-content rack-action-modal" role="dialog" aria-modal="true" aria-labelledby="cancelRackTitle">
        <div class="modal-header">
            <strong id="cancelRackTitle">Batalkan Konfirmasi Rack</strong>
            <button class="btn-icon" type="button" onclick="closeRackModal('cancelRackModal')" aria-label="Tutup modal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="cancelRackInfo" class="rack-cancel-message"></p>
            <div class="form-group"><label class="form-label">Alasan <span style="color:var(--danger);">*</span></label><textarea id="cancelRackNote" class="form-control" rows="3" minlength="5" maxlength="500"></textarea></div>
            <div id="cancelRackError" class="rack-form-error" role="alert"></div>
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeRackModal('cancelRackModal')">Batal</button>
            <button class="btn btn-danger" id="btnCancelRack" type="button" onclick="submitCancel()">Ya, Batalkan</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const RACK_ROUTES = {
    datatable: '{{ route('admin.api.rack-confirmation') }}',
    export: '{{ route('admin.rack-confirmation.export') }}',
    finish: id => `{{ url('admin/api/rack-confirmation') }}/${id}/finish`,
    validate: id => `{{ url('admin/api/rack-confirmation') }}/${id}/validate`,
    confirm: id => `{{ url('admin/api/rack-confirmation') }}/${id}/confirm`,
    cancel: id => `{{ url('admin/api/rack-confirmation') }}/${id}/cancel`,
};
const RACK_IS_ADMIN = @json(auth()->user()->isAdmin());
const RACK_CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
const RACK_ACTION_COPY = {
    finish: { title: 'Finished Rack', submit: 'Finished' },
    validate: { title: 'Validate Rack', submit: 'Validate' },
    confirm: { title: 'Konfirmasi Rack', submit: 'Konfirmasi' },
};
let rackTable;
let rackAction = null;
let rackLocationId = null;

document.addEventListener('DOMContentLoaded', () => {
    rackTable = $('#rackConfirmationTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: RACK_ROUTES.datatable, data: d => {
            d.filter_plant = document.getElementById('filterPlant').value;
            d.filter_status = document.getElementById('filterStatus').value;
        }},
        columns: [
            { data: null, orderable: false, searchable: false, render: (d,t,r,m) => m.settings._iDisplayStart + m.row + 1 },
            { data: 'name', name: 'locations.name' },
            { data: 'plant_name', name: 'plants.name' },
            { data: 'total_barcode', orderable: false, searchable: false },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'actor_name', orderable: false, searchable: false },
            { data: 'action_at', orderable: false, searchable: false, render: d => d || '-' },
            { data: 'note', orderable: false, searchable: false, render: d => escapeHtml(d || '-') },
            { data: null, orderable: false, searchable: false, render: (data, type, row) => actionButtons(row) },
        ],
        pageLength: 25,
        order: [[1, 'asc']],
    });

    document.getElementById('rackConfirmationTable').addEventListener('click', event => {
        const button = event.target.closest('[data-rack-action]');
        if (!button || button.disabled) {
            return;
        }

        const action = button.dataset.rackAction;
        const id = Number(button.dataset.id);
        const name = button.dataset.name || '-';
        const total = Number(button.dataset.total || 0);

        if (action === 'cancel') {
            openCancel(id, name);
            return;
        }

        openTransition(action, id, name, total);
    });
});

function actionButtons(row) {
    const actions = row.allowed_actions || {};
    const status = String(row.rack_status || 'pending').toLowerCase();
    const buttons = [];

    if (actions.finish) {
        buttons.push(rackActionButton(row, 'finish', 'Finished', 'btn-primary'));
    }
    if (actions.validate) {
        buttons.push(rackActionButton(row, 'validate', 'Validate', 'btn-primary'));
    }

    if (RACK_IS_ADMIN) {
        if (actions.cancel) {
            buttons.push(rackActionButton(row, 'cancel', 'Batalkan', 'btn-danger'));
        }
        if (actions.confirm) {
            buttons.push(rackActionButton(row, 'confirm', 'Konfirmasi', 'btn-primary'));
        } else if (status === 'pending' || status === 'finished') {
            const waitingMessage = status === 'pending'
                ? 'Menunggu scanner menandai Finished.'
                : 'Menunggu scanner validator melakukan Validate.';
            buttons.push(rackActionButton(row, 'confirm', 'Konfirmasi', 'btn-primary', true, waitingMessage));
        }
    }

    return buttons.length ? `<div class="rack-action-list">${buttons.join('')}</div>` : '-';
}

function rackActionButton(row, action, label, variant, disabled = false, title = '') {
    const disabledAttributes = disabled ? ' disabled aria-disabled="true"' : '';
    const button = `
        <button
            class="btn ${variant}"
            type="button"
            data-rack-action="${escapeAttribute(action)}"
            data-id="${Number(row.id)}"
            data-name="${escapeAttribute(row.name || '-')}"
            data-total="${Number(row.total_barcode || 0)}"
            ${disabledAttributes}
        >${escapeHtml(label)}</button>
    `;

    if (disabled && title) {
        return `<span class="rack-disabled-action" tabindex="0" title="${escapeAttribute(title)}">${button}</span>`;
    }

    return button;
}

function openTransition(action, id, name, total) {
    const copy = RACK_ACTION_COPY[action];
    if (!copy || !RACK_ROUTES[action]) {
        return;
    }

    rackAction = action;
    rackLocationId = id;
    document.getElementById('transitionTitle').textContent = copy.title;
    document.getElementById('transitionInfo').innerHTML = `<strong>${escapeHtml(name)}</strong><br>${total} scan valid pada STO aktif`;
    document.getElementById('transitionNote').value = '';
    document.getElementById('btnTransition').textContent = copy.submit;
    hideRackError('transitionError');
    openRackModal('transitionModal');
}

async function submitTransition() {
    if (!rackAction || !rackLocationId || !RACK_ROUTES[rackAction]) {
        showRackError('transitionError', { message: 'Aksi Rack tidak valid.' });
        return;
    }

    const button = document.getElementById('btnTransition');
    const originalLabel = button.textContent;
    setRackButtonLoading(button, true);

    try {
        await postRack(RACK_ROUTES[rackAction](rackLocationId), { note: document.getElementById('transitionNote').value.trim() });
        closeRackModal('transitionModal');
        rackTable.ajax.reload(null, false);
    } catch (error) {
        showRackError('transitionError', error);
    } finally {
        setRackButtonLoading(button, false, originalLabel);
    }
}

function openCancel(id, name) {
    rackLocationId = id;
    document.getElementById('cancelRackInfo').textContent =
        `Workflow Rack ${name} akan dibatalkan dan dikembalikan ke status Pending. Seluruh proses Finished, Validate, dan Konfirmasi harus dilakukan kembali.`;
    document.getElementById('cancelRackNote').value = '';
    hideRackError('cancelRackError');
    openRackModal('cancelRackModal');
}

async function submitCancel() {
    const note = document.getElementById('cancelRackNote').value.trim();
    if (note.length < 5) {
        showRackError('cancelRackError', { message: 'Alasan minimal 5 karakter.' });
        return;
    }

    const button = document.getElementById('btnCancelRack');
    const originalLabel = button.textContent;
    setRackButtonLoading(button, true);

    try {
        await postRack(RACK_ROUTES.cancel(rackLocationId), { note });
        closeRackModal('cancelRackModal');
        rackTable.ajax.reload(null, false);
    } catch (error) {
        showRackError('cancelRackError', error);
    } finally {
        setRackButtonLoading(button, false, originalLabel);
    }
}

async function postRack(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': RACK_CSRF_TOKEN,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    });
    const responseText = await response.text();
    let data = {};

    if (responseText) {
        try {
            data = JSON.parse(responseText);
        } catch (error) {
            data = {};
        }
    }

    if (!response.ok) {
        const requestError = new Error(
            rackResponseMessage(data, `Aksi Rack gagal diproses (HTTP ${response.status}).`),
        );
        requestError.status = response.status;
        requestError.errors = data.errors || {};
        throw requestError;
    }

    notifyRackSuccess(data.message || 'Aksi Rack berhasil diproses.');

    return data;
}

function showRackError(id, error) {
    const element = document.getElementById(id);
    element.textContent = rackResponseMessage(error, 'Aksi Rack gagal diproses.');
    element.style.display = 'block';
}

function hideRackError(id) {
    const element = document.getElementById(id);
    element.textContent = '';
    element.style.display = 'none';
}

function rackResponseMessage(payload, fallback) {
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

function setRackButtonLoading(button, loading, originalLabel = '') {
    button.disabled = loading;
    button.textContent = loading ? 'Memproses...' : originalLabel;
}

function openRackModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeRackModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
    }
}

function notifyRackSuccess(message) {
    if (typeof showToast === 'function') {
        showToast(message, 'success');
        return;
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: message,
            timer: 2200,
            showConfirmButton: false,
        });
    }
}

function reloadTable() { rackTable?.ajax.reload(null, false); }
function resetFilters() { document.getElementById('filterPlant').value=''; document.getElementById('filterStatus').value=''; reloadTable(); }
function exportReport() {
    const plant = document.getElementById('filterPlant').value;
    const status = document.getElementById('filterStatus').value;
    window.location.href = `${RACK_ROUTES.export}?filter_plant=${encodeURIComponent(plant)}&filter_status=${encodeURIComponent(status)}`;
}
function escapeHtml(value) { const element = document.createElement('div'); element.textContent = value ?? ''; return element.innerHTML; }
function escapeAttribute(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;');
}
</script>
@endpush
</x-layouts.app>
