@extends('layouts.app')

@section('title', 'Tambah Tindak Lanjut KPI')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">🆕 Tambah Tindak Lanjut KPI</h4>

    <form action="{{ route('tindak-lanjut.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="kpi_id" class="form-label">Pilih KPI</label>
            <select name="kpi_id" id="kpi_id" class="form-select" required>
                <option value="">-- Pilih KPI --</option>
                @foreach($kpis as $kpi)
                    <option value="{{ $kpi->id }}">{{ $kpi->nama_kpi }} ({{ $kpi->bidang->nama_bidang ?? '-' }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="rekomendasi" class="form-label">Rekomendasi</label>
            <textarea name="rekomendasi" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="catatan_tindak_lanjut" class="form-label">Catatan Tindak Lanjut</label>
            <textarea name="catatan_tindak_lanjut" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="status_tindak_lanjut" class="form-label">Status</label>
            <select name="status_tindak_lanjut" id="status_tindak_lanjut" class="form-select">
                <option value="Belum">Belum</option>
                <option value="Sudah">Sudah</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
