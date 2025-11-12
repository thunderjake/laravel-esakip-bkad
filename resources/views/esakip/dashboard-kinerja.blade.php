@extends('layouts.app')

@section('title', 'Dashboard Evaluasi KPI')

@push('style')
<link rel="stylesheet" href="{{ asset('library/bootstrap-social/bootstrap-social.css') }}">
<style>
.card-stat {
    border-left: 5px solid #007bff;
    border-radius: 10px;
}
.status-hijau { background-color: #28a745; color: white; font-weight: bold; }
.status-kuning { background-color: #ffc107; color: black; font-weight: bold; }
.status-merah { background-color: #dc3545; color: white; font-weight: bold; }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Dashboard Evaluasi Kinerja </h1>
    </div>

    <div class="section-body">

        {{-- FILTER --}}
        <div class="card mb-4">
            <div class="card-header">
                <h4>Filter Evaluasi KPI</h4>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('esakip.dashboard.kinerja') }}" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="bidang_id" class="mr-2">Bidang</label>
                       <select name="bidang_id" id="bidang_id" class="form-control">
                    <option value="">-- Semua Bidang --</option>
                   @php
  $bidangs = $bidangs ?? collect();
@endphp

                    @foreach ($bidangs as $bidang)
                        <option value="{{ $bidang->id }}" {{ request('bidang_id') == $bidang->id ? 'selected' : '' }}>
                            {{ $bidang->nama_bidang }}
                        </option>
                    @endforeach
                </select>

                    </div>

                    <div class="form-group mr-3">
                        <label for="tahun" class="mr-2">Tahun</label>
                        <select name="tahun" id="tahun" class="form-control">
                            @for ($y = date('Y') - 2; $y <= date('Y'); $y++)
                                <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Tampilkan
                    </button>
                </form>
            </div>
        </div>

        {{-- REKAP KPI --}}
        <div class="row">
            @forelse($rekap as $bidang => $data)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card card-stat shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="text-primary">{{ $bidang }}</h5>
                            <h3>{{ $data['rata_capaian'] }}%</h3>
                            <span class="badge
                                {{ $data['status'] == 'Hijau' ? 'badge-success' :
                                   ($data['status'] == 'Kuning' ? 'badge-warning' : 'badge-danger') }}">
                                {{ $data['status'] }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="text-muted">Tidak ada data Kinerja Instansi yang tersedia untuk filter ini.</p>
                </div>
            @endforelse
        </div>

        {{-- GRAFIK --}}
        <div class="card">
            <div class="card-header">
                <h4>Grafik Capaian Kinerja per Bidang</h4>
            </div>
            <div class="card-body">
                <canvas id="chartKinerja" height="120"></canvas>
            </div>
        </div>


        {{-- TOMBOL LAPORAN --}}
        <div class="text-right mt-3">
            <a href="{{ route('esakip.kpi.report') }}" class="btn btn-primary">
                <i class="fas fa-file-alt"></i> Lihat Laporan Kinerja Instansi
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartKinerja').getContext('2d');
const data = {
    labels: {!! json_encode($rekap->keys()) !!},
    datasets: [{
        label: 'Capaian (%)',
        data: {!! json_encode($rekap->pluck('rata_capaian')->values()) !!},
        borderWidth: 2,
        backgroundColor: [
            '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1', '#fd7e14'
        ],
    }]
};

new Chart(ctx, {
    type: 'bar',
    data: data,
    options: {
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        },
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'Capaian Kinerja Rata-Rata per Bidang'
            }
        }
    }
});
</script>
@endpush
