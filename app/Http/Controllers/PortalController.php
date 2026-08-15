<?php

namespace App\Http\Controllers;

use App\Helpers\PortalAssets;
use App\Models\Absensi;
use App\Models\Alumni;
use App\Models\Konfigurasi;
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

        // 1. Total Siswa Realtime
        $siswaRawCount = Siswa::count();
        $totalSiswa = number_format($siswaRawCount, 0, ',', '.');

        // 2. Total Guru & Tenaga Kependidikan Realtime
        $guruRawCount = User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'siswa');
        })->count();
        $totalGuru = number_format($guruRawCount, 0, ',', '.');

        // 3. Tingkat Kehadiran Realtime (Bulan Ini / Hari Ini)
        $totalAbsenBulanIni = Absensi::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count();
        if ($totalAbsenBulanIni > 0) {
            $totalHadirBulanIni = Absensi::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->whereIn('status', ['Hadir', 'Masuk', 'Terlambat'])
                ->count();
            $tingkatKehadiran = round(($totalHadirBulanIni / $totalAbsenBulanIni) * 100);
        } else {
            $totalHadirHariIni = Absensi::where('tanggal', $today)
                ->whereIn('status', ['Hadir', 'Masuk', 'Terlambat'])
                ->count();
            $tingkatKehadiran = ($siswaRawCount > 0 && $totalHadirHariIni > 0)
                ? round(($totalHadirHariIni / $siswaRawCount) * 100)
                : 100;
        }

        // 4. Total Alumni Realtime
        $alumniRawCount = Alumni::count();
        $totalAlumni = number_format($alumniRawCount, 0, ',', '.');

        // 5. Config/Dynamic School Info
        $schoolName = Konfigurasi::where('key', 'website_name')->value('value') ?: 'SMK Nurul Hidayah Bungah';

        $assets = [
            'logo' => PortalAssets::getLogo(),
            'building' => PortalAssets::getBuilding(),
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
            'assets'
        ));
    }
}
