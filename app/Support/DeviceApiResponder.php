<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use stdClass;

class DeviceApiResponder
{
    public static function isDeviceRequest(?Request $request): bool
    {
        if (!$request instanceof Request) {
            return false;
        }

        return $request->is('api/device') || $request->is('api/device/*');
    }

    public static function success(
        ?Request $request,
        string $message,
        array|object|null $data = null,
        int $statusCode = 200,
        array $headers = []
    ): JsonResponse {
        return self::response($request, true, $message, $statusCode, $data, $headers);
    }

    public static function error(
        ?Request $request,
        string $message,
        int $statusCode,
        array|object|null $data = null,
        array $headers = []
    ): JsonResponse {
        $httpStatus = self::isDeviceRequest($request) ? 200 : $statusCode;
        return self::response($request, false, $message, $httpStatus, $data, $headers, $statusCode);
    }

    public static function response(
        ?Request $request,
        bool $success,
        string $message,
        int $statusCode,
        array|object|null $data = null,
        array $headers = [],
        ?int $logicalCode = null
    ): JsonResponse {
        return response()->json([
            'success' => $success,
            'code' => $logicalCode ?? $statusCode,
            'message' => $message,
            'data' => self::normalizeData($data),
        ], $statusCode, $headers);
    }

    protected static function normalizeData(array|object|null $data): array|object|null
    {
        if ($data === null) {
            return null;
        }

        if (is_array($data) && $data === []) {
            return new stdClass();
        }

        return $data;
    }
}
