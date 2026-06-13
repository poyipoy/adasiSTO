DESIGN.md — STO (Scan To Office)
1. Purpose
Dokumen ini menjadi referensi utama UI/UX dan visual design untuk sistem STO (Scan To Office).
Tujuan utama:
•	Menyeragamkan tampilan seluruh modul.
•	Menjadi acuan AI Agent saat membuat UI.
•	Menjaga konsistensi antar halaman.
•	Menghindari asumsi desain dari AI maupun developer.
________________________________________
2. Document Priority
Jika terjadi konflik antar dokumen:
Priority:
1. AGENTS.md
2. DESIGN.md
3. Developer Preference
AGENTS.md mengatur:
•	Business Logic
•	Database Rules
•	Barcode Rules
•	Permission Rules
DESIGN.md mengatur:
•	Layout
•	Visual Design
•	User Experience
•	Responsive Behavior
________________________________________
3. Design Philosophy
Arah desain mengikuti ERP Infor System.
Karakter utama:
•	Enterprise
•	Clean
•	Compact
•	Data-Oriented
•	Professional
•	Fast Navigation
Bukan:
•	Landing Page Style
•	Modern SaaS Style
•	Card Heavy Layout
Fokus utama:
Data
Filter
Monitoring
Efficiency
________________________________________
4. Color System
:root {
  --topbar-bg: #1d252c;
  --sidebar-bg: #2f3640;
  --sidebar-hover: #3d4654;
  --sidebar-active: rgba(0,114,206,0.15);

  --workspace-bg: #f0f0f0;
  --surface: #ffffff;

  --primary: #0072ce;
  --primary-dark: #005fa8;

  --success: #22a06b;
  --warning: #e5a100;
  --danger: #d92d20;

  --text: #252a31;
  --text-secondary: #525e6c;
  --text-muted: #808b99;

  --border: #bfc4ce;
  --border-light: #e0e3e8;
}
________________________________________
5. Typography
font-family:
"Inter",
"Segoe UI",
Arial,
sans-serif;
Size:
Element	Size
Body	13px
Table Header	12px
Table Data	13px
Sidebar	14px
Button	13px
Page Title	16px
________________________________________
6. Application Layout
Desktop Layout:
+------------------------------------------------------+
| Topbar                                               |
+------------------+-----------------------------------+
| Sidebar          | Page Tabs                         |
|                  +-----------------------------------+
|                  | Toolbar                           |
|                  +-----------------------------------+
|                  | Filter Section                    |
|                  +-----------------------------------+
|                  | Content Area                      |
|                  +-----------------------------------+
|                  | Pagination / Footer               |
+------------------+-----------------------------------+
________________________________________
7. Topbar
Height:
40px - 48px
Content:
[Logo]
STO

Current STO : STO2607

Company : 0520

[Notification]
[Help]
[User]
Rule:
Current STO harus selalu terlihat.
________________________________________
8. Sidebar
Admin Menu
Dashboard
 └ Overview

STO Result
 ├ All Scan Results
 └ Material Summary

Master Data
 ├ Master STO
 ├ Master Plant
 ├ Master Location
 ├ Master Material
 ├ Master Keterangan
 └ User Management
________________________________________
User Menu
Scan Material
 ├ Setup STO
 ├ Scanner
 └ Scan History
________________________________________
9. Login Page
Layout:
+--------------------------------+
|         Logo ADASI            |
|                                |
| Username                       |
| Password                       |
|                                |
| [ Sign In ]                    |
|                                |
| Contact IT Support             |
+--------------------------------+
Style:
•	Centered Card
•	White Surface
•	Dark Background
•	Blue Primary Button
________________________________________
10. User Flow
Login
↓
Setup STO
↓
Scanner
↓
Scan History
↓
Logout
________________________________________
11. Setup STO Page
Purpose:
Menentukan konteks scan sebelum proses scan dimulai.
________________________________________
Layout
PIC
STO Code
Plant
Location

