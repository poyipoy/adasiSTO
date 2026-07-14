# Materi & Panduan Presentasi PowerPoint Seminar Proposal (Sempro)
## Tugas Akhir: Rancang Bangun Sistem Informasi Pengadaan Material Impor Berbasis Web dan Konversi Kurs Otomatis pada PT. Astra Daido Steel Indonesia (ADASI Portal Supplier)

---

### Panduan Umum Desain Visual & Layout Slide (Untuk Canva / PowerPoint)
- **Palet Warna Utama (Brand Color ADASI Portal Supplier)**:
  - **Primary Blue (`#1F5FA6`)**: Gunakan untuk judul utama slide, header tabel, border card, dan elemen grafis dominan.
  - **ADASI Accent Red (`#C0392B`)**: Gunakan sebagai aksen penegasan (badge status penting, highlight masalah, grafik harga tertinggi/NG).
  - **Card / Container Background (`#F4F6F8`)**: Warna latar belakang kotak kartu agar kontras dengan background utama yang putih bersih.
  - **Text Dark (`#1E293B`)**: Untuk teks paragraf, label, dan poin-poin agar memiliki *readability* maksimal.
- **Tipografi**:
  - *Heading/Judul*: **Inter / Poppins Bold** (Ukuran 28 - 36 pt).
  - *Body Text*: **Inter / Poppins Regular/Medium** (Ukuran 16 - 20 pt).
- **Konsep Visual Layout**: 
  - Gunakan layout *Card / Grid Multi-Kolom* (misal: 2 atau 3 kolom) untuk membedakan peran (`purchasing`, `supplier`, `qc`) atau membandingkan kondisi sebelum vs sesudah.
  - Hindari blok teks panjang; gunakan *bullet points* bernomor, tabel komparasi interaktif, serta ikon dari **Bootstrap Icons (`bi bi-*`)**.

---

## SLIDE 1: Halaman Judul (*Title Slide*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **Judul Utama**: Rancang Bangun Sistem Informasi Pengadaan Material Impor Berbasis Web dengan Evaluasi Penawaran Multi-Mata Uang pada PT. Astra Daido Steel Indonesia
* **Sub-Judul**: Studi Kasus: Implementasi **ADASI Portal Supplier** untuk Digitalisasi *Supply Chain*, Tracking Dokumen Impor, dan Audit Klaim QC Terintegrasi
* **Identitas Presenter**:
  - **Nama Mahasiswa**: [Nama Lengkap Anda]
  - **NIM**: [NIM Anda]
  - **Program Studi**: [Teknik Informatika / Sistem Informasi]
  - **Fakultas / Universitas**: [Nama Kampus Anda]
* **Elemen Visual**: Logo Universitas di sudut kiri atas, Logo PT. Astra Daido Steel Indonesia (ADASI) di sudut kanan atas. Ilustrasi grafis jaringan *Supply Chain* / kapal kargo impor / grafik analisis mata uang di sisi kanan slide.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Selamat pagi/siang kepada Yth. Bapak/Ibu Dewan Penguji dan Dosen Pembimbing. Assalamualaikum Wr. Wb. / Salam sejahtera bagi kita semua.*
> 
> *Pada kesempatan Seminar Proposal Tugas Akhir hari ini, saya **[Nama Anda]**, akan mempresentasikan rencana penelitian saya yang berjudul: **'Rancang Bangun Sistem Informasi Pengadaan Material Impor Berbasis Web dengan Evaluasi Penawaran Multi-Mata Uang pada PT. Astra Daido Steel Indonesia'**, atau yang dalam implementasi praktisnya dinamakan **ADASI Portal Supplier**.*
> 
> *Penelitian ini bertujuan untuk mendigitalisasi dan mengintegrasikan seluruh ekosistem pengadaan bahan baku impor—mulai dari penerbitan spesifikasi material oleh Purchasing, submission penawaran harga multi-mata uang oleh para Supplier luar negeri, konversi kurs otomatis, hingga verifikasi kedatangan dan klaim kualitas oleh tim Quality Control (QC).*
> *Mohon izin untuk memaparkan proposal ini dalam waktu kurang lebih 12 hingga 15 menit ke depan."*

---

