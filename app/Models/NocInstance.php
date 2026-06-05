<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NocInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_key',
        'name',
        'url',
        'admin_username',
        'admin_password',
        'sanctum_token',
        'token_expires_at',
        'is_active',
        'sync_status',
        'last_sync_at',
        'notes',
    ];

    protected $casts = [
        'admin_password'   => 'encrypted',
        'sanctum_token'    => 'encrypted',
        'is_active'        => 'boolean',
        'token_expires_at' => 'datetime',
        'last_sync_at'     => 'datetime',
    ];

    protected $hidden = [
        'admin_password',
        'sanctum_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (NocInstance $noc) {
            if (empty($noc->api_key)) {
                $noc->api_key = Str::random(48);
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function getStatusColorClass(): string
    {
        return match($this->sync_status) {
            'online'  => 'bg-green-100 text-green-800',
            'offline' => 'bg-red-100 text-red-800',
            'syncing' => 'bg-blue-100 text-blue-800',
            default   => 'bg-gray-100 text-gray-800',
        };
    }

    public function getBaseUrl(): string
    {
        return rtrim($this->url, '/');
    }
}
