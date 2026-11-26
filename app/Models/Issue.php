<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Issue extends Model
{
    protected $primaryKey = 'issue_id';

    protected $fillable = [
        'pond_id', 'reported_by', 'reported_at', 'category', 'description',
        'priority', 'status', 'follow_up_notes', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['reported_at' => 'datetime', 'resolved_at' => 'datetime'];
    }

    public function pond(): BelongsTo
    {
        return $this->belongsTo(Pond::class, 'pond_id', 'pond_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'reported_by', 'member_id');
    }
}
