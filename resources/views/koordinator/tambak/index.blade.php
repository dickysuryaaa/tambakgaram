@extends('layouts.koordinator')
@section('title', 'Tambak')
@php
    use App\Support\Label;
    $stages = ['persiapan' => 1, 'pengisian_air' => 2, 'penguapan' => 3, 'kristalisasi_panen' => 4];
    $keep = request()->except(['status', 'page']);
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Data Tambak</h1>
        <p class="lead-text">Semua petak tambak kelompok, pemilik, dan statusnya.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('k.ponds.create') }}" class="btn btn-primary btn-lg"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Tambak</a>
    </div>
</div>

<form method="GET" class="card filter-card" aria-label="Cari tambak">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="q" class="form-label">Cari kode, nama, atau wilayah</label>
                <div class="search-box">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" id="q" name="q" value="{{ $q }}" class="form-control">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label for="status" class="form-label">Status tambak</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach (Label::pondNames() as $k => $name)
                        <option value="{{ $k }}" @selected($status === $k)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="anggota" class="form-label">Anggota (pemilik)</label>
                <select id="anggota" name="anggota" class="form-select">
                    <option value="">Semua anggota</option>
                    @foreach ($members as $m)
                        <option value="{{ $m->member_id }}" @selected((string) $memberId === (string) $m->member_id)>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search" aria-hidden="true"></i> Cari</button>
                <a href="{{ route('k.ponds.index') }}" class="btn btn-outline-secondary">Hapus Filter</a>
            </div>
        </div>
    </div>
</form>

<section data-pond-board aria-label="Daftar tambak">
    <div class="toolbar">
        <nav class="chip-row flex-grow-1" aria-label="Saring status tambak">
            <a href="{{ route('k.ponds.index', $keep) }}" class="chip text-decoration-none" @unless ($status) aria-current="page" @endunless>Semua</a>
            @foreach (Label::pondNames() as $k => $name)
                <a href="{{ route('k.ponds.index', $keep + ['status' => $k]) }}" class="chip text-decoration-none" @if ($status === $k) aria-current="page" @endif>{{ $name }}</a>
            @endforeach
        </nav>
        <div class="view-toggle" role="group" aria-label="Pilih tampilan daftar">
            <button type="button" data-view="kartu" aria-pressed="true"><i class="bi bi-grid" aria-hidden="true"></i>Kartu</button>
            <button type="button" data-view="tabel" aria-pressed="false"><i class="bi bi-list-ul" aria-hidden="true"></i>Tabel</button>
        </div>
    </div>

    @if ($ponds->isEmpty())
        <div class="card empty-state"><i class="bi bi-search" aria-hidden="true"></i><p class="fw-semibold">Tidak ada tambak yang cocok.</p></div>
    @else
        <div class="pond-list" data-view-pane="kartu">
            @foreach ($ponds as $p)
                @php
                    [$l, $c] = Label::pond($p->operational_status);
                    $step = $stages[$p->operational_status] ?? 0;
                    $tone = $p->open_issues_count > 0 ? 'danger' : $c;
                @endphp
                <article class="card pond-item tone-{{ $tone }}">
                    <div class="pond-head">
                        <span class="pond-avatar"><i class="bi bi-water" aria-hidden="true"></i></span>
                        <div class="min-w-0">
                            <a href="{{ route('k.ponds.show', $p) }}" class="pond-name d-block text-decoration-none text-body">{{ $p->pond_name ?: $p->pond_code }}</a>
                            <span class="pond-meta d-block">Kode {{ $p->pond_code }} &middot; {{ $p->member->name }}</span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @include('partials.status', ['label' => $l, 'color' => $c])
                        @if ($p->open_issues_count > 0)
                            @include('partials.status', ['label' => $p->open_issues_count.' kendala belum selesai', 'color' => 'danger'])
                        @endif
                    </div>
                    <dl class="pond-facts">
                        <div><dt>Luas</dt><dd>{{ number_format($p->area_m2, 0, ',', '.') }} m&sup2;</dd></div>
                        <div><dt>Wilayah</dt><dd class="text-truncate">{{ $p->village }}</dd></div>
                    </dl>
                    <div>
                        <div class="readiness-top"><span class="t">Kesiapan panen</span><span class="p">{{ $step * 25 }}%</span></div>
                        <div class="meter {{ $step === 4 ? 'ok' : ($step === 0 ? 'off' : '') }}" role="progressbar" aria-label="Kesiapan panen {{ $p->pond_code }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $step * 25 }}"><span style="width: {{ $step * 25 }}%"></span></div>
                    </div>
                    <div class="pond-actions">
                        <a href="{{ route('k.ponds.show', $p) }}" class="btn btn-primary"><i class="bi bi-card-list" aria-hidden="true"></i>Detail</a>
                        <a href="{{ route('k.ponds.edit', $p) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil" aria-hidden="true"></i>Ubah</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="card table-card pond-table-wrap" data-view-pane="tabel" hidden>
            <div class="table-responsive">
                <table class="table">
                    <caption class="visually-hidden">Daftar tambak</caption>
                    <thead>
                        <tr><th scope="col">Kode</th><th scope="col">Nama tambak</th><th scope="col">Pemilik</th><th scope="col">Wilayah</th><th scope="col" class="table-num">Luas (m&sup2;)</th><th scope="col">Status</th><th scope="col">Kendala</th><th scope="col">Aksi</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($ponds as $p)
                        <tr>
                            <td class="fw-bold">{{ $p->pond_code }}</td>
                            <td>{{ $p->pond_name ?: '-' }}</td>
                            <td>{{ $p->member->name }}</td>
                            <td>{{ $p->village }}, {{ $p->district }}</td>
                            <td class="table-num">{{ number_format($p->area_m2, 0, ',', '.') }}</td>
                            <td>@php [$l, $c] = Label::pond($p->operational_status); @endphp @include('partials.status', ['label' => $l, 'color' => $c])</td>
                            <td>
                                @if ($p->open_issues_count > 0)
                                    @include('partials.status', ['label' => $p->open_issues_count.' belum selesai', 'color' => 'danger'])
                                @else
                                    Tidak ada
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('k.ponds.show', $p) }}" class="btn btn-outline-primary">Lihat</a>
                                <a href="{{ route('k.ponds.edit', $p) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil" aria-hidden="true"></i> Ubah</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>
{{ $ponds->links() }}
@endsection
