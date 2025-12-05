<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Laporan Pengukuran Kinerja - Cetak</title>
  <style>
    /* Styling khusus PDF (standalone) */
    @page { margin: 15mm; }
    html, body {
      font-family: "Times New Roman", Times, serif;
      color: #000;
      font-size: 12px;
      margin: 0;
      padding: 0;
    }
    .container { padding: 15mm; }
    .header-title { text-align: center; font-weight: bold; margin-bottom: 10px; }
  table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11px;
  table-layout: fixed;        /* pastikan kolom bisa pecah dan teks melipat */
  word-wrap: break-word;
}

/* kepala & sel */
th, td { border: 1px solid #000; padding: 10px 4px; vertical-align: middle; }
th { background: #eee; }

/* ulangi thead di setiap halaman (penting!) */
thead { display: table-header-group; }
tfoot { display: table-footer-group; }

/* hindari memecah baris tabel di tengah */
tr { page-break-inside: avoid; }

/* biarkan tabel terpotong antar halaman (bukan memaksa satu halaman penuh) */
tbody { display: table-row-group; }

/* kecilkan ukuran font di cell yang padat (opsional) */
.small-cell { font-size: 10px; }

/* signature area */
.signatures { margin-top: 30px; width: 100%; }
.signature { width: 45%; display: inline-block; text-align: center; vertical-align: top; }
    .small { font-size: 10px; color: #333; }
    /* small table for notes without borders (visual separation) */
    .notes { width:100%; margin-top:12px; }
    .notes td { border:none; vertical-align: top; padding:6px; }
  </style>
</head>
<body>
  <div class="container">
    @php
      // tahun yang dicetak (ambil dari query string atau default sekarang)
      $year = (int) ($tahun ?? request('tahun') ?? date('Y'));

    @endphp

    <div class="header-title">
      <div>Pengukuran Kinerja Tahun {{ $year }}</div>
      <div><strong>{{ optional(auth()->user())->jabatan ?? '[Nama Jabatan Pelaksana]' }}</strong></div>
      <div><strong>{{ $kpis->first()->bidang->nama_bidang ?? '[Nama Perangkat Daerah] Kabupaten Barru' }}</strong></div>
    </div>

    <table>
      <thead>
        <tr>
          <th rowspan="2" style="width:30px">No</th>
          <th rowspan="2">Sasaran Kinerja</th>
          <th rowspan="2">Indikator Kinerja</th>
          <th rowspan="2" style="width:70px">Target 1 Tahun</th>

          <th colspan="4">Target (Triwulan)</th>
          <th colspan="4">Realisasi (Triwulan)</th>
          <th colspan="4">Capaian (%) (Triwulan)</th>
        </tr>
        <tr>
          <th style="width:45px">I</th><th style="width:45px">II</th><th style="width:45px">III</th><th style="width:45px">IV</th>
          <th style="width:45px">I</th><th style="width:45px">II</th><th style="width:45px">III</th><th style="width:45px">IV</th>
          <th style="width:45px">I</th><th style="width:45px">II</th><th style="width:45px">III</th><th style="width:45px">IV</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($kpis as $index => $kpi)
          @php
            // Pastikan relasi measurements ada (harus ada model & relation)
            // Cari semua measurement untuk tahun yang dipilih, keyed by triwulan
            $measurements = collect();
            if (method_exists($kpi, 'measurements')) {
                // jika relasi belum eager-loaded, ini akan query per row (bisa dioptimize di controller)
                $measurements = $kpi->measurements()->where('tahun', $year)->get()->keyBy(function($m){ return (int)$m->triwulan; });
            }

            // default isi untuk 4 triwulan
            $targetCols = [1 => '-', 2 => '-', 3 => '-', 4 => '-'];
            $realCols   = [1 => '-', 2 => '-', 3 => '-', 4 => '-'];
            $capCols    = [1 => '-', 2 => '-', 3 => '-', 4 => '-'];

            // tampilkan target tahunan (dari kpi->target) jika ada
            $targetYearVal = is_numeric($kpi->target) ? (float)$kpi->target : null;

            // isi kolom berdasarkan measurement yang tersedia untuk tiap triwulan
            for ($t=1; $t<=4; $t++) {
                if ($measurements->has($t)) {
                    $m = $measurements->get($t);
                    // ada kemungkinan measurement menyimpan target/realisasi per triwulan
                    $tgt = isset($m->target) && $m->target !== null ? (float)$m->target : null;
                    $rls = isset($m->realisasi) && $m->realisasi !== null ? (float)$m->realisasi : null;

                    $targetCols[$t] = $tgt !== null ? number_format($tgt, 2) : ($targetYearVal !== null ? number_format($targetYearVal,2) : '-');
                    $realCols[$t]   = $rls !== null ? number_format($rls, 2) : '-';

                    if ($tgt !== null && $tgt > 0 && $rls !== null) {
                        $cap = ($rls / $tgt) * 100;
                        $capCols[$t] = number_format(round($cap, 2), 2) . '%';
                    } elseif ($tgt === null && $targetYearVal !== null && $targetYearVal > 0 && $rls !== null) {
                        // fallback jika measurement hanya simpan realisasi, gunakan target tahunan sebagai pembanding (opsional)
                        $cap = ($rls / $targetYearVal) * 100;
                        $capCols[$t] = number_format(round($cap, 2), 2) . '%';
                    } else {
                        $capCols[$t] = '-';
                    }
                } else {
                    // tidak ada measurement triwulan t -> tampil '-'
                    $targetCols[$t] = '-';
                    $realCols[$t] = '-';
                    $capCols[$t] = '-';
                }
            }
          @endphp

          <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="text-left">{{ $kpi->bidang->nama_bidang ?? '-' }}</td>
            <td class="text-left">{{ $kpi->nama_kpi }}</td>
            <td class="text-center">{{ $targetYearVal !== null ? number_format($targetYearVal, 2) : '-' }}</td>

            {{-- target triwulan --}}
            <td class="text-center">{{ $targetCols[1] }}</td>
            <td class="text-center">{{ $targetCols[2] }}</td>
            <td class="text-center">{{ $targetCols[3] }}</td>
            <td class="text-center">{{ $targetCols[4] }}</td>

            {{-- realisasi triwulan --}}
            <td class="text-center">{{ $realCols[1] }}</td>
            <td class="text-center">{{ $realCols[2] }}</td>
            <td class="text-center">{{ $realCols[3] }}</td>
            <td class="text-center">{{ $realCols[4] }}</td>

            {{-- capaian --}}
            <td class="text-center">{{ $capCols[1] }}</td>
            <td class="text-center">{{ $capCols[2] }}</td>
            <td class="text-center">{{ $capCols[3] }}</td>
            <td class="text-center">{{ $capCols[4] }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="16" class="no-data">Belum ada data Kinerja Instansi.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <table class="notes">
      <tr>
        <td style="width:50%;">
          <strong>CATATAN / REKOMENDASI ATASAN LANGSUNG TRIWULAN SEBELUMNYA</strong>
          <div style="border:1px solid #000; height:70px; margin-top:5px;"></div>
        </td>
        <td style="width:50%;">
          <strong>PERMASALAHAN TRIWULAN BERKENAAN</strong>
          <div style="border:1px solid #000; height:70px; margin-top:5px;"></div>
          <strong>CATATAN / REKOMENDASI ATASAN LANGSUNG</strong>
          <div style="border:1px solid #000; height:70px; margin-top:5px;"></div>
        </td>
      </tr>
    </table>

    <div class="signatures">
      <div class="signature">
        Mengetahui,<br><strong>[Atasan Langsung]</strong><br><br><br><br>
        (.....................................)
      </div>

      <div class="signature" style="float:right;">
        Barru, {{ now()->translatedFormat('d F Y') }}<br>
        <strong>[Nama Jabatan]</strong><br><br><br><br>
        (.....................................)
      </div>
    </div>
  </div>
</body>
</html>
