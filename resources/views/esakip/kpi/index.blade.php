@extends('layouts.app')

@section('title', 'Data KPI')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📊 Data Kinerja Instansi</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('esakip.kpi.create') }}" class="btn btn-primary">+ Tambah KPI</a>
            <a href="{{ route('esakip.kpi.report') }}" class="btn btn-outline-secondary">🖨️ Cetak Kertas Kerja</a>
        </div>
    </div>

    @php
        use Illuminate\Support\Str;
        use Illuminate\Support\Facades\Storage;
    @endphp

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
                <th>Bukti Dukung</th>
                <th class="text-center" width="220px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kpis as $key => $kpi)
                @php
                    // gunakan nilai aman
                    $realisasi = is_null($kpi->realisasi) ? 0 : (float)$kpi->realisasi;
                    $target = is_null($kpi->target) ? 0 : (float)$kpi->target;
                    $persentase = ($target > 0 && $realisasi !== null) ? ($realisasi / $target) * 100 : 0;

                    if (!is_null($kpi->status) && $kpi->status == 'Hijau') {
                        $badge = 'bg-success';
                        $keteranganDisplay = $kpi->keterangan ?? 'Tercapai';
                    } elseif (!is_null($kpi->status) && $kpi->status == 'Kuning') {
                        $badge = 'bg-warning text-dark';
                        $keteranganDisplay = $kpi->keterangan ?? 'Perhatian';
                    } elseif (!is_null($kpi->status) && $kpi->status == 'Merah') {
                        $badge = 'bg-danger';
                        $keteranganDisplay = $kpi->keterangan ?? 'Tidak Tercapai';
                    } else {
                        $badge = 'bg-secondary';
                        $keteranganDisplay = $kpi->keterangan ?? 'Belum Dinilai';
                    }

                    // bukti dukung logic (kpi level)
                    $bd = trim((string)($kpi->bukti_dukung ?? ''));
                    $isUrl = filter_var($bd, FILTER_VALIDATE_URL);
                    $storageUrl = null;
                    if (!$isUrl && $bd !== '') {
                        if (Storage::exists($bd)) {
                            $storageUrl = Storage::url($bd);
                        }
                    }
                @endphp

                <tr>
                    <td>{{ $kpis->firstItem() + $key }}</td>
                    <td>{{ optional($kpi->bidang)->nama_bidang ?? '-' }}</td>
                    <td class="text-start">{{ $kpi->nama_kpi }}</td>
                    <td>{{ $kpi->satuan ?? '-' }}</td>
                    <td>{{ $target === 0 ? '-' : number_format($target, 2) }}</td>
                    <td>{{ $realisasi === 0 ? '-' : number_format($realisasi, 2) }}</td>
                    <td class="text-center">
                        @if($target > 0)
                            {{ number_format($persentase, 2) }}%
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center"><span class="badge {{ $badge }}">{{ $keteranganDisplay }}</span></td>
                    <td class="text-center">{{ $kpi->rekomendasi ?? ($kpi->keterangan ?? '-') }}</td>

                    {{-- Bukti Dukung (level KPI) --}}
                    <td>
                        @if(!empty($bd))
                            @php $displayText = Str::limit($bd, 60); @endphp

                            @if($isUrl)
                                <a href="{{ $bd }}" target="_blank" rel="noopener noreferrer">🔗 {{ $displayText }}</a>
                            @elseif($storageUrl)
                                <a href="{{ $storageUrl }}" target="_blank" rel="noopener noreferrer">📁 {{ $displayText }}</a>
                            @else
                                <span title="{{ $bd }}">{{ $displayText }}</span>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <a href="{{ route('esakip.kpi.edit', $kpi->id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>

                        {{-- Tombol Pengukuran (buka modal) --}}
                        <button
                            type="button"
                            class="btn btn-sm btn-primary mb-1 btn-measure"
                            data-bs-toggle="modal"
                            data-bs-target="#measurementModal"
                            data-kpiid="{{ $kpi->id }}"
                            data-kpiname="{{ e($kpi->nama_kpi) }}"
                        >
                            Pengukuran
                        </button>

                        {{-- Lihat Selengkapnya (buka modal Riwayat langsung) --}}
                        <button
                            type="button"
                            class="btn btn-sm btn-secondary mb-1 btn-view-history"
                            data-kpiid="{{ $kpi->id }}"
                            data-kpiname="{{ e($kpi->nama_kpi) }}"
                        >
                            Lihat Selengkapnya
                        </button>

                        {{-- Cetak single KPI (buka PDF di tab baru) --}}
                        <a href="{{ route('esakip.kpi.printSingle', $kpi->id) }}" target="_blank" class="btn btn-sm btn-info mb-1">Cetak</a>

                        <form action="{{ route('esakip.kpi.destroy', $kpi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center">Belum ada data Kinerja Instansi</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $kpis->links() }}
    </div>
