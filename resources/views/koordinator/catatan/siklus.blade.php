@extends('layouts.koordinator')
@section('title', 'Siklus dan Kegiatan')
@php use App\Support\Label; @endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Siklus Produksi dan Kegiatan</h1>
        <p class="lead-text">Putaran produksi tiap tambak beserta kegiatannya.</p>
    </div>
</div>

<form method="GET" class="card card-body filter-card" aria-label="Saring siklus">
    <div class="row g-3 align-items-end">
        @include('koordinator.partials.pilih-tambak')
        <div class="col-12 col-md-3">
            <label for="status" class="form-label">Status siklus</label>
            <select id="status" name="status" class="form-select">
                <option value="">Semua status</option>
                @foreach (Label::cycleNames() as $k => $name)
                    <option value="{{ $k }}" @selected($f['status'] === $k)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        @include('koordinator.partials.tanggal')
        @include('koordinator.partials.aksi-filter')
    </div>
</form>

<p class="info-strip"><i class="bi bi-calendar-range" aria-hidden="true"></i>Periode: {{ $period->label() }}. Siklus yang masa berjalannya masuk periode ikut ditampilkan. {{ $cycles->total() }} siklus ditemukan.</p>

@forelse ($cycles as $cy)
    <article class="card card-body mb-3">
        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
            <div>
                <h2 class="h5 mb-1">{{ $cy->pond->pond_name ?: $cy->pond->pond_code }} ({{ $cy->pond->pond_code }})</h2>
                <div class="text-muted">{{ $cy->pond->member->name }} &middot; {{ $cy->start_date->translatedFormat('j F Y') }} sampai {{ $cy->end_date ? $cy->end_date->translatedFormat('j F Y') : 'sekarang' }}</div>
            </div>
            @include('partials.status', ['label' => Label::cycle($cy->cycle_status)[0], 'color' => Label::cycle($cy->cycle_status)[1]])
        </div>
        <details class="disclosure mt-2">
            <summary>{{ $cy->activities->count() }} kegiatan tercatat</summary>
            @forelse ($cy->activities as $a)
                <div class="border-top py-2">
                    <strong>{{ Label::activity($a->activity_type) }}</strong>
                    <span class="text-muted">&middot; {{ $a->activity_date->translatedFormat('j F Y') }} &middot; dicatat {{ $a->recorder->name }}</span>
                    @if ($a->description)<div>{{ $a->description }}</div>@endif
                </div>
            @empty
                <p class="mb-0">Belum ada kegiatan.</p>
            @endforelse
        </details>
    </article>
@empty
    <div class="card empty-state"><i class="bi bi-search" aria-hidden="true"></i><p class="fw-semibold">Tidak ada siklus yang cocok.</p></div>
@endforelse
{{ $cycles->links() }}
@endsection
