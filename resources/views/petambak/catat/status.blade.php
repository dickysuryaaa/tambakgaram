@extends('layouts.petambak')
@section('title', 'Ubah Status Tambak')

@section('content')
@include('partials.form-head', ['heading' => 'Ubah Status Tambak', 'lead' => 'Pilih tahap produksi tambak saat ini.'])

@if ($ponds->isEmpty())
    @include('partials.no-ponds')
@else
<form method="POST" action="{{ route('catat.status') }}" class="card form-card" novalidate>
    <div class="card-body">
    @csrf
    @include('partials.pond-select')
    @include('partials.pond-status-choice', ['legend' => 'Status baru', 'selected' => old('operational_status')])

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg" data-busy="Menyimpan..."><i class="bi bi-check2-circle" aria-hidden="true"></i> Simpan Status</button>
        <a href="{{ route('catat') }}" class="btn btn-outline-secondary btn-lg">Batal</a>
    </div>
    </div>
</form>
@endif
@endsection