## SLIDE 2: Latar Belakang Masalah (*Why This Project Matters*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **Mengapa Pengadaan Material Impor di PT. ADASI Membutuhkan Transformasi Digital?** (Layout 3 Kartu Masalah Utama):
  1. **📧 Fragmentasi Komunikasi & Penawaran Konvensional (Email & Excel Terpisah)**
     - Tim Purchasing mengirim spesifikasi (*Purchase Requirement / PR*) via email ke berbagai supplier luar negeri secara terpisah. Penawaran harga (*Quotation*) dikembalikan dalam format dokumen beraneka ragam, menyulitkan rekapitulasi massal.
  2. **💱 Kerumitan Evaluasi Harga Multi-Mata Uang (USD & JPY) vs IDR**
     - Supplier menawarkan harga dalam mata uang asing (USD atau JPY) per kilogram. Perhitungan konversi ke IDR (`IDR = price_per_kg × weight_needed × rate_to_idr`) secara manual sangat rentan kesalahan (*human error*) dan tidak memiliki histori kurs yang akuntabel.
  3. **📦 *Blind Spot* Tracking Kedatangan Dokumen Impor & Lambatnya Klaim QC**
     - Dokumen impor vital (Invoice, Bill of Lading, Packing List, Form E) sering tercecer karena tanpa *repository* terpusat. Selain itu, ketika material tiba dengan status rusak (*NG/No Good*), alur pelaporan klaim ke supplier membutuhkan waktu berhari-hari karena tidak langsung terhubung ke dokumen PO asal.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Bapak/Ibu Penguji yang saya hormati, penelitian ini dilatarbelakangi oleh kendala nyata yang dihadapi oleh PT. Astra Daido Steel Indonesia dalam mengelola rantai pasok material baja impor dari para mitra supplier luar negeri.*
> 
> *Pertama, proses yang berjalan saat ini masih bersifat terfragmentasi menggunakan email dan spreadsheet Excel. Ketika tim Purchasing menerbitkan kebutuhan material impor (PR), mereka harus mengirim email satu per satu ke banyak supplier. Balasan penawaran dari supplier pun memiliki format yang berbeda-beda, sehingga Purchasing menghabiskan waktu berjam-jam hanya untuk menyalin ulang ke tabel perbandingan manual.*
> 
> *Kedua, tantangan evaluasi harga multi-mata uang. Material impor baja ditawarkan oleh supplier dalam Dolar Amerika (USD) atau Yen Jepang (JPY) per kilogram. Dalam proses evaluasi manual, konversi nilai tukar ke Rupiah sering mengalami ketidaksesuaian akibat fluktuasi kurs harian dan tidak adanya histori pencatatan kurs acuan yang transparan di dalam satu sistem.*
> 
> *Dan ketiga, adanya 'blind spot' dalam pelacakan empat tanggal kritis impor dan pengelolaan lampiran dokumen bea cukai (seperti BL, Invoice, dan Form E). Ketika barang tiba dan diinspeksi oleh tim Quality Control (QC) dengan status NG (No Good), proses investigasi dan pengajuan klaim ke supplier sering kali tersendat karena data bukti foto dan riwayat PO tidak berada dalam satu platform terpadu."*

---

## SLIDE 3: Rumusan Masalah & Tujuan Penelitian

### 🖥️ Konten Slide (Tampilan di Layar):
* **❓ Rumusan Masalah**:
  1. Bagaimana merancang dan membangun sistem informasi portal supplier berbasis web yang mengisolasi data penawaran secara aman antar mitra supplier (*Supplier Data Isolation*)?
  2. Bagaimana menerapkan algoritma konversi kurs otomatis dan modul perbandingan harga komprehensif (3 View: Antar Supplier, Historis, & vs Harga Terbaik) untuk mendukung keputusan pengadaan yang akurat?
  3. Bagaimana merancang sistem pelacakan dokumen impor polimorfik serta integrasi siklus inspeksi QC hingga pengajuan klaim material (*Material Claim Workflow*)?
* **🎯 Tujuan Penelitian**:
  1. **Membangun ADASI Portal Supplier** berbasis web menggunakan framework **Laravel 10/11 (PHP 8.2+)** dan **Bootstrap 5** dengan arsitektur Multi-Role (*Role-Based Access Control*).
  2. **Mengotomatisasi Evaluasi Finansial** melalui konversi kurs dinamis dari tabel histori `exchange_rates` dan visualisasi grafik perbandingan harga (*Chart.js / ApexCharts*).
  3. **Mewujudkan *End-to-End Traceability*** pada pengadaan material impor melalui pelacakan 4 tanggal krusial PO dan repositori dokumen terpusat.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Berdasarkan latar belakang tersebut, rumusan masalah penelitian ini difokuskan pada tiga aspek utama: perancangan arsitektur portal dengan isolasi keamanan data supplier, otomasi evaluasi dan konversi harga penawaran multi-currency, serta integrasi tracking dokumen impor dan klaim inspeksi QC.*
