<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubStorageError extends Model {
    protected $fillable = ['noc_instance_id','location_id','server_name','message','recommended_action','storage_generale_status','projector_brand','projector_ip','projector_model','sound_brand','screen_model','display_message','synced_at'];
    protected $casts = ['synced_at' => 'datetime'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
