<div class="table-responsive mt-3">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Bidang</th>
                <th>Nama KPI</th>
                <th>Rekomendasi</th>
                <th>Catatan Tindak Lanjut</th>
                <th>Status</th>
                <th>Dibuat Oleh</th>
                <th width="150px" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tindakLanjuts as $key => $tindak)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $tindak->kpi->bidang->nama_bidang ?? '-' }}</td>
                    <td>{{ $tindak->kpi->nama_kpi ?? '-' }}</td>
                    <td>{{ $tindak->rekomendasi ?? '-' }}</td>
                    <td>{{ $tindak->catatan_tindak_lanjut ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $tindak->status_tindak_lanjut == 'Sudah' ? 'bg-success' : 'bg-warning' }}">
                            {{ $tindak->status_tindak_lanjut }}
                        </span>
                    </td>
                    <td>{{ $tindak->user->name ?? '-' }}</td>
                    <td class="text-center">
                        <a href="{{ route('tindak-lanjut.edit', $tindak->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('tindak-lanjut.destroy', $tindak->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus tindak lanjut ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Belum ada data tindak lanjut</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
