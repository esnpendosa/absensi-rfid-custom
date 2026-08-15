<?php

require 'c:/laragon/www/absensindo/absensindo/vendor/autoload.php';
$app = require_once 'c:/laragon/www/absensindo/absensindo/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Konfigurasi;

$configs = [
    'jam_masuk_mulai' => '00:00',
    'jam_masuk_akhir' => '23:59',
    'jam_masuk_telat' => '23:59',
    'jam_pulang_mulai' => '00:00',
    'jam_pulang_akhir' => '23:59',
];

foreach ($configs as $k => $v) {
    Konfigurasi::updateOrCreate(
        ['key' => $k],
        ['value' => $v, 'keterangan' => 'Pengaturan jam fleksibel']
    );
}

\Illuminate\Support\Facades\Cache::flush();
echo "SUCCESS_JAM_BEBAS_CONFIGURED\n";
