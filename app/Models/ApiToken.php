<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use HasFactory;

    public const SCOPES = [
        'students.read' => 'Baca Data Siswa',
        'attendance.read' => 'Baca Laporan Absensi',
        'attendance.summary' => 'Baca Ringkasan Absensi',
    ];

    protected $fillable = [
        'name',
        'token_hash',
        'scopes',
        'last_used_at',
        'expires_at',
        'is_active',
        'created_by_user_id',
    ];

    protected $hidden = [
        'token_hash',
    ];

    protected $casts = [
        'scopes' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public static function generatePlainToken(): string
    {
        return Str::random(64);
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', trim($token));
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function hasScope(string $scope): bool
    {
        $scopes = $this->scopes;
        if (!is_array($scopes)) {
            return false;
        }

        return in_array('*', $scopes, true) || in_array($scope, $scopes, true);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
