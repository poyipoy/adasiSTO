<x-layouts.app :title="'Overview Barcode Sama'">

    <details class="glass-card animate-in"
        style="margin-bottom: 1rem; padding: 1rem; cursor: pointer; transition: all 0.2s;">
        <summary
            style="font-weight: 600; display: flex; align-items: center; gap: 8px; user-select: none; outline: none; list-style: none;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                </path>
            </svg>
            Filter Data
        </summary>

        <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem; cursor: default;">
            <div class="form-group" style="flex: 1; min-width: 140px;">
                <label class="form-label">Plant</label>
                <select id="filterPlant" class="form-control">
                    <option value="">Semua Plant</option>
                    @foreach($plants as $plant)
                        <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="flex: 1; min-width: 140px;">
                <label class="form-label">STO Code</label>
                <select id="filterSto" class="form-control">
                    <option value="">Semua STO</option>
                    @foreach($stoCodes as $code)
                        <option value="{{ $code }}">{{ $code }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="flex: 2; min-width: 260px;">
                <label class="form-label">Rentang Tanggal</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="date" id="filterDateFrom" class="form-control" title="Dari Tanggal">
                    <span style="align-self: center; color: var(--text-muted); font-weight: 600;">-</span>
                    <input type="date" id="filterDateTo" class="form-control" title="Sampai Tanggal">
                </div>
            </div>

            <div class="form-group" style="display:flex; gap:0.5rem; align-items:flex-end;">
                <button class="btn btn-primary" onclick="overviewTable.ajax.reload()" title="Terapkan Filter"
                    style="padding: 0 1rem; height: 36px; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
                <button class="btn btn-secondary"
                    onclick="$('#filterPlant,#filterSto,#filterDateFrom,#filterDateTo').val(''); overviewTable.ajax.reload();"
                    title="Reset Filter"
                    style="padding: 0 1rem; height: 36px; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </details>

    {{-- Overview Table --}}
    <div class="glass-card animate-in animate-delay-1">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <h3 style="font-size: 1rem; font-weight: 700;">
                🔍 Grouping Barcode Sama
            </h3>
            <span class="badge badge-info" style="font-size: 0.75rem;">Qty Total = Akumulasi semua scan</span>
        </div>

        <div class="table-responsive">
            <table id="overviewTable" class="display" style="width: 100%;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Barcode</th>
                        <th>Material</th>
                        <th>Shape</th>
                        <th>Size</th>
                        <th>Qty Total</th>
                        <th>Jumlah Scan</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            let overviewTable;

            $(document).ready(function () {
                overviewTable = $('#overviewTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("admin.barcode-overview.datatable") }}',
                        data: function (d) {
                            d.plant_id = $('#filterPlant').val();
                            d.sto_code = $('#filterSto').val();
                            d.date_from = $('#filterDateFrom').val();
                            d.date_to = $('#filterDateTo').val();
                        }
                    },
                    order: [],
                    columns: [
                        { data: 'no', orderable: false, className: 'mono', width: '50px' },
                        { data: 'barcode', className: 'mono', render: d => `<span style="color:#818cf8;font-weight:600;">${d}</span>` },
                        { data: 'material' },
                        { data: 'shape' },
                        { data: 'size' },
                        { data: 'qty_total', className: 'mono', render: d => `<span style="font-weight:700;color:#34d399;font-size:1rem;">${d}</span>` },
                        { data: 'scan_count', className: 'mono', render: d => `<span class="badge badge-info">${d}x</span>` },
                    ],
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_',
                        info: '_START_ - _END_ dari _TOTAL_',
                        paginate: { previous: '‹', next: '›' },
                        emptyTable: 'Tidak ada data',
                        processing: '<span style="color:var(--accent-primary);">Memuat...</span>',
                    },
                    pageLength: 25,
                });
            });
        </script>
    @endpush

</x-layouts.app>