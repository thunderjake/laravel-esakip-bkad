<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_bidang',
        'kepala_bidang',
        'nip_kepala',
    ];

    public function kpis()
    {
        return $this->hasMany(Kpi::class);
    }

    public function programs()
{
    return $this->hasMany(Program::class);
}
public function tindakLanjut()
{
    return $this->hasMany(TindakLanjut::class);
}

}
