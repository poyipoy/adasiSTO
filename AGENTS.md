AGENTS.md — STO (Scan To Office) System
Part 1 — Foundation, Business Rules, Master Data, STO Rules, QR Rules
________________________________________
1. Project Overview
1.1 Project Name
STO (Scan To Office)
________________________________________
1.2 Project Description
STO adalah sistem berbasis web yang digunakan untuk proses Stock Taking Opname material melalui QR Code atau Barcode.
Sistem digunakan untuk:
•	Melakukan scan material.
•	Mencatat hasil stock opname.
•	Memvalidasi material berdasarkan master data.
•	Melakukan monitoring hasil scan.
•	Menyediakan reporting dan export data.
________________________________________
1.3 Target Users
User Scanner
Operator lapangan yang melakukan scan material menggunakan:
•	Mobile Phone
•	Scanner Gun
Admin
Pengguna yang mengelola:
•	Master Data
•	Hasil Scan
•	Monitoring
•	Export
•	Reporting
________________________________________
1.4 Technology Stack
Framework:
Laravel 12
Database:
MySQL
Frontend:
Blade
Bootstrap
jQuery
DataTables
Export:
Laravel Excel
DOMPDF
________________________________________
2. AI Agent Instructions
2.1 Mandatory Reading Order
Sebelum melakukan perubahan apa pun, AI Agent wajib membaca:
1. AGENTS.md
2. docs/DESIGN.md
Urutan tersebut wajib.
________________________________________
2.2 Document Priority
Jika terjadi konflik antar dokumen:
Priority:
1. AGENTS.md
2. DESIGN.md
3. Existing Code
AGENTS.md selalu menjadi sumber kebenaran utama untuk:
•	Business Rules
•	Database Rules
•	Permission Rules
•	Validation Rules
•	Workflow Rules
DESIGN.md menjadi sumber utama untuk:
•	Layout
•	Styling
•	UI Components
•	Responsive Behavior
________________________________________
2.3 AI Agent Restrictions
AI Agent dilarang:
•	Mengubah business rule tanpa instruksi.
•	Menghapus fitur existing tanpa instruksi.
•	Membuat asumsi baru yang tidak tertulis.
•	Mengubah workflow scan.
•	Mengubah struktur QR tanpa instruksi.
Jika requirement tidak jelas:
Stop and ask for clarification.
________________________________________
3. Business Rules
________________________________________
3.1 Data Ownership
User hanya boleh melihat data scan miliknya sendiri.
Contoh:
User A:
100 Scan
User B:
50 Scan
Saat User A login:
Hanya melihat 100 scan miliknya.
Saat User B login:
Hanya melihat 50 scan miliknya.
________________________________________
3.2 Admin Visibility
Admin dapat melihat:
•	Semua scan
•	Semua user
•	Semua plant
•	Semua STO
Tanpa batasan ownership.
________________________________________
3.3 Scan Quantity Rule
Setiap scan memiliki:
Qty = berasal dari QR
Contoh:
RF1H059-00960099B|ST2605|1
Hasil:
Qty = 1
________________________________________
3.4 Duplicate Scan Rule
Barcode yang sama boleh discan ulang.
Namun sistem wajib:
Tampilkan warning duplicate.
Contoh:
Barcode sudah pernah discan sebelumnya.

Tetap simpan?
Button:
Ya
Batal
Jika user memilih:
Ya
maka data tetap disimpan sebagai row baru.
________________________________________
3.5 Data Ordering Rule
Data terbaru wajib muncul paling atas.
Default query:
ORDER BY created_at DESC,
         id DESC
