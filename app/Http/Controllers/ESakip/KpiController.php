<?php

namespace App\Http\Controllers\ESakip;

use App\Models\Kpi;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Bidang;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;


class KpiController extends Controller
{
    public function index()
    {

    $user = auth()->user(); // Ambil user yang sedang login

        if ($user->role === 'admin_bidang') {
            // Admin Bidang hanya lihat data KPI miliknya
            $kpis = Kpi::with('bidang')
                        ->where('bidang_id', $user->bidang_id)
                        ->orderByDesc('id')
                        ->paginate(10);
        } else {
            // Superadmin & Pimpinan bisa lihat semua
            $kpis = Kpi::with('bidang')
                        ->orderByDesc('id')
                        ->paginate(10);
        }

    return view('esakip.kpi.index', compact('kpis'));
    }

    public function create()
    {
        $bidangs = Bidang::all();
        return view('esakip.kpi.create', compact('bidangs'));
    }

public function store(Request $request)
{
    $user = auth()->user();

    // Validasi input
    $validated = $request->validate([
        'bidang_id' => 'nullable',
        'nama_kpi' => 'required|string|max:255',
        'satuan' => 'nullable|string|max:50',
        'target' => 'nullable|numeric',
        'realisasi' => 'nullable|numeric',
        'bukti_dukung' => 'nullable|string',
        'triwulan' => 'nullable|string',
    ]);

    // Otomatis set bidang_id untuk admin_bidang
    if ($user->role === 'admin_bidang') {
        $validated['bidang_id'] = $user->bidang_id;
    }

    // Hitung capaian dan status otomatis
    $validated['capaian'] = ($validated['target'] > 0 && $validated['realisasi'] !== null)
        ? ($validated['realisasi'] / $validated['target']) * 100
        : 0;

    if (!is_null($validated['target']) && !is_null($validated['realisasi'])) {
        if ($validated['realisasi'] >= $validated['target']) {
            $validated['status'] = 'Hijau';
            $validated['keterangan'] = 'Tercapai';
        } else {
            $validated['status'] = 'Merah';
            $validated['keterangan'] = 'Tidak Tercapai';
        }
    } else {
        $validated['status'] = null;
        $validated['keterangan'] = null;
    }

    // Simpan hanya sekali
    Kpi::create($validated);

    return redirect()->route('esakip.kpi.index')->with('success', 'Data KPI berhasil ditambahkan.');
}


    public function edit(Kpi $kpi)
    {
        $bidangs = Bidang::all();
        return view('esakip.kpi.edit', compact('kpi', 'bidangs'));
    }

    public function update(Request $request, Kpi $kpi)
    {  $user = auth()->user();

    // Validasi input
    $validated = $request->validate([
        'bidang_id' => 'nullable',
        'nama_kpi' => 'required|string|max:255',
        'satuan' => 'nullable|string|max:50',
        'target' => 'nullable|numeric',
        'realisasi' => 'nullable|numeric',
        'bukti_dukung' => 'nullable|string',
        'triwulan' => 'nullable|string',
    ]);
     // Otomatis set bidang_id untuk admin_bidang
if ($user->role === 'admin_bidang' && $kpi->bidang_id !== $user->bidang_id) {
    return redirect()->route('esakip.kpi.index')
        ->with('error', 'Anda tidak memiliki akses untuk mengubah data bidang lain.');
}


    // Hitung capaian & status otomatis
    $validated['capaian'] = ($validated['target'] > 0 && $validated['realisasi'] !== null)
        ? ($validated['realisasi'] / $validated['target']) * 100
        : 0;

    if (!is_null($validated['target']) && !is_null($validated['realisasi'])) {
        if ($validated['realisasi'] >= $validated['target']) {
            $validated['status'] = 'Hijau';
            $validated['keterangan'] = 'Tercapai';
        } else {
            $validated['status'] = 'Merah';
            $validated['keterangan'] = 'Tidak Tercapai';
        }
    } else {
        $validated['status'] = null;
        $validated['keterangan'] = null;
    }

    // Update data KPI
    $kpi->update($validated);

    return redirect()->route('esakip.kpi.index')->with('success', 'Data KPI berhasil diperbarui.');
}

    public function destroy(Kpi $kpi)
    {
        $user = auth()->user();

if ($user->role === 'admin_bidang' && $kpi->bidang_id !== $user->bidang_id) {
    return redirect()->route('esakip.kpi.index')
        ->with('error', 'Anda tidak memiliki akses untuk menghapus data bidang lain.');
}

        $kpi->delete();
        return redirect()->route('esakip.kpi.index')->with('success', 'Data KPI berhasil dihapus.');
    }

 public function report()
{
    $user = auth()->user();

    if ($user->role === 'admin_bidang') {
        // admin bidang hanya bisa lihat datanya sendiri
        $kpis = Kpi::with('bidang')->where('bidang_id', $user->bidang_id)->get();
    } else {
        // pimpinan dan superadmin bisa lihat semua bidang
        $kpis = Kpi::with('bidang')->get();
    }

    return view('esakip.kpi.report', compact('kpis'));
}


public function hierarki()
{
    $bidangs = Bidang::with('programs.kegiatans.subKegiatans.kpis')->get();
    return view('esakip.kpi.hierarki', compact('bidangs'));
}

//FUNGSI REPORT WORD

public function exportWord()
{

    $user = auth()->user();

    // Ambil data KPI berdasarkan role
    if ($user->role === 'admin_bidang') {
        $kpis = \App\Models\Kpi::with('bidang')
            ->where('bidang_id', $user->bidang_id)
            ->get();
    } else {
        $kpis = \App\Models\Kpi::with('bidang')->get();
    }

    // Ambil template
    $templatePath = storage_path('app/templates/Laporan_KPI.docx');
    $template = new TemplateProcessor($templatePath);

    // --- Header laporan ---
    $template->setValue('tahun', date('Y'));
    $template->setValue('jabatan', $user->jabatan ?? $user->name);
    $template->setValue('perangkat', $kpis->first()->bidang->nama_bidang ?? 'BKAD Kabupaten Barru');
    $template->setValue('tanggal', now()->translatedFormat('d F Y'));

    // --- Buat tabel dinamis KPI ---
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

    // --- Output file ---
    $fileName = 'Laporan_Pengukuran_Kinerja_' . date('Y') . '.docx';
    $tempFile = tempnam(sys_get_temp_dir(), $fileName);
    $template->saveAs($tempFile);

    return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
}
public function reportPrint()
{
    $user = auth()->user();

    if ($user->role === 'admin_bidang') {
        $kpis = \App\Models\Kpi::with('bidang')
            ->where('bidang_id', $user->bidang_id)
            ->get();
    } else {
        $kpis = \App\Models\Kpi::with('bidang')->get();
    }

    return view('esakip.kpi.report_print', compact('kpis'));
}
}
