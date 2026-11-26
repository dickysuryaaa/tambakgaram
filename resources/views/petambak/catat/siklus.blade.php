@extends('layouts.petambak')
@section('title', 'Mulai Siklus Baru')

@section('content')
@include('partials.form-head', ['heading' => 'Mulai Siklus Baru', 'lead' => 'Satu siklus adalah satu putaran produksi garam, dari persiapan sampai panen.'])

@if ($ponds->isEmpty())
    @include('partials.no-ponds')
@else
<form method="POST" action="{{ route('catat.siklus') }}" class="card form-card" novalidate>
    <div class="card-body">
    @csrf
    @include('partials.pond-select')

    <div class="field">
        <label for="start_date" class="form-label">Tanggal mulai <span class="req" aria-hidden="true">*</span></label>
        <input id="start_date" name="start_date" type="date" required max="{{ now()->toDateString() }}"
               value="{{ old('start_date', now()->toDateString()) }}" class="form-control @error('start_date') is-invalid @enderror">
        @include('partials.field-error', ['name' => 'start_date'])
    </div>

    @include('partials.choice', [
        'name' => 'cycle_status', 'legend' => 'Keadaan siklus', 'idBase' => 'cs', 'cols' => 'cols-2', 'required' => false,
        'selected' => old('cycle_status', 'berjalan'),
        'options' => [
            'persiapan' => ['label' => 'Persiapan', 'desc' => 'Belum mulai produksi', 'icon' => 'bi-tools', 'tone' => 'warn'],
            'berjalan' => ['label' => 'Berjalan', 'desc' => 'Produksi sedang berlangsung', 'icon' => 'bi-play-circle', 'tone' => 'ok'],
        ],
    ])

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg" data-busy="Menyimpan..."><i class="bi bi-check2-circle" aria-hidden="true"></i> Simpan Siklus</button>
        <a href="{{ route('catat') }}" class="btn btn-outline-secondary btn-lg">Batal</a>
    </div>
    </div>
</form>
@endif
@endsection
