<form action="{{ $action }}" method="POST">
  @csrf
  @if(in_array($method, ['PUT','PATCH'])) @method($method) @endif

  @php
    $user = auth()->user();
  @endphp

  {{-- Jika bukan admin_bidang, tampilkan pilihan bidang --}}
  @if ($user->role !== 'admin_bidang')
    <div class="form-group mb-3">
      <label for="bidang_id">Bidang</label>
      <select name="bidang_id" id="bidang_id" class="form-control" required>
        <option value="">-- Pilih Bidang --</option>
        @foreach ($bidangs as $bidang)
          <option value="{{ $bidang->id }}"
            {{ old('bidang_id', $kpi->bidang_id ?? '') == $bidang->id ? 'selected' : '' }}>
            {{ $bidang->nama_bidang }}
          </option>
        @endforeach
      </select>
    </div>
  @else
    {{-- Jika admin_bidang, sembunyikan dropdown tapi tetap kirim bidang_id otomatis --}}
    <input type="hidden" name="bidang_id" value="{{ $user->bidang_id }}">
  @endif

  <div class="form-group mb-3">
    <label>Nama KPI</label>
    <input type="text" name="nama_kpi" value="{{ old('nama_kpi', $kpi->nama_kpi ?? '') }}"
           class="form-control @error('nama_kpi') is-invalid @enderror" required>
    @error('nama_kpi') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="row">
    <div class="col-md-3 mb-3">
      <label>Satuan</label>
      <input type="text" name="satuan" placeholder="Dokumen/Unit"
             value="{{ old('satuan', $kpi->satuan ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3 mb-3">
      <label>Target</label>
      <input type="number" step="0.01" name="target"
             value="{{ old('target', $kpi->target ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3 mb-3">
      <label>Realisasi</label>
      <input type="number" step="0.01" name="realisasi"
             value="{{ old('realisasi', $kpi->realisasi ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3 mb-3">
      <label>Bukti Dukung (URL)</label>
      <input type="url" name="bukti_dukung"
             placeholder="Link Bukti Dukung"
             value="{{ old('bukti_dukung', $kpi->bukti_dukung ?? '') }}" class="form-control">
    </div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label>Triwulan</label>
      <select name="triwulan" class="form-control">
        <option value="">-- Pilih Triwulan --</option>
        <option value="TW1" {{ old('triwulan', $kpi->triwulan ?? '') == 'TW1' ? 'selected' : '' }}>Triwulan 1</option>
        <option value="TW2" {{ old('triwulan', $kpi->triwulan ?? '') == 'TW2' ? 'selected' : '' }}>Triwulan 2</option>
        <option value="TW3" {{ old('triwulan', $kpi->triwulan ?? '') == 'TW3' ? 'selected' : '' }}>Triwulan 3</option>
        <option value="TW4" {{ old('triwulan', $kpi->triwulan ?? '') == 'TW4' ? 'selected' : '' }}>Triwulan 4</option>
      </select>
    </div>
    <div class="col-md-6 mb-3">
      <label>Status (Otomatis)</label>
      <input type="text"
             class="form-control bg-light"
             value="{{ $kpi->status ?? 'Akan dihitung otomatis' }}"
             readonly>
    </div>
  </div>

  <div class="form-group mb-3">
    <label>Keterangan (Faktor Pendukung / Faktor Penghambat)</label>
    <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $kpi->keterangan ?? '') }}</textarea>
  </div>

  <button class="btn btn-primary">{{ $kpi ? 'Simpan Perubahan' : 'Tambah KPI' }}</button>
</form>
