<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Services\Modules\AlumniRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataAlumniController extends PageActionController
{
    public function __construct(
        protected AlumniRecordService $alumniRecords,
    ) {
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($this->shouldReturnJson($request)) {
            return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->alumniRecords->getAlumniList($args, $auth));
        }

        return view('pages.data-alumni');
    }

    public function store(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->alumniRecords->storeAlumni($args, $auth));
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->alumniRecords->deleteAlumni($args, $auth));
    }

    public function restore(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->alumniRecords->restoreToSiswa($args, $auth));
    }

    public function updateTracer(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->alumniRecords->updateTracer($args, $auth));
    }

    public function cetakLaporan(Request $request): View
    {
        $alumniList = $this->buildLaporanQuery($request)->get();

        $total = $alumniList->count();
        $bekerja = $alumniList->where('status_alumni', 'Bekerja')->count();
        $kuliah = $alumniList->where('status_alumni', 'Kuliah')->count();
        $wirausaha = $alumniList->where('status_alumni', 'Wirausaha')->count();
        $lainnya = $total - ($bekerja + $kuliah + $wirausaha);

        $stats = [
            'total' => $total,
            'total_bekerja' => $bekerja,
            'pct_bekerja' => $total > 0 ? round(($bekerja / $total) * 100, 1) : 0,
            'total_kuliah' => $kuliah,
            'pct_kuliah' => $total > 0 ? round(($kuliah / $total) * 100, 1) : 0,
            'total_wirausaha' => $wirausaha,
            'pct_wirausaha' => $total > 0 ? round(($wirausaha / $total) * 100, 1) : 0,
            'total_lainnya' => $lainnya,
            'pct_lainnya' => $total > 0 ? round(($lainnya / $total) * 100, 1) : 0,
        ];

        $filterInfo = [
            'tahun' => $request->tahun_lulus ?: 'Semua Tahun Lulus',
            'kelas' => $request->kelas ?: 'Semua Kelas',
            'tracer' => $request->status_alumni ?: 'Semua Status',
        ];

        return view('pdf.laporan-alumni', compact('alumniList', 'stats', 'filterInfo'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $alumniList = $this->buildLaporanQuery($request)->get();
        $fileName = 'Laporan_Alumni_TracerStudy_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($alumniList): void {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'No',
                'NISN',
                'Nama Lengkap',
                'Jenis Kelamin',
                'Kelas Terakhir',
                'Tahun Lulus',
                'Status Tracer Study',
                'Nama Instansi / Tempat Studi / Usaha',
                'Posisi / Jurusan',
                'Tahun Mulai',
                'Kontak (No HP)',
                'Alamat',
                'Keterangan',
            ]);

            foreach ($alumniList as $index => $item) {
                fputcsv($handle, [
                    $index + 1,
                    $item->nisn ?: '',
                    $item->nama ?: '',
                    $item->jenis_kelamin ?: '',
                    $item->kelas_terakhir ?: '',
                    $item->tahun_lulus ?: '',
                    $item->status_alumni ?: 'Belum Diisi',
                    $item->nama_instansi ?: '',
                    $item->jurusan_posisi ?: '',
                    $item->tahun_mulai ?: '',
                    $item->kontak ?: '',
                    $item->alamat ?: '',
                    $item->keterangan_tracer ?: '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    protected function buildLaporanQuery(Request $request)
    {
        $query = Alumni::query()->orderBy('tahun_lulus', 'desc')->orderBy('nama', 'asc');

        if ($request->filled('tahun_lulus')) {
            $query->where('tahun_lulus', (int) $request->tahun_lulus);
        }

        if ($request->filled('kelas')) {
            $query->where('kelas_terakhir', $request->kelas);
        }

        if ($request->filled('status_alumni')) {
            $status = $request->status_alumni;
            if ($status === 'Belum Diisi') {
                $query->where(function ($q) {
                    $q->whereNull('status_alumni')
                      ->orWhere('status_alumni', '')
                      ->orWhere('status_alumni', 'Belum Diisi')
                      ->orWhere('status_alumni', 'Belum Mengisi');
                });
            } else {
                $query->where('status_alumni', $status);
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('kontak', 'like', "%{$search}%")
                  ->orWhere('nama_instansi', 'like', "%{$search}%")
                  ->orWhere('jurusan_posisi', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
