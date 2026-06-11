<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubServerSmart extends Model
{
    protected $fillable = [
        'noc_instance_id', 'location_id', 'screen_id',
        'overall_health', 'power_on_hours', 'raw_read_error',
        'reallocated_event', 'reallocated_sector_count', 'smart_support',
        'scan_date', 'seek_error_rate', 'temperature', 'udma_error', 'synced_at',
    ];

    protected $casts = ['synced_at' => 'datetime'];

    public function screen(): BelongsTo   { return $this->belongsTo(HubScreen::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
