<x-layouts.app :title="'Scanner'">

<div class="card" style="margin-bottom: 10px; border-left: 3px solid var(--primary);">
    <div class="info-bar-container" style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;">
        <div class="info-bar-items" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <span><strong>STO:</strong> <span class="mono">{{ $activeSto->code }}</span></span>
            <span><strong>PIC:</strong> {{ auth()->user()->name }}</span>
            <span><strong>Plant:</strong> {{ $plant->name }}</span>
            <span><strong>Location:</strong>
                <select id="locationId" style="border:none;background:transparent;font-family:inherit;font-size:inherit;color:var(--primary);font-weight:700;padding:0;cursor:pointer;outline:none;" onchange="updateLocationSession(this.value)">
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ $loc->id === $location->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </span>
        </div>
        <div><strong>Today:</strong> <span class="mono" id="counterToday">{{ $totalToday }}</span></div>
    </div>
</div>

<div class="card" style="margin-bottom:12px;">
    <div class="scan-header" style="display:flex;justify-content:space-between;gap:8px;align-items:center;margin-bottom:8px;">
        <div class="card-title" style="margin:0;">Scan QR / Barcode</div>
        <div style="display:flex;gap:6px;">
            <button class="btn" type="button" id="showCameraBtn" onclick="showCamera()">Show Camera</button>
            <button class="btn" type="button" id="hideCameraBtn" onclick="hideCamera()" style="display:none;">Hide Camera</button>
        </div>
    </div>

    <div id="readerWrap" style="display:none;margin-bottom:10px;">
        <div id="reader" style="min-height:220px;border:1px solid var(--border);background:#fafbfc;"></div>
    </div>

    <div class="form-group">
        <label class="form-label" for="qrInput">Manual / Scanner Gun Input</label>
        <div class="input-group-mobile" style="display:flex;gap:6px;">
            <input id="qrInput" class="form-control mono" placeholder="RF1H059-00960099B|ST2605|1" autocomplete="off">
            <button class="btn btn-primary" type="button" onclick="submitScan(false)">Save</button>
        </div>
    </div>

    <input type="hidden" id="plantId" value="{{ $plant->id }}">
</div>

<div class="card">
    <div class="card-title">Hasil Scan Terbaru</div>
    <div id="recentList">
        @forelse($recentScans as $scan)
            <div class="recent-row" id="scan-row-{{ $scan->id }}" style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--border-light);">
                <div style="flex:1;min-width:0;">
                    <div class="mono" style="font-weight:700;color:var(--primary);">{{ $scan->barcode_material }}</div>
                    <div style="font-size:11px;color:var(--text-secondary);">{{ $scan->recent_detail }}</div>
                </div>
                <div class="recent-row-actions" style="display:flex;align-items:center;gap:8px;">
                    <span class="badge {{ $scan->keterangan === 'OK' ? 'badge-valid' : 'badge-invalid' }}">{{ $scan->keterangan }}</span>
                    <button class="btn-icon" style="color:var(--danger);padding:0 4px;" type="button" onclick="confirmDeleteScan({{ $scan->id }})" title="Hapus">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                    </button>
                </div>
            </div>
        @empty
            <div id="emptyRecent" style="padding:16px;color:var(--text-muted);text-align:center;">Belum ada hasil scan.</div>
        @endforelse
    </div>
</div>

