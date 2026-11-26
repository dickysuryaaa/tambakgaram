@extends('layouts.koordinator')
@section('title', 'Pengamatan Air')
@php use App\Support\RecordFeed; @endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Pengamatan Air</h1>
        <p class="lead-text">Hasil ukur salinitas, suhu, dan pH dari semua tambak.</p>
    </div>
</div>

<form method="GET" class="card card-body filter-card" aria-label="Saring pengamatan air">
    <div class="row g-3 align-items-end">
        @include('koordinator.partials.pilih-tambak')
        @include('koordinator.partials.tanggal')
        @include('koordinator.partials.aksi-filter')
    </div>
</form>

<p class="info-strip"><i class="bi bi-calendar-range" aria-hidden="true"></i>Periode: {{ $period->label() }}. {{ $observations->total() }} catatan ditemukan. Tanda "-" berarti angka itu tidak diukur.</p>

<div class="table-responsive card mb-3 table-card">
    <table class="table mb-0">
        <caption class="visually-hidden">Daftar pengamatan air</caption>
        <thead>
            <tr><th scope="col">Waktu</th><th scope="col">Tambak</th><th scope="col" class="table-num">Salinitas (°Bé)</th><th scope="col" class="table-num">Suhu (°C)</th><th scope="col" class="table-num">pH</th><th scope="col">Alat ukur</th><th scope="col">Catatan</th></tr>
        </thead>
        <tbody>
        @forelse ($observations as $o)
            <tr>
                <td class="text-nowrap">{{ $o->observed_at->translatedFormat('j M Y, H.i') }}</td>
                <td>{{ $o->pond->pond_name ?: $o->pond->pond_code }} ({{ $o->pond->pond_code }})<br><span class="text-muted">{{ $o->pond->member->name }}</span></td>
                <td class="table-num">{{ $o->salinity_value !== null ? RecordFeed::num($o->salinity_value) : '-' }}</td>
                <td class="table-num">{{ $o->temperature_c !== null ? RecordFeed::num($o->temperature_c) : '-' }}</td>
                <td class="table-num">{{ $o->ph_value !== null ? RecordFeed::num($o->ph_value) : '-' }}</td>
                <td>{{ $o->instrument ?: '-' }}</td>
                <td>{{ $o->notes ?: '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7">Tidak ada pengamatan air yang cocok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $observations->links() }}
@endsection
