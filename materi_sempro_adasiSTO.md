# Materi & Panduan Presentasi PowerPoint Seminar Proposal (Sempro)
## Tugas Akhir: Rancang Bangun Sistem Informasi Stock Opname (*Scan To Office*) Berbasis Web dan Barcode pada Industri Manufaktur Menggunakan Metode Agile

---

### Panduan Umum Desain Visual & Layout Slide (Untuk Canva / PowerPoint)
- **Palet Warna Utama (Brand Color adasiSTO)**:
  - **Primary Blue**: `#1F5FA6` (Gunakan untuk judul slide, header tabel, tombol utama, dan aksen grafis).
  - **Background**: White `#FFFFFF` atau Off-White `#F8FAFC` (Supaya teks terbaca jelas dan profesional).
  - **Text Dark**: `#1E293B` (Untuk teks paragraf dan poin-poin).
  - **Accent Warning/Alert**: `#D97706` / `#DC2626` (Untuk menyoroti masalah duplikasi dan error konvensional).
- **Tipografi**:
  - *Heading/Judul*: **Inter / Outfit / Arial Bold** (Ukuran 28 - 36 pt).
  - *Body Text*: **Inter / Roboto / Calibri** (Ukuran 16 - 20 pt).
- **Konsep Visual**: Gunakan layout *Card/Grid 2 Kolom* (kiri teks, kanan diagram/ikon/mockup UI) agar slide tidak penuh dengan teks. Hindari paragraf panjang; gunakan *bullet points* bernomor atau ikonik.

---

## SLIDE 1: Halaman Judul (*Title Slide*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **Judul Utama**: Rancang Bangun Sistem Informasi Stock Opname (*Scan To Office*) Berbasis Web dan Barcode pada Industri Manufaktur Menggunakan Metode Agile
* **Sub-Judul**: Studi Kasus / Implementasi Sistem **adasiSTO** (*Scan To Office*) untuk Akurasi dan Real-Time Inventory Control
* **Identitas Presenter**:
  - **Nama Mahasiswa**: [Nama Lengkap Anda]
  - **NIM**: [NIM Anda]
  - **Program Studi**: [Teknik Informatika / Sistem Informasi / Rekayasa Perangkat Lunak]
  - **Fakultas / Universitas**: [Nama Kampus Anda]
* **Elemen Visual**: Logo Universitas di sudut atas/bawah, serta aksen ilustrasi scanner barcode dan grafik jaringan di sisi kanan.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Selamat pagi/siang kepada Yth. Bapak/Ibu Dewan Penguji dan Dosen Pembimbing. Assalamualaikum Wr. Wb. / Salam sejahtera bagi kita semua.*
> 
> *Pada kesempatan Seminar Proposal hari ini, saya **[Nama Anda]**, akan mempresentasikan rencana penelitian Tugas Akhir saya yang berjudul: **'Rancang Bangun Sistem Informasi Stock Opname (Scan To Office) Berbasis Web dan Barcode pada Industri Manufaktur Menggunakan Metode Agile'**, atau yang dalam implementasinya dikenal sebagai sistem **adasiSTO**.*
> 
> *Penelitian ini berfokus pada digitalisasi proses perhitungan fisik material (stock take) di gudang manufaktur agar terintegrasi secara langsung, akurat, dan seketika dari lapangan ke kantor.*
> *Mohon izin untuk memulai presentasi dalam waktu kurang lebih 10 menit ke depan."*

---

## SLIDE 2: Latar Belakang Masalah (*Why This Matters*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **Mengapa Stock Opname Manufaktur Kritis & Bermasalah?** (Layout 3 Kartu / Poin Kunci):
  1. **📝 Pencatatan Konvensional yang Lambat & Rentan *Human Error***
     - Pencatatan spesifikasi material (ketebalan/diameter, dimensi, lot number) di atas kertas atau spreadsheet manual memakan waktu lama dan sering terjadi salah ketik/salah baca.
  2. **⚠️ Risiko Duplikasi & Kesalahan Hitung (*Double Scan*)**
     - Ribuan material tersebar di berbagai rak (*Location*) dan gedung (*Plant*). Saat banyak petugas lapangan mencatat secara paralel, sering terjadi pencatatan ganda atas material yang sama tanpa adanya sistem deteksi seketika.
  3. **⏳ Kesenjangan Waktu Lapangan vs. Kantor (*Scan To Office Gap*)**
     - Rekapitulasi kertas menjadi laporan resmi kantor (*Office Report*) membutuhkan waktu berhari-hari, menunda rekonsiliasi stok dan pengambilan keputusan manajerial.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Bapak/Ibu Penguji yang saya hormati, penelitian ini dilatarbelakangi oleh permasalahan nyata yang dihadapi oleh industri manufaktur saat melakukan audit fisik persediaan atau stock opname.*
