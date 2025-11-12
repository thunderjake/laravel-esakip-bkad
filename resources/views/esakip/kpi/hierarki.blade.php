@extends('layouts.app')

@section('title', 'Hierarki KPI')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Data KPI Berdasarkan Struktur Bidang - Program - Kegiatan - Sub Kegiatan</h2>

    @forelse ($bidangs as $bidang)
        <div class="card mb-3 border-primary">
            <div class="card-header bg-primary text-white fw-bold">
                {{ $bidang->nama_bidang }}
            </div>
            <div class="card-body">
                @forelse ($bidang->programs as $program)
                    <div class="ms-3 mb-3">
                        <h5 class="text-success">Program: {{ $program->nama_program }}</h5>

                        @forelse ($program->kegiatans as $kegiatan)
                            <div class="ms-4 mb-2">
                                <h6 class="text-info">Kegiatan: {{ $kegiatan->nama_kegiatan }}</h6>

                                @forelse ($kegiatan->subKegiatans as $sub)
                                    <div class="ms-5 mb-2">
                                        <p><strong>Sub Kegiatan:</strong> {{ $sub->nama_sub_kegiatan }}</p>

                                        @if ($sub->kpis->count() > 0)
                                            <ul class="ms-4">
                                                @foreach ($sub->kpis as $kpi)
                                                    <li>{{ $kpi->nama_kpi }} — <strong>{{ $kpi->target }}</strong> ({{ $kpi->status_warna }})</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted ms-4">Belum ada KPI</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted ms-4">Belum ada Sub Kegiatan</p>
                                @endforelse
                            </div>
                        @empty
                            <p class="text-muted ms-3">Belum ada Kegiatan</p>
                        @endforelse
                    </div>
                @empty
                    <p class="text-muted">Belum ada Program</p>
                @endforelse
            </div>
        </div>
    @empty
        <p class="text-center text-muted">Belum ada data Bidang</p>
    @endforelse
</div>
@endsection
