# DESIGN.md - STO Scan To Office

> Status: canonical UI/UX guideline
> Bahasa antarmuka: Indonesia
> Divalidasi terhadap implementasi aktif: 24 Juli 2026

## 1. Purpose and Document Authority

Dokumen ini adalah referensi utama untuk desain visual, layout, pola interaksi, responsive behavior, dan accessibility STO. Audiensnya adalah product owner, reviewer, designer, developer, dan AI agent.

DESIGN.md mengatur application shell, navigasi, design token, komponen, penyajian data, responsive behavior, dan pola halaman admin/scanner. Dokumen ini tidak menetapkan business rule, authorization backend, database, barcode parsing, atau kontrak API.

### 1.1 Prioritas aturan

Jika terjadi konflik:

1. Security, authorization, dan business rule pada `AGENTS.md` serta backend.
2. Aturan UI/UX canonical pada `DESIGN.md`.
3. Layout aktif `resources/views/components/layouts/app.blade.php`.
4. Implementasi halaman existing.
5. Preferensi developer.

Implementasi existing yang bertentangan dicatat sebagai legacy deviation, bukan preseden. Kata **wajib** berarti harus dipenuhi; **disarankan** adalah default; **legacy** berarti tidak boleh disalin.

## 2. Product and Audience

STO adalah aplikasi operasional stock opname material melalui QR/barcode.

| Audience | Perangkat utama | Prioritas UX |
|---|---|---|
| Admin | Desktop/laptop | Monitoring padat, filter, tabel besar, validasi, export |
| Scanner | Ponsel/scanner gun | Scan cepat, touch target besar, feedback langsung |
| Scanner validator | Ponsel/desktop | Kebutuhan scanner ditambah Material Double |

Desain harus mendukung koneksi tidak selalu stabil, penggunaan satu tangan, input scanner gun yang cepat, data besar, dan perpindahan halaman berulang.

## 3. Design Philosophy

Arah visual mengikuti enterprise ERP bergaya Infor: compact, data-oriented, professional, predictable, dan cepat dipindai.

### 3.1 Prinsip

1. Data dan action utama terlihat lebih dulu.
2. Informasi terbaru berada di atas.
3. Satu area memiliki satu primary action dominan.
4. Admin menggunakan kepadatan data efisien.
5. Scanner mobile-first dengan minimum perpindahan fokus.
6. Request asynchronous memiliki loading, success, error, dan empty state.
7. Status tidak disampaikan hanya melalui warna.
8. Layout stabil saat data, badge, loading, atau label berubah.

Hindari landing page, decorative card-heavy layout, nested cards, gradient dekoratif, heading hero, pill untuk semua kontrol, whitespace berlebihan, dan animasi yang memperlambat pekerjaan.

## 4. Design Foundations

### 4.1 Color system

Gunakan CSS variables layout canonical. Jangan membuat warna status lokal jika token yang sesuai tersedia.

| Token | Nilai | Penggunaan |
|---|---:|---|
| `--topbar-bg` | `#1d252c` | Topbar |
| `--sidebar-bg` | `#161719` | Sidebar |
| `--sidebar-hover` | `#2a2b2f` | Hover sidebar |
| `--sidebar-active` | `#3d3d42` | Active/floating sidebar |
| `--sidebar-border` | `#2f3035` | Pemisah sidebar |
| `--sidebar-text` | `#e2e8f0` | Label sidebar |
| `--sidebar-icon` | `#cdd5e0` | Icon sidebar |
| `--workspace-bg` | `#e1e6eb` | Workspace |
| `--surface` | `#ffffff` | Card, table, modal |
| `--border` | `#cdd5e0` | Border utama |
| `--border-light` | `#e2e8f0` | Divider |
| `--row-hover` | `#f0f0f0` | Hover row |
| `--row-selected` | `#d3e3fd` | Selected row |
| `--primary` | `#0066d4` | Primary action |
| `--primary-dark` | `#0054b3` | Primary hover |
| `--text` | `#1a1a1a` | Teks utama |
| `--text-secondary` | `#5c5c5c` | Supporting text |
| `--text-muted` | `#808b99` | Metadata |
| `--success` | `#197f4c` | Success/valid |
| `--warning` | `#f28b00` | Warning/pending |
| `--danger` | `#b92525` | Error/delete |
| `--table-header-bg` | `#f5f6f8` | Header tabel |
| `--table-header-text` | `#5c5c5c` | Label header |

