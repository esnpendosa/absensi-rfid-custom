<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Surat extends Model
{
    use HasFactory;

    public const JENIS_MASUK = 'masuk';
    public const JENIS_KELUAR = 'keluar';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_DIARSIPKAN = 'diarsipkan';

    protected $table = 'surat';

    protected $fillable = [
        'jenis',
        'nomor_surat',
        'tanggal_surat',
        'tanggal_diterima',
        'tanggal_dikirim',
        'asal_surat',
        'tujuan_surat',
        'perihal',
        'ringkasan',
        'status',
        'lampiran_path',
        'lampiran_nama',
        'lampiran_mime',
        'lampiran_size',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_diterima' => 'date',
        'tanggal_dikirim' => 'date',
        'lampiran_size' => 'integer',
        'created_by_user_id' => 'integer',
        'updated_by_user_id' => 'integer',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
