<?php

namespace App\Http\Controllers;

use App\Services\Modules\AttendanceRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends PageActionController
{
    public function __construct(
        protected AttendanceRecordService $attendanceRecords,
    ) {
    }

    public function index(): View
    {
        return view('pages.monitoring');
    }

    public function monitoring(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->attendanceRecords->getMonitoringRealtime($args, $auth));
    }

    public function dashboardSummary(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->attendanceRecords->getDashboardSummary($args, $auth));
    }

    public function markPulangMassal(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->attendanceRecords->markPulangMassal($args, $auth));
    }

    public function updateStatus(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->attendanceRecords->updateAbsensiStatus($args, $auth));
    }

    public function updateRecord(Request $request): JsonResponse
    {
        return $this->respondArgsAuth($request, fn (array $args, $auth) => $this->attendanceRecords->updateAbsensiRecord($args, $auth));
    }
}
