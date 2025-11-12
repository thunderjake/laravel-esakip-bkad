<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TindakLanjut extends Model
{
    use HasFactory;

    protected $table = 'tindak_lanjut';
    protected $fillable = ['bidang_id', 'pesan', 'status'];

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }
}
