<x-layouts.app :title="'Monitoring Hasil Scan'">

@push('styles')
<style>
    .table-enterprise.nowrap th,
    .table-enterprise.nowrap td {
        white-space: nowrap;
    }
</style>
@endpush

{{-- Toolbar --}}
<div class="enterprise-toolbar">
    <button class="btn btn-icon" onclick="reloadTable()" title="Refresh">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
    </button>
    <button class="btn btn-icon" onclick="resetFilters()" title="Reset Filters">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </button>
    
    <div class="toolbar-sep"></div>
    
    <a href="{{ route('admin.scan-results.export') }}" id="exportBtn" class="btn btn-success">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
        Export Excel
    </a>
</div>

{{-- Filters (Infor style: under toolbar, above table) --}}
<div style="background: var(--surface); border: 1px solid var(--border); border-top: none; padding: 10px; display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
    <div style="width: 140px;">
        <label class="form-label">Plant</label>
        <select id="filterPlant" class="form-control">
            <option value="">Semua</option>
            @foreach($plants as $plant)
            <option value="{{ $plant->id }}">{{ $plant->name }}</option>
            @endforeach
        </select>
    </div>
    <div style="width: 140px;">
        <label class="form-label">User</label>
        <select id="filterUser" class="form-control">
            <option value="">Semua</option>
            @foreach($users as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div style="width: 140px;">
        <label class="form-label">STO Code</label>
        <select id="filterSto" class="form-control">
            <option value="">Semua</option>
            @foreach($stoCodes as $code)
            <option value="{{ $code }}">{{ $code }}</option>
            @endforeach
        </select>
    </div>
    <div style="width: 140px;">
        <label class="form-label">Keterangan</label>
        <select id="filterKeterangan" class="form-control">
            <option value="">Semua</option>
            @foreach($keteranganList as $ket)
            <option value="{{ $ket }}">{{ $ket }}</option>
            @endforeach
        </select>
    </div>
    <div style="width: 120px;">
        <label class="form-label">Dari</label>
        <input type="date" id="filterDateFrom" class="form-control">
    </div>
    <div style="width: 120px;">
        <label class="form-label">Sampai</label>
        <input type="date" id="filterDateTo" class="form-control">
    </div>
    <div>
        <button class="btn btn-primary" onclick="reloadTable()">Filter</button>
    </div>
</div>

{{-- Data Table --}}
<div class="table-container" style="border-top: none;">
    <table id="adminScanTable" class="table-enterprise nowrap" style="width:100%;">
        <thead>
            <tr>
                <th>No</th>
                <th>Barcode</th>
                <th>Material</th>
                <th>Shape</th>
                <th>T</th>
                <th>W</th>
                <th>D</th>
                <th>L</th>
                <th>Qty</th>
                <th>Lot</th>
                <th>User</th>
                <th>Plant</th>
                <th>Lokasi</th>
                <th>Jam</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
    </table>
</div>



@push('scripts')
<script>
    let adminTable;

    $(document).ready(function() {
        adminTable = $('#adminScanTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.scan-results.datatable") }}',
                data: function(d) {
                    d.plant_id = $('#filterPlant').val();
                    d.user_id = $('#filterUser').val();
                    d.sto_code = $('#filterSto').val();
                    d.keterangan = $('#filterKeterangan').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },
            order: [],
            columnDefs: [
                { className: "dt-center", targets: [0, 4, 5, 6, 7, 8, 9, 14, 15] },
            ],
            columns: [
                { data: 'no', orderable: false, width: '40px' },
                { data: 'barcode', render: d => `<span class="mono" style="color:var(--primary);font-weight:600;">${d}</span>` },
                { data: 'material' },
                { data: 'shape' },
                { data: 'thickness', render: d => d || '-' },
                { data: 'width', render: d => d || '-' },
                { data: 'diameter', render: d => d || '-' },
                { data: 'length', render: d => d || '-' },
                { data: 'qty' },
                { data: 'lot', render: d => `<span class="mono">${d}</span>` },
                { data: 'user' },
                { data: 'plant' },
                { data: 'location' },
                { data: 'scan_time', render: d => `<span class="mono">${d}</span>` },
                {
                    data: 'keterangan',
                    render: function(d) {
                        const cls = d === 'OK' ? 'badge-valid' : 'badge-invalid';
                        return `<span class="badge ${cls}">${d}</span>`;
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(id) {
                        return `
                            <div style="display:flex;gap:4px;">
                                <button class="btn-icon" onclick="openInlineEdit(this, ${id})" title="Edit">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <button class="btn-icon" onclick="deleteResult(${id})" title="Delete" style="color:var(--danger);">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        `;
                    }
                },
            ],
            language: {
                search: 'Cari Data:',
                lengthMenu: 'Tampilkan _MENU_',
                info: '_START_ - _END_ dari _TOTAL_',
                paginate: { previous: '‹', next: '›' },
                emptyTable: 'Tidak ada data',
                processing: '<span style="color:var(--primary);font-weight:600;">Memuat...</span>',
            },
            pageLength: 25,
            scrollX: true,
        });

        $('#filterDateFrom, #filterDateTo').on('change', reloadTable);
    });

    function reloadTable() {
        adminTable.ajax.reload();
        updateExportLink();
    }

    function resetFilters() {
        $('#filterPlant, #filterUser, #filterSto, #filterKeterangan, #filterDateFrom, #filterDateTo').val('');
        reloadTable();
    }

    function updateExportLink() {
        const params = new URLSearchParams({
            plant_id: $('#filterPlant').val() || '',
            user_id: $('#filterUser').val() || '',
            sto_code: $('#filterSto').val() || '',
            keterangan: $('#filterKeterangan').val() || '',
            date_from: $('#filterDateFrom').val() || '',
            date_to: $('#filterDateTo').val() || '',
        });
        document.getElementById('exportBtn').href = '{{ route("admin.scan-results.export") }}?' + params.toString();
    }

    let currentEditRow = null;

    function openInlineEdit(btn, id) {
        if (currentEditRow) {
            currentEditRow.child.hide();
            $(currentEditRow.node()).removeClass('shown');
            currentEditRow = null;
        }

        var tr = $(btn).closest('tr');
        var row = adminTable.row(tr);
        
        var btnHtml = $(btn).html();
        $(btn).html('...').prop('disabled', true);

        fetch(`/admin/scan-results/${id}/edit`)
            .then(r => r.json())
            .then(res => {
                $(btn).html(btnHtml).prop('disabled', false);

                const data = res.data;
                let locationOptions = res.locations.map(l => `<option value="${l.id}" ${l.id === data.location_id ? 'selected' : ''}>${l.name}</option>`).join('');
                let plantOptions = res.plants.map(p => `<option value="${p.id}" ${p.id === data.plant_id ? 'selected' : ''}>${p.name}</option>`).join('');
                let userOptions = res.users.map(u => `<option value="${u.id}" ${u.id === data.user_id ? 'selected' : ''}>${u.name}</option>`).join('');
                let keteranganOptions = res.keterangan_list.map(k => `<option value="${k}" ${k === data.keterangan ? 'selected' : ''}>${k}</option>`).join('');
                let scanTime = data.scan_time || '';

                let editHtml = `
                <div class="inline-edit-wrap">
                    <div class="inline-edit-grid">
                        <div class="ie-field"><label>Barcode</label><input type="text" id="ie_barcode_${id}" value="${data.barcode_material}"></div>
                        <div class="ie-field"><label>Material</label><input type="text" id="ie_material_${id}" value="${data.material_name || ''}"></div>
                        <div class="ie-field"><label>Shape</label><input type="text" id="ie_shape_${id}" value="${data.shape_name || ''}"></div>
                        <div class="ie-field"><label>Thickness (T)</label><input type="number" step="0.01" id="ie_thickness_${id}" value="${data.thickness || ''}"></div>
                        <div class="ie-field"><label>Width (W)</label><input type="number" step="0.01" id="ie_width_${id}" value="${data.width || ''}"></div>
                        <div class="ie-field"><label>Diameter (D)</label><input type="number" step="0.01" id="ie_diameter_${id}" value="${data.diameter || ''}"></div>
                        <div class="ie-field"><label>Length (L)</label><input type="number" step="0.01" id="ie_length_${id}" value="${data.length || ''}"></div>
                        <div class="ie-field"><label>Qty</label><input type="number" id="ie_qty_${id}" value="${data.qty}" min="1"></div>
                        <div class="ie-field"><label>Lot</label><input type="text" id="ie_lot_${id}" value="${data.lot || ''}"></div>
                        <div class="ie-field"><label>User</label><select id="ie_user_${id}">${userOptions}</select></div>
                        <div class="ie-field"><label>Plant</label><select id="ie_plant_${id}" onchange="fetchLocationsForInline(this.value, ${id})">${plantOptions}</select></div>
                        <div class="ie-field"><label>Lokasi</label><select id="ie_location_${id}">${locationOptions}</select></div>
                        <div class="ie-field"><label>Jam Scan</label><input type="text" id="ie_scantime_${id}" value="${scanTime}"></div>
                        <div class="ie-field"><label>Keterangan</label><select id="ie_keterangan_${id}">${keteranganOptions}</select></div>
                    </div>
                    <div class="inline-edit-actions">
                        <button class="btn" onclick="cancelInlineEdit()">Batal</button>
                        <button class="btn btn-primary" onclick="saveInlineEdit(${id})">Simpan Perubahan</button>
                    </div>
                </div>`;

                // Use DataTables child row API for proper rendering
                row.child(editHtml).show();
                $(row.node()).addClass('shown');
                currentEditRow = row;
            });
    }

    function cancelInlineEdit() {
        if (currentEditRow) {
            currentEditRow.child.hide();
            $(currentEditRow.node()).removeClass('shown');
            currentEditRow = null;
        }
    }

    function fetchLocationsForInline(plantId, id) {
        if (!plantId) return;
        fetch(`/api/locations/${plantId}`)
            .then(r => r.json())
            .then(locations => {
                let options = locations.map(l => `<option value="${l.id}">${l.name}</option>`).join('');
                document.getElementById(`ie_location_${id}`).innerHTML = options;
            });
    }

    function saveInlineEdit(id) {
        fetch(`/admin/scan-results/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                barcode_material: document.getElementById(`ie_barcode_${id}`).value,
                material_name: document.getElementById(`ie_material_${id}`).value,
                shape_name: document.getElementById(`ie_shape_${id}`).value,
                thickness: document.getElementById(`ie_thickness_${id}`).value || null,
                width: document.getElementById(`ie_width_${id}`).value || null,
                diameter: document.getElementById(`ie_diameter_${id}`).value || null,
                length: document.getElementById(`ie_length_${id}`).value || null,
                lot: document.getElementById(`ie_lot_${id}`).value,
                qty: parseInt(document.getElementById(`ie_qty_${id}`).value),
                scan_time: document.getElementById(`ie_scantime_${id}`).value,
                user_id: document.getElementById(`ie_user_${id}`).value,
                plant_id: document.getElementById(`ie_plant_${id}`).value,
                location_id: document.getElementById(`ie_location_${id}`).value,
                keterangan: document.getElementById(`ie_keterangan_${id}`).value,
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
                if (currentEditRow) {
                    currentEditRow.child.hide();
                    $(currentEditRow.node()).removeClass('shown');
                    currentEditRow = null;
                }
                adminTable.ajax.reload(null, false);
            } else {
                showToast('Gagal update', 'error');
            }
        })
        .catch(err => {
            showToast('Terjadi kesalahan', 'error');
            console.error(err);
        });
    }

    function deleteResult(id) {
        if (!confirm('Yakin ingin menghapus data scan ini?')) return;

        fetch(`/admin/scan-results/${id}`, {
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
                adminTable.ajax.reload(null, false);
            }
        });
    }
</script>
@endpush

</x-layouts.app>