<div class="modal-overlay" id="duplicateModal">
    <div class="modal-content">
        <div class="modal-header"><strong>Warning</strong></div>
        <div class="modal-body">
            Barcode sudah pernah discan sebelumnya. Tetap simpan?
        </div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeDuplicateModal()">Batal</button>
            <button class="btn btn-primary" type="button" onclick="confirmDuplicate()">Ya</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header"><strong style="color:var(--danger);">Hapus Scan</strong></div>
        <div class="modal-body">Yakin ingin menghapus hasil scan ini?</div>
        <div class="modal-footer">
            <button class="btn" type="button" onclick="closeDeleteModal()">Batal</button>
            <button class="btn btn-danger" type="button" id="confirmDeleteBtn">Hapus</button>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media (max-width: 768px) {
        .info-bar-container { flex-direction: column !important; align-items: stretch !important; gap: 8px !important; }
        .info-bar-items { flex-direction: column !important; align-items: flex-start !important; gap: 4px !important; }
        .scan-header { flex-direction: column; align-items: stretch !important; gap: 8px; }
        .scan-header .btn { width: 100%; justify-content: center; }
        .input-group-mobile { flex-direction: column; }
        .input-group-mobile .btn { width: 100%; margin-top: 6px; }
        .recent-row { flex-wrap: wrap; }
        .recent-row-actions { width: 100%; justify-content: space-between; margin-top: 6px; }
        .recent-row .mono { word-break: break-all; }
        #reader { min-height: 280px !important; }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let pendingQr = '';
    let pendingSource = 'manual';
    let scanningLocked = false;
    let html5Scanner = null;
    let cameraRunning = false;

    function updateLocationSession(newLocationId) {
        const plantId = document.getElementById('plantId').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        fetch('{{ route("scan.setup.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                plant_id: plantId,
                location_id: newLocationId
            })
        }).catch(err => console.error('Failed to update session location', err));
    }

    function submitScan(forceSave = false, qrText = null, source = 'manual') {
        const qrInput = document.getElementById('qrInput');
        const qr = (qrText || qrInput.value).trim();
        if (!qr) return;

        pendingQr = qr;
        pendingSource = source;

        fetch('{{ route("api.scan.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({
                qr,
                plant_id: document.getElementById('plantId').value,
                location_id: document.getElementById('locationId').value,
                scan_source: source,
                force_save: forceSave
            })
        })
        .then(async response => {
            const payload = await response.json();
            if (!response.ok) throw payload;
            return payload;
        })
        .then(payload => {
            if (document.getElementById('duplicateModal').classList.contains('active')) {
                closeDuplicateModal();
            }
            addRecent(payload.data);
            document.getElementById('counterToday').textContent = String(parseInt(document.getElementById('counterToday').textContent || '0') + 1);
            qrInput.value = '';
            qrInput.focus();
            showToast(payload.message);
        })
        .catch(error => {
            if (error.duplicate) {
                scanningLocked = true;
                document.getElementById('duplicateModal').classList.add('active');
                return;
            }
            showToast(error.message || 'Scan gagal.', 'error');
        });
    }

    function confirmDuplicate() {
        submitScan(true, pendingQr, pendingSource);
    }

    function addRecent(data) {
        document.getElementById('emptyRecent')?.remove();
        const html = `
            <div class="recent-row" id="scan-row-${data.id}" style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--border-light);">
                <div style="flex:1;min-width:0;">
                    <div class="mono" style="font-weight:700;color:var(--primary);">${data.barcode_material}</div>
                    <div style="font-size:11px;color:var(--text-secondary);">${data.recent_detail}</div>
                </div>
                <div class="recent-row-actions" style="display:flex;align-items:center;gap:8px;">
                    <span class="badge ${data.keterangan === 'OK' ? 'badge-valid' : 'badge-invalid'}">${data.keterangan}</span>
                    <button class="btn-icon" style="color:var(--danger);padding:0 4px;" type="button" onclick="confirmDeleteScan(${data.id})" title="Hapus">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                    </button>
                </div>
            </div>
        `;
        document.getElementById('recentList').insertAdjacentHTML('afterbegin', html);
    }

    function closeDuplicateModal() {
        document.getElementById('duplicateModal').classList.remove('active');
        scanningLocked = false;
    }

    let scanToDelete = null;

    function confirmDeleteScan(id) {
        scanToDelete = id;
        document.getElementById('deleteModal').classList.add('active');
        document.getElementById('confirmDeleteBtn').onclick = () => performDelete();
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
        scanToDelete = null;
        document.getElementById('qrInput').focus();
    }

    function performDelete() {
        if (!scanToDelete) return;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const idToDel = scanToDelete;
        closeDeleteModal();
        
        fetch(`/api/scan/${idToDel}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw data;
            return data;
        })
        .then(data => {
            showToast(data.message);
            const row = document.getElementById(`scan-row-${idToDel}`);
            if (row) row.remove();
            
            const counter = document.getElementById('counterToday');
            if (counter && parseInt(counter.textContent) > 0) {
                counter.textContent = String(parseInt(counter.textContent) - 1);
            }
        })
        .catch(error => {
            showToast(error.message || 'Gagal menghapus data', 'error');
        });
    }

    function showCamera() {
        if (!window.Html5Qrcode) {
            showToast('Library kamera belum tersedia.', 'error');
            return;
        }

        document.getElementById('readerWrap').style.display = 'block';
        document.getElementById('showCameraBtn').style.display = 'none';
        document.getElementById('hideCameraBtn').style.display = 'inline-flex';

        if (!html5Scanner) {
            html5Scanner = new Html5Qrcode('reader');
        }

        if (cameraRunning) return;

        html5Scanner.start(
            { facingMode: 'environment' },
            { fps: 8, qrbox: { width: 220, height: 220 } },
            decodedText => {
                if (scanningLocked || document.getElementById('duplicateModal').classList.contains('active')) return;
                scanningLocked = true;
                document.getElementById('qrInput').value = decodedText;
                submitScan(false, decodedText, 'camera');
                setTimeout(() => scanningLocked = false, 1500);
            },
            () => {}
        ).then(() => {
            cameraRunning = true;
        }).catch(() => {
            document.getElementById('reader').innerHTML = '<div style="padding:32px;text-align:center;color:var(--text-muted);">Kamera tidak tersedia. Gunakan input manual atau scanner gun.</div>';
        });
    }

    function hideCamera() {
        document.getElementById('readerWrap').style.display = 'none';
        document.getElementById('showCameraBtn').style.display = 'inline-flex';
        document.getElementById('hideCameraBtn').style.display = 'none';

        if (!html5Scanner || !cameraRunning) return;

        html5Scanner.stop().then(() => {
            cameraRunning = false;
        }).catch(() => {
            cameraRunning = false;
        });
    }

    document.getElementById('qrInput').addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            submitScan(false);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('qrInput').focus();
    });
</script>
@endpush

</x-layouts.app>