> 
> *Pertama, pencatatan konvensional menggunakan kertas atau Excel manual untuk spesifikasi material baja yang rumit—seperti ketebalan, panjang, lebar, dan nomor lot—sangat lambat dan rentan akan kesalahan manusia (human error).*
> 
> *Kedua, dan yang paling fatal adalah **masalah duplikasi data**. Ketika puluhan petugas mencatat ribuan barang di berbagai rak secara bersamaan, sering kali barang yang sudah dicatat oleh Petugas A, dicatat ulang oleh Petugas B. Tanpa sistem real-time, kesalahan ganda ini baru diketahui berhari-hari kemudian di kantor.*
> 
> *Dan ketiga, adanya kesenjangan atau delay waktu yang signifikan antara proses pemindaian fisik di lapangan (Scan) hingga menjadi laporan resmi di kantor (Office). Inilah yang menjadi dasar lahirnya konsep **Scan To Office (STO)** yang akan saya kembangkan."*

---

## SLIDE 3: Rumusan Masalah & Tujuan Penelitian

### 🖥️ Konten Slide (Tampilan di Layar):
* **❓ Rumusan Masalah**:
  1. Bagaimana merancang dan membangun sistem *Scan To Office* (STO) berbasis web yang mampu mempercepat pencatatan stock opname material menggunakan pemindaian barcode/QR?
  2. Bagaimana menerapkan alur kerja terpisah (*Admin* vs. *Scanner*) dan algoritma validasi duplikasi (*real-time duplicate checking*) untuk mencegah salah catat?
  3. Bagaimana mengotomatisasi rekapitulasi data fisik lapangan menjadi laporan inventaris yang cepat dan akurat?
* **🎯 Tujuan Penelitian**:
  1. **Membangun sistem STO berbasis web** (*Laravel 12 & Bootstrap 5*) yang responsif untuk perangkat bergerak maupun desktop.
  2. **Mengeliminasi duplikasi & *human error*** melalui mekanisme *Check Letter Verification*, *Duplicate Warning*, & *Material Double Validation*.
  3. **Mempercepat siklus pelaporan** dari hitungan hari menjadi seketika (*real-time synchronization*).

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Berdasarkan latar belakang tersebut, rumusan masalah dalam penelitian ini difokuskan pada tiga hal: bagaimana merancang sistem STO berbasis barcode, bagaimana menerapkan alur kerja multi-role beserta deteksi duplikasi real-time, dan bagaimana mengotomatisasi rekapitulasi laporannya.*
> 
> *Adapun tujuan utama dari penelitian ini adalah menghasilkan sistem **adasiSTO** berbasis web menggunakan framework modern Laravel 12, yang terbukti mampu mengeliminasi duplikasi pencatatan, mengurangi angka human error mendekati nol, serta memangkas waktu proses pelaporan dari yang sebelumnya memakan waktu berhari-hari menjadi real-time seketika saat petugas menembakkan gun scanner di lapangan."*

---

