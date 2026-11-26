@extends('layouts.koordinator')
@section('title', 'Dashboard')

@php
    use App\Support\Label;
    use App\Support\RecordFeed;
    $prioText = Label::PRIORITIES;
    $activePonds = collect($byStatus)->except(Label::INACTIVE_PONDS)->sum();
    $h = (int) now()->format('G');
    $salam = $h < 11 ? 'Selamat pagi' : ($h < 15 ? 'Selamat siang' : ($h < 18 ? 'Selamat sore' : 'Selamat malam'));
@endphp

@section('content')
<div class="page-head">
    <div>
        <div class="eyebrow"><i class="bi bi-calendar3" aria-hidden="true"></i>{{ now()->translatedFormat('l, j F Y') }}</div>
        <h1>Dashboard Kelompok</h1>
        <p class="lead-text">{{ $salam }}, {{ auth()->user()->name }}. Berikut ringkasan seluruh tambak kelompok.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('k.issues.index', ['status' => 'terbuka']) }}" class="btn btn-danger-soft btn-lg"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i> Tindak Lanjut Kendala</a>
        <a href="{{ route('k.ponds.create') }}" class="btn btn-primary btn-lg"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Tambak</a>
    </div>
</div>

{{-- 1. Filter periode --}}
<form method="GET" class="card filter-card" aria-label="Pilih periode">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            @include('koordinator.partials.tanggal')
            @include('koordinator.partials.aksi-filter', ['presets' => true])
        </div>
    </div>
</form>
@include('koordinator.partials.sumber')

{{-- 2-5. Ringkasan bento (urutan isi mengikuti PRD 7.4; susunan visual diatur CSS) --}}
<div class="bento bento-dash mb-4">
    <a href="{{ route('k.members.index') }}" class="kpi b-anggota">
        <div class="kpi-top"><span class="kpi-label">Total Anggota</span><span class="kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span></div>
        <div class="kpi-value">{{ $memberTotal }}</div>
        <div class="kpi-hint">{{ $memberActive }} aktif</div>
    </a>
    <a href="{{ route('k.ponds.index') }}" class="kpi tone-ok b-tambak">
        <div class="kpi-top"><span class="kpi-label">Total Tambak</span><span class="kpi-icon"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i></span></div>
        <div class="kpi-value">{{ $pondTotal }}</div>
        <div class="kpi-hint">{{ $activePonds }} aktif berproduksi</div>
    </a>

    {{-- 3. Tambak berdasarkan status --}}
    <section class="card b-status" aria-labelledby="status-tambak">
        <div class="card-body">
            <div class="card-title-row mb-3">
                <h2 id="status-tambak" class="h5 section-title"><i class="bi bi-diagram-3" aria-hidden="true"></i>Tambak Berdasarkan Status</h2>
                <span class="text-muted">Ketuk untuk melihat daftarnya</span>
            </div>
            <div class="status-tiles">
                @foreach (Label::PONDS as $key => [$name, $color])
                    <a href="{{ route('k.ponds.index', ['status' => $key]) }}" class="status-tile">
                        <span class="n">{{ $byStatus[$key] ?? 0 }}</span>
                        <span>@include('partials.status', ['label' => $name, 'color' => $color])</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 4. Total hasil panen pada periode --}}
    <a href="{{ route('k.harvests', ['dari' => $period->from?->toDateString(), 'sampai' => $period->to?->toDateString()]) }}" class="kpi hero-kpi b-panen">
        <div class="kpi-top"><span class="kpi-label">Total Hasil Panen</span><span class="kpi-icon"><i class="bi bi-basket2" aria-hidden="true"></i></span></div>
        <div class="kpi-value">{{ number_format($harvestKg, 0, ',', '.') }}<span class="unit">kg</span></div>
        <div class="kpi-hint">Periode {{ $period->label() }}. Panen dalam ton sudah diubah ke kg.</div>
    </a>
    <a href="{{ route('k.issues.index') }}" class="kpi tone-bad b-kendala">
        <div class="kpi-top"><span class="kpi-label">Kendala Terbuka</span><span class="kpi-icon"><i class="bi bi-exclamation-octagon" aria-hidden="true"></i></span></div>
        <div class="kpi-value">{{ $openIssueTotal }}</div>
        <div class="kpi-hint">perlu ditindaklanjuti</div>
    </a>
</div>

<div class="grid-2 grid-2-even mb-4">
    {{-- 5. Kendala yang belum selesai --}}
    <section aria-labelledby="kendala-belum">
        <div class="card-title-row">
            <h2 id="kendala-belum" class="h5 section-title"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Kendala yang Belum Selesai ({{ $openIssueTotal }})</h2>
            <a href="{{ route('k.issues.index') }}" class="btn btn-ghost">Semua <i class="bi bi-chevron-right" aria-hidden="true"></i></a>
        </div>
        <div class="feed">
        @forelse ($openIssues as $i)
            <a href="{{ route('k.issues.show', $i) }}" class="card record record-kendala">
                <span class="record-ic"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
                <div class="record-main">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                        <strong class="record-title">{{ $i->pond->pond_name ?: $i->pond->pond_code }} ({{ $i->pond->pond_code }})</strong>
                        @include('partials.status', ['label' => Label::issue($i->status)[0], 'color' => Label::issue($i->status)[1]])
                    </div>
                    <div class="fw-semibold">{{ Label::CATEGORIES[$i->category] ?? $i->category }}</div>
                    <div class="text-muted">{{ \Illuminate\Support\Str::limit($i->description, 90) }}</div>
                    <div class="record-meta">
                        <span><i class="bi bi-flag" aria-hidden="true"></i>{{ $prioText[$i->priority] ?? $i->priority }}</span>
                        <span><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $i->reported_at->translatedFormat('j F Y') }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="card empty-state"><i class="bi bi-check2-circle" aria-hidden="true"></i><p class="fw-semibold">Tidak ada kendala yang belum selesai.</p></div>
        @endforelse
        </div>
    </section>
    {{-- 6. Grafik produksi --}}
    <section class="card" aria-labelledby="grafik">
        <div class="card-body">
            <h2 id="grafik" class="h5 section-title mb-3"><i class="bi bi-bar-chart" aria-hidden="true"></i>Grafik Produksi per Bulan</h2>
            @if ($harvestKg > 0)
                <div class="chart-box">
                    <canvas id="grafik-produksi" role="img" aria-label="Grafik batang hasil panen per bulan dalam kg. Angkanya ada pada tabel di bawah grafik."
                        data-chart="{{ json_encode($chart) }}"></canvas>
                </div>
            @else
                <div class="empty-state"><i class="bi bi-bar-chart" aria-hidden="true"></i><p class="fw-semibold">Belum ada panen tercatat pada periode ini.</p></div>
            @endif
            <details class="disclosure mt-2">
                <summary>Lihat angka per bulan</summary>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th scope="col">Bulan</th><th scope="col" class="table-num">Hasil panen (kg)</th></tr></thead>
                        <tbody>
                            @foreach ($chart as $row)
                                <tr><td>{{ $row['label'] }}</td><td class="table-num">{{ number_format($row['kg'], 0, ',', '.') }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
    </section>

</div>

{{-- 7. Aktivitas terbaru --}}
<section aria-labelledby="aktivitas">
    <h2 id="aktivitas" class="h4 section-title mb-3"><i class="bi bi-clock-history" aria-hidden="true"></i>Aktivitas Terbaru</h2>
    @include('partials.feed', ['items' => $recent])
</section>
@endsection

@push('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
<script src="{{ asset('js/koordinator-grafik.js') }}"></script>
@endpush
