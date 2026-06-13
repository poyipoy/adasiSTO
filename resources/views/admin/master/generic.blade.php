<x-layouts.app :title="$title">

<div class="enterprise-toolbar">
    <button class="btn btn-primary" type="button" onclick="openCreate()">Tambah</button>
    <button class="btn btn-icon" type="button" onclick="reloadTable()" title="Refresh">Refresh</button>
</div>

<div id="inlineEditorError" class="inline-editor-error"></div>

<div class="table-container" style="border-top:0;">
    <table id="masterTable" class="table-enterprise" style="width:100%;">
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>

@push('styles')
<style>
    #masterTable .inline-scan-editor td {
        background: #f8fafc;
        border-top: 1px solid var(--primary);
        border-bottom: 1px solid var(--primary);
        padding: 4px 6px;
        vertical-align: top;
        white-space: normal;
    }

    .inline-editor-label {
        color: var(--primary);
        font-weight: 700;
        white-space: nowrap;
        text-align: center;
        padding-top: 8px !important;
    }

    .inline-input,
    .inline-select {
        width: 100%;
        min-width: 0;
        max-width: 100%;
        height: 26px;
        padding: 2px 4px;
        font-size: 11px;
    }

    .inline-select {
        appearance: none;
        -webkit-appearance: none;
        background-image:
            linear-gradient(45deg, transparent 50%, var(--text) 50%),
            linear-gradient(135deg, var(--text) 50%, transparent 50%);
        background-position:
            calc(100% - 11px) 7px,
            calc(100% - 6px) 7px;
        background-repeat: no-repeat;
        background-size: 5px 5px, 5px 5px;
        padding-right: 18px;
    }

    .inline-select::-ms-expand {
        display: none;
    }

    .inline-actions {
        display: flex;
        gap: 4px;
        min-width: 0;
    }

    .inline-actions .btn {
        flex: 1 1 0;
        min-width: 0;
        padding: 3px 5px;
        font-size: 11px;
        white-space: nowrap;
    }

    .inline-editor-error {
        display: none;
        background: #fff7f7;
        border: 1px solid #f3b4b0;
        color: var(--danger);
        font-size: 12px;
        font-weight: 600;
        padding: 6px 10px;
        margin-bottom: 0;
    }

    .inline-editor-error.active {
        display: block;
    }

    .inline-field.is-invalid {
        border-color: var(--danger);
        box-shadow: 0 0 0 1px rgba(217, 45, 32, 0.12);
    }
</style>
@endpush