Foreground/background wajib memenuhi contrast ratio WCAG AA.

### 4.2 Typography

Font utama adalah Inter self-hosted, dengan fallback `"Segoe UI", Arial, sans-serif`.

Letter spacing adalah `0`; ukuran font tidak boleh berubah berdasarkan lebar viewport.

| Elemen | Ukuran panduan | Aturan |
|---|---:|---|
| Page title | `16-20px` | Ringkas, satu level dominan per halaman |
| Section/card title | `12-14px` | Semibold, bukan hero text |
| Body/table data | `13-14px` | Nyaman dipindai pada data padat |
| Table header | `12px` | Semibold, label singkat |
| Form label | `11-12px` | Selalu terlihat, tidak hanya placeholder |
| Button | `12-13px` | Semibold untuk primary action |
| Metadata | `10-12px` | Tetap memiliki kontras memadai |
| KPI value | `22-26px` | Hanya untuk metrik utama |

Barcode, lot, timestamp, dan identifier teknis memakai font monospace. Angka pada tabel memakai tabular numerals bila tersedia. Teks harus membungkus secara wajar; jangan menurunkan ukuran font dinamis hanya agar muat.

### 4.3 Spacing, border, dan elevation

Gunakan scale `4, 6, 8, 12, 16, 24px`. Jarak internal kontrol umumnya `6-10px`; jarak antarkelompok `12-16px`; jarak section utama maksimal `24px`.

- Border kontrol dan tabel: `1px solid var(--border)`.
- Radius canonical kontrol operasional: `2px`.
- Radius modal, KPI, dan panel framed: maksimal `8px`.
- Avatar dan indikator bulat boleh memakai `50%`.
- Shadow hanya untuk modal, floating submenu, dropdown, dan overlay yang memerlukan pemisahan layer.
- Radius `12-16px` pada halaman existing adalah legacy deviation dan tidak boleh disalin.

### 4.4 Icons, focus, dan motion

Gunakan Boxicons atau inline SVG dari sistem icon existing. Ukuran default `16-18px` di desktop dan `18-20px` di mobile. Icon button wajib memiliki tooltip; label teks tetap dipakai untuk command penting seperti Save, Delete Selected, dan Export.

Semua kontrol interaktif wajib memiliki focus ring terlihat, misalnya outline primary `2px` dengan offset `2px`. Jangan menghapus outline tanpa pengganti.

Transisi sidebar, tab, hover, dan modal berada pada `120-300ms`. Animasi hanya membantu orientasi atau menunjukkan proses. Hormati `prefers-reduced-motion: reduce` dengan menghapus gerakan non-esensial.

## 5. Application Shell

Layout canonical berada di `resources/views/components/layouts/app.blade.php` dan dipanggil melalui `<x-layouts.app>`. File `resources/views/layouts/app.blade.php` adalah legacy dan tidak menjadi referensi halaman baru.

| Area | Desktop | Mobile |
|---|---:|---:|
| Topbar | `44px` | `50px` |
| Workspace headbar | `40px` | `42px` |
| Sidebar expanded | `280px` | Overlay, `50vw`, minimum `200px` |
| Sidebar collapsed | `60px` | Tertutup |
| Breakpoint utama | `768px` | `<= 768px` |

### 5.1 Topbar

Topbar adalah layer paling atas dan memuat identitas STO, Current STO, identitas user, dan logout. Hamburger tidak berada di topbar; tombol tersebut berada di workspace headbar. Informasi harus tetap terbaca tanpa menggeser action user keluar viewport.

### 5.2 Workspace headbar dan tabs

Workspace headbar berada tepat di bawah topbar dan memuat hamburger, tab Recent, serta workspace tabs.

- Recent bersifat fixed, gelap, tidak dapat ditutup, dan memiliki icon recent.
- Workspace tab berwarna putih, memiliki active underline primary, label ellipsis, dan tombol close di ujung kanan.
- Tombol close tersembunyi sampai tab di-hover atau menerima keyboard focus. Pada perangkat touch tombol harus tetap dapat diakses.
- Tab menyusut proporsional saat jumlahnya bertambah, lalu horizontal scroll setelah mencapai minimum width.
- Membuka menu sidebar mengaktifkan atau membuat tab terkait; berpindah tab wajib menyinkronkan active item dan accordion sidebar.
- Close tab mengaktifkan tab tetangga yang paling relevan tanpa memuat ulang tab lain.