</div>

<br><br><br><br>
<p>Keterangan: <span style="color: red">Inputkan Faktor Pendukung ataupun Faktor Penghambat</span></p>

<!-- Modal Measurement (Tambah + Riwayat) -->
<div class="modal fade" id="measurementModal" tabindex="-1" aria-labelledby="measurementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="measurementModalLabel">Pengukuran KPI</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs mb-3" id="measurementTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-add" data-bs-toggle="tab" data-bs-target="#tabContentAdd" type="button" role="tab">Tambah Pengukuran</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-history" data-bs-toggle="tab" data-bs-target="#tabContentHistory" type="button" role="tab">Riwayat Pengukuran</button>
          </li>
        </ul>

        <div class="tab-content">
          <!-- Tab: Add -->
          <div class="tab-pane fade show active" id="tabContentAdd" role="tabpanel">
            <!-- Note: name="tahun" agar sesuai controller/migration -->
            <form id="measurementForm" method="POST" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="kpi_id" id="modal_kpi_id">

              <div class="row">
                <div class="col-md-3 mb-2">
                  <label for="tahun" class="form-label">Tahun</label>
                  <!-- ganti name="year" menjadi name="tahun" -->
                <input type="number" name="tahun" id="year" class="form-control" value="{{ date('Y') }}" required>

                </div>

                <div class="col-md-3 mb-2">
                  <label for="triwulan" class="form-label">Triwulan</label>
                  <select name="triwulan" id="triwulan" class="form-select" required>
                    <option value="">Pilih Triwulan</option>
                    <option value="1">Triwulan I</option>
                    <option value="2">Triwulan II</option>
                    <option value="3">Triwulan III</option>
                    <option value="4">Triwulan IV</option>
                  </select>
                </div>

                <div class="col-md-3 mb-2">
                  <label for="target" class="form-label">Target (opsional)</label>
                  <input type="number" step="0.01" name="target" id="target" class="form-control">
                </div>

                <div class="col-md-3 mb-2">
                  <label for="realisasi" class="form-label">Realisasi</label>
                  <input type="number" step="0.01" name="realisasi" id="realisasi" class="form-control">
                </div>

                <div class="col-md-6 mb-2">
                  <label for="bukti_dukung" class="form-label">Bukti Dukung (file, max 20MB)</label>
                  <input type="file" name="bukti_dukung" id="bukti_dukung" class="form-control">
                </div>

                <div class="col-md-6 mb-2">
                  <label for="catatan" class="form-label">Catatan</label>
                  <textarea name="catatan" id="catatan" class="form-control" rows="2"></textarea>
                </div>
              </div>

              <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pengukuran</button>
              </div>
            </form>
          </div>

          <!-- Tab: History -->
          <div class="tab-pane fade" id="tabContentHistory" role="tabpanel">
            <div id="historyArea">
              <div class="text-muted mb-2">Memuat riwayat...</div>
              <div class="table-responsive">
                <table class="table table-sm table-bordered" id="historyTable">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Tahun</th>
                      <th>Triwulan</th>
                      <th>Target</th>
                      <th>Realisasi</th>
                      <th>Capaian</th>
                      <th>Status</th>
                      <th>Catatan</th>
                      <th>Bukti</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
              <div class="mt-2 text-muted small">Jika tombol aksi tidak muncul, periksa izin user atau route API belum dibuat.</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const measurementModal = document.getElementById('measurementModal');
    const form = document.getElementById('measurementForm');
    const historyTableBody = document.querySelector('#historyTable tbody');

    // helper: format number
    function fmt(n) {
        if (n === null || n === undefined) return '-';
        return parseFloat(n).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    measurementModal.addEventListener('show.bs.modal', function (event) {
        // button that triggered
        const button = event.relatedTarget;
        const kpiId = button.getAttribute('data-kpiid');
        const kpiName = button.getAttribute('data-kpiname') || '';

        // isi hidden form & action
        document.getElementById('modal_kpi_id').value = kpiId;
        form.action = '/esakip/kpi/' + kpiId + '/measurement';

        // ubah judul modal
        const title = measurementModal.querySelector('.modal-title');
        title.textContent = 'Pengukuran: ' + kpiName;

        // jika membuka modal, default tab Add. (Riwayat akan dimuat saat tab dipilih)
        const addTab = document.querySelector('#tab-add');
        addTab.click();

        // kosongkan history table sementara
        historyTableBody.innerHTML = '<tr><td colspan="10" class="text-center">Belum memuat riwayat.</td></tr>';
    });

    // When user clicks "Lihat Selengkapnya" (button di baris), open modal and switch to history tab
    document.querySelectorAll('.btn-view-history').forEach(btn => {
        btn.addEventListener('click', function (ev) {
            const kpiId = this.getAttribute('data-kpiid');
            const kpiName = this.getAttribute('data-kpiname') || '';
            // programmatically open modal
            const modal = new bootstrap.Modal(document.getElementById('measurementModal'));
            // set necessary fields
            document.getElementById('modal_kpi_id').value = kpiId;
            form.action = '/esakip/kpi/' + kpiId + '/measurement';
            const title = measurementModal.querySelector('.modal-title');
            title.textContent = 'Pengukuran: ' + kpiName;
            modal.show();
            // switch to history tab
            document.querySelector('#tab-history').click();
            // load history
            loadHistory(kpiId);
        });
    });

    // load history when history tab becomes active
    document.getElementById('tab-history').addEventListener('shown.bs.tab', function () {
        const kpiId = document.getElementById('modal_kpi_id').value;
        if (kpiId) loadHistory(kpiId);
    });

    // function to fetch history (expects JSON from server)
    async function loadHistory(kpiId) {
        historyTableBody.innerHTML = '<tr><td colspan="10" class="text-center">Memuat...</td></tr>';
        try {
            const res = await fetch(`/esakip/kpi/${kpiId}/measurements`);
            if (!res.ok) throw new Error('Network response not ok');
            const data = await res.json();

            if (!Array.isArray(data) || data.length === 0) {
                historyTableBody.innerHTML = '<tr><td colspan="10" class="text-center">Belum ada pengukuran.</td></tr>';
                return;
            }

            historyTableBody.innerHTML = '';
            data.forEach((m, idx) => {
                // gunakan m.tahun (sesuai controller yang mengembalikan 'tahun')
                const cap = (m.target && m.target > 0 && m.realisasi !== null) ? ((m.realisasi / m.target) * 100).toFixed(2) + '%' : '-';
                const buktiHtml = m.bukti_dukung ? `<a href="${m.bukti_dukung}" target="_blank">🔗 ${ (m.bukti_filename || 'Bukti') }</a>` : '-';

                const editUrl = `/esakip/kpi/measurement/${m.id}/edit`; // optional
                const deleteAction = `/esakip/kpi/measurement/${m.id}`;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${idx+1}</td>
                    <td class="text-center">${m.tahun}</td>
                    <td class="text-center">${m.triwulan}</td>
                    <td class="text-end">${m.target !== null ? Number(m.target).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
                    <td class="text-end">${m.realisasi !== null ? Number(m.realisasi).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
                    <td class="text-center">${cap}</td>
                    <td class="text-center">${m.status ?? '-'}</td>
                    <td>${m.catatan ? m.catatan : '-'}</td>
                    <td>${buktiHtml}</td>
                    <td class="text-center">
                        ${m.can_edit ? `<a href="${editUrl}" class="btn btn-sm btn-warning mb-1">Edit</a>` : ''}
                        ${m.can_delete ? `
                            <form action="${deleteAction}" method="POST" style="display:inline" onsubmit="return confirm('Hapus measurement?');">
                                <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').getAttribute('content')}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        ` : ''}
                    </td>
                `;
                historyTableBody.appendChild(row);
            });
        } catch (err) {
            console.error(err);
            historyTableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Gagal memuat riwayat (periksa route /esakip/kpi/{id}/measurements).</td></tr>';
        }
    }

    // optional: reset form when modal hidden
    measurementModal.addEventListener('hidden.bs.modal', function () {
        form.reset();
        historyTableBody.innerHTML = '<tr><td colspan="10" class="text-center">Belum memuat riwayat.</td></tr>';
    });

    // Attach click for .btn-measure to clear form & set proper action (also covered in show.bs.modal)
    document.querySelectorAll('.btn-measure').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('measurementForm').reset();
        });
    });
});
</script>
@endpush
