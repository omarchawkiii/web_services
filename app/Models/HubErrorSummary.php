<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubErrorSummary extends Model {
    protected $fillable = ['noc_instance_id','location_id','kdm_errors','nbr_sound_alert','nbr_projector_alert','nbr_server_alert','nbr_storage_errors','nbr_tms_alert','synced_at'];
    protected $casts = ['synced_at' => 'datetime'];

    public function getTotalAttribute(): int {
        return $this->kdm_errors + $this->nbr_sound_alert + $this->nbr_projector_alert + $this->nbr_server_alert + $this->nbr_storage_errors + $this->nbr_tms_alert;
    }
    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