Konten workspace dapat dimuat dalam iframe. Halaman wajib mendukung `html.is-iframe`: application shell internal disembunyikan, padding disesuaikan, dan tidak boleh muncul scrollbar ganda.

### 5.3 Sidebar

Sidebar menggunakan dark Infor-style, compact row, icon kiri, label putih, dan chevron kanan.

- Parent adalah accordion dan tidak membuka halaman secara langsung bila memiliki submenu.
- Submenu mengikuti role/permission backend; jangan hanya menyembunyikan item lewat CSS.
- Active item memakai background `--sidebar-active` dan garis primary.
- Hover parent dan submenu memberi underline tipis sepanjang label, bukan sepanjang row.
- Expanded/collapsed state mempertahankan group yang sebelumnya terbuka.
- Saat collapsed, hanya icon parent yang terlihat; submenu tidak ditampilkan sebagai icon tambahan.
- Klik parent saat collapsed membuka floating submenu di sebelah sidebar.
- Counter memakai badge compact dan tidak boleh mengubah lebar row.

### 5.4 Mobile shell

Pada mobile, sidebar adalah overlay di bawah dua headbar dan tidak boleh menutup topbar. Workspace tabs tetap horizontal-scroll. Area konten memakai satu kolom, padding lebih rapat, dan tidak boleh memiliki fixed width yang memicu overflow halaman.

## 6. Navigation by Role

Menu adalah representasi permission, bukan pengganti authorization backend. Urutan berikut adalah canonical.

### 6.1 Admin

```text
Dashboard
??? Overview

STO Result
??? All Scan Results
??? Material Summary
??? Material Double
??? Generate Barcode

Master Data
??? Master STO
??? Master Plant
??? Master Material
??? Master Keterangan
??? Master Location
??? Konfirmasi Rak
??? User Management
```

### 6.2 Scanner

```text
Scan Material
??? Overview
??? Setup STO
??? Scanner
??? Request QR/Barcode
??? Scan History
??? Material Summary
```

`Material Double` ditambahkan untuk scanner hanya ketika flag validator aktif. Admin selalu memiliki kemampuan validator dan flag tersebut tidak dapat dinonaktifkan selama role-nya admin.

### 6.3 Navigation state

- Parent group route aktif otomatis terbuka saat initial load.
- Active item mengikuti workspace tab yang sedang aktif, bukan hanya URL parent browser.
- Klik parent hanya expand/collapse. Klik submenu membuka workspace.
- Recent kembali ke recent pane dan menghapus active submenu tanpa menghapus tab.
- Permission counter dan validator visibility harus berasal dari state server.
- Scanner non-validator tidak boleh melihat atau mengakses Material Double melalui URL langsung.

## 7. Core Components

### 7.1 Enterprise toolbar dan command

Toolbar berada tepat di atas filter atau tabel. Urutan canonical: primary action, refresh, reset, separator bila perlu, lalu export/secondary action. Toolbar menggunakan tinggi stabil dan boleh wrap di mobile.

| Jenis | Contoh | Aturan |
|---|---|---|
| Primary | Save, Add Scan, Filter | Satu dominan per konteks |
| Secondary | Refresh, Cancel, Reset | Neutral outline atau text button |
| Success | Export Excel, Approve | Hanya untuk hasil positif yang jelas |
| Danger | Delete, Reject | Konfirmasi untuk aksi destruktif |
| Icon | Close, delete row, camera | Simbol familiar, tooltip, accessible name |

Button wajib memiliki `default`, `hover`, `focus`, `disabled`, dan `loading` state. Loading menonaktifkan double submit dan tidak mengubah dimensi tombol. Action destruktif berada setelah action netral.

### 7.2 Form controls

- Label selalu terlihat di atas atau di samping field; placeholder hanya contoh.
- Tinggi kontrol desktop `32-36px`; touch control minimal `40px`.
- Dropdown memakai arrow di sisi kanan field dan tidak menimpa value.
- Searchable dropdown dipakai untuk option set panjang.
- Switch dipakai untuk boolean seperti Active atau Validator; checkbox untuk multi-select.
- Readonly terlihat berbeda dari disabled dan nilainya tetap memiliki contrast yang cukup.
- Unit atau format harus dekat dengan input terkait.
- Error ditampilkan inline di bawah field dan ringkas; fokus diarahkan ke error pertama saat submit gagal.
- Plant dan Location yang saling bergantung wajib mencegah kombinasi tidak valid.

