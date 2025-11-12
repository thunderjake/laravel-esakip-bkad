@extends('layouts.app')

@section('title', 'Dashboard E-SAKIP BKAD')

@section('content')
<div class="section-header">
  <h1>E-SAKIP BKAD Kabupaten Barru</h1>
</div>

<div class="section-body">
  <div class="row">
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card card-statistic-1">
        <div class="card-icon bg-primary">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="card-wrap">
          <div class="card-header"><h4>KPI Aktif</h4></div>
          <div class="card-body">12</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card card-statistic-1">
        <div class="card-icon bg-success">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="card-wrap">
          <div class="card-header"><h4>Persentase Capaian</h4></div>
          <div class="card-body">89%</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card card-statistic-1">
        <div class="card-icon bg-warning">
          <i class="fas fa-bell"></i>
        </div>
        <div class="card-wrap">
          <div class="card-header"><h4>Notifikasi</h4></div>
          <div class="card-body">5</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card card-statistic-1">
        <div class="card-icon bg-info">
          <i class="fas fa-users"></i>
        </div>
        <div class="card-wrap">
          <div class="card-header"><h4>Pengguna Aktif</h4></div>
          <div class="card-body">24</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
    