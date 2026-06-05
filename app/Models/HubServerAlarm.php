<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubServerAlarm extends Model {
    protected $fillable = ['noc_instance_id','location_id','screen_id','alarm_working_state','index_alarm','title','synced_at'];
    protected $casts = ['synced_at' => 'datetime'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function screen(): BelongsTo { return $this->belongsTo(HubScreen::class, 'screen_id'); }
}
