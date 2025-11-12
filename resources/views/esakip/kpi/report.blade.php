@extends('layouts.app')

@section('title', 'Laporan KPI')

@push('style')
<style>
/* === TAMPILAN LAYAR (normal) === */
.table thead th {
  background-color: #007bff;
  color: white;
}
.status-hijau { background-color: #28a745; color: white; font-weight: bold; }
.status-kuning { background-color: #ffc107; color: black; font-weight: bold; }
.status-merah { background-color: #dc3545; color: white; font-weight: bold; }

/* === TAMPILAN CETAK / PDF === */
@media print {
  body { font-family: 'Times New Roman', Times, serif; font-size: 12px; color: #000; margin: 10mm; }

  /* Sembunyikan tampilan web */
  .no-print, .table, .table-striped, .table-bordered { display: none !important; }

  /* Tampilkan layout cetak */
  #print-layout { display: block !important; }

  table.report-table { width: 100%; border-collapse: collapse; }
  table.report-table th, table.report-table td {
    border: 1px solid #000;
    padding: 4px;
    text-align: center;
    vertical-align: middle;
  }
  .header-title {
    text-align: center;
    margin-bottom: 20px;
    font-weight: bold;
  }
}

/* Default sembunyikan layout cetak */
#print-layout { display: none; }
</style>
@endpush

@section('content')
<div class="container mt-4">

  {{-- === TAMPILAN NORMAL DI LAYAR === --}}
 <div class="d-flex justify-content-between align-items-center mb-3 no-print">
  <h3 class="fw-bold">Laporan KPI BKAD</h3>
  <div>
    <button onclick="window.print()" class="btn btn-primary me-2">🖨 Print</button>
    <a href="{{ route('kpi.exportWord') }}" class="btn btn-success">📄 Save Word</a>

  </div>
</div>


  <table class="table table-bordered table-striped">
    <thead class="text-center">
      <tr>
        <th>No</th>
        <th>Bidang</th>
        <th>Indikator</th>
        <th>Target</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($kpis as $index => $kpi)
      @php
        $capaian = $kpi->capaian ?? 0;
        $status = 'Merah';
        if ($capaian >= 90) $status = 'Hijau';
        elseif ($capaian >= 70) $status = 'Kuning';
      @endphp
      <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td>{{ $kpi->bidang->nama_bidang ?? '-' }}</td>
        <td>{{ $kpi->nama_kpi }}</td>
        <td class="text-center">{{ $kpi->target ?? '-' }}</td>
        <td class="text-center">{{ $kpi->realisasi ?? '-' }}</td>
        <td class="text-center">{{ number_format($capaian, 2) }}%</td>
        <td class="text-center {{
          $status == 'Hijau' ? 'status-hijau' : ($status == 'Kuning' ? 'status-kuning' : 'status-merah')
        }}">
          {{ $status }}
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="mt-4 text-end">
    <small><em>Dicetak pada: {{ now()->format('d M Y, H:i') }}</em></small>
  </div>


</div>
@endsection

@push('scripts')
<!-- jsPDF + html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.getElementById('btn-pdf').addEventListener('click', () => {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('p', 'pt', 'a4');
  const printContent = document.querySelector('#print-layout');
  printContent.style.display = 'block'; // tampilkan layout cetak sementara
  html2canvas(printContent).then(canvas => {
    const imgData = canvas.toDataURL('image/png');
    const pdfWidth = doc.internal.pageSize.getWidth();
    const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
    doc.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
    doc.save('Laporan-Pengukuran-Kinerja.pdf');
    printContent.style.display = 'none'; // sembunyikan lagi setelah generate
  });
});
</script>
@endpush