Nomor urut juga harus descending.
Contoh:
100
99
98
97
________________________________________
3.6 Default Scan Status
Saat scan berhasil:
Keterangan = OK
Otomatis.
User tidak dapat mengubah keterangan.
________________________________________
3.7 Keterangan Master
Pilihan keterangan:
OK
Lot Salah
Size Salah
Material Salah
________________________________________
3.8 Edit Keterangan Rule
User:
Tidak boleh edit keterangan.
Admin:
Boleh edit keterangan.
________________________________________
3.9 Delete Scan Rule
User boleh menghapus scan miliknya sendiri.
Rule:
Hard Delete
Tidak menggunakan soft delete.
Admin boleh menghapus scan apa pun.
________________________________________
4. User Roles & Permissions
________________________________________
4.1 Scanner User
Dapat:
•	Login
•	Setup Scan
•	Scan Material
•	Melihat Scan History miliknya
•	Menghapus scan miliknya
Tidak dapat:
•	Mengakses master data
•	Melihat data user lain
•	Mengubah keterangan
•	Mengubah STO aktif
•	Mengubah master material
________________________________________
4.2 Admin
Dapat:
•	Login
•	Melihat seluruh data
•	Edit scan
•	Delete scan
•	Export data
•	Kelola master data
•	Kelola STO aktif
________________________________________
5. Authentication Rules
Login menggunakan:
Username
Password
________________________________________
5.1 PIC Rule
PIC otomatis berasal dari user login.
Contoh:
Username:
BAHRIALGI

PIC:
Bahrialgi Fadillah
PIC tidak dipilih manual.
PIC tidak dapat diubah user.
________________________________________
6. Master Data Rules
Admin mengelola seluruh master data.
________________________________________
6.1 Master STO
Contoh:
STO2606
STO2607
STO2608
________________________________________
6.2 Master Plant
Contoh:
Cikarang
Deltamas
Surabaya
________________________________________
6.3 Master Location
Relasi:
Plant
  └ Location
Contoh:
Cikarang
 ├ CT01
 ├ CT02
 └ CT03

Deltamas
 ├ DM01
 └ DM02
User tidak boleh membuat location baru.
________________________________________
6.4 Master Material
Contoh:
Code	Material
1H	SKD11
2P	SKD61
2L	DHAW
4F	P20
4E	NAK80
Master Material digunakan sebagai validasi QR.
________________________________________
6.5 Master Keterangan
Data:
OK
Lot Salah
Size Salah
Material Salah
________________________________________
6.6 Master User
Berisi:
User Scanner
Admin
________________________________________
7. STO Active Rules
________________________________________
7.1 Active STO Concept
Pada satu waktu hanya boleh ada:
1 STO aktif
Contoh:
STO Code	Status
STO2606	Inactive
STO2607	Active
STO2608	Inactive
________________________________________
7.2 STO Switching
Jika Admin mengaktifkan STO baru:
Contoh:
Sebelum:
STO2607 = Active
Sesudah:
STO2607 = Inactive
STO2608 = Active
Otomatis.
________________________________________
7.3 User Setup
Saat user membuka Setup Scan:
PIC        : Auto
STO Code   : Auto
Plant      : Select
Location   : Select
________________________________________
7.4 STO Visibility
User dapat melihat STO aktif.
User tidak dapat:
•	Mengubah STO
•	Memilih STO
•	Membuat STO
________________________________________
7.5 No Active STO
Jika tidak ada STO aktif:
Tidak ada STO aktif yang tersedia.

