{{-- Pilihan tambak dan anggota untuk filter. Butuh $ponds, $members, $f. --}}
<div class="col-12 col-md-3">
    <label for="tambak" class="form-label">Tambak</label>
    <select id="tambak" name="tambak" class="form-select">
        <option value="">Semua tambak</option>
        @foreach ($ponds as $p)
            <option value="{{ $p->pond_id }}" @selected((string) ($f['tambak'] ?? '') === (string) $p->pond_id)>{{ $p->pond_code }}{{ $p->pond_name ? ' - '.$p->pond_name : '' }}</option>
        @endforeach
    </select>
</div>
<div class="col-12 col-md-3">
    <label for="anggota" class="form-label">Anggota (pemilik)</label>
    <select id="anggota" name="anggota" class="form-select">
        <option value="">Semua anggota</option>
        @foreach ($members as $m)
            <option value="{{ $m->member_id }}" @selected((string) ($f['anggota'] ?? '') === (string) $m->member_id)>{{ $m->name }}</option>
        @endforeach
    </select>
</div>