@push('scripts')
<script>
    const columns = @json($columns);
    const fields = @json($fields);
    const apiBase = @json($apiBase);
    const activateBase = @json($activateBase ?? null);
    
    let masterTable;
    let activeEditor = null;
    let suppressOutsideUntil = 0;
    window.masterRows = {};

    $(document).ready(function() {
        masterTable = $('#masterTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: apiBase,
            order: [],
            columns: [
                ...columns.map(column => ({
                    data: column.data,
                    orderable: column.data !== 'no',
                    render: function(value, type, row) {
                        if (column.type === 'status') {
                            return `<span class="badge ${value ? 'badge-valid' : 'badge-invalid'}">${value ? 'Active' : 'Inactive'}</span>`;
                        }
                        return escapeHtml(value ?? '-');
                    }
                })),
                {
                    data: null,
                    orderable: false,
                    render: function(row) {
                        window.masterRows[row.id] = row;
                        const activate = activateBase ? `<button class="btn-icon" type="button" onclick="activateRecord(${row.id})">Activate</button>` : '';
                        return `<div style="display:flex;gap:4px;">${activate}<button class="btn-icon js-row-edit" type="button" data-id="${row.id}" onclick="openEdit(${row.id})">Edit</button><button class="btn-icon js-row-delete" style="color:var(--danger);" type="button" onclick="deleteRecord(${row.id})">Delete</button></div>`;
                    }
                }
            ],
            language: { emptyTable: 'Tidak ada data ditemukan.' }
        });

        $(document).on('mousedown', function(event) {
            if (!activeEditor || Date.now() < suppressOutsideUntil) return;

            const target = $(event.target);
            if (target.closest('.inline-scan-editor,.js-row-edit,.js-row-delete,.enterprise-toolbar').length) {
                return;
            }

            if (!closeActiveEditor(true)) {
                suppressOutsideUntil = Date.now() + 250;
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });

    function reloadTable(confirmClose = true) {
        if (confirmClose && !closeActiveEditor(true)) {
            suppressOutsideUntil = Date.now() + 250;
            return;
        }
        masterTable.ajax.reload(null, false);
    }

    function openCreate() {
        if (!closeActiveEditor(true)) {
            suppressOutsideUntil = Date.now() + 250;
            return;
        }

        const createData = defaultCreateData();
        const mainRow = $(inlineEditorRow('create', createData));
        const tbody = $('#masterTable tbody');

        tbody.prepend(mainRow);
        activeEditor = { mode: 'create', id: null, mainRow, originalPayload: payloadSnapshot(createData) };
        attachInlineEvents();
        focusFirstInput();
    }

    function openEdit(id) {
        const row = window.masterRows[id];
        if (!row) return;

        if (!closeActiveEditor(true)) {
            suppressOutsideUntil = Date.now() + 250;
            return;
        }

        const parentRow = $(`button.js-row-edit[data-id="${id}"]`).closest('tr');
        if (!parentRow.length) return;

        const normalized = normalizeRow(row);
        const mainRow = $(inlineEditorRow('edit', normalized));

        parentRow.after(mainRow);
        activeEditor = { mode: 'edit', id, mainRow, originalPayload: payloadSnapshot(normalized) };
        attachInlineEvents();
        focusFirstInput();
    }

    function closeActiveEditor(confirmDirty = true) {
        if (!activeEditor) return true;

        if (confirmDirty && isEditorDirty() && !confirm('Batalkan perubahan data?')) {
            return false;
        }

        clearActiveEditor();
        return true;
    }

    function clearActiveEditor() {
        activeEditor?.mainRow.remove();
        activeEditor = null;
        clearInlineErrors();
    }

    function attachInlineEvents() {
        activeEditor.mainRow.find('.inline-field').on('input change', clearInlineErrors);
    }

    function focusFirstInput() {
        activeEditor.mainRow.find('input:visible, select:visible').first().trigger('focus');
    }

    function isEditorDirty() {
        if (!activeEditor) return false;
        return JSON.stringify(payloadSnapshot(formPayload())) !== JSON.stringify(activeEditor.originalPayload);
    }

    function formPayload() {
        if (!activeEditor) return {};
        const data = {};
        activeEditor.mainRow.find('.inline-field').each(function() {
            data[this.dataset.field] = this.value;
        });
        return data;
    }

    function payloadSnapshot(payload) {
        const normalized = {};
        fields.forEach(field => {
            normalized[field.name] = payload[field.name] === null || payload[field.name] === undefined ? '' : String(payload[field.name]);
        });
        return normalized;
    }

    function defaultCreateData() {
        const data = {};
        fields.forEach(field => {
            if (field.type === 'checkbox') data[field.name] = 1;
            else if (field.name === 'role') data[field.name] = 'scanner';
            else data[field.name] = '';
        });
        return data;
    }

    function normalizeRow(row) {
        const data = {};
        fields.forEach(field => {
            if (field.type === 'checkbox') {
                data[field.name] = row[field.name] ? 1 : 0;
            } else if (field.name === 'password') {
                data[field.name] = '';
            } else {
                data[field.name] = row[field.name] ?? '';
            }
        });
        return data;
    }

    function inlineEditorRow(mode, data) {
        const label = mode === 'create' ? 'New' : 'Edit';
        let html = `<tr class="inline-scan-editor" data-mode="${mode}">`;

        // Render each column based on generic columns definition
        columns.forEach(col => {
            if (col.data === 'no') {
                html += `<td class="inline-editor-label">${label}</td>`;
            } else {
                const field = fields.find(f => f.name === col.data);
                html += `<td>`;
                if (field) {
                    html += renderField(field, data[field.name]);
                }
                
                // Special case: if column is username, append password field underneath it if password field exists
                if (col.data === 'username') {
                    const passField = fields.find(f => f.name === 'password');
                    if (passField) {
                        const passLabel = mode === 'create' ? 'Password (Wajib Diisi)' : 'Password (Kosongkan jika tidak ubah)';
                        const passFieldConfig = { ...passField, required: mode === 'create' };
                        html += `<div style="margin-top:4px;"><label style="font-size:10px;color:var(--text-secondary);display:block;margin-bottom:2px;">${passLabel}</label>${renderField(passFieldConfig, data[passField.name])}</div>`;
                    }
                }
                html += `</td>`;
            }
        });

        // Any fields that don't belong to any column and aren't handled as special cases
        let hiddenFields = '';
        fields.forEach(field => {
            if (!columns.some(col => col.data === field.name) && field.name !== 'password') {
                hiddenFields += `<input class="inline-field" data-field="${field.name}" type="hidden" value="${escapeAttr(data[field.name])}">`;
            }
        });

        html += `<td>
                    ${hiddenFields}
                    <div class="inline-actions">
                        <button class="btn btn-primary" type="button" onclick="saveRecord()">Save</button>
                        <button class="btn" type="button" onclick="closeActiveEditor(true)">Cancel</button>
                    </div>
                </td>
            </tr>`;
        return html;
    }

    function renderField(field, value) {
        if (field.type === 'select') {
            const optionsHtml = field.options.map(opt => `<option value="${escapeAttr(opt.value)}"${String(opt.value) === String(value ?? '') ? ' selected' : ''}>${escapeHtml(opt.label)}</option>`).join('');
            return `<select class="form-control inline-field inline-select" data-field="${field.name}">${optionsHtml}</select>`;
        } else if (field.type === 'checkbox') {
            // Checkbox mapped to Active/Inactive dropdown
            return `<select class="form-control inline-field inline-select" data-field="${field.name}">
                <option value="1"${String(value) === '1' ? ' selected' : ''}>Active</option>
                <option value="0"${String(value) === '0' ? ' selected' : ''}>Inactive</option>
            </select>`;
        } else {
            return `<input class="form-control inline-field inline-input" data-field="${field.name}" type="${field.type}" value="${escapeAttr(value)}" ${field.required ? 'required' : ''}>`;
        }
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
    }

    function escapeAttr(value) {
        return escapeHtml(value);
    }

    function saveRecord() {
        if (!activeEditor) return;

        const mode = activeEditor.mode;
        const id = activeEditor.id;
        const data = formPayload();
        clearInlineErrors();

        fetch(mode === 'edit' ? `${apiBase}/${id}` : apiBase, {
            method: mode === 'edit' ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(data)
        })
        .then(async response => {
            const resData = await response.json();
            if (!response.ok || !resData.success) throw resData;
            return resData;
        })
        .then(resData => {
            clearActiveEditor();
            showToast(resData.message);
            reloadTable(false);
        })
        .catch(error => {
            showInlineError(error);
        });
    }

    function errorMessage(error) {
        if (error.message && !error.errors) return error.message;
        const first = Object.values(error.errors || {})[0];
        return first?.[0] || 'Data tidak valid.';
    }

    function showInlineError(error) {
        const messages = error.errors
            ? Object.entries(error.errors).map(([field, values]) => {
                activeEditor?.mainRow.find(`[data-field="${field}"]`).addClass('is-invalid');
                return values[0];
            })
            : [errorMessage(error)];

        $('#inlineEditorError').html(messages.map(escapeHtml).join('<br>')).addClass('active');
        showToast(messages[0] || 'Data tidak valid.', 'error');
    }

    function clearInlineErrors() {
        activeEditor?.mainRow.find('.inline-field').removeClass('is-invalid');
        $('#inlineEditorError').removeClass('active').empty();
    }

    function activateRecord(id) {
        fetch(`${activateBase}/${id}/activate`, { method: 'POST', headers: { Accept: 'application/json' } })
            .then(r => r.json()).then(response => {
                if (response.success) { showToast(response.message); masterTable.ajax.reload(null, false); }
                else showToast(response.message || 'Gagal aktivasi.', 'error');
            });
    }

    function deleteRecord(id) {
        if (!confirm('Hapus data ini?')) return;
        fetch(`${apiBase}/${id}`, { method: 'DELETE', headers: { Accept: 'application/json' } })
            .then(r => r.json()).then(response => {
                if (response.success) { showToast(response.message); masterTable.ajax.reload(null, false); }
                else showToast(response.message || 'Gagal hapus data.', 'error');
            });
    }
</script>
@endpush

</x-layouts.app>
