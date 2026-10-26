# DATABASE.md - STO Scan To Office

Dokumen ini adalah kontrak database canonical untuk implementasi aktif. Database target adalah MySQL dan migration Laravel menjadi source of truth perubahan skema.

## 1. Prinsip Umum

- Primary key menggunakan unsigned bigint `id`.
- Foreign key operasional memakai index dan aturan delete eksplisit.
- `scan_results` memakai hard delete. Riwayat delete dipertahankan sebagai snapshot append-only.
- Data scan aktif dibatasi oleh `sto_code_id` Active STO melalui global scope model.
- Migration baru harus additive dan aman untuk fresh database maupun deployment berisi data.

## 2. Tabel Inti

### 2.1 `users`

| Kolom | Aturan |
|---|---|
| `name` | Nama tampilan |
| `username` | Kredensial unik |
| `password` | Hash Laravel |
| `role` | `admin` atau `scanner` |
| `is_validator` | Capability validator untuk scanner; admin selalu dianggap validator oleh model |
| `is_active` | User nonaktif tidak dapat login |

Tidak ada role database `validator`.

### 2.2 `sto_codes`

Kolom: `code`, `description`, `start_date`, `end_date`, `is_active`, timestamps. Hanya satu STO boleh aktif. Aktivasi dilakukan melalui `ActiveStoService` dalam transaction.

### 2.3 `plants`

Kolom utama: `name`, `is_active`, timestamps.

### 2.4 `locations`

| Kolom | Aturan |
|---|---|
| `plant_id` | FK Plant |
| `name` | Nama Location/Rack |
| `old_location_name` | Nullable |
| `description` | Nullable, maksimum 255 |
| `warehouse` | Nullable |
| `is_active` | Status master |
| `created_by_user_id` | Actor pembuat, nullable untuk data legacy |

Unique key canonical adalah `(plant_id, name)`. Field legacy `is_confirmed`, `confirmed_by_user_id`, `confirmed_at`, dan `confirmation_note` sementara dipertahankan untuk kompatibilitas; source of truth workflow baru adalah `rack_confirmations`.

Penghapusan Location wajib melalui `LocationService`. Location tidak boleh dihapus jika sudah dipakai oleh scan pada STO mana pun, Request Barcode, atau workflow Rack Confirmation yang telah diproses/memiliki history. Row `rack_confirmations` berstatus `pending` tanpa actor, timestamp, note, dan history hanya merupakan placeholder dan boleh dibersihkan bersama Location. Untuk data yang sudah dipakai, gunakan `is_active=false` agar referensi dan audit tetap utuh.

### 2.5 `master_materials`

Kolom utama: `material_code`, `material_name`, `is_active`, timestamps. Parser/generator hanya menerima material aktif.

### 2.6 `master_keterangan`

Kolom utama: `name`, `is_active`, timestamps. Default scan berasal dari `config('sto.default_keterangan')`.

## 3. Scan dan Audit

### 3.1 `scan_results`

Kolom utama:

```text
id
user_id
sto_code_id
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
```

Index penting mencakup foreign key, `sto_code`, `barcode_material`, `material_code`, `shape_code`, `created_at`, `(user_id, created_at)`, dan `(sto_code, barcode_material)`.

`shape_name` data baru disimpan uppercase. Data lama dinormalisasi saat display melalui `ShapeFormatter`.

### 3.2 `scan_result_logs`

Audit field/snapshot untuk action `created`, `updated`, dan `deleted`:

```text
scan_result_id
user_id
action
field_name
old_value
new_value
created_at
```

Log harus tetap tersedia setelah hard delete `scan_results`.

### 3.3 `activity_logs`

Audit aktivitas lintas modul dengan `user_id`, action, subject polymorphic, old/new values, metadata, IP, user agent, dan timestamps. Operasi integritas tinggi memakai `ActivityLogService::recordOrFail()`.

### 3.4 `barcode_delete_histories`

