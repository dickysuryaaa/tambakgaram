@extends('layouts.base')
@section('title', $judul)

@section('body')
<main id="isi" class="error-page">
    <div class="card error-card">
        <div class="error-icon {{ $nada ?? '' }}"><i class="bi {{ $ikon ?? 'bi-compass' }}" aria-hidden="true"></i></div>
        @isset($kode)<div class="error-code">Kode {{ $kode }}</div>@endisset
        <h1 class="h2">{{ $judul }}</h1>
        <p class="text-muted mb-4">{{ $pesan }}</p>
        <div class="d-grid gap-2">
            <a href="{{ url('/') }}" class="btn btn-primary btn-lg"><i class="bi bi-house-door" aria-hidden="true"></i> Ke Halaman Awal</a>
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali</a>
        </div>
    </div>
</main>
@endsection
