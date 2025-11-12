@extends('layouts.app')

@section('title', 'Data KPI')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📊 Data Kinerja Instansi</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('esakip.kpi.create') }}" class="btn btn-primary">+ Tambah KPI</a>
        <a href="{{ route('esakip.kpi.report') }}" class="btn btn-outline-secondary">
            🖨️ Cetak Kertas Kerja
        </a>
    </div>

    <table class="table table-bordered align-middle table-hover">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Bidang</th>
                <th>Nama KPI</th>
                <th>Satuan</th>
                <th>Target</th>
                <th>Realisasi</th>
                <th>Persentase</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th class="text-center" width="160px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kpis as $key => $kpi)
                @php
                    $realisasi = $kpi->realisasi ?? 0;
                    $target = $kpi->target ?? 0;
                    $persentase = $target > 0 ? ($realisasi / $target) * 100 : 0;

                    if ($persentase >= 100) {
                        $status = 'Hijau';
                        $badge = 'bg-success';
                        $keterangan = 'Tercapai';
                    } elseif ($persentase > 0 && $persentase < 100) {
                        $status = 'Merah';
                        $badge = 'bg-danger';
                        $keterangan = 'Tidak Tercapai';
                    } else {
                        $status = 'Abu';
                        $badge = 'bg-secondary';
                        $keterangan = 'Belum Dinilai';
                    }
                @endphp

                <tr>
                    <td>{{ $kpis->firstItem() + $key }}</td>
                    <td>{{ $kpi->bidang->nama_bidang ?? '-' }}</td>
                    <td>{{ $kpi->nama_kpi }}</td>
                    <td>{{ $kpi->satuan }}</td>
                    <td>{{ $target }}</td>
                    <td>{{ $realisasi }}</td>
                    <td>{{ number_format($persentase, 2) }}%</td>
                    <td>
                        <span class="badge {{ $badge }}">{{ $keterangan }}</span>
                    </td>
                    <td>
                        @if($status == 'Hijau')
                            Faktor Pendukung: {{ $kpi->rekomendasi ?? '-' }}
                        @elseif($status == 'Merah')
                            Faktor Penghambat: {{ $kpi->rekomendasi ?? '-' }}
                        @else
                            {{ $kpi->rekomendasi ?? '-' }}
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('esakip.kpi.edit', $kpi->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('esakip.kpi.destroy', $kpi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center">Belum ada data Kinerja Instansi</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $kpis->links() }}
    </div>
</div>

<br><br><br><br>
<p>Keterangan: <span style="color: red">Inputkan Faktor Pendukung ataupun Faktor Penghambat</span></p>

@endsection
