# Admin Master Views

This directory contains the views for the master data management pages (STO Codes, Plants, Materials, Keterangan, and Users).

## Important Architecture Note
All master data pages are dynamically rendered using a single universal template: **`generic.blade.php`**.

The `MasterController` passes a configuration array (`$this->moduleConfig('module_name')`) to `generic.blade.php`, which then automatically constructs the appropriate DataTables columns, form inputs, and API endpoint bindings.

## Legacy Files
The following files are **DISABLED** and kept only for historical reference. They are no longer used by the application and should not be modified:
- `plants.blade.php`
- `materials.blade.php`
- `keterangan.blade.php`

To modify a master data page, edit `generic.blade.php` or update the configuration array in `MasterController.php`.
