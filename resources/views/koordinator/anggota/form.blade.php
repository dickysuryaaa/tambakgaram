@extends('layouts.koordinator')
@php $isNew = ! $member->exists; @endphp
@section('title', $isNew ? 'Tambah Anggota' : 'Ubah Anggota')

@section('content')
<a href="{{ route('k.members.index') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> Data Anggota</a>
<div class="page-head">
    <div>
        <h1>{{ $isNew ? 'Tambah Anggota' : 'Ubah Anggota' }}</h1>
        <p class="lead-text">{{ $isNew ? 'Buat akun baru untuk anggota kelompok.' : 'Perbarui data akun '.$member->name.'.' }}</p>
    </div>
</div>

<form method="POST" action="{{ $isNew ? route('k.members.store') : route('k.members.update', $member) }}" class="card form-card mb-4" novalidate>
    <div class="card-body">
    @csrf
    @unless ($isNew) @method('PUT') @endunless

    <div class="field">
        <label for="name" class="form-label">Nama lengkap <span class="req" aria-hidden="true">*</span></label>
        <input type="text" id="name" name="name" required maxlength="255" value="{{ old('name', $member->name) }}" class="form-control @error('name') is-invalid @enderror">
        @include('partials.field-error', ['name' => 'name'])
    </div>

    <div class="row g-3">
        <div class="col-md-6 field mb-md-4">
            <label for="username" class="form-label">Nama pengguna untuk masuk <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="username" name="username" required maxlength="255" autocapitalize="none" autocomplete="off" value="{{ old('username', $member->username) }}" class="form-control @error('username') is-invalid @enderror" aria-describedby="username-help">
            <div id="username-help" class="form-text">Huruf, angka, titik, garis bawah, atau strip. Tanpa spasi.</div>
            @include('partials.field-error', ['name' => 'username'])
        </div>
        <div class="col-md-6 field mb-md-4">
            <label for="password" class="form-label">Kata sandi @if ($isNew)<span class="req" aria-hidden="true">*</span>@endif</label>
            <div class="input-icon">
                <input type="password" id="password" name="password" autocomplete="new-password" @required($isNew) class="form-control has-trail @error('password') is-invalid @enderror" style="padding-left: .9rem;" aria-describedby="password-help">
                <button type="button" class="trail" data-toggle-password="password" aria-pressed="false" aria-label="Tampilkan kata sandi"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
            <div id="password-help" class="form-text">{{ $isNew ? 'Minimal 8 karakter.' : 'Kosongkan bila tidak ingin mengubah kata sandi. Jika diisi, minimal 8 karakter.' }}</div>
            @include('partials.field-error', ['name' => 'password'])
        </div>
    </div>

    <div class="field">
        <label for="phone" class="form-label">Nomor telepon</label>
        <input type="tel" id="phone" name="phone" maxlength="30" value="{{ old('phone', $member->phone) }}" class="form-control tabular @error('phone') is-invalid @enderror" aria-describedby="phone-help">
        <div id="phone-help" class="form-text">Hanya dapat dilihat koordinator.</div>
        @include('partials.field-error', ['name' => 'phone'])
    </div>

    @include('partials.choice', ['name' => 'role', 'legend' => 'Peran', 'cols' => 'cols-2', 'selected' => old('role', $member->role ?? 'petambak'), 'options' => [
        'petambak' => ['label' => 'Petambak', 'desc' => 'Hanya melihat tambak miliknya', 'icon' => 'bi-person', 'tone' => 'sea'],
        'koordinator_admin' => ['label' => 'Koordinator', 'desc' => 'Mengelola seluruh kelompok', 'icon' => 'bi-person-gear', 'tone' => 'warn'],
    ]])

    @include('partials.choice', ['name' => 'status', 'legend' => 'Status akun', 'cols' => 'cols-2', 'selected' => old('status', $member->status ?? 'aktif'), 'options' => [
        'aktif' => ['label' => 'Aktif', 'desc' => 'Bisa masuk ke aplikasi', 'icon' => 'bi-check-circle', 'tone' => 'ok'],
        'nonaktif' => ['label' => 'Tidak aktif', 'desc' => 'Tidak bisa masuk', 'icon' => 'bi-slash-circle', 'tone' => 'mute'],
    ]])

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg" data-busy="Menyimpan..."><i class="bi bi-check-lg" aria-hidden="true"></i> Simpan</button>
        <a href="{{ route('k.members.index') }}" class="btn btn-outline-secondary btn-lg">Batal</a>
    </div>
    </div>
</form>

@unless ($isNew)
    <form method="POST" action="{{ route('k.members.destroy', $member) }}" class="card card-body border-danger form-card" onsubmit="return confirm('Hapus anggota ini? Tindakan ini tidak bisa dibatalkan.');">
        @csrf @method('DELETE')
        <h2 class="h5 section-title"><i class="bi bi-trash" aria-hidden="true" style="color: var(--bad-ink);"></i>Hapus Anggota</h2>
        <p>Hanya bisa dilakukan bila anggota belum punya tambak atau catatan. Jika sudah, ubah status akun menjadi Tidak aktif.</p>
        <div><button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash" aria-hidden="true"></i> Hapus Anggota</button></div>
    </form>
@endunless
@endsection