### 7.3 Cards dan panels

Card dipakai untuk unit informasi atau tool yang benar-benar framed, bukan untuk membungkus setiap section. Jangan menaruh card di dalam card. Header card berisi title kiri dan action opsional kanan; body memakai padding `12-16px`.

KPI card memuat label, nilai, konteks waktu/filter, dan opsional indikator perubahan. Nilai tidak boleh menjadi link tersembunyi tanpa affordance.

### 7.4 Charts dan progress

Gunakan Chart.js self-hosted. Chart wajib memiliki title, legend bila seri lebih dari satu, tooltip, empty state, dan label yang tetap bermakna tanpa warna. Dashboard admin boleh mengikuti filter halaman; validator realtime harus menyebutkan scope global bila tidak terfilter.

Progress indicator menjelaskan `completed/total` dan persentase. Jangan memakai chart untuk data yang lebih cepat dibaca sebagai satu angka atau tabel pendek.

### 7.5 Tables dan filters

Tabel admin untuk data besar wajib menggunakan DataTables server-side: pagination, search, filter, sorting, dan total count dikerjakan backend. Jangan memuat seluruh dataset ke browser atau memakai `Model::all()` untuk halaman utama.

- Toolbar berada di atas filter; filter berada di atas tabel.
- Filter aktif dipertahankan saat refresh dan ikut diterapkan pada export.
- Header dan kolom memiliki lebar stabil; kolom No dibuat sesempit mungkin.
- Barcode mendapat ruang prioritas dan tidak dipotong bila kolom lain masih dapat dipersempit.
- Status dan Action memiliki ruang minimum agar badge serta command tidak bertabrakan.
- Row hover dan selected state berbeda. Sorting indicator tidak menimpa label.
- Mobile memakai horizontal overflow pada container tabel, bukan seluruh halaman.
- Loading DataTables berada di tengah tabel. Jangan tampilkan moving-dot loader tambahan pada saat yang sama.

### 7.6 Inline add dan edit

All Scan Results memakai inline editor, bukan modal. Add menyisipkan row kosong di atas tabel; Edit menyisipkan child row tepat di bawah parent. Setiap `td` harus mengikuti grid/lebar kolom induk sehingga user tidak perlu scroll tambahan.

Hanya satu editor aktif. Save dan Cancel berada di Action. Klik luar menutup row kosong; bila ada perubahan, minta konfirmasi sebelum membuang input. Error backend tampil dekat row tanpa menggeser header atau menghapus value user.

### 7.7 Status badges

Badge compact, uppercase hanya bila konsisten dengan label existing, dan tidak dipakai sebagai tombol kecuali memiliki affordance interaktif. Badge panjang seperti Material Salah memakai font/padding lebih kecil tetapi tidak boleh overlap dengan Time atau Action.

### 7.8 Feedback, modal, dan loading

- Gunakan modal untuk task terfokus yang memerlukan input atau review detail.
- Gunakan SweetAlert2 untuk confirm destructive, duplicate warning, dan hasil command penting.
- Toast dipakai untuk sukses non-blocking; error yang perlu tindakan tetap terlihat.
- Modal wajib memiliki title, close, primary/secondary action yang jelas, focus trap, Escape behavior, dan body scroll pada mobile.
- Empty state menjelaskan bahwa data tidak ada untuk scope/filter aktif dan menawarkan action hanya bila relevan.
- Gunakan satu loading indicator per konteks. Equalizer loader adalah default table/page loader; spinner kecil boleh dipakai di tombol.

### 7.9 Pagination

Pagination server-side menampilkan Previous, `Page x / y`, Next, dan jumlah row. Scanner recent menggunakan maksimum 50 row per page dan button compact. Disabled control tetap terlihat tetapi tidak dapat difokuskan sebagai action aktif.

## 8. Status and Data Formatting

### 8.1 Status vocabulary

