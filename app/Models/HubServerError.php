<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubServerError extends Model {
    protected $fillable = ['noc_instance_id','location_id','event_id','date','class','type','sub_type','criticity','error_code','server_name','message','recommended_action','synced_at'];
    protected $casts = ['date' => 'datetime', 'synced_at' => 'datetime'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
