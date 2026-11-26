@extends('layouts.petambak')
@section('title', $pond->pond_code)

@php use App\Support\Label; use App\Support\RecordFeed; [$l, $c] = Label::pond($pond->operational_status); @endphp

@section('content')
<a href="{{ route('ponds.index') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> Tambak Saya</a>

<div class="page-head">
    <div>
        <div class="eyebrow"><i class="bi bi-water" aria-hidden="true"></i>Kode {{ $pond->pond_code }}</div>
        <h1>{{ $pond->pond_name ?: $pond->pond_code }}</h1>
        <div class="mt-2">@include('partials.status', ['label' => $l, 'color' => $c])</div>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('catat.status', ['tambak' => $pond->pond_id]) }}" class="btn btn-outline-primary"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Ubah Status</a>
    </div>
</div>

<div class="fact-grid mb-4">
    <div class="fact"><div class="l"><i class="bi bi-bounding-box" aria-hidden="true"></i>Luas</div><div class="v">{{ number_format($pond->area_m2, 0, ',', '.') }} m&sup2;</div></div>
    <div class="fact"><div class="l"><i class="bi bi-basket2" aria-hidden="true"></i>Total panen</div><div class="v">{{ number_format($harvestKg, 0, ',', '.') }} kg</div></div>
    <div class="fact"><div class="l"><i class="bi bi-exclamation-octagon" aria-hidden="true"></i>Kendala terbuka</div><div class="v">{{ $openIssues->count() }}</div></div>
    <div class="fact"><div class="l"><i class="bi bi-arrow-repeat" aria-hidden="true"></i>Siklus</div><div class="v">{{ $cycles->count() }}</div></div>
</div>

<div class="d-grid gap-2 mb-4" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <a href="{{ route('catat.panen', ['tambak' => $pond->pond_id]) }}" class="btn btn-primary btn-lg"><i class="bi bi-basket2" aria-hidden="true"></i> Catat Panen</a>
    <a href="{{ route('catat.air', ['tambak' => $pond->pond_id]) }}" class="btn btn-outline-primary btn-lg"><i class="bi bi-droplet-half" aria-hidden="true"></i> Catat Pengamatan Air</a>
    <a href="{{ route('catat.kendala', ['tambak' => $pond->pond_id]) }}" class="btn btn-danger-soft btn-lg"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i> Laporkan Kendala</a>
</div>

<div class="grid-2">
    <div class="stack">
        @if ($openIssues->isNotEmpty())
            <section aria-labelledby="kendala-terbuka">
                <h2 id="kendala-terbuka" class="h4 section-title mb-3"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Kendala yang Belum Selesai</h2>
                <div class="feed">
                @foreach ($openIssues as $i)
                    <div class="card record record-kendala">
                        <span class="record-ic"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
                        <div class="record-main">
                            <div class="d-flex justify-content-between gap-2 flex-wrap">
                                <strong class="record-title">{{ Label::CATEGORIES[$i->category] ?? $i->category }}</strong>
                                @include('partials.status', ['label' => Label::issue($i->status)[0], 'color' => Label::issue($i->status)[1]])
                            </div>
                            <div>{{ $i->description }}</div>
                            <div class="record-meta"><span><i class="bi bi-calendar3" aria-hidden="true"></i>Dilaporkan {{ $i->reported_at->translatedFormat('j F Y') }}</span></div>
                        </div>
                    </div>
                @endforeach
                </div>
            </section>
        @endif

        <section aria-labelledby="siklus">
            <div class="card-title-row">
                <h2 id="siklus" class="h4 section-title"><i class="bi bi-arrow-repeat" aria-hidden="true"></i>Siklus Produksi</h2>
                <a href="{{ route('catat.siklus', ['tambak' => $pond->pond_id]) }}" class="btn btn-outline-primary"><i class="bi bi-plus-circle" aria-hidden="true"></i> Siklus Baru</a>
            </div>
            <div class="feed">
            @forelse ($cycles as $cy)
                <div class="card card-body">
                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <div>
                            <div class="fw-bold">Mulai {{ $cy->start_date->translatedFormat('j F Y') }}</div>
                            <div class="text-muted">
                                {{ $cy->end_date ? 'Selesai '.$cy->end_date->translatedFormat('j F Y') : 'Belum selesai' }}
                                &middot; <span class="tabular">{{ $cy->activities_count }}</span> kegiatan
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @include('partials.status', ['label' => Label::cycle($cy->cycle_status)[0], 'color' => Label::cycle($cy->cycle_status)[1]])
                            <a href="{{ route('cycles.edit', $cy) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil" aria-hidden="true"></i> Ubah</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card empty-state">
                    <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                    <p class="fw-semibold">Belum ada siklus. Buat siklus baru sebelum mencatat panen atau kegiatan.</p>
                </div>
            @endforelse
            </div>
        </section>
    </div>

    <section class="card" aria-labelledby="info-tambak">
        <div class="card-body">
            <h2 id="info-tambak" class="h5 section-title mb-2"><i class="bi bi-info-circle" aria-hidden="true"></i>Informasi Tambak</h2>
            <dl class="kv" style="grid-template-columns: 1fr;">
                <dt>Lokasi</dt><dd>{{ $pond->village }}, {{ $pond->district }}, {{ $pond->regency }}</dd>
                <dt>Pengamatan air terakhir</dt>
                <dd>
                    @if ($latestObs)
                        {{ $latestObs->observed_at->translatedFormat('j F Y, H.i') }}:
                        {{ collect([
                            $latestObs->salinity_value !== null ? 'Salinitas '.RecordFeed::num($latestObs->salinity_value).' °Bé' : null,
                            $latestObs->temperature_c !== null ? 'Suhu '.RecordFeed::num($latestObs->temperature_c).' °C' : null,
                            $latestObs->ph_value !== null ? 'pH '.RecordFeed::num($latestObs->ph_value) : null,
                        ])->filter()->implode(', ') ?: 'tidak ada angka yang diisi' }}
                    @else
                        Belum diisi
                    @endif
                </dd>
                @if ($pond->notes)<dt>Keterangan</dt><dd>{{ $pond->notes }}</dd>@endif
            </dl>
        </div>
    </section>
</div>
@endsection
