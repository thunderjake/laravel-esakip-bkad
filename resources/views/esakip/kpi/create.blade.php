@extends('layouts.app')

@section('title', 'Tambah KPI')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">➕ Tambah Kinerja Instansi</h4>

    @include('esakip.kpi._form', [
        'action' => route('esakip.kpi.store'),
        'method' => 'POST',
        'kpi' => null,
        'bidangs' => $bidangs
    ])

    <a href="{{ route('esakip.kpi.index') }}" class="btn btn-secondary mt-3">Kembali</a>
</div>
@endsection