> 
> *Adapun tujuan akhir dari penelitian Tugas Akhir ini adalah menghasilkan sistem **ADASI Portal Supplier** yang siap diterapkan di perusahaan. Sistem ini tidak hanya mendigitalisasi proses input penawaran oleh supplier, tetapi memberikan alat bantu analitik yang cerdas bagi tim Purchasing melalui 3 tampilan perbandingan harga, sekaligus menjadi jembatan transparansi saat terjadi klaim material rusak dari tim QC."*

---

## SLIDE 4: Batasan Masalah (*Research Scope*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **🚧 Batasan & Ruang Lingkup Pengembangan**:
  1. **Platform & Hak Akses (RBAC 4 Role)**: Sistem dikembangkan berbasis Web Responsive dengan pembatasan hak akses ketat untuk 4 peran:
     - `admin`: Kelola pengguna, data master, dan pembaruan kurs mata uang (`exchange_rates`).
     - `purchasing`: Buat spesifikasi PR (`purchase_requirements`), evaluasi penawaran, & terbitkan PO.
     - `supplier`: Lihat PR aktif & input penawaran (`quotations`) — **Wajib terisolasi (`where('supplier_id', auth()->id())`)**, tidak dapat melihat data supplier lain.
     - `qc`: Input hasil inspeksi fisik (`qc_inspections`) & ajukan bukti klaim kerusakan (`material_claims`).
  2. **Spesifikasi Dimensi Material Baja (`pr_items`)**:
     - Sistem memproses spesifikasi teknis spesifik baja impor: HS Code, Nama Material, Shape (Flat/Round), Thickness, Diameter Inner (`d_inner`), Diameter Outer (`d_outer`), Width, Length, dan `weight_needed` (kg).
  3. **Rumus Konversi & Mata Uang**:
     - Dibatasi pada konversi mata uang **USD** dan **JPY** ke **IDR** menggunakan rumus baku: `IDR = price_per_kg × weight_needed × rate_to_idr` (mengambil kurs valid terbaru tanpa meniban histori lama).
  4. **Pelacakan Siklus PO (`purchase_orders`)**:
     - Difokuskan pada tracking **4 Tanggal Kritis**: `purchase_requirements.created_at` (PR dibuat), `purchase_orders.created_at` (PO dibuat), `estimated_arrival` (Estimasi tiba), dan `actual_arrival` (Tiba fisik di pabrik).

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Agar penelitian ini memiliki fokus yang tajam dan mendalam, saya menetapkan empat batasan masalah teknis.*
> 
> *Pertama, arsitektur sistem dibagi menjadi 4 Role utama: Admin, Purchasing, Supplier, dan QC. Yang paling krusial di sini adalah penerapan **Isolasi Data Supplier di level query database**. Setiap pengguna dengan role supplier hanya dapat melihat dan mengisi penawaran untuk akun perusahaannya sendiri, sehingga kerahasiaan harga antar pesaing tetap terjamin 100%.*
> 
> *Kedua, struktur item permintaan disesuaikan dengan parameter industri baja PT. ADASI, mencakup HS Code bea cukai, bentuk material, dimensi ketebalan, hingga berat total kebutuhan dalam satuan kilogram.*
> 
> *Ketiga, perhitungan konversi harga difokuskan pada mata uang impor utama yaitu USD dan JPY terhadap Rupiah, dengan menggunakan histori tabel kurs dinamis.*
> *Dan keempat, pelacakan pesanan difokuskan pada 4 tanggal kritis siklus impor, mulai dari tanggal terbit PR, tanggal terbit PO, estimasi kedatangan kapal, hingga tanggal kedatangan aktual yang diverifikasi oleh QC."*

---

