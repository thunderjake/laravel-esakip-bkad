<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SubKegiatan;

class Kpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'bidang_id',
        'user_id',
        'nama_kpi',
        'satuan',
        'target',
        'realisasi',
        'bobot',
        'status',
        'keterangan',   // modern field
        'ket',          // backward-compat (migration lama)
        'capaian',
        'triwulan',
        'bukti_dukung',
        'rekomendasi',  // optional
    ];

    /**
     * Relasi ke Bidang
     */
    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    /**
     * Relasi ke User (pembuat/owner)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * (Opsional) relasi ke SubKegiatan jika digunakan
     */
    public function subKegiatan()
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    /**
     * Pengukuran per triwulan (kpi_measurements)
     */
    public function measurements()
    {
        return $this->hasMany(\App\Models\KpiMeasurement::class, 'kpi_id');
    }
}
