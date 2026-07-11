<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubProjectorError extends Model {
    protected $fillable = ['noc_instance_id','location_id','title','time_saved','code','severity','message','recommended_action','server_name','ip_projector','projector_brand','projector_model','display_message','synced_at'];
    protected $casts = ['time_saved' => 'datetime', 'synced_at' => 'datetime'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