## SLIDE 5: Landasan Teori & Arsitektur Teknologi (*Tech Stack*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **🛠️ Spesifikasi Teknologi & Konsep Kunci** (Layout Grid 4 Kotak):
  - **1. PHP 8.2+ & Laravel MVC Architecture**
    Implementasi *Model-View-Controller* yang rapi, dilengkapi proteksi *Middleware RBAC*, *Form Request Validation*, dan penamaan route standar (`role.resource.action`).
  - **2. Polymorphic Relationship (`attachments`)**
    Satu tabel relasional `attachments` berbasis polimorfik (`morphMany`) untuk menangani berbagai tipe dokumen (Surat Penawaran Supplier, Foto Bukti QC NG, Lampiran Invoice/BL PO, dan Bukti Klaim).
  - **3. Server-Side DataTables (`DataTables.js` + AJAX)**
    Pengolahan dan pencarian server-side berkinerja tinggi untuk memproses ribuan data riwayat PR, PO, dan Quotation tanpa membebani memori browser client.
  - **4. Data Visualization & Export Engine**
    Integrasi **Chart.js / ApexCharts** untuk pemetaan grafik perbandingan harga, serta **Laravel Excel (Maatwebsite)** dan **Laravel Mail (SMTP)** untuk notifikasi email & ekspor laporan format `.xlsx`.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Dalam membangun ADASI Portal Supplier, saya menerapkan landasan rekayasa perangkat lunak modern dengan fondasi framework **Laravel pada PHP 8.2**.*
> 
> *Salah satu sorotan arsitektur database pada penelitian ini adalah penggunaan **Polymorphic Relationship pada tabel attachments**. Alih-alih membuat kolom file atau tabel lampiran terpisah di setiap modul, saya merancang satu tabel attachments serbaguna yang dapat dihubungkan secara dinamis ke model Quotation, Purchase Order, QC Inspection, maupun Material Claim. Hal ini membuat manajemen penyimpanan file di folder private storage menjadi sangat terstruktur dan mudah di-maintenance.*
> 
> *Selain itu, untuk menjamin user experience yang cepat saat menampilkan ribuan transaksi impor, seluruh tabel utama di-render menggunakan **Server-Side DataTables via AJAX**.*
> *Dan untuk pendukung keputusa, saya mengintegrasikan library Chart.js untuk memvisualisasikan tren harga, serta Maatwebsite Excel untuk kebutuhan ekspor laporan resmi."*

---

## SLIDE 6: Skema Basis Data Relasional & Polimorfik (*Database Schema*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **🗄️ Relasi Entitas Utama (ERD Summary)** (Layout Visual Tabel Interkoneksi):
  - `users` (1:1) ➔ `suppliers` (company_name, npwp, category).
  - `periods` (1:N) ➔ `purchase_requirements` (PR master dengan auto-numbering `REQ/MM/YYYY/XXX`).
  - `purchase_requirements` (1:N) ➔ `pr_items` (Detail spesifikasi HS Code & dimensi material baja).
  - `pr_items` (1:N) ➔ `quotation_items` ⬅ (N:1) `quotations` (Penawaran supplier `currency` USD/JPY).
  - `quotations` (1:1) ➔ `purchase_orders` (`po_number` `PO/MM/YYYY/XXX`, `estimated_arrival`, `actual_arrival`).
  - `purchase_orders` (1:N) ➔ `po_documents` (Tracking `doc_type`: Invoice, BL, Packing List, Form E).
  - `purchase_orders` (1:1) ➔ `qc_inspections` (1:N) ➔ `qc_items` (Inspeksi dimensi aktual vs spesifikasi).
  - `qc_inspections` (1:N) ➔ `material_claims` (Pengajuan klaim jika status `ng` / No Good).
  - **Polymorphic Table**: `attachments` (`attachable_type`, `attachable_id`, `file_path` di `storage/app/private`).

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Slide ini memperlihatkan skema basis data relasional komprehensif yang telah saya rancang khusus untuk memenuhi alur bisnis PT. ADASI.*
> 
> *Alur relasi dimulai dari entitas **Periods** dan **Users/Suppliers**. Ketika tim Purchasing membuka periode pengadaan, mereka membuat **Purchase Requirements (PR)** beserta detail **PR Items** yang memuat nomor HS Code dan dimensi material.*
> 
> *Selanjutnya, supplier memasukkan **Quotations** dan **Quotation Items** yang merujuk langsung ke PR Item tersebut. Setelah penawaran dievaluasi dan dipilih oleh Purchasing, sistem me-generate **Purchase Order (PO)**.*
> 
> *PO ini terhubung langsung dengan tabel **PO Documents** untuk memantau status kelengkapan dokumen impor bea cukai, serta terintegrasi ke tabel **QC Inspections** dan **QC Items** ketika fisik barang tiba di pabrik. Jika dalam pemeriksaan QC ditemukan dimensi aktual yang tidak sesuai spesifikasi atau cacat fisik, data tersebut langsung mengalir ke tabel **Material Claims** yang terikat dengan ID Supplier bersangkutan."*

