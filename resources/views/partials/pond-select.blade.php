@php $current = old('pond_id', $selected ?? ($ponds->count() === 1 ? $ponds->first()->pond_id : null)); @endphp
<div class="field">
    <label for="pond_id" class="form-label">Tambak <span class="req" aria-hidden="true">*</span></label>
    <select id="pond_id" name="pond_id" required class="form-select @error('pond_id') is-invalid @enderror" @error('pond_id') aria-describedby="pond_id-error" @enderror>
        <option value="">Pilih tambak</option>
        @foreach ($ponds as $p)
            <option value="{{ $p->pond_id }}" @selected((string) $current === (string) $p->pond_id)>{{ $p->pond_name ?: $p->pond_code }} ({{ $p->pond_code }})</option>
        @endforeach
    </select>
    @error('pond_id')<div id="pond_id-error" class="invalid-feedback d-flex" role="alert">{{ $message }}</div>@enderror
</div>
