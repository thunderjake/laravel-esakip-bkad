@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Tambah User Baru</h1>
        </div>

        <form action="{{ route('hakakses.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

          <div class="form-group">
    <label>Role</label>
    <select name="role" id="role" class="form-control" required>
        <option value="">-- Pilih Role --</option>
        <option value="superadmin">Superadmin</option>
        <option value="pimpinan">Pimpinan</option>
        <option value="admin_bidang">Admin Bidang</option>
        <option value="user">User Biasa</option>
    </select>
</div>

<div class="form-group" id="bidang-group" style="display:none;">
    <label>Pilih Bidang</label>
    <select name="bidang_id" class="form-control">
        <option value="">-- Pilih Bidang --</option>
        @foreach($bidangs as $bidang)
            <option value="{{ $bidang->id }}">{{ $bidang->nama_bidang }}</option>
        @endforeach
    </select>
</div>


            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('hakakses.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </section>
</div>

<script>
document.getElementById('role').addEventListener('change', function() {
    var bidangGroup = document.getElementById('bidang-group');
    bidangGroup.style.display = (this.value === 'admin_bidang') ? 'block' : 'none';
});
</script>
@endsection