---

## SLIDE 7: Fitur Unggulan 1 — Modul Perbandingan Harga Komprehensif (3 View)

### 🖥️ Konten Slide (Tampilan di Layar):
* **📊 Decision Support System bagi Purchasing ADASI** (Layout 3 Kolom/Tab Visual):
  1. **Tab 1: Perbandingan Antar Supplier (*Side-by-Side View*)**
     - Menampilkan seluruh penawaran dari berbagai supplier untuk satu nomor PR dalam satu layar berdampingan.
     - Dilengkapi grafik batang (*Bar Chart*) komparasi harga setelah dikonversi ke IDR secara realtime.
  2. **Tab 2: Analisis Tren Historis (*Historical Price Chart*)**
     - Grafik garis (*Line Chart*) yang memetakan pergerakan harga satu material dari satu supplier tertentu melintasi beberapa periode/bulan pengadaan sebelumnya.
  3. **Tab 3: Komparasi vs Harga Terbaik (*vs Best Historical Price*)**
     - Membandingkan harga penawaran saat ini dengan query agregasi database: `MIN(price_per_kg)` dari seluruh riwayat pembelian material yang sama di masa lalu.
     - *Alert Indicator*: Memberi tanda hijau (🔥 *Best Price*) atau merah (⚠️ *Overpriced*) pada item penawaran.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Salah satu kontribusi teknis dan inovasi terbesar dalam penelitian Tugas Akhir ini adalah hadirnya **Modul Perbandingan Harga 3 View** yang berfungsi sebagai Decision Support System bagi tim Purchasing.*
> 
> *Pada **View Pertama (Antar Supplier)**, sistem menyajikan komparasi side-by-side seluruh supplier yang menawar pada satu PR yang sama. Tim Purchasing tidak perlu lagi menghitung manual karena sistem otomatis menampilkan harga asli (USD/JPY) disandingkan dengan nilai total IDR berdasarkan kurs acuan saat itu, lengkap dengan visualisasi Bar Chart.*
> 
> *Pada **View Kedua (Historis)**, sistem menyediakan grafik garis yang melacak rekam jejak harga suatu material dari supplier tertentu selama 1 atau 2 tahun ke belakang untuk melihat apakah tren harga mengalami kenaikan atau penurunan.*
> 
> *Dan pada **View Ketiga (vs Harga Terbaik)**, sistem secara otomatis melakukan query `MIN(price_per_kg)` pada histori database untuk mengecek apakah penawaran yang masuk hari ini lebih murah atau lebih mahal dibanding harga termurah yang pernah didapatkan PT. ADASI dalam sejarah pembelian material tersebut."*

---

## SLIDE 8: Fitur Unggulan 2 — Konversi Kurs Dinamis & Isolasi Data Keamanan

### 🖥️ Konten Slide (Tampilan di Layar):
* **💱 Algoritma Konversi Kurs Tanpa Overwrite (`exchange_rates`)**:
  - Kurs mata uang (`USD` / `JPY` ke `IDR`) disimpan secara historis dengan parameter `valid_from`.
  - **Logika Query Kurs Acuan**:
    ```php
    $rate = ExchangeRate::where('currency', $currency)
        ->where('valid_from', '<=', now())
        ->orderBy('valid_from', 'desc')->first();
    $idr = $item->price_per_kg * $item->pr_item->weight_needed * $rate->rate_to_idr;
    ```
  - *Integritas Audit*: Kurs baru selalu di-`INSERT` sebagai record baru, tidak pernah me-replace kurs lama, sehingga riwayat evaluasi harga PO masa lalu tetap akurat 100%.
* **🛡️ Proteksi Isolasi Data Supplier (*Tenant-Like Security*)**:
  - Middleware & Global Scope / Query Builder Enforcement:
    ```php
    Quotation::where('supplier_id', auth()->id())->paginate(20);
    ```
  - Mencegah kebocoran data sensitif (*Price Leakage*); supplier tidak dapat mengakses endpoint atau memanipulasi ID untuk melihat penawaran kompetitor.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Keunggulan kedua dari sistem ini terletak pada integritas perhitungan finansial dan keamanan datanya.*