| State | Warna | Label minimum |
|---|---|---|
| OK / Valid / Approved / Confirmed | Success | Teks status + icon opsional |
| Duplicate / Need Check | Warning | Teks warning yang eksplisit |
| Invalid / Error / Rejected | Danger | Alasan bila tersedia |
| Pending | Warning/neutral | Tahap proses saat ini |
| Inactive | Neutral muted | `Inactive`, bukan hanya warna abu |

Gunakan istilah yang sama pada badge, filter, modal, export, dan chart. Jangan mengubah arti business status demi warna visual.

### 8.2 Identifiers dan quantity

- Barcode material ditampilkan uppercase, monospace, dan dapat disalin.
- QR raw lengkap hanya ditampilkan saat diperlukan untuk validasi atau audit.
- Lot menggunakan monospace dan tidak diberi prefix berulang jika kolom sudah bernama Lot.
- Quantity ditampilkan sebagai `1 pcs`, `2 pcs`, dan seterusnya.
- Plant dan Location memakai nama yang dikenal user; ID database tidak ditampilkan.
- Nilai kosong ditampilkan `-`, bukan string `null`.

### 8.3 Date and time

Gunakan timezone aplikasi. Tabel audit/admin memakai `Y-m-d H:i:s`. Recent scan hari ini menampilkan `H:i:s` di area metadata karena tanggal sudah tersirat oleh scope Today. Jika scope mencakup lebih dari satu tanggal, tampilkan timestamp lengkap.

### 8.4 Dimensions

Angka dimensi tidak menampilkan trailing `.00`. Desimal non-zero tetap dipertahankan secara akurat.

| Shape | Format | Contoh |
|---|---|---|
| Flat | `thickness x width x length` | `59 x 96 x 99` |
| Round | `?diameter x length` | `?51 x 835` |

Jangan menampilkan thickness/width untuk Round atau diameter untuk Flat. Gunakan simbol `?` secara konsisten.

### 8.5 Ordering dan overflow

Data terbaru diurutkan `created_at DESC, id DESC` agar stabil. Nomor descending harus dihitung dari total dan posisi page, bukan ID database. Barcode tidak boleh di-ellipsis selama ruang dapat diambil dari kolom sekunder. Jika truncation tidak dapat dihindari, sediakan title/tooltip dan akses ke nilai lengkap.

## 9. Page Patterns

Setiap halaman wajib memiliki tujuan jelas, primary action, loading, empty, success, dan error state. Responsive behavior harus dirancang bersama desktop layout, bukan ditambahkan setelahnya.

### 9.1 Login dan forgot password

Login adalah form tunggal yang ringkas: brand, username, password, submit, dan link forgot password bila flow tersedia. Error credential tampil tanpa membocorkan akun valid. Submit memiliki loading dan mencegah double click. Pada mobile form memenuhi lebar aman tanpa decorative hero.

Forgot password menjelaskan hasil request secara netral. Jangan mengungkap apakah username/email terdaftar.

### 9.2 Admin dashboard dan scanner overview

Admin dashboard bersifat desktop-first: filter global, KPI, chart, lalu Latest Scan server-side maksimal 50 row per page. Semua widget yang dipengaruhi filter harus diperbarui bersama dan menunjukkan loading satu kali per widget.

Scanner overview mobile-first memuat activity user dan Scan Overview: Total Today, Valid Scans, Duplicate Scans, dan Invalid Scans. Scanner validator mendapat Validator Overview tambahan: Total Barcode, Valid, Need Check, serta chart jumlah validasi per validator. Data validator bersifat global/realtime dan harus konsisten antarvalidator; chart juga memasukkan admin yang melakukan validasi.

`Total Barcode` adalah jumlah group duplicate yang tampil pada Material Double, `Valid` adalah group yang sudah memiliki validasi, dan `Need Check` adalah sisanya. `Valid + Need Check` wajib sama dengan `Total Barcode`. Chart Validation by Scanner menghitung jumlah aksi validasi per user validator, termasuk admin.

### 9.3 Setup STO dan Location/Rack

Setup menampilkan PIC dan STO aktif sebagai readonly, lalu Plant dan Location sebagai input utama. Location hanya memuat data yang valid untuk user/plant sesuai rule backend. Tombol Tambah Location membuka modal compact dengan input manual serta scanner QR/barcode rack.

