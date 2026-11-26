<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionCycle extends Model
{
    protected $primaryKey = 'cycle_id';

    protected $fillable = ['pond_id', 'start_date', 'end_date', 'cycle_status'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function pond(): BelongsTo
    {
        return $this->belongsTo(Pond::class, 'pond_id', 'pond_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProductionActivity::class, 'cycle_id', 'cycle_id');
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class, 'cycle_id', 'cycle_id');
    }
}
