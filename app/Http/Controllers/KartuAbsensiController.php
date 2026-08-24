<?php

namespace App\Http\Controllers;

use App\Models\KartuAbsensi;
use App\Models\Siswa;
use App\Models\User;
use App\Services\AttendanceCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KartuAbsensiController extends Controller
{
    public function index(): View
    {
        [$cards, $students, $teachers, $teachersByCard] = $this->getPageData();

        return view('pages.kartu-absensi', [
            'cards' => $cards,
            'students' => $students,
            'teachers' => $teachers,
            'teachersByCard' => $teachersByCard,
            'cardRecords' => $cards->map(fn (KartuAbsensi $card) => $this->serializeCard($card, $teachersByCard))->values()->all(),
            'studentRecords' => $students->map(fn (Siswa $student) => $this->serializeStudent($student))->values()->all(),
            'teacherRecords' => $teachers->map(fn (User $teacher) => $this->serializeTeacher($teacher))->values()->all(),
            'dataUrl' => route('kartu-absensi.data'),
            'streamUrl' => route('kartu-absensi.stream'),
            'storeUrl' => route('kartu-absensi.store'),
            'updateUrlTemplate' => route('kartu-absensi.update', ['kartuAbsensi' => '__ID__']),
        ]);
    }

    public function data(): JsonResponse
    {
        [$cards, $students, $teachers, $teachersByCard] = $this->getPageData();

        return response()->json([
            'success' => true,
            'data' => $this->serializePayload($cards, $students, $teachers, $teachersByCard),
        ]);
    }

    public function stream(): StreamedResponse
    {
        return response()->stream(function (): void {
            $this->prepareSseRuntime();

            $lastFingerprint = null;
            $heartbeatTicks = 0;

            echo "retry: 3000\n\n";
            $this->sendSsePadding();
            $this->flushSseBuffer();

            while (!connection_aborted()) {
                [$cards, $students, $teachers, $teachersByCard] = $this->getPageData();
                $payload = $this->serializePayload($cards, $students, $teachers, $teachersByCard);
                $fingerprint = sha1($this->encodeSsePayload($payload));

                if ($lastFingerprint !== $fingerprint) {
                    $lastFingerprint = $fingerprint;
                    $heartbeatTicks = 0;
                    $this->sendSseEvent('sync', $payload);
                } else {
                    $heartbeatTicks++;

                    if ($heartbeatTicks >= 5) {
                        $this->sendSseComment('heartbeat');
                        $heartbeatTicks = 0;
                    }
                }

                sleep(3);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, no-transform',
            'Pragma' => 'no-cache',
            'Connection' => 'keep-alive',
            'Content-Encoding' => 'none',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function captureStream(): StreamedResponse
    {
        return response()->stream(function (): void {
            $this->prepareSseRuntime();

            $baseline = $this->getLatestCaptureSnapshot();
            $baselineFingerprint = $baseline === null
                ? null
                : sha1($this->encodeSsePayload($baseline));

            $attempts = 0;
            $maxAttempts = 15;

            echo "retry: 3000\n\n";
            $this->sendSsePadding();
            $this->flushSseBuffer();

            while (!connection_aborted() && $attempts < $maxAttempts) {
                $snapshot = $this->getLatestCaptureSnapshot();
                $snapshotFingerprint = $snapshot === null
                    ? null
                    : sha1($this->encodeSsePayload($snapshot));

                if ($snapshot !== null && $snapshotFingerprint !== null && $snapshotFingerprint !== $baselineFingerprint) {
                    $this->sendSseEvent('captured', $snapshot);

                    return;
                }

                $attempts++;

                if ($attempts % 5 === 0) {
                    $this->sendSseComment('waiting');
                }

                sleep(3);
            }

            $this->sendSseEvent('timeout', [
                'message' => 'Waktu tunggu scan kartu habis.',
            ]);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, no-transform',
            'Pragma' => 'no-cache',
            'Connection' => 'keep-alive',
            'Content-Encoding' => 'none',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function startCapture(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'anchor_fingerprint' => $this->getLatestCaptureFingerprint(),
            'timeout_seconds' => 30,
            'poll_interval_ms' => 1000,
            'message' => 'Siap menunggu scan kartu baru.',
        ]);
    }

    public function pollCapture(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'after_fingerprint' => ['nullable', 'string', 'max:80'],
        ]);

        $afterFingerprint = trim((string) ($validated['after_fingerprint'] ?? ''));
        $snapshot = $this->getLatestCaptureSnapshot();
        $fingerprint = $snapshot === null ? '' : $this->fingerprintCaptureSnapshot($snapshot);

        if ($snapshot === null || $fingerprint === '' || $fingerprint === $afterFingerprint) {
            return response()->json([
                'success' => true,
                'found' => false,
                'after_fingerprint' => $afterFingerprint,
            ]);
        }

        return response()->json([
            'success' => true,
            'found' => true,
            'after_fingerprint' => $fingerprint,
            'message' => 'Kartu berhasil terdeteksi.',
            'data' => $snapshot,
        ]);
    }

    public function store(Request $request, AttendanceCardService $cardService): JsonResponse|RedirectResponse
    {
        $validated = $this->validateCard($request);
        $code = $cardService->normalizeCode($validated['code']);
        $ownerTarget = trim((string) ($request->input('owner_target') ?? ''));

        $siswaId = null;
        $guruId = null;

        if (str_starts_with($ownerTarget, 'siswa_')) {
            $siswaId = (int) str_replace('siswa_', '', $ownerTarget);
        } elseif (str_starts_with($ownerTarget, 'guru_')) {
            $guruId = (int) str_replace('guru_', '', $ownerTarget);
        } elseif (!empty($validated['siswa_id'])) {
            $siswaId = (int) $validated['siswa_id'];
        }

        $card = KartuAbsensi::query()->create([
            'type' => KartuAbsensi::TYPE_RFID,
            'code' => $code,
            'siswa_id' => $siswaId,
        ]);

        if ($guruId > 0) {
            User::query()->where('nomor_kartu', $code)->update(['nomor_kartu' => null]);
            $teacher = User::query()->find($guruId);
            if ($teacher) {
                $teacher->nomor_kartu = $code;
                $teacher->save();
            }
        } elseif ($siswaId > 0) {
            User::query()->where('nomor_kartu', $code)->update(['nomor_kartu' => null]);
        }

        $card->load('siswa');
        [$cards, $students, $teachers, $teachersByCard] = $this->getPageData();

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Kartu absensi berhasil ditambahkan.',
                'data' => $this->serializeCard($card, $teachersByCard),
            ], 201);
        }

        return redirect()
            ->route('kartu-absensi.index')
            ->with('success', 'Kartu absensi berhasil ditambahkan.');
    }

    public function update(Request $request, KartuAbsensi $kartuAbsensi): JsonResponse|RedirectResponse
    {
        $this->ensureManagedCard($kartuAbsensi);

        $ownerTarget = trim((string) ($request->input('owner_target') ?? ''));
        $siswaId = null;
        $guruId = null;

        if (str_starts_with($ownerTarget, 'siswa_')) {
            $siswaId = (int) str_replace('siswa_', '', $ownerTarget);
        } elseif (str_starts_with($ownerTarget, 'guru_')) {
            $guruId = (int) str_replace('guru_', '', $ownerTarget);
        } elseif ($request->has('siswa_id')) {
            $rawSiswaId = $request->input('siswa_id');
            if (!empty($rawSiswaId)) {
                $siswaId = (int) $rawSiswaId;
            }
        }

        if ($guruId > 0) {
            $kartuAbsensi->siswa_id = null;
            $kartuAbsensi->save();

            User::query()->where('nomor_kartu', $kartuAbsensi->code)->where('id', '!=', $guruId)->update(['nomor_kartu' => null]);
            $teacher = User::query()->find($guruId);
            if ($teacher) {
                $teacher->nomor_kartu = $kartuAbsensi->code;
                $teacher->save();
            }
        } elseif ($siswaId > 0) {
            $kartuAbsensi->siswa_id = $siswaId;
            $kartuAbsensi->save();

            User::query()->where('nomor_kartu', $kartuAbsensi->code)->update(['nomor_kartu' => null]);
        } else {
            $kartuAbsensi->siswa_id = null;
            $kartuAbsensi->save();

            User::query()->where('nomor_kartu', $kartuAbsensi->code)->update(['nomor_kartu' => null]);
        }

        $kartuAbsensi->load('siswa');
        [$cards, $students, $teachers, $teachersByCard] = $this->getPageData();

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Kartu absensi berhasil diperbarui.',
                'data' => $this->serializeCard($kartuAbsensi, $teachersByCard),
            ]);
        }

        return redirect()
            ->route('kartu-absensi.index')
            ->with('success', 'Kartu absensi berhasil diperbarui.');
    }

    public function destroy(Request $request, KartuAbsensi $kartuAbsensi): JsonResponse|RedirectResponse
    {
        $this->ensureManagedCard($kartuAbsensi);

        $deletedId = $kartuAbsensi->id;
        $code = $kartuAbsensi->code;
        $kartuAbsensi->delete();

        // Also detach from any teacher
        User::query()->where('nomor_kartu', $code)->update(['nomor_kartu' => null]);

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Kartu absensi berhasil dihapus.',
                'data' => [
                    'id' => $deletedId,
                ],
            ]);
        }

        return redirect()
            ->route('kartu-absensi.index')
            ->with('success', 'Kartu absensi berhasil dihapus.');
    }

    protected function validateCard(Request $request, ?KartuAbsensi $card = null): array
    {
        $type = $card?->type ?? KartuAbsensi::TYPE_RFID;

        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kartu_absensi', 'code')
                    ->where(fn ($query) => $query->where('type', $type))
                    ->ignore($card?->id),
            ],
            'siswa_id' => [
                'nullable',
            ],
        ], [
            'code.unique' => 'Kode kartu sudah terdaftar.',
        ]);
    }

    protected function ensureManagedCard(KartuAbsensi $card): void
    {
        abort_unless($card->type === KartuAbsensi::TYPE_RFID, 404);
    }

    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    protected function serializeCard(KartuAbsensi $card, array $teachersByCard = []): array
    {
        $code = strtoupper(trim((string) $card->code));
        $teacher = $card->siswa_id ? null : ($teachersByCard[$code] ?? null);

        $ownerType = 'unlinked';
        $ownerName = null;
        $ownerIdentifier = null;
        $ownerClass = null;

        if ($card->siswa) {
            $ownerType = 'siswa';
            $ownerName = $card->siswa->nama;
            $ownerIdentifier = $card->siswa->nisn;
            $ownerClass = $card->siswa->kelas;
        } elseif ($teacher) {
            $ownerType = 'guru';
            $ownerName = $teacher->name;
            $ownerIdentifier = $teacher->username;
            $ownerClass = $teacher->jabatan ?: 'Guru & Staf';
        }

        return [
            'id' => $card->id,
            'code' => $card->code,
            'siswa_id' => $card->siswa_id,
            'guru_id' => $teacher?->id,
            'owner_type' => $ownerType,
            'owner_name' => $ownerName,
            'owner_identifier' => $ownerIdentifier,
            'owner_class' => $ownerClass,
            'student_name' => $ownerName,
            'student_nisn' => $ownerIdentifier,
            'student_class' => $ownerClass,
            'last_scanned_at' => $card->last_scanned_at?->toIso8601String(),
            'last_scanned_date' => $card->last_scanned_at?->format('d M Y'),
            'last_scanned_time' => $card->last_scanned_at?->format('H:i'),
            'last_scanned_source' => $card->last_scanned_source,
        ];
    }

    protected function serializeStudent(Siswa $student): array
    {
        return [
            'id' => $student->id,
            'target_key' => 'siswa_' . $student->id,
            'nama' => $student->nama,
            'nisn' => $student->nisn,
            'kelas' => $student->kelas,
            'type' => 'siswa',
        ];
    }

    protected function serializeTeacher(User $teacher): array
    {
        return [
            'id' => $teacher->id,
            'target_key' => 'guru_' . $teacher->id,
            'nama' => $teacher->name,
            'username' => $teacher->username,
            'jabatan' => $teacher->jabatan ?: 'Guru & Staf',
            'type' => 'guru',
        ];
    }

    protected function serializePayload($cards, $students, $teachers, $teachersByCard): array
    {
        return [
            'cards' => $cards->map(fn (KartuAbsensi $card) => $this->serializeCard($card, $teachersByCard))->values()->all(),
            'students' => $students->map(fn (Siswa $student) => $this->serializeStudent($student))->values()->all(),
            'teachers' => $teachers->map(fn (User $teacher) => $this->serializeTeacher($teacher))->values()->all(),
        ];
    }

    protected function sendSseEvent(string $event, array $payload): void
    {
        echo 'event: ' . $event . "\n";
        echo 'data: ' . $this->encodeSsePayload($payload) . "\n\n";

        $this->flushSseBuffer();
    }

    protected function sendSseComment(string $comment): void
    {
        echo ': ' . $comment . "\n\n";

        $this->flushSseBuffer();
    }

    protected function sendSsePadding(): void
    {
        echo ': ' . str_repeat(' ', 2048) . "\n\n";
    }

    protected function prepareSseRuntime(): void
    {
        ignore_user_abort(true);

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');

        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }

        @ob_implicit_flush(true);
    }

    protected function encodeSsePayload(array $payload): string
    {
        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encoded === false ? '{}' : $encoded;
    }

    protected function flushSseBuffer(): void
    {
        while (ob_get_level() > 0) {
            @ob_end_flush();
        }

        flush();
    }

    protected function getLatestCaptureSnapshot(): ?array
    {
        $card = KartuAbsensi::query()
            ->where('type', KartuAbsensi::TYPE_RFID)
            ->whereNotNull('last_scanned_at')
            ->where('last_scanned_source', 'device')
            ->orderByDesc('last_scanned_at')
            ->orderByDesc('id')
            ->first();

        if (!$card) {
            return null;
        }

        return [
            'id' => $card->id,
            'code' => $card->code,
            'last_scanned_at' => $card->last_scanned_at?->toIso8601String(),
            'last_scanned_source' => $card->last_scanned_source,
        ];
    }

    protected function getLatestCaptureFingerprint(): string
    {
        $snapshot = $this->getLatestCaptureSnapshot();

        if ($snapshot === null) {
            return '';
        }

        return $this->fingerprintCaptureSnapshot($snapshot);
    }

    protected function fingerprintCaptureSnapshot(array $snapshot): string
    {
        return sha1($this->encodeSsePayload($snapshot));
    }

    protected function getPageData(): array
    {
        // Auto-sync: Pastikan semua Guru yang memiliki nomor_kartu terdaftar di tabel kartu_absensi
        $teachersWithCard = User::query()
            ->whereNotNull('nomor_kartu')
            ->where('nomor_kartu', '!=', '')
            ->get(['id', 'name', 'username', 'nomor_kartu', 'jabatan']);

        foreach ($teachersWithCard as $teacher) {
            $code = strtoupper(trim((string) $teacher->nomor_kartu));
            if ($code !== '') {
                KartuAbsensi::query()->firstOrCreate(
                    ['code' => $code, 'type' => KartuAbsensi::TYPE_RFID],
                    ['siswa_id' => null]
                );
            }
        }

        $cards = KartuAbsensi::query()
            ->where('type', KartuAbsensi::TYPE_RFID)
            ->with('siswa')
            ->orderByDesc('last_scanned_at')
            ->orderByDesc('id')
            ->get();

        $students = Siswa::query()
            ->orderBy('nama')
            ->get(['id', 'nama', 'nisn', 'kelas']);

        $teachers = User::query()
            ->where(function ($q) {
                $q->where('status', 'Aktif')
                  ->orWhereNotNull('nomor_kartu');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'nomor_kartu', 'jabatan']);

        $teachersByCard = [];
        foreach ($teachers as $teacher) {
            $code = strtoupper(trim((string) ($teacher->nomor_kartu ?? '')));
            if ($code !== '') {
                $teachersByCard[$code] = $teacher;
            }
        }

        return [$cards, $students, $teachers, $teachersByCard];
    }
}
