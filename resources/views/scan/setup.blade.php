<x-layouts.app :title="'Setup STO'">

<div style="max-width: 480px; margin: 0 auto; padding: 12px 0;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
            <svg fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24" style="width:22px;height:22px;"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Setup Sesi Scan
        </h2>
        <p style="color: var(--text-muted); font-size: 12px;">Lengkapi informasi berikut sebelum memulai scan.</p>
    </div>

    @if($activeSession)
    <div class="card" style="margin-bottom: 16px; border-left: 3px solid var(--success);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; border-bottom: 1px solid var(--border-light); padding-bottom: 8px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display:block;width:8px;height:8px;background:var(--success);border-radius:50%;"></span>
                <span style="font-size: 12px; font-weight: 700; color: var(--text); text-transform:uppercase;">Sesi Aktif</span>
            </div>
            <form action="{{ route('scan.end-session') }}" method="POST" onsubmit="return confirm('Yakin ingin mengakhiri sesi ini?');">
                @csrf
                <button type="submit" class="btn btn-danger" style="padding: 2px 8px; font-size: 11px;">Akhiri Sesi</button>
            </form>
        </div>
        <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.6;">
            <div style="display:flex; justify-content:space-between;">
                <span>STO Code</span>
                <strong class="mono" style="color:var(--primary);">{{ $activeSession->sto_code }}</strong>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span>Plant</span>
                <strong>{{ $activeSession->plant->name }}</strong>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span>PIC</span>
                <strong>{{ $activeSession->pic }}</strong>
            </div>
        </div>
        <a href="{{ route('scan.index') }}" class="btn btn-primary" style="margin-top: 12px; width: 100%; justify-content: center; height: 36px;">
            Lanjutkan Scan →
        </a>
    </div>
    @endif

    <form action="{{ route('scan.store-setup') }}" method="POST" id="setupForm">
        @csrf

        <div class="card" style="margin-bottom: 16px;">
            <div class="card-title">Sesi STO Baru</div>

            <div class="form-group" style="margin-top: 12px;">
                <label class="form-label">PIC (Person In Charge)</label>
                <div class="form-control" style="background: #f4f5f7; height: 36px; display:flex; align-items:center;">
                    {{ auth()->user()->name }} ({{ auth()->user()->role }})
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="plant_id">Plant</label>
                <select id="plant_id" name="plant_id" class="form-control" required style="height: 36px;">
                    <option value="">-- Pilih Plant --</option>
                    @foreach($plants as $plant)
                    <option value="{{ $plant->id }}" {{ old('plant_id') == $plant->id ? 'selected' : '' }}>
                        {{ $plant->name }}
                    </option>
                    @endforeach
                </select>
                @error('plant_id')<p style="color:var(--danger);font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Kode STO</label>
                <div class="form-control mono" style="background: #f4f5f7; color: var(--primary); font-weight: 600; cursor: default; height: 36px; display:flex; align-items:center;">
                    STO{{ now()->format('dm') }}
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="location_id">Lokasi / Rack</label>
                <div style="display: flex; gap: 8px;">
                    <select id="location_id" name="location_id" class="form-control" required style="flex:1; height: 36px;">
                        <option value="">-- Pilih Plant terlebih dahulu --</option>
                    </select>
                    <button type="button" class="btn" onclick="openAddLocation()" title="Tambah Lokasi Baru" style="height: 36px; white-space:nowrap;">
                        + Baru
                    </button>
                </div>
                @error('location_id')<p style="color:var(--danger);font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; height: 42px; font-size: 13px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700;" id="startScanBtn">
            Mulai Scan
        </button>
    </form>
</div>

{{-- Add Location Modal --}}
<div class="modal-overlay" id="addLocationModal">
    <div class="modal-content" style="border-radius:0;">
        <div class="modal-header">
            <span>Tambah Lokasi Baru</span>
            <button class="btn-icon" onclick="closeAddLocation()" style="border:none;background:none;font-size:16px;">×</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Nama Lokasi / Rack</label>
                <input type="text" id="newLocationName" class="form-control" placeholder="Contoh: RACK-A1" style="height: 36px; text-transform:uppercase;">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" onclick="saveNewLocation()" style="width:100%;justify-content:center;height:36px;">Simpan</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const plantSelect = document.getElementById('plant_id');
    const locationSelect = document.getElementById('location_id');

    plantSelect.addEventListener('change', function() {
        const plantId = this.value;
        locationSelect.innerHTML = '<option value="">Memuat...</option>';

        if (!plantId) {
            locationSelect.innerHTML = '<option value="">-- Pilih Plant --</option>';
            return;
        }

        fetch(`/api/locations/${plantId}`)
            .then(r => r.json())
            .then(locations => {
                locationSelect.innerHTML = '<option value="">-- Pilih Lokasi --</option>';
                locations.forEach(loc => {
                    locationSelect.innerHTML += `<option value="${loc.id}">${loc.name}</option>`;
                });
                if (locations.length === 0) {
                    locationSelect.innerHTML = '<option value="">(Belum ada, klik "Baru")</option>';
                }
            })
            .catch(() => {
                locationSelect.innerHTML = '<option value="">Error memuat lokasi</option>';
            });
    });

    if (plantSelect.value) {
        plantSelect.dispatchEvent(new Event('change'));
    }

    function openAddLocation() {
        if (!plantSelect.value) {
            showToast('Pilih Plant terlebih dahulu!', 'error');
            return;
        }
        document.getElementById('addLocationModal').classList.add('active');
        document.getElementById('newLocationName').focus();
    }

    function closeAddLocation() {
        document.getElementById('addLocationModal').classList.remove('active');
    }

    function saveNewLocation() {
        const name = document.getElementById('newLocationName').value.trim().toUpperCase();
        if (!name) {
            showToast('Nama lokasi wajib diisi', 'error');
            return;
        }

        fetch('/api/locations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                plant_id: plantSelect.value,
                name: name
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const option = new Option(data.data.name, data.data.id, true, true);
                locationSelect.appendChild(option);
                closeAddLocation();
                document.getElementById('newLocationName').value = '';
                showToast('Lokasi berhasil ditambah');
            } else {
                showToast(data.message || 'Gagal', 'error');
            }
        })
        .catch(() => showToast('Terjadi kesalahan', 'error'));
    }

    document.getElementById('newLocationName').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); saveNewLocation(); }
    });
</script>
@endpush

</x-layouts.app>
