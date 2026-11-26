@extends('layouts.base')

@section('bodyclass', 'has-shell area-petambak')

@section('body')
@php
    $catatActive = request()->routeIs('catat*') || request()->routeIs('cycles.*');
    // Lima menu petambak (CLAUDE.md: maksimal 5, urutan tetap). "Catat" ditonjolkan di tengah menu bawah.
    $menu = [
        ['beranda', 'Beranda', 'bi-house-door', request()->routeIs('beranda')],
        ['ponds.index', 'Tambak Saya', 'bi-grid-3x3-gap', request()->routeIs('ponds.*')],
        ['catat', 'Catat', 'bi-plus-lg', $catatActive, true],
        ['riwayat', 'Riwayat', 'bi-clock-history', request()->routeIs('riwayat')],
        ['akun', 'Akun', 'bi-person-circle', request()->routeIs('akun')],
    ];
@endphp
@include('layouts.partials.demo-banner')

<div class="app-shell">
    @include('layouts.partials.shell', ['menu' => $menu, 'bottom' => $menu, 'home' => 'beranda', 'area' => 'Petambak', 'navClass' => 'app-nav'])
    <div class="app-main">
        @include('layouts.partials.topbar', ['home' => 'beranda', 'area' => 'Petambak'])
        <main id="isi" class="page">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
@stack('after-shell')
@stack('scripts')
@endsection
