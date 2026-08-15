<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApiAccessController extends Controller
{
    public function index(): View
    {
        $tokens = ApiToken::query()
            ->with('createdBy:id,name,username')
            ->latest('id')
            ->get();

        return view('pages.settings-api-access', [
            'tokens' => $tokens,
            'scopes' => ApiToken::SCOPES,
            'stats' => $this->tokenStats(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/\S/'],
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => ['required', 'string', Rule::in(array_keys(ApiToken::SCOPES))],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $plainToken = ApiToken::generatePlainToken();

        $token = ApiToken::query()->create([
            'name' => trim((string) $validated['name']),
            'token_hash' => ApiToken::hashToken($plainToken),
            'scopes' => array_values(array_unique($validated['scopes'])),
            'expires_at' => filled($validated['expires_at'] ?? null)
                ? Carbon::parse((string) $validated['expires_at'])->endOfDay()
                : null,
            'is_active' => true,
            'created_by_user_id' => $request->user()?->id,
        ]);

        $token->load('createdBy:id,name,username');

        return response()->json([
            'success' => true,
            'message' => 'Token API berhasil dibuat. Salin token sekarang.',
            'rc' => 200,
            'data' => [
                'plain_token' => $plainToken,
                'record' => $this->serializeToken($token),
                'stats' => $this->tokenStats(),
            ],
        ]);
    }

    public function toggle(Request $request, ApiToken $apiToken): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
        ]);

        $apiToken->forceFill([
            'is_active' => array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : !$apiToken->is_active,
        ])->save();

        $apiToken->load('createdBy:id,name,username');

        return response()->json([
            'success' => true,
            'message' => $apiToken->is_active ? 'Token API berhasil diaktifkan.' : 'Token API berhasil dinonaktifkan.',
            'rc' => 200,
            'data' => [
                'record' => $this->serializeToken($apiToken),
                'stats' => $this->tokenStats(),
            ],
        ]);
    }

    public function update(Request $request, ApiToken $apiToken): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/\S/'],
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => ['required', 'string', Rule::in(array_keys(ApiToken::SCOPES))],
            'expires_at' => ['nullable', 'date'],
        ]);

        $apiToken->forceFill([
            'name' => trim((string) $validated['name']),
            'scopes' => array_values(array_unique($validated['scopes'])),
            'expires_at' => filled($validated['expires_at'] ?? null)
                ? Carbon::parse((string) $validated['expires_at'])->endOfDay()
                : null,
        ])->save();

        $apiToken->load('createdBy:id,name,username');

        return response()->json([
            'success' => true,
            'message' => 'Token API berhasil diperbarui.',
            'rc' => 200,
            'data' => [
                'record' => $this->serializeToken($apiToken),
                'stats' => $this->tokenStats(),
            ],
        ]);
    }

    public function regenerate(ApiToken $apiToken): JsonResponse
    {
        $plainToken = ApiToken::generatePlainToken();

        $apiToken->forceFill([
            'token_hash' => ApiToken::hashToken($plainToken),
            'last_used_at' => null,
        ])->save();

        $apiToken->load('createdBy:id,name,username');

        return response()->json([
            'success' => true,
            'message' => 'Token API berhasil dibuat ulang. Salin token sekarang.',
            'rc' => 200,
            'data' => [
                'plain_token' => $plainToken,
                'record' => $this->serializeToken($apiToken),
                'stats' => $this->tokenStats(),
            ],
        ]);
    }

    public function destroy(ApiToken $apiToken): JsonResponse
    {
        $apiToken->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token API berhasil dihapus.',
            'rc' => 200,
            'data' => [
                'deleted_id' => (int) $apiToken->id,
                'stats' => $this->tokenStats(),
            ],
        ]);
    }

    private function tokenStats(): array
    {
        $now = now();

        return [
            'total' => ApiToken::query()->count(),
            'active' => ApiToken::query()
                ->where('is_active', true)
                ->where(function ($query) use ($now): void {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', $now);
                })
                ->count(),
            'inactive' => ApiToken::query()->where('is_active', false)->count(),
            'expired' => ApiToken::query()
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', $now)
                ->count(),
        ];
    }

    private function serializeToken(ApiToken $token): array
    {
        $scopeKeys = is_array($token->scopes) ? array_values($token->scopes) : [];
        $scopeLabels = collect($scopeKeys)
            ->map(fn (string $scope) => ApiToken::SCOPES[$scope] ?? $scope)
            ->values()
            ->all();
        $isExpired = $token->isExpired();
        $isUsable = $token->is_active && !$isExpired;

        return [
            'id' => (int) $token->id,
            'name' => (string) $token->name,
            'scopes' => $scopeKeys,
            'scope_labels' => $scopeLabels,
            'is_active' => (bool) $token->is_active,
            'is_expired' => $isExpired,
            'is_usable' => $isUsable,
            'status_label' => !$token->is_active ? 'Nonaktif' : ($isExpired ? 'Expired' : 'Aktif'),
            'last_used_at' => $token->last_used_at?->format('d M Y H:i'),
            'expires_at' => $token->expires_at?->format('d M Y H:i'),
            'expires_at_date' => $token->expires_at?->format('Y-m-d'),
            'created_at' => $token->created_at?->format('d M Y H:i'),
            'created_by' => trim((string) ($token->createdBy?->name ?: $token->createdBy?->username)) ?: '-',
        ];
    }
}
