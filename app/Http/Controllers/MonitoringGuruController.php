<?php

namespace App\Http\Controllers;

use App\Services\TeacherAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringGuruController extends PageActionController
{
    public function __construct(
        protected TeacherAttendanceService $teacherAttendance,
    ) {
    }

    public function index(): View
    {
        return view('pages.monitoring-guru');
    }

    public function monitoring(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->teacherAttendance->getMonitoringRealtime($args, $auth));
    }

    public function markPulangMassal(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->teacherAttendance->markPulangMassal($args, $auth));
    }

    public function updateStatus(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->teacherAttendance->updateAbsensiStatus($args, $auth));
    }

    public function exportExcel(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->teacherAttendance->exportExcel($args, $auth));
    }
}
