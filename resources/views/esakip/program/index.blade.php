@extends('layouts.app')
@section('title', 'Program & Kegiatan')

@section('content')
<div class="section-header">
  <h1>Program & Kegiatan</h1>
</div>

<div class="section-body">
  <div class="card">
    <div class="card-header">
      <h4>Struktur Program, Kegiatan, & Sub Kegiatan</h4>
    </div>
    <div class="card-body">
      @foreach($programs as $program)
        <div class="mb-3">
          <h5 class="text-primary">
            <i class="fas fa-folder-open"></i> {{ $program->kode_program }} - {{ $program->nama_program }}
          </h5>
          @foreach($program->kegiatans as $kegiatan)
            <div class="ms-3">
              <h6><i class="fas fa-tasks"></i> {{ $kegiatan->kode_kegiatan }} - {{ $kegiatan->nama_kegiatan }}</h6>
              <ul class="list-group list-group-flush ms-3">
                @foreach($kegiatan->subKegiatans as $sub)
                  <li class="list-group-item">
                    <i class="fas fa-circle text-success"></i>
                    {{ $sub->kode_sub_kegiatan }} - {{ $sub->nama_sub_kegiatan }}
                  </li>
                @endforeach
              </ul>
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
