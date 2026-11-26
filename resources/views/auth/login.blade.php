@extends('layouts.base')
@section('title', 'Masuk')

@section('body')
@include('layouts.partials.demo-banner')
<div class="auth">
    <aside class="auth-art" aria-hidden="true">
        <span class="brand">
            <span class="brand-mark"><i class="bi bi-water"></i></span>
            <span class="brand-text"><strong>SIMTAG</strong><span>Sistem Informasi Tambak Garam</span></span>
        </span>
        <div>
            <h2>Catatan tambak garam kelompok, rapi dalam satu tempat.</h2>
            <p>Catat panen, pantau air, dan laporkan kendala langsung dari HP di area tambak.</p>
            <ul class="auth-points">
                <li><i class="bi bi-basket2"></i>Hasil panen tercatat dalam kg atau ton</li>
                <li><i class="bi bi-droplet-half"></i>Pengamatan salinitas, suhu, dan pH</li>
                <li><i class="bi bi-exclamation-triangle"></i>Kendala cepat sampai ke koordinator</li>
            </ul>
        </div>
        <svg class="auth-waves" viewBox="0 0 1200 160" preserveAspectRatio="none"><path d="M0 80 Q150 30 300 80 T600 80 T900 80 T1200 80 V160 H0Z" fill="#fff"/><path d="M0 110 Q150 70 300 110 T600 110 T900 110 T1200 110 V160 H0Z" fill="#fff" opacity=".6"/></svg>
    </aside>

    <main id="isi" class="auth-panel">
        <div class="auth-card">
            <div class="auth-mobile-brand d-flex justify-content-center">
                <span class="brand">
                    <span class="brand-mark"><i class="bi bi-water" aria-hidden="true"></i></span>
                    <span class="brand-text"><strong>SIMTAG</strong><span>Tambak Garam</span></span>
                </span>
            </div>

            @include('partials.flash')

            <form method="POST" action="{{ route('login.store') }}" class="card" novalidate>
                <div class="card-body">
                    @csrf
                    <h1 class="h2 mb-1">Selamat datang</h1>
                    <p class="text-muted mb-4">Masuk dengan nama pengguna dan kata sandi dari koordinator.</p>

                    <div class="field">
                        <label for="username" class="form-label">Nama pengguna</label>
                        <div class="input-icon">
                            <i class="bi bi-person lead" aria-hidden="true"></i>
                            <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" autocapitalize="none" autofocus required
                                   class="form-control @error('username') is-invalid @enderror" @error('username') aria-describedby="username-error" aria-invalid="true" @enderror>
                        </div>
                        @error('username')<div id="username-error" class="invalid-feedback d-flex" role="alert">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="password" class="form-label">Kata sandi</label>
                        <div class="input-icon">
                            <i class="bi bi-lock lead" aria-hidden="true"></i>
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                   class="form-control has-trail @error('password') is-invalid @enderror" @error('password') aria-describedby="password-error" @enderror>
                            <button type="button" class="trail" data-toggle-password="password" aria-pressed="false" aria-label="Tampilkan kata sandi"><i class="bi bi-eye" aria-hidden="true"></i></button>
                        </div>
                        @error('password')<div id="password-error" class="invalid-feedback d-flex" role="alert">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label" for="remember">Tetap masuk di HP ini</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Masuk ke Sistem</button>
                </div>
            </form>
            <p class="text-muted text-center mt-3 mb-0">Lupa kata sandi? Hubungi koordinator kelompok.</p>
        </div>
    </main>
</div>
@endsection
