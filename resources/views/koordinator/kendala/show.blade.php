@extends('layouts.koordinator')
@section('title', 'Tindak Lanjut Kendala')
@php
    use App\Support\Label;
    $statusMeta = ['terbuka' => ['bi-exclamation-octagon', 'bad'], 'diproses' => ['bi-hourglass-split', 'warn'], 'selesai' => ['bi-check-circle', 'ok']];
    $statusOptions = collect(Label::ISSUES)->map(fn ($v, $k) => ['label' => $v[0], 'icon' => $statusMeta[$k][0] ?? 'bi-circle', 'tone' => $statusMeta[$k][1] ?? 'mute'])->all();
    $prioMeta = ['rendah' => ['bi-hourglass', 'ok'], 'sedang' => ['bi-hourglass-split', 'warn'], 'tinggi' => ['bi-lightning-charge', 'bad']];
    $prioOptions = collect(Label::PRIORITIES)->map(fn ($v, $k) => ['label' => $v, 'icon' => $prioMeta[$k][0] ?? 'bi-circle', 'tone' => $prioMeta[$k][1] ?? 'mute'])->all();
@endphp

@section('content')
<a href="{{ route('k.issues.index') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> Daftar Kendala</a>
<div class="page-head">
    <div>
        <h1>Tindak Lanjut Kendala</h1>
        <p class="lead-text">{{ $issue->pond->pond_name ?: $issue->pond->pond_code }} ({{ $issue->pond->pond_code }})</p>
    </div>
</div>

<div class="grid-2">
    <form method="POST" action="{{ route('k.issues.update', $issue) }}" class="card" novalidate>
        <div class="card-body">
        @csrf @method('PUT')
        <h2 class="h5 section-title mb-3"><i class="bi bi-pencil-square" aria-hidden="true"></i>Perbarui Tindak Lanjut</h2>

        @include('partials.choice', ['name' => 'status', 'legend' => 'Status tindak lanjut', 'cols' => 'cols-3', 'options' => $statusOptions, 'selected' => old('status', $issue->status)])
        @include('partials.choice', ['name' => 'priority', 'legend' => 'Seberapa mendesak', 'cols' => 'cols-3', 'options' => $prioOptions, 'selected' => old('priority', $issue->priority)])

        <div class="field">
            <label for="follow_up_notes" class="form-label">Catatan tindak lanjut</label>
            <textarea id="follow_up_notes" name="follow_up_notes" rows="4" maxlength="2000" class="form-control @error('follow_up_notes') is-invalid @enderror" aria-describedby="fu-help">{{ old('follow_up_notes', $issue->follow_up_notes) }}</textarea>
            <div id="fu-help" class="form-text">Contoh: Menunggu suku cadang pompa, dijadwalkan Kamis.</div>
            @include('partials.field-error', ['name' => 'follow_up_notes'])
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg" data-busy="Menyimpan..."><i class="bi bi-check-lg" aria-hidden="true"></i> Simpan</button>
            <a href="{{ route('k.issues.index') }}" class="btn btn-outline-secondary btn-lg">Batal</a>
        </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                <h2 class="h5 section-title mb-0"><i class="bi bi-exclamation-triangle" aria-hidden="true" style="color: var(--bad-ink);"></i>{{ Label::CATEGORIES[$issue->category] ?? $issue->category }}</h2>
                @include('partials.status', ['label' => Label::issue($issue->status)[0], 'color' => Label::issue($issue->status)[1]])
            </div>
            <dl class="kv" style="grid-template-columns: 1fr;">
                <dt>Tambak</dt>
                <dd><a href="{{ route('k.ponds.show', $issue->pond) }}">{{ $issue->pond->pond_name ?: $issue->pond->pond_code }} ({{ $issue->pond->pond_code }})</a></dd>
                <dt>Pemilik tambak</dt><dd>{{ $issue->pond->member->name }}</dd>
                <dt>Dilaporkan oleh</dt><dd>{{ $issue->reporter->name }}, {{ $issue->reported_at->translatedFormat('j F Y, H.i') }}</dd>
                <dt>Seberapa mendesak</dt><dd>{{ Label::PRIORITIES[$issue->priority] ?? $issue->priority }}</dd>
                <dt>Cerita kendala</dt><dd>{{ $issue->description }}</dd>
                @if ($issue->resolved_at)
                    <dt>Selesai pada</dt><dd>{{ $issue->resolved_at->translatedFormat('j F Y, H.i') }}</dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
