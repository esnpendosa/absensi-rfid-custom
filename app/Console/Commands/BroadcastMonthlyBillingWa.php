<?php

namespace App\Console\Commands;

use App\Models\PosKeuangan;
use App\Models\Siswa;
use App\Models\TagihanSiswa;
use App\Services\WaGatewayService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BroadcastMonthlyBillingWa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keuangan:broadcast-tagihan-bulanan {--bulan=} {--tahun=} {--kelas=} {--preview}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi tagihan keuangan bulanan siswa secara otomatis via WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $namaBulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $currentMonthName = $namaBulanIndo[Carbon::now()->month] ?? 'Juli';
        $bulan = $this->option('bulan') ?: $currentMonthName;
        $tahun = $this->option('tahun') ?: '2026/2027';
        $kelasFilter = $this->option('kelas');
        $isPreview = (bool) $this->option('preview');

        $this->info("Memulai proses notifikasi tagihan bulanan: Bulan {$bulan}, Tahun Ajaran {$tahun}...");

        $query = Siswa::query()
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');

        if ($kelasFilter) {
            $query->where('kelas', $kelasFilter);
        }

        $siswaList = $query->with(['tagihan' => function ($q) use ($bulan, $tahun) {
            $q->where('sisa', '>', 0)
              ->with('posKeuangan');
        }])->get();

        $waService = app(WaGatewayService::class);
        $sentCount = 0;
        $skippedCount = 0;

        foreach ($siswaList as $siswa) {
            $unpaidBills = $siswa->tagihan->filter(function ($t) use ($bulan) {
                // Prioritaskan tagihan bulan ini atau tagihan bebas/sekali bayar yang belum lunas
                return $t->sisa > 0;
            });

            if ($unpaidBills->isEmpty()) {
                $skippedCount++;
                continue;
            }

            $totalSisa = $unpaidBills->sum('sisa');
            $totalSisaStr = 'Rp ' . number_format($totalSisa, 0, ',', '.');

            // Format rincian tagihan
            $rincianText = "";
            $no = 1;
            foreach ($unpaidBills as $t) {
                $posNama = ($t->posKeuangan->nama ?? 'Keuangan') . ($t->bulan ? ' (' . $t->bulan . ')' : '');
                $sisaStr = 'Rp ' . number_format($t->sisa, 0, ',', '.');
                $rincianText .= "{$no}. {$posNama} : {$sisaStr}\n";
                $no++;
                if ($no > 10) {
                    $sisaLainnya = $unpaidBills->count() - 10;
                    if ($sisaLainnya > 0) {
                        $rincianText .= "... dan {$sisaLainnya} tagihan lainnya\n";
                    }
                    break;
                }
            }

            $portalUrl = url('/');

            $pesan = "*PEMBERITAHUAN TAGIHAN KEUANGAN SISWA*\n" .
                     "*SMK NURUL HIDAYAH*\n" .
                     "------------------------------------\n" .
                     "Bulan / Periode : {$bulan} {$tahun}\n" .
                     "Nama Siswa      : {$siswa->nama}\n" .
                     "NISN / Kelas    : {$siswa->nisn} (" . ($siswa->kelas ?: '-') . ")\n" .
                     "------------------------------------\n" .
                     "*Rincian Tagihan Belum Lunas:*\n" .
                     $rincianText .
                     "------------------------------------\n" .
                     "TOTAL TUNGGAKAN : *{$totalSisaStr}*\n" .
                     "------------------------------------\n" .
                     "*Informasi Pembayaran:*\n" .
                     "Pembayaran dapat dilakukan melalui Bendahara Sekolah.\n\n" .
                     "*Portal Sekolah:*\n" .
                     "{$portalUrl}\n\n" .
                     "_Pemberitahuan resmi otomatis ini dikirimkan untuk kenyamanan administrasi keuangan sekolah. Abaikan jika sudah melakukan pembayaran._";

            if ($isPreview) {
                $this->line("=== PREVIEW WA UNTUK {$siswa->nama} ({$siswa->no_hp}) ===");
                $this->line($pesan);
                $this->line("====================================================\n");
                $sentCount++;
                continue;
            }

            try {
                $res = $waService->sendCustomMessage($siswa->no_hp, $pesan);
                if ($res['success'] ?? false) {
                    $sentCount++;
                    $this->info("✓ Berhasil kirim WA tagihan ke {$siswa->nama} ({$siswa->no_hp})");
                } else {
                    $this->warn("✗ Gagal kirim WA ke {$siswa->nama}: " . ($res['message'] ?? 'Error gateway'));
                }
            } catch (\Throwable $e) {
                $this->error("Error kirim WA {$siswa->nama}: " . $e->getMessage());
                Log::error("Broadcast WA Tagihan Error", ['siswa' => $siswa->id, 'error' => $e->getMessage()]);
            }

            // Jeda 200ms agar pengiriman broadcast teratur dan tidak membebani gateway
            usleep(200000);
        }

        $this->info("Selesai! Berhasil terkirim: {$sentCount}, Dilewati (Lunas): {$skippedCount}");

        return Command::SUCCESS;
    }
}
