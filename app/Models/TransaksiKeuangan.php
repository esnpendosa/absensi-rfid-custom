<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiKeuangan extends Model
{
    use HasFactory;

    protected $table = 'transaksi_keuangan';

    protected $fillable = [
        'nomor_transaksi',
        'siswa_id',
        'pos_keuangan_id',
        'tagihan_siswa_id',
        'nominal_bayar',
        'tanggal_bayar',
        'metode_pembayaran',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'nominal_bayar' => 'decimal:2',
        'tanggal_bayar' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function posKeuangan(): BelongsTo
    {
        return $this->belongsTo(PosKeuangan::class, 'pos_keuangan_id');
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(TagihanSiswa::class, 'tagihan_siswa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
