<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Harvest extends Model
{
    protected $primaryKey = 'harvest_id';

    protected $fillable = ['cycle_id', 'pond_id', 'harvest_date', 'quantity', 'unit', 'notes', 'recorded_by'];

    protected function casts(): array
    {
        return ['harvest_date' => 'date', 'quantity' => 'decimal:2'];
    }

    /** Jumlah panen dalam kg (konversi dari ton saat rekap; data asli tidak diubah). */
    public function getQuantityKgAttribute(): float
    {
        return (float) $this->quantity * ($this->unit === 'ton' ? 1000 : 1);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(ProductionCycle::class, 'cycle_id', 'cycle_id');
    }

    public function pond(): BelongsTo
    {
        return $this->belongsTo(Pond::class, 'pond_id', 'pond_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'recorded_by', 'member_id');
    }
}
