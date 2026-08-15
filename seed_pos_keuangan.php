<?php

require 'c:/laragon/www/absensindo/absensindo/vendor/autoload.php';
$app = require_once 'c:/laragon/www/absensindo/absensindo/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PosKeuangan;

$defaults = [
    [
        'kode' => 'SPP',
        'nama' => 'SPP Bulanan',
        'tipe' => 'bulanan',
        'nominal_default' => 150000,
        'tahun_ajaran' => '2026/2027',
        'deskripsi' => 'Sumbangan Pembinaan Pendidikan bulanan siswa',
        'is_active' => true,
    ],
    [
        'kode' => 'GEDUNG',
        'nama' => 'Uang Gedung / Sarpras',
        'tipe' => 'bebas',
        'nominal_default' => 1000000,
        'tahun_ajaran' => '2026/2027',
        'deskripsi' => 'Biaya pembangunan dan sarana prasarana sekolah (bisa diangsur)',
        'is_active' => true,
    ],
    [
        'kode' => 'UJIAN',
        'nama' => 'Ujian Semester (PTS/PAS)',
        'tipe' => 'sekali_bayar',
        'nominal_default' => 100000,
        'tahun_ajaran' => '2026/2027',
        'deskripsi' => 'Biaya administrasi pelaksanaan ujian semester',
        'is_active' => true,
    ],
    [
        'kode' => 'SERAGAM',
        'nama' => 'Seragam & Atribut Sekolah',
        'tipe' => 'sekali_bayar',
        'nominal_default' => 350000,
        'tahun_ajaran' => '2026/2027',
        'deskripsi' => 'Paket seragam sekolah dan kelengkapan atribut',
        'is_active' => true,
    ],
    [
        'kode' => 'KEGIATAN',
        'nama' => 'Uang Kegiatan & Praktik',
        'tipe' => 'bebas',
        'nominal_default' => 200000,
        'tahun_ajaran' => '2026/2027',
        'deskripsi' => 'Biaya kegiatan kesiswaan, ekstrakurikuler dan praktik',
        'is_active' => true,
    ],
];

foreach ($defaults as $item) {
    PosKeuangan::firstOrCreate(['kode' => $item['kode']], $item);
}

echo "DEFAULT_POS_KEUANGAN_CREATED\n";
