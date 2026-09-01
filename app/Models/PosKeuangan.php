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
        'tarif_per_kelas',
        'target_kelas',
        'tahun_ajaran',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'nominal_default' => 'decimal:2',
        'tarif_per_kelas' => 'array',
        'target_kelas' => 'array',
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

    public static function getTingkatKelas(?string $kelas): string
    {
        if (!$kelas) return '';
        $k = strtoupper(trim($kelas));
        if (str_starts_with($k, 'XII') || str_contains($k, '12')) return 'XII';
        if (str_starts_with($k, 'XI') || str_contains($k, '11')) return 'XI';
        if (str_starts_with($k, 'X') || str_contains($k, '10')) return 'X';
        return $k;
    }

    public function isApplicableToSiswa(Siswa $siswa): bool
    {
        $target = $this->target_kelas;
        if (empty($target) || in_array('all', $target, true) || in_array('semua', $target, true)) {
            return true;
        }

        $kelas = trim((string) ($siswa->kelas ?? ''));
        if ($kelas === '') return true;

        $tingkat = self::getTingkatKelas($kelas);

        foreach ($target as $t) {
            $tClean = strtoupper(trim($t));
            if ($tClean === 'ALL' || $tClean === 'SEMUA') return true;
            if (strcasecmp($tClean, $kelas) === 0) return true;
            if (strcasecmp($tClean, $tingkat) === 0) return true;
        }

        return false;
    }

    public function getNominalForSiswa(Siswa $siswa): float
    {
        $tarifMap = $this->tarif_per_kelas;
        if (empty($tarifMap) || !is_array($tarifMap)) {
            return (float) $this->nominal_default;
        }

        $kelas = trim((string) ($siswa->kelas ?? ''));
        $tingkat = self::getTingkatKelas($kelas);

        // 1. Exact match class (e.g. "X RPL")
        foreach ($tarifMap as $k => $val) {
            if ($k !== '' && strcasecmp(trim($k), $kelas) === 0 && is_numeric($val) && (float) $val > 0) {
                return (float) $val;
            }
        }

        // 2. Tingkat match (e.g. "X", "XI", "XII")
        foreach ($tarifMap as $k => $val) {
            if ($k !== '' && strcasecmp(trim($k), $tingkat) === 0 && is_numeric($val) && (float) $val > 0) {
                return (float) $val;
            }
        }

        // 3. Fallback default
        return (float) $this->nominal_default;
    }
}
