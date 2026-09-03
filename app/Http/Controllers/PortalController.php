<?php

namespace App\Http\Controllers;

use App\Helpers\PortalAssets;
use App\Models\Absensi;
use App\Models\Alumni;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $stats = \Illuminate\Support\Facades\Cache::remember('portal_stats_summary', 120, function () use ($today, $startOfMonth, $endOfMonth) {
            $siswaRawCount = Siswa::count();
            $guruRawCount = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['guru', 'wakel']);
            })->count();

            $totalHadirHariIni = Absensi::where('tanggal', $today)
                ->whereIn('status', ['Hadir', 'Masuk', 'Terlambat'])
                ->count();

            if ($totalHadirHariIni > 0 && $siswaRawCount > 0) {
                $tingkatKehadiran = round(($totalHadirHariIni / $siswaRawCount) * 100);
            } else {
                $totalAbsenBulanIni = Absensi::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count();
                if ($totalAbsenBulanIni > 0) {
                    $totalHadirBulanIni = Absensi::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                        ->whereIn('status', ['Hadir', 'Masuk', 'Terlambat'])
                        ->count();
                    $tingkatKehadiran = round(($totalHadirBulanIni / $totalAbsenBulanIni) * 100);
                } else {
                    $tingkatKehadiran = 100;
                }
            }

            $alumniRawCount = Alumni::count();

            return [
                'totalSiswa' => number_format($siswaRawCount, 0, ',', '.'),
                'totalGuru' => number_format($guruRawCount, 0, ',', '.'),
                'tingkatKehadiran' => $tingkatKehadiran,
                'totalAlumni' => number_format($alumniRawCount, 0, ',', '.'),
            ];
        });

        $totalSiswa = $stats['totalSiswa'];
        $totalGuru = $stats['totalGuru'];
        $tingkatKehadiran = $stats['tingkatKehadiran'];
        $totalAlumni = $stats['totalAlumni'];

        // 5. UI Settings — baca dari shared data yang sudah di-cache oleh AppServiceProvider
        $uiSettings = view()->shared('appUiSettings') ?? [];

        // 6. Config/Dynamic School Info
        $schoolName = $uiSettings['website_nama'] ?? 'SMK Nurul Hidayah Bungah';

        // 7. Tentukan URL foto gedung
        $buildingUrl = !empty($uiSettings['portal_building_photo_url'])
            ? $uiSettings['portal_building_photo_url']
            : PortalAssets::getBuilding();

        $assets = [
            'logo' => PortalAssets::getLogo(),
            'building' => $buildingUrl,
            'card1' => PortalAssets::getCard1(),
            'card2' => PortalAssets::getCard2(),
            'card3' => PortalAssets::getCard3(),
            'card4' => PortalAssets::getCard4(),
            'card5' => PortalAssets::getCard5(),
        ];

        return view('pages.portal', compact(
            'totalSiswa',
            'totalGuru',
            'tingkatKehadiran',
            'totalAlumni',
            'schoolName',
            'assets',
            'uiSettings',
            'buildingUrl'
        ));
    }
}
