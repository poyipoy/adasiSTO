{{-- [DISABLED] Legacy file. This logic is handled by generic.blade.php. Do not use. --}}
<x-layouts.app :title="'Master Plant'">

{{-- Toolbar --}}
<div class="enterprise-toolbar">
    <button class="btn btn-primary" onclick="openAddModal()">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
        Tambah Plant
    </button>
</div>

<div class="table-container" style="border-top: none;">
    <table class="table-enterprise" id="plantTable">
        <thead>
            <tr>
                <th style="width:50px; text-align:center;">No</th>
                <th style="width:150px;">Kode</th>
                <th>Nama</th>
                <th style="width:100px;">Status</th>
                <th style="width:80px;text-align:center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plants as $i => $plant)
            <tr id="plant-{{ $plant->id }}">
                <td style="text-align:center; color:var(--text-muted);">{{ $i + 1 }}</td>
                <td class="mono" style="font-weight:600;color:var(--primary);">{{ $plant->code }}</td>
                <td>{{ $plant->name }}</td>
                <td><span class="badge {{ $plant->is_active ? 'badge-valid' : 'badge-invalid' }}">{{ $plant->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td style="text-align:center;">
                    <div style="display:inline-flex;gap:4px;">
                        <button class="btn-icon" onclick="openInlineEdit(this, {{ $plant->id }}, '{{ $plant->code }}', '{{ $plant->name }}', {{ $plant->is_active ? 'true' : 'false' }})" title="Edit">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        <button class="btn-icon" onclick="deleteItem({{ $plant->id }})" title="Delete" style="color:var(--danger);">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Add/Edit Modal --}}
<div class="modal-overlay" id="plantModal">
    <div class="modal-content">
        <div class="modal-header">
            <span id="modalTitle">Tambah Plant</span>
            <button class="btn-icon" onclick="closeModal()" style="border:none;background:none;font-size:16px;">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editingId">
            <div class="form-group">
                <label class="form-label">Kode Plant</label>
                <input type="text" id="plantCode" class="form-control" placeholder="Contoh: CKR">
            </div>
            <div class="form-group">
                <label class="form-label">Nama Plant</label>
                <input type="text" id="plantName" class="form-control" placeholder="Contoh: Cikarang">
            </div>
            <div class="form-group" id="statusGroup" style="display:none;">
                <label class="form-label">Status</label>
                <select id="plantActive" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="saveItem()" id="saveBtn">Simpan</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Plant';
        document.getElementById('editingId').value = '';
        document.getElementById('plantCode').value = '';
        document.getElementById('plantName').value = '';
        document.getElementById('statusGroup').style.display = 'none';
        document.getElementById('plantModal').classList.add('active');
    }

    let currentEditId = null;

    function openInlineEdit(btn, id, code, name, isActive) {
        if (currentEditId) {
            cancelInlineEdit(currentEditId);
        }
        currentEditId = id;
        
        const tr = btn.closest('tr');
        const activeSelected = isActive ? 'selected' : '';
        const inactiveSelected = !isActive ? 'selected' : '';

        const editTr = document.createElement('tr');
        editTr.id = `inline_edit_row_${id}`;
        editTr.className = 'inline-edit-child shown';
        editTr.innerHTML = `
            <td colspan="5">
                <div class="inline-edit-wrap">
                    <div class="inline-edit-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="ie-field"><label>Kode Plant</label><input type="text" id="ie_code_${id}" value="${code}"></div>
                        <div class="ie-field"><label>Nama Plant</label><input type="text" id="ie_name_${id}" value="${name}"></div>
                        <div class="ie-field"><label>Status</label>
                            <select id="ie_active_${id}">
                                <option value="1" ${activeSelected}>Aktif</option>
                                <option value="0" ${inactiveSelected}>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="inline-edit-actions">
                        <button class="btn" onclick="cancelInlineEdit(${id})">Batal</button>
                        <button class="btn btn-primary" onclick="saveInlineEdit(${id})">Simpan Perubahan</button>
                    </div>
                </div>
            </td>
        `;

        tr.insertAdjacentElement('afterend', editTr);
    }

    function cancelInlineEdit(id) {
        const row = document.getElementById(`inline_edit_row_${id}`);
        if (row) row.remove();
        if (currentEditId === id) currentEditId = null;
    }

    function saveInlineEdit(id) {
        const data = {
            code: document.getElementById(`ie_code_${id}`).value,
            name: document.getElementById(`ie_name_${id}`).value,
            is_active: document.getElementById(`ie_active_${id}`).value === '1'
        };

        fetch(`/admin/master/plants/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message);
                location.reload();
            } else {
                showToast('Gagal update', 'error');
            }
        })
        .catch(err => {
            showToast('Terjadi kesalahan', 'error');
        });
    }

    function closeModal() {
        document.getElementById('plantModal').classList.remove('active');
    }

    function saveItem() {
        const data = {
            code: document.getElementById('plantCode').value,
            name: document.getElementById('plantName').value,
        };

        fetch('/admin/master/plants', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message);
                closeModal();
                location.reload();
            } else {
                showToast('Gagal menyimpan', 'error');
            }
        })
        .catch(err => {
            err.response?.json?.().then(d => showToast(d.message || 'Error', 'error'));
        });
    }



    function deleteItem(id) {
        confirmAction('Yakin ingin menghapus plant ini?', () => {
            fetch(`/admin/master/plants/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    document.getElementById(`plant-${id}`).remove();
                }
            });
        });
    }
</script>
@endpush

</x-layouts.app>
