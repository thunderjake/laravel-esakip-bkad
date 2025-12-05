<?php

namespace App\Http\Controllers\ESakip;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bidang;
use App\Models\TindakLanjut;
use Illuminate\Support\Facades\Auth;

class TindakLanjutKpiController extends Controller
{
    /**
     * Simpan tindak lanjut dari pimpinan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bidang_id' => 'required|integer|exists:bidangs,id',
            'nama_bidang' => 'nullable|string',
            'pesan' => 'required|string',
        ]);

        $bidang = Bidang::find($validated['bidang_id']);
        if (!$bidang) {
            return redirect()->back()->with('error', 'Bidang tidak ditemukan.');
        }

        $tindakLanjut = TindakLanjut::where('bidang_id', $bidang->id)
            ->where('status', 'baru')
            ->first();

        if ($tindakLanjut) {
            $tindakLanjut->update([
                'pesan' => $validated['pesan'],
                'status' => 'baru',
            ]);
        } else {
            TindakLanjut::create([
                'bidang_id' => $bidang->id,
                'pesan' => $validated['pesan'],
                'status' => 'baru',
            ]);
        }

        return redirect()
            ->route('esakip.dashboard')
            ->with('success', 'Tindak lanjut berhasil disimpan.');
    }

    /**
     * Tandai tindak lanjut selesai.
     */
    public function selesai($id)
    {
        $tindakLanjut = TindakLanjut::findOrFail($id);
        $tindakLanjut->update(['status' => 'selesai']);

        return redirect()->back()->with('success', 'Tindak lanjut telah ditandai selesai.');
    }

    /**
     * Tandai satu tindak lanjut sudah dilihat (AJAX).
     */
    public function markViewed(Request $request, $id)
    {
        $tindak = TindakLanjut::find($id);
        if (! $tindak) {
            return response()->json(['status' => 'error', 'message' => 'Tindak lanjut tidak ditemukan'], 404);
        }

        $user = Auth::user();

        if (in_array($user->role, ['bidang','admin_bidang','kepala_bidang']) && $tindak->bidang_id != $user->bidang_id) {
            return response()->json(['status' => 'error', 'message' => 'Tidak punya akses untuk menandai ini'], 403);
        }

        $tindak->update([
            'viewed_at' => now(),
            'viewed_by' => $user->id,
        ]);

        return response()->json(['status' => 'ok', 'message' => 'Tindak lanjut ditandai dilihat']);
    }

    /**
     * Tandai beberapa tindak lanjut sudah dilihat.
     * Body: { ids: [1,2,3] }
     */
    public function markMultipleViewed(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'IDs tidak ditemukan'], 400);
        }

        $user = Auth::user();
        $updated = 0;

        foreach ($ids as $id) {
            $tindak = TindakLanjut::find($id);
            if (! $tindak) continue;

            if (in_array($user->role, ['bidang','admin_bidang','kepala_bidang']) && $tindak->bidang_id != $user->bidang_id) {
                continue;
            }

            $tindak->update([
                'viewed_at' => now(),
                'viewed_by' => $user->id,
            ]);
            $updated++;
        }

        return response()->json(['status' => 'ok', 'updated' => $updated]);
    }
}
