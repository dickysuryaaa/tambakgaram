@extends('layouts.petambak')
@section('title', 'Catat Kegiatan')

@php
    $icons = ['pembersihan_lahan' => 'bi-brush', 'pengisian_air' => 'bi-water', 'pengaturan_air' => 'bi-arrow-left-right', 'perawatan_tanggul' => 'bi-bricks', 'perbaikan_alat' => 'bi-wrench-adjustable', 'lainnya' => 'bi-three-dots'];
    $activities = collect(\App\Support\Label::ACTIVITIES)->map(fn ($v, $k) => ['label' => $v, 'icon' => $icons[$k] ?? 'bi-circle', 'tone' => $k === 'lainnya' ? 'mute' : 'sea'])->all();
@endphp

@section('content')
@include('partials.form-head', ['heading' => 'Catat Kegiatan', 'lead' => 'Catat pekerjaan yang dilakukan di tambak.'])

@if ($ponds->isEmpty())
    @include('partials.no-ponds')
@else
<form method="POST" action="{{ route('catat.kegiatan') }}" class="card form-card" novalidate>
    <div class="card-body">
    @csrf
    @include('partials.pond-select')
    @include('partials.cycle-select')

    @include('partials.choice', ['name' => 'activity_type', 'legend' => 'Jenis kegiatan', 'options' => $activities, 'selected' => old('activity_type'), 'cols' => 'cols-2'])

    <div class="field">
        <label for="activity_date" class="form-label">Tanggal kegiatan <span class="req" aria-hidden="true">*</span></label>
        <input id="activity_date" name="activity_date" type="date" required max="{{ now()->toDateString() }}"
               value="{{ old('activity_date', now()->toDateString()) }}" class="form-control @error('activity_date') is-invalid @enderror">
        @include('partials.field-error', ['name' => 'activity_date'])
    </div>

    <div class="field">
        <label for="description" class="form-label">Catatan singkat (boleh dikosongkan)</label>
        <textarea id="description" name="description" rows="3" maxlength="1000" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
        @include('partials.field-error', ['name' => 'description'])
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg" data-busy="Menyimpan..."><i class="bi bi-check2-circle" aria-hidden="true"></i> Simpan Catatan Kegiatan</button>
        <a href="{{ route('catat') }}" class="btn btn-outline-secondary btn-lg">Batal</a>
    </div>
    </div>
</form>
@endif
@endsection
