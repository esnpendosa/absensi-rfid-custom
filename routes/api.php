<?php

use App\Http\Controllers\Api\Admin\AttendanceLogController as AdminAttendanceLogController;
use App\Http\Controllers\Api\Admin\DeviceController as AdminDeviceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\TelegramWebhookController;
use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\StudentApiController;
use App\Http\Middleware\EnsureApiToken;
use Illuminate\Support\Facades\Route;


Route::post('/device/activate', [DeviceController::class, 'activate']);
Route::post('/telegram/webhook/{secret}', [TelegramWebhookController::class, 'handle']);

Route::prefix('device')
    ->middleware('device')
    ->group(function (): void {
        Route::post('/cek', [DeviceController::class, 'cek']);
        Route::post('/attendance', [DeviceController::class, 'attendance'])
            ->middleware('throttle:device-attendance');
        Route::post('/heartbeat', [DeviceController::class, 'heartbeat']);
    });

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin|super-admin'])
    ->group(function (): void {
        Route::get('/devices', [AdminDeviceController::class, 'index']);
        Route::post('/devices', [AdminDeviceController::class, 'store']);
        Route::put('/devices/{device}/activate', [AdminDeviceController::class, 'activate']);
        Route::put('/devices/{device}/deactivate', [AdminDeviceController::class, 'deactivate']);
        Route::put('/devices/{device}/revoke', [AdminDeviceController::class, 'revoke']);
        Route::put('/devices/{device}/reset', [AdminDeviceController::class, 'reset']);
        Route::get('/attendance', [AdminAttendanceLogController::class, 'index']);
    });

Route::prefix('v1')
    ->middleware('throttle:120,1')
    ->group(function (): void {
        Route::post('/students/list', [StudentApiController::class, 'list'])
            ->middleware(EnsureApiToken::class . ':students.read');
        Route::post('/students/detail', [StudentApiController::class, 'detail'])
            ->middleware(EnsureApiToken::class . ':students.read');

        Route::post('/attendance/list', [AttendanceApiController::class, 'list'])
            ->middleware(EnsureApiToken::class . ':attendance.read');
        Route::post('/attendance/student', [AttendanceApiController::class, 'studentReport'])
            ->middleware(EnsureApiToken::class . ':attendance.read');
        Route::post('/attendance/class', [AttendanceApiController::class, 'classReport'])
            ->middleware(EnsureApiToken::class . ':attendance.read');
        Route::post('/attendance/all', [AttendanceApiController::class, 'allReport'])
            ->middleware(EnsureApiToken::class . ':attendance.read');
        Route::post('/attendance/summary', [AttendanceApiController::class, 'summary'])
            ->middleware(EnsureApiToken::class . ':attendance.summary');
    });
