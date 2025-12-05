@extends('layouts.app')

@section('title', 'Dashboard E-SAKIP')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">📊 Dashboard Kinerja E-SAKIP BKAD</h3>

    {{-- === Notifikasi Pimpinan === --}}
    @if(auth()->user()->role === 'pimpinan' && $showWarning)
        <div class="alert alert-warning">
            ⚠️ Ada bidang yang <b>tidak mencapai target</b>:
            <ul>
                @foreach($bidangMerah as $nama)
                    <li>{{ $nama }}</li>
                @endforeach
            </ul>
            <small>Silakan isi tindak lanjut pada tab “Rekap Keseluruhan”.</small>
        </div>
    @endif

    {{-- === Notifikasi Tindak Lanjut Pimpinan (untuk Pimpinan melihat TL belum selesai) === --}}
    @if(auth()->user()->role === 'pimpinan' && $tindakLanjut->isNotEmpty())
        <div class="alert alert-info">
            💬 <strong>Tindak Lanjut yang Belum Diselesaikan:</strong>
            <ul class="mb-0">
                @foreach($tindakLanjut as $tl)
                    <li>
                        <strong>{{ $tl->bidang->nama_bidang ?? '-' }}</strong> — {{ $tl->pesan }}
                        <form action="{{ route('esakip.tindak-lanjut.selesai', $tl->id) }}" method="POST" class="d-inline ms-2">
                            @csrf
                            <button class="btn btn-success btn-sm">Selesai</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- === Pesan untuk Admin Bidang (akan juga ditampilkan modal/toast otomatis) === --}}
    @if(in_array(auth()->user()->role, ['bidang','admin_bidang','kepala_bidang']) && $tindakLanjut->isNotEmpty())
        <div class="alert alert-info">
            💬 <strong>Pesan dari Pimpinan:</strong>
            <ul class="mb-0">
                @foreach($tindakLanjut as $tl)
                    <li>{{ $tl->pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif


    {{-- === Tab Navigasi === --}}
    <ul class="nav nav-tabs mb-3" id="dashboardTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ringkasan-tab" data-bs-toggle="tab" data-bs-target="#ringkasan" type="button" role="tab">Ringkasan Umum</button>
        </li>

        @if(auth()->user()->role !== 'admin_bidang')
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="evaluasi-tab" data-bs-toggle="tab" data-bs-target="#evaluasi" type="button" role="tab">Evaluasi Kinerja</button>
        </li>
        @endif

        @if(auth()->user()->role === 'pimpinan')
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="rekap-tab" data-bs-toggle="tab" data-bs-target="#rekap" type="button" role="tab">Rekap Keseluruhan</button>
        </li>
        @endif
    </ul>

    <div class="tab-content" id="dashboardTabContent">
        {{-- === TAB 1: Ringkasan Umum === --}}
        <div class="tab-pane fade show active" id="ringkasan" role="tabpanel">
            <div class="card shadow-sm p-3">
                <h5>Rata-rata Capaian per Bidang</h5>
                <canvas id="chartBidang" height="120"></canvas>
                <hr>
                <div class="row text-center mt-3">
                    <div class="col-md-4"><h6>Total KPI</h6><h4>{{ $totalKpi }}</h4></div>
                    <div class="col-md-4"><h6>Total Bidang</h6><h4>{{ $totalBidang }}</h4></div>
                    <div class="col-md-4"><h6>Rata-rata Keseluruhan</h6><h4>{{ $rataKeseluruhan }}%</h4></div>
                </div>
            </div>
        </div>

        {{-- === TAB 2: Evaluasi Kinerja === --}}
        @if(auth()->user()->role !== 'admin_bidang')
        <div class="tab-pane fade" id="evaluasi" role="tabpanel">
            <div class="card shadow-sm p-3">
                <h5>Evaluasi Kinerja per Bidang</h5>
                <table class="table table-bordered mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>Bidang</th>
                            <th>Rata-rata Capaian (%)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap as $nama => $data)
                        <tr>
                            <td>{{ $nama }}</td>
                            <td>{{ $data['rata_capaian'] }}</td>
                            <td>
                                <span class="badge
                                    @if($data['status'] == 'Hijau') bg-success
                                    @elseif($data['status'] == 'Kuning') bg-warning
                                    @else bg-danger @endif">
                                    {{ $data['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- === TAB 3: Rekap Keseluruhan (Pimpinan) === --}}
        @if(auth()->user()->role === 'pimpinan')
        <div class="tab-pane fade" id="rekap" role="tabpanel">
            <div class="card shadow-sm p-3">
                <h5>Rekap Kinerja Bidang</h5>
                <table class="table table-bordered mt-3 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Bidang</th>
                            <th>Rata-rata (%)</th>
                            <th>Status</th>
                            <th>Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap as $nama => $data)
                        <tr>
                            <td>{{ $nama }}</td>
                            <td>{{ $data['rata_capaian'] }}</td>
                            <td>
                                <span class="badge
                                    @if($data['status'] == 'Hijau') bg-success
                                    @elseif($data['status'] == 'Kuning') bg-warning
                                    @else bg-danger @endif">
                                    {{ $data['status'] }}
                                </span>
                            </td>
                            <td>
                                {{-- Tampilkan pesan tindak lanjut jika sudah ada --}}
                                @if(!empty($data['tindak_lanjut']))
                                    <div class="mb-1"><small class="text-muted">Tindak lanjut: {{ $data['tindak_lanjut'] }}</small></div>
                                @endif

                                @if($data['status'] == 'Merah')
                                    <button
                                        class="btn btn-sm btn-danger open-tl-btn"
                                        type="button"
                                        data-bidang-id="{{ $data['bidang_id'] ?? '' }}"
                                        data-bidang-name="{{ $nama }}">
                                        Isi Tindak Lanjut
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- === Modal Isi Tindak Lanjut (form) === --}}
<div class="modal fade" id="tindakLanjutModal" tabindex="-1" aria-labelledby="tindakLanjutLabel" aria-hidden="true">
  <div class="modal-dialog">
<form method="POST" action="{{ route('esakip.tindak-lanjut.store') }}">
    @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="tindakLanjutLabel">Isi Tindak Lanjut</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
        <input type="hidden" name="nama_bidang" id="bidangNama">
        <input type="hidden" name="bidang_id" id="bidangId">

            <div class="mb-3">
                <label for="pesan" class="form-label">Pesan / Arahan</label>
                <textarea class="form-control" name="pesan" rows="3" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Kirim</button>
          </div>
        </div>
    </form>
  </div>
</div>

{{-- === Modal Peringatan Pimpinan (muncul otomatis untuk admin/bidang) === --}}
@if(in_array(auth()->user()->role, ['bidang','admin_bidang','kepala_bidang']) && $tindakLanjut->isNotEmpty())
<div class="modal fade" id="modalPeringatanPimpinan" tabindex="-1" aria-labelledby="modalPeringatanLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-warning">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="modalPeringatanLabel">⚠️ Pesan Penting dari Pimpinan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-1">Terdapat pesan tindak lanjut yang perlu ditindaklanjuti oleh bidang Anda:</p>

        <ul class="list-unstyled">
          @foreach($tindakLanjut as $tl)
            <li class="mb-2">
              <strong>{{ $tl->bidang->nama_bidang ?? 'Untuk Bidang Anda' }}</strong>
              <div class="border rounded p-2 bg-light mt-1">
                {{ $tl->pesan }}
                <div class="text-muted small mt-1">Dikirim: {{ $tl->created_at->diffForHumans() }}</div>
              </div>
            </li>
          @endforeach
        </ul>

        <p class="small text-danger mb-0">Mohon segera tindak lanjuti pesan ini. Klik "Lihat" untuk membuka panel tindak lanjut.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" id="btnTutupPeringatan">Tutup</button>
        <button type="button" class="btn btn-warning" id="btnLihatTindakLanjut">Lihat & Tindak Lanjut</button>
      </div>

      {{-- hidden JSON of ids to mark viewed --}}
      <input type="hidden" id="tindakLanjutIdsForView" value="{{ $tindakLanjut->pluck('id')->toJson() }}">
      {{-- hidden bidang id (fallback) --}}
      <input type="hidden" id="tindakLanjutBidangId" value="{{ optional($tindakLanjut->first())->bidang_id ?? '' }}">
    </div>
  </div>
</div>
@endif

{{-- === Toast Peringatan (alternatif, pojok kanan atas) === --}}
@if(in_array(auth()->user()->role, ['bidang','admin_bidang','kepala_bidang']) && $tindakLanjut->isNotEmpty())
<div aria-live="polite" aria-atomic="true" class="position-relative">
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <div id="toastPeringatan" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="9000">
      <div class="toast-header bg-warning text-dark">
        <strong class="me-auto">Pesan Penting dari Pimpinan</strong>
        <small class="text-muted ms-2">baru</small>
        <button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        <ul class="mb-0">
          @foreach($tindakLanjut->take(3) as $tl)
            <li><strong>{{ $tl->bidang->nama_bidang ?? '' }}:</strong> {{ \Illuminate\Support\Str::limit($tl->pesan, 80) }}</li>
          @endforeach
        </ul>
        @if($tindakLanjut->count() > 3)
          <div class="mt-1"><small>... dan {{ $tindakLanjut->count() - 3 }} pesan lainnya</small></div>
        @endif
        <div class="mt-2"><a href="#rekap" class="small">Lihat semua</a></div>
      </div>
    </div>
  </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    /* Chart */
    const labels = @json($rekap->keys());
    const dataValues = @json($rekap->pluck('rata_capaian'));

    const canvasEl = document.getElementById('chartBidang');
    if (canvasEl) {
        const ctx = canvasEl.getContext ? canvasEl.getContext('2d') : canvasEl;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Capaian (%)',
                    data: dataValues,
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                plugins: { legend: { display: false }},
                scales: { y: { beginAtZero: true, max: 120 } }
            }
        });
    }

    /* Open Isi Tindak Lanjut modal (safe) */
    document.querySelectorAll('.open-tl-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const bidangName = btn.getAttribute('data-bidang-name') || '';
            const bidangId = btn.getAttribute('data-bidang-id') || '';

            const namaInput = document.getElementById('bidangNama');
            const idInput = document.getElementById('bidangId');

            if (namaInput) namaInput.value = bidangName;
            if (idInput) idInput.value = bidangId;

            const label = document.getElementById('tindakLanjutLabel');
            if (label) label.textContent = 'Isi Tindak Lanjut — ' + (bidangName || 'Bidang');

            const modalEl = document.getElementById('tindakLanjutModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    });

    /* Auto-show modal peringatan jika ada, dan mark viewed via AJAX when shown */
    var peringatanEl = document.getElementById('modalPeringatanPimpinan');
    if (peringatanEl) {
        var peringatanModal = new bootstrap.Modal(peringatanEl);
        peringatanModal.show();

        peringatanEl.addEventListener('shown.bs.modal', function () {
            var idsInput = document.getElementById('tindakLanjutIdsForView');
            if (idsInput) {
                try {
                    var ids = JSON.parse(idsInput.value || '[]');
                    if (ids.length > 0) {
                        fetch('{{ route("esakip.tindak-lanjut.viewMultiple") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ ids: ids })
                        })
                        .then(res => res.json())
                        .then(data => {
                            // prevent re-marking
                            idsInput.value = '[]';
                        })
                        .catch(err => {
                            console.warn('Mark viewed failed', err);
                        });
                    }
                } catch(e) {
                    console.warn(e);
                }
            }
        });

        // Tombol lihat -> tutup modal & aktifkan tab Rekap (atau fallback open-tl-btn untuk bidang)
        var lihatBtn = document.getElementById('btnLihatTindakLanjut');
        if (lihatBtn) {
            lihatBtn.addEventListener('click', function () {
                peringatanModal.hide();

                // Coba aktifkan tab rekap (untuk pimpinan)
                var rekTabBtn = document.getElementById('rekap-tab');
                if (rekTabBtn) {
                    var bsTab = new bootstrap.Tab(rekTabBtn);
                    bsTab.show();

                    var rekapPanel = document.getElementById('rekap');
                    if (rekapPanel) rekapPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return;
                }

                // Fallback: buka modal Isi Tindak Lanjut untuk bidang terkait (cari tombol open-tl-btn)
                var bidangIdInput = document.getElementById('tindakLanjutBidangId');
                var bidangId = bidangIdInput ? bidangIdInput.value : null;

                if (bidangId) {
                    var selector = '.open-tl-btn[data-bidang-id="' + bidangId + '"]';
                    var openBtn = document.querySelector(selector);

                    if (!openBtn) {
                        openBtn = document.querySelector('.open-tl-btn');
                    }

                    if (openBtn) {
                        setTimeout(function () {
                            openBtn.click();
                        }, 250);
                        return;
                    }
                }

                // fallback paling dasar
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // Tombol tutup sederhana
        var tutupBtn = document.getElementById('btnTutupPeringatan');
        if (tutupBtn) tutupBtn.addEventListener('click', function(){ peringatanModal.hide(); });
    }

    /* Auto-show toast peringatan (if present) */
    var toastEl = document.getElementById('toastPeringatan');
    if (toastEl) {
        var bsToast = new bootstrap.Toast(toastEl);
        bsToast.show();
    }
});
</script>
@endpush
