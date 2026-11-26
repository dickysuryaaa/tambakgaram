@extends('layouts.petambak')
@section('title', 'Riwayat')

@section('content')
<div class="page-head">
    <div>
        <h1>Riwayat</h1>
        <p class="lead-text">Semua catatan yang pernah Anda buat.</p>
    </div>
</div>

<details class="card filter-card" @if ($type || $pondId || $dari || $sampai || $errors->any()) open @endif>
    <summary class="card-body"><span class="section-title"><i class="bi bi-funnel" aria-hidden="true"></i>Saring catatan</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
    <form method="GET" action="{{ route('riwayat') }}" class="card-body pt-0" novalidate>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="jenis" class="form-label">Jenis catatan</label>
                <select id="jenis" name="jenis" class="form-select">
                    <option value="">Semua</option>
                    @foreach (\App\Support\RecordFeed::TYPES as $k => $v)
                        <option value="{{ $k }}" @selected($type === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label for="tambak" class="form-label">Tambak</label>
                <select id="tambak" name="tambak" class="form-select">
                    <option value="">Semua tambak</option>
                    @foreach ($ponds as $p)
                        <option value="{{ $p->pond_id }}" @selected($pondId === $p->pond_id)>{{ $p->pond_name ?: $p->pond_code }} ({{ $p->pond_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <label for="dari" class="form-label">Dari tanggal</label>
                <input id="dari" name="dari" type="date" value="{{ $dari }}" class="form-control @error('dari') is-invalid @enderror">
            </div>
            <div class="col-6">
                <label for="sampai" class="form-label">Sampai tanggal</label>
                <input id="sampai" name="sampai" type="date" value="{{ $sampai }}" class="form-control @error('sampai') is-invalid @enderror">
                @include('partials.field-error', ['name' => 'sampai'])
            </div>
            <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel" aria-hidden="true"></i> Tampilkan</button>
                <a href="{{ route('riwayat') }}" class="btn btn-outline-secondary">Hapus Pilihan</a>
            </div>
        </div>
    </form>
</details>

@if ($totalKg !== null)
    <div class="kpi hero-kpi mb-3" style="min-height: auto;">
        <div class="kpi-top"><span class="kpi-label">Total panen pada pilihan ini</span><span class="kpi-icon"><i class="bi bi-basket2" aria-hidden="true"></i></span></div>
        <div class="kpi-value num">{{ number_format($totalKg, 0, ',', '.') }}<span class="unit">kg</span></div>
    </div>
@endif

@include('partials.feed', ['items' => $items])

@if ($items->count() >= $limit)
    <p class="text-muted mt-3">Menampilkan {{ $limit }} catatan terbaru. Persempit dengan pilihan tanggal atau tambak untuk melihat yang lebih lama.</p>
@endif
@endsection