Primary action hanya aktif setelah kombinasi valid. Bila tidak ada STO aktif, tampilkan blocking error yang jelas. Kamera location dapat dihentikan/ditutup dan tidak boleh tetap mengakses device setelah modal ditutup.

### 9.4 Scanner

Card konteks atas memakai dua kelompok: STO/PIC/Plant di kiri dan Location/Today di kanan; label dan value tetap satu baris bila ruang cukup. Location dapat diganti dan sekaligus memfilter recent scan.

Input mendukung scanner gun/manual, kamera Auto Scan, dan Select Barcode:

- Auto Scan mengirim hasil yang valid ke flow store.
- Select Barcode menampilkan label kandidat di atas barcode pada camera overlay; user memilih langsung di area kamera.
- Jika `BarcodeDetector` tidak tersedia, tampilkan fallback dan gunakan mode lama.
- Kamera default dapat di-hide dan selalu memiliki tombol on/off yang jelas.
- Duplicate selalu memunculkan warning dan tidak boleh auto-submit hanya karena barcode yang sama dipindai kembali.

Recent scan hanya memuat data Today untuk user dan Location aktif. Row mobile: nomor, barcode satu baris, detail material/shape/size/qty/lot, lalu badge dan delete. Location serta jam berada di bawah action. Format detail tidak memakai prefix Lot, misalnya `HP4MA - Flat - 45 x 30 x 405 - 1 pcs - ST2603`. Gunakan server-side pagination 50 row, terbaru di atas, dan refresh page pertama setelah save.

### 9.5 Scan History dan Material Summary

Scan History hanya menampilkan row yang boleh dilihat scanner login. Filter: Barcode, Material, Plant, Location, dan date range. Option distinct diurutkan berdasarkan penggunaan terbaru. Tabel mencantumkan Location, loading/refresh animation tunggal, dan server-side pagination. Setelah scan baru, initial load maupun refresh harus mengambil data terbaru, bukan cache count dengan row lama.

Material Summary mengelompokkan barcode/material dan menggunakan DataTables server-side. Scanner biasa melihat scope miliknya; scanner validator melihat scope global. Scope tersebut wajib dijelaskan pada heading/filter agar tidak ambigu.

### 9.6 All Scan Results

Halaman admin memakai toolbar Add Scan, Refresh, Reset, Export Excel, Export PDF; panel filter; lalu tabel server-side. Kolom fokus pada barcode, material, shape, dimensi, lot, qty, user, plant, location, time, status, dan action. STO tetap tersedia sebagai filter/data backend walaupun dapat dikeluarkan dari tabel demi ruang.

Add/Edit mengikuti inline editor pada 7.6. Manual data tetap divalidasi backend. Duplicate create menampilkan warning dan hanya disimpan sebagai row baru setelah konfirmasi eksplisit.

### 9.7 Material Double

Tabel mengelompokkan duplicate sesuai kontrak backend dan memiliki filter STO, Plant, Location, Material, serta date range. Action canonical:

- Valid: tandai group terverifikasi tanpa menghapus row.
- Tidak Valid: buka detail modal, pilih row, lalu Delete Selected.
- Scan: buka modal kamera/input manual dengan Plant dan Location dari group sebagai readonly.

Detail modal memuat checkbox, No, QR Code, Material, Shape, Size, dan waktu scan bila diperlukan untuk membedakan row. Delete Selected memerlukan konfirmasi, hard delete + audit, lalu menandai group valid sesuai flow aktif dan memperbarui tabel terkait. Feedback sukses harus menjelaskan kedua hasil tersebut.

### 9.8 Master Data

Master STO, Plant, Material, Keterangan, Location, dan User Management memakai pola yang sama: toolbar, server-side table, status, dan action. Form create/edit menggunakan modal atau inline pattern yang konsisten per halaman.

Master Location admin menampilkan Location, PIC, Created At, Status, Action. User Management menampilkan role dan switch Validator; role admin selalu validator aktif dan switch terkunci, sedangkan scanner dapat diubah sesuai permission.

### 9.9 Barcode, Rack Confirmation, export, dan print

Request QR/Barcode dan Generate Barcode harus membedakan input, preview, hasil, dan command download/print. Preview bukan pengganti validasi backend.

Rack Confirmation menampilkan identitas rack, user/PIC, status, waktu, dan action dengan vocabulary yang sama seperti bagian 8.

