<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Member extends Authenticatable
{
    use Notifiable;

    public const ROLE_PETAMBAK = 'petambak';
    public const ROLE_KOORDINATOR_ADMIN = 'koordinator_admin';

    protected $primaryKey = 'member_id';

    protected $fillable = ['name', 'username', 'password', 'phone', 'role', 'status'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function isKoordinatorAdmin(): bool
    {
        return $this->role === self::ROLE_KOORDINATOR_ADMIN;
    }

    public function isPetambak(): bool
    {
        return $this->role === self::ROLE_PETAMBAK;
    }

    public function ponds(): HasMany
    {
        return $this->hasMany(Pond::class, 'member_id', 'member_id');
    }

    public function issuesReported(): HasMany
    {
        return $this->hasMany(Issue::class, 'reported_by', 'member_id');
    }
}
