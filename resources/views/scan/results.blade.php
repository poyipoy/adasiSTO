<x-layouts.app :title="'Hasil Scan Saya'">

{{-- Stats --}}
<div class="stat-grid">
    <div class="stat-card animate-in animate-delay-1">
        <div class="stat-icon indigo">📊</div>
        <div class="stat-value" data-count="{{ $totalToday }}">0</div>
        <div class="stat-label">Scan Hari Ini</div>
    </div>
    <div class="stat-card animate-in animate-delay-2">
        <div class="stat-icon green">📋</div>
        <div class="stat-value" data-count="{{ $totalSession }}">0</div>
        <div class="stat-label">Scan STO Aktif</div>
    </div>
    <div class="stat-card animate-in animate-delay-3">
        <div class="stat-icon amber">🏭</div>
        <div class="stat-value" style="-webkit-text-fill-color: #fbbf24;">{{ $plantName }}</div>
        <div class="stat-label">Plant Aktif</div>
    </div>
    <div class="stat-card animate-in animate-delay-4">
        <div class="stat-icon blue">📍</div>
        <div class="stat-value" data-count="{{ $locationCount }}">0</div>
        <div class="stat-label">Lokasi Aktif</div>
    </div>
</div>

{{-- Data Table --}}
<div class="glass-card animate-in" style="animation-delay: 0.5s; opacity: 0;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <h3 style="font-size: 1rem; font-weight: 700;">Riwayat Scan</h3>
        <a href="{{ route('scan.index') }}" class="btn btn-primary btn-sm">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
            Scan Lagi
        </a>
    </div>

    <div class="table-responsive">
        <table id="scanTable" class="display" style="width: 100%;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Barcode</th>
                    <th>Material</th>
                    <th>Shape</th>
                    <th>Size</th>
                    <th>Lot</th>
                    <th>Jam</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $('#scanTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("scan.datatable") }}',
            order: [],
            columns: [
                { data: 'no', orderable: false, className: 'mono', width: '50px' },
                { data: 'barcode', className: 'mono', render: d => `<span style="color:#818cf8;font-weight:600;">${d}</span>` },
                { data: 'material' },
                { data: 'shape' },
                { data: 'size' },
                { data: 'lot' },
                { data: 'scan_time', className: 'mono' },
                {
                    data: 'keterangan',
                    render: function(data, type, row) {
                        const options = @json($keteranganList);
                        let select = `<select class="form-control" style="padding:0.3rem;font-size:0.75rem;border-radius:6px;min-width:120px;" onchange="updateKeterangan(${row.id}, this.value)">`;
                        options.forEach(opt => {
                            select += `<option value="${opt}" ${opt === data ? 'selected' : ''}>${opt}</option>`;
                        });
                        select += '</select>';
                        return select;
                    }
                },
            ],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                paginate: { previous: '‹', next: '›' },
                emptyTable: 'Belum ada data scan',
                processing: '<span style="color:var(--accent-primary);">Memuat...</span>',
            },
            pageLength: 25,
        });
    });

    function updateKeterangan(id, value) {
        fetch(`/scan/${id}/keterangan`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ keterangan: value })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) showToast('Keterangan berhasil diupdate!');
            else showToast('Gagal update keterangan', 'error');
        })
        .catch(() => showToast('Error koneksi', 'error'));
    }
</script>
@endpush

</x-layouts.app>
