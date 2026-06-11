<x-layouts.app :title="'Scanner'">

<div style="max-width: 500px; margin: 4rem auto; text-align: center;">
    <div class="glass-card">
        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width:64px;height:64px;margin:0 auto 1rem;display:block;color:var(--text-muted);">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">Belum Ada Sesi Aktif</h2>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">
            Silakan setup sesi STO terlebih dahulu sebelum memulai scan.
        </p>
        <a href="{{ route('scan.setup') }}" class="btn btn-primary" style="justify-content: center;">
            Setup STO →
        </a>
    </div>
</div>

</x-layouts.app>
