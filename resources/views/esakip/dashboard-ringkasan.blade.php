@extends('layouts.app')

@section('title', 'Dashboard Ringkasan E-SAKIP')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">📈 Ringkasan Keseluruhan Kinerja BKAD</h3>

    {{-- Kartu Statistik Utama --}}
    <div class="row text-center mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total KPI</h6>
                    <h3 class="fw-bold">{{ $totalKpi }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Bidang</h6>
                    <h3 class="fw-bold">{{ $totalBidang }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Rata-rata Nilai KPI</h6>
                    <h3 class="fw-bold text-primary">{{ $rataNilaiKpi }}%</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik dan Tabel Ringkasan --}}
    <div class="card shadow-sm p-4">
        <h5 class="mb-3">📊 Rata-rata Capaian per Bidang</h5>
        <canvas id="chartRingkasan" height="100"></canvas>

        <hr>

        <table class="table table-bordered mt-4">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Bidang</th>
                    <th>Rata-rata Capaian (%)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ringkasanPerBidang as $i => $bidang)
                @php
                    $status = 'Merah';
                    if ($bidang['rata'] >= 90) $status = 'Hijau';
                    elseif ($bidang['rata'] >= 70) $status = 'Kuning';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $bidang['nama'] }}</td>
                    <td>{{ $bidang['rata'] }}%</td>
                    <td>
                        <span class="badge
                            @if($status == 'Hijau') bg-success
                            @elseif($status == 'Kuning') bg-warning
                            @else bg-danger @endif">
                            {{ $status }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartRingkasan');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($ringkasanPerBidang->pluck('nama')) !!},
        datasets: [{
            label: 'Capaian (%)',
            data: {!! json_encode($ringkasanPerBidang->pluck('rata')) !!},
            backgroundColor: [
                'rgba(75, 192, 192, 0.6)',
                'rgba(255, 205, 86, 0.6)',
                'rgba(255, 99, 132, 0.6)',
                'rgba(54, 162, 235, 0.6)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        plugins: { legend: { display: false }},
        scales: {
            y: { beginAtZero: true, max: 120 }
        }
    }
});
</script>
@endpush
