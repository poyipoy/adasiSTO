# STO Revision Plan

## 1. Current System Analysis

The current project is a Laravel 12 application using Blade, MySQL-ready migrations, Laravel Excel, and Yajra DataTables. Existing implementation already has login by username/password, scanner pages, admin dashboard, master plant/material/keterangan pages, and an export class.

The current scan workflow is still session-based through `sto_sessions`. The system auto-generates STO codes from the date and stores scan data against `sto_session_id`. Barcode parsing currently accepts only the material barcode string and does not parse the final QR format `<barcode_material>|<lot_number>|<qty>`.

## 2. Missing Features

- `sto_codes` master table and active STO rule.
- Scanner endpoints from `docs/API_SPECIFICATION.md`.
- `BarcodeParserService`, `ScanService`, `STOService`, and `ExportService`.
- Master STO, Master Location, and User Management admin modules.
- Duplicate scan warning and force-save flow.
- User hard delete own scan endpoint with audit log before delete.
- PDF export endpoint.
- Requirement-focused unit and feature tests.

## 3. Incorrect Features

- Scanner role is stored as `user` instead of `scanner`.
- User can edit `keterangan`; final rule allows only admin to edit.
- User can create new locations from scanner setup; final rule forbids this.
- STO code is generated from the scan setup session, not from active Master STO.
- Lot is set from STO code; final rule requires lot from QR.
- Admin scan update currently allows editing barcode, material, qty, user, and scan time; final API only allows `keterangan`, `plant_id`, and `location_id`.
- Several admin/master tables load all records into Blade instead of server-side DataTables.

## 4. Database Changes Required

- Replace `sto_sessions` with `sto_codes`.
- Rebuild `scan_results` with final columns: `sto_code_id`, `sto_code`, `barcode_raw`, `barcode_material`, `lot_number`, `qty`, parsed material fields, dimensions, `keterangan`, and `scan_source`.
- Rename master material columns to `material_code` and `material_name`.
- Add `locations.code` and `locations.is_active`.
- Change user role enum to `admin` and `scanner`.
- Rebuild `scan_result_logs` to field-level audit columns.
- Add required indexes from `docs/DATABASE.md`.

## 5. Controller Changes Required

- Keep controllers thin and move scan, STO, parser, export, and audit work into services.
- Split scanner endpoints into setup page, location lookup, preview, duplicate check, store, history, and delete.
- Split admin scan result endpoints into page, DataTables data, update, delete, Excel export, and PDF export.
- Add admin CRUD endpoints for STO, plant, location, material, keterangan, and users.

## 6. View/UI Changes Required

- Keep Infor-style layout from `docs/DESIGN.md`.
- Show Current STO in the topbar.
- Replace scanner setup with readonly PIC and active STO, plus plant/location dropdowns.
- Replace scanner page with QR input/camera/manual flow, parsing preview, duplicate warning modal, save action, and recent scan list.
- Replace user history keterangan select with readonly status badge and delete action.
- Add admin menu entries for Master STO, Master Location, and User Management.
- Convert master/admin data pages to server-side DataTables.

## 7. Route/API Changes Required

- Add scanner routes: `/scan/setup`, `/api/locations`, `/api/scan/preview`, `/api/scan/check-duplicate`, `/api/scan/store`, `/api/scan/history`, `/api/scan/{id}`.
- Add admin scan routes: `/admin/dashboard`, `/admin/scan-results`, `/admin/api/scan-results`, `/admin/api/scan-results/{id}`.
- Add admin master data API routes matching `docs/API_SPECIFICATION.md`.
- Add export routes: `/admin/export/scan-results/excel` and `/admin/export/scan-results/pdf`.

## 8. Service Layer Required

- `BarcodeParserService`: parse final QR, validate shape, dimensions, qty, lot, and material lookup.
- `STOService`: active STO lookup and activation transaction.
- `ScanService`: preview, duplicate check, store, history query, admin update, hard delete, and audit.
- `ExportService`: shared filtered query and export payload generation.

## 9. Testing Plan

- Parser tests for valid RF/RR, unknown shape, unknown material, missing lot, missing qty, invalid qty, and whitespace separators.
- Scan feature tests for active STO required, ownership, delete audit, duplicate warning, forced duplicate save, scanner keterangan restriction, and admin keterangan update.
- Admin API tests for DataTables filtering, STO activation uniqueness, and export filter behavior.

## 10. Step-by-Step Implementation Plan

1. Normalize requirement docs under `docs/`.
2. Rebuild migrations and seeders for final schema.
3. Update models, role helpers, middleware, and factories.
4. Add Form Requests and service layer.
5. Replace routes and controllers with final endpoint contracts.
6. Revise scanner UI.
7. Revise admin UI and master data screens.
8. Add Excel/PDF exports.
9. Add audit log handling.
10. Add tests and run validation commands.
