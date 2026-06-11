<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubProjectorDetail extends Model
{
    protected $fillable = [
        'noc_instance_id', 'location_id', 'screen_id',
        'system_model', 'system_serial_number', 'system_manufacture_date',
        'system_software_version', 'operating_hours',
        'power_status', 'shutter_status', 'light_status',
        'light_model', 'light_serial', 'light_hours', 'synced_at',
    ];

    protected $casts = ['synced_at' => 'datetime'];

    public function screen(): BelongsTo   { return $this->belongsTo(HubScreen::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
