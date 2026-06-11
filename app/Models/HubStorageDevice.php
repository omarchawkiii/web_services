<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubStorageDevice extends Model
{
    protected $fillable = [
        'noc_instance_id', 'location_id', 'screen_id',
        'bus', 'capacity', 'index_storage', 'model',
        'serial_number', 'working_state', 'title', 'type', 'version', 'synced_at',
    ];

    protected $casts = ['synced_at' => 'datetime'];

    public function screen(): BelongsTo   { return $this->belongsTo(HubScreen::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
