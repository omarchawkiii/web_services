<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'last_name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'noc_instance_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function nocInstance(): BelongsTo
    {
        return $this->belongsTo(NocInstance::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'location_user')->withTimestamps();
    }

    public function getRoleLabelAttribute(): string
    {
        return match((int) $this->role) {
            1       => 'Admin',
            2       => 'Manager',
            3       => 'Cinema Staff',
            default => 'Unknown',
        };
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match((int) $this->role) {
            1       => 'bg-blue-100 text-blue-800',
            2       => 'bg-green-100 text-green-800',
            3       => 'bg-gray-100 text-gray-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function isAdmin(): bool
    {
        return $this->role === 1;
    }

    public function isLocalAdmin(): bool
    {
        return $this->noc_instance_id === null;
    }
}
