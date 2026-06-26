<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubTmsError extends Model
{
    protected $fillable = [
        'noc_instance_id',
        'location_id',
        'id_tms_error',
        'title',
        'code',
        'severity',
        'message',
        'time_saved',
        'id_screen',
        'ip_projector',
        'display_message',
        'recommended_action',
        'device_sub_type',
        'device_sub_type_ip',
        'device_sub_type_model',
        'device_sub_type_title',
        'server_name',
        'screen_model',
        'projector_ip',
        'sound_ip',
        'number',
        'projector_brand',
        'projector_model',
        'sound_brand',
        'sound_model',
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
