<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'noc_instance_id',
        'noc_location_id',
        'name',
        'city',
        'country',
        'state',
        'company',
        'tms_system',
    ];

    public function nocInstance(): BelongsTo
    {
        return $this->belongsTo(NocInstance::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'location_user')->withTimestamps();
    }
}
