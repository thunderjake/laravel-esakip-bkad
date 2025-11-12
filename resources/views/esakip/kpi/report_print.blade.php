@extends('layouts.app')

@section('title', 'Laporan Pengukuran Kinerja - Cetak')

@push('style')
<style>
  body { font-family: 'Times New Roman', Times, serif; color: #000; margin: 15mm; font-size: 12px; }
  table { width: 100%; border-collapse: collapse; font-size: 12px; }
  th, td { border: 1px solid #000; padding: 5px; text-align: center; vertical-align: middle; }
  th { background: #f2f2f2; }
  .header-title { text-align: center; font-weight: bold; margin-bottom: 20px; }
  .signatures { margin-top: 50px; width: 100%; }
  .signature { width: 45%; display: inline-block; text-align: center; vertical-align: top; }
</style>
@endpush

@section('content')
<div class="container">
  <div class="header-title">
    <div>Pengukuran Kinerja Tahun {{ date('Y') }}</div>
    <div><strong>{{ auth()->user()->jabatan ?? '[Nama Jabatan Pelaksana]' }}</strong></div>
    <div><strong>{{ $kpis->first()->bidang->nama_bidang ?? '[Nama Perangkat Daerah] Kabupaten Barru' }}</strong></div>
  </div>

  <table>
    <thead>
      <tr>
        <th rowspan="2">No</th>
        <th rowspan="2">Sasaran Kinerja</th>
        <th rowspan="2">Indikator Kinerja</th>
        <th rowspan="2">Target 1 Tahun</th>
        <th colspan="4">Target (Akumulatif) Triwulan Ke-</th>
        <th colspan="4">Realisasi (Akumulatif) Triwulan Ke-</th>
        <th colspan="4">Capaian (%) Akumulatif Triwulan Ke-</th>
      </tr>
      <tr>
        <th>I</th><th>II</th><th>III</th><th>IV</th>
        <th>I</th><th>II</th><th>III</th><th>IV</th>
        <th>I</th><th>II</th><th>III</th><th>IV</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($kpis as $index => $kpi)
        @php
          $targetTri = $kpi->target ? $kpi->target / 4 : 0;
          $realTri = $kpi->realisasi ? $kpi->realisasi / 4 : 0;
          $capTri = $targetTri > 0 ? ($realTri / $targetTri) * 100 : 0;
        @endphp
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $kpi->bidang->nama_bidang ?? '-' }}</td>
          <td>{{ $kpi->nama_kpi }}</td>
          <td>{{ number_format($kpi->target, 2) }}</td>

          <td>{{ number_format($targetTri, 2) }}</td>
          <td>{{ number_format($targetTri * 2, 2) }}</td>
          <td>{{ number_format($targetTri * 3, 2) }}</td>
          <td>{{ number_format($targetTri * 4, 2) }}</td>

          <td>{{ number_format($realTri, 2) }}</td>
          <td>{{ number_format($realTri * 2, 2) }}</td>
          <td>{{ number_format($realTri * 3, 2) }}</td>
          <td>{{ number_format($realTri * 4, 2) }}</td>

          <td>{{ number_format($capTri, 2) }}</td>
          <td>{{ number_format($capTri, 2) }}</td>
          <td>{{ number_format($capTri, 2) }}</td>
          <td>{{ number_format($capTri, 2) }}</td>
        </tr>
      @empty
        <tr><td colspan="16">Belum ada data Kinerja Instansi.</td></tr>
      @endforelse
    </tbody>
  </table>

  <br><br>
  <table style="width:100%; border:none;">
    <tr>
      <td style="width:50%; vertical-align:top;">
        <strong>CATATAN / REKOMENDASI ATASAN LANGSUNG TRIWULAN SEBELUMNYA</strong>
        <div style="border:1px solid #000; height:70px; margin-top:5px;"></div>
      </td>
      <td style="width:50%; vertical-align:top;">
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
@endsection
