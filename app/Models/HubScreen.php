<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HubScreen extends Model {
    protected $fillable = ['noc_instance_id','location_id','noc_screen_id','screen_name','screen_number','screen_model','synced_at'];
    protected $casts = ['synced_at' => 'datetime'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function playback(): HasOne { return $this->hasOne(HubPlayback::class, 'screen_id'); }
    public function alarms(): HasMany { return $this->hasMany(HubServerAlarm::class, 'screen_id'); }
}
