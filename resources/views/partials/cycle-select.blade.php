{{-- Pilihan siklus; skrip kecil menyaring sesuai tambak terpilih. Tanpa JavaScript semua siklus tetap tampil dengan nama tambaknya. --}}
@php use App\Support\Label; @endphp
<div class="field">
    <label for="cycle_id" class="form-label">Siklus produksi <span class="req" aria-hidden="true">*</span></label>
    <select id="cycle_id" name="cycle_id" required class="form-select @error('cycle_id') is-invalid @enderror">
        <option value="">Pilih siklus</option>
        @foreach ($cycles as $cy)
            <option value="{{ $cy->cycle_id }}" data-pond="{{ $cy->pond_id }}" @selected((string) old('cycle_id') === (string) $cy->cycle_id)>
                {{ $cy->pond->pond_code }}: mulai {{ $cy->start_date->translatedFormat('j F Y') }} ({{ Label::cycle($cy->cycle_status)[0] }})
            </option>
        @endforeach
    </select>
    @error('cycle_id')<div id="cycle_id-error" class="invalid-feedback d-flex" role="alert">{{ $message }}</div>@enderror
    @if ($cycles->isEmpty())
        <div class="form-text">Belum ada siklus. <a href="{{ route('catat.siklus') }}">Buat siklus baru</a> terlebih dahulu.</div>
    @else
        <div class="form-text">Siklus adalah satu putaran produksi garam, dari persiapan sampai panen.</div>
    @endif
</div>
<script>
(function () {
    var pond = document.getElementById('pond_id'), cycle = document.getElementById('cycle_id');
    if (!pond || !cycle) return;
    var all = Array.prototype.slice.call(cycle.options).filter(function (o) { return o.value; });
    function filter() {
        var keep = cycle.value;
        all.forEach(function (o) { o.hidden = o.disabled = pond.value && o.dataset.pond !== pond.value; });
        var sel = cycle.options[cycle.selectedIndex];
        if (sel && sel.value && sel.disabled) cycle.value = '';
        // Pilih otomatis bila hanya ada satu siklus untuk tambak ini.
        var visible = all.filter(function (o) { return !o.disabled; });
        if (!cycle.value && visible.length === 1 && pond.value) cycle.value = visible[0].value;
    }
    pond.addEventListener('change', filter); filter();
})();
</script>