Silakan hubungi Admin.
User tidak boleh scan.
________________________________________
8. QR / Barcode Rules
________________________________________
8.1 QR Structure
Format QR:
<barcode_material>|<lot_number>|<qty>
Contoh:
RF1H059-00960099B|ST2605|1
________________________________________
8.2 QR Components
Bagian pertama:
barcode_material
Contoh:
RF1H059-00960099B
Bagian kedua:
lot_number
Contoh:
ST2605
Bagian ketiga:
qty
Contoh:
1
________________________________________
8.3 Important Rule
QR Code:
TIDAK mengandung STO Code
STO Code berasal dari:
Master STO Aktif
________________________________________
9. Barcode Parsing Rules
________________________________________
9.1 Supported Shapes
Saat ini hanya:
RF = Flat
RR = Round
Shape lain dianggap invalid.
________________________________________
9.2 RF Parsing
Contoh:
RF1H059-00960099B
Parsing:
Shape Code    = RF
Material Code = 1H
Thickness     = 59
Width         = 96
Length        = 99
________________________________________
9.3 RR Parsing
Contoh:
RR2P051-00000835B
Parsing:
Shape Code    = RR
Material Code = 2P
Diameter      = 51
Length        = 835
________________________________________
9.4 Material Validation
Setelah parsing:
Material Code
wajib dicari di:
Master Material
Jika tidak ditemukan:
Reject Scan
________________________________________
9.5 Invalid Barcode
Reject jika:
•	Format QR salah.
•	Shape bukan RF atau RR.
•	Material tidak ditemukan.
•	Dimensi tidak valid.
•	Qty kosong.
•	Lot kosong.
Pesan:
Format barcode tidak valid.
________________________________________
9.6 Parser Responsibility
BarcodeParserService bertanggung jawab untuk:
•	Parsing QR
•	Parsing Material
•	Parsing Dimension
•	Material Validation
•	Shape Validation
Controller tidak boleh berisi logic parsing.

10. User Features
10.1 Setup STO
Halaman pertama setelah login.
Tujuan:
Menentukan konteks scan sebelum proses scanning dimulai.
Field:
PIC (Readonly)
STO Code (Readonly)
Plant (Dropdown)
Location (Dropdown)
Rule:
PIC otomatis dari user login.
STO Code otomatis dari STO aktif.
User hanya memilih:
•	Plant
•	Location
________________________________________
10.2 Scanner Module
User dapat:
•	Scan menggunakan kamera.
•	Scan menggunakan scanner gun.
•	Input manual barcode jika diperlukan.
________________________________________
10.3 Scan Preview
Sebelum disimpan, sistem menampilkan hasil parsing.
Contoh:
Barcode Material
Material
Shape
Thickness
Width
Diameter
Length
Lot Number
Qty
________________________________________
10.4 Save Scan
Jika valid:
Simpan ke scan_results
Status otomatis:
OK
________________________________________
10.5 Scan History
User hanya melihat data miliknya.
Filter:
Tanggal Awal
Tanggal Akhir
Sorting:
ORDER BY created_at DESC
________________________________________
10.6 Delete Scan
User dapat menghapus scan miliknya sendiri.
Rule:
Hard Delete
Validasi:
if ($scan->user_id !== auth()->id()) {
    abort(403);
}
________________________________________
11. Admin Features
11.1 Dashboard
Menampilkan:
Total Scan Hari Ini
Total Scan Bulan Ini
Total Valid
Total Duplicate
Total Invalid
________________________________________
11.2 Scan Monitoring
Admin dapat melihat seluruh scan.
Tanpa batas user.
________________________________________
11.3 Scan Management
Admin dapat:
View
Edit
Delete
________________________________________
11.4 Master STO
CRUD.
Field:
STO Code
Description
Start Date
End Date
Is Active
________________________________________
11.5 Master Plant
CRUD.
________________________________________
11.6 Master Location
CRUD.
Relasi ke Plant.
________________________________________
11.7 Master Material
CRUD.
Field minimal:
Material Code
Material Name
Is Active
________________________________________
11.8 Master Keterangan
CRUD.
Default data:
OK
Lot Salah
Size Salah
Material Salah
________________________________________
11.9 User Management
CRUD.
Role:
Admin
Scanner
________________________________________
11.10 Export
Admin dapat:
Export Excel
Export PDF
________________________________________
12. Database Design Rules
________________________________________
12.1 Required Tables
users
sto_codes
plants
locations
master_materials
master_keterangan
scan_results
scan_result_logs
________________________________________
12.2 users
Purpose:
User authentication.
________________________________________
12.3 sto_codes
Purpose:
Master STO.
Columns:
id
code
description
start_date
end_date
is_active
created_at
updated_at
________________________________________
12.4 plants
Columns:
id
name
created_at
updated_at
________________________________________
12.5 locations
Columns:
id
plant_id
code
name
created_at
updated_at
________________________________________
12.6 master_materials
Columns:
id
material_code
material_name
is_active
created_at
updated_at
________________________________________
12.7 master_keterangan
Columns:
id
name
created_at
updated_at
________________________________________
12.8 scan_results
Core table.
Columns:
id

