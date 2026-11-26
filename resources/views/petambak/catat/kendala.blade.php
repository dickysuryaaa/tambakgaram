@extends('layouts.petambak')
@section('title', 'Laporkan Kendala')

@php
    $categoryIcons = ['tanggul_bocor' => ['bi-bricks', 'bad'], 'pompa_saluran' => ['bi-gear-wide-connected', 'sea'], 'cuaca_hujan' => ['bi-cloud-rain', 'warn'], 'lainnya' => ['bi-three-dots', 'mute']];
    $categories = collect(\App\Support\Label::CATEGORIES)->map(fn ($v, $k) => ['label' => $v, 'icon' => $categoryIcons[$k][0] ?? 'bi-circle', 'tone' => $categoryIcons[$k][1] ?? 'mute'])->all();
    $prioIcons = ['rendah' => ['bi-hourglass', 'ok'], 'sedang' => ['bi-hourglass-split', 'warn'], 'tinggi' => ['bi-lightning-charge', 'bad']];
    $priorities = collect(\App\Support\Label::PRIORITIES)->map(fn ($v, $k) => ['label' => $v, 'icon' => $prioIcons[$k][0] ?? 'bi-circle', 'tone' => $prioIcons[$k][1] ?? 'mute'])->all();
@endphp

@section('content')
@include('partials.form-head', ['heading' => 'Laporkan Kendala', 'lead' => 'Koordinator akan menerima laporan ini dan menindaklanjutinya.'])

@if ($ponds->isEmpty())
    @include('partials.no-ponds')
@else
<form method="POST" action="{{ route('catat.kendala') }}" class="card form-card" novalidate>
    <div class="card-body">
    @csrf
    @include('partials.pond-select')

    @include('partials.choice', ['name' => 'category', 'legend' => 'Jenis kendala', 'options' => $categories, 'selected' => old('category'), 'cols' => 'cols-2'])
    @include('partials.choice', ['name' => 'priority', 'legend' => 'Seberapa mendesak?', 'options' => $priorities, 'selected' => old('priority', 'sedang'), 'cols' => 'cols-3'])

    <div class="field">
        <label for="description" class="form-label">Ceritakan kendalanya <span class="req" aria-hidden="true">*</span></label>
        <textarea id="description" name="description" rows="5" required maxlength="2000" class="form-control @error('description') is-invalid @enderror" aria-describedby="desc-help">{{ old('description') }}</textarea>
        <div id="desc-help" class="form-text">Contoh: Tanggul sisi timur bocor sekitar 2 meter.</div>
        @include('partials.field-error', ['name' => 'description'])
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg" data-busy="Mengirim..."><i class="bi bi-send" aria-hidden="true"></i> Kirim Laporan</button>
        <a href="{{ route('catat') }}" class="btn btn-outline-secondary btn-lg">Batal</a>
    </div>
    </div>
</form>
@endif
@endsection
