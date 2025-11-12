@extends('layouts.app')

@section('title', 'Edit Tindak Lanjut KPI')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">✏️ Edit Tindak Lanjut KPI</h4>

    <form action="{{ route('tindak-lanjut.update', $tindakLanjut->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">KPI</label>
            <input type="text" class="form-control" value="{{ $tindakLanjut->kpi->nama_kpi ?? '-' }}" disabled>
        </div>

        <div class="mb-3">
            <label for="rekomendasi" class="form-label">Rekomendasi</label>
            <textarea name="rekomendasi" class="form-control" rows="3">{{ $tindakLanjut->rekomendasi }}</textarea>
        </div>

        <div class="mb-3">
            <label for="catatan_tindak_lanjut" class="form-label">Catatan Tindak Lanjut</label>
            <textarea name="catatan_tindak_lanjut" class="form-control" rows="3">{{ $tindakLanjut->catatan_tindak_lanjut }}</textarea>
        </div>

        <div class="mb-3">
            <label for="status_tindak_lanjut" class="form-label">Status</label>
            <select name="status_tindak_lanjut" class="form-select">
                <option value="Belum" {{ $tindakLanjut->status_tindak_lanjut == 'Belum' ? 'selected' : '' }}>Belum</option>
                <option value="Sudah" {{ $tindakLanjut->status_tindak_lanjut == 'Sudah' ? 'selected' : '' }}>Sudah</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
