@extends('layouts.petambak')
@section('title', 'Catat Pengamatan Air')

@section('content')
@include('partials.form-head', ['heading' => 'Catat Pengamatan Air', 'lead' => 'Isi hanya angka yang benar-benar Anda ukur.'])

@if ($ponds->isEmpty())
    @include('partials.no-ponds')
@else
<form method="POST" action="{{ route('catat.air') }}" class="card form-card" novalidate>
    <div class="card-body">
    @csrf
    @include('partials.pond-select')

    <div class="field">
        <label for="observed_at" class="form-label">Tanggal dan jam pengamatan <span class="req" aria-hidden="true">*</span></label>
        <input id="observed_at" name="observed_at" type="datetime-local" required max="{{ now()->format('Y-m-d\TH:i') }}"
               value="{{ old('observed_at', now()->format('Y-m-d\TH:i')) }}" class="form-control @error('observed_at') is-invalid @enderror">
        @include('partials.field-error', ['name' => 'observed_at'])
    </div>

    <fieldset class="form-section field">
        <legend class="form-section-title">Hasil pengukuran</legend>
        <p class="text-muted mb-3">Isi hanya yang Anda ukur. Yang tidak diukur, kosongkan saja.</p>
        @error('salinity_value')<div class="alert alert-danger py-2" role="alert">{{ $message }}</div>@enderror

        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="salinity_value" class="form-label">Salinitas (°Bé)</label>
                <input id="salinity_value" name="salinity_value" type="text" inputmode="decimal" value="{{ old('salinity_value') }}" class="form-control tabular @error('salinity_value') is-invalid @enderror" aria-describedby="sal-help">
                <div id="sal-help" class="form-text">Derajat Baumé. Contoh: 18,5</div>
            </div>
            <div class="col-12 col-md-4">
                <label for="temperature_c" class="form-label">Suhu air (°C)</label>
                <input id="temperature_c" name="temperature_c" type="text" inputmode="decimal" value="{{ old('temperature_c') }}" class="form-control tabular @error('temperature_c') is-invalid @enderror" aria-describedby="suhu-help">
                <div id="suhu-help" class="form-text">Contoh: 32</div>
                @include('partials.field-error', ['name' => 'temperature_c'])
            </div>
            <div class="col-12 col-md-4">
                <label for="ph_value" class="form-label">Keasaman air (pH)</label>
                <input id="ph_value" name="ph_value" type="text" inputmode="decimal" value="{{ old('ph_value') }}" class="form-control tabular @error('ph_value') is-invalid @enderror" aria-describedby="ph-help">
                <div id="ph-help" class="form-text">Contoh: 7,8</div>
                @include('partials.field-error', ['name' => 'ph_value'])
            </div>
        </div>
    </fieldset>

    <div class="field">
        <label for="instrument" class="form-label">Alat ukur yang dipakai (boleh dikosongkan)</label>
        <input id="instrument" name="instrument" type="text" maxlength="255" value="{{ old('instrument') }}" class="form-control @error('instrument') is-invalid @enderror" aria-describedby="alat-help">
        <div id="alat-help" class="form-text">Contoh: Baumé meter, termometer</div>
        @include('partials.field-error', ['name' => 'instrument'])
    </div>

    <div class="field">
        <label for="notes" class="form-label">Catatan (boleh dikosongkan)</label>
        <textarea id="notes" name="notes" rows="3" maxlength="1000" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
        @include('partials.field-error', ['name' => 'notes'])
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg" data-busy="Menyimpan..."><i class="bi bi-check2-circle" aria-hidden="true"></i> Simpan Pengamatan Air</button>
        <a href="{{ route('catat') }}" class="btn btn-outline-secondary btn-lg">Batal</a>
    </div>
    </div>
</form>
@endif
@endsection
