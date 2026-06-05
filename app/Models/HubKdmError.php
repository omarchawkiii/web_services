<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubKdmError extends Model {
    protected $fillable = ['noc_instance_id','location_id','cpl_id','annotation_text','details','server_name','date_time','synced_at'];
    protected $casts = ['date_time' => 'datetime', 'synced_at' => 'datetime'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