[ Start Scan ]
________________________________________
Fields
PIC
Readonly
Auto dari user login.
Contoh:
Bahrialgi Fadillah
________________________________________
STO Code
Readonly
Auto dari STO aktif.
Contoh:
STO2607
User tidak dapat mengubah.
________________________________________
Plant
Dropdown.
Contoh:
Cikarang
Deltamas
Surabaya
________________________________________
Location
Dropdown.
Berdasarkan Plant.
Contoh:
CT01
CT02
CT03
________________________________________
Validation
Jika tidak ada STO aktif:
Tidak ada STO aktif yang tersedia.
Silakan hubungi Admin.
User tidak boleh scan.
________________________________________
12. Scanner Page
Purpose:
Melakukan scan material.
________________________________________
Scanner Input
Harus mendukung:
•	Camera Scanner
•	Scanner Gun
•	Keyboard Input
________________________________________
QR Structure
RF1H059-00960099B | ST2605 | 1
Parsing:
Field	Value
Barcode Material	RF1H059-00960099B
Lot Number	ST2605
Qty	1
________________________________________
Parsing Result Panel
Barcode Material
Material Name
Shape
Thickness
Width
Diameter
Length
Lot Number
Qty
Plant
Location
STO Code
________________________________________
Actions
[ Save Scan ]
________________________________________
13. Duplicate Warning
Jika barcode pernah discan:
┌─────────────────────────┐
│ Warning                 │
├─────────────────────────┤
│ Barcode sudah pernah    │
│ discan sebelumnya.      │
│                         │
│ Tetap simpan?           │
│                         │
│ [Ya] [Batal]            │
└─────────────────────────┘
Jika Ya:
Simpan sebagai row baru.
________________________________________
14. Scan Status
Valid
Hijau
background:#dcfce7;
color:#166534;
________________________________________
Duplicate
Kuning
background:#fef3c7;
color:#92400e;
________________________________________
Invalid
Merah
background:#fee2e2;
color:#991b1b;
________________________________________
Pending
Abu-abu
________________________________________
15. Recent Scan Section
User hanya melihat data miliknya.
Kolom:
No
Barcode
Material
Lot
Qty
Status
Time
Sorting:
ORDER BY created_at DESC
________________________________________
16. Empty State
User:
Belum ada hasil scan.
Silakan lakukan scan pertama.
Admin:
Tidak ada data ditemukan.
________________________________________
17. Dashboard Admin
Cards
•	Total Scan Today
•	Total Scan Month
•	Total Valid Material
•	Total Duplicate
•	Total Invalid
________________________________________
Charts
•	Scan per User
•	Scan per Plant
•	Scan per Day
•	Top Material
________________________________________
Latest Scan
Menampilkan scan terbaru.
________________________________________
18. All Scan Results
Komponen utama sistem.
________________________________________
Toolbar
Refresh
Export Excel
Export PDF
Search
Column Settings
Actions
________________________________________
Filter
STO Code
Plant
Location
User
Material
Lot Number
Date From
Date To
________________________________________
Table Columns
No
Barcode
Material
Shape
T
W
D
L
Lot
Qty
User
Plant
Location
STO
Time
Status
Action
________________________________________
Inline Edit
Menggunakan DataTables Child Row.
Tidak membuka halaman baru.
________________________________________
19. Master STO
Columns:
STO Code
Description
Start Date
End Date
Status
________________________________________
Rules
Hanya boleh ada satu STO aktif.
Jika STO baru aktif:
STO Lama = Inactive
STO Baru = Active
Otomatis.
________________________________________
20. Master Plant
CRUD.
Contoh:
Cikarang
Deltamas
Surabaya
________________________________________
21. Master Location
Relasi ke Plant.
Contoh:
Plant: Cikarang

CT01
CT02
CT03
________________________________________
22. Master Material
Columns:
Material Code
Material Name
Is Active
Contoh:
1H
SKD11
________________________________________
23. Master Keterangan
Options:
OK
Lot Salah
Size Salah
Material Salah
________________________________________
24. Modal Design
Enterprise Style.
Compact.
No oversized modal.
________________________________________
25. Loading Behavior
Saat scan:
Loading
↓
Parsing
↓
Validation
↓
Save
↓
Success Message
↓
Refresh Recent Scan
Target:
< 1 second
________________________________________
26. Responsive Rules
Desktop
•	Sidebar Expanded
•	Full Table
•	Full Toolbar
________________________________________
Tablet
•	Sidebar Collapsible
•	Horizontal Scroll
________________________________________
Mobile
•	Focus Scan
•	Focus Result
•	Focus Recent Scan
Tidak menggunakan tabel besar.
________________________________________
27. Performance Rules
Admin pages wajib:
•	DataTables Server Side
•	Pagination
•	Search
•	Column Filter
•	Export
Dilarang:
Model::all();
untuk halaman data utama.
________________________________________
28. Recommended Components
Topbar
Sidebar
PageTabs
EnterpriseToolbar
EnterpriseTable
ColumnFilter
StatusBadge
ConfirmModal
ScanResultCard
MobileScannerPanel
________________________________________
29. Final UX Principles
1.	Data terbaru selalu di atas.
2.	Scan maksimal 1-2 langkah.
3.	User hanya melihat datanya sendiri.
4.	Admin dapat filter dengan cepat.
5.	Semua error harus jelas.
6.	Layout harus compact.
7.	Tabel adalah komponen utama.
8.	Tidak menggunakan desain landing page.
9.	Semua halaman admin menggunakan server-side processing.
10.	Tetap konsisten dengan referensi ERP Infor.
