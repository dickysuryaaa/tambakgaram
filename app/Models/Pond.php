<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pond extends Model
{
    protected $primaryKey = 'pond_id';

    protected $fillable = [
        'pond_code', 'pond_name', 'member_id', 'village', 'district', 'regency',
        'latitude', 'longitude', 'area_m2', 'operational_status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'area_m2' => 'integer',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function productionCycles(): HasMany
    {
        return $this->hasMany(ProductionCycle::class, 'pond_id', 'pond_id');
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class, 'pond_id', 'pond_id');
    }

    public function waterObservations(): HasMany
    {
        return $this->hasMany(WaterObservation::class, 'pond_id', 'pond_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'pond_id', 'pond_id');
    }

    /** Batasi query ke tambak milik anggota; koordinator_admin melihat semua. */
    public function scopeVisibleTo($query, Member $member)
    {
        return $member->isKoordinatorAdmin() ? $query : $query->where('member_id', $member->member_id);
    }
}
