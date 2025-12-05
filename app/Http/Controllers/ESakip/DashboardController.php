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
    /**
     * Tampilkan dashboard utama.
     * Mendukung query parameter 'tahun' untuk ringkasan triwulan.
     */
    public function index(Request $request)
    {
        $tahun = (int) ($request->get('tahun') ?? date('Y'));

        // Ambil bidangs dengan kpis + measurements untuk tahun yang dipilih (eager load)
        $bidangs = Bidang::with(['kpis.measurements' => function ($q) use ($tahun) {
            $q->where('tahun', $tahun);
        }, 'kpis'])->orderBy('nama_bidang')->get();

        // Struktur rekap per bidang
        $rekap = $bidangs->mapWithKeys(function ($bidang) use ($tahun) {
            // Inisialisasi akumulator per triwulan
            $sumPercent = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            $count = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

            // Untuk rata keseluruhan (ambil semua triwulan yang terisi)
            $allPercents = [];

            // Iterasi tiap KPI
            foreach ($bidang->kpis as $kpi) {
                // Ambil measurements untuk tahun tersebut, keyed by triwulan (1..4)
                $measurements = $kpi->measurements->keyBy(function ($m) {
                    return (int) $m->triwulan;
                });

                for ($t = 1; $t <= 4; $t++) {
                    if (isset($measurements[$t])) {
                        $m = $measurements[$t];

                        // Prefer measurement target jika tersedia, fallback ke kpi->target
                        $target = is_numeric($m->target) ? (float)$m->target : (is_numeric($kpi->target) ? (float)$kpi->target : null);
                        $realisasi = is_numeric($m->realisasi) ? (float)$m->realisasi : null;

                        if ($target !== null && $target > 0 && $realisasi !== null) {
                            $percent = ($realisasi / $target) * 100;
                            $sumPercent[$t] += $percent;
                            $count[$t]++;
                            $allPercents[] = $percent;
                        }
                    }
                }
            }

            // Hitung rata per triwulan
            $rataTriwulan = [];
            for ($t = 1; $t <= 4; $t++) {
                $rataTriwulan[$t] = $count[$t] > 0 ? round($sumPercent[$t] / $count[$t], 2) : null;
            }

            // Rata keseluruhan bidang: rata dari semua nilai triwulan yang tersedia
            $rataKeseluruhanBidang = count($allPercents) > 0 ? round(array_sum($allPercents) / count($allPercents), 2) : 0;

            // Tentukan status berdasarkan rata keseluruhan
            $status = 'Merah';
            if ($rataKeseluruhanBidang >= 90) $status = 'Hijau';
            elseif ($rataKeseluruhanBidang >= 70) $status = 'Kuning';

            // Ambil tindak lanjut terkini (status 'baru') untuk bidang ini
            $tindak = TindakLanjut::where('bidang_id', $bidang->id)
                ->where('status', 'baru')
                ->first();

            return [
                $bidang->nama_bidang => [
                    'nama' => $bidang->nama_bidang,
                    'bidang_id' => $bidang->id,
                    'rata_capaian' => $rataKeseluruhanBidang,
                    'rata_triwulan' => $rataTriwulan, // array 1..4 (null atau nilai)
                    'status' => $status,
                    'tindak_lanjut' => $tindak ? $tindak->pesan : null,
                    'tindak_lanjut_id' => $tindak ? $tindak->id : null,
                ]
            ];
        });

        // Hitung total KPI, bidang, dan rata-rata keseluruhan (bidang)
        $totalKpi = Kpi::count();
        $totalBidang = $bidangs->count();

        // rataKeseluruhan: rata dari rata_capaian setiap bidang (jika ada)
        $rataKeseluruhan = $rekap->count() > 0 ? round($rekap->avg(fn($r) => $r['rata_capaian']), 2) : 0;

        // Bidang yang tidak tercapai (status Merah)
        $bidangMerah = $rekap->filter(fn($r) => $r['status'] === 'Merah')->keys();

        // Tampilkan warning hanya untuk pimpinan
        $showWarning = false;
        if (Auth::check() && Auth::user()->role === 'pimpinan') {
            $showWarning = $bidangMerah->isNotEmpty();
        }

        // Ambil tindak lanjut untuk dashboard (pimpinan: semua 'baru', bidang: yang untuk mereka)
        $tindakLanjut = collect([]);
        if (Auth::check()) {
            if (Auth::user()->role === 'pimpinan') {
                $tindakLanjut = TindakLanjut::with('bidang')
                    ->where('status', 'baru')
                    ->get();
            } elseif (in_array(Auth::user()->role, ['bidang', 'admin_bidang', 'kepala_bidang'])) {
                $userBidangId = Auth::user()->bidang_id;
                if ($userBidangId) {
                    $tindakLanjut = TindakLanjut::where('bidang_id', $userBidangId)
                        ->where('status', 'baru')
                        ->get();
                }
            }
        }

        return view('esakip.dashboard', compact(
            'rekap',
            'totalKpi',
            'totalBidang',
            'rataKeseluruhan',
            'bidangMerah',
            'showWarning',
            'tindakLanjut',
            'tahun' // kirim tahun supaya blade bisa menampilkan / form filter
        ));
    }

    // --- fungsi lain tetap ada (kinerja, evaluasi, dll) ---
    public function evaluasiKinerja()
    {
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
