<?php

namespace App\Http\Controllers;

use App\Services\TeacherAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanAbsensiGuruController extends PageActionController
{
    public function __construct(
        protected TeacherAttendanceService $teacherAttendance,
    ) {
    }

    public function index(): View
    {
        return view('pages.laporan-absensi-guru');
    }

    public function list(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->teacherAttendance->getAbsensiList($args, $auth));
    }

    public function exportExcel(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->teacherAttendance->exportExcel($args, $auth));
    }

    public function rekapBulanan(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->teacherAttendance->getRekapBulanan($args, $auth));
    }

    public function rekapTahunan(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->teacherAttendance->getRekapTahunan($args, $auth));
    }

    public function exportRekapBulanan(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->teacherAttendance->exportExcelRekapBulanan($args, $auth));
    }

    public function exportRekapTahunan(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->teacherAttendance->exportExcelRekapTahunan($args, $auth));
    }
}
