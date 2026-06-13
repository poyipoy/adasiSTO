# Mobile-Friendly Optimization untuk Scanner Role

Scanner role akan digunakan oleh user di **smartphone** dan **gun scanner**. Berikut analisis masalah dan rencana perbaikan.

---

## Hasil Analisis

### ✅ Yang Sudah Baik
- `<meta name="viewport">` sudah ada di semua halaman
- Login page sudah responsif (flexbox center, max-width 420px)
- Sidebar sudah auto-hide di `max-width: 768px` dengan hamburger menu
- Setup page sudah punya `max-width: 560px` dan centered
- Semua form input sudah full-width

### ❌ Masalah yang Ditemukan

#### 1. Layout Global ([app.blade.php](file:///c:/laragon/www/adasi_sto%20-%20Copy/resources/views/components/layouts/app.blade.php))

| Masalah | Detail |
|---------|--------|
| **Touch target terlalu kecil** | Tombol dan link di topbar/sidebar hanya `padding: 5-7px`, terlalu kecil untuk jari (minimal 44px) |
| **Font terlalu kecil** | `body: 13px` terlalu kecil untuk mobile, badge `10px`, form-label `11px` |
| **Topbar terlalu pendek** | `42px` cukup untuk desktop, tapi sulit di-tap di mobile |
| **Toast position** | `bottom: 16px; right: 16px` — di mobile, bisa tertutup oleh keyboard |
| **Page-tab-bar** di-hide pada mobile (`display: none`), ini OK |

#### 2. Scanner Page ([scanner.blade.php](file:///c:/laragon/www/adasi_sto%20-%20Copy/resources/views/scan/scanner.blade.php))

| Masalah | Detail |
|---------|--------|
| **Info bar terlalu padat** | STO, PIC, Plant, Location, Today semua dalam 1 baris flex-wrap — di layar 360px akan berantakan |
| **QR Input + Save button sebaris** | Di layar kecil, tombol "Save" bisa terlalu sempit |
| **Barcode text overflow** | `mono font-weight:700` barcode panjang akan overflow di layar kecil |
| **Tombol kamera kecil** | Button "Show Camera" standar `.btn` padding 4px 10px — sulit di-tap |
| **Delete icon kecil** | SVG icon 16x16 dengan padding 0-4px — sangat sulit di-tap di mobile |
| **Modal padding** | Modal `padding-top: 80px` — di landscape mobile bisa terlalu banyak |

#### 3. Setup Page ([setup.blade.php](file:///c:/laragon/www/adasi_sto%20-%20Copy/resources/views/scan/setup.blade.php))

| Masalah | Detail |
|---------|--------|
| **Relatif sudah oke** | Centered, max-width 560px, select dan input full-width |
| **Tombol "+ Baru" kecil** | `min-width: 64px` dengan padding `.btn` standar — bisa sulit di-tap |
| **Start Scan button height** | `38px` — sedikit kurang ideal, rekomendasi 48px untuk mobile |

#### 4. History Page ([results.blade.php](file:///c:/laragon/www/adasi_sto%20-%20Copy/resources/views/scan/results.blade.php))

| Masalah | Detail |
|---------|--------|
| **Tabel horizontal 10 kolom** | Pada layar 360px, tabel akan sangat sempit, perlu scroll horizontal |
| **Filter bar overflow** | 3 input + 1 tombol dalam flex-wrap, di mobile bisa berantakan |
| **Delete button hanya teks** | "Delete" tanpa icon, sulit di-tap |

---

## Proposed Changes

Perubahan utama menggunakan `@media (max-width: 768px)` agar hanya berlaku di mobile tanpa merusak tampilan desktop.

### Komponen 1: Layout Global

#### [MODIFY] [app.blade.php](file:///c:/laragon/www/adasi_sto%20-%20Copy/resources/views/components/layouts/app.blade.php)

Tambahkan CSS responsive di dalam blok `@media (max-width: 768px)` yang sudah ada:

- **Topbar**: Tinggi jadi `50px`, padding lebih besar
- **Sidebar nav-item**: Padding jadi `12px 14px`, font `14px` (touch-friendly)
- **Body font**: `14px` untuk mobile
- **Button (.btn)**: Padding `8px 14px`, `font-size: 13px`, `min-height: 40px`
- **Form controls**: Padding `10px 12px`, `font-size: 14px`, `min-height: 40px`
- **Badge**: `font-size: 11px`, `padding: 2px 8px`
- **Toast**: `left: 16px; right: 16px` (full-width di bawah)
- **Modal**: `padding-top: 40px`, modal-content `margin: 0 12px`
- **Card padding**: `padding: 14px`
- **Page content**: `padding: 8px`

---

### Komponen 2: Scanner Page

#### [MODIFY] [scanner.blade.php](file:///c:/laragon/www/adasi_sto%20-%20Copy/resources/views/scan/scanner.blade.php)

Tambahkan `@push('styles')` dengan media query mobile:

- **Info bar**: Layout vertikal (flex-direction: column, gap 4px), item tersusun ke bawah
- **Scan QR header**: Stack tombol kamera di bawah judul
- **QR Input**: Full-width input, tombol Save full-width di bawah (flex-direction: column)
- **Recent scan rows**: Barcode text `word-break: break-all`, detail text wrapping
- **Delete icon**: Area tap diperbesar `min-width: 40px; min-height: 40px`
- **Camera reader**: `min-height: 280px`, qrbox lebih besar

---

### Komponen 3: Setup Page

#### [MODIFY] [setup.blade.php](file:///c:/laragon/www/adasi_sto%20-%20Copy/resources/views/scan/setup.blade.php)

Tambahkan `@push('styles')` dengan media query:

- **Container**: `max-width: 100%`, `padding: 0`
- **Start Scan button**: `height: 48px`, `font-size: 14px`
- **"+ Baru" button**: `min-width: 72px`, `min-height: 40px`
- **Select/input**: Lebih tinggi dan mudah di-tap

---

### Komponen 4: History Page

#### [MODIFY] [results.blade.php](file:///c:/laragon/www/adasi_sto%20-%20Copy/resources/views/scan/results.blade.php)

Transformasi tabel menjadi **card-based layout** di mobile:

- **Filter bar**: Stack vertikal, setiap input full-width
- **Tabel → Card**: Sembunyikan `<table>` di mobile, tampilkan `<div>` card per scan row
- **Setiap card**: Barcode besar di atas, detail di bawah dalam 2 kolom, badge + delete di kanan
- **Delete button**: Icon lebih besar dengan `min-height: 40px`

---

## Open Questions

> [!IMPORTANT]
> **Tabel History di mobile**: Apakah Anda lebih memilih (A) tabel tetap ada dengan horizontal scroll, atau (B) diganti jadi tampilan card/list? Opsi B lebih mobile-friendly tapi tampilannya berbeda dari desktop.

> [!NOTE]
> Semua perubahan ini hanya berdampak pada layar ≤ 768px. Tampilan desktop **tidak akan berubah sama sekali**.

---

## Verification Plan

### Manual Verification
- Buka halaman login, setup, scanner, history via Chrome DevTools → Toggle Device (iPhone SE / Samsung Galaxy S8)
- Test tap semua tombol apakah mudah disentuh
- Test scan barcode (manual input) di mobile
- Test modal duplicate dan delete di landscape/portrait
- Pastikan tampilan desktop tidak terpengaruh