user_id

plant_id

location_id

sto_code

barcode_raw

barcode_material

lot_number

qty

material_code

material_name

shape_code

shape_name

thickness

width

diameter

length

keterangan

scan_source

created_at

updated_at
________________________________________
12.9 scan_result_logs
Audit table.
Columns:
id

scan_result_id

user_id

action

field_name

old_value

new_value

created_at
________________________________________
13. Database Relationship Rules
________________________________________
User → Scan
User
1:N
Scan Result
________________________________________
Plant → Location
Plant
1:N
Location
________________________________________
Plant → Scan
Plant
1:N
Scan Result
________________________________________
Location → Scan
Location
1:N
Scan Result
________________________________________
14. Validation Rules
________________________________________
14.1 Setup Validation
Plant wajib dipilih.
Location wajib dipilih.
STO aktif wajib tersedia.
________________________________________
14.2 QR Validation
QR wajib memiliki:
barcode_material
lot_number
qty
Format:
<barcode_material>|<lot_number>|<qty>
________________________________________
14.3 Material Validation
Material wajib ditemukan di Master Material.
Jika tidak ditemukan:
Reject Scan
________________________________________
14.4 Shape Validation
Shape valid:
RF
RR
Selain itu:
Reject Scan
________________________________________
14.5 Qty Validation
Qty harus:
Integer
Greater Than 0
________________________________________
14.6 Lot Validation
Lot tidak boleh kosong.
________________________________________
15. Audit Trail Rules
________________________________________
15.1 Purpose
Melacak perubahan data penting.
________________________________________
15.2 Audit Actions
created
updated
deleted
________________________________________
15.3 Audit Fields
Audit wajib untuk:
keterangan
barcode
material
plant
location
________________________________________
15.4 User Delete Rule
Walaupun user melakukan hard delete:
Audit tetap dibuat sebelum data dihapus.
Contoh:
Action : deleted
User   : Scanner
________________________________________
16. Export Rules
________________________________________
16.1 Supported Export
Excel
PDF
________________________________________
16.2 Export Scope
Export harus mengikuti filter aktif.
Contoh:
Plant
User
STO
Tanggal
________________________________________
16.3 Export Security
User:
Tidak boleh export seluruh data.
Admin:
Boleh export seluruh data.
________________________________________
17. Performance Rules
________________________________________
17.1 Data Volume
Target:
10.000 - 100.000+
________________________________________
17.2 Mandatory Server Side Processing
Semua tabel admin wajib menggunakan:
DataTables Server Side
________________________________________
17.3 Prohibited
Dilarang:
ScanResult::all();
untuk halaman utama.
________________________________________
17.4 Required Features
Semua tabel besar wajib memiliki:
Pagination
Search
Filter
Sorting
________________________________________
17.5 Export Optimization
Untuk export besar:
Gunakan:
Chunk Processing
Jika data sangat besar:
Queue Export
________________________________________
18. Indexing Rules
Mandatory Index:
INDEX(user_id)

INDEX(plant_id)

INDEX(location_id)

INDEX(material_code)

INDEX(shape_code)

INDEX(sto_code)

INDEX(created_at)

INDEX(barcode_material)

INDEX(user_id, created_at)

INDEX(sto_code, barcode_material)
________________________________________
19. Security Rules
________________________________________
19.1 Ownership Check
User tidak boleh:
•	Edit scan user lain.
•	Delete scan user lain.
•	Melihat scan user lain.
________________________________________
19.2 Mass Assignment
Gunakan:
$fillable
atau DTO.
Jangan gunakan:
$request->all()
langsung.
________________________________________
19.3 Authorization
Gunakan:
Policy
Gate
Middleware
sesuai kebutuhan.
________________________________________
20. API Response Standards
Success:
{
  "success": true,
  "message": "Scan berhasil disimpan."
}
Failed:
{
  "success": false,
  "message": "Format barcode tidak valid."
}
Validation:
{
  "success": false,
  "errors": {}
}

