<?php

namespace App\Services;

use App\Models\AbsensiGuru;
use App\Models\Konfigurasi;
use App\Models\User;
use App\Services\Modules\BaseActionService;
use App\Services\WaGatewayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TeacherAttendanceService extends BaseActionService
{
    /**
     * Process RFID / Scanner attendance tap for a teacher.
     */
    public function process(User $teacher, ?Carbon $moment = null): array
    {
        $now = ($moment ?? Carbon::now())->copy();
        $today = $now->toDateString();
        $jamNow = $now->format('H:i:s');

        $jamConfig = $this->getGlobalJamConfig();
        $jamMasukMulai = $jamConfig['jam_masuk_mulai'] . ':00';
        $jamMasukAkhir = $jamConfig['jam_masuk_akhir'] . ':59';
        $jamPulangMulai = $jamConfig['jam_pulang_mulai'] . ':00';

        $row = AbsensiGuru::query()
            ->where('tanggal', $today)
            ->where('user_id', $teacher->id)
            ->first();

        // 1. Presensi Masuk (Datang Pertama) - Fleksibel tanpa batas terlambat untuk guru
        if (!$row) {
            $keterangan = 'Tepat Waktu';

            $row = AbsensiGuru::query()->create([
                'user_id' => $teacher->id,
                'tanggal' => $today,
                'nama' => $teacher->name,
                'username' => $teacher->username,
                'jabatan' => $teacher->jabatan ?: ($teacher->kelas ?: 'Guru / Staf'),
                'jam_datang' => $jamNow,
                'jam_pulang' => null,
                'keterangan' => $keterangan,
                'status' => 'Hadir',
            ]);

            $this->dispatchTeacherAttendanceNotification($teacher, [
                'type' => 'datang',
                'tanggal' => $today,
                'jam' => $jamNow,
                'status' => 'Hadir',
                'keterangan' => $keterangan,
            ]);

            return [
                'success' => true,
                'type' => 'masuk',
                'status_label' => 'Hadir',
                'nisn' => $teacher->username,
                'nama' => $teacher->name,
                'kelas' => $teacher->jabatan ?: ($teacher->kelas ?: 'Guru / Staf'),
                'status' => 'Hadir',
                'jamDatang' => $jamNow,
                'jamPulang' => null,
                'keterangan' => $keterangan,
                'message' => 'Presensi Masuk Guru: ' . $teacher->name . ' (' . $keterangan . ')',
                'role' => 'guru',
            ];
        }

        // 2. Presensi Pulang (Sudah Datang, Belum Pulang)
        if ($row->jam_datang && !$row->jam_pulang) {
            // Hindari double tap dalam 60 detik
            $diffInSeconds = Carbon::parse($today . ' ' . $row->jam_datang)->diffInSeconds($now);
            if ($diffInSeconds < 60) {
                return [
                    'success' => true,
                    'type' => 'masuk',
                    'status_label' => $row->status,
                    'nisn' => $teacher->username,
                    'nama' => $teacher->name,
                    'kelas' => $teacher->jabatan ?: ($teacher->kelas ?: 'Guru / Staf'),
                    'status' => $row->status,
                    'jamDatang' => $row->jam_datang,
                    'jamPulang' => null,
                    'keterangan' => $row->keterangan,
                    'message' => 'Sudah presensi masuk pada ' . $row->jam_datang,
                    'role' => 'guru',
                ];
            }

            $isPulangCepat = $jamNow < $jamPulangMulai;
            $keteranganPulang = $isPulangCepat ? 'Pulang Cepat' : 'Tepat Waktu';

            $row->jam_pulang = $jamNow;
            $row->save();

            $this->dispatchTeacherAttendanceNotification($teacher, [
                'type' => 'pulang',
                'tanggal' => $today,
                'jam' => $jamNow,
                'status' => 'Pulang',
                'keterangan' => $keteranganPulang,
            ]);

            return [
                'success' => true,
                'type' => 'pulang',
                'status_label' => 'Pulang',
                'nisn' => $teacher->username,
                'nama' => $teacher->name,
                'kelas' => $teacher->jabatan ?: ($teacher->kelas ?: 'Guru / Staf'),
                'status' => 'Pulang',
                'jamDatang' => $row->jam_datang,
                'jamPulang' => $jamNow,
                'keterangan' => $keteranganPulang,
                'message' => 'Presensi Pulang Guru: ' . $teacher->name . ' (' . $keteranganPulang . ')',
                'role' => 'guru',
            ];
        }

        // 3. Sudah presensi datang & pulang lengkap
        return [
            'success' => true,
            'type' => 'pulang',
            'status_label' => $row->status,
            'nisn' => $teacher->username,
            'nama' => $teacher->name,
            'kelas' => $teacher->jabatan ?: ($teacher->kelas ?: 'Guru / Staf'),
            'status' => $row->status,
            'jamDatang' => $row->jam_datang,
            'jamPulang' => $row->jam_pulang,
            'keterangan' => $row->keterangan,
            'message' => 'Sudah lengkap presensi masuk (' . $row->jam_datang . ') & pulang (' . $row->jam_pulang . ')',
            'role' => 'guru',
        ];
    }

    protected function getGlobalJamConfig(): array
    {
        $config = [
            'jam_masuk_mulai' => '06:00',
            'jam_masuk_akhir' => '07:15',
            'jam_masuk_telat' => '07:15',
            'jam_pulang_mulai' => '15:00',
            'jam_pulang_akhir' => '17:00',
        ];

        $rows = Konfigurasi::query()
            ->whereIn('key', array_keys($config))
            ->get();

        foreach ($rows as $row) {
            $key = (string) $row->key;
            $val = trim((string) $row->value);
            if (array_key_exists($key, $config) && $val !== '') {
                $config[$key] = strlen($val) === 5 ? $val : substr($val, 0, 5);
            }
        }

        return $config;
    }

    /**
     * Get monthly attendance recap (matriks per-date 1–31) for all teachers.
     */
    public function getRekapBulanan(array $args, $auth): array
    {
        $role = $this->getRoleFromAuth($auth);
        if (!$auth || !in_array($role, ['admin', 'super-admin', 'kepsek', 'wakasek', 'wakel', 'piket', 'guru'], true)) {
            return ['success' => false, 'message' => 'Akses Ditolak: Anda tidak memiliki izin.'];
        }

        $bulan = (int) ($args[0] ?? Carbon::now()->month);
        $tahun = (int) ($args[1] ?? Carbon::now()->year);
        $search = trim((string) ($args[2] ?? ''));

        $startDate = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $endDate->day;

        // Get all active teachers (non-siswa)
        $query = User::query()->whereDoesntHave('roles', fn ($q) => $q->where('name', 'siswa'))->orderBy('name');
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('jabatan', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%"));
        }
        $teachers = $query->get();

        // Bulk load attendance for the month
        $absensiRaw = AbsensiGuru::query()
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy('user_id');

        $statusBadge = [
            'Hadir' => 'H',
            'Masuk' => 'H',
            'Terlambat' => 'TL',
            'Izin' => 'I',
            'Sakit' => 'S',
            'Alpa' => 'A',
            'Pulang Cepat' => 'PC',
            'Pulang' => 'H',
        ];

        $rows = [];
        $totalHadir = 0; $totalTelat = 0; $totalIzin = 0; $totalSakit = 0; $totalAlpa = 0;
        $totalTeacherDays = 0;

        foreach ($teachers as $t) {
            $absMap = collect($absensiRaw->get($t->id, []))->keyBy(fn ($r) => Carbon::parse($r->tanggal)->day);
            $harian = [];
            $tHadir = 0; $tTelat = 0; $tIzin = 0; $tSakit = 0; $tAlpa = 0; $tPulangCepat = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $abs = $absMap->get($d);
                if ($abs) {
                    $st = $abs->status ?? 'Hadir';
                    $ket = $abs->keterangan ?? '';
                    $code = match (true) {
                        $st === 'Izin' => 'I',
                        $st === 'Sakit' => 'S',
                        $st === 'Alpa' => 'A',
                        stripos($ket, 'terlambat') !== false => 'TL',
                        stripos($ket, 'pulang cepat') !== false => 'PC',
                        default => 'H',
                    };

                    if ($code === 'H' || $code === 'TL' || $code === 'PC') $tHadir++;
                    if ($code === 'TL') $tTelat++;
                    if ($code === 'I') $tIzin++;
                    if ($code === 'S') $tSakit++;
                    if ($code === 'A') $tAlpa++;
                    if ($code === 'PC') $tPulangCepat++;

                    $harian[$d] = ['code' => $code, 'jam_datang' => $abs->jam_datang, 'jam_pulang' => $abs->jam_pulang];
                } else {
                    $harian[$d] = null;
                }
            }

            $totalDays = $tHadir + $tIzin + $tSakit + $tAlpa;
            $persen = $totalDays > 0 ? round(($tHadir / max(1, $totalDays)) * 100, 1) : 0;

            $totalHadir += $tHadir; $totalTelat += $tTelat;
            $totalIzin += $tIzin; $totalSakit += $tSakit; $totalAlpa += $tAlpa;
            $totalTeacherDays += $totalDays;

            $rows[] = [
                'user_id' => $t->id,
                'nama' => $t->name,
                'username' => $t->username,
                'jabatan' => $t->jabatan ?: ($t->kelas ?: 'Guru / Staf'),
                'harian' => $harian,
                'total_hadir' => $tHadir,
                'total_telat' => $tTelat,
                'total_izin' => $tIzin,
                'total_sakit' => $tSakit,
                'total_alpa' => $tAlpa,
                'total_pulang_cepat' => $tPulangCepat,
                'persen_kehadiran' => $persen,
            ];
        }

        $overallPersen = $totalTeacherDays > 0 ? round(($totalHadir / max(1, $totalTeacherDays)) * 100, 1) : 0;

        return [
            'success' => true,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_label' => Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y'),
            'days_in_month' => $daysInMonth,
            'summary' => [
                'total_guru' => count($rows),
                'total_hadir' => $totalHadir,
                'total_telat' => $totalTelat,
                'total_izin' => $totalIzin,
                'total_sakit' => $totalSakit,
                'total_alpa' => $totalAlpa,
                'persen_kehadiran' => $overallPersen,
            ],
            'data' => $rows,
        ];
    }

    /**
     * Get yearly attendance recap (12-month summary) for all teachers.
     */
    public function getRekapTahunan(array $args, $auth): array
    {
        $role = $this->getRoleFromAuth($auth);
        if (!$auth || !in_array($role, ['admin', 'super-admin', 'kepsek', 'wakasek', 'wakel', 'piket', 'guru'], true)) {
            return ['success' => false, 'message' => 'Akses Ditolak: Anda tidak memiliki izin.'];
        }

        $tahun = (int) ($args[0] ?? Carbon::now()->year);
        $search = trim((string) ($args[1] ?? ''));

        $startDate = Carbon::create($tahun, 1, 1)->startOfDay()->toDateString();
        $endDate = Carbon::create($tahun, 12, 31)->endOfDay()->toDateString();

        $query = User::query()->whereDoesntHave('roles', fn ($q) => $q->where('name', 'siswa'))->orderBy('name');
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('jabatan', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%"));
        }
        $teachers = $query->get();

        // Bulk load all attendance for the year
        $absensiRaw = AbsensiGuru::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $rows = [];
        foreach ($teachers as $t) {
            $absForTeacher = collect($absensiRaw->get($t->id, []));

            $bulanan = [];
            $totalHadir = 0; $totalTelat = 0; $totalIzin = 0; $totalSakit = 0; $totalAlpa = 0;

            for ($m = 1; $m <= 12; $m++) {
                $absMonth = $absForTeacher->filter(fn ($r) => Carbon::parse($r->tanggal)->month === $m);
                $h = 0; $tl = 0; $i = 0; $s = 0; $a = 0;
                foreach ($absMonth as $abs) {
                    $st = $abs->status ?? 'Hadir';
                    $ket = $abs->keterangan ?? '';
                    if ($st === 'Izin') { $i++; }
                    elseif ($st === 'Sakit') { $s++; }
                    elseif ($st === 'Alpa') { $a++; }
                    else {
                        $h++;
                        if (stripos($ket, 'terlambat') !== false) $tl++;
                    }
                }
                $monthDays = $h + $i + $s + $a;
                $persen = $monthDays > 0 ? round(($h / max(1, $monthDays)) * 100, 1) : 0;
                $bulanan[$m] = ['hadir' => $h, 'telat' => $tl, 'izin' => $i, 'sakit' => $s, 'alpa' => $a, 'persen' => $persen, 'label' => $namaBulan[$m]];
                $totalHadir += $h; $totalTelat += $tl; $totalIzin += $i; $totalSakit += $s; $totalAlpa += $a;
            }

            $totalDays = $totalHadir + $totalIzin + $totalSakit + $totalAlpa;
            $persen = $totalDays > 0 ? round(($totalHadir / max(1, $totalDays)) * 100, 1) : 0;

            $rows[] = [
                'user_id' => $t->id,
                'nama' => $t->name,
                'username' => $t->username,
                'jabatan' => $t->jabatan ?: ($t->kelas ?: 'Guru / Staf'),
                'bulanan' => $bulanan,
                'total_hadir' => $totalHadir,
                'total_telat' => $totalTelat,
                'total_izin' => $totalIzin,
                'total_sakit' => $totalSakit,
                'total_alpa' => $totalAlpa,
                'persen_kehadiran' => $persen,
            ];
        }

        return [
            'success' => true,
            'tahun' => $tahun,
            'summary' => [
                'total_guru' => count($rows),
            ],
            'data' => $rows,
        ];
    }

    /**
     * Export monthly recap to Excel (matrix format).
     */
    public function exportExcelRekapBulanan(array $args, $auth): array
    {
        $result = $this->getRekapBulanan($args, $auth);
        if (!($result['success'] ?? false)) return $result;

        $bulanLabel = $result['bulan_label'];
        $daysInMonth = $result['days_in_month'];
        $rows = $result['data'];
        $bulan = $result['bulan'];
        $tahun = $result['tahun'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Bulanan');

        // Title
        $sheet->setCellValue('A1', 'REKAP ABSENSI GURU & STAF');
        $sheet->setCellValue('A2', 'Bulan: ' . strtoupper($bulanLabel));

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $daysInMonth);
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Headers
        $baseHeaders = ['No', 'Nama Guru / Staf', 'NIP / Username', 'Jabatan'];
        $row = 4;
        $col = 1;
        foreach ($baseHeaders as $h) {
            $sheet->setCellValue([$col++, $row], $h);
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $sheet->setCellValue([$col++, $row], $d);
        }
        $summaryHeaders = ['Hadir', 'Telat', 'Izin', 'Sakit', 'Alpa', '%'];
        foreach ($summaryHeaders as $h) {
            $sheet->setCellValue([$col++, $row], $h);
        }

        $totalCols = 4 + $daysInMonth + 6;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
        $sheet->getStyle('A4:' . $lastColLetter . '4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A4:' . $lastColLetter . '4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');
        $sheet->getStyle('A4:' . $lastColLetter . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data rows
        $row = 5;
        $no = 1;
        foreach ($rows as $r) {
            $col = 1;
            $sheet->setCellValue([$col++, $row], $no++);
            $sheet->setCellValue([$col++, $row], $r['nama']);
            $sheet->setCellValue([$col++, $row], $r['username']);
            $sheet->setCellValue([$col++, $row], $r['jabatan']);
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $val = $r['harian'][$d]['code'] ?? '-';
                $sheet->setCellValue([$col++, $row], $val);
            }
            $sheet->setCellValue([$col++, $row], $r['total_hadir']);
            $sheet->setCellValue([$col++, $row], $r['total_telat']);
            $sheet->setCellValue([$col++, $row], $r['total_izin']);
            $sheet->setCellValue([$col++, $row], $r['total_sakit']);
            $sheet->setCellValue([$col++, $row], $r['total_alpa']);
            $sheet->setCellValue([$col++, $row], $r['persen_kehadiran'] . '%');

            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':' . $lastColLetter . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            }
            $row++;
        }

        // Auto width for key columns
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(20);
        for ($c = 5; $c <= 4 + $daysInMonth; $c++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c))->setWidth(5);
        }

        // Borders
        $sheet->getStyle('A4:' . $lastColLetter . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);

        // Save
        $filename = 'Rekap_Bulanan_Guru_' . $bulan . '_' . $tahun . '_' . date('Ymd_His') . '.xlsx';
        $path = storage_path('app/public/exports/' . $filename);
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return ['success' => true, 'url' => '/storage/exports/' . $filename, 'filename' => $filename];
    }

    /**
     * Export yearly recap to Excel (12-month summary).
     */
    public function exportExcelRekapTahunan(array $args, $auth): array
    {
        $result = $this->getRekapTahunan($args, $auth);
        if (!($result['success'] ?? false)) return $result;

        $tahun = $result['tahun'];
        $rows = $result['data'];
        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Tahunan');

        $sheet->setCellValue('A1', 'REKAP ABSENSI TAHUNAN GURU & STAF');
        $sheet->setCellValue('A2', 'Tahun: ' . $tahun);
        $sheet->mergeCells('A1:V1');
        $sheet->mergeCells('A2:V2');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Headers
        $row = 4; $col = 1;
        foreach (['No', 'Nama Guru / Staf', 'NIP / Username', 'Jabatan'] as $h) {
            $sheet->setCellValue([$col++, $row], $h);
        }
        foreach ($namaBulan as $bln) {
            $sheet->setCellValue([$col++, $row], $bln);
        }
        foreach (['Total Hadir', 'Total Telat', 'Total Izin', 'Total Sakit', 'Total Alpa', '% Hadir'] as $h) {
            $sheet->setCellValue([$col++, $row], $h);
        }
        $totalCols = 4 + 12 + 6;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
        $sheet->getStyle('A4:' . $lastColLetter . '4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A4:' . $lastColLetter . '4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');
        $sheet->getStyle('A4:' . $lastColLetter . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 5; $no = 1;
        foreach ($rows as $r) {
            $col = 1;
            $sheet->setCellValue([$col++, $row], $no++);
            $sheet->setCellValue([$col++, $row], $r['nama']);
            $sheet->setCellValue([$col++, $row], $r['username']);
            $sheet->setCellValue([$col++, $row], $r['jabatan']);
            for ($m = 1; $m <= 12; $m++) {
                $b = $r['bulanan'][$m] ?? [];
                $sheet->setCellValue([$col++, $row], ($b['hadir'] ?? 0));
            }
            $sheet->setCellValue([$col++, $row], $r['total_hadir']);
            $sheet->setCellValue([$col++, $row], $r['total_telat']);
            $sheet->setCellValue([$col++, $row], $r['total_izin']);
            $sheet->setCellValue([$col++, $row], $r['total_sakit']);
            $sheet->setCellValue([$col++, $row], $r['total_alpa']);
            $sheet->setCellValue([$col++, $row], $r['persen_kehadiran'] . '%');

            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':' . $lastColLetter . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            }
            $row++;
        }

        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getStyle('A4:' . $lastColLetter . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);

        $filename = 'Rekap_Tahunan_Guru_' . $tahun . '_' . date('Ymd_His') . '.xlsx';
        $path = storage_path('app/public/exports/' . $filename);
        if (!file_exists(dirname($path))) mkdir(dirname($path), 0755, true);
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return ['success' => true, 'url' => '/storage/exports/' . $filename, 'filename' => $filename];
    }

    /**
     * Get Realtime Monitoring for Teachers.
     */
    public function getMonitoringRealtime(array $args, $auth): array
    {
        $role = $this->getRoleFromAuth($auth);
        if (!$auth || !in_array($role, ['admin', 'kepsek', 'wakasek', 'wakel', 'piket', 'guru'], true)) {
            return ['success' => false, 'message' => 'Akses Ditolak: Anda tidak memiliki izin.'];
        }

        $today = Carbon::today()->toDateString();

        // Ambil semua user staff/guru (bukan siswa)
        $teachers = User::query()
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'siswa');
            })
            ->orderBy('name')
            ->get();

        $absensiToday = AbsensiGuru::query()
            ->where('tanggal', $today)
            ->get()
            ->keyBy('user_id');

        $rows = [];
        $total = $teachers->count();
        $hadirCount = 0;
        $telatCount = 0;
        $izinSakitCount = 0;
        $belumHadirCount = 0;

        foreach ($teachers as $t) {
            $abs = $absensiToday->get($t->id);
            $status = $abs ? $abs->status : 'Belum Absen';
            $jamDatang = $abs ? $abs->jam_datang : null;
            $jamPulang = $abs ? $abs->jam_pulang : null;
            $keterangan = $abs ? ($abs->keterangan ?: '-') : '-';

            if ($abs) {
                if (in_array($status, ['Hadir', 'Masuk'], true)) {
                    $hadirCount++;
                    if ($keterangan === 'Terlambat') {
                        $telatCount++;
                    }
                } elseif (in_array($status, ['Izin', 'Sakit'], true)) {
                    $izinSakitCount++;
                } elseif ($status === 'Alpa') {
                    $belumHadirCount++;
                }
            } else {
                $belumHadirCount++;
            }

            $rows[] = [
                'id' => $abs ? $abs->id : null,
                'user_id' => $t->id,
                'nama' => $t->name,
                'username' => $t->username,
                'jabatan' => $t->jabatan ?: 'Guru',
                'status_kepegawaian' => $t->status ?: 'Aktif',
                'jam_datang' => $jamDatang,
                'jam_pulang' => $jamPulang,
                'keterangan' => $keterangan,
                'status' => $status,
                'nomor_kartu' => $t->nomor_kartu,
            ];
        }

        return [
            'success' => true,
            'tanggal' => $today,
            'tanggal_label' => Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y'),
            'summary' => [
                'total' => $total,
                'hadir' => $hadirCount,
                'terlambat' => $telatCount,
                'izin_sakit' => $izinSakitCount,
                'belum_hadir' => $belumHadirCount,
            ],
            'data' => $rows,
        ];
    }

    /**
     * Get Attendance Report List for Teachers.
     */
    public function getAbsensiList(array $args, $auth): array
    {
        $role = $this->getRoleFromAuth($auth);
        if (!$auth || !in_array($role, ['admin', 'kepsek', 'wakasek', 'wakel', 'piket', 'guru'], true)) {
            return ['success' => false, 'message' => 'Akses Ditolak: Anda tidak memiliki izin.'];
        }

        $tanggalMulai = $args[0] ?? Carbon::today()->startOfMonth()->toDateString();
        $tanggalAkhir = $args[1] ?? Carbon::today()->toDateString();
        $filterStatus = $args[2] ?? null;
        $search = trim((string) ($args[3] ?? ''));

        $query = AbsensiGuru::query()
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir]);

        if ($filterStatus && $filterStatus !== 'all' && $filterStatus !== '') {
            $query->where('status', $filterStatus);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('tanggal', 'desc')
            ->orderBy('nama', 'asc')
            ->get();

        return [
            'success' => true,
            'data' => $records->map(fn ($row) => [
                'id' => $row->id,
                'user_id' => $row->user_id,
                'tanggal' => $row->tanggal->toDateString(),
                'tanggal_formatted' => $row->tanggal->locale('id')->isoFormat('D MMM Y'),
                'nama' => $row->nama,
                'username' => $row->username,
                'jabatan' => $row->jabatan ?: 'Guru',
                'jam_datang' => $row->jam_datang ?: '-',
                'jam_pulang' => $row->jam_pulang ?: '-',
                'keterangan' => $row->keterangan ?: '-',
                'status' => $row->status,
            ])->all(),
        ];
    }

    /**
     * Update or manually insert Teacher Attendance Status.
     */
    public function updateAbsensiStatus(array $args, $auth): array
    {
        if (!$this->authHasAnyRole($auth, ['admin', 'kepsek', 'super-admin'])) {
            return ['success' => false, 'message' => 'Akses Ditolak: Hanya Admin atau Kepala Sekolah yang dapat mengubah status.'];
        }

        $userId = (int) ($args[0] ?? 0);
        $status = trim((string) ($args[1] ?? 'Hadir'));
        $keterangan = trim((string) ($args[2] ?? ''));
        $tanggal = trim((string) ($args[3] ?? ''));
        $jamDatang = isset($args[4]) && trim((string)$args[4]) !== '' ? trim((string)$args[4]) : null;
        $jamPulang = isset($args[5]) && trim((string)$args[5]) !== '' ? trim((string)$args[5]) : null;
        $kirimWa = isset($args[6]) ? filter_var($args[6], FILTER_VALIDATE_BOOLEAN) : true;

        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Guru tidak valid.'];
        }

        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Data Guru tidak ditemukan.'];
        }

        $targetDate = $tanggal !== '' ? $tanggal : Carbon::today()->toDateString();
        $now = Carbon::now();

        $record = AbsensiGuru::query()
            ->where('tanggal', $targetDate)
            ->where('user_id', $userId)
            ->first();

        if ($status === 'Belum Absen') {
            if ($record) {
                $record->delete();
            }
            return [
                'success' => true,
                'message' => 'Status kehadiran ' . $user->name . ' direset menjadi Belum Absen.',
            ];
        }

        if (!$record) {
            $record = new AbsensiGuru();
            $record->user_id = $user->id;
            $record->tanggal = $targetDate;
            $record->nama = $user->name;
            $record->username = $user->username;
            $record->jabatan = $user->jabatan ?: 'Guru';
        }

        // Format jam HH:MM:SS
        if ($jamDatang) {
            $record->jam_datang = strlen($jamDatang) === 5 ? $jamDatang . ':00' : $jamDatang;
        } elseif (in_array($status, ['Hadir', 'Masuk'], true) && !$record->jam_datang) {
            $record->jam_datang = $now->format('H:i:s');
        }

        if ($jamPulang) {
            $record->jam_pulang = strlen($jamPulang) === 5 ? $jamPulang . ':00' : $jamPulang;
        }

        $record->status = $status;
        if ($keterangan !== '') {
            $record->keterangan = $keterangan;
        } elseif (in_array($status, ['Izin', 'Sakit', 'Alpa'], true)) {
            $record->keterangan = $status;
        } else {
            $record->keterangan = 'Tepat Waktu';
        }

        $record->save();

        if ($kirimWa) {
            $jamNotif = $record->jam_datang ?: ($record->jam_pulang ?: $now->format('H:i:s'));
            $this->dispatchTeacherAttendanceNotification($user, [
                'type' => in_array($status, ['Pulang', 'Pulang Cepat']) ? 'pulang' : 'datang',
                'tanggal' => $targetDate,
                'jam' => $jamNotif,
                'status' => $status,
                'keterangan' => $record->keterangan ?: $status,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Status kehadiran ' . $user->name . ' berhasil diperbarui menjadi ' . $status . ($kirimWa ? ' & WA terkirim.' : '.'),
            'data' => $record,
        ];
    }

    /**
     * Mark mass checkout for teachers today.
     */
    public function markPulangMassal(array $args, $auth): array
    {
        if (!$this->authHasAnyRole($auth, ['admin', 'kepsek', 'super-admin'])) {
            return ['success' => false, 'message' => 'Akses Ditolak: Hanya Admin atau Kepala Sekolah yang dapat melakukan aksi ini.'];
        }

        $today = Carbon::today()->toDateString();
        $nowJam = Carbon::now()->format('H:i:s');

        $updatedCount = AbsensiGuru::query()
            ->where('tanggal', $today)
            ->whereNotNull('jam_datang')
            ->whereNull('jam_pulang')
            ->whereIn('status', ['Hadir', 'Masuk'])
            ->update([
                'jam_pulang' => $nowJam,
            ]);

        return [
            'success' => true,
            'message' => "Berhasil mencatat absen pulang massal untuk {$updatedCount} guru.",
            'updated_count' => $updatedCount,
        ];
    }

    /**
     * Export Teacher Attendance Report to Excel.
     */
    public function exportExcel(array $args, $auth): array
    {
        $role = $this->getRoleFromAuth($auth);
        if (!$auth || !in_array($role, ['admin', 'kepsek', 'wakasek', 'wakel', 'piket', 'guru'], true)) {
            return ['success' => false, 'message' => 'Akses Ditolak: Anda tidak memiliki izin.'];
        }

        $tanggalMulai = $args[0] ?? Carbon::today()->startOfMonth()->toDateString();
        $tanggalAkhir = $args[1] ?? Carbon::today()->toDateString();
        $filterStatus = $args[2] ?? null;

        $query = AbsensiGuru::query()
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir]);

        if ($filterStatus && $filterStatus !== 'all' && $filterStatus !== '') {
            $query->where('status', $filterStatus);
        }

        $records = $query->orderBy('tanggal', 'asc')->orderBy('nama', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Presensi Guru');

        // Header Title
        $sheet->setCellValue('A1', 'LAPORAN PRESENSI GURU & STAF');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMMM Y') . ' s/d ' . Carbon::parse($tanggalAkhir)->locale('id')->isoFormat('D MMMM Y'));
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Headers
        $headers = ['No', 'Tanggal', 'Nama Guru / Staf', 'NIP / Username', 'Jabatan', 'Jam Datang', 'Jam Pulang', 'Keterangan', 'Status'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        $rowNum = 4;
        foreach ($headers as $i => $header) {
            $sheet->setCellValue($cols[$i] . $rowNum, $header);
        }

        $sheet->getStyle('A4:I4')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle('A4:I4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');
        $sheet->getStyle('A4:I4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNum = 5;
        $no = 1;
        foreach ($records as $r) {
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $r->tanggal->format('Y-m-d'));
            $sheet->setCellValue('C' . $rowNum, $r->nama);
            $sheet->setCellValue('D' . $rowNum, $r->username);
            $sheet->setCellValue('E' . $rowNum, $r->jabatan ?: 'Guru');
            $sheet->setCellValue('F' . $rowNum, $r->jam_datang ?: '-');
            $sheet->setCellValue('G' . $rowNum, $r->jam_pulang ?: '-');
            $sheet->setCellValue('H' . $rowNum, $r->keterangan ?: '-');
            $sheet->setCellValue('I' . $rowNum, $r->status);

            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rowNum . ':I' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        // Borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle('A4:I' . max(5, $rowNum - 1))->applyFromArray($styleArray);

        // Auto width
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Laporan_Presensi_Guru_' . date('Ymd_His') . '.xlsx';
        $exportDir = storage_path('app/public/exports');
        if (!file_exists($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        $filePath = $exportDir . '/' . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return [
            'success' => true,
            'url' => asset('storage/exports/' . $fileName),
            'filename' => $fileName,
        ];
    }

    public function dispatchTeacherAttendanceNotification(User $teacher, array $context): void
    {
        $teacherId = (int) ($teacher->id ?? 0);
        if ($teacherId <= 0) {
            return;
        }

        dispatch(function () use ($teacherId, $context): void {
            try {
                $targetTeacher = User::query()->find($teacherId);
                if (!$targetTeacher) {
                    return;
                }
                app(WaGatewayService::class)->notifyTeacherAttendance($targetTeacher, $context);
            } catch (\Throwable $e) {
                Log::warning('WA teacher attendance notification failed', [
                    'teacher_id' => $teacherId,
                    'message' => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }
}
