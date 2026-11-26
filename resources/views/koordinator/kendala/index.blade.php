@extends('layouts.koordinator')
@section('title', 'Kendala')
@php use App\Support\Label; @endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Kendala dan Tindak Lanjut</h1>
        <p class="lead-text">Pantau dan tindak lanjuti laporan kendala dari petambak.</p>
    </div>
</div>

<form method="GET" class="card card-body filter-card" aria-label="Saring kendala">
    <div class="row g-3 align-items-end">
        @include('koordinator.partials.pilih-tambak')
        <div class="col-6 col-md-3">
            <label for="status" class="form-label">Status tindak lanjut</label>
            <select id="status" name="status" class="form-select">
                <option value="">Semua status</option>
                @foreach (Label::ISSUES as $k => [$name])
                    <option value="{{ $k }}" @selected($f['status'] === $k)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label for="kategori" class="form-label">Jenis kendala</label>
            <select id="kategori" name="kategori" class="form-select">
                <option value="">Semua jenis</option>
                @foreach (Label::CATEGORIES as $k => $name)
                    <option value="{{ $k }}" @selected($f['kategori'] === $k)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        @include('koordinator.partials.tanggal')
        @include('koordinator.partials.aksi-filter')
    </div>
</form>

<p class="info-strip"><i class="bi bi-calendar-range" aria-hidden="true"></i>Periode laporan: {{ $period->label() }}. {{ $issues->total() }} kendala ditemukan.</p>

<div class="table-responsive card mb-3 table-card">
    <table class="table mb-0">
        <caption class="visually-hidden">Daftar kendala</caption>
        <thead>
            <tr><th scope="col">Dilaporkan</th><th scope="col">Tambak</th><th scope="col">Jenis</th><th scope="col">Mendesak</th><th scope="col">Status</th><th scope="col">Aksi</th></tr>
        </thead>
        <tbody>
        @forelse ($issues as $i)
            <tr>
                <td class="text-nowrap">{{ $i->reported_at->translatedFormat('j M Y') }}</td>
                <td>{{ $i->pond->pond_name ?: $i->pond->pond_code }} ({{ $i->pond->pond_code }})<br><span class="text-muted">{{ $i->pond->member->name }}</span></td>
                <td>{{ Label::CATEGORIES[$i->category] ?? $i->category }}<br><span class="text-muted">{{ \Illuminate\Support\Str::limit($i->description, 80) }}</span></td>
                <td>{{ Label::PRIORITIES[$i->priority] ?? $i->priority }}</td>
                <td>@include('partials.status', ['label' => Label::issue($i->status)[0], 'color' => Label::issue($i->status)[1]])</td>
                <td><a href="{{ route('k.issues.show', $i) }}" class="btn btn-outline-primary">Tindak Lanjut</a></td>
            </tr>
        @empty
            <tr><td colspan="6">Tidak ada kendala yang cocok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $issues->links() }}
@endsection
