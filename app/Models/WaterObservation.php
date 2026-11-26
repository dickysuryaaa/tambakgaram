<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterObservation extends Model
{
    protected $primaryKey = 'observation_id';

    protected $fillable = [
        'pond_id', 'observed_at', 'salinity_value', 'temperature_c', 'ph_value',
        'instrument', 'notes', 'recorded_by',
    ];

    // Cast desimal tetap mempertahankan NULL (tidak diubah menjadi 0).
    protected function casts(): array
    {
        return [
            'observed_at' => 'datetime',
            'salinity_value' => 'decimal:2',
            'temperature_c' => 'decimal:2',
            'ph_value' => 'decimal:2',
        ];
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
