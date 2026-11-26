@extends('layouts.petambak')
@section('title', 'Catat Panen')

@section('content')
@include('partials.form-head', ['heading' => 'Catat Panen', 'lead' => 'Isi berat garam yang dipanen hari ini.'])

@if ($ponds->isEmpty())
    @include('partials.no-ponds')
@else
<form method="POST" action="{{ route('catat.panen') }}" class="card form-card" novalidate>
    <div class="card-body">
    @csrf
    @include('partials.pond-select')
    @include('partials.cycle-select')

    <div class="field">
        <label for="harvest_date" class="form-label">Tanggal panen <span class="req" aria-hidden="true">*</span></label>
        <input id="harvest_date" name="harvest_date" type="date" required max="{{ now()->toDateString() }}"
               value="{{ old('harvest_date', now()->toDateString()) }}" class="form-control @error('harvest_date') is-invalid @enderror">
        @include('partials.field-error', ['name' => 'harvest_date'])
    </div>

    <div class="row g-3 field">
        <div class="col-12 col-sm-7">
            <label for="quantity" class="form-label">Jumlah panen <span class="req" aria-hidden="true">*</span></label>
            <input id="quantity" name="quantity" type="text" inputmode="decimal" required value="{{ old('quantity') }}"
                   class="form-control form-control-lg tabular @error('quantity') is-invalid @enderror" aria-describedby="quantity-help">
            @include('partials.field-error', ['name' => 'quantity'])
        </div>
        <fieldset class="col-12 col-sm-5">
            <legend class="form-label fs-6">Satuan <span class="req" aria-hidden="true">*</span></legend>
            <div class="segmented">
                <label><input type="radio" name="unit" value="kg" @checked(old('unit', 'kg') === 'kg')><span>kg</span></label>
                <label><input type="radio" name="unit" value="ton" @checked(old('unit') === 'ton')><span>ton</span></label>
            </div>
            @include('partials.field-error', ['name' => 'unit'])
        </fieldset>
        <div class="col-12 mt-2">
            <div id="quantity-help" class="form-text">Contoh: 1500 (kg) atau 1,5 (ton). Pilih satuan sesuai angka yang Anda isi.</div>
            <div id="konversi" class="fw-bold mt-1 tabular" style="color: var(--sea-deep);" aria-live="polite"></div>
        </div>
    </div>

    <div class="field">
        <label for="notes" class="form-label">Catatan (boleh dikosongkan)</label>
        <textarea id="notes" name="notes" rows="3" maxlength="1000" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
        @include('partials.field-error', ['name' => 'notes'])
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg" data-busy="Menyimpan..."><i class="bi bi-check2-circle" aria-hidden="true"></i> Simpan Catatan Panen</button>
        <a href="{{ route('catat') }}" class="btn btn-outline-secondary btn-lg">Batal</a>
    </div>
    </div>
</form>
<script>
(function () {
    var q = document.getElementById('quantity'), out = document.getElementById('konversi');
    var units = document.querySelectorAll('input[name="unit"]');
    function unit() { var c = document.querySelector('input[name="unit"]:checked'); return c ? c.value : 'kg'; }
    function show() {
        var v = parseFloat(String(q.value).replace(',', '.'));
        if (isNaN(v) || v <= 0 || unit() !== 'ton') { out.textContent = ''; return; }
        out.textContent = 'Sama dengan ' + (v * 1000).toLocaleString('id-ID') + ' kg';
    }
    q.addEventListener('input', show);
    units.forEach(function (u) { u.addEventListener('change', show); });
    show();
})();
</script>
@endif
@endsection
