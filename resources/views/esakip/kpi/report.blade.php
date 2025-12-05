@extends('layouts.app')

@section('title', 'Laporan KPI')

@push('style')
<style>
.table thead th { background-color: #007bff; color: white; }
.status-hijau { background-color: #28a745; color: white; font-weight: bold; }
.status-kuning { background-color: #ffc107; color: black; font-weight: bold; }
.status-merah { background-color: #dc3545; color: white; font-weight: bold; }
</style>
@endpush

@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Laporan KPI BKAD</h3>
    <div>
      {{-- Form sederhana untuk pilih bidang/tahun --}}
      <form method="GET" action="{{ route('esakip.kpi.report') }}" class="d-flex g-2">
        <select name="bidang_id" class="form-control me-2">
          <option value="">Semua Bidang</option>
          @foreach($bidangs as $b)
            <option value="{{ $b->id }}" @if(isset($bidangId) && $bidangId == $b->id) selected @endif>{{ $b->nama_bidang }}</option>
          @endforeach
        </select>
        <input type="number" name="tahun" class="form-control me-2" value="{{ $tahun ?? date('Y') }}" style="width:110px">
        <button type="submit" class="btn btn-primary me-2">Tampilkan</button>

        {{-- Cetak semua hasil filter (server PDF) --}}
        <a href="{{ route('esakip.kpi.report', array_merge(request()->all(), ['format'=>'pdf'])) }}" target="_blank" class="btn btn-danger">Cetak PDF</a>
      </form>
    </div>
  </div>

  <table class="table table-bordered table-striped">
    <thead class="text-center">
      <tr>
        <th>No</th>
        <th>Bidang</th>
        <th>Indikator</th>
        <th>Target</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($kpis as $index => $kpi)
      @php
        $capaian = $kpi->capaian ?? 0;
        $status = 'Tidak Tercapai';
        if ($capaian >= 90) $status = 'Tercapai';
        elseif ($capaian >= 70) $status = 'Cukup Tercapai';
      @endphp
      <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td>{{ $kpi->bidang->nama_bidang ?? '-' }}</td>
        <td>{{ $kpi->nama_kpi }}</td>
        <td class="text-center">{{ $kpi->target ?? '-' }}</td>
        <td class="text-center">{{ $kpi->realisasi ?? '-' }}</td>
        <td class="text-center">{{ number_format($capaian, 2) }}%</td>
        <td class="text-center {{ $status == 'Hijau' ? 'status-hijau' : ($status == 'Kuning' ? 'status-kuning' : 'status-merah') }}">
          {{ $status }}
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="mt-4 text-end">
    <small><em>Dicetak pada: {{ now()->format('d M Y, H:i') }}</em></small>
  </div>
</div>
@endsection
