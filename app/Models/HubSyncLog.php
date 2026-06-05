<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubSyncLog extends Model {
    protected $fillable = ['noc_instance_id','sync_type','status','records_synced','error_message','started_at','completed_at'];
    protected $casts = ['started_at' => 'datetime', 'completed_at' => 'datetime'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
}
