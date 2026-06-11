<x-layouts.app :title="'Master Keterangan'">

{{-- Toolbar --}}
<div class="enterprise-toolbar">
    <button class="btn btn-primary" onclick="openAddModal()">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
        Tambah Keterangan
    </button>
</div>

<div class="table-container" style="border-top: none;">
    <table class="table-enterprise" id="ketTable">
        <thead>
            <tr>
                <th style="width:50px;text-align:center;">No</th>
                <th>Nama Keterangan</th>
                <th style="width:100px;">Status</th>
                <th style="width:80px;text-align:center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($keteranganList as $i => $ket)
            <tr id="ket-{{ $ket->id }}">
                <td style="text-align:center;color:var(--text-muted);">{{ $i + 1 }}</td>
                <td style="font-weight:600;color:var(--text);">{{ $ket->name }}</td>
                <td><span class="badge {{ $ket->is_active ? 'badge-valid' : 'badge-invalid' }}">{{ $ket->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td style="text-align:center;">
                    <div style="display:inline-flex;gap:4px;">
                        <button class="btn-icon" onclick="openInlineEdit(this, {{ $ket->id }}, '{{ $ket->name }}', {{ $ket->is_active ? 'true' : 'false' }})" title="Edit">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        <button class="btn-icon" onclick="deleteItem({{ $ket->id }})" title="Delete" style="color:var(--danger);">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Modal --}}
<div class="modal-overlay" id="ketModal">
    <div class="modal-content">
        <div class="modal-header">
            <span id="modalTitle">Tambah Keterangan</span>
            <button class="btn-icon" onclick="closeModal()" style="border:none;background:none;font-size:16px;">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editingId">
            <div class="form-group">
                <label class="form-label">Nama Keterangan</label>
                <input type="text" id="ketName" class="form-control" placeholder="Contoh: OK">
            </div>
            <div class="form-group" id="statusGroup" style="display:none;">
                <label class="form-label">Status</label>
                <select id="ketActive" class="form-control">
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
    let ketTable;
    $(document).ready(function() {
        ketTable = $('#ketTable').DataTable({
            language: {
                search: 'Cari Data:',
                lengthMenu: 'Tampilkan _MENU_',
                info: '_START_ - _END_ dari _TOTAL_',
                paginate: { previous: '‹', next: '›' }
            }
        });
    });

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Keterangan';
        document.getElementById('editingId').value = '';
        document.getElementById('ketName').value = '';
        document.getElementById('statusGroup').style.display = 'none';
        document.getElementById('ketModal').classList.add('active');
    }

    let currentEditRow = null;

    function openInlineEdit(btn, id, name, isActive) {
        if (currentEditRow) {
            currentEditRow.child.hide();
            $(currentEditRow.node()).removeClass('shown');
            currentEditRow = null;
        }
        
        const tr = btn.closest('tr');
        const row = ketTable.row(tr);
        const activeSelected = isActive ? 'selected' : '';
        const inactiveSelected = !isActive ? 'selected' : '';

        const editHtml = `
            <div class="inline-edit-wrap">
                <div class="inline-edit-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="ie-field"><label>Nama Keterangan</label><input type="text" id="ie_name_${id}" value="${name}"></div>
                    <div class="ie-field"><label>Status</label>
                        <select id="ie_active_${id}">
                            <option value="1" ${activeSelected}>Aktif</option>
                            <option value="0" ${inactiveSelected}>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="inline-edit-actions">
                    <button class="btn" onclick="cancelInlineEdit()">Batal</button>
                    <button class="btn btn-primary" onclick="saveInlineEdit(${id})">Simpan Perubahan</button>
                </div>
            </div>
        `;

        row.child(editHtml).show();
        $(row.node()).addClass('shown');
        currentEditRow = row;
    }

    function cancelInlineEdit() {
        if (currentEditRow) {
            currentEditRow.child.hide();
            $(currentEditRow.node()).removeClass('shown');
            currentEditRow = null;
        }
    }

    function closeModal() { document.getElementById('ketModal').classList.remove('active'); }

    function saveItem() {
        const data = { name: document.getElementById('ketName').value };

        fetch('/admin/master/keterangan', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify(data)
        }).then(r => r.json()).then(res => { if (res.success) { showToast(res.message); closeModal(); location.reload(); } });
    }

    function saveInlineEdit(id) {
        const data = { 
            name: document.getElementById(`ie_name_${id}`).value,
            is_active: document.getElementById(`ie_active_${id}`).value === '1'
        };
        
        fetch(`/admin/master/keterangan/${id}`, {
            method: 'PUT', 
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify(data)
        }).then(r => r.json()).then(res => { 
            if (res.success) { 
                showToast(res.message); 
                location.reload(); 
            } 
        });
    }

    function deleteItem(id) {
        if (!confirm('Yakin?')) return;
        fetch(`/admin/master/keterangan/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(r => r.json()).then(d => { if (d.success) { showToast(d.message); location.reload(); } });
    }
</script>
@endpush

</x-layouts.app>