Export selalu mengikuti filter aktif dan saat ini berjalan secara synchronous dalam request HTTP. UI wajib menampilkan loading yang jelas, menonaktifkan double submit, dan mempertahankan state filter sampai file diterima atau request gagal. Queue worker belum menjadi bagian dari runtime/deployment aktif; migrasi ke queued export adalah pekerjaan arsitektur terpisah.

Print label dikecualikan dari application shell. Gunakan putih/hitam high-contrast, ukuran fisik eksplisit, barcode tidak terdistorsi, area aman printer, dan `@media print` yang menyembunyikan control.

## 10. Responsive and Accessibility Rules

### 10.1 Responsive

- Admin adalah desktop-first, tetapi command penting tetap dapat dipakai pada tablet/mobile.
- Scanner adalah mobile-first dan diuji pada lebar sempit serta browser Android.
- Touch target minimum `40 x 40px`; jarak antartarget mencegah salah tap.
- Di bawah `768px`, grid multi-kolom menjadi satu kolom kecuali pasangan informasi ringkas yang terbukti muat.
- Table overflow berada pada wrapper horizontal; page body tidak boleh bergeser ke kanan.
- Modal memakai lebar viewport aman, body scroll, footer action tetap terjangkau, dan tidak melebihi tinggi layar.
- Recent scan menjaga nomor, barcode, status, dan delete terbaca. Metadata dipindahkan ke baris berikutnya sebelum barcode dipotong.
- Label, badge, timestamp, arrow dropdown, dan action tidak boleh overlap pada viewport apa pun.
- Gunakan `aspect-ratio`, grid tracks, dan min/max width untuk camera, chart, toolbar, dan control fixed-format agar layout tidak bergeser saat loading.

### 10.2 Accessibility

- Gunakan elemen `button` untuk command dan link untuk navigasi.
- Setiap input memiliki `label`; setiap icon-only button memiliki `aria-label` dan tooltip.
- Urutan tab keyboard mengikuti urutan visual. Focus indicator selalu terlihat.
- Accordion mengelola `aria-expanded`; tab memakai semantics tab yang sesuai; state aktif tidak hanya berupa warna.
- Modal memindahkan fokus ke dalam, menahan fokus, mendukung Escape bila aman, lalu mengembalikan fokus ke trigger.
- Loading dan hasil asynchronous diumumkan melalui live region tanpa mengganggu input scanner.
- Error dikaitkan ke field dan berisi cara memperbaiki, bukan hanya `Invalid`.
- Status selalu memiliki text/icon selain warna. Kontras text, icon, border, dan focus memenuhi WCAG AA.
- Target destruktif memerlukan konfirmasi dan tidak ditempatkan berdekatan dengan primary action tanpa jarak.
- Pada `prefers-reduced-motion: reduce`, hilangkan sliding/bouncing dan pertahankan feedback statis.

## 11. Technical Implementation Rules

1. Gunakan `resources/views/components/layouts/app.blade.php` melalui `<x-layouts.app>`.
2. Gunakan CSS variables layout existing; token baru harus reusable, bernama semantik, dan ditambahkan pada sumber canonical.
3. Asset harus self-hosted. Stack aktif: Inter, jQuery, DataTables, SweetAlert2, Chart.js, html5-qrcode, serta Boxicons/inline SVG.
4. Halaman workspace wajib kompatibel dengan `html.is-iframe` dan event sinkronisasi tab/sidebar existing.
5. Data besar wajib DataTables/server-side atau pagination server-side khusus mobile.
6. Gunakan helper global existing untuk confirm, toast, CSRF fetch, dan loading bila tersedia; jangan membuat implementasi duplikat per halaman.
7. Hindari inline style baru ketika class/pattern reusable dapat dibuat. Page-specific CSS hanya untuk kebutuhan unik halaman.
8. Controller/API menentukan data, permission, dan state; Blade tidak boleh menebak authorization.
9. Dimensi komponen fixed-format harus stabil melalui CSS grid, `min-width`, `max-width`, atau `aspect-ratio`.
10. Print label boleh tanpa application shell, tetapi wajib high-contrast, print-safe, dan memakai ukuran fisik eksplisit.

Perubahan UI harus ditinjau pada full-page dan iframe mode. Jangan menambahkan CDN fallback yang membuat production bergantung pada internet.

