# AUTHORIZATION_MATRIX.md - STO Scan To Office

Validator adalah capability, bukan role. Identitasnya tetap `role=scanner` dan `is_validator=true`. Admin selalu dianggap validator oleh `User::isValidator()`.

## 1. Matriks Halaman dan Data

| Modul | Admin | Scanner | Scanner Validator |
|---|---:|---:|---:|
| Admin Dashboard | Ya | Tidak | Tidak |
| Scanner Overview | Tidak | Ya, data sendiri | Ya, data sendiri + validator overview global |
| Setup STO / Scanner / Scan History | Tidak | Ya | Ya |
| All Scan Results | Ya, seluruh Active STO | Tidak | Tidak |
| Material Summary admin | Ya, global | Tidak | Tidak |
| Material Summary scanner | Tidak | Ya, data sendiri | Ya, global Active STO |
| Material Double | Ya | Tidak | Ya |
| Request QR/Barcode | Tidak | Ya | Ya |
| Generate Barcode | Ya | Tidak | Ya |
| Rack Confirmation | Ya | Ya | Ya |
| Master STO/Plant/Material/Keterangan/Location/User | Ya | Tidak | Tidak |
| History Delete Barcode | Seluruh data | Data yang dilakukan olehnya atau scan asli miliknya | Data yang dilakukan olehnya atau scan asli miliknya |

## 2. Matriks Action

| Action | Admin | Scanner | Scanner Validator |
|---|---:|---:|---:|
| Store scan scanner | Tidak melalui UI scanner | Ya | Ya |
| Delete scan sendiri | Ya | Ya | Ya |
| Delete scan user lain dari All Scan Results | Ya | Tidak | Tidak |
| Edit seluruh kolom scan | Ya | Tidak | Tidak |
| Edit keterangan scan | Ya | Tidak | Tidak |
| Buat Location dari Setup | Tidak melalui Setup | Ya | Ya |
| Hapus Location Setup | Tidak | Hanya buatan sendiri dan belum dipakai | Hanya buatan sendiri dan belum dipakai |
| CRUD Master Location | Ya | Tidak | Tidak |
| Buat Barcode Request | Tidak melalui Request page | Ya | Ya |
| Cancel Barcode Request | Tidak | Pending milik sendiri | Pending milik sendiri |
| Add Barcode dari Generate | Ya | Tidak | Tidak |
| Generate pending barcode | Ya | Tidak | Ya |
| Print/reprint barcode | Ya | Tidak | Ya |
| Reject barcode baru | Tidak tersedia | Tidak tersedia | Tidak tersedia |
| Validasi Material Double | Ya | Tidak | Ya |
| Scan replacement Material Double | Ya | Tidak | Ya |
| Partial delete Material Double | Ya | Tidak | Ya |
| Finish Rack | Tidak | Ya | Ya |
| Validate Rack | Tidak | Tidak | Ya |
| Confirm Rack | Ya | Tidak | Tidak |
| Cancel Rack ke Pending | Ya, alasan wajib | Tidak | Tidak |
| Export Scan Results | Ya | Tidak | Tidak |
| Export Material Double | Ya | Tidak | Ya |
| Export Rack Confirmation | Ya | Tidak | Tidak |
| Mutasi History Delete Barcode | Tidak tersedia | Tidak tersedia | Tidak tersedia |

## 3. Enforcement Backend

| Capability | Enforcement |
|---|---|
| Area admin | `auth`, `role:admin` |
| Area scanner | `auth`, `role:scanner` |
| Material Double | `material-double-access` + Form Request + `ScanResultPolicy::deleteFromMaterialDouble` |
| Generate Barcode | `generate-barcode-access`; Add Barcode ditambah `role:admin` |
| Rack Confirmation | `rack-confirmation-access` + state/role check di `RackConfirmationService` |
| Delete scan biasa | `ScanResultPolicy::delete` |
| History delete | Scoped query pada `deleted_by_user_id` atau `original_user_id` |

Sidebar dan tombol hanya membantu UX. Menyembunyikan menu tidak pernah menggantikan middleware, policy, Form Request, atau state validation backend.

## 4. Invariant User Management

- Saat role disimpan sebagai `admin`, `is_validator` dipaksa `true` dan switch tidak dapat diedit.
- Saat role `scanner`, admin dapat mengaktifkan/menonaktifkan `is_validator`.
- Mengubah scanner menjadi non-validator harus langsung menghilangkan akses Material Double dan Generate Barcode pada request berikutnya.
- User `is_active=false` tidak dapat login meskipun password benar.
