<?php

return [
    'company_name' => env('STO_COMPANY_NAME', 'PT Astra Daido Steel Indonesia'),

    'default_qty' => 1,
    'default_keterangan' => 'OK',
    'scanner_can_edit_keterangan' => false,
    'scanner_delete_scan' => 'hard_delete',

    'scan_history_filter_limit' => 250,
    'admin_filter_options_limit' => 500,
    'export_pdf_row_limit' => 5000,
    'datatable_max_length' => (int) env('STO_DATATABLE_MAX_LENGTH', 100),
    'dashboard_latest_max_length' => (int) env('STO_DASHBOARD_LATEST_MAX_LENGTH', 50),
    'export_disk' => env('STO_EXPORT_DISK', 'local'),
    'health_expose_environment' => (bool) env('STO_HEALTH_EXPOSE_ENVIRONMENT', false),

    'rate_limits' => [
        'login_per_minute' => (int) env('STO_RATE_LIMIT_LOGIN_PER_MINUTE', 5),
        'scan_write_per_minute' => (int) env('STO_RATE_LIMIT_SCAN_WRITE_PER_MINUTE', 120),
        'export_per_minute' => (int) env('STO_RATE_LIMIT_EXPORT_PER_MINUTE', 10),
        'datatable_per_minute' => (int) env('STO_RATE_LIMIT_DATATABLE_PER_MINUTE', 240),
    ],
];
