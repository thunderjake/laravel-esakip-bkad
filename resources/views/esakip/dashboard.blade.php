@extends('layouts.app')

@section('title', 'Dashboard E-SAKIP')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">📊 Dashboard Kinerja E-SAKIP BKAD</h3>

    {{-- === Notifikasi Pimpinan === --}}
    @if(auth()->user()->role === 'pimpinan' && $showWarning)
        <div class="alert alert-warning">
            ⚠️ Ada bidang yang <b>tidak mencapai target</b>:
            <ul>
                @foreach($bidangMerah as $nama)
                    <li>{{ $nama }}</li>
                @endforeach
            </ul>
            <small>Silakan isi tindak lanjut pada tab “Rekap Keseluruhan”.</small>
        </div>
    @endif

    {{-- === Notifikasi Tindak Lanjut Pimpinan / Bidang === --}}
    @if(auth()->user()->role === 'pimpinan' && $tindakLanjut->isNotEmpty())
        <div class="alert alert-info">
            💬 <strong>Tindak Lanjut yang Belum Diselesaikan:</strong>
            <ul class="mb-0">
                @foreach($tindakLanjut as $tl)
                    <li>
                        <strong>{{ $tl->bidang->nama_bidang ?? '-' }}</strong> — {{ $tl->pesan }}
                        <form action="{{ route('tindak-lanjut.selesai', $tl->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-success btn-sm">Selesai</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(auth()->user()->role === 'bidang' && $tindakLanjut->isNotEmpty())
        <div class="alert alert-info">
            💬 <strong>Pesan dari Pimpinan:</strong>
            <ul class="mb-0">
                @foreach($tindakLanjut as $tl)
                    <li>{{ $tl->pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- === Tab Navigasi === --}}
    <ul class="nav nav-tabs mb-3" id="dashboardTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ringkasan-tab" data-bs-toggle="tab" data-bs-target="#ringkasan" type="button" role="tab">Ringkasan Umum</button>
        </li>

        @if(auth()->user()->role !== 'admin_bidang')
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="evaluasi-tab" data-bs-toggle="tab" data-bs-target="#evaluasi" type="button" role="tab">Evaluasi Kinerja</button>
        </li>
        @endif

        @if(auth()->user()->role === 'pimpinan')
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="rekap-tab" data-bs-toggle="tab" data-bs-target="#rekap" type="button" role="tab">Rekap Keseluruhan</button>
        </li>
        @endif
    </ul>

    <div class="tab-content" id="dashboardTabContent">
        {{-- === TAB 1: Ringkasan Umum === --}}
        <div class="tab-pane fade show active" id="ringkasan" role="tabpanel">
            <div class="card shadow-sm p-3">
                <h5>Rata-rata Capaian per Bidang</h5>
                <canvas id="chartBidang" height="120"></canvas>
                <hr>
                <div class="row text-center mt-3">
                    <div class="col-md-4"><h6>Total KPI</h6><h4>{{ $totalKpi }}</h4></div>
                    <div class="col-md-4"><h6>Total Bidang</h6><h4>{{ $totalBidang }}</h4></div>
                    <div class="col-md-4"><h6>Rata-rata Keseluruhan</h6><h4>{{ $rataKeseluruhan }}%</h4></div>
                </div>
            </div>
        </div>

        {{-- === TAB 2: Evaluasi Kinerja === --}}
        @if(auth()->user()->role !== 'admin_bidang')
        <div class="tab-pane fade" id="evaluasi" role="tabpanel">
            <div class="card shadow-sm p-3">
                <h5>Evaluasi Kinerja per Bidang</h5>
                <table class="table table-bordered mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>Bidang</th>
                            <th>Rata-rata Capaian (%)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap as $nama => $data)
                        <tr>
                            <td>{{ $nama }}</td>
                            <td>{{ $data['rata_capaian'] }}</td>
                            <td>
                                <span class="badge
                                    @if($data['status'] == 'Hijau') bg-success
                                    @elseif($data['status'] == 'Kuning') bg-warning
                                    @else bg-danger @endif">
                                    {{ $data['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- === TAB 3: Rekap Keseluruhan (Pimpinan) === --}}
        @if(auth()->user()->role === 'pimpinan')
        <div class="tab-pane fade" id="rekap" role="tabpanel">
            <div class="card shadow-sm p-3">
                <h5>Rekap Kinerja Bidang</h5>
                <table class="table table-bordered mt-3 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Bidang</th>
                            <th>Rata-rata (%)</th>
                            <th>Status</th>
                            <th>Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap as $nama => $data)
                        <tr>
                            <td>{{ $nama }}</td>
                            <td>{{ $data['rata_capaian'] }}</td>
                            <td>
                                <span class="badge
                                    @if($data['status'] == 'Hijau') bg-success
                                    @elseif($data['status'] == 'Kuning') bg-warning
                                    @else bg-danger @endif">
                                    {{ $data['status'] }}
                                </span>
                            </td>
                            <td>
                                @if($data['status'] == 'Merah')
                                    <button class="btn btn-sm btn-danger" onclick="openTindakLanjutModal('{{ $nama }}')">Isi Tindak Lanjut</button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- === Modal Isi Tindak Lanjut === --}}
<div class="modal fade" id="tindakLanjutModal" tabindex="-1" aria-labelledby="tindakLanjutLabel" aria-hidden="true">
  <div class="modal-dialog">
<form method="POST" action="{{ route('esakip.tindak-lanjut.store') }}">

    @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="tindakLanjutLabel">Isi Tindak Lanjut</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
        <input type="hidden" name="bidang_nama" id="bidangNama">

            <div class="mb-3">
                <label for="pesan" class="form-label">Pesan / Arahan</label>
                <textarea class="form-control" name="pesan" rows="3" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Kirim</button>
          </div>
        </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartBidang');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($rekap->keys()) !!},
        datasets: [{
            label: 'Capaian (%)',
            data: {!! json_encode($rekap->pluck('rata_capaian')) !!},
            backgroundColor: '#007bff'
        }]
    },
    options: {
        plugins: { legend: { display: false }},
        scales: { y: { beginAtZero: true, max: 120 } }
    }
});

function openTindakLanjutModal(bidang) {
    document.getElementById('bidangNama').value = bidang;
    new bootstrap.Modal(document.getElementById('tindakLanjutModal')).show();
}
</script>
@endpush
