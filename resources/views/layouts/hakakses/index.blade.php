@extends('layouts.app')

@section('title', 'Daftar User')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Daftar User</h1>
            <a href="{{ route('hakakses.create') }}" class="btn btn-primary ml-auto">+ Tambah User</a>
        </div>

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <div class="section-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Bidang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hakakses as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge
                                    @if($user->role == 'superadmin') badge-danger
                                    @elseif($user->role == 'pimpinan') badge-info
                                    @elseif($user->role == 'admin_bidang') badge-warning
                                    @else badge-secondary @endif">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                {{ $user->bidang ? $user->bidang->nama_bidang : '-' }}
                            </td>
                            <td>
                                <a href="{{ route('hakakses.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('hakakses.delete', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data user.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