Snapshot append-only sebelum hard delete scan. Tabel menyimpan:

- ID scan lama dan STO.
- Actor delete dan owner scan asli, termasuk snapshot nama.
- Plant/Location beserta snapshot nama.
- QR raw, barcode material, material, shape, dimensi, lot, qty, keterangan, dan source scan.
- `deletion_source`: `scan_history`, `admin_scan_results`, atau `material_double`.
- `deletion_reason`, JSON `snapshot`, waktu scan asli, dan waktu delete.

Foreign key snapshot nullable memakai `nullOnDelete`; nilai nama dan JSON snapshot menjaga histori saat master/user dihapus. Model menolak update dan delete.

## 4. Barcode Request dan Print

### 4.1 `barcode_requests`

Kolom domain utama:

```text
user_id
sto_code_id
plant_id
location_id
material_code
material_name
shape_code
shape_name
thickness
width
diameter
length
lot_number
qty
print_count
status
generated_barcode_material
reviewed_by_user_id
reviewed_at
printed_by_user_id
printed_at
created_at
updated_at
```

Aturan:

- `qty` non-null default `1` dan selalu `1` untuk barcode hasil generate.
- `print_count` non-null default `1`; tidak masuk payload QR.
- State aktif: `pending`, `approved`, `printed`.
- `rejected` dan `rejection_reason` dipertahankan untuk data legacy.
- Index `(status, printed_at)` mendukung monitoring print.

Riwayat setiap print/reprint disimpan di `activity_logs`, bukan tabel print history terpisah.

## 5. Material Double

### 5.1 `material_double_validations`

Status valid per group Active STO dengan unique key:

```text
(sto_code_id, barcode_material, plant_id, location_id)
```

Kolom actor/waktu: `validated_by`, `validated_at`.

Delete Selected tidak membuat row validasi. Jika group sebelumnya valid lalu dilakukan partial delete, row validasi dihapus agar status kembali `Need Check`.

Group duplicate dibentuk dari `scan_results` dengan `COUNT(*) > 1`, dikelompokkan juga berdasarkan shape/dimensi agar data berbeda tidak tercampur.

## 6. Rack Confirmation

### 6.1 `rack_confirmations`

Satu row per `(sto_code_id, location_id)` dengan unique key. Kolom:

```text
status
finished_by_user_id
finished_at
validated_by_user_id
validated_at
confirmed_by_user_id
confirmed_at
note
timestamps
```

State: `pending`, `finished`, `validated`, `confirmed`.

### 6.2 `rack_confirmation_histories`

Append-only transition history:

```text
rack_confirmation_id
sto_code_id
location_id
from_status
to_status
action_by_user_id
note
metadata
created_at
```

Semua transition dan cancel menambah history. Model menolak update/delete.

## 7. Export Runtime

Tabel/job export lama dapat tetap tersedia sebagai infrastructure, tetapi endpoint aktif membuat Excel/PDF/CSV secara synchronous. Tidak ada queue worker pada kontrak runtime saat ini.

## 8. Transaction dan Locking

- Generate/batch generate mengunci `barcode_requests` sebelum state change.
- Print mencatat semua request dalam satu transaction setelah PDF berhasil dirender.
- Rack transition memakai row lock per `(STO, Location)`.
- Material Double partial delete mengunci seluruh row group dan berjalan atomic.
- Hard delete scan membuat `barcode_delete_histories`, `scan_result_logs`, strict `activity_logs`, lalu delete dalam satu transaction.

## 9. Deployment Migration

Sebelum production migration:

1. Backup database dan verifikasi restore.
2. Aktifkan maintenance/deployment window.
3. Jalankan `php artisan migrate --force` satu kali.
4. Verifikasi kolom nullable Location, tabel workflow Rack, serta tabel delete history.
5. Jalankan smoke test role dan lifecycle.

Rollback migration Location nullable sengaja tidak mengembalikan `NOT NULL`, karena dapat merusak row production yang sudah valid dengan nilai null.
