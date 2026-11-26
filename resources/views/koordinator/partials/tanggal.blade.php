<div class="col-6 col-md-3">
    <label for="dari" class="form-label">Dari tanggal</label>
    <input type="date" id="dari" name="dari" class="form-control" value="{{ $period->from?->toDateString() }}">
</div>
<div class="col-6 col-md-3">
    <label for="sampai" class="form-label">Sampai tanggal</label>
    <input type="date" id="sampai" name="sampai" class="form-control" value="{{ $period->to?->toDateString() }}">
</div>
