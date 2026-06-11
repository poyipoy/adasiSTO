<x-layouts.app :title="'Scanner'">

{{-- Status Bar --}}
<div class="card" style="margin-bottom: 12px; padding: 10px 12px; background: var(--surface); border-bottom: 3px solid var(--primary);">
    <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;">
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <span class="badge badge-valid" style="animation: pulse-glow 2s ease-in-out infinite;">● LIVE</span>
            <span style="font-size: 12px;"><strong>STO:</strong> <span class="mono" style="color:var(--primary); font-weight:600;">{{ $stoSession->sto_code }}</span></span>
            <span style="font-size: 12px;"><strong>Plant:</strong> {{ $stoSession->plant->name }}</span>
            <span style="font-size: 12px;"><strong>PIC:</strong> {{ $stoSession->pic }}</span>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <div style="text-align:center;">
                <div class="mono" style="font-size:16px; font-weight:700; color:var(--primary);" id="counterToday">{{ $totalToday }}</div>
                <div style="font-size:9px; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Hari Ini</div>
            </div>
            <div style="text-align:center;">
                <div class="mono" style="font-size:16px; font-weight:700; color:var(--success);" id="counterSession">{{ $totalSession }}</div>
                <div style="font-size:9px; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Sesi Ini</div>
            </div>
            <div style="width:1px; height:24px; background:var(--border);"></div>
            <form action="{{ route('scan.end-session') }}" method="POST" style="margin:0;" onsubmit="return confirm('Akhiri sesi STO ini?');">
                @csrf
                <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size:11px;">Akhiri</button>
            </form>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;" id="mainGrid">
    {{-- Scanner Panel --}}
    <div class="card" style="min-height: 300px; padding: 12px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <h3 style="font-size: 13px; font-weight: 700; color: var(--text); text-transform:uppercase; letter-spacing:0.3px;">
                Barcode Scanner
            </h3>
            <button class="btn" onclick="toggleManualInput()" style="font-size: 11px; padding: 2px 8px;">
                ⌨ Manual
            </button>
        </div>

        {{-- Camera Scanner --}}
        <div id="scannerContainer" style="width: 100%; border: 1px solid var(--border); background: #fafbfc; min-height: 250px; position:relative; margin-bottom: 12px;">
            <div id="reader" style="width: 100%;"></div>
        </div>

        {{-- Manual Input --}}
        <div id="manualInput" style="display: none; margin-bottom: 12px; padding: 10px; background: var(--row-hover); border: 1px solid var(--border-light);">
            <label class="form-label">Input Manual Barcode</label>
            <div style="display: flex; gap: 8px;">
                <input type="text" id="manualBarcode" class="form-control mono" style="flex:1;" placeholder="Ketik barcode..." autocomplete="off">
                <button class="btn btn-primary" onclick="processManualBarcode()">Scan</button>
            </div>
        </div>

        {{-- Location & Qty --}}
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px; background: #fafbfc; padding: 10px; border: 1px solid var(--border);">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Lokasi / Rack</label>
                <select id="activeLocation" class="form-control">
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ $loc->id == $location->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Qty</label>
                <input type="number" id="qtyInput" class="form-control" value="1" min="1">
            </div>
        </div>
    </div>

    {{-- Recent Scans Panel --}}
    <div class="card" style="padding: 12px;">
        <h3 style="font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 12px; text-transform:uppercase; letter-spacing:0.3px;">
            Hasil Scan Terbaru
        </h3>

        <div id="scanList" style="max-height: 400px; overflow-y: auto; border: 1px solid var(--border); border-bottom: none;">
            @forelse($recentScans as $scan)
            <div class="scan-item" style="padding: 8px 10px; border-bottom: 1px solid var(--border); font-size: 12px; background: #fff;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="mono" style="font-weight: 600; color: var(--primary);">{{ $scan->barcode_material }}</span>
                    <span class="badge {{ $scan->keterangan === 'OK' ? 'badge-valid' : 'badge-invalid' }}">{{ $scan->keterangan }}</span>
                </div>
                <div style="color: var(--text-secondary); font-size: 10px; margin-top: 4px;">
                    {{ $scan->material_name }} · {{ $scan->shape_name }} · {{ $scan->size }} · {{ $scan->scan_time->format('H:i:s') }}
                </div>
            </div>
            @empty
            <div id="emptyState" style="text-align: center; padding: 32px; color: var(--text-muted); border-bottom: 1px solid var(--border);">
                <div style="font-size:24px; margin-bottom:8px; opacity:0.5;">📷</div>
                <p style="font-size: 11px;">Belum ada scan. Mulai scan barcode!</p>
            </div>
            @endforelse
        </div>

        <a href="{{ route('scan.results') }}" class="btn" style="width:100%;justify-content:center;margin-top:12px; border-style:dashed;">
            Lihat Semua History →
        </a>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let html5QrcodeScanner = null;
    let isProcessing = false;
    let lastScannedBarcode = '';
    let lastScanTime = 0;

    function startScanner() {
        html5QrcodeScanner = new Html5Qrcode("reader");

        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
        };

        html5QrcodeScanner.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanFailure
        ).catch(err => {
            console.warn("Camera not available:", err);
            document.getElementById('scannerContainer').innerHTML = `
                <div style="padding: 32px; text-align: center; color: var(--text-muted);">
                    <p style="margin-bottom: 8px;">📷 Kamera tidak tersedia</p>
                    <p style="font-size: 11px;">Gunakan mode Manual untuk input barcode</p>
                </div>
            `;
            document.getElementById('manualInput').style.display = 'block';
        });
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;

        const now = Date.now();
        if (decodedText === lastScannedBarcode && (now - lastScanTime) < 2000) return;

        isProcessing = true;
        lastScannedBarcode = decodedText;
        lastScanTime = now;

        if (navigator.vibrate) navigator.vibrate([100, 50, 100]);

        processScan(decodedText);

        setTimeout(() => { isProcessing = false; }, 1500);
    }

    function onScanFailure(error) {
    }

    function toggleManualInput() {
        const manual = document.getElementById('manualInput');
        manual.style.display = manual.style.display === 'none' ? 'block' : 'none';
        if (manual.style.display === 'block') {
            document.getElementById('manualBarcode').focus();
        }
    }

    function processManualBarcode() {
        const barcode = document.getElementById('manualBarcode').value.trim();
        if (!barcode) return;
        processScan(barcode);
        document.getElementById('manualBarcode').value = '';
        document.getElementById('manualBarcode').focus();
    }

    document.getElementById('manualBarcode')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            processManualBarcode();
        }
    });

    document.getElementById('activeLocation')?.addEventListener('change', function() {
        fetch('{{ route("api.change-location") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ location_id: this.value })
        });
    });

    function processScan(barcode) {
        const locationId = document.getElementById('activeLocation').value;
        const qty = parseInt(document.getElementById('qtyInput').value) || 1;

        fetch('{{ route("scan.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                barcode: barcode,
                location_id: locationId,
                qty: qty,
            })
        })
        .then(r => {
            if (!r.ok) return r.json().then(d => Promise.reject(d));
            return r.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message);
                addScanToList(data.data);
                updateCounters();
            } else {
                showToast(data.message || 'Scan gagal', 'error');
            }
        })
        .catch(err => {
            showToast(err.message || 'Terjadi kesalahan', 'error');
        });
    }

    function addScanToList(data) {
        const list = document.getElementById('scanList');
        const emptyState = document.getElementById('emptyState');
        if (emptyState) emptyState.remove();

        const badgeClass = data.keterangan === 'OK' ? 'badge-valid' : 'badge-invalid';

        const html = `
            <div class="scan-item" style="padding: 8px 10px; border-bottom: 1px solid var(--border); font-size: 12px; background: #fff; animation: fadeInUp 0.3s ease;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="mono" style="font-weight: 600; color: var(--primary);">${data.barcode}</span>
                    <span class="badge ${badgeClass}">${data.keterangan}</span>
                </div>
                <div style="color: var(--text-secondary); font-size: 10px; margin-top: 4px;">
                    ${data.material} · ${data.shape} · ${data.size} · ${data.scan_time}
                </div>
            </div>
        `;

        list.insertAdjacentHTML('afterbegin', html);

        const items = list.querySelectorAll('.scan-item');
        if (items.length > 10) items[items.length - 1].remove();
    }

    function updateCounters() {
        const today = document.getElementById('counterToday');
        const session = document.getElementById('counterSession');
        today.textContent = parseInt(today.textContent) + 1;
        session.textContent = parseInt(session.textContent) + 1;
    }

    document.addEventListener('DOMContentLoaded', startScanner);
</script>

<style>
    @keyframes pulse-glow {
        0%, 100% { opacity: 1; color: var(--success); }
        50% { opacity: 0.5; color: #a5d6a7; }
    }
    #reader { min-height: 250px; border: none !important; }
    #reader video { object-fit: cover; }
    #reader img[alt="Info icon"] { display: none; }
    #reader__scan_region { background: #000; }
    #reader__dashboard { padding: 8px !important; background: #fff; }
    #reader__dashboard_section_swaplink { color: var(--primary) !important; font-size: 11px; }
    #html5-qrcode-anchor-scan-type-change { color: var(--primary) !important; text-decoration: none !important; }
    
    #scanList::-webkit-scrollbar { width: 4px; }
    #scanList::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

    @media (max-width: 768px) {
        #mainGrid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

</x-layouts.app>
