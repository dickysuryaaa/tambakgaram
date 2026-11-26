@extends('layouts.base')
@section('title', 'Selamat Datang')

@php
    $user = auth()->user();
    $cta = $user ? ($user->isKoordinatorAdmin() ? route('koordinator') : route('beranda')) : route('login');
@endphp

@section('body')
@include('layouts.partials.demo-banner')
<div class="page">
    <header class="landing-nav">
        <span class="brand">
            <span class="brand-mark"><i class="bi bi-water" aria-hidden="true"></i></span>
            <span class="brand-text"><strong>SIMTAG</strong><span>Tambak Garam</span></span>
        </span>
        <a href="{{ $cta }}" class="btn btn-outline-primary">{{ $user ? 'Buka Dashboard' : 'Masuk' }}</a>
    </header>

    <main id="isi">
        <section class="hero mb-5" aria-labelledby="judul-hero">
            <span class="hero-badge"><i class="bi bi-sun" aria-hidden="true"></i>Sistem Informasi Tambak Garam</span>
            <h1 id="judul-hero">Kelola tambak garam kelompok dengan mudah, langsung dari HP.</h1>
            <p class="mt-3 mb-4">Catat panen, pantau kualitas air, dan laporkan kendala dalam hitungan detik. Koordinator melihat ringkasan seluruh tambak di satu layar.</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ $cta }}" class="btn btn-light btn-lg"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> {{ $user ? 'Buka Dashboard' : 'Masuk ke Sistem' }}</a>
            </div>
            <svg class="hero-crystals" viewBox="0 0 200 200" aria-hidden="true"><g fill="#fff"><rect x="40" y="40" width="50" height="50" rx="6" transform="rotate(15 65 65)"/><rect x="110" y="70" width="40" height="40" rx="5" transform="rotate(-12 130 90)"/><rect x="70" y="120" width="56" height="56" rx="7" transform="rotate(30 98 148)"/></g></svg>
        </section>

        <section aria-labelledby="judul-fitur">
            <h2 id="judul-fitur" class="h3 mb-3">Yang bisa Anda lakukan</h2>
            <div class="feature-grid">
                <article class="card feature">
                    <span class="kpi-icon"><i class="bi bi-basket2" aria-hidden="true"></i></span>
                    <h3>Catat Panen</h3>
                    <p>Isi berat garam dalam kg atau ton. Total otomatis dihitung dalam kg.</p>
                </article>
                <article class="card feature">
                    <span class="kpi-icon"><i class="bi bi-droplet-half" aria-hidden="true"></i></span>
                    <h3>Pantau Air</h3>
                    <p>Simpan hasil ukur salinitas, suhu, dan pH. Yang tidak diukur boleh dikosongkan.</p>
                </article>
                <article class="card feature">
                    <span class="kpi-icon" style="background: var(--bad-soft); color: var(--bad-ink);"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
                    <h3>Lapor Kendala</h3>
                    <p>Tanggul bocor atau pompa rusak? Laporan langsung sampai ke koordinator.</p>
                </article>
                <article class="card feature">
                    <span class="kpi-icon" style="background: var(--ok-soft); color: var(--ok-ink);"><i class="bi bi-bar-chart" aria-hidden="true"></i></span>
                    <h3>Rekap Kelompok</h3>
                    <p>Ringkasan produksi dan status tambak per periode untuk koordinator.</p>
                </article>
            </div>
        </section>
    </main>
</div>
@endsection
