<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubPlayback extends Model {
    protected $fillable = [
        'noc_instance_id','location_id','screen_id',
        'playback_status','spl_title','cpl_title','elapsed_runtime','remaining_runtime','progress_bar',
        'projector_status','projector_lamp_stat','lamp_status','dowser_status',
        'ip_management_server_status','storage_generale_status','schedule_mode','security_manager','soap_session',
        'sound_model','ip_sound_status','mute_status','fader_status','format_status','bit_stream','synced_at',
    ];
    protected $casts = ['synced_at' => 'datetime', 'progress_bar' => 'float'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function screen(): BelongsTo { return $this->belongsTo(HubScreen::class, 'screen_id'); }
}
