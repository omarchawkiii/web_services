<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubRaidAlert extends Model {
    protected $fillable = ['noc_instance_id','location_id','count_alerts','alerts','synced_at'];
    protected $casts = ['alerts' => 'array', 'synced_at' => 'datetime'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
