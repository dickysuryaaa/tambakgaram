{{-- Tombol filter dan catatan tanggal. Pilihan cepat periode bila $presets true. --}}
<div class="col-12 col-md-auto d-flex gap-2 flex-wrap">
    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel" aria-hidden="true"></i> Tampilkan</button>
    <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Hapus Filter</a>
</div>
@if (! empty($presets))
    @php
        $today = \Illuminate\Support\Carbon::today();
        $keep = request()->except(['dari', 'sampai', 'page']);
        $quick = [
            '30 hari terakhir' => [$today->copy()->subDays(29), $today],
            '90 hari terakhir' => [$today->copy()->subDays(89), $today],
            'Tahun ini' => [$today->copy()->startOfYear(), $today],
        ];
    @endphp
    <div class="col-12 preset-row" role="group" aria-label="Pilihan cepat periode">
        @foreach ($quick as $name => [$a, $b])
            @php $on = $period->from?->toDateString() === $a->toDateString() && $period->to?->toDateString() === $b->toDateString(); @endphp
            <a class="btn {{ $on ? 'btn-primary' : 'btn-outline-secondary' }}" @if ($on) aria-current="true" @endif href="{{ url()->current().'?'.http_build_query($keep + ['dari' => $a->toDateString(), 'sampai' => $b->toDateString()]) }}">{{ $name }}</a>
        @endforeach
    </div>
@endif
@if ($period->notice)
    <div class="col-12"><div class="alert alert-warning mb-0" role="status"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i> {{ $period->notice }}</div></div>
@endif
