<?php

namespace App\Http\Controllers\ESakip;

use App\Models\Kpi;
use App\Models\User;
use App\Models\Bidang;
use App\Models\KpiMeasurement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KpiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $user = auth()->user();

        $kpis = Kpi::with('bidang')
            ->when($user->role === 'admin_bidang', function ($q) use ($user) {
                return $q->where('bidang_id', $user->bidang_id);
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('esakip.kpi.index', compact('kpis'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $bidangs = Bidang::all();
        return view('esakip.kpi.create', compact('bidangs'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE KPI
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'bidang_id' => 'nullable|integer|exists:bidangs,id',
            'nama_kpi' => 'required|string|max:255',
            'satuan' => 'nullable|string|max:50',
            'target' => 'nullable|numeric',
            'realisasi' => 'nullable|numeric',
            'bukti_dukung' => 'nullable|string|max:1000',
            'triwulan' => 'nullable|string|max:50',
            'ket' => 'nullable|string|max:255',
        ]);

        if ($user->role === 'admin_bidang') {
            $validated['bidang_id'] = $user->bidang_id;
        }

        $target = $validated['target'] ?? null;
        $realisasi = $validated['realisasi'] ?? null;

        // Hitung capaian
        if ($target && $realisasi) {
            $validated['capaian'] = round(($realisasi / $target) * 100, 2);
            $validated['status'] = $realisasi >= $target ? 'Hijau' : 'Merah';
            $validated['keterangan'] = $realisasi >= $target ? 'Tercapai' : 'Tidak Tercapai';
        } else {
            $validated['capaian'] = 0;
            $validated['status'] = null;
            $validated['keterangan'] = null;
        }

        // Untuk DB ket NOT NULL
        $validated['ket'] = $validated['keterangan'] ?? ($validated['ket'] ?? '');

        $validated['user_id'] = $user->id;

        Kpi::create($validated);

        return redirect()->route('esakip.kpi.index')->with('success', 'Data KPI berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT KPI
    |--------------------------------------------------------------------------
    */
    public function edit(Kpi $kpi)
    {
        $bidangs = Bidang::all();
        return view('esakip.kpi.edit', compact('kpi', 'bidangs'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE KPI
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Kpi $kpi)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'bidang_id' => 'nullable|integer|exists:bidangs,id',
            'nama_kpi' => 'required|string|max:255',
            'satuan' => 'nullable|string|max:50',
            'target' => 'nullable|numeric',
            'realisasi' => 'nullable|numeric',
            'bukti_dukung' => 'nullable|string|max:1000',
            'triwulan' => 'nullable|string|max:50',
            'ket' => 'nullable|string|max:255',
        ]);

        if ($user->role === 'admin_bidang') {
            if ($kpi->bidang_id !== $user->bidang_id) {
                return back()->with('error', 'Tidak punya akses edit.');
            }
            $validated['bidang_id'] = $user->bidang_id;
        }

        $target = $validated['target'] ?? null;
        $realisasi = $validated['realisasi'] ?? null;

        if ($target && $realisasi) {
            $validated['capaian'] = round(($realisasi / $target) * 100, 2);
            $validated['status'] = $realisasi >= $target ? 'Hijau' : 'Merah';
            $validated['keterangan'] = $realisasi >= $target ? 'Tercapai' : 'Tidak Tercapai';
        } else {
            $validated['capaian'] = 0;
            $validated['status'] = null;
            $validated['keterangan'] = null;
        }

        $validated['ket'] = $validated['keterangan'] ?? ($validated['ket'] ?? '');
        $validated['user_id'] = $user->id;

        $kpi->update($validated);

        return redirect()->route('esakip.kpi.index')->with('success', 'Data KPI diperbarui.');
    }
public function destroy(Kpi $kpi)
{
    $user = auth()->user();

    // Batasi admin_bidang hanya boleh hapus KPI di bidangnya
    if ($user->role === 'admin_bidang' && $kpi->bidang_id !== $user->bidang_id) {
        return redirect()->back()->with('error', 'Tidak punya akses hapus KPI ini.');
    }

    // Hapus dulu semua pengukuran terkait (kalau tidak pakai ON DELETE CASCADE)
    KpiMeasurement::where('kpi_id', $kpi->id)->delete();

    // Hapus KPI
    $kpi->delete();

    return redirect()
        ->route('esakip.kpi.index')
        ->with('success', 'Data KPI berhasil dihapus.');
}

    /*
    |--------------------------------------------------------------------------
    | REPORT (tampilan & export PDF)
    |--------------------------------------------------------------------------
    */
    public function report(Request $request)
    {
        $user = auth()->user();

        $bidangs = Bidang::orderBy('nama_bidang')->get();
        $tahun = $request->get('tahun', date('Y'));
        $bidangId = $request->get('bidang_id', null);

        $query = Kpi::with('bidang');

        if ($user->role === 'admin_bidang') {
            $query->where('bidang_id', $user->bidang_id);
        } elseif ($bidangId) {
            $query->where('bidang_id', $bidangId);
        }

        if ($tahun) {
            // filter berdasarkan tahun pembuatan KPI (jika diperlukan)
            $query->whereYear('created_at', $tahun);
        }

        $kpis = $query->orderBy('bidang_id')->get();

        // jika diminta PDF via query param format=pdf
        if ($request->get('format') === 'pdf') {
            // gunakan view report_print (yang sudah ada) — kirim tahun & bidangId
            $pdf = Pdf::loadView('esakip.kpi.report_print', compact('kpis', 'tahun', 'bidangId'))
                ->setPaper('A4', 'portrait');

            return $pdf->stream("laporan_kpi_{$tahun}.pdf");
        }

        return view('esakip.kpi.report', compact('kpis', 'bidangs', 'tahun', 'bidangId'));
    }

    /*
    |--------------------------------------------------------------------------
    | Measurements JSON (dipakai oleh modal riwayat)
    |--------------------------------------------------------------------------
    */
    public function measurementsJson($id)
    {
        $user = auth()->user();
        $kpi = Kpi::with('bidang')->findOrFail($id);

        if ($user->role === 'admin_bidang' && $kpi->bidang_id !== $user->bidang_id) {
            return response()->json(['message' => 'Tidak punya akses.'], 403);
        }

        $measurements = KpiMeasurement::with('creator')
            ->where('kpi_id', $kpi->id)
            ->orderByDesc('tahun')
            ->orderByDesc('triwulan')
            ->get();

        $data = $measurements->map(function ($m) use ($user) {
            return [
                'id' => $m->id,
                // sediakan 'year' dan 'tahun' untuk kompatibilitas frontend
                'year' => (int) ($m->tahun ?? date('Y')),
                'tahun' => (int) ($m->tahun ?? date('Y')),
                'triwulan' => (int) $m->triwulan,
                'target' => $m->target !== null ? (float) $m->target : null,
                'realisasi' => $m->realisasi !== null ? (float) $m->realisasi : null,
                'bukti_dukung' => $m->bukti_url ?? null,
                'bukti_filename' => $m->bukti_filename ?? null,
                'catatan' => $m->catatan ?? null,
                'status' => $m->status ?? null,
                // tidak menulis created_by; baca fallback ke user_id jika ada
                'created_by' => $m->created_by ?? $m->user_id ?? null,
                'creator' => $m->creator ? ['id' => $m->creator->id, 'name' => $m->creator->name] : null,
                'can_edit' => ($user->id === ($m->created_by ?? $m->user_id)) || $user->role === 'superadmin',
                'can_delete' => ($user->id === ($m->created_by ?? $m->user_id)) || $user->role === 'superadmin',
            ];
        });

        return response()->json($data);
    }

    /*
    |--------------------------------------------------------------------------
    | HIERARKI (tidak diubah)
    |--------------------------------------------------------------------------
    */
    public function hierarki()
    {
        $bidangs = Bidang::with('programs.kegiatans.subKegiatans.kpis')->get();
        return view('esakip.kpi.hierarki', compact('bidangs'));
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT WORD (tetap seperti sebelumnya)
    |--------------------------------------------------------------------------
    */
    public function exportWord()
    {
        $user = auth()->user();

        if ($user->role === 'admin_bidang') {
            $kpis = Kpi::with('bidang')->where('bidang_id', $user->bidang_id)->get();
        } else {
            $kpis = Kpi::with('bidang')->get();
        }

        $templatePath = storage_path('app/templates/Laporan_KPI.docx');
        $template = new TemplateProcessor($templatePath);

        $template->setValue('tahun', date('Y'));
        $template->setValue('jabatan', $user->jabatan ?? $user->name);
        $template->setValue('perangkat', $kpis->first()->bidang->nama_bidang ?? 'BKAD Kabupaten Barru');
        $template->setValue('tanggal', now()->translatedFormat('d F Y'));

        $rows = [];
        foreach ($kpis as $index => $kpi) {
            $targetTri = $kpi->target ? $kpi->target / 4 : 0;
            $realTri = $kpi->realisasi ? $kpi->realisasi / 4 : 0;
            $capTri = $targetTri > 0 ? ($realTri / $targetTri) * 100 : 0;

            $rows[] = [
                'no' => $index + 1,
                'sasaran' => $kpi->bidang->nama_bidang ?? '-',
                'indikator' => $kpi->nama_kpi,
                'target' => number_format($kpi->target, 2),
                'target1' => number_format($targetTri, 2),
                'target2' => number_format($targetTri*2, 2),
                'target3' => number_format($targetTri*3, 2),
                'target4' => number_format($targetTri*4, 2),
                'real1' => number_format($realTri, 2),
                'real2' => number_format($realTri*2, 2),
                'real3' => number_format($realTri*3, 2),
                'real4' => number_format($realTri*4, 2),
                'cap1' => number_format($capTri, 2),
                'cap2' => number_format($capTri, 2),
                'cap3' => number_format($capTri, 2),
                'cap4' => number_format($capTri, 2),
            ];
        }

        $template->cloneRowAndSetValues('no', $rows);

        $fileName = 'Laporan_Pengukuran_Kinerja_' . date('Y') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $template->saveAs($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    /*
    |--------------------------------------------------------------------------
    | PRINT SINGLE KPI -> PDF
    |--------------------------------------------------------------------------
    */
    public function printSingle(Request $request, $id)
    {
        $kpi = Kpi::with('bidang')->findOrFail($id);
        $user = Auth::user();

        if ($user->role === 'admin_bidang' && $kpi->bidang_id !== $user->bidang_id) {
            return redirect()->back()->with('error', 'Anda tidak berwenang mencetak KPI ini.');
        }

        $kpis = collect([$kpi]);
        $bidangName = optional($kpi->bidang)->nama_bidang;
        $tahun = (int) ($request->get('tahun') ?? date('Y'));

        $pdf = Pdf::loadView('esakip.kpi.report_print', compact('kpis', 'bidangName', 'tahun'))
            ->setPaper('A4', 'landscape');

        return $pdf->stream("kpi_{$id}.pdf");
    }

    /*
    |--------------------------------------------------------------------------
    | STORE MEASUREMENT (per triwulan)
    |--------------------------------------------------------------------------
    */
    public function storeMeasurement(Request $request, Kpi $kpi)
    {
        $user = auth()->user();

        if ($user->role === 'admin_bidang' && $kpi->bidang_id !== $user->bidang_id) {
            return redirect()->back()->with('error', 'Tidak punya akses.');
        }

        // terima 'year' (view lama) atau 'tahun' (view baru)
        $validated = $request->validate([
            'year' => 'nullable|integer',
            'tahun' => 'nullable|integer',
            'triwulan' => 'required|in:1,2,3,4',
            'target' => 'nullable|numeric',
            'realisasi' => 'nullable|numeric',
            'bukti_dukung' => 'nullable|file|max:20480',
            'catatan' => 'nullable|string',
        ]);

        // normalisasi tahun
        $tahun = isset($validated['tahun']) && $validated['tahun'] ? (int)$validated['tahun']
                 : (isset($validated['year']) && $validated['year'] ? (int)$validated['year'] : (int) date('Y'));

        $data = [
            'tahun' => $tahun,
            'triwulan' => (int) $validated['triwulan'],
            'target' => isset($validated['target']) ? (float)$validated['target'] : null,
            'realisasi' => isset($validated['realisasi']) ? (float)$validated['realisasi'] : null,
            'catatan' => $validated['catatan'] ?? null,
            'kpi_id' => $kpi->id,
            'user_id' => $user->id,
        ];

        // file handling: simpan ke disk 'public' dan simpan path ke kolom 'bukti_file'
        if ($request->hasFile('bukti_dukung')) {
            $path = $request->file('bukti_dukung')->store('kpi_bukti', 'public');
            $data['bukti_file'] = $path;
        }

        // updateOrCreate berdasarkan unique (kpi_id, tahun, triwulan)
        KpiMeasurement::updateOrCreate(
            ['kpi_id' => $kpi->id, 'tahun' => $data['tahun'], 'triwulan' => $data['triwulan']],
            $data
        );

        return redirect()->back()->with('success', 'Pengukuran tersimpan.');
    }

    public function updateMeasurement(Request $request, $id)
    {
        $m = KpiMeasurement::findOrFail($id);
        $user = auth()->user();
        if ($user->role === 'admin_bidang' && $m->kpi->bidang_id !== $user->bidang_id) {
            return redirect()->back()->with('error', 'Tidak punya akses.');
        }

        $validated = $request->validate([
            'target' => 'nullable|numeric',
            'realisasi' => 'nullable|numeric',
            'bukti_dukung' => 'nullable|file|max:20480',
            'catatan' => 'nullable|string',
        ]);

        if ($request->hasFile('bukti_dukung')) {
            // hapus file lama jika ada (disk public)
            if ($m->bukti_file && Storage::disk('public')->exists($m->bukti_file)) {
                Storage::disk('public')->delete($m->bukti_file);
            }
            $m->bukti_file = $request->file('bukti_dukung')->store('kpi_bukti', 'public');
        }

        // update numeric/text fields bila ada
        if (array_key_exists('target', $validated)) {
            $m->target = $validated['target'];
        }
        if (array_key_exists('realisasi', $validated)) {
            $m->realisasi = $validated['realisasi'];
        }
        if (array_key_exists('catatan', $validated)) {
            $m->catatan = $validated['catatan'];
        }

        $m->save();

        return redirect()->back()->with('success', 'Pengukuran diperbarui.');
    }

    public function destroyMeasurement($id)
    {
        $m = KpiMeasurement::findOrFail($id);
        $user = auth()->user();
        if ($user->role === 'admin_bidang' && $m->kpi->bidang_id !== $user->bidang_id) {
            return redirect()->back()->with('error', 'Tidak punya akses.');
        }

        if ($m->bukti_file && Storage::disk('public')->exists($m->bukti_file)) {
            Storage::disk('public')->delete($m->bukti_file);
        }

        $m->delete();
        return redirect()->back()->with('success', 'Pengukuran dihapus.');
    }
}
