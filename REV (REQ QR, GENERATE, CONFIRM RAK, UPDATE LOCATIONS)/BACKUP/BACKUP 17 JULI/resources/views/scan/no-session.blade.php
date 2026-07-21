<x-layouts.app :title="'Scanner'">
<div style="max-width: 520px; margin: 48px auto; text-align: center;">
    <div class="card" style="border-left: 3px solid var(--warning);">
        <div class="card-title">Scanner Belum Siap</div>
        <p style="color: var(--text-secondary); margin-bottom: 14px;">
            {{ $message ?? 'Silakan setup STO terlebih dahulu sebelum memulai scan.' }}
        </p>
        <a href="{{ route('scan.setup') }}" class="btn btn-primary">Setup STO</a>
    </div>
</div>
</x-layouts.app>