## 12. Known Legacy Deviations

Bagian ini adalah inventaris teknis, bukan instruksi untuk mengubah UI dalam pekerjaan dokumentasi ini.

| Deviation | Risiko | Arah normalisasi |
|---|---|---|
| Dashboard/filter/card tertentu memakai radius `12-16px` | Visual tidak konsisten dengan tool operasional | Gunakan `2px` untuk control dan maksimal `8px` untuk modal/KPI baru |
| Referensi token `--bg-color` yang tidak didefinisikan canonical | Fallback warna berbeda antarhalaman | Ganti bertahap dengan `--workspace-bg` atau `--surface` |
| Inline style berulang pada Blade | Sulit dirawat dan rawan drift | Ekstrak ke class reusable di layout/page stylesheet |
| Ukuran page title dan card title berbeda antarhalaman | Hierarchy tidak konsisten | Ikuti typography scale bagian 4.2 |
| CSS table/filter/modal diduplikasi per halaman | Fix tidak tersebar merata | Konsolidasikan primitive bersama tanpa rewrite besar |
| Campuran istilah Indonesia/Inggris pada command | Cognitive load | Pertahankan istilah domain, normalisasi command pada revisi halaman terkait |
| Class job/`ExportRequest` tersedia tetapi queue worker belum dioperasikan | Dokumentasi dapat salah menganggap export sudah asynchronous | Perlakukan flow aktif sebagai synchronous sampai worker, monitoring, retry, dan deployment queue benar-benar diterapkan |

Legacy deviation boleh diperbaiki bertahap saat halaman disentuh, selama behavior, permission, dan business rule tidak berubah tanpa requirement terpisah.

## 13. Definition of Done Checklist

Sebuah perubahan UI selesai hanya jika seluruh item relevan berikut terpenuhi:

- [ ] Menggunakan application shell dan token canonical.
- [ ] Menu, action, dan data mengikuti role/permission backend.
- [ ] Tampil benar pada desktop serta mobile utama di sekitar breakpoint `768px`.
- [ ] Full-page dan `html.is-iframe` tidak overlap atau menghasilkan scrollbar ganda.
- [ ] Touch target minimal `40px` untuk workflow mobile.
- [ ] Text, barcode, timestamp, badge, dropdown arrow, dan action tidak bertabrakan.
- [ ] Barcode lengkap mendapat prioritas ruang; truncation memiliki akses ke nilai penuh.
- [ ] Loading hanya satu per konteks dan layout tidak bergeser saat loading.
- [ ] Success, error, validation, disabled, empty, dan no-result state tersedia.
- [ ] Status memiliki label, tidak hanya warna, dan mengikuti vocabulary bagian 8.
- [ ] Format qty, date/time, lot, dan dimensi konsisten.
- [ ] Tabel data besar memakai server-side processing dan filter/export memiliki scope sama.
- [ ] Keyboard focus, label, accessible name, modal focus, dan contrast sudah diperiksa.
- [ ] Aksi destruktif memakai confirm dan audit behavior backend tetap terjaga.
- [ ] Asset berasal dari bundle self-hosted dan halaman tetap berfungsi tanpa CDN.
- [ ] Print output diuji pada ukuran media target bila perubahan menyentuh label.
- [ ] Tidak menambah nested cards, decorative layout, atau inline style yang dapat direuse.
- [ ] Visual diperiksa dengan data kosong, data panjang, badge panjang, dan jumlah row realistis.

## 14. Implementation References

- `AGENTS.md` - engineering rules, authorization, dan business constraints.
- `resources/views/components/layouts/app.blade.php` - application shell canonical.
- `resources/views/scan/scanner.blade.php` - pola scanner mobile dan recent scan.
- `resources/views/scan/results.blade.php` - pola Scan History.
- `resources/views/admin/dashboard.blade.php` - pola dashboard admin.
- `resources/views/admin/scan-results.blade.php` - pola tabel besar dan inline editor.
- `resources/views/admin/material-double.blade.php` - pola validator duplicate.
- `scripts/publish-vendor-assets.js` - publication asset self-hosted.

Jika implementasi aktif dan dokumen ini berbeda, buat keputusan eksplisit: perbarui implementasi untuk mengikuti canonical rule atau revisi dokumen melalui review. Jangan membiarkan keduanya drift tanpa catatan.
