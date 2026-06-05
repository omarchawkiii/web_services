<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubSchedule extends Model {
    protected $fillable = [
        'noc_instance_id','location_id','screen_id','noc_schedule_id',
        'display_title','type','date_start','date_end','status','cpls','kdm','kdm_notes','list_cpl_notes','uuid_spl','synced_at',
    ];
    protected $casts = ['date_start' => 'datetime', 'date_end' => 'datetime', 'kdm_notes' => 'array', 'synced_at' => 'datetime'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function screen(): BelongsTo { return $this->belongsTo(HubScreen::class, 'screen_id'); }
}
