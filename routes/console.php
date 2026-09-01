<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal otomatis broadcast tagihan bulanan setiap tanggal 1 dan 10 jam 08:00 WIB
\Illuminate\Support\Facades\Schedule::command('keuangan:broadcast-tagihan-bulanan')->monthlyOn(1, '08:00');
\Illuminate\Support\Facades\Schedule::command('keuangan:broadcast-tagihan-bulanan')->monthlyOn(10, '08:00');