21. UI Integration Rules
________________________________________
21.1 Design Reference
Seluruh implementasi UI wajib mengikuti:
docs/DESIGN.md
DESIGN.md adalah sumber utama untuk:
•	Layout
•	Sidebar
•	Topbar
•	Toolbar
•	Tables
•	Login
•	Dashboard
•	Mobile Scanner
•	Responsive Design
•	Colors
•	Typography
________________________________________
21.2 Business Logic vs UI
Jika terjadi konflik:
AGENTS.md  = Business Logic

DESIGN.md  = UI / UX
AGENTS.md selalu menang untuk:
•	Validation
•	Permission
•	Workflow
•	Database
________________________________________
21.3 UI Consistency Rule
Semua halaman wajib:
•	Menggunakan layout yang sama.
•	Menggunakan warna yang sama.
•	Menggunakan typography yang sama.
•	Menggunakan spacing yang sama.
AI Agent tidak boleh membuat desain baru yang tidak ada pada DESIGN.md.
________________________________________
22. User Interface Rules
________________________________________
22.1 User Scanner UI
Target Device:
Mobile First
Prioritas:
Fast Scan
Fast Feedback
Minimal Click
________________________________________
22.2 User Scanner Restrictions
User tidak boleh melihat:
Master Data
Admin Dashboard
Other User Data
________________________________________
22.3 Recent Scan
Recent Scan hanya berisi:
Data milik user login
________________________________________
22.4 Scan Process
Target:
Scan → Save ≤ 2 langkah
________________________________________
23. Admin Interface Rules
________________________________________
23.1 Admin Layout
Target Device:
Desktop First
________________________________________
23.2 Admin Tables
Semua tabel besar wajib memiliki:
Search
Filter
Sorting
Pagination
Export
________________________________________
23.3 Inline Editing
Gunakan:
DataTables Child Row
Bukan halaman edit terpisah jika tidak diperlukan.
________________________________________
24. Laravel Architecture Rules
________________________________________
24.1 Thin Controller Principle
Controller hanya bertugas:
Receive Request
Validate Request
Call Service
Return Response
Controller tidak boleh berisi business logic panjang.
________________________________________
24.2 Service Layer
Business logic wajib dipisahkan.
Contoh:
BarcodeParserService
ScanService
STOService
ExportService
________________________________________
24.3 Form Request
Semua validasi wajib menggunakan:
Laravel Form Request
Contoh:
StoreScanRequest
UpdateScanRequest
StoreMaterialRequest
________________________________________
24.4 Repository Pattern
Optional.
Tidak wajib.
Gunakan hanya jika kompleksitas meningkat.
________________________________________
24.5 Eloquent First
Prioritas:
Eloquent
Query Builder
Raw SQL
Gunakan Raw SQL hanya jika benar-benar diperlukan.
________________________________________
25. Barcode Parser Service Contract
________________________________________
25.1 Service Location
app/Services/BarcodeParserService.php
________________________________________
25.2 Input
Input:
string $qr
Contoh:
RF1H059-00960099B|ST2605|1
________________________________________
25.3 Success Response
[
    'valid' => true,

    'barcode_material' => 'RF1H059-00960099B',

    'lot_number' => 'ST2605',

    'qty' => 1,

    'shape_code' => 'RF',

    'shape_name' => 'Flat',

    'material_code' => '1H',

    'material_name' => 'SKD11',

    'thickness' => 59,

    'width' => 96,

    'diameter' => null,

    'length' => 99,
]
________________________________________
25.4 Failed Response
[
    'valid' => false,

    'message' => 'Format barcode tidak valid.'
]
________________________________________
26. Scan Service Rules
________________________________________
26.1 Scan Flow
Receive QR
↓
Parse QR
↓
Validate QR
↓
Validate Material
↓
Check Duplicate
↓
Save Scan
↓
Return Response
________________________________________
26.2 Duplicate Flow
Jika barcode ditemukan sebelumnya:
Show Warning
User menentukan:
Continue
Cancel
________________________________________
26.3 Save Scan
Saat save:
Keterangan:
OK
secara otomatis.
________________________________________
27. Coding Standards
________________________________________
27.1 Naming Convention
Model:
ScanResult
Plant
Location
MasterMaterial
Controller:
ScanController
PlantController
MaterialController
Service:
ScanService
BarcodeParserService
________________________________________
27.2 Method Naming
Gunakan:
store()
update()
destroy()
scan()
export()
parse()
________________________________________
27.3 Variable Naming
Gunakan:
$barcodeMaterial
$lotNumber
$materialCode
Bukan:
$bm
$ln
$temp
________________________________________
28. Logging Rules
________________________________________
28.1 Application Error
Gunakan:
Log::error()
untuk error penting.
________________________________________
28.2 Scan Error
Jika parser gagal:
Log::warning()
________________________________________
29. Testing Requirements
________________________________________
29.1 Mandatory Tests
Wajib membuat test untuk:
Barcode Parser
Scan Flow
Duplicate Flow
Validation
________________________________________
29.2 Barcode Tests
Test 1
RF Valid
RF1H059-00960099B|ST2605|1
Expected:
Valid
________________________________________
Test 2
RR Valid
RR2P051-00000835B|ST2605|1
Expected:
Valid
________________________________________
Test 3
Unknown Shape
Expected:
Reject
________________________________________
Test 4
Unknown Material
Expected:
Reject
________________________________________
Test 5
Missing Lot
Expected:
Reject
________________________________________
Test 6
Missing Qty
Expected:
Reject
________________________________________
30. Do Not Do
AI Agent dilarang:
________________________________________
30.1 Business Rules
Jangan:
Mengubah workflow scan
Mengubah format QR
Mengubah STO Rule
________________________________________
30.2 Database
Jangan:
Menghapus relasi penting
Menghapus audit log
________________________________________
30.3 UI
Jangan:
Membuat desain baru
Mengubah layout utama
Mengabaikan DESIGN.md
________________________________________
30.4 Performance
Jangan:
ScanResult::all();
untuk halaman utama.
________________________________________
30.5 Data Ownership
Jangan:
Menampilkan data user lain ke scanner user.
________________________________________
30.6 Material Validation
Jangan:
Melewati validasi Master Material.
________________________________________
30.7 STO Validation
Jangan:
Mengizinkan scan tanpa STO aktif.
________________________________________
31. Development Priority
________________________________________
Priority 1
Core System
Authentication
Master STO
Master Plant
Master Location
Master Material
Master Keterangan
Barcode Parser
Setup STO
Scanner Module
Scan History
________________________________________
Priority 2
Admin Features
Dashboard
Scan Monitoring
Scan Management
Export Excel
Export PDF
Duplicate Warning
________________________________________
Priority 3
Advanced Features
Audit Dashboard
Analytics
Queue Export
Performance Optimization
________________________________________
32. Acceptance Criteria
Sistem dianggap selesai jika:
________________________________________
Scanner User
Dapat:
Login
Setup STO
Scan QR
Melihat Scan History
Menghapus Scan Miliknya
________________________________________
Admin
Dapat:
Kelola STO
Kelola Plant
Kelola Location
Kelola Material
Kelola Keterangan
Kelola User
Monitoring Scan
Export Data
________________________________________
QR Validation
Sistem mampu:
Parse RF
Parse RR
Reject Invalid QR
Reject Unknown Material
________________________________________
Performance
Sistem mampu:
10.000 - 100.000+ Data
dengan:
Server Side Processing
Pagination
Filtering
Export
________________________________________
33. Final Principle
Sistem STO harus selalu mengutamakan:
Accuracy
Performance
Auditability
Simplicity
Enterprise Usability
Prioritas utama:
Scan Cepat
Validasi Akurat
Monitoring Mudah
Performa Tinggi
End of AGENTS.md