> 
> *Dalam hal konversi kurs, saya menerapkan aturan ketat bahwa nilai kurs tidak boleh di-hardcode ataupun di-overwrite. Setiap kali Admin memperbarui kurs Dolar atau Yen, sistem melakukan `INSERT` record baru di tabel `exchange_rates` dengan stempel waktu `valid_from`.*
> *Ketika kalkulasi dilakukan, sistem selalu menarik kurs valid terbaru pada saat penawaran diajukan. Dengan demikian, jika 3 tahun lagi auditor internal memeriksa data PO lama, nilai Rupiah yang tercatat tetap presisi sesuai dengan kurs pada hari transaksi tersebut dilakukan.*
> 
> *Dari sisi keamanan, saya mengimplementasikan **Supplier Data Isolation**. Setiap query yang dieksekusi oleh pengguna ber-role supplier secara otomatis di-filter oleh sistem dengan kondisi `where('supplier_id', auth()->id())`. Hal ini menutup celah kerentanan IDOR (Insecure Direct Object Reference) sehingga kerahasiaan penawaran antar mitra bisnis ADASI terjaga dengan standar keamanan tinggi."*

---

## SLIDE 9: Fitur Unggulan 3 — Tracking 4 Tanggal PO & Alur Klaim QC (*Closed-Loop*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **🗓️ Pelacakan 4 Tanggal Kritis Impor (*SLA Tracking*)**:
  1. `purchase_requirements.created_at` ➔ Tanggal inisiasi permintaan material.
  2. `purchase_orders.created_at` ➔ Tanggal PO resmi diterbitkan kepada supplier.
  3. `purchase_orders.estimated_arrival` ➔ Target kedatangan kapal/cargo di pelabuhan & pabrik (Diisi Purchasing).
  4. `purchase_orders.actual_arrival` ➔ Tanggal material benar-benar dibongkar di gudang (Diisi & diverifikasi QC).
* **🔬 Alur Inspeksi & Klaim Material Rusak (*Closed-Loop Quality Workflow*)**:
  - **Inspeksi QC (`qc_inspections`)**: QC memeriksa dimensi fisik (`actual_thickness`, `actual_width`, `actual_weight`).
  - **Keputusan Status**: Jika `status == 'OK'`, PO ditutup & disetujui. Jika `status == 'NG'`, QC **wajib mengunggah foto bukti** ke tabel `attachments` (Polymorphic, max 10MB, private storage).
  - **Penerbitan Klaim (`material_claims`)**: Sistem otomatis menerbitkan tiket klaim material NG kepada supplier bersangkutan, melampirkan foto bukti QC, dan melacak status penyelesaian klaim (Ganti barang / Potong tagihan).

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Keunggulan ketiga adalah hadirnya pelacakan rantai pasok terintegrasi dari mulai penerbitan PO hingga verifikasi kualitas barang di gudang.*
> 
> *Sistem melacak **4 Tanggal Kritis Impor** untuk mengukur Service Level Agreement (SLA) pengiriman. Dengan membandingkan `estimated_arrival` yang diinput Purchasing dengan `actual_arrival` yang divalidasi oleh QC saat truk tiba, manajemen ADASI dapat menilai rasio ketepatan waktu pengiriman dari masing-masing supplier (Supplier Delivery Rating).*
> 
> *Selanjutnya, ketika barang tiba, tim QC melakukan inspeksi dimensi fisik pada menu khusus. Jika seluruh ukuran sesuai spesifikasi, status disetujui OK. Namun jika ditemukan barang cacat atau dimensi diluar toleransi (NG / No Good), sistem mewajibkan pengunggahan foto bukti kerusakan ke private storage.*
> *Foto bukti ini secara otomatis langsung terlampir pada modul **Material Claims** yang dikirim ke Dasbor Supplier, menciptakan alur penyelesaian klaim (Closed-Loop Workflow) yang transparan, cepat, dan tidak bisa disanggah oleh supplier tanpa dasar."*

---

## SLIDE 10: Metodologi Pengembangan & Rencana Pengujian Sistem

