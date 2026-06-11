<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubLightHistory extends Model
{
    protected $fillable = [
        'noc_instance_id', 'location_id', 'screen_id',
        'hours', 'index_lamp', 'power_range', 'rotation_state',
        'serial_number', 'type', 'date_lamp', 'synced_at',
    ];

    protected $casts = ['synced_at' => 'datetime'];

    public function screen(): BelongsTo   { return $this->belongsTo(HubScreen::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