## SLIDE 4: Batasan Masalah (*Research Scope*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **🚧 Koridor & Batasan Pengembangan Sistem**:
  1. **Platform & Framework**: Aplikasi dikembangkan berbasis Web Responsif menggunakan **Laravel 12 (PHP 8.2+)** dan **Bootstrap 5**, dengan database relasional (MySQL/MariaDB).
  2. **Format Barcode Industri**: Sistem dipersiapkan khusus untuk memparsing struktur barcode material manufaktur standar:
     `{ShapeCode}{MaterialCode}{Primary:3 digit}-{Secondary:8 digit}{CheckLetter} | {LotNumber} | {Qty}`
  3. **Role & Hak Akses (RBAC)**: Dibagi menjadi 2 peran utama:
     - **Role Scanner**: Petugas operasional lapangan (Setup sesi, scan barcode, request QR darurat).
     - **Role Admin**: Pengawas kantor (Monitoring KPI, verifikasi rak, validasi duplikat, dan export laporan).
  4. **Ekstensi & Integrasi**: Integrasi dengan sistem ERP eksternal (seperti SAP/Oracle) dibatasi pada pertukaran data melalui *Batch Export (Excel/PDF)* dan impor master data.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Untuk menjaga agar penelitian ini terarah dan mendalam, saya menetapkan empat batasan masalah utama.*
> 
> *Pertama, sistem dikembangkan berbasis web menggunakan arsitektur Laravel 12 dan Bootstrap 5 agar ringan dan dapat diakses melalui browser pada perangkat mobile petugas lapangan maupun komputer kantor.*
> 
> *Kedua, mesin parser barcode yang dibangun dikhususkan untuk memproses format barcode industri berstandar ketat yang memuat kode bentuk (Flat/Round), dimensi milimeter, nomor lot, dan kuantitas dalam satu string QR tunggal.*
> 
> *Ketiga, sistem membatasi hak akses secara tegas pada dua peran: **Scanner** untuk eksekusi lapangan, dan **Admin** untuk validasi serta kontrol manajemen kantor.*
> *Dan keempat, integrasi data ke ERP perusahaan dilakukan melalui mekanisme asynchronous queue export Excel dan PDF berkinerja tinggi."*

---

## SLIDE 5: Landasan Teori & Teknologi Pendukung

### 🖥️ Konten Slide (Tampilan di Layar):
* **📚 Konsep Kunci & Tech Stack adasiSTO** (Layout Grid 4 Kotak):
  - **1. Konsep *Stock Opname (STO) & Scan To Office***
    Audit verifikasi fisik berkala yang menjembatani kenyataan stok di gudang (*Field/Scan*) dengan catatan administratif di kantor (*Office/System*).
  - **2. Laravel 12 Architecture & Security**
    Menggunakan *Role-Based Access Control (RBAC)*, *Database Transactions* untuk menjamin integritas data, serta *Rate Limiting* (`throttle:scan-write` & `throttle:datatable`) untuk proteksi API dari *flood request*.
  - **3. Server-Side DataTables (`yajra/laravel-datatables-oracle`)**
    Pengolahan dan pagination puluhan ribu baris data inventory secara langsung di level *database query*, mencegah *browser freeze* saat rekapitulasi massal.
  - **4. Barcode/QR Regex Parsing & SweetAlert2 UI**
    Sistem pemecahan string teks barcode otomatis (*BarcodeParserService*) dipadukan dengan konfirmasi aksi & toast notification (*SweetAlert2*) yang interaktif.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Secara teoritis, penelitian ini bertumpu pada konsep **Stock Opname dan Scan To Office (STO)**, di mana kecepatan dan keakuratan perpindahan data dari lantai pabrik ke meja manajemen menjadi indikator utama keberhasilan.*
> 
> *Dari sisi teknologi pendukung, saya menggunakan **Laravel 12** yang dilengkapi dengan proteksi Rate Limiting dan middleware Role-Based Access Control.*
> 
> *Untuk menangani big data inventaris yang bisa mencapai puluhan ribu baris saat audit tahunan, saya menerapkan **Yajra Server-Side DataTables** sehingga tabel di browser tetap sangat cepat dan ringan.*
> *Selain itu, saya merancang **BarcodeParserService** berbasis regular expression (Regex) yang secara otomatis membedah string barcode mentah menjadi spesifikasi dimensi material yang terstruktur."*

---

