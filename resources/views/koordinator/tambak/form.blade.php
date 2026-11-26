@extends('layouts.koordinator')
@php
    use App\Support\Label;
    $isNew = ! $pond->exists;
    $field = fn (string $k) => old($k, $pond->{$k});
@endphp
@section('title', $isNew ? 'Tambah Tambak' : 'Ubah Tambak')

@section('content')
<a href="{{ route('k.ponds.index') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> Data Tambak</a>
<div class="page-head">
    <div>
        <h1>{{ $isNew ? 'Tambah Tambak' : 'Ubah Tambak '.$pond->pond_code }}</h1>
        <p class="lead-text">Data petak tambak, pemilik, lokasi, dan statusnya.</p>
    </div>
</div>

<form method="POST" action="{{ $isNew ? route('k.ponds.store') : route('k.ponds.update', $pond) }}" class="card card-body form-card mb-4" novalidate>
    @csrf
    @unless ($isNew) @method('PUT') @endunless

    <div class="row g-3 g-md-4">
        <div class="col-md-4">
            <label for="pond_code" class="form-label">Kode tambak <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="pond_code" name="pond_code" required maxlength="30" value="{{ $field('pond_code') }}" class="form-control @error('pond_code') is-invalid @enderror">
            @include('partials.field-error', ['name' => 'pond_code'])
        </div>
        <div class="col-md-8">
            <label for="pond_name" class="form-label">Nama tambak</label>
            <input type="text" id="pond_name" name="pond_name" maxlength="255" value="{{ $field('pond_name') }}" class="form-control @error('pond_name') is-invalid @enderror">
            @include('partials.field-error', ['name' => 'pond_name'])
        </div>

        <div class="col-12">
            <label for="member_id" class="form-label">Pemilik atau pengelola <span class="req" aria-hidden="true">*</span></label>
            <select id="member_id" name="member_id" required class="form-select @error('member_id') is-invalid @enderror">
                <option value="">Pilih anggota</option>
                @foreach ($members as $m)
                    <option value="{{ $m->member_id }}" @selected((string) $field('member_id') === (string) $m->member_id)>{{ $m->name }}{{ $m->status !== 'aktif' ? ' (tidak aktif)' : '' }}</option>
                @endforeach
            </select>
            @include('partials.field-error', ['name' => 'member_id'])
        </div>

        <div class="col-md-4">
            <label for="village" class="form-label">Desa atau kelurahan <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="village" name="village" required maxlength="255" value="{{ $field('village') }}" class="form-control @error('village') is-invalid @enderror">
            @include('partials.field-error', ['name' => 'village'])
        </div>
        <div class="col-md-4">
            <label for="district" class="form-label">Kecamatan <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="district" name="district" required maxlength="255" value="{{ $field('district') }}" class="form-control @error('district') is-invalid @enderror">
            @include('partials.field-error', ['name' => 'district'])
        </div>
        <div class="col-md-4">
            <label for="regency" class="form-label">Kabupaten atau kota <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="regency" name="regency" required maxlength="255" value="{{ $field('regency') }}" class="form-control @error('regency') is-invalid @enderror">
            @include('partials.field-error', ['name' => 'regency'])
        </div>

        <div class="col-md-4">
            <label for="latitude" class="form-label">Lintang</label>
            <input type="text" inputmode="decimal" id="latitude" name="latitude" value="{{ $field('latitude') }}" class="form-control @error('latitude') is-invalid @enderror" aria-describedby="koord-help">
            @include('partials.field-error', ['name' => 'latitude'])
        </div>
        <div class="col-md-4">
            <label for="longitude" class="form-label">Bujur</label>
            <input type="text" inputmode="decimal" id="longitude" name="longitude" value="{{ $field('longitude') }}" class="form-control @error('longitude') is-invalid @enderror" aria-describedby="koord-help">
            @include('partials.field-error', ['name' => 'longitude'])
        </div>
        <div class="col-md-4">
            <label for="area_m2" class="form-label">Luas (m&sup2;) <span class="req" aria-hidden="true">*</span></label>
            <input type="text" inputmode="numeric" id="area_m2" name="area_m2" required value="{{ $field('area_m2') }}" class="form-control @error('area_m2') is-invalid @enderror">
            @include('partials.field-error', ['name' => 'area_m2'])
        </div>
        <div class="col-12 form-text mt-0" id="koord-help">Koordinat boleh dikosongkan. Contoh: -7.0100000. Bila isinya data contoh, tulis "data contoh" pada keterangan.</div>

        <div class="col-12">
            @include('partials.pond-status-choice', ['selected' => $field('operational_status') ?? 'persiapan'])
        </div>

        <div class="col-12">
            <label for="notes" class="form-label">Keterangan</label>
            <textarea id="notes" name="notes" rows="3" maxlength="2000" class="form-control @error('notes') is-invalid @enderror">{{ $field('notes') }}</textarea>
            @include('partials.field-error', ['name' => 'notes'])
        </div>
    </div>

    <div class="form-actions mt-4">
        <button type="submit" class="btn btn-primary btn-lg" data-busy="Menyimpan..."><i class="bi bi-check-lg" aria-hidden="true"></i> Simpan</button>
        <a href="{{ $isNew ? route('k.ponds.index') : route('k.ponds.show', $pond) }}" class="btn btn-outline-secondary btn-lg">Batal</a>
    </div>
</form>

@unless ($isNew)
    <form method="POST" action="{{ route('k.ponds.destroy', $pond) }}" class="card card-body border-danger form-card" onsubmit="return confirm('Hapus tambak ini? Tindakan ini tidak bisa dibatalkan.');">
        @csrf @method('DELETE')
        <h2 class="h5">Hapus Tambak</h2>
        <p>Hanya bisa dilakukan bila tambak belum punya siklus, panen, pengamatan air, atau kendala. Jika sudah, ubah statusnya menjadi Tidak Aktif.</p>
        <div><button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash" aria-hidden="true"></i> Hapus Tambak</button></div>
    </form>
@endunless
@endsection
