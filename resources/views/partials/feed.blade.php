<div class="feed">
@forelse ($items as $it)
    <article class="card record record-{{ $it['type'] }}">
        <span class="record-ic"><i class="bi {{ $it['icon'] }}" aria-hidden="true"></i></span>
        <div class="record-main">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                <h3 class="record-title">{{ $it['title'] }}</h3>
                @if ($it['badge'])
                    @include('partials.status', ['label' => $it['badge'][0], 'color' => $it['badge'][1]])
                @endif
            </div>
            <div class="tabular">{{ $it['summary'] }}</div>
            @if (! empty($it['note']))
                <div class="text-muted">{{ $it['note'] }}</div>
            @endif
            <div class="record-meta">
                <span><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $it['pond']->pond_name ?: $it['pond']->pond_code }} ({{ $it['pond']->pond_code }})</span>
                <span><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $it['when']->translatedFormat($it['date_only'] ? 'j F Y' : 'j F Y, H.i') }}</span>
            </div>
        </div>
    </article>
@empty
    <div class="card empty-state">
        <i class="bi bi-journal-text" aria-hidden="true"></i>
        <p class="fw-semibold">Belum ada catatan.</p>
    </div>
@endforelse
</div>
