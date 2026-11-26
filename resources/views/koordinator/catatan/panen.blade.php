@extends('layouts.koordinator')
@section('title', 'Hasil Panen')
@php use App\Support\RecordFeed; @endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Hasil Panen</h1>
        <p class="lead-text">Semua catatan panen anggota kelompok.</p>
    </div>
</div>

<form method="GET" class="card card-body filter-card" aria-label="Saring hasil panen">
    <div class="row g-3 align-items-end">
        @include('koordinator.partials.pilih-tambak')
        @include('koordinator.partials.tanggal')
        @include('koordinator.partials.aksi-filter')
    </div>
</form>

<div class="bento mb-4">
    <div class="kpi hero-kpi span-2">
        <div class="kpi-top"><span class="kpi-label">Total pada daftar ini</span><span class="kpi-icon"><i class="bi bi-basket2" aria-hidden="true"></i></span></div>
        <div class="kpi-value">{{ number_format($totalKg, 0, ',', '.') }}<span class="unit">kg</span></div>
        <div class="kpi-hint">Panen dalam ton diubah ke kg hanya pada total. Angka asli tetap seperti yang dicatat.</div>
    </div>
    <div class="kpi">
        <div class="kpi-top"><span class="kpi-label">Jumlah catatan</span><span class="kpi-icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span></div>
        <div class="kpi-value">{{ $harvests->total() }}</div>
        <div class="kpi-hint">catatan panen</div>
    </div>
    <div class="kpi tone-mute">
        <div class="kpi-top"><span class="kpi-label">Periode</span><span class="kpi-icon"><i class="bi bi-calendar-range" aria-hidden="true"></i></span></div>
        <div class="fw-bold mt-auto">{{ $period->label() }}</div>
    </div>
</div>

<div class="table-responsive card mb-3 table-card">
    <table class="table mb-0">
        <caption class="visually-hidden">Daftar hasil panen</caption>
        <thead>
            <tr><th scope="col">Tanggal</th><th scope="col">Tambak</th><th scope="col" class="table-num">Dicatat</th><th scope="col" class="table-num">Setara (kg)</th><th scope="col">Dicatat oleh</th><th scope="col">Catatan</th></tr>
        </thead>
        <tbody>
        @forelse ($harvests as $h)
            <tr>
                <td class="text-nowrap">{{ $h->harvest_date->translatedFormat('j M Y') }}</td>
                <td>{{ $h->pond->pond_name ?: $h->pond->pond_code }} ({{ $h->pond->pond_code }})<br><span class="text-muted">{{ $h->pond->member->name }}</span></td>
                <td class="table-num">{{ RecordFeed::num($h->quantity) }} {{ $h->unit }}</td>
                <td class="table-num">{{ number_format($h->quantity_kg, 0, ',', '.') }}</td>
                <td>{{ $h->recorder->name }}</td>
                <td>{{ $h->notes ?: '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Tidak ada catatan panen yang cocok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $harvests->links() }}
@endsection
