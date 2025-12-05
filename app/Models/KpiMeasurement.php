<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class KpiMeasurement extends Model
{
    use HasFactory;

    protected $table = 'kpi_measurements';


    protected $fillable = [
        'kpi_id',
        'tahun',        // sesuai skema DB
        'triwulan',
        'target',
        'realisasi',
        'bukti_file',   // path di disk public atau URL eksternal
        'bukti_dukung', // legacy fallback if needed
        'catatan',
        'user_id',      // kolom di DB
        'created_by',   // jika ada
        'status',       // 'draft' / 'final'
    ];

    // accessor tambahan agar mudah diakses di view/controller
    protected $appends = [
        'bukti_url',
        'bukti_filename',
    ];

    protected $casts = [
        'target' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'triwulan' => 'integer',
        'tahun' => 'integer',
    ];

    /**
     * Relasi ke KPI utama
     */
    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    /**
     * Siapa yang menginput (creator)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Fallback relasi user (jika memakai user_id)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mengembalikan URL yang dapat diakses untuk bukti_file / bukti_dukung.
     * - Jika tersimpan sebagai URL eksternal, kembalikan langsung.
     * - Jika tersimpan di disk 'public', buat URL via asset('storage/...')
     * - Jika tidak ditemukan file, kembalikan nilai mentah.
     *
     * Akses: $measurement->bukti_url
     */
    public function getBuktiUrlAttribute()
    {
        // prioritas: bukti_file (DB) lalu bukti_dukung (legacy)
        $raw = $this->attributes['bukti_file'] ?? $this->attributes['bukti_dukung'] ?? null;
        if (! $raw) return null;

        // jika url eksternal
        if (filter_var($raw, FILTER_VALIDATE_URL)) {
            return $raw;
        }

        // normalisasi path (hilangkan prefix 'public/' jika ada)
        $path = preg_replace('#^public/#', '', ltrim($raw, '/'));

        // jika file ada di disk public, kembalikan url via storage symlink
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . ltrim($path, '/'));
        }

        // fallback: kembalikan nilai mentah (caller akan menampilkannya)
        return $raw;
    }

    /**
     * Nama file bukti (filename) jika ada
     * Akses: $measurement->bukti_filename
     */
    public function getBuktiFilenameAttribute()
    {
        $raw = $this->attributes['bukti_file'] ?? $this->attributes['bukti_dukung'] ?? null;
        if (! $raw) return null;

        if (filter_var($raw, FILTER_VALIDATE_URL)) {
            $path = parse_url($raw, PHP_URL_PATH);
            return $path ? basename($path) : $raw;
        }

        $p = preg_replace('#^public/#', '', ltrim($raw, '/'));
        return basename($p);
    }
}
