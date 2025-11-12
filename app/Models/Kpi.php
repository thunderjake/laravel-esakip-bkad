<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'bidang_id',
        'nama_kpi',
        'satuan',
        'target',
        'realisasi',
        'bobot',
        'status',
    ];

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function subKegiatan()
{
    return $this->belongsTo(SubKegiatan::class);
}

}
