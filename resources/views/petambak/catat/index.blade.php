@extends('layouts.petambak')
@section('title', 'Catat')

@section('content')
<div class="page-head">
    <div>
        <h1>Catat</h1>
        <p class="lead-text">Pilih apa yang ingin Anda catat.</p>
    </div>
</div>

@if (! $hasPonds)
    @include('partials.no-ponds')
@else
    @php
        $items = [
            ['catat.panen', 'Catat Panen', 'Berat garam yang dipanen', 'bi-basket2', 'ok'],
            ['catat.air', 'Catat Pengamatan Air', 'Salinitas, suhu, dan pH', 'bi-droplet-half', ''],
            ['catat.kendala', 'Laporkan Kendala', 'Tanggul bocor, pompa rusak, dll.', 'bi-exclamation-triangle', 'bad'],
            ['catat.kegiatan', 'Catat Kegiatan', 'Pekerjaan harian di tambak', 'bi-tools', 'warn'],
            ['catat.status', 'Ubah Status Tambak', 'Tahap produksi atau jeda', 'bi-arrow-repeat', 'mute'],
            ['catat.siklus', 'Mulai Siklus Baru', 'Satu putaran produksi garam', 'bi-plus-circle', ''],
        ];
    @endphp
    <div class="action-grid">
        @foreach ($items as [$route, $title, $desc, $icon, $tone])
            <a href="{{ route($route) }}" class="action-card">
                <span class="ic {{ $tone }}"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                <span><span class="t">{{ $title }}</span><span class="d">{{ $desc }}</span></span>
                <i class="bi bi-chevron-right go" aria-hidden="true"></i>
            </a>
        @endforeach
    </div>
@endif
@endsection
