<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;

class EnsureApiToken
{
    public function handle(Request $request, Closure $next, string ...$scopes): mixed
    {
        $plainToken = trim((string) $request->bearerToken());

        if ($plainToken === '') {
            return $this->errorResponse('Token API wajib dikirim lewat Authorization: Bearer.', 401);
        }

        $apiToken = ApiToken::query()
            ->where('token_hash', ApiToken::hashToken($plainToken))
            ->first();

        if (!$apiToken || !$apiToken->is_active) {
            return $this->errorResponse('Token API tidak valid atau nonaktif.', 401);
        }

        if ($apiToken->isExpired()) {
            return $this->errorResponse('Token API sudah kedaluwarsa.', 401);
        }

        foreach ($scopes as $scope) {
            if (!$apiToken->hasScope($scope)) {
                return $this->errorResponse('Token API tidak memiliki akses untuk endpoint ini.', 403);
            }
        }

        $apiToken->forceFill([
            'last_used_at' => now(),
        ])->saveQuietly();

        $request->attributes->set('api_token', $apiToken);

        return $next($request);
    }

    private function errorResponse(string $message, int $statusCode)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'rc' => $statusCode,
        ], $statusCode);
    }
}
