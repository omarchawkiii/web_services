<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubUnifiedError extends Model
{
    protected $fillable = [
        'noc_instance_id',
        'location_id',
        'device_type',
        'external_key',
        'message',
        'screen_name',
        'screen_number',
        'device_ip',
        'display_message',
        'recommended_action',
        'severity',
        'brand',
        'model',
        'serial_number',
        'date_error',
        'device_sub_type',
        'device_sub_type_ip',
        'device_sub_type_model',
        'device_sub_type_title',
        'movie_title',
        'spl_title',
        'session_start',
        'synced_at',
    ];

    protected $casts = ['synced_at' => 'datetime'];

    public function nocInstance(): BelongsTo
    {
        return $this->belongsTo(NocInstance::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
