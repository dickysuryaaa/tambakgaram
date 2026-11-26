@extends('layouts.koordinator')
@section('title', 'Anggota')

@section('content')
<div class="page-head">
    <div>
        <h1>Data Anggota</h1>
        <p class="lead-text">Akun petambak dan koordinator dalam kelompok.</p>
    </div>
    <div class="page-head-actions">
    <a href="{{ route('k.members.create') }}" class="btn btn-primary btn-lg"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Anggota</a>
    </div>
</div>

<form method="GET" class="card card-body filter-card" aria-label="Cari anggota">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-4">
            <label for="q" class="form-label">Cari nama, nama pengguna, atau telepon</label>
            <input type="search" id="q" name="q" value="{{ $q }}" class="form-control">
        </div>
        <div class="col-6 col-md-3">
            <label for="peran" class="form-label">Peran</label>
            <select id="peran" name="peran" class="form-select">
                <option value="">Semua peran</option>
                <option value="petambak" @selected($role === 'petambak')>Petambak</option>
                <option value="koordinator_admin" @selected($role === 'koordinator_admin')>Koordinator</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label for="status" class="form-label">Status akun</label>
            <select id="status" name="status" class="form-select">
                <option value="">Semua status</option>
                <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected($status === 'nonaktif')>Tidak aktif</option>
            </select>
        </div>
        <div class="col-12 col-md-auto d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search" aria-hidden="true"></i> Cari</button>
            <a href="{{ route('k.members.index') }}" class="btn btn-outline-secondary">Hapus Filter</a>
        </div>
    </div>
</form>

<div class="table-responsive card mb-3 table-card">
    <table class="table mb-0">
        <caption class="visually-hidden">Daftar anggota</caption>
        <thead>
            <tr><th scope="col">Nama</th><th scope="col">Nama pengguna</th><th scope="col">Telepon</th><th scope="col">Peran</th><th scope="col" class="table-num">Tambak</th><th scope="col">Status</th><th scope="col">Aksi</th></tr>
        </thead>
        <tbody>
        @forelse ($members as $m)
            <tr>
                <td><div class="d-flex align-items-center gap-2"><span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(trim($m->name), 0, 1)) }}</span><span class="fw-bold">{{ $m->name }}</span></div></td>
                <td>{{ $m->username }}</td>
                <td>{{ $m->phone ?: '-' }}</td>
                <td>{{ $m->isKoordinatorAdmin() ? 'Koordinator' : 'Petambak' }}</td>
                <td class="table-num">{{ $m->ponds_count }}</td>
                <td>@include('partials.status', ['label' => $m->status === 'aktif' ? 'Aktif' : 'Tidak Aktif', 'color' => $m->status === 'aktif' ? 'success' : 'secondary'])</td>
                <td><a href="{{ route('k.members.edit', $m) }}" class="btn btn-outline-primary"><i class="bi bi-pencil" aria-hidden="true"></i> Ubah</a></td>
            </tr>
        @empty
            <tr><td colspan="7">Tidak ada anggota yang cocok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $members->links() }}
@endsection
