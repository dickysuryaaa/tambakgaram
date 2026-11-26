<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionActivity extends Model
{
    protected $primaryKey = 'activity_id';

    protected $fillable = ['cycle_id', 'activity_type', 'activity_date', 'description', 'recorded_by'];

    protected function casts(): array
    {
        return ['activity_date' => 'date'];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(ProductionCycle::class, 'cycle_id', 'cycle_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'recorded_by', 'member_id');
    }
}
