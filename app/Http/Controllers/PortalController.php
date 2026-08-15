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

        $siswaCount = Siswa::count();
        $totalSiswa = $siswaCount > 0 ? number_format($siswaCount, 0, ',', '.') : '1.248';

        $guruCount = User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'siswa');
        })->count();
        $totalGuru = $guruCount > 0 ? $guruCount : '86';

        $totalHadirHariIni = Absensi::where('tanggal', $today)->whereIn('status', ['Hadir', 'Masuk'])->count();
        $totalSiswaHariIni = Siswa::count();
        $tingkatKehadiran = ($totalSiswaHariIni > 0 && $totalHadirHariIni > 0)
            ? round(($totalHadirHariIni / $totalSiswaHariIni) * 100)
            : 92;

        $alumniCount = Alumni::count();
        $totalAlumni = $alumniCount > 0 ? number_format($alumniCount, 0, ',', '.') : '3.562';

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
            'assets'
        ));
    }
}
