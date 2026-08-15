<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PersuratanController extends Controller
{
    public function index(): View
    {
        return view('pages.persuratan');
    }

    public function data(Request $request): JsonResponse
    {
        $jenis = trim((string) $request->query('jenis', ''));
        $status = trim((string) $request->query('status', ''));
        $search = trim((string) $request->query('search', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $query = Surat::query()
            ->with(['createdBy:id,name,username', 'updatedBy:id,name,username'])
            ->when(
                in_array($jenis, [Surat::JENIS_MASUK, Surat::JENIS_KELUAR], true),
                fn ($builder) => $builder->where('jenis', $jenis)
            )
            ->when(
                in_array($status, [Surat::STATUS_AKTIF, Surat::STATUS_DIARSIPKAN], true),
                fn ($builder) => $builder->where('status', $status)
            )
            ->when($search !== '', function ($builder) use ($search): void {
                $builder->where(function ($inner) use ($search): void {
                    $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';

                    $inner
                        ->where('nomor_surat', 'like', $like)
                        ->orWhere('perihal', 'like', $like)
                        ->orWhere('asal_surat', 'like', $like)
                        ->orWhere('tujuan_surat', 'like', $like)
                        ->orWhere('ringkasan', 'like', $like);
                });
            })
            ->orderByDesc('tanggal_surat')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $rows = (clone $query)
            ->forPage($page, $perPage)
            ->get()
            ->map(fn (Surat $surat) => $this->formatRow($surat))
            ->values()
            ->all();

        $offset = ($page - 1) * $perPage;
        $from = $total === 0 ? 0 : $offset + 1;
        $to = $total === 0 ? 0 : min($offset + count($rows), $total);

        return response()->json([
            'success' => true,
            'data' => $rows,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => $from,
                'to' => $to,
            ],
            'summary' => $this->summary(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);
        $payload = $this->normalizePayload($validated);
        $payload['created_by_user_id'] = $request->user()?->id;
        $payload['updated_by_user_id'] = $request->user()?->id;

        if ($request->hasFile('lampiran')) {
            $payload = array_merge($payload, $this->storeLampiran($request));
        }

        $surat = Surat::query()->create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Data surat berhasil ditambahkan.',
            'data' => $this->formatRow($surat->load(['createdBy:id,name,username', 'updatedBy:id,name,username'])),
        ]);
    }

    public function update(Request $request, Surat $surat): JsonResponse
    {
        $validated = $this->validatePayload($request);
        $payload = $this->normalizePayload($validated);
        $payload['updated_by_user_id'] = $request->user()?->id;

        $removeLampiran = $request->boolean('hapus_lampiran');
        if ($request->hasFile('lampiran')) {
            $this->deleteLampiran($surat);
            $payload = array_merge($payload, $this->storeLampiran($request));
        } elseif ($removeLampiran) {
            $this->deleteLampiran($surat);
            $payload = array_merge($payload, $this->emptyLampiranPayload());
        }

        $surat->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Data surat berhasil diperbarui.',
            'data' => $this->formatRow($surat->load(['createdBy:id,name,username', 'updatedBy:id,name,username'])->refresh()),
        ]);
    }

    public function destroy(Surat $surat): JsonResponse
    {
        $this->deleteLampiran($surat);
        $surat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data surat berhasil dihapus.',
        ]);
    }

    public function download(Surat $surat): StreamedResponse
    {
        $path = trim((string) $surat->lampiran_path);

        abort_unless($path !== '' && Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download(
            $path,
            $surat->lampiran_nama ?: basename($path)
        );
    }

    protected function validatePayload(Request $request): array
    {
        return $request->validate([
            'jenis' => ['required', Rule::in([Surat::JENIS_MASUK, Surat::JENIS_KELUAR])],
            'nomor_surat' => ['required', 'string', 'max:100'],
            'tanggal_surat' => ['required', 'date'],
            'tanggal_diterima' => ['required_if:jenis,masuk', 'nullable', 'date'],
            'tanggal_dikirim' => ['required_if:jenis,keluar', 'nullable', 'date'],
            'asal_surat' => ['required_if:jenis,masuk', 'nullable', 'string', 'max:191'],
            'tujuan_surat' => ['required_if:jenis,keluar', 'nullable', 'string', 'max:191'],
            'perihal' => ['required', 'string', 'max:191'],
            'ringkasan' => ['nullable', 'string', 'max:3000'],
            'status' => ['required', Rule::in([Surat::STATUS_AKTIF, Surat::STATUS_DIARSIPKAN])],
            'hapus_lampiran' => ['nullable', 'boolean'],
            'lampiran' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
        ], [
            'tanggal_diterima.required_if' => 'Tanggal diterima wajib diisi untuk surat masuk.',
            'tanggal_dikirim.required_if' => 'Tanggal dikirim wajib diisi untuk surat keluar.',
            'asal_surat.required_if' => 'Asal surat wajib diisi untuk surat masuk.',
            'tujuan_surat.required_if' => 'Tujuan surat wajib diisi untuk surat keluar.',
            'lampiran.max' => 'Ukuran lampiran maksimal 5 MB.',
            'lampiran.mimes' => 'Lampiran harus berupa PDF, gambar, Word, atau Excel.',
        ]);
    }

    protected function normalizePayload(array $validated): array
    {
        $jenis = (string) $validated['jenis'];

        return [
            'jenis' => $jenis,
            'nomor_surat' => trim((string) $validated['nomor_surat']),
            'tanggal_surat' => $validated['tanggal_surat'],
            'tanggal_diterima' => $jenis === Surat::JENIS_MASUK ? ($validated['tanggal_diterima'] ?? null) : null,
            'tanggal_dikirim' => $jenis === Surat::JENIS_KELUAR ? ($validated['tanggal_dikirim'] ?? null) : null,
            'asal_surat' => $jenis === Surat::JENIS_MASUK ? $this->nullableString($validated['asal_surat'] ?? null) : null,
            'tujuan_surat' => $jenis === Surat::JENIS_KELUAR ? $this->nullableString($validated['tujuan_surat'] ?? null) : null,
            'perihal' => trim((string) $validated['perihal']),
            'ringkasan' => $this->nullableString($validated['ringkasan'] ?? null),
            'status' => (string) $validated['status'],
        ];
    }

    protected function storeLampiran(Request $request): array
    {
        $file = $request->file('lampiran');
        if (!$file) {
            return [];
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $path = $file->storeAs(
            'surat-lampiran/' . now()->format('Y'),
            Str::uuid()->toString() . ($extension !== '' ? '.' . $extension : ''),
            'public'
        );

        return [
            'lampiran_path' => $path,
            'lampiran_nama' => $file->getClientOriginalName(),
            'lampiran_mime' => $file->getClientMimeType(),
            'lampiran_size' => $file->getSize(),
        ];
    }

    protected function deleteLampiran(Surat $surat): void
    {
        $path = trim((string) $surat->lampiran_path);
        if ($path !== '') {
            Storage::disk('public')->delete($path);
        }
    }

    protected function emptyLampiranPayload(): array
    {
        return [
            'lampiran_path' => null,
            'lampiran_nama' => null,
            'lampiran_mime' => null,
            'lampiran_size' => null,
        ];
    }

    protected function summary(): array
    {
        return [
            'masuk' => Surat::query()->where('jenis', Surat::JENIS_MASUK)->count(),
            'keluar' => Surat::query()->where('jenis', Surat::JENIS_KELUAR)->count(),
            'diarsipkan' => Surat::query()->where('status', Surat::STATUS_DIARSIPKAN)->count(),
        ];
    }

    protected function formatRow(Surat $surat): array
    {
        $isMasuk = $surat->jenis === Surat::JENIS_MASUK;
        $agendaDate = $isMasuk ? $surat->tanggal_diterima : $surat->tanggal_dikirim;
        $pihak = $isMasuk ? $surat->asal_surat : $surat->tujuan_surat;

        return [
            'id' => (int) $surat->id,
            'jenis' => (string) $surat->jenis,
            'jenis_label' => $isMasuk ? 'Surat Masuk' : 'Surat Keluar',
            'nomor_surat' => (string) $surat->nomor_surat,
            'tanggal_surat' => $this->dateValue($surat->tanggal_surat),
            'tanggal_surat_label' => $this->dateLabel($surat->tanggal_surat),
            'tanggal_diterima' => $this->dateValue($surat->tanggal_diterima),
            'tanggal_dikirim' => $this->dateValue($surat->tanggal_dikirim),
            'tanggal_agenda_label' => $this->dateLabel($agendaDate),
            'asal_surat' => (string) ($surat->asal_surat ?? ''),
            'tujuan_surat' => (string) ($surat->tujuan_surat ?? ''),
            'pihak_label' => (string) ($pihak ?: '-'),
            'pihak_title' => $isMasuk ? 'Asal Surat' : 'Tujuan Surat',
            'perihal' => (string) $surat->perihal,
            'ringkasan' => (string) ($surat->ringkasan ?? ''),
            'status' => (string) $surat->status,
            'status_label' => $surat->status === Surat::STATUS_DIARSIPKAN ? 'Diarsipkan' : 'Aktif',
            'lampiran_path' => (string) ($surat->lampiran_path ?? ''),
            'lampiran_nama' => (string) ($surat->lampiran_nama ?? ''),
            'lampiran_size_label' => $surat->lampiran_size ? $this->formatBytes((int) $surat->lampiran_size) : '',
            'download_url' => $surat->lampiran_path ? route('persuratan.download', ['surat' => $surat]) : '',
            'created_by_name' => (string) ($surat->createdBy?->name ?: ($surat->createdBy?->username ?? '-')),
            'updated_by_name' => (string) ($surat->updatedBy?->name ?: ($surat->updatedBy?->username ?? '-')),
        ];
    }

    protected function dateValue(?CarbonInterface $date): string
    {
        return $date ? $date->format('Y-m-d') : '';
    }

    protected function dateLabel(?CarbonInterface $date): string
    {
        return $date ? $date->copy()->locale('id')->translatedFormat('d M Y') : '-';
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return number_format($bytes / (1024 * 1024), 2) . ' MB';
    }

    protected function nullableString($value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text === '' ? null : $text;
    }
}
