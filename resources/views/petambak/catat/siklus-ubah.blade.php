@extends('layouts.petambak')
@section('title', 'Ubah Siklus')

@php
    $meta = ['persiapan' => ['bi-tools', 'warn'], 'berjalan' => ['bi-play-circle', 'ok'], 'selesai' => ['bi-check-circle', 'ok'], 'dibatalkan' => ['bi-x-circle', 'mute']];
    $options = collect(\App\Support\Label::cycleNames())->map(fn ($v, $k) => ['label' => $v, 'icon' => $meta[$k][0] ?? 'bi-circle', 'tone' => $meta[$k][1] ?? 'mute'])->all();
@endphp

@section('content')
<a href="{{ route('ponds.show', $cycle->pond_id) }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke tambak</a>
<div class="page-head">
    <div>
        <h1>Ubah Status Siklus</h1>
        <p class="lead-text">{{ $cycle->pond->pond_name ?: $cycle->pond->pond_code }}, mulai {{ $cycle->start_date->translatedFormat('j F Y') }}</p>
    </div>
</div>

<form method="POST" action="{{ route('cycles.update', $cycle) }}" class="card form-card" novalidate>
    <div class="card-body">
    @csrf
    @method('PUT')

    @include('partials.choice', ['name' => 'cycle_status', 'legend' => 'Keadaan siklus', 'idBase' => 'cs', 'cols' => 'cols-2', 'required' => false,
        'options' => $options, 'selected' => old('cycle_status', $cycle->cycle_status)])

    <div class="field">
        <label for="end_date" class="form-label">Tanggal selesai (hanya untuk siklus selesai atau dibatalkan)</label>
        <input id="end_date" name="end_date" type="date" min="{{ $cycle->start_date->toDateString() }}" max="{{ now()->toDateString() }}"
               value="{{ old('end_date', $cycle->end_date?->toDateString()) }}" class="form-control @error('end_date') is-invalid @enderror" aria-describedby="end-help">
        <div id="end-help" class="form-text">Jika dikosongkan, dipakai tanggal hari ini.</div>
        @include('partials.field-error', ['name' => 'end_date'])
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg" data-busy="Menyimpan..."><i class="bi bi-check2-circle" aria-hidden="true"></i> Simpan</button>
        <a href="{{ route('ponds.show', $cycle->pond_id) }}" class="btn btn-outline-secondary btn-lg">Batal</a>
    </div>
    </div>
</form>
@endsection
