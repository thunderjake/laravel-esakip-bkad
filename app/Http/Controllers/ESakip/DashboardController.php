<?php

namespace App\Http\Controllers\ESakip;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kpi;
use App\Models\Bidang;
use Illuminate\Support\Facades\Auth;
use App\Models\TindakLanjut;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil semua KPI dengan relasi bidang
        $kpis = Kpi::with('bidang')->get();
        $bidangs = Bidang::orderBy('nama_bidang')->get();

        // Hitung rata-rata capaian per bidang (berdasarkan realisasi / target)
        $rekap = $kpis->groupBy(fn($kpi) => $kpi->bidang->nama_bidang ?? 'Tanpa Bidang')
            ->map(function ($items) {
                $total = 0;
                $count = 0;

                foreach ($items as $kpi) {
                    if ($kpi->target && $kpi->target > 0) {
                        $total += ($kpi->realisasi / $kpi->target) * 100;
                        $count++;
                    }
                }

                $rata = $count > 0 ? $total / $count : 0;

                $status = 'Merah';
                if ($rata >= 90) $status = 'Hijau';
                elseif ($rata >= 70) $status = 'Kuning';

                return [
                    'rata_capaian' => round($rata, 2),
                    'status' => $status,
                ];
            });

        // Hitung total KPI, bidang, dan rata-rata keseluruhan
        $totalKpi = $kpis->count();
        $totalBidang = $bidangs->count();
        $rataKeseluruhan = round($rekap->avg('rata_capaian'), 2);

        // Deteksi bidang yang tidak tercapai (status merah)
        $bidangMerah = $rekap->filter(fn($r) => $r['status'] === 'Merah')->keys();

        // Tampilkan warning hanya untuk pimpinan
        $showWarning = false;
        if (Auth::check() && Auth::user()->role === 'pimpinan') {
            $showWarning = $bidangMerah->isNotEmpty();
        }
// Ambil tindak lanjut untuk dashboard
$tindakLanjut = [];

if (Auth::check()) {
    if (Auth::user()->role === 'pimpinan') {
        // Pimpinan lihat bidang yang masih merah dan belum ada tindak lanjut
        $tindakLanjut = TindakLanjut::with('bidang')
            ->where('status', 'baru')
            ->get();
    } elseif (Auth::user()->role === 'bidang') {
        // Bidang lihat pesan tindak lanjut yang ditujukan untuk mereka
        $tindakLanjut = TindakLanjut::whereHas('bidang', function ($q) {
            $q->where('nama_bidang', Auth::user()->bidang->nama_bidang ?? '');
        })->where('status', 'baru')->get();
    }
}

        return view('esakip.dashboard', compact(
            'rekap',
            'totalKpi',
            'totalBidang',
            'rataKeseluruhan',
            'bidangMerah',
            'showWarning',
            'tindakLanjut'
        ));
    }

    public function evaluasiKinerja()
    {
        // Ambil semua KPI beserta relasi bidang
        $kpis = Kpi::with('bidang')->get();

        $rekap = $kpis->groupBy(fn($kpi) => $kpi->bidang->nama_bidang ?? 'Tanpa Bidang')
            ->map(function ($items) {
                $total = 0;
                $count = 0;
                foreach ($items as $kpi) {
                    if ($kpi->target && $kpi->target > 0) {
                        $total += ($kpi->realisasi / $kpi->target) * 100;
                        $count++;
                    }
                }
                $rata = $count > 0 ? $total / $count : 0;
                $status = 'Merah';
                if ($rata >= 90) $status = 'Hijau';
                elseif ($rata >= 70) $status = 'Kuning';
                return [
                    'rata_capaian' => round($rata, 2),
                    'status' => $status,
                ];
            });

        return view('esakip.dashboard-kinerja', compact('rekap'));
    }

    public function filter(Request $request)
    {
        $query = Kpi::query();

        if ($request->bidang_id) $query->where('bidang_id', $request->bidang_id);
        if ($request->tahun) $query->whereYear('created_at', $request->tahun);

        $kpis = $query->with('bidang')->get();

        return response()->json($kpis);
    }

    public function kinerja(Request $request)
    {
        $bidangs = Bidang::orderBy('nama_bidang')->get();
        $bidangId = $request->get('bidang_id');
        $tahun = $request->get('tahun', date('Y'));

        $query = Kpi::with('bidang');
        if ($bidangId) $query->where('bidang_id', $bidangId);
        if ($tahun) $query->whereYear('created_at', $tahun);

        $kpis = $query->get();

        $rekap = $kpis->groupBy(fn($kpi) => $kpi->bidang->nama_bidang ?? 'Tanpa Bidang')
            ->map(function ($items) {
                $total = 0; $count = 0;
                foreach ($items as $kpi) {
                    if ($kpi->target && $kpi->target > 0) {
                        $total += ($kpi->realisasi / $kpi->target) * 100;
                        $count++;
                    }
                }
                $rata = $count > 0 ? $total / $count : 0;
                $status = 'Merah';
                if ($rata >= 90) $status = 'Hijau';
                elseif ($rata >= 70) $status = 'Kuning';
                return [
                    'rata_capaian' => round($rata, 2),
                    'status' => $status,
                ];
            });

        return view('esakip.dashboard-kinerja', compact('rekap', 'bidangs', 'tahun'));
    }

    public function ringkasanKeseluruhan()
    {
        $totalKpi = Kpi::count();
        $totalBidang = Bidang::count();

        $ringkasanPerBidang = Bidang::with(['kpis' => function ($q) {
            $q->select('id', 'bidang_id', 'target', 'realisasi');
        }])->get()->map(function ($bidang) {
            $total = 0;
            $count = 0;
            foreach ($bidang->kpis as $kpi) {
                if ($kpi->target && $kpi->target > 0) {
                    $total += ($kpi->realisasi / $kpi->target) * 100;
                    $count++;
                }
            }
            $rata = $count > 0 ? $total / $count : 0;
            return [
                'nama' => $bidang->nama_bidang,
                'rata' => round($rata, 2),
            ];
        });

        $rataNilaiKpi = round($ringkasanPerBidang->avg('rata'), 2);

        return view('esakip.dashboard-ringkasan', compact(
            'totalKpi',
            'rataNilaiKpi',
            'totalBidang',
            'ringkasanPerBidang'
        ));


    }
    public function selesaikanTindakLanjut($id)
{
    $tl = TindakLanjut::findOrFail($id);
    $tl->update(['status' => 'selesai']);
    return back()->with('success', 'Tindak lanjut telah diselesaikan.');
}

}
