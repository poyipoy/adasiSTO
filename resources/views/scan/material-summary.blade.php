<x-layouts.app :title="'Material Summary'">

@push('styles')
    <style>
        .srch-filter-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1050;
            display: flex; align-items: flex-end; justify-content: center;
        }
        .srch-filter-content {
            background: #fff; width: 100%; max-width: 500px; max-height: 85vh;
            border-radius: 16px 16px 0 0;
            display: flex; flex-direction: column;
            animation: slideUp 0.3s ease-out;
        }
        @media (min-width: 768px) {
            .srch-filter-overlay { align-items: center; }
            .srch-filter-content { border-radius: 12px; max-height: 80vh; animation: fadeIn 0.2s ease-out; }
        }
        .srch-filter-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 16px; border-bottom: 1px solid var(--border-light);
        }
        .srch-filter-search {
            padding: 12px 16px; border-bottom: 1px solid var(--border-light); background: #fafbfc;
        }
        .srch-filter-list { flex: 1; overflow-y: auto; padding: 8px 16px 16px 16px; }
        .srch-filter-item {
            padding: 12px; border-bottom: 1px solid var(--border-light);
            cursor: pointer; font-size: 14px; font-weight: 500; color: var(--text);
            transition: background 0.1s;
        }
        .srch-filter-item:last-child { border-bottom: none; }
        .srch-filter-item:hover { background: #f4f8ff; }
        .srch-filter-item.active {
            background: var(--primary); color: #fff;
            border-radius: 6px; border-bottom: none; margin-bottom: 1px; font-weight: 600;
        }
        .srch-filter-trigger {
            text-align: left; background: #fff; cursor: pointer;
            color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .srch-filter-trigger.has-value { color: var(--primary); font-weight: 600; }
    </style>
@endpush

<div class="card" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div style="width:150px;">
        <label class="form-label">Plant</label>
        <select id="filterPlant" class="form-control">
            <option value="">All</option>
            @foreach($plants as $plant)
                <option value="{{ $plant->id }}">{{ $plant->name }}</option>
            @endforeach
        </select>
    </div>
    <div style="width:150px;">
        <label class="form-label">Location</label>
        <select id="filterLocation" style="display:none;">
            <option value="">All</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" data-plant-id="{{ $location->plant_id }}">{{ $location->name }}</option>
            @endforeach
        </select>
        <button type="button" id="filterLocationTrigger" class="form-control srch-filter-trigger" onclick="openSrchFilterModal('location')">All</button>
    </div>
    <div style="width:130px;"><label class="form-label">Material Code</label><input type="text" id="filterMaterialCode" class="form-control" placeholder="Code..."></div>
    <div style="width:130px;"><label class="form-label">Material</label><input type="text" id="filterMaterialName" class="form-control" placeholder="Name..."></div>
    <div style="width:130px;">
        <label class="form-label">Shape</label>
        <select id="filterShape" class="form-control">
            <option value="">All</option>
            <option value="RF">Flat (RF)</option>
            <option value="RR">Round (RR)</option>
            <option value="RH">Hollow (RH)</option>
        </select>
    </div>
    <div style="width:150px;"><label class="form-label">Date From</label><input type="date" id="filterDateFrom" class="form-control"></div>
    <div style="width:150px;"><label class="form-label">Date To</label><input type="date" id="filterDateTo" class="form-control"></div>
    <button class="btn btn-primary" onclick="summaryTable.ajax.reload()">Filter</button>
</div>

<div id="srchFilterLocationModal" class="srch-filter-overlay" style="display:none;">
    <div class="srch-filter-content">
        <div class="srch-filter-header">
            <h3 style="margin:0;font-size:16px;font-weight:700;">Filter Location</h3>
            <button type="button" class="btn-icon" onclick="closeSrchFilterModal('location')">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="srch-filter-search">
            <input type="text" id="srchFilterLocationInput" class="form-control" placeholder="Cari lokasi..." oninput="srchFilterSearch('location', this.value)">
        </div>
        <div class="srch-filter-list" id="srchFilterLocationList"></div>
    </div>
</div>

<div class="table-container" style="border-top:0;">
    <table id="summaryTable" class="table-enterprise" style="width:100%;">
        <thead>
            <tr>
                <th>No</th>
                <th>QR Code</th>
                <th>Material Code</th>
                <th>Material</th>
                <th>Plant</th>
                <th>Location</th>
                <th>Shape</th>
                <th>Size</th>
                <th>Qty Total</th>
                <th>Total Scan Count</th>
            </tr>
        </thead>
    </table>
</div>

@push('scripts')
<script>
    let summaryTable;
    const _srchFilterCfg = {
        location: {
            selectId: 'filterLocation', triggerId: 'filterLocationTrigger',
            modalId: 'srchFilterLocationModal', inputId: 'srchFilterLocationInput',
            listId: 'srchFilterLocationList', placeholder: 'All',
        },
    };

    function _srchFilterBuildList(type) {
        const cfg = _srchFilterCfg[type];
        const select = document.getElementById(cfg.selectId);
        const list = document.getElementById(cfg.listId);
        list.innerHTML = '';
        Array.from(select.options).forEach(opt => {
            if (opt.disabled) return;
            const div = document.createElement('div');
            div.className = 'srch-filter-item' + (opt.selected ? ' active' : '');
            div.dataset.value = opt.value;
            div.dataset.label = opt.text;
            div.textContent = opt.value === '' ? ' All ' : opt.text;
            div.onclick = () => srchFilterSelect(type, opt.value, opt.value === '' ? cfg.placeholder : opt.text);
            list.appendChild(div);
        });
    }

    function openSrchFilterModal(type) {
        const cfg = _srchFilterCfg[type];
        _srchFilterBuildList(type);
        const modal = document.getElementById(cfg.modalId);
        modal.style.display = 'flex';
        const input = document.getElementById(cfg.inputId);
        input.value = '';
        srchFilterSearch(type, '');
        input.focus();
        setTimeout(() => {
            const active = modal.querySelector('.srch-filter-item.active');
            if (active) active.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }, 50);
    }

    function closeSrchFilterModal(type) {
        document.getElementById(_srchFilterCfg[type].modalId).style.display = 'none';
    }

    function srchFilterSelect(type, value, label) {
        const cfg = _srchFilterCfg[type];
        const select = document.getElementById(cfg.selectId);
        select.value = value;
        const trigger = document.getElementById(cfg.triggerId);
        trigger.textContent = label;
        trigger.classList.toggle('has-value', value !== '');
        closeSrchFilterModal(type);
    }

    function srchFilterSearch(type, query) {
        const lower = query.toLowerCase();
        const list = document.getElementById(_srchFilterCfg[type].listId);
        Array.from(list.children).forEach(child => {
            const label = (child.dataset.label || '').toLowerCase();
            child.style.display = label.includes(lower) ? '' : 'none';
        });
    }

    $(document).ready(function() {
        $('#filterPlant').on('change', function() {
            const plantId = $(this).val();
            const locationSelect = document.getElementById('filterLocation');
            srchFilterSelect('location', '', 'All');
            Array.from(locationSelect.options).forEach(opt => {
                if (opt.value === '') return;
                const optPlantId = opt.getAttribute('data-plant-id');
                opt.disabled = !!(plantId && optPlantId && optPlantId !== plantId);
            });
        });

        $(document).on('click', '.srch-filter-overlay', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });

        summaryTable = $('#summaryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("api.scan.material-summary") }}',
                data: d => {
                    d.plant_id = $('#filterPlant').val();
                    d.location_id = $('#filterLocation').val();
                    d.material_code = $('#filterMaterialCode').val();
                    d.material_name = $('#filterMaterialName').val();
                    d.shape_code = $('#filterShape').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },
            order: [],
            columns: [
                { data: 'no', orderable: false },
                { data: 'barcode_material', className: 'mono' },
                { data: 'material_code' },
                { data: 'material_name' },
                { data: 'plant' },
                { data: 'location' },
                { data: 'shape_name' },
                { data: 'size' },
                { data: 'qty_total' },
                { data: 'scan_count' }
            ],
            language: { emptyTable: 'Tidak ada data ditemukan.' }
        });
    });
</script>
@endpush

</x-layouts.app>
