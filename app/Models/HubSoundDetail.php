<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubSoundDetail extends Model
{
    protected $fillable = [
        'noc_instance_id', 'location_id', 'screen_id',
        'model', 'serial_number', 'cat1700_serial_number',
        'software', 'bypass', 'power_supply', 'aes_status',
        'alert', 'screen_number', 'synced_at',
    ];

    protected $casts = ['synced_at' => 'datetime'];

    public function screen(): BelongsTo   { return $this->belongsTo(HubScreen::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
