<?php

namespace App\Console\Commands;

use App\Http\Controllers\KeuanganSekolahController;
use Illuminate\Console\Command;

class SyncKeuanganBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keuangan:sync-tagihan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi tagihan seluruh siswa sesuai kategori kelas dan tarif pos aktif.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi tagihan siswa...');

        $controller = app(KeuanganSekolahController::class);
        $controller->ensureBillingSynced();

        $this->info('Sinkronisasi tagihan siswa berhasil diselesaikan.');

        return Command::SUCCESS;
    }
}