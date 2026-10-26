<?php

use App\Models\ExportRequest;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal untuk menghapus data export lama (beserta filenya)
Schedule::command('model:prune', [
    '--model' => [ExportRequest::class],
])->daily();
