<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagihanSiswa extends Model
{
    use HasFactory;

    protected $table = 'tagihan_siswa';

    protected $fillable = [
        'pos_keuangan_id',
        'siswa_id',
        'tahun_ajaran',
        'bulan',
        'nominal',
        'terbayar',
        'sisa',
        'status',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'terbayar' => 'decimal:2',
        'sisa' => 'decimal:2',
    ];

    public function posKeuangan(): BelongsTo
    {
        return $this->belongsTo(PosKeuangan::class, 'pos_keuangan_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiKeuangan::class, 'tagihan_siswa_id');
    }
}
