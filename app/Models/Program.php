<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    // Program.php
class Program extends Model {
    protected $fillable = ['bidang_id', 'kode_program', 'nama_program'];
    public function bidang() { return $this->belongsTo(Bidang::class); }
    public function kegiatans() { return $this->hasMany(Kegiatan::class); }
}


