<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubKegiatan extends Model
{
    use HasFactory;

    protected $fillable = ['kegiatan_id', 'kode_sub_kegiatan', 'nama_sub_kegiatan'];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function kpis()
    {
        return $this->hasMany(Kpi::class);
    }
}
