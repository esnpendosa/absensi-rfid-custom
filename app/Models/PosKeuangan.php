<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosKeuangan extends Model
{
    use HasFactory;

    protected $table = 'pos_keuangan';

    protected $fillable = [
        'kode',
        'nama',
        'tipe',
        'nominal_default',
        'tahun_ajaran',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'nominal_default' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tagihan(): HasMany
    {
        return $this->hasMany(TagihanSiswa::class, 'pos_keuangan_id');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiKeuangan::class, 'pos_keuangan_id');
    }
}