### 🖥️ Konten Slide (Tampilan di Layar):
* **🔄 Metodologi Pengembangan (*Agile SDLC*)**:
  1. **Analisis Kebutuhan & Observasi**: Studi alur kerja eksisting tim Purchasing & QC PT. ADASI.
  2. **Desain Arsitektur**: Perancangan ERD relasional 16 tabel, desain UI/UX, dan pemetaan RBAC Middleware.
  3. **Iterasi Coding (Sprints)**:
     - *Sprint 1*: Setup Master Data, Auth RBAC, & Modul Permintaan PR.
     - *Sprint 2*: Portal Supplier Quotation, Konversi Kurs Otomatis, & Upload Attachment Polimorfik.
     - *Sprint 3*: Modul Perbandingan Harga (3 View Chart.js) & Penerbitan PO.
     - *Sprint 4*: Modul Tracking Dokumen Impor, QC Inspection, & Material Claim.
* **📋 Matriks Rencana Pengujian (*Testing Plan*)**:

| Jenis Pengujian | Target / Parameter Penilaian | Acceptance Criteria |
| :--- | :--- | :--- |
| **1. Black Box Testing** | Fungsionalitas seluruh form, validasi input, middleware role `admin`, `purchasing`, `supplier`, `qc`. | **100% Lulus** — Tidak ada rute yang salah alamat atau dapat diakses role yang salah. |
| **2. Security & Isolation Test** | Pengujian celah IDOR pada URL & parameter query supplier ID. | **100% Aman** — Supplier terbukti `403 Forbidden` / kosong saat mencoba mengakses ID penawaran orang lain. |
| **3. Financial Calculation Test** | Verifikasi akurasi matematis rumus konversi kurs & query `MIN(price_per_kg)`. | **100% Presisi** — Nilai IDR konsisten dengan tabel `exchange_rates` acuan tanpa selisih pembulatan fatal. |
| **4. UAT (User Acceptance Test)** | Uji coba langsung bersama user Purchasing & QC di PT. Astra Daido Steel Indonesia. | **Tingkat Kepuasan > 85%** & efisiensi waktu rekapitulasi penawaran meningkat drastis. |

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Dalam pengembangan sistem ADASI Portal Supplier ini, saya menggunakan metodologi **Agile SDLC** yang dibagi ke dalam 4 Sprint pengembangan terukur, dimulai dari fondasi RBAC hingga penyempurnaan modul perbandingan harga dan klaim QC.*
> 
> *Untuk menjamin kualitas perangkat lunak sebelum diimplementasikan di PT. ADASI, saya telah merancang 4 skenario pengujian ketat.*
> *Pertama, Black Box Testing untuk menguji seluruh fungsionalitas tombol dan middleware role.*
> *Kedua, **Security & Isolation Testing** khusus untuk menguji efektivitas isolasi data supplier, memastikan tidak ada celah bagi supplier untuk mengintip penawaran kompetitor.*
> 
> *Ketiga, Financial Calculation Testing untuk memvalidasi keakuratan matematis dari rumus konversi kurs mata uang dan algoritma pencarian harga terbaik historis.*
> *Dan keempat, User Acceptance Testing (UAT) bersama para praktisi nyata yaitu tim Purchasing dan tim QC PT. Astra Daido Steel Indonesia untuk memastikan sistem benar-benar memberikan dampak efisiensi operasional yang nyata."*

---

## SLIDE 11: Kesimpulan & Rencana Jadwal Pelaksanaan (*Closing*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **📌 Kesimpulan Proposal Tugas Akhir**:
  - **ADASI Portal Supplier** merupakan solusi digitalisasi integratif berbasis web yang mentransformasi pengadaan material impor PT. ADASI dari proses konvensional yang terfragmentasi menjadi alur kerja terpusat, aman, dan transparan.
  - Dengan fitur **Perbandingan Harga 3 View** dan **Konversi Kurs Otomatis**, sistem meningkatkan kecepatan dan akurasi pengambilan keputusan strategis tim Purchasing.
  - Integrasi **Tracking 4 Tanggal Kritis PO**, **Polymorphic Attachment**, dan **QC Material Claim** mewujudkan *traceability* penuh dari pemesanan hingga penanganan barang di lantai pabrik.
* **📅 Jadwal Pelaksanaan Penelitian (Timeline 4 Bulan)**:
  - **Bulan Ke-1**: Studi literatur, pengumpulan sampel dokumen PR/PO/BL di PT. ADASI, & finalisasi skema database.
  - **Bulan Ke-2**: Implementasi core engine, RBAC, Modul Purchasing (PR), & Modul Supplier Quotation.
  - **Bulan Ke-3**: Implementasi konversi kurs, Dasbor Perbandingan Harga 3 View, PO, & Modul QC/Claim.
  - **Bulan Ke-4**: Pengujian sistem (Black Box, Security, UAT), perbaikan bug, & penyusunan draf skripsi akhir.

