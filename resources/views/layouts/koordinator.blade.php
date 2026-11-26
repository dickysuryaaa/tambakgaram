@extends('layouts.base')

@section('bodyclass', 'has-shell area-kelola')

@section('body')
@php
    $menu = [
        ['koordinator', 'Dashboard', 'bi-speedometer2', request()->routeIs('koordinator')],
        ['k.members.index', 'Anggota', 'bi-people', request()->routeIs('k.members.*')],
        ['k.ponds.index', 'Tambak', 'bi-grid-3x3-gap', request()->routeIs('k.ponds.*')],
        ['k.cycles', 'Siklus & Kegiatan', 'bi-arrow-repeat', request()->routeIs('k.cycles')],
        ['k.harvests', 'Hasil Panen', 'bi-basket2', request()->routeIs('k.harvests')],
        ['k.waters', 'Pengamatan Air', 'bi-droplet-half', request()->routeIs('k.waters')],
        ['k.issues.index', 'Kendala', 'bi-exclamation-triangle', request()->routeIs('k.issues.*')],
        ['k.rekap', 'Rekap Periode', 'bi-file-earmark-text', request()->routeIs('k.rekap')],
    ];
    // Menu bawah HP: 4 menu utama + tombol "Menu" untuk sisanya.
    $bottom = [$menu[0], $menu[2], $menu[6], [$menu[7][0], 'Rekap', $menu[7][2], $menu[7][3]]];
@endphp
@include('layouts.partials.demo-banner')

<div class="app-shell">
    @include('layouts.partials.shell', ['menu' => $menu, 'bottom' => $bottom, 'home' => 'koordinator', 'area' => 'Koordinator', 'drawer' => true, 'navClass' => 'kelola-nav'])
    <div class="app-main">
        @include('layouts.partials.topbar', ['home' => 'koordinator', 'area' => 'Koordinator'])
        <main id="isi" class="page">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
@stack('after-shell')
@stack('scripts')
@endsection