## SLIDE 6: Metodologi Penelitian & Pengembangan (*Agile SDLC*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **🔄 Siklus Pengembangan Agile / SDLC** (Diagram Alur 4 Tahap Iteratif):
  1. **🎯 Requirement & Backlog Analysis**
     - Analisis kebutuhan pengguna di lapangan (petugas butuh setup cepat, penambahan lokasi rak dinamis/*on-the-fly*, dan request QR darurat untuk barang rusak label).
  2. **📐 System & Database Architecture Design**
     - Perancangan skema relasional (*Master STO, Plant, Master Material, Locations, Scan Results, Activity Logs*) dan alur validasi ganda.
  3. **⚡ Iterative Development Sprint**
     - *Sprint 1*: Fondasi & Request Barcode Baru (`BarcodeRequestController`).
     - *Sprint 2*: Generate Barcode & Cetak Label Massal (`GenerateBarcodeController`).
     - *Sprint 3*: Konfirmasi Rak Fisik (`RackConfirmationController`).
     - *Sprint 4*: Dashboard KPI Real-time & Validasi Material Double (`MaterialDoubleController`).
  4. **🧪 Testing & Evaluation**
     - *Black Box Testing*, *Round-Trip Barcode Accuracy Test*, *Stress Test DataTables*, dan *User Acceptance Testing (UAT)*.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Dalam pengembangan sistem ini, saya menerapkan metodologi **Agile SDLC** melalui pendekatan iteratif berkesinambungan yang dibagi menjadi beberapa tahap atau Sprint.*
> 
> *Tahap pertama adalah analisis kebutuhan, di mana saya mengidentifikasi kondisi nyata di lapangan—seperti kebutuhan petugas untuk menambah lokasi rak secara dinamis atau mengajukan cetak ulang QR langsung dari HP saat menemukan barang tanpa label.*
> 
> *Tahap kedua adalah perancangan arsitektur database relasional serta sistem audit log menyeluruh.*
> *Tahap ketiga adalah iterasi coding yang saya bagi menjadi 4 mission utama: mulai dari fitur request QR, generate label A4, konfirmasi rak, hingga dashboard monitoring.*
> *Dan tahap keempat adalah pengujian ketat untuk memastikan seluruh alur bebas bug sebelum diuji coba oleh pengguna akhir."*

---

## SLIDE 7: Arsitektur Sistem & Alur Kerja Multi-Role (*Solusi adasiSTO*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **📊 Alur Kerja Terintegrasi Lapangan vs. Kantor** (Layout 2 Kolom Komparasi):

| 🧑‍🔧 ROLE SCANNER (Petugas Lapangan / Gudang) | 👨‍💼 ROLE ADMIN (Pengawas Kantor / Validator) |
| :--- | :--- |
| **1. Setup Sesi Operasional (`/scan/setup`)**<br>• Pilih Periode STO Aktif, Plant, & Lokasi Rak.<br>• Bisa buat Rak baru *on-the-fly* jika belum ada. | **1. Live Dashboard KPI (`/admin/dashboard`)**<br>• Monitoring metrik real-time dengan *polling 30 detik* (`data-card`).<br>• Pantau live feed scan terbaru (`latestScanData`). |
| **2. Pemindaian Gun Scanner (`/scan/scanner`)**<br>• Tembak barcode -> Ekstraksi otomatis atribut material.<br>• **Peringatan Duplikasi Seketika (`check-duplicate`)**: Blokir & beri peringatan jika barang sudah discan! | **2. Konfirmasi Rak Fisik (`/admin/rack-confirmation`)**<br>• Admin keliling memeriksa rak yang sudah selesai discan petugas.<br>• Kunci rak (`confirm`) atau batalkan (`cancel`) jika ada selisih. |
| **3. Request QR Darurat (`/scan/barcode-request`)**<br>• Ajukan pembuatan QR baru jika material fisik tidak memiliki label/rusak. | **3. Validasi Material Double (`/admin/material-double`)**<br>• Bedah konflik scan ganda -> Setujui data yang benar, tiban dengan scan baru (`scan`), atau hapus (`deleteSelected`). |

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Slide ini memperlihatkan jantung dari inovasi **adasiSTO**, yaitu pembagian peran dan alur kerja yang sangat sinkron antara petugas lapangan (Scanner) dan pengawas kantor (Admin).*
> 
> *Di sisi kiri, **Petugas Scanner** memulai kerja dengan memilih sesi STO dan Rak. Saat mereka menembakkan gun scanner ke material baja, sistem tidak hanya menyimpan data, tetapi langsung memparsing spesifikasi dan melakukan **Check Duplicate Real-Time**. Jika barcode tersebut sudah pernah discan di rak lain 5 menit yang lalu, alarm peringatan langsung muncul di layar scanner, mencegah double scan saat itu juga!*
> 
> *Di sisi kanan, **Admin Kantor** melihat pergerakan data melalui Live Dashboard yang me-refresh otomatis setiap 30 detik. Admin juga memiliki tugas **Konfirmasi Rak**, yaitu mengunci rak fisik yang sudah diverifikasi, serta fitur **Material Double Validation** untuk menyelesaikan konflik jika ada dua petugas yang bersikeras mencatat barang yang sama."*

---

## SLIDE 8: Keunggulan Inovasi & Fitur Kunci (*Why adasiSTO Stand Out*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **💡 4 Pilar Keunggulan Sistem adasiSTO**:
  1. **🔍 Automated Barcode Parsing Engine (`BarcodeParserService`)**
     Mampu membedah kode kompleks berstruktur ketat (Shape `RF/RR`, Kode Material 2 karakter, Primary/Thickness 3 digit, Secondary/Dimensions 8 digit, Check Letter `B`, Lot Number, & Qty) secara akurat dalam hitungan milidetik.
  2. **🛡️ Real-Time Duplicate Prevention & Conflict Handling**
     Perpaduan validasi front-end *Check Duplicate* dan *Rate Limiting API* (`throttle:scan-write`), didukung modul khusus `MaterialDoubleController` untuk resolusi audit data ganda.
  3. **📜 Complete Audit Trail (`ActivityLogService`)**
     Setiap aksi tulis (*create, update, confirm, cancel, generate, reject*) otomatis mencatat siapa pelaku (`user_id`), jenis aksi (`subject_type`), nilai sebelum (`old_values`), dan nilai sesudah (`new_values`).
  4. **⚡ High-Performance Asynchronous Export & Batch Label Printing**
     Dukungan *Queue Export* (Excel/PDF via latar belakang) untuk big data >50.000 baris, serta cetak label grid A4 massal (`batchPrintA4Grid`) untuk efisiensi operasional.

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Ada empat keunggulan inovatif yang membuat sistem adasiSTO unggul dibandingkan sistem pencatatan inventaris standar.*
> 
> *Pertama adalah mesin parser otomatisnya yang mampu menerjemahkan 15 hingga 20 digit kode alfanumerik menjadi spesifikasi ketebalan milimeter, panjang, dan diameter material dalam sekejap mata.*
> 
> *Kedua adalah sistem pertahanan duplikasi berlapis yang tidak hanya menolak data ganda, tetapi menyediakan alur investigasi khusus bagi Admin untuk menentukan data mana yang sah.*
> 
> *Ketiga, akuntabilitas mutlak. Setiap klik, setiap perubahan, dan setiap pembatalan scan dicatat oleh **ActivityLogService** lengkap dengan data sebelum dan sesudahnya, sehingga jejak digital audit terjamin 100%.*
> *Dan keempat, sistem didesain siap menangani skala enterprise dengan fitur background job queue export, sehingga server tidak akan pernah hang saat mengunduh puluhan ribu riwayat stock opname."*

---

## SLIDE 9: Rencana Pengujian & Evaluasi Sistem

### 🖥️ Konten Slide (Tampilan di Layar):
* **📋 Matriks Rencana Pengujian Sistem**:

| Metode Pengujian | Fokus Parameter Pengujian | Target Keberhasilan (*Acceptance Criteria*) |
| :--- | :--- | :--- |
| **1. Black Box Testing** | Validasi fungsionalitas seluruh route & fitur (Login RBAC, Setup Rak, Scan Gun, Request QR, Confirm Rak, Double Validation). | **100% Berhasil** — Semua tombol, form input, dan middleware role berjalan tanpa error. |
| **2. Round-Trip Accuracy Test** | Pengujian siklus *Generate Barcode Label* -> *Cetak A4* -> *Scan dengan Gun/Kamera* -> *Parsing Ekstraksi*. | **100% Presisi** — Nilai atribut material hasil scan persis sama dengan master data asal. |
| **3. Stress & Performance Test** | Uji beban *Server-Side DataTables* (50.000+ row) & konkurensi API (`throttle:scan-write` & `throttle:datatable`). | Pemuatan tabel **< 1.5 detik** & sistem mampu menolak *flood request* tanpa *crash*. |
| **4. User Acceptance Testing (UAT)** | Uji coba langsung bersama petugas operator gudang dan admin supervisor inventory manufaktur. | **Nilai Kepuasan > 85%** & terbukti memangkas waktu pelaporan STO minimal **50%**. |

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Untuk menjamin kehandalan sistem sebelum diterapkan di lingkungan manufaktur nyata, saya telah merancang empat tahapan pengujian komprehensif.*
> 
> *Pertama, Black Box Testing untuk menguji fungsionalitas setiap menu dan proteksi role akses.*
> *Kedua, **Round-Trip Accuracy Testing**, yaitu menguji siklus lengkap dari pembuatan label barcode di sistem Admin, dicetak ke kertas A4, lalu discan kembali di lapangan. Hasil ekstraksi harus 100% presisi tanpa ada pergeseran satu milimeter pun.*
> 
> *Ketiga, Performance dan Stress Testing untuk memastikan tabel DataTables tetap dimuat di bawah 1,5 detik meskipun berisi lebih dari 50.000 baris riwayat scan, serta menguji ketahanan sistem terhadap serangan atau request serentak.*
> *Dan keempat, User Acceptance Testing bersama para operator gudang guna mengukur secara kuantitatif berapa besar peningkatan efisiensi waktu yang berhasil dicapai."*

---

## SLIDE 10: Kesimpulan & Rencana Jadwal Pelaksanaan (*Closing*)

### 🖥️ Konten Slide (Tampilan di Layar):
* **📌 Kesimpulan Proposal**:
  Sistem **adasiSTO** (*Scan To Office*) merupakan solusi digitalisasi integratif yang mentransformasi stock opname manufaktur dari proses manual yang lambat dan rentan duplikasi menjadi alur kerja otomatis, seketika (*real-time*), dan akuntabel melalui pembagian peran *Scanner* dan *Admin* yang terstruktur.
* **📅 Jadwal Pelaksanaan Tugas Akhir (Timeline)**:
  - **Bulan Ke-1**: Studi Literatur, Observasi Lapangan, & Desain Arsitektur Basis Data.
  - **Bulan Ke-2**: Implementasi Modul Core, Setup Sesi, & *Gun Scanner Workflow*.
  - **Bulan Ke-3**: Implementasi Modul Admin, Dashboard KPI Real-Time, & *Material Double Validation*.
  - **Bulan Ke-4**: Pengujian (Black Box, Stress Test, UAT), Evaluasi Akhir, & Penyusunan Laporan TA.

*(Sesi Tanya Jawab / Q&A — Terima Kasih)*

### 🎙️ Naskah Bicara (*Speaker Notes*):
> *"Sebagai kesimpulan penutup dari seminar proposal ini, perancangan dan implementasi sistem **adasiSTO** diharapkan mampu menjadi rujukan solusi digitalisasi inventaris manufaktur modern yang mengedepankan kecepatan eksekusi lapangan, akurasi data tanpa duplikasi, dan visibilitas real-time bagi manajemen.*
> 
> *Adapun estimasi waktu penyelesaian Tugas Akhir ini direncanakan selama 4 bulan, mulai dari analisis spesifikasi hingga pengujian akhir UAT.*
> 
> *Demikian presentasi Seminar Proposal Tugas Akhir saya. Besar harapan saya untuk mendapatkan saran, masukan, dan arahan yang konstruktif dari Bapak/Ibu Dewan Penguji demi kesempurnaan penelitian ini.*
> *Terima kasih atas perhatiannya. Waktu dan tempat saya kembalikan kepada Bapak/Ibu Moderator."*

---
*Dokumen materi PowerPoint & Naskah Bicara ini disusun khusus berdasarkan spesifikasi dan arsitektur nyata repository **adasiSTO (Fastware STO Scan To Office)**.*
