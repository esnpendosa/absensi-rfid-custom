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

        $row = AbsensiGuru::query()
            ->where('tanggal', $today)
            ->where('user_id', $teacher->id)
            ->first();

        // Ambil konfigurasi jam jika ada
        $jamMasukAkhir = '07:15:00';
        $jamConfig = Konfigurasi::where('key', 'jam_masuk_akhir')->first();
        if ($jamConfig && !empty($jamConfig->value)) {
            $jamMasukAkhir = strlen($jamConfig->value) === 5 ? $jamConfig->value . ':00' : $jamConfig->value;
        }

        if (!$row) {
            $isLate = $jamNow > $jamMasukAkhir;
            $keterangan = $isLate ? 'Terlambat' : 'Tepat Waktu';

            $row = AbsensiGuru::query()->create([
                'user_id' => $teacher->id,
                'tanggal' => $today,
                'nama' => $teacher->name,
                'username' => $teacher->username,
                'jabatan' => $teacher->jabatan ?: 'Guru',
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
                'kelas' => $teacher->jabatan ?: 'Guru / Staf',
                'status' => 'Hadir',
                'jamDatang' => $jamNow,
                'jamPulang' => null,
                'keterangan' => $keterangan,
                'message' => 'Presensi Masuk Guru: ' . $teacher->name . ' (' . $keterangan . ')',
                'role' => 'guru',
            ];
        }

        // Jika sudah ada jam datang dan belum ada jam pulang
        if ($row->jam_datang && !$row->jam_pulang) {
            // Hindari double tap dalam 1 menit
            $diffInSeconds = Carbon::parse($today . ' ' . $row->jam_datang)->diffInSeconds($now);
            if ($diffInSeconds < 60) {
                return [
                    'success' => true,
                    'type' => 'masuk',
                    'status_label' => $row->status,
                    'nisn' => $teacher->username,
                    'nama' => $teacher->name,
                    'kelas' => $teacher->jabatan ?: 'Guru / Staf',
                    'status' => $row->status,
                    'jamDatang' => $row->jam_datang,
                    'jamPulang' => null,
                    'keterangan' => $row->keterangan,
                    'message' => 'Sudah presensi masuk pada ' . $row->jam_datang,
                    'role' => 'guru',
                ];
            }

            $row->jam_pulang = $jamNow;
            $row->save();

            $this->dispatchTeacherAttendanceNotification($teacher, [
                'type' => 'pulang',
                'tanggal' => $today,
                'jam' => $jamNow,
                'status' => 'Pulang',
                'keterangan' => 'Tepat Waktu',
            ]);

            return [
                'success' => true,
                'type' => 'pulang',
                'status_label' => 'Pulang',
                'nisn' => $teacher->username,
                'nama' => $teacher->name,
                'kelas' => $teacher->jabatan ?: 'Guru / Staf',
                'status' => 'Pulang',
                'jamDatang' => $row->jam_datang,
                'jamPulang' => $jamNow,
                'keterangan' => $row->keterangan,
                'message' => 'Presensi Pulang Guru: ' . $teacher->name,
                'role' => 'guru',
            ];
        }

        // Sudah presensi datang & pulang
        return [
            'success' => true,
            'type' => 'pulang',
            'status_label' => $row->status,
            'nisn' => $teacher->username,
            'nama' => $teacher->name,
            'kelas' => $teacher->jabatan ?: 'Guru / Staf',
            'status' => $row->status,
            'jamDatang' => $row->jam_datang,
            'jamPulang' => $row->jam_pulang,
            'keterangan' => $row->keterangan,
            'message' => 'Sudah lengkap presensi masuk (' . $row->jam_datang . ') & pulang (' . $row->jam_pulang . ')',
            'role' => 'guru',
        ];
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

        if (!$record) {
            $record = new AbsensiGuru();
            $record->user_id = $user->id;
            $record->tanggal = $targetDate;
            $record->nama = $user->name;
            $record->username = $user->username;
            $record->jabatan = $user->jabatan ?: 'Guru';
            if (in_array($status, ['Hadir', 'Masuk'], true)) {
                $record->jam_datang = $now->format('H:i:s');
            }
        }

        $record->status = $status;
        if ($keterangan !== '') {
            $record->keterangan = $keterangan;
        } elseif (in_array($status, ['Izin', 'Sakit', 'Alpa'], true)) {
            $record->keterangan = $status;
        }

        $record->save();

        $this->dispatchTeacherAttendanceNotification($user, [
            'type' => in_array($status, ['Pulang', 'Pulang Cepat']) ? 'pulang' : 'datang',
            'tanggal' => $targetDate,
            'jam' => $record->jam_datang ?: $now->format('H:i:s'),
            'status' => $status,
            'keterangan' => $record->keterangan ?: $status,
        ]);

        return [
            'success' => true,
            'message' => 'Status kehadiran ' . $user->name . ' berhasil diperbarui menjadi ' . $status . '.',
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
        $dispatchMode = strtoupper(trim((string) config('services.wa_gateway.dispatch_mode', 'QUEUE')));
        $teacherId = (int) ($teacher->id ?? 0);

        if (in_array($dispatchMode, ['REALTIME', 'AFTER_RESPONSE'], true)) {
            dispatch(function () use ($teacherId, $context): void {
                try {
                    if ($teacherId <= 0) {
                        return;
                    }
                    $targetTeacher = User::query()->find($teacherId);
                    if (!$targetTeacher) {
                        return;
                    }
                    app(WaGatewayService::class)->notifyTeacherAttendance($targetTeacher, $context);
                } catch (\Throwable $e) {
                    Log::warning('WA teacher attendance notification failed (after response)', [
                        'teacher_id' => $teacherId,
                        'message' => $e->getMessage(),
                    ]);
                }
            })->afterResponse();

            return;
        }

        if (in_array($dispatchMode, ['QUEUE', 'AFTER'], true)) {
            dispatch(function () use ($teacherId, $context): void {
                try {
                    if ($teacherId <= 0) {
                        return;
                    }
                    $targetTeacher = User::query()->find($teacherId);
                    if (!$targetTeacher) {
                        return;
                    }
                    app(WaGatewayService::class)->notifyTeacherAttendance($targetTeacher, $context);
                } catch (\Throwable $e) {
                    Log::warning('WA teacher attendance notification failed (queue)', [
                        'teacher_id' => $teacherId,
                        'message' => $e->getMessage(),
                    ]);
                }
            })->onQueue('notifications');

            return;
        }

        try {
            app(WaGatewayService::class)->notifyTeacherAttendance($teacher, $context);
        } catch (\Throwable $e) {
            Log::warning('WA teacher attendance notification failed (sync)', [
                'teacher_id' => $teacher->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
