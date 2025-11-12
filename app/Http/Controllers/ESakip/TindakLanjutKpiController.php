<?php

namespace App\Http\Controllers\ESakip;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bidang;
use App\Models\TindakLanjut;

class TindakLanjutKpiController extends Controller
{
    /**
     * Simpan tindak lanjut dari pimpinan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bidang_nama' => 'required|string',
            'pesan' => 'required|string',
        ]);

        // Cari bidang berdasarkan nama (pastikan nama di dashboard sama dengan di tabel 'bidangs')
        $bidang = Bidang::where('nama', $request->bidang_nama)->first();

        if (!$bidang) {
            return redirect()->back()->with('error', 'Bidang tidak ditemukan.');
        }

        // Cek apakah sudah ada tindak lanjut sebelumnya untuk bidang ini
        $tindakLanjut = TindakLanjut::where('bidang_id', $bidang->id)->first();

        if ($tindakLanjut) {
            // Update pesan dan reset status jadi "baru"
            $tindakLanjut->update([
                'pesan' => $request->pesan,
                'status' => 'baru',
            ]);
        } else {
            // Simpan baru
            TindakLanjut::create([
                'bidang_id' => $bidang->id,
                'pesan' => $request->pesan,
                'status' => 'baru',
            ]);
        }

        return redirect()
            ->route('esakip.dashboard')
            ->with('success', 'Tindak lanjut berhasil disimpan.');
    }
}
