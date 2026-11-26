@extends('layouts.petambak')
@section('title', 'Akun')

@section('content')
<div class="page-head">
    <div><h1>Akun</h1></div>
</div>

<div class="card mb-3 form-card">
    <div class="card-body d-flex align-items-center gap-3">
        <span class="avatar" style="width:64px;height:64px;flex-basis:64px;font-size:1.6rem;" aria-hidden="true">{{ mb_strtoupper(mb_substr(trim(auth()->user()->name), 0, 1)) }}</span>
        <div>
            <div class="h4 mb-0">{{ auth()->user()->name }}</div>
            <div class="text-muted">Petambak</div>
        </div>
    </div>
    <div class="card-body pt-0">
        <dl class="kv">
            <dt>Nama</dt><dd>{{ auth()->user()->name }}</dd>
            <dt>Nama pengguna</dt><dd>{{ auth()->user()->username }}</dd>
            <dt>Peran</dt><dd>Petambak</dd>
        </dl>
    </div>
</div>

<div class="card mb-3 form-card">
    <div class="card-body">
        <h2 class="h5 section-title"><i class="bi bi-question-circle" aria-hidden="true"></i>Bantuan</h2>
        <ul class="mb-0 ps-3 d-grid gap-2">
            <li><strong>Catat</strong>: untuk mencatat panen, pengamatan air, kendala, dan kegiatan.</li>
            <li><strong>Tambak Saya</strong>: untuk melihat dan mengubah status tambak.</li>
            <li><strong>Riwayat</strong>: untuk melihat catatan yang pernah Anda buat.</li>
            <li>Lupa kata sandi atau ada data yang salah? Hubungi koordinator kelompok.</li>
        </ul>
    </div>
</div>

<form method="POST" action="{{ route('logout') }}" class="form-card">
    @csrf
    <button type="submit" class="btn btn-outline-danger btn-lg w-100"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Keluar</button>
</form>
@endsection
