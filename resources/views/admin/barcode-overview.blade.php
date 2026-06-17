<x-layouts.app :title="'Material Summary'">

<div class="card" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div style="width:150px;"><label class="form-label">Plant</label><select id="filterPlant" class="form-control"><option value="">All</option>@foreach($plants as $plant)<option value="{{ $plant->id }}">{{ $plant->name }}</option>@endforeach</select></div>
    <div style="width:130px;"><label class="form-label">Material Code</label><input type="text" id="filterMaterialCode" class="form-control" placeholder="Code..."></div>
    <div style="width:130px;"><label class="form-label">Material</label><input type="text" id="filterMaterialName" class="form-control" placeholder="Name..."></div>
    <div style="width:130px;">
        <label class="form-label">Shape</label>
        <select id="filterShape" class="form-control">
            <option value="">All</option>
            <option value="RF">Flat (RF)</option>
            <option value="RR">Round (RR)</option>
        </select>
    </div>
    <div style="width:150px;"><label class="form-label">Date From</label><input type="date" id="filterDateFrom" class="form-control"></div>
    <div style="width:150px;"><label class="form-label">Date To</label><input type="date" id="filterDateTo" class="form-control"></div>
    <button class="btn btn-primary" onclick="summaryTable.ajax.reload()">Filter</button>
</div>

<div class="table-container" style="border-top:0;">
    <table id="summaryTable" class="table-enterprise" style="width:100%;">
        <thead><tr><th>No</th><th>QR Code</th><th>Material Code</th><th>Material</th><th>Shape</th><th>Size</th><th>Qty Total</th><th>Scan Count</th></tr></thead>
    </table>
</div>

@push('scripts')
<script>
    let summaryTable;
    $(document).ready(function() {
        summaryTable = $('#summaryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.api.material-summary") }}',
                data: d => {
                    d.plant_id = $('#filterPlant').val();
                    d.sto_code = $('#filterSto').val();
                    d.material_code = $('#filterMaterialCode').val();
                    d.material_name = $('#filterMaterialName').val();
                    d.shape_code = $('#filterShape').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },
            order: [],
            columns: [
                { data: 'no', orderable: false }, { data: 'barcode_material', className: 'mono' }, { data: 'material_code' },
                { data: 'material_name' }, { data: 'shape_name' }, { data: 'size' }, { data: 'qty_total' }, { data: 'scan_count' }
            ],
        });
    });
</script>
@endpush

</x-layouts.app>
