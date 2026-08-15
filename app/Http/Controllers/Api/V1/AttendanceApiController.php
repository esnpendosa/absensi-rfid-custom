<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class AttendanceApiController extends Controller
{
    private const REPORT_MAX_DAYS = 366;

    private const SUMMARY_KEYS = [
        'Hadir',
        'Masuk',
        'Izin',
        'Sakit',
        'Alpa',
        'Belum Absen',
    ];

    public function list(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request, [
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['required', 'date_format:Y-m-d'],
            'kelas' => ['nullable', 'string', 'max:100'],
            'nisn' => ['nullable', 'string', 'max:32'],
            'status' => ['nullable', 'string', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $range = $this->resolveDateRange($validated);
        if ($range instanceof JsonResponse) {
            return $range;
        }

        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 50);
        $status = $this->normalizeStatusFilter($validated['status'] ?? null);

        if ($status === 'Belum Absen') {
            return $this->missingAttendanceList($validated, $range['dates'], $page, $perPage);
        }

        $query = Absensi::query()
            ->whereBetween('tanggal', [$range['start']->toDateString(), $range['end']->toDateString()]);

        $this->applyAttendanceFilters($query, $validated);

        if ($status !== null) {
            $this->applyStatusFilter($query, $status);
        }

        $rows = $query
            ->orderBy('tanggal')
            ->orderBy('kelas')
            ->orderBy('nama')
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->successResponse('Data absensi berhasil dimuat.', [
            'range' => $this->rangePayload($range['start'], $range['end']),
            'records' => $rows
                ->getCollection()
                ->map(fn (Absensi $row) => $this->serializeAttendance($row))
                ->values()
                ->all(),
            'meta' => $this->paginationMeta($rows),
        ]);
    }

    public function studentReport(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request, [
            'nisn' => ['required', 'string', 'max:32'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $range = $this->resolveDateRange($validated);
        if ($range instanceof JsonResponse) {
            return $range;
        }

        $student = Siswa::query()->where('nisn', trim((string) $validated['nisn']))->first();
        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan.', 404);
        }

        $attendanceByDate = Absensi::query()
            ->whereBetween('tanggal', [$range['start']->toDateString(), $range['end']->toDateString()])
            ->where(function ($query) use ($student): void {
                $query->where('siswa_id', $student->id)
                    ->orWhere('nisn', $student->nisn);
            })
            ->get()
            ->keyBy(fn (Absensi $row) => $this->dateKey($row->tanggal));

        $report = $this->buildStudentRows($student, $attendanceByDate, $range['dates']);

        return $this->successResponse('Laporan absensi siswa berhasil dimuat.', [
            'student' => $this->serializeStudentIdentity($student),
            'range' => $this->rangePayload($range['start'], $range['end']),
            'summary' => $report['summary'],
            'records' => $report['records'],
        ]);
    }

    public function classReport(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request, [
            'kelas' => ['required', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $range = $this->resolveDateRange($validated);
        if ($range instanceof JsonResponse) {
            return $range;
        }

        $students = Siswa::query()
            ->where('kelas', trim((string) $validated['kelas']))
            ->orderBy('nama')
            ->get();

        $attendanceByKey = $this->attendanceRowsForStudents($students, $range['start'], $range['end']);
        $summary = $this->emptySummary();

        $studentReports = $students
            ->map(function (Siswa $student) use ($attendanceByKey, $range, &$summary): array {
                $studentRows = $attendanceByKey->filter(function (Absensi $row) use ($student): bool {
                    return (string) $row->nisn === (string) $student->nisn;
                })->keyBy(fn (Absensi $row) => $this->dateKey($row->tanggal));

                $report = $this->buildStudentRows($student, $studentRows, $range['dates']);
                $this->mergeSummary($summary, $report['summary']);

                return [
                    'student' => $this->serializeStudentIdentity($student),
                    'summary' => $report['summary'],
                    'records' => $report['records'],
                ];
            })
            ->values()
            ->all();

        return $this->successResponse('Laporan absensi kelas berhasil dimuat.', [
            'kelas' => trim((string) $validated['kelas']),
            'range' => $this->rangePayload($range['start'], $range['end']),
            'summary' => $summary,
            'students' => $studentReports,
        ]);
    }

    public function allReport(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request, [
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['required', 'date_format:Y-m-d'],
            'kelas' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $range = $this->resolveDateRange($validated);
        if ($range instanceof JsonResponse) {
            return $range;
        }

        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 50);

        $students = Siswa::query()
            ->when(trim((string) ($validated['kelas'] ?? '')) !== '', function ($query) use ($validated): void {
                $query->where('kelas', trim((string) $validated['kelas']));
            })
            ->orderBy('kelas')
            ->orderBy('nama')
            ->paginate($perPage, ['*'], 'page', $page);

        $attendanceByNisn = $this->attendanceRowsForStudents($students->getCollection(), $range['start'], $range['end'])
            ->groupBy(fn (Absensi $row) => (string) $row->nisn);

        $studentsPayload = $students
            ->getCollection()
            ->map(function (Siswa $student) use ($attendanceByNisn, $range): array {
                $rows = $attendanceByNisn->get((string) $student->nisn, collect());
                $summary = $this->summaryForAttendanceRows($rows, count($range['dates']));

                return [
                    'student' => $this->serializeStudentIdentity($student),
                    'summary' => $summary,
                ];
            })
            ->values()
            ->all();

        return $this->successResponse('Laporan absensi semua siswa berhasil dimuat.', [
            'range' => $this->rangePayload($range['start'], $range['end']),
            'students' => $studentsPayload,
            'meta' => $this->paginationMeta($students),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request, [
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['required', 'date_format:Y-m-d'],
            'kelas' => ['nullable', 'string', 'max:100'],
            'nisn' => ['nullable', 'string', 'max:32'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $range = $this->resolveDateRange($validated);
        if ($range instanceof JsonResponse) {
            return $range;
        }

        $students = Siswa::query()
            ->when(trim((string) ($validated['kelas'] ?? '')) !== '', function ($query) use ($validated): void {
                $query->where('kelas', trim((string) $validated['kelas']));
            })
            ->when(trim((string) ($validated['nisn'] ?? '')) !== '', function ($query) use ($validated): void {
                $query->where('nisn', trim((string) $validated['nisn']));
            })
            ->get(['id', 'nisn', 'nama', 'kelas']);

        $totalStudents = $students->count();
        $attendanceRows = $this->attendanceRowsForStudents($students, $range['start'], $range['end']);
        $rowsByDate = $attendanceRows->groupBy(fn (Absensi $row) => $this->dateKey($row->tanggal));

        $totalSummary = $this->emptySummary();
        $byDate = [];

        foreach ($range['dates'] as $date) {
            $dateRows = $rowsByDate->get($date, collect());
            $dateSummary = $this->summaryForAttendanceRows($dateRows, 1, $totalStudents);

            $byDate[] = [
                'tanggal' => $date,
                'summary' => $dateSummary,
            ];

            $this->mergeSummary($totalSummary, $dateSummary);
        }

        return $this->successResponse('Ringkasan absensi berhasil dimuat.', [
            'range' => $this->rangePayload($range['start'], $range['end']),
            'total_students' => $totalStudents,
            'summary' => $totalSummary,
            'by_date' => $byDate,
        ]);
    }

    private function missingAttendanceList(array $validated, array $dates, int $page, int $perPage): JsonResponse
    {
        $students = Siswa::query()
            ->when(trim((string) ($validated['kelas'] ?? '')) !== '', function ($query) use ($validated): void {
                $query->where('kelas', trim((string) $validated['kelas']));
            })
            ->when(trim((string) ($validated['nisn'] ?? '')) !== '', function ($query) use ($validated): void {
                $query->where('nisn', trim((string) $validated['nisn']));
            })
            ->orderBy('kelas')
            ->orderBy('nama')
            ->get();

        $attendanceKeys = $this->attendanceRowsForStudents(
            $students,
            Carbon::parse($dates[0]),
            Carbon::parse($dates[count($dates) - 1])
        )
            ->mapWithKeys(fn (Absensi $row) => [$this->dateKey($row->tanggal) . '|' . (string) $row->nisn => true])
            ->all();

        $rows = [];
        foreach ($dates as $date) {
            foreach ($students as $student) {
                $key = $date . '|' . (string) $student->nisn;
                if (!isset($attendanceKeys[$key])) {
                    $rows[] = $this->serializeMissingAttendance($student, $date);
                }
            }
        }

        $paginated = $this->paginateArray($rows, $page, $perPage);

        return $this->successResponse('Data siswa belum absen berhasil dimuat.', [
            'range' => [
                'tanggal_mulai' => $dates[0],
                'tanggal_selesai' => $dates[count($dates) - 1],
                'total_hari' => count($dates),
            ],
            'records' => $paginated['items'],
            'meta' => $paginated['meta'],
        ]);
    }

    private function attendanceRowsForStudents(Collection $students, Carbon $start, Carbon $end): Collection
    {
        if ($students->isEmpty()) {
            return collect();
        }

        $studentIds = $students->pluck('id')->filter()->values()->all();
        $nisnList = $students->pluck('nisn')->filter()->values()->all();

        return Absensi::query()
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where(function ($query) use ($studentIds, $nisnList): void {
                if ($studentIds !== []) {
                    $query->whereIn('siswa_id', $studentIds);
                }

                if ($nisnList !== []) {
                    $method = $studentIds !== [] ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('nisn', $nisnList);
                }
            })
            ->orderBy('tanggal')
            ->orderBy('kelas')
            ->orderBy('nama')
            ->get();
    }

    private function buildStudentRows(Siswa $student, Collection $attendanceByDate, array $dates): array
    {
        $summary = $this->emptySummary();
        $records = [];

        foreach ($dates as $date) {
            $attendance = $attendanceByDate->get($date);
            $record = $attendance instanceof Absensi
                ? $this->serializeAttendance($attendance)
                : $this->serializeMissingAttendance($student, $date);

            $this->incrementSummary($summary, $record['status']);
            $records[] = $record;
        }

        return [
            'summary' => $summary,
            'records' => $records,
        ];
    }

    private function summaryForAttendanceRows(Collection $rows, int $dayCount, ?int $studentCount = null): array
    {
        $summary = $this->emptySummary();
        $seenDates = [];

        foreach ($rows as $row) {
            $date = $this->dateKey($row->tanggal);
            $seenDates[$date] = true;
            $this->incrementSummary($summary, $this->normalizeStatus($row->status));
        }

        if ($studentCount === null) {
            $expectedRecords = $dayCount;
        } else {
            $expectedRecords = $studentCount * $dayCount;
            $seenDates = $rows
                ->mapWithKeys(fn (Absensi $row) => [$this->dateKey($row->tanggal) . '|' . (string) $row->nisn => true])
                ->all();
        }

        $missing = max(0, $expectedRecords - count($seenDates));
        $summary['Belum Absen'] += $missing;
        $summary['total'] += $missing;

        return $summary;
    }

    private function serializeAttendance(Absensi $row): array
    {
        return [
            'id' => (int) $row->id,
            'tanggal' => $this->dateKey($row->tanggal),
            'siswa_id' => (int) $row->siswa_id,
            'nisn' => (string) $row->nisn,
            'nama' => (string) $row->nama,
            'kelas' => (string) $row->kelas,
            'jam_datang' => $this->formatTime($row->jam_datang),
            'jam_pulang' => $this->formatTime($row->jam_pulang),
            'keterangan' => $row->keterangan ? (string) $row->keterangan : null,
            'status' => $this->normalizeStatus($row->status),
            'is_generated' => false,
            'created_at' => $row->created_at?->toDateTimeString(),
            'updated_at' => $row->updated_at?->toDateTimeString(),
        ];
    }

    private function serializeMissingAttendance(Siswa $student, string $date): array
    {
        return [
            'id' => null,
            'tanggal' => $date,
            'siswa_id' => (int) $student->id,
            'nisn' => (string) $student->nisn,
            'nama' => (string) $student->nama,
            'kelas' => $student->kelas ? (string) $student->kelas : null,
            'jam_datang' => null,
            'jam_pulang' => null,
            'keterangan' => null,
            'status' => 'Belum Absen',
            'is_generated' => true,
            'created_at' => null,
            'updated_at' => null,
        ];
    }

    private function serializeStudentIdentity(Siswa $student): array
    {
        return [
            'id' => (int) $student->id,
            'nisn' => (string) $student->nisn,
            'nama' => (string) $student->nama,
            'kelas' => $student->kelas ? (string) $student->kelas : null,
        ];
    }

    private function applyAttendanceFilters($query, array $validated): void
    {
        $kelas = trim((string) ($validated['kelas'] ?? ''));
        if ($kelas !== '') {
            $query->where('kelas', $kelas);
        }

        $nisn = trim((string) ($validated['nisn'] ?? ''));
        if ($nisn !== '') {
            $query->where('nisn', $nisn);
        }
    }

    private function applyStatusFilter($query, string $status): void
    {
        if ($status === 'Alpa') {
            $query->whereIn('status', ['Alpa', 'Alfa', 'Alpha']);
            return;
        }

        if ($status === 'Terlambat') {
            $query->where('keterangan', 'like', '%Terlambat%');
            return;
        }

        $query->where('status', $status);
    }

    private function validatePayload(Request $request, array $rules): array|JsonResponse
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', 422, [
                'errors' => $validator->errors(),
            ]);
        }

        return $validator->validated();
    }

    private function resolveDateRange(array $validated): array|JsonResponse
    {
        $start = Carbon::createFromFormat('Y-m-d', (string) $validated['tanggal_mulai'])->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', (string) $validated['tanggal_selesai'])->startOfDay();

        if ($start->gt($end)) {
            return $this->errorResponse('Tanggal mulai tidak boleh lebih besar dari tanggal selesai.', 422);
        }

        $totalDays = ((int) $start->diffInDays($end)) + 1;
        if ($totalDays > self::REPORT_MAX_DAYS) {
            return $this->errorResponse('Rentang laporan maksimal ' . self::REPORT_MAX_DAYS . ' hari.', 422);
        }

        return [
            'start' => $start,
            'end' => $end,
            'dates' => $this->dateList($start, $end),
        ];
    }

    private function dateList(Carbon $start, Carbon $end): array
    {
        $dates = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        return $dates;
    }

    private function dateKey($date): string
    {
        if ($date instanceof Carbon) {
            return $date->toDateString();
        }

        return Carbon::parse((string) $date)->toDateString();
    }

    private function formatTime($time): ?string
    {
        $value = trim((string) ($time ?? ''));

        return $value === '' ? null : substr($value, 0, 8);
    }

    private function normalizeStatus($status): string
    {
        $value = trim((string) ($status ?? ''));
        if ($value === '') {
            return 'Belum Absen';
        }

        $lower = strtolower($value);
        if (in_array($lower, ['alfa', 'alpha', 'alpa'], true)) {
            return 'Alpa';
        }

        return match ($lower) {
            'hadir' => 'Hadir',
            'masuk' => 'Masuk',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'belum absen' => 'Belum Absen',
            'terlambat' => 'Terlambat',
            default => $value,
        };
    }

    private function normalizeStatusFilter($status): ?string
    {
        $value = trim((string) ($status ?? ''));

        return $value === '' ? null : $this->normalizeStatus($value);
    }

    private function emptySummary(): array
    {
        $summary = ['total' => 0];

        foreach (self::SUMMARY_KEYS as $key) {
            $summary[$key] = 0;
        }

        return $summary;
    }

    private function incrementSummary(array &$summary, string $status): void
    {
        $key = $this->normalizeStatus($status);
        if (!array_key_exists($key, $summary)) {
            $summary[$key] = 0;
        }

        $summary[$key]++;
        $summary['total']++;
    }

    private function mergeSummary(array &$target, array $source): void
    {
        foreach ($source as $key => $value) {
            if (!array_key_exists($key, $target)) {
                $target[$key] = 0;
            }

            $target[$key] += (int) $value;
        }
    }

    private function rangePayload(Carbon $start, Carbon $end): array
    {
        return [
            'tanggal_mulai' => $start->toDateString(),
            'tanggal_selesai' => $end->toDateString(),
            'total_hari' => ((int) $start->diffInDays($end)) + 1,
        ];
    }

    private function paginateArray(array $items, int $page, int $perPage): array
    {
        $total = count($items);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);
        $offset = ($page - 1) * $perPage;
        $pagedItems = array_slice($items, $offset, $perPage);

        return [
            'items' => $pagedItems,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => $total === 0 ? null : $offset + 1,
                'to' => $total === 0 ? null : $offset + count($pagedItems),
            ],
        ];
    }

    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    private function successResponse(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'rc' => 200,
            'data' => $data,
        ]);
    }

    private function errorResponse(string $message, int $statusCode = 422, array $data = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'rc' => $statusCode,
        ];

        if ($data !== []) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $statusCode);
    }
}
