<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiGuru extends Model
{
    use HasFactory;

    protected $table = 'absensi_guru';

    protected $fillable = [
        'user_id',
        'tanggal',
        'nama',
        'username',
        'jabatan',
        'jam_datang',
        'jam_pulang',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_datang' => 'string',
        'jam_pulang' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
