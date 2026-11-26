@extends('layouts.koordinator')
@section('title', 'Rekap Periode')
@php
    use App\Support\Label;
    use App\Support\RecordFeed;
    $avg = fn ($v) => $v === null ? '-' : RecordFeed::num(round((float) $v, 2));
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Rekap Periode</h1>
        <p class="lead-text">Ringkasan produksi, status, air, dan kendala dalam satu periode.</p>
    </div>
</div>

<form method="GET" class="card card-body filter-card" aria-label="Pilih periode rekap">
    <div class="row g-3 align-items-end">
        @include('koordinator.partials.tanggal')
        <div class="col-12 col-md-3">
            <label for="anggota" class="form-label">Anggota (pemilik)</label>
            <select id="anggota" name="anggota" class="form-select">
                <option value="">Semua anggota</option>
                @foreach ($members as $m)
                    <option value="{{ $m->member_id }}" @selected((int) $memberId === $m->member_id)>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>
        @include('koordinator.partials.aksi-filter', ['presets' => true])
    </div>
</form>

@include('koordinator.partials.sumber')

{{-- Produksi --}}
<section class="mb-5" aria-labelledby="rekap-produksi">
    <h2 id="rekap-produksi" class="h4 section-title"><i class="bi bi-basket2" aria-hidden="true"></i>Produksi (Hasil Panen)</h2>
    <p>Total <strong>{{ number_format($totalKg, 0, ',', '.') }} kg</strong> dari {{ $totalHarvests }} catatan panen. Panen dalam ton sudah diubah ke kg.</p>
    <div class="table-responsive card table-card">
        <table class="table mb-0">
            <caption class="visually-hidden">Hasil panen per tambak pada periode</caption>
            <thead><tr><th scope="col">Tambak</th><th scope="col">Pemilik</th><th scope="col" class="table-num">Catatan panen</th><th scope="col" class="table-num">Total (kg)</th></tr></thead>
            <tbody>
            @forelse ($ponds as $p)
                @php $h = $harvestRows[$p->pond_id] ?? null; @endphp
                <tr>
                    <td>{{ $p->pond_name ?: $p->pond_code }} ({{ $p->pond_code }})</td>
                    <td>{{ $p->member->name }}</td>
                    <td class="table-num">{{ $h->jumlah ?? 0 }}</td>
                    <td class="table-num">{{ $h ? number_format($h->kg, 0, ',', '.') : '0' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Tidak ada tambak.</td></tr>
            @endforelse
            </tbody>
            <tfoot><tr><th scope="row" colspan="2">Jumlah</th><th class="table-num">{{ $totalHarvests }}</th><th class="table-num">{{ number_format($totalKg, 0, ',', '.') }}</th></tr></tfoot>
        </table>
    </div>
</section>

{{-- Status tambak --}}
<section class="mb-5" aria-labelledby="rekap-status">
    <h2 id="rekap-status" class="h4 section-title"><i class="bi bi-diagram-3" aria-hidden="true"></i>Status Tambak</h2>
    <p class="text-muted">Status menunjukkan keadaan saat halaman ini dibuka, bukan keadaan pada tanggal periode.</p>
    <div class="card card-body">
        <ul class="list-unstyled d-flex flex-wrap gap-2 mb-0">
            @foreach (Label::PONDS as $key => [$name, $color])
                <li>@include('partials.status', ['label' => $name.': '.($statusCount[$key] ?? 0), 'color' => $color])</li>
            @endforeach
        </ul>
    </div>
</section>

{{-- Pengamatan air --}}
<section class="mb-5" aria-labelledby="rekap-air">
    <h2 id="rekap-air" class="h4 section-title"><i class="bi bi-droplet-half" aria-hidden="true"></i>Pengamatan Air</h2>
    <p class="text-muted">Rata-rata hanya dihitung dari angka yang benar-benar diukur. Tanda "-" berarti tidak ada pengukuran.</p>
    <div class="table-responsive card table-card">
        <table class="table mb-0">
            <caption class="visually-hidden">Rata-rata pengamatan air per tambak pada periode</caption>
            <thead><tr><th scope="col">Tambak</th><th scope="col" class="table-num">Jumlah pengamatan</th><th scope="col" class="table-num">Rata-rata salinitas (°Bé)</th><th scope="col" class="table-num">Rata-rata suhu (°C)</th><th scope="col" class="table-num">Rata-rata pH</th></tr></thead>
            <tbody>
            @forelse ($ponds as $p)
                @php $w = $waterRows[$p->pond_id] ?? null; @endphp
                <tr>
                    <td>{{ $p->pond_name ?: $p->pond_code }} ({{ $p->pond_code }})</td>
                    <td class="table-num">{{ $w->jumlah ?? 0 }}</td>
                    <td class="table-num">{{ $avg($w->salinitas ?? null) }}</td>
                    <td class="table-num">{{ $avg($w->suhu ?? null) }}</td>
                    <td class="table-num">{{ $avg($w->ph ?? null) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada tambak.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- Kendala --}}
<section class="mb-5" aria-labelledby="rekap-kendala">
    <h2 id="rekap-kendala" class="h4 section-title"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Kendala yang Dilaporkan</h2>
    <p>{{ $issueTotal }} kendala dilaporkan pada periode ini.</p>
    <div class="card card-body mb-3">
        <div class="d-flex flex-wrap gap-2 mb-2">
            @foreach (Label::ISSUES as $key => [$name, $color])
                @include('partials.status', ['label' => $name.': '.($issueByStatus[$key] ?? 0), 'color' => $color])
            @endforeach
        </div>
        <div>
            @foreach (Label::CATEGORIES as $key => $name)
                <span class="me-3 d-inline-block tabular">{{ $name }}: <strong>{{ $issueByCategory[$key] ?? 0 }}</strong></span>
            @endforeach
        </div>
    </div>
    @if ($issues->isNotEmpty())
        <div class="table-responsive card table-card">
            <table class="table mb-0">
                <caption class="visually-hidden">Daftar kendala pada periode</caption>
                <thead><tr><th scope="col">Tanggal</th><th scope="col">Tambak</th><th scope="col">Jenis</th><th scope="col">Status</th></tr></thead>
                <tbody>
                @foreach ($issues as $i)
                    <tr>
                        <td class="text-nowrap">{{ $i->reported_at->translatedFormat('j M Y') }}</td>
                        <td>{{ $i->pond->pond_name ?: $i->pond->pond_code }} ({{ $i->pond->pond_code }})</td>
                        <td>{{ Label::CATEGORIES[$i->category] ?? $i->category }}</td>
                        <td>@include('partials.status', ['label' => Label::issue($i->status)[0], 'color' => Label::issue($i->status)[1]])</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if ($issueTotal > $issues->count())
            <p class="text-muted mt-2">Menampilkan {{ $issues->count() }} kendala terbaru. Lihat semuanya di menu Kendala.</p>
        @endif
    @endif
</section>
@endsection
