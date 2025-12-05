<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TindakLanjut extends Model
{
    use HasFactory;

    protected $table = 'tindak_lanjut';

    protected $fillable = [
        'bidang_id',
        'pesan',
        'status',
        'viewed_at',
        'viewed_by',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'viewed_at',
    ];

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function viewer()
    {
        return $this->belongsTo(User::class, 'viewed_by');
    }
}
