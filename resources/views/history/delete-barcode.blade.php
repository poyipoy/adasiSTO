<x-layouts.app :title="'History Delete Barcode'">
@push('styles')
<style>
    .delete-history-filters { display:grid; grid-template-columns:repeat(6,minmax(130px,1fr)); gap:10px; align-items:end; }
    .delete-history-detail { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px 18px; }
    .delete-history-detail div { min-width:0; }
    .delete-history-detail span { display:block; color:var(--text-secondary); font-size:11px; margin-bottom:3px; }
    .delete-history-detail strong { display:block; overflow-wrap:anywhere; }
    .delete-history-snapshot { margin-top:14px; padding:10px; max-height:220px; overflow:auto; background:#f6f7f9; border:1px solid var(--border-light); font-size:11px; white-space:pre-wrap; }
    @media (max-width: 900px) {
        .delete-history-filters { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .delete-history-detail { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media (max-width: 520px) {
        .delete-history-filters, .delete-history-detail { grid-template-columns:1fr; }
    }
</style>
@endpush

<div class="enterprise-toolbar">
    <button class="btn btn-icon" type="button" onclick="reloadDeleteHistory()">Refresh</button>
    <button class="btn btn-icon" type="button" onclick="resetDeleteHistoryFilters()">Reset</button>
</div>

<div class="card" style="border-top:0;">
    <div class="delete-history-filters">
        <div>
            <label class="form-label" for="historyPlant">Plant</label>
            <select id="historyPlant" class="form-control">
                <option value="">All</option>
                @foreach($plants as $plant)
                    <option value="{{ $plant->plant_id }}">{{ $plant->plant_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label" for="historyLocation">Location</label>
            <select id="historyLocation" class="form-control" data-searchable-location data-search-title="Filter Location">
                <option value="">All</option>
                @foreach($locations as $location)
                    <option value="{{ $location->location_id }}" data-plant-id="{{ $location->plant_id }}">{{ $location->location_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label" for="historyActor">Deleted By</label>
            <select id="historyActor" class="form-control">
                <option value="">All</option>
                @foreach($actors as $actor)
                    <option value="{{ $actor->deleted_by_user_id }}">{{ $actor->deleted_by_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label" for="historySource">Source</label>
            <select id="historySource" class="form-control">
                <option value="">All</option>
                @foreach($sources as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label" for="historyDateFrom">Date From</label>
            <input id="historyDateFrom" class="form-control" type="date">
        </div>
        <div>
            <label class="form-label" for="historyDateTo">Date To</label>
            <input id="historyDateTo" class="form-control" type="date">
        </div>
    </div>
    <button class="btn btn-primary" type="button" style="margin-top:10px;" onclick="applyDeleteHistoryFilters()">Filter</button>
</div>

<div class="table-container" style="border-top:0;">
    <table id="deleteHistoryTable" class="table-enterprise" style="width:100%;">
        <thead>
            <tr>
                <th>No</th><th>QR Code</th><th>Material</th><th>Shape</th><th>Size</th>
                <th>Owner</th><th>Plant</th><th>Location</th><th>Deleted By</th><th>Source</th>
                <th>Scan Time</th><th>Deleted Time</th><th>Action</th>
            </tr>
        </thead>
    </table>
</div>

<div class="modal-overlay" id="deleteHistoryDetailModal">
    <div class="modal-content" style="max-width:860px;">
        <div class="modal-header">
            <strong>Delete Snapshot</strong>
            <button class="btn-icon" type="button" onclick="closeDeleteHistoryDetail()">X</button>
        </div>
        <div class="modal-body">
            <div id="deleteHistoryDetail" class="delete-history-detail"></div>
            <pre id="deleteHistorySnapshot" class="delete-history-snapshot"></pre>
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeDeleteHistoryDetail()">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let deleteHistoryTable;
    const deleteHistoryApi = @json(route('api.history.delete-barcode'));
    const deleteHistoryDetailBase = @json(url('/api/history/delete-barcode'));

    function escapeDeleteHistory(value) {
        return String(value ?? '-').replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[char]));
    }

    function deleteHistoryFilters() {
        return {
            plant_id: $('#historyPlant').val(),
            location_id: $('#historyLocation').val(),
            deleted_by_user_id: $('#historyActor').val(),
            deletion_source: $('#historySource').val(),
            date_from: $('#historyDateFrom').val(),
            date_to: $('#historyDateTo').val()
        };
    }

    function applyDeleteHistoryFilters() { deleteHistoryTable.ajax.reload(null, true); }
    function reloadDeleteHistory() { deleteHistoryTable.ajax.reload(null, false); }

    function resetDeleteHistoryFilters() {
        $('#historyPlant,#historyLocation,#historyActor,#historySource,#historyDateFrom,#historyDateTo').val('');
        $('#historyLocation option').prop('disabled', false);
        deleteHistoryTable.search('').order([]).ajax.reload(null, true);
    }

    async function showDeleteHistoryDetail(id) {
        try {
            const response = await fetch(`${deleteHistoryDetailBase}/${id}`, { headers: { Accept: 'application/json' } });
            const payload = await response.json();
            if (!response.ok) throw payload;
            const item = payload.data;
            const fields = [
                ['QR Code', item.barcode_material], ['Raw Barcode', item.barcode_raw], ['Material', `${item.material_name || '-'} (${item.material_code || '-'})`],
                ['Shape / Size', `${item.shape_name || '-'} / ${item.size || '-'}`], ['Lot / Qty', `${item.lot_number || '-'} / ${item.qty || 0} pcs`], ['Keterangan', item.keterangan],
                ['Original Owner', item.original_user_name], ['Plant / Location', `${item.plant_name || '-'} / ${item.location_name || '-'}`], ['STO', item.sto_code],
                ['Deleted By', item.deleted_by_name], ['Source', item.deletion_source], ['Reason', item.deletion_reason || '-'],
                ['Original Scan Time', item.original_scanned_at], ['Deleted Time', item.deleted_at], ['Original Scan ID', item.original_scan_result_id]
            ];
            document.getElementById('deleteHistoryDetail').innerHTML = fields.map(field =>
                `<div><span>${escapeDeleteHistory(field[0])}</span><strong>${escapeDeleteHistory(field[1])}</strong></div>`
            ).join('');
            document.getElementById('deleteHistorySnapshot').textContent = JSON.stringify(item.snapshot || {}, null, 2);
            $('#deleteHistoryDetailModal').addClass('active');
        } catch (error) {
            Swal.fire('Gagal', error.message || 'Detail history gagal dimuat.', 'error');
        }
    }

    function closeDeleteHistoryDetail() { $('#deleteHistoryDetailModal').removeClass('active'); }

    $(document).ready(function () {
        $('#historyPlant').on('change', function () {
            const plantId = this.value;
            $('#historyLocation').val('');
            $('#historyLocation option').each(function () {
                if (!this.value) return;
                this.disabled = !!plantId && String(this.dataset.plantId) !== String(plantId);
            });
        });

        deleteHistoryTable = $('#deleteHistoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: deleteHistoryApi, data: data => Object.assign(data, deleteHistoryFilters()) },
            pageLength: 25,
            order: [],
            columns: [
                { data: 'no', orderable: false },
                { data: 'barcode_material', className: 'mono' },
                { data: 'material_name' },
                { data: 'shape_name' },
                { data: 'size' },
                { data: 'original_user_name' },
                { data: 'plant_name' },
                { data: 'location_name' },
                { data: 'deleted_by_name' },
                { data: 'deletion_source' },
                { data: 'original_scanned_at', className: 'mono' },
                { data: 'deleted_at', className: 'mono' },
                { data: 'id', orderable: false, searchable: false, render: id => `<button class="btn btn-secondary" type="button" onclick="showDeleteHistoryDetail(${Number(id)})">Detail</button>` }
            ],
            language: { emptyTable: 'Belum ada history delete barcode.' }
        });
    });
</script>
@endpush
</x-layouts.app>
