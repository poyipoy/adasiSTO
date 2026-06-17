<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Jadwal untuk mengeksekusi worker secara background via Cron Job cPanel
// Worker akan berhenti otomatis saat antrean kosong (--stop-when-empty)
// Dan tidak akan bertumpuk (--withoutOverlapping)
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();
