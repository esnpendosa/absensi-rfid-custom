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
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Anda tidak memiliki izin menambah pos keuangan.'], 403);
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
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Anda tidak memiliki izin mengubah pos keuangan.'], 403);
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
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Anda tidak memiliki izin menghapus pos keuangan.'], 403);
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

        $user = auth()->user();

        if ($user && $user->hasRole('siswa')) {
            // Siswa hanya melihat tagihan milik dirinya sendiri
            $siswa = Siswa::where('nisn', $user->username)
                ->orWhere('nama', $user->name)
                ->first();

            $isSiswa = true;
            $posList = PosKeuangan::where('is_active', true)->orderBy('nama')->get();

            return view('pages.keuangan-pembayaran', compact('posList', 'siswa', 'isSiswa'));
        }

        $posList = PosKeuangan::where('is_active', true)->orderBy('nama')->get();
        $kelasList = Siswa::query()->whereNotNull('kelas')->distinct()->pluck('kelas');
        $isSiswa = false;

        return view('pages.keuangan-pembayaran', compact('posList', 'kelasList', 'isSiswa'));
    }

    public function ensureBillingSynced(): void
    {
        try {
            // Jika tabel siswa atau tabel pos keuangan kosong, tidak ada yang perlu di-sync
            if (Siswa::count() === 0 || PosKeuangan::count() === 0) {
                return;
            }

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
        } catch (\Throwable $e) {
            \Log::warning('ensureBillingSynced gagal: ' . $e->getMessage());
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
        $query = Siswa::query()->with(['tagihan.posKeuangan']);

        // JIKA ROLE SISWA: HANYA BOLEH MELIHAT DATA TAGIHAN SENDIRI
        if ($user && $user->hasRole('siswa')) {
            $nisn = $user->username;
            $nama = $user->name;
            $query->where(function ($q) use ($nisn, $nama) {
                $q->where('nisn', $nisn)->orWhere('nama', $nama);
            });
        } elseif ($user && $user->hasRole('wakel') && !empty($user->kelas)) {
            // WALI KELAS: HANYA BOLEH MELIHAT KELASNYA
            $query->where('kelas', $user->kelas);
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('pos_id')) {
            $posId = $request->pos_id;
            $query->whereHas('tagihan', function ($q) use ($posId) {
                $q->where('pos_keuangan_id', $posId);
            });
        }

        if ($request->filled('status')) {
            $st = $request->status;
            if ($st === 'lunas') {
                $query->whereDoesntHave('tagihan', fn ($q) => $q->where('status', '!=', 'lunas'));
            } elseif ($st === 'belum_bayar') {
                $query->whereDoesntHave('tagihan', fn ($q) => $q->where('terbayar', '>', 0));
            } elseif ($st === 'cicilan') {
                $query->whereHas('tagihan', fn ($q) => $q->where('terbayar', '>', 0)->where('status', '!=', 'lunas'));
            }
        }

        $siswaList = $query->orderBy('nama')->paginate($request->input('per_page', 20));

        // Format data 1 baris per siswa dengan total gabungan tagihan
        $transformedData = $siswaList->getCollection()->map(function ($s) {
            $tagihan = $s->tagihan;
            $totalNominal = (float) $tagihan->sum('nominal');
            $totalTerbayar = (float) $tagihan->sum('terbayar');
            $totalSisa = (float) $tagihan->sum('sisa');

            $status = 'belum_bayar';
            if ($totalSisa <= 0 && $totalNominal > 0) {
                $status = 'lunas';
            } elseif ($totalTerbayar > 0) {
                $status = 'cicilan';
            }

            return [
                'id' => $s->id,
                'siswa_id' => $s->id,
                'nama' => $s->nama,
                'nisn' => $s->nisn,
                'kelas' => $s->kelas,
                'total_tagihan_count' => $tagihan->count(),
                'tagihan_lunas_count' => $tagihan->where('status', 'lunas')->count(),
                'tagihan_belum_count' => $tagihan->where('status', '!=', 'lunas')->count(),
                'nominal' => $totalNominal,
                'terbayar' => $totalTerbayar,
                'sisa' => $totalSisa,
                'status' => $status,
            ];
        });

        // HITUNG RINGKASAN DATA (DISESUAIKAN DENGAN ROLE USER)
        if ($user && $user->hasRole('siswa')) {
            $mySiswa = Siswa::where('nisn', $user->username)->orWhere('nama', $user->name)->first();
            $mySiswaId = $mySiswa ? $mySiswa->id : 0;

            $totalPemasukan = (float) TransaksiKeuangan::where('siswa_id', $mySiswaId)->sum('nominal_bayar');
            $totalLunas = TagihanSiswa::where('siswa_id', $mySiswaId)->where('status', 'lunas')->count();
            $totalSiswa = 1;
            $totalTunggakan = (float) TagihanSiswa::where('siswa_id', $mySiswaId)->where('status', '!=', 'lunas')->sum('sisa');
        } elseif ($user && $user->hasRole('wakel') && !empty($user->kelas)) {
            $kelas = $user->kelas;
            $siswaIds = Siswa::where('kelas', $kelas)->pluck('id');

            $totalPemasukan = (float) TransaksiKeuangan::whereIn('siswa_id', $siswaIds)->sum('nominal_bayar');
            $totalLunas = TagihanSiswa::whereIn('siswa_id', $siswaIds)->where('status', 'lunas')->count();
            $totalSiswa = count($siswaIds);
            $totalTunggakan = (float) TagihanSiswa::whereIn('siswa_id', $siswaIds)->where('status', '!=', 'lunas')->sum('sisa');
        } else {
            $totalPemasukan = (float) TransaksiKeuangan::sum('nominal_bayar');
            $totalLunas = TagihanSiswa::where('status', 'lunas')->count();
            $totalSiswa = Siswa::count();
            $totalTunggakan = (float) TagihanSiswa::where('status', '!=', 'lunas')->sum('sisa');
        }

        return response()->json([
            'success' => true,
            'data' => $transformedData,
            'meta' => [
                'current_page' => $siswaList->currentPage(),
                'last_page' => $siswaList->lastPage(),
                'total' => $siswaList->total(),
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
        $user = auth()->user();
        if ($user && $user->hasRole('siswa')) {
            if ($siswa->nisn !== $user->username && $siswa->nama !== $user->name) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses Ditolak: Anda hanya diizinkan melihat tagihan milik akun Anda sendiri.'
                ], 403);
            }
        } elseif ($user && $user->hasRole('wakel') && !empty($user->kelas)) {
            if ($siswa->kelas !== $user->kelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses Ditolak: Anda hanya diizinkan melihat data siswa di kelas Anda.'
                ], 403);
            }
        }

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

    public function updateTagihan(Request $request, TagihanSiswa $tagihanSiswa): JsonResponse
    {
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak.'], 403);
        }

        $validated = $request->validate([
            'nominal' => ['required', 'numeric', 'min:0'],
        ]);

        $nominal = (float) $validated['nominal'];
        $terbayar = (float) $tagihanSiswa->terbayar;
        $sisa = max(0, $nominal - $terbayar);
        $status = $sisa <= 0 ? 'lunas' : ($terbayar > 0 ? 'cicilan' : 'belum_bayar');

        $tagihanSiswa->update([
            'nominal' => $nominal,
            'sisa' => $sisa,
            'status' => $status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data tagihan berhasil diperbarui.',
            'data' => $tagihanSiswa,
        ]);
    }

    public function destroyTagihan(TagihanSiswa $tagihanSiswa): JsonResponse
    {
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak.'], 403);
        }

        DB::transaction(function () use ($tagihanSiswa) {
            $tagihanSiswa->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Tagihan berhasil dihapus.',
        ]);
    }

    public function destroyTransaksi(TransaksiKeuangan $transaksiKeuangan): JsonResponse
    {
        if (!auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak.'], 403);
        }

        DB::transaction(function () use ($transaksiKeuangan) {
            $tagihan = $transaksiKeuangan->tagihan;
            if ($tagihan) {
                $newTerbayar = max(0, $tagihan->terbayar - (float) $transaksiKeuangan->nominal_bayar);
                $newSisa = max(0, $tagihan->nominal - $newTerbayar);
                $newStatus = $newSisa <= 0 ? 'lunas' : ($newTerbayar > 0 ? 'cicilan' : 'belum_bayar');

                $tagihan->update([
                    'terbayar' => $newTerbayar,
                    'sisa' => $newSisa,
                    'status' => $newStatus,
                ]);
            }

            $transaksiKeuangan->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Transaksi pembayaran berhasil dibatalkan dan sisa tagihan telah disesuaikan.',
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
            'tagihan_id' => ['nullable', 'exists:tagihan_siswa,id'],
            'nominal_bayar' => ['nullable', 'numeric', 'min:1000'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.tagihan_id' => ['required_with:items', 'exists:tagihan_siswa,id'],
            'items.*.nominal_bayar' => ['required_with:items', 'numeric', 'min:1000'],
            'metode_pembayaran' => ['nullable', 'string', 'max:30'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        // Siapkan daftar items pembayaran
        $paymentItems = [];
        if (!empty($validated['items'])) {
            $paymentItems = $validated['items'];
        } elseif (!empty($validated['tagihan_id']) && !empty($validated['nominal_bayar'])) {
            $paymentItems[] = [
                'tagihan_id' => $validated['tagihan_id'],
                'nominal_bayar' => $validated['nominal_bayar'],
            ];
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Silakan pilih minimal 1 tagihan untuk dibayar.'
            ], 422);
        }

        $nomorTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        $metode = $validated['metode_pembayaran'] ?? 'Tunai';

        $createdTransactions = DB::transaction(function () use ($validated, $paymentItems, $nomorTransaksi, $metode) {
            $trxList = [];
            foreach ($paymentItems as $item) {
                $tagihan = TagihanSiswa::with('posKeuangan', 'siswa')->findOrFail($item['tagihan_id']);
                $nominalBayar = (float) $item['nominal_bayar'];

                if ($nominalBayar > $tagihan->sisa) {
                    $nominalBayar = (float) $tagihan->sisa;
                }

                $posLabel = $tagihan->posKeuangan->nama . ($tagihan->bulan ? ' (' . $tagihan->bulan . ')' : '');

                $trx = TransaksiKeuangan::create([
                    'nomor_transaksi' => $nomorTransaksi,
                    'siswa_id' => $tagihan->siswa_id,
                    'pos_keuangan_id' => $tagihan->pos_keuangan_id,
                    'tagihan_siswa_id' => $tagihan->id,
                    'nominal_bayar' => $nominalBayar,
                    'tanggal_bayar' => Carbon::today(),
                    'metode_pembayaran' => $metode,
                    'keterangan' => $validated['keterangan'] ?? ('Pembayaran ' . $posLabel),
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

                $trx->setRelation('tagihan', $tagihan);
                $trxList[] = $trx;
            }

            return $trxList;
        });

        $firstTrx = $createdTransactions[0];
        $siswa = Siswa::find($validated['siswa_id']);

        // Kirim notifikasi WhatsApp HANYA JIKA LUNAS (Jika belum lunas/sebagian, tidak kirim notifikasi)
        try {
            if ($isAllLunas && $siswa && !empty($siswa->no_hp)) {
                $waService = app(\App\Services\WaGatewayService::class);
                $totalBayar = collect($createdTransactions)->sum('nominal_bayar');
                $totalNominalStr = 'Rp ' . number_format($totalBayar, 0, ',', '.');
                $notaUrl = url('/keuangan/kuitansi/' . $firstTrx->id);

                // Buat rincian item checklist lunas
                $rincianText = "";
                foreach ($createdTransactions as $idx => $t) {
                    $tgh = $t->tagihan;
                    $pNama = ($tgh->posKeuangan->nama ?? 'Keuangan') . ($tgh->bulan ? ' (' . $tgh->bulan . ')' : '');
                    $rincianText .= ($idx + 1) . ". {$pNama} : Rp " . number_format($t->nominal_bayar, 0, ',', '.') . " [LUNAS]\n";
                }

                $pesan = "*BUKTI PEMBAYARAN RESMI*\n" .
                         "*SMK NURUL HIDAYAH*\n" .
                         "------------------------------------\n" .
                         "No. Nota     : {$nomorTransaksi}\n" .
                         "Nama Siswa   : {$siswa->nama}\n" .
                         "NISN / Kelas : {$siswa->nisn} ({$siswa->kelas})\n" .
                         "Tanggal      : " . Carbon::today()->translatedFormat('d F Y') . "\n" .
                         "------------------------------------\n" .
                         "*Rincian Pembayaran:*\n" .
                         $rincianText .
                         "------------------------------------\n" .
                         "Total Bayar  : *{$totalNominalStr}*\n" .
                         "Metode       : {$metode}\n" .
                         "Status       : *LUNAS*\n" .
                         "------------------------------------\n" .
                         "*Lihat / Unduh Nota Struk Digital:*\n" .
                         "{$notaUrl}\n\n" .
                         "_Terima kasih, pembayaran telah kami terima dan tercatat secara resmi di sistem sekolah._";

                $nomorSiswa = trim((string) ($siswa->no_hp ?? ''));
                if ($nomorSiswa !== '') {
                    $waService->sendCustomMessage($nomorSiswa, $pesan);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal kirim WA pembayaran: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil disimpan dan kuitansi 1 nota telah siap.',
            'data' => $firstTrx,
            'items_count' => count($createdTransactions),
        ]);
    }

    public function cetakKuitansi(TransaksiKeuangan $transaksi): View
    {
        $user = auth()->user();
        if ($user && $user->hasRole('siswa')) {
            $siswa = $transaksi->siswa;
            if ($siswa && $siswa->nisn !== $user->username && $siswa->nama !== $user->name) {
                abort(403, 'Akses Ditolak: Anda hanya boleh melihat kuitansi Anda sendiri.');
            }
        }

        // Ambil semua transaksi dengan nomor_transaksi yang sama (1 nota kuitansi)
        $allTransactions = TransaksiKeuangan::query()
            ->where('nomor_transaksi', $transaksi->nomor_transaksi)
            ->with(['siswa', 'posKeuangan', 'tagihan', 'user'])
            ->get();

        if ($allTransactions->isEmpty()) {
            $allTransactions = collect([$transaksi]);
        }

        $transaksi->load(['siswa', 'posKeuangan', 'tagihan', 'user']);
        return view('pdf.kuitansi-keuangan', compact('transaksi', 'allTransactions'));
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

    /**
     * Private helper: membangun query TransaksiKeuangan dengan semua parameter filter.
     * Filter hanya diterapkan jika parameter filled() (tidak kosong).
     * Mendukung parameter baru (tanggal_mulai, tanggal_selesai, kelas_id, pos_keuangan_id,
     * metode_pembayaran) maupun parameter lama (kelas, pos_id, metode) untuk backward-compat.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildLaporanQuery(Request $request)
    {
        $user  = auth()->user();
        $query = TransaksiKeuangan::query()->with(['siswa', 'posKeuangan', 'tagihan', 'user']);

        // Scope wakel hanya ke kelasnya sendiri
        if ($user && $user->hasRole('wakel') && !empty($user->kelas)) {
            $query->whereHas('siswa', fn ($q) => $q->where('kelas', $user->kelas));
        }

        // Filter tanggal mulai — gunakan created_at (parameter baru) atau tanggal_bayar (lama)
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('created_at', '>=', $request->tanggal_mulai);
        }

        // Filter tanggal selesai
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_selesai);
        }

        // Filter kelas — parameter baru: kelas_id, parameter lama: kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', fn ($q) => $q->where('kelas_id', $request->kelas_id));
        } elseif ($request->filled('kelas')) {
            $query->whereHas('siswa', fn ($q) => $q->where('kelas', $request->kelas));
        }

        // Filter pos keuangan — parameter baru: pos_keuangan_id, parameter lama: pos_id
        if ($request->filled('pos_keuangan_id')) {
            $query->where('pos_keuangan_id', $request->pos_keuangan_id);
        } elseif ($request->filled('pos_id')) {
            $query->where('pos_keuangan_id', $request->pos_id);
        }

        // Filter metode pembayaran — parameter baru: metode_pembayaran, parameter lama: metode
        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        } elseif ($request->filled('metode')) {
            $query->where('metode_pembayaran', $request->metode);
        }

        // Filter pencarian teks (nomor transaksi, nama siswa, NISN)
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

        return $query;
    }

    public function dataLaporan(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user && $user->hasRole('siswa')) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Anda tidak memiliki izin.'], 403);
        }

        $query = $this->buildLaporanQuery($request);
        $transaksiList = $query->orderByDesc('id')->get();

        $totalKas       = (float) $transaksiList->sum('nominal_bayar');
        $totalTransaksi = $transaksiList->count();
        $totalTunai     = (float) $transaksiList->where('metode_pembayaran', 'Tunai')->sum('nominal_bayar');
        $totalNonTunai  = $totalKas - $totalTunai;

        return response()->json([
            'success' => true,
            'data'    => $transaksiList,
            'summary' => [
                'total_kas'       => $totalKas,
                'total_transaksi' => $totalTransaksi,
                'total_tunai'     => $totalTunai,
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

        $transaksiList = $this->buildLaporanQuery($request)
            ->orderBy('created_at')
            ->get();

        $totalKas      = (float) $transaksiList->sum('nominal_bayar');
        $totalTransaksi = $transaksiList->count();
        $totalTunai    = (float) $transaksiList->where('metode_pembayaran', 'Tunai')->sum('nominal_bayar');
        $totalNonTunai = $totalKas - $totalTunai;

        $summary = [
            'total_kas'       => $totalKas,
            'total_transaksi' => $totalTransaksi,
            'total_tunai'     => $totalTunai,
            'total_non_tunai' => $totalNonTunai,
        ];

        $filterInfo = [
            'tanggal_mulai'   => $request->tanggal_mulai  ?: 'Awal',
            'tanggal_selesai' => $request->tanggal_selesai ?: date('Y-m-d'),
            'kelas'           => $request->kelas_id
                                     ? $request->kelas_id
                                     : ($request->kelas ?: 'Semua Kelas'),
            'pos'             => ($request->pos_keuangan_id ?? $request->pos_id)
                                     ? (PosKeuangan::find($request->pos_keuangan_id ?? $request->pos_id)?->nama ?? 'Semua Pos')
                                     : 'Semua Pos',
            'metode'          => $request->metode_pembayaran ?: ($request->metode ?: 'Semua Metode'),
            'search'          => $request->search ?: null,
        ];

        return view('pdf.laporan-keuangan', compact('transaksiList', 'summary', 'totalKas', 'filterInfo'));
    }
}
