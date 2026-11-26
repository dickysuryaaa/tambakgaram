@extends('layouts.petambak')
@section('title', 'Beranda')

@php
    $h = (int) now()->format('G');
    $salam = $h < 11 ? 'Selamat pagi' : ($h < 15 ? 'Selamat siang' : ($h < 18 ? 'Selamat sore' : 'Selamat malam'));
@endphp

{{-- Urutan komponen mengikuti PRD 7.3: sapaan, tambak saya + status, tiga tombol catat, catatan terakhir. --}}
@section('content')
{{-- 1. Sapaan --}}
<div class="page-head">
    <div>
        <div class="eyebrow"><i class="bi bi-calendar3" aria-hidden="true"></i>{{ now()->translatedFormat('l, j F Y') }}</div>
        <h1>Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="lead-text">{{ $salam }}! Pantau tambak Anda dan catat hasil kerja hari ini.</p>
    </div>
</div>

{{-- 2-3. Tambak Saya: kartu ringkasan yang bisa diklik, filter, dan daftar petak --}}
<div class="d-flex justify-content-between align-items-center gap-2 mb-2">
    <h2 class="h4 mb-0">Tambak Saya</h2>
    <a href="{{ route('ponds.index') }}" class="btn btn-ghost">Lihat semua <i class="bi bi-chevron-right" aria-hidden="true"></i></a>
</div>
<p class="text-muted mb-3">Ketuk salah satu kotak untuk menyaring daftar tambak di bawahnya.</p>
@include('partials.pond-board', ['ponds' => $ponds, 'showStats' => true])

{{-- 4-6. Tiga tombol aksi besar --}}
<section class="my-5" aria-labelledby="aksi-cepat">
    <h2 id="aksi-cepat" class="h4 section-title mb-3"><i class="bi bi-lightning-charge" aria-hidden="true"></i>Aksi Cepat</h2>
    <div class="action-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <a href="{{ route('catat.panen') }}" class="action-card">
            <span class="ic ok"><i class="bi bi-basket2" aria-hidden="true"></i></span>
            <span><span class="t">+ Catat Panen</span><span class="d">Berat garam yang dipanen</span></span>
        </a>
        <a href="{{ route('catat.air') }}" class="action-card">
            <span class="ic"><i class="bi bi-droplet-half" aria-hidden="true"></i></span>
            <span><span class="t">+ Catat Pengamatan Air</span><span class="d">Salinitas, suhu, dan pH</span></span>
        </a>
        <a href="{{ route('catat.kendala') }}" class="action-card">
            <span class="ic bad"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
            <span><span class="t">+ Laporkan Kendala</span><span class="d">Kabari koordinator</span></span>
        </a>
    </div>
</section>

{{-- 7. Catatan Terakhir --}}
<section aria-labelledby="catatan-terakhir">
    <div class="card-title-row">
        <h2 id="catatan-terakhir" class="h4 section-title"><i class="bi bi-clock-history" aria-hidden="true"></i>Catatan Terakhir</h2>
        @if ($recent->isNotEmpty())
            <a href="{{ route('riwayat') }}" class="btn btn-ghost">Semua riwayat <i class="bi bi-chevron-right" aria-hidden="true"></i></a>
        @endif
    </div>
    @include('partials.feed', ['items' => $recent])
</section>
@endsection
