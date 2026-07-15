<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubSoundError extends Model {
    protected $fillable = ['noc_instance_id','location_id','alarm_id','date_saved','severity','title','clearable','hardware','screen','message','recommended_action','device_sub_type','device_sub_type_model','device_sub_type_title','sound_ip','display_message','synced_at'];
    protected $casts = ['synced_at' => 'datetime'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
