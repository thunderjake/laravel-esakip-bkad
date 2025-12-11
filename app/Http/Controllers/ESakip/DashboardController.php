<?php

namespace App\Http\Controllers\ESakip;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kpi;
use App\Models\Bidang;
use App\Models\TindakLanjut;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Bangun rekap capaian per bidang berdasarkan KPI + kpi_measurements
     * untuk tahun tertentu. Dipakai oleh index() dan kinerja().
     */
    protected function buildRekapPerBidang(int $tahun, $onlyBidangId = null)
    {
        // load bidangs + kpis + measurements tahun tertentu
        $bidangQuery = Bidang::with([
            'kpis.measurements' => function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            },
            'kpis',
        ])->orderBy('nama_bidang');

        if ($onlyBidangId) {
            $bidangQuery->where('id', $onlyBidangId);
        }

        $bidangs = $bidangQuery->get();

        // susun rekap
        $rekap = $bidangs->mapWithKeys(function ($bidang) {
            // akumulator per triwulan
            $sumPercent = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            $count      = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

            // untuk rata keseluruhan bidang
            $allPercents = [];

            foreach ($bidang->kpis as $kpi) {
                // keyBy triwulan => langsung bisa akses $measurements[1..4]
                $measurements = $kpi->measurements->keyBy(function ($m) {
                    return (int) $m->triwulan;
                });

                for ($t = 1; $t <= 4; $t++) {
                    if (isset($measurements[$t])) {
                        $m = $measurements[$t];

                        // target: prioritas dari measurement, fallback ke kpi->target
                        $target = is_numeric($m->target)
                            ? (float) $m->target
                            : (is_numeric($kpi->target) ? (float) $kpi->target : null);

                        $realisasi = is_numeric($m->realisasi) ? (float) $m->realisasi : null;

                        if ($target !== null && $target > 0 && $realisasi !== null) {
                            $percent = ($realisasi / $target) * 100;
                            $sumPercent[$t] += $percent;
                            $count[$t]++;
                            $allPercents[] = $percent;
                        }
                    }
                }
            }

            // rata capaian per triwulan (kalau mau dipakai di masa depan)
            $rataTriwulan = [];
            for ($t = 1; $t <= 4; $t++) {
                $rataTriwulan[$t] = $count[$t] > 0
                    ? round($sumPercent[$t] / $count[$t], 2)
                    : null;
            }

            // rata keseluruhan bidang = rata dari semua nilai triwulan KPI
            $rataKeseluruhanBidang = count($allPercents) > 0
                ? round(array_sum($allPercents) / count($allPercents), 2)
                : 0;

            // status warna
            $status = 'Merah';
            if ($rataKeseluruhanBidang >= 90) {
                $status = 'Hijau';
            } elseif ($rataKeseluruhanBidang >= 70) {
                $status = 'Kuning';
            }

            // Tindak lanjut terbaru (status 'baru') untuk bidang ini – dipakai di dashboard utama
            $tindak = TindakLanjut::where('bidang_id', $bidang->id)
                ->where('status', 'baru')
                ->first();

            return [
                $bidang->nama_bidang => [
                    'nama'            => $bidang->nama_bidang,
                    'bidang_id'       => $bidang->id,
                    'rata_capaian'    => $rataKeseluruhanBidang,
                    'rata_triwulan'   => $rataTriwulan, // array 1..4
                    'status'          => $status,
                    'tindak_lanjut'   => $tindak ? $tindak->pesan : null,
                    'tindak_lanjut_id'=> $tindak ? $tindak->id : null,
                ],
            ];
        });

        return $rekap;
    }

    /**
     * Dashboard utama (tab Ringkasan / Evaluasi / Rekap)
     */
    public function index(Request $request)
    {
        $tahun = (int) ($request->get('tahun') ?? date('Y'));

        // rekap semua bidang
        $rekap = $this->buildRekapPerBidang($tahun);

        // Hitung total KPI & bidang
        $totalKpi    = Kpi::count();
        $totalBidang = Bidang::count();

        // rata keseluruhan = rata dari rata_capaian setiap bidang
        $rataKeseluruhan = $rekap->count() > 0
            ? round($rekap->avg(fn ($r) => $r['rata_capaian']), 2)
            : 0;

        // bidang berstatus merah
        $bidangMerah = $rekap->filter(fn ($r) => $r['status'] === 'Merah')->keys();

        // warning hanya untuk pimpinan
        $showWarning   = false;
        $tindakLanjut  = collect([]);

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'pimpinan') {
                $showWarning = $bidangMerah->isNotEmpty();
                $tindakLanjut = TindakLanjut::with('bidang')
                    ->where('status', 'baru')
                    ->get();
            } elseif (in_array($user->role, ['bidang','admin_bidang','kepala_bidang'])) {
                if ($user->bidang_id) {
                    $tindakLanjut = TindakLanjut::where('bidang_id', $user->bidang_id)
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
            'tahun'
        ));
    }

    /**
     * Halaman khusus "Dashboard Evaluasi Kinerja" (monitoring).
     * SEKARANG: pakai logika yang sama dengan index(), plus filter bidang & tahun.
     */
    public function kinerja(Request $request)
    {
        $bidangs   = Bidang::orderBy('nama_bidang')->get(); // untuk dropdown
        $bidangId  = $request->get('bidang_id');
        $tahun     = (int) ($request->get('tahun') ?? date('Y'));

        // rekap hanya untuk bidang yang difilter (atau semua jika kosong)
        $rekap = $this->buildRekapPerBidang($tahun, $bidangId);

        return view('esakip.dashboard-kinerja', compact(
            'rekap',
            'bidangs',
            'tahun'
        ));
    }

    /**
     * (Opsional) masih ada method lama ini; kalau sudah tidak dipakai bisa dihapus.
     * Dibiarkan saja, tapi tidak dipakai lagi oleh route.
     */
    public function evaluasiKinerja()
    {
        // arahkan saja ke kinerja() tanpa filter supaya tidak bingung
        return redirect()->route('esakip.dashboard.kinerja');
    }

    public function filter(Request $request)
    {
        $query = Kpi::query();

        if ($request->bidang_id) $query->where('bidang_id', $request->bidang_id);
        if ($request->tahun)     $query->whereYear('created_at', $request->tahun);

        $kpis = $query->with('bidang')->get();

        return response()->json($kpis);
    }

    /**
     * Ringkasan keseluruhan (tab/halaman terpisah, masih pakai field kpis langsung)
     * Boleh dibiarkan seperti ini kalau memang format ini yang diinginkan.
     */
    public function ringkasanKeseluruhan()
    {
        $totalKpi    = Kpi::count();
        $totalBidang = Bidang::count();

        $ringkasanPerBidang = Bidang::with(['kpis' => function ($q) {
            $q->select('id','bidang_id','target','realisasi');
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
