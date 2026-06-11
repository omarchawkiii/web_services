<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubServerDetail extends Model
{
    protected $fillable = [
        'noc_instance_id', 'location_id', 'screen_id',
        'screen_number', 'show_title', 'serial_number',
        'main_software_version', 'main_firmware_version',
        'bundle_version', 'certificat_date', 'synced_at',
    ];

    protected $casts = ['synced_at' => 'datetime'];

    public function screen(): BelongsTo   { return $this->belongsTo(HubScreen::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
