@extends('layouts.app')

@section('title', 'Edit KPI')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">✏️ Edit KPI</h4>

    @include('esakip.kpi._form', [
        'action' => route('esakip.kpi.update', $kpi->id),
        'method' => 'PUT',
        'kpi' => $kpi,
        'bidangs' => $bidangs
    ])

    <a href="{{ route('esakip.kpi.index') }}" class="btn btn-secondary mt-3">Kembali</a>
</div>
@endsection
