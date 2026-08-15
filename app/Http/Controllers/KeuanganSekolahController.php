<?php

namespace App\Http\Controllers;

use App\Models\PosKeuangan;
use App\Models\Siswa;
use App\Models\TagihanSiswa;
use App\Models\TransaksiKeuangan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KeuanganSekolahController extends Controller
{
    // ==========================================
    // 1. POS / KATEGORI KEUANGAN DINAMIS
    // ==========================================

    public function indexPos(): View
    {
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara', 'kepsek'])) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin melihat kategori pos keuangan.');
        }
        return view('pages.keuangan-pos');
    }

    public function dataPos(): JsonResponse
    {
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara', 'kepsek'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak.'], 403);
        }

        $pos = PosKeuangan::query()
            ->withCount('tagihan')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pos,
        ]);
    }

    public function storePos(Request $request): JsonResponse
    {
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Hanya Super Admin dan Admin yang dapat menambah pos keuangan.'], 403);
        }

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:30', 'unique:pos_keuangan,kode'],
            'nama' => ['required', 'string', 'max:100'],
            'tipe' => ['required', 'in:bulanan,bebas,sekali_bayar'],
            'nominal_default' => ['required', 'numeric', 'min:0'],
            'tahun_ajaran' => ['nullable', 'string', 'max:20'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
        ]);

        $pos = PosKeuangan::create([
            'kode' => strtoupper(trim($validated['kode'])),
            'nama' => trim($validated['nama']),
            'tipe' => $validated['tipe'],
            'nominal_default' => $validated['nominal_default'],
            'tahun_ajaran' => $validated['tahun_ajaran'] ?? '2026/2027',
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori pos keuangan berhasil ditambahkan.',
            'data' => $pos,
        ]);
    }

    public function updatePos(Request $request, PosKeuangan $posKeuangan): JsonResponse
    {
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Hanya Super Admin dan Admin yang dapat mengubah pos keuangan.'], 403);
        }
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:30', 'unique:pos_keuangan,kode,' . $posKeuangan->id],
            'nama' => ['required', 'string', 'max:100'],
            'tipe' => ['required', 'in:bulanan,bebas,sekali_bayar'],
            'nominal_default' => ['required', 'numeric', 'min:0'],
            'tahun_ajaran' => ['nullable', 'string', 'max:20'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $posKeuangan->update([
            'kode' => strtoupper(trim($validated['kode'])),
            'nama' => trim($validated['nama']),
            'tipe' => $validated['tipe'],
            'nominal_default' => $validated['nominal_default'],
            'tahun_ajaran' => $validated['tahun_ajaran'] ?? $posKeuangan->tahun_ajaran,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $posKeuangan->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori pos keuangan berhasil diperbarui.',
            'data' => $posKeuangan,
        ]);
    }

    public function destroyPos(PosKeuangan $posKeuangan): JsonResponse
    {
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Hanya Super Admin dan Admin yang dapat menghapus pos keuangan.'], 403);
        }

        $posKeuangan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori pos keuangan berhasil dihapus.',
        ]);
    }

    // ==========================================
    // 2. PEMBAYARAN & TRANSAKSI SISWA
    // ==========================================

    public function indexPembayaran(): View
    {
        $this->ensureBillingSynced();
        $posList = PosKeuangan::where('is_active', true)->orderBy('nama')->get();
        $kelasList = Siswa::query()->whereNotNull('kelas')->distinct()->pluck('kelas');
        return view('pages.keuangan-pembayaran', compact('posList', 'kelasList'));
    }

    public function ensureBillingSynced(): void
    {
        $posAktif = PosKeuangan::where('is_active', true)->get();
        if ($posAktif->isEmpty()) return;

        $siswaList = Siswa::all();
        if ($siswaList->isEmpty()) return;

        $bulanList = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];

        foreach ($siswaList as $siswa) {
            foreach ($posAktif as $pos) {
                if ($pos->tipe === 'bulanan') {
                    foreach ($bulanList as $bln) {
                        TagihanSiswa::firstOrCreate(
                            [
                                'pos_keuangan_id' => $pos->id,
                                'siswa_id' => $siswa->id,
                                'bulan' => $bln,
                                'tahun_ajaran' => $pos->tahun_ajaran ?: '2026/2027',
                            ],
                            [
                                'nominal' => $pos->nominal_default,
                                'terbayar' => 0,
                                'sisa' => $pos->nominal_default,
                                'status' => 'belum_bayar',
                            ]
                        );
                    }
                } else {
                    TagihanSiswa::firstOrCreate(
                        [
                            'pos_keuangan_id' => $pos->id,
                            'siswa_id' => $siswa->id,
                            'tahun_ajaran' => $pos->tahun_ajaran ?: '2026/2027',
                        ],
                        [
                            'nominal' => $pos->nominal_default,
                            'terbayar' => 0,
                            'sisa' => $pos->nominal_default,
                            'status' => 'belum_bayar',
                        ]
                    );
                }
            }
        }
    }

    public function syncSemuaTagihan(): JsonResponse
    {
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak.'], 403);
        }

        $this->ensureBillingSynced();

        return response()->json([
            'success' => true,
            'message' => 'Semua tagihan siswa berhasil disinkronisasi.',
        ]);
    }

    public function dataPembayaran(Request $request): JsonResponse
    {
        $this->ensureBillingSynced();

        $user = auth()->user();
        $query = TagihanSiswa::query()->with(['siswa', 'posKeuangan']);

        // JIKA ROLE SISWA: HANYA BOLEH MELIHAT DATA TAGIHAN SENDIRI
        if ($user && $user->hasRole('siswa')) {
            $nisn = $user->username;
            $nama = $user->name;
            $query->whereHas('siswa', function ($q) use ($nisn, $nama) {
                $q->where('nisn', $nisn)->orWhere('nama', $nama);
            });
        } elseif ($user && $user->hasRole('wakel') && !empty($user->kelas)) {
            // WALI KELAS: HANYA BOLEH MELIHAT KELASNYA
            $query->whereHas('siswa', function ($q) use ($user) {
                $q->where('kelas', $user->kelas);
            });
        }

        if ($request->filled('kelas')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        if ($request->filled('pos_id')) {
            $query->where('pos_keuangan_id', $request->pos_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $tagihanList = $query->orderByDesc('id')->paginate($request->input('per_page', 20));

        $totalPemasukan = (float) TransaksiKeuangan::sum('nominal_bayar');
        $totalLunas = TagihanSiswa::where('status', 'lunas')->count();
        $totalSiswa = Siswa::count();
        $totalTunggakan = (float) TagihanSiswa::where('status', '!=', 'lunas')->sum('sisa');

        return response()->json([
            'success' => true,
            'data' => $tagihanList->items(),
            'meta' => [
                'current_page' => $tagihanList->currentPage(),
                'last_page' => $tagihanList->lastPage(),
                'total' => $tagihanList->total(),
            ],
            'summary' => [
                'total_pemasukan' => $totalPemasukan,
                'tagihan_lunas' => $totalLunas,
                'siswa_tercakup' => $totalSiswa,
                'total_tunggakan' => $totalTunggakan,
            ],
        ]);
    }

    public function cariSiswa(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));
        if ($query === '') {
            return response()->json(['success' => true, 'data' => []]);
        }

        $siswa = Siswa::query()
            ->where('nama', 'like', "%{$query}%")
            ->orWhere('nisn', 'like', "%{$query}%")
            ->take(10)
            ->get(['id', 'nama', 'nisn', 'kelas', 'jenis_kelamin']);

        return response()->json([
            'success' => true,
            'data' => $siswa,
        ]);
    }

    public function tagihanSiswa(Siswa $siswa): JsonResponse
    {
        // Pastikan tagihan default pos keuangan dibuat jika belum ada
        $posAktif = PosKeuangan::where('is_active', true)->get();
        $bulanList = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];

        foreach ($posAktif as $pos) {
            if ($pos->tipe === 'bulanan') {
                foreach ($bulanList as $bln) {
                    TagihanSiswa::firstOrCreate(
                        [
                            'pos_keuangan_id' => $pos->id,
                            'siswa_id' => $siswa->id,
                            'bulan' => $bln,
                            'tahun_ajaran' => $pos->tahun_ajaran ?: '2026/2027',
                        ],
                        [
                            'nominal' => $pos->nominal_default,
                            'terbayar' => 0,
                            'sisa' => $pos->nominal_default,
                            'status' => 'belum_bayar',
                        ]
                    );
                }
            } else {
                TagihanSiswa::firstOrCreate(
                    [
                        'pos_keuangan_id' => $pos->id,
                        'siswa_id' => $siswa->id,
                        'tahun_ajaran' => $pos->tahun_ajaran ?: '2026/2027',
                    ],
                    [
                        'nominal' => $pos->nominal_default,
                        'terbayar' => 0,
                        'sisa' => $pos->nominal_default,
                        'status' => 'belum_bayar',
                    ]
                );
            }
        }

        $tagihan = TagihanSiswa::query()
            ->where('siswa_id', $siswa->id)
            ->with('posKeuangan')
            ->orderBy('id')
            ->get();

        $riwayatTransaksi = TransaksiKeuangan::query()
            ->where('siswa_id', $siswa->id)
            ->with('posKeuangan')
            ->orderByDesc('id')
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'siswa' => $siswa,
            'tagihan' => $tagihan,
            'transaksi' => $riwayatTransaksi,
        ]);
    }

    public function bayarTagihan(Request $request): JsonResponse
    {
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk menginput pembayaran kasir. Hanya Bendahara dan Admin yang diperbolehkan.'
            ], 403);
        }

        $validated = $request->validate([
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tagihan_id' => ['required', 'exists:tagihan_siswa,id'],
            'nominal_bayar' => ['required', 'numeric', 'min:1000'],
            'metode_pembayaran' => ['nullable', 'string', 'max:30'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $tagihan = TagihanSiswa::with('posKeuangan', 'siswa')->findOrFail($validated['tagihan_id']);
        $nominalBayar = (float) $validated['nominal_bayar'];

        if ($nominalBayar > $tagihan->sisa) {
            $nominalBayar = (float) $tagihan->sisa;
        }

        $transaksi = DB::transaction(function () use ($validated, $tagihan, $nominalBayar) {
            $nomorTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $trx = TransaksiKeuangan::create([
                'nomor_transaksi' => $nomorTransaksi,
                'siswa_id' => $tagihan->siswa_id,
                'pos_keuangan_id' => $tagihan->pos_keuangan_id,
                'tagihan_siswa_id' => $tagihan->id,
                'nominal_bayar' => $nominalBayar,
                'tanggal_bayar' => Carbon::today(),
                'metode_pembayaran' => $validated['metode_pembayaran'] ?? 'Tunai',
                'keterangan' => $validated['keterangan'] ?? ('Pembayaran ' . $tagihan->posKeuangan->nama . ($tagihan->bulan ? ' (' . $tagihan->bulan . ')' : '')),
                'user_id' => auth()->id(),
            ]);

            $newTerbayar = $tagihan->terbayar + $nominalBayar;
            $newSisa = max(0, $tagihan->nominal - $newTerbayar);
            $newStatus = $newSisa <= 0 ? 'lunas' : ($newTerbayar > 0 ? 'cicilan' : 'belum_bayar');

            $tagihan->update([
                'terbayar' => $newTerbayar,
                'sisa' => $newSisa,
                'status' => $newStatus,
            ]);

            return $trx;
        });

        // Kirim notifikasi WhatsApp otomatis ke nomor siswa
        try {
            $siswa = $tagihan->siswa;
            if ($siswa && !empty($siswa->no_hp)) {
                $waService = app(\App\Services\WaGatewayService::class);
                $nominalStr = 'Rp ' . number_format($transaksi->nominal_bayar, 0, ',', '.');
                $posNama = ($tagihan->posKeuangan->nama ?? 'Keuangan') . ($tagihan->bulan ? ' (' . $tagihan->bulan . ')' : '');
                $sisaStr = $tagihan->sisa <= 0 ? 'LUNAS' : ('Rp ' . number_format($tagihan->sisa, 0, ',', '.'));
                $notaUrl = url('/keuangan/kuitansi/' . $transaksi->id);

                $pesan = "*BUKTI PEMBAYARAN RESMI*\n"
                       . "*SMK NURUL HIDAYAH*\n"
                       . "------------------------------------\n"
                       . "No. Nota     : {$transaksi->nomor_transaksi}\n"
                       . "Nama Siswa   : {$siswa->nama}\n"
                       . "NISN / Kelas : {$siswa->nisn} ({$siswa->kelas})\n"
                       . "Tanggal      : " . Carbon::parse($transaksi->tanggal_bayar)->translatedFormat('d F Y') . "\n"
                       . "------------------------------------\n"
                       . "Jenis Bayar  : {$posNama}\n"
                       . "Jumlah Bayar : *{$nominalStr}*\n"
                       . "Metode       : {$transaksi->metode_pembayaran}\n"
                       . "Sisa Tagihan : *{$sisaStr}*\n"
                       . "------------------------------------\n"
                       . "📄 *Lihat / Unduh Nota Struk Digital:*\n"
                       . "{$notaUrl}\n\n"
                       . "_Terima kasih, pembayaran telah kami terima dan tercatat secara resmi di sistem sekolah._";

                $waService->sendCustomMessage($siswa->no_hp, $pesan);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal kirim WA pembayaran: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil disimpan dan dikirim ke WA siswa.',
            'data' => $transaksi,
        ]);
    }

    public function cetakKuitansi(TransaksiKeuangan $transaksi): View
    {
        $transaksi->load(['siswa', 'posKeuangan', 'tagihan', 'user']);
        return view('pdf.kuitansi-keuangan', compact('transaksi'));
    }

    // ==========================================
    // 3. LAPORAN & REKAP KEUANGAN
    // ==========================================

    public function indexLaporan(): View
    {
        $user = auth()->user();
        if ($user && $user->hasRole('siswa')) {
            abort(403, 'Akses Ditolak: Siswa tidak memiliki akses ke laporan rekap kas sekolah.');
        }

        $posList = PosKeuangan::orderBy('nama')->get();
        $kelasList = Siswa::query()->whereNotNull('kelas')->distinct()->pluck('kelas');
        return view('pages.keuangan-laporan', compact('posList', 'kelasList'));
    }

    public function dataLaporan(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user && $user->hasRole('siswa')) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Anda tidak memiliki izin.'], 403);
        }

        $query = TransaksiKeuangan::query()->with(['siswa', 'posKeuangan', 'tagihan', 'user']);

        if ($user && $user->hasRole('wakel') && !empty($user->kelas)) {
            $query->whereHas('siswa', fn ($q) => $q->where('kelas', $user->kelas));
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_bayar', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_bayar', '<=', $request->tanggal_selesai);
        }

        if ($request->filled('kelas')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        if ($request->filled('pos_id')) {
            $query->where('pos_keuangan_id', $request->pos_id);
        }

        if ($request->filled('metode')) {
            $query->where('metode_pembayaran', $request->metode);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('siswa', function ($sq) use ($search) {
                      $sq->where('nama', 'like', "%{$search}%")
                         ->orWhere('nisn', 'like', "%{$search}%");
                  });
            });
        }

        $transaksiList = $query->orderByDesc('id')->get();

        $totalKasMasuk = (float) $transaksiList->sum('nominal_bayar');
        $totalTransaksi = $transaksiList->count();
        $totalTunai = (float) $transaksiList->where('metode_pembayaran', 'Tunai')->sum('nominal_bayar');
        $totalNonTunai = $totalKasMasuk - $totalTunai;

        return response()->json([
            'success' => true,
            'data' => $transaksiList,
            'summary' => [
                'total_kas' => $totalKasMasuk,
                'total_transaksi' => $totalTransaksi,
                'total_tunai' => $totalTunai,
                'total_non_tunai' => $totalNonTunai,
            ],
        ]);
    }

    public function cetakLaporan(Request $request): View
    {
        $user = auth()->user();
        if ($user && $user->hasRole('siswa')) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin mencetak laporan kas.');
        }

        $query = TransaksiKeuangan::query()->with(['siswa', 'posKeuangan', 'tagihan', 'user']);

        if ($user && $user->hasRole('wakel') && !empty($user->kelas)) {
            $query->whereHas('siswa', fn ($q) => $q->where('kelas', $user->kelas));
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_bayar', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_bayar', '<=', $request->tanggal_selesai);
        }

        if ($request->filled('kelas')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        if ($request->filled('pos_id')) {
            $query->where('pos_keuangan_id', $request->pos_id);
        }

        if ($request->filled('metode')) {
            $query->where('metode_pembayaran', $request->metode);
        }

        $transaksiList = $query->orderBy('tanggal_bayar')->get();
        $totalKas = (float) $transaksiList->sum('nominal_bayar');
        $filterInfo = [
            'tanggal_mulai' => $request->tanggal_mulai ?: 'Awal',
            'tanggal_selesai' => $request->tanggal_selesai ?: date('Y-m-d'),
            'kelas' => $request->kelas ?: 'Semua Kelas',
            'pos' => $request->pos_id ? (PosKeuangan::find($request->pos_id)?->nama ?? 'Semua Pos') : 'Semua Pos',
        ];

        return view('pdf.laporan-keuangan', compact('transaksiList', 'totalKas', 'filterInfo'));
    }
}