*(Sesi Tanya Jawab / Q&A — Terima Kasih)*

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Sebagai kesimpulan dari presentasi seminar proposal ini, perancangan sistem **ADASI Portal Supplier** diharapkan dapat memberikan terobosan digital bagi PT. Astra Daido Steel Indonesia dalam menciptakan ekosistem pengadaan material impor yang cepat, akurat, terintegrasi, dan memiliki keunggulan audit trail yang kuat.*
> 
> *Adapun estimasi waktu pelaksanaan penelitian Tugas Akhir ini dijadwalkan selama 4 bulan, mulai dari analisis kebutuhan mendalam hingga pengujian akhir UAT bersama pihak perusahaan.*
> 
> *Demikian presentasi Seminar Proposal Tugas Akhir yang dapat saya sampaikan. Besar harapan saya untuk memperoleh saran, masukan, serta arahan yang konstruktif dari Bapak/Ibu Dewan Penguji dan Dosen Pembimbing demi kesempurnaan penelitian ini.*
> *Terima kasih atas perhatian Bapak dan Ibu sekalian. Waktu dan tempat saya kembalikan kepada Moderator."*

---

## 💡 Tips Tambahan Menghadapi Sesi Tanya Jawab (Q&A Defense Sempro):

1. **Jika Penguji Bertanya: *"Mengapa harus membangun web custom dengan Laravel, kenapa tidak memakai fitur purchasing dari ERP standar seperti SAP atau Odoo?"***
   - **Jawaban Anda**: *"Bapak/Ibu, sistem ERP enterprise standar memiliki biaya lisensi per user (*seat license*) yang sangat tinggi jika harus memberikan hak akses eksternal kepada puluhan atau ratusan supplier luar negeri PT. ADASI. Selain itu, alur konversi kurs dan modul perbandingan harga 3 view yang disesuaikan persis dengan kebiasaan analisis teknis spesifikasi baja ADASI (seperti pemetaan Shape, Diameter Inner/Outer, dan kemurnian material) sulit dikustomisasi pada ERP standar tanpa biaya yang sangat mahal. Portal custom Laravel ini berfungsi sebagai **Gateway Satelit** yang ramah pengguna, aman, dan nantinya data PO final yang sudah matang dapat diekspor/diintegrasikan ke ERP utama perusahaan tanpa membengkakkan biaya lisensi."*

2. **Jika Penguji Bertanya: *"Bagaimana Anda menjamin bahwa Supplier A tidak bisa meretas atau melihat penawaran harga dari Supplier B?"***
   - **Jawaban Anda**: *"Keamanan data supplier diproteksi secara berlapis di tingkat framework Laravel. Pertama, pada tingkat Middleware `RoleMiddleware` yang memblokir akses ke rute di luar role `supplier`. Kedua, pada setiap query Eloquent ORM di Controller, saya menerapkan *forced filtering query* `->where('supplier_id', auth()->id())`. Jadi meskipun seorang supplier mencoba mengganti parameter URL atau ID Quotation di browser (serangan IDOR), database secara fisik menolak mengembalikan record yang `supplier_id`-nya tidak cocok dengan session login pengguna tersebut."*

3. **Jika Penguji Bertanya: *"Mengapa Anda menggunakan tabel `attachments` dengan konsep Polymorphic daripada membuat kolom upload file di masing-masing tabel?"***
   - **Jawaban Anda**: *"Konsep Polymorphic Relationship (`morphMany`) di Laravel dipilih karena dalam sistem pengadaan impor ini terdapat minimal 4 modul berbeda yang membutuhkan fitur upload lampiran dengan tipe dan ukuran file yang bervariasi—yaitu dokumen pendukung penawaran supplier, dokumen bea cukai PO (Invoice/BL/Form E), foto inspeksi fisik QC, dan bukti klaim kerusakan. Jika dibuat kolom terpisah di setiap tabel, struktur database akan menjadi redundan dan sulit dikelola jika di masa depan ada penambahan modul baru. Dengan satu tabel `attachments` polimorfik, logika validasi file (maksimal 10MB, mime types), penyimpanan di `storage/app/private`, serta pencatatan metadata (`file_name`, `file_type`, `uploaded_by`) dapat distandardisasi secara konsisten di satu tempat."*
