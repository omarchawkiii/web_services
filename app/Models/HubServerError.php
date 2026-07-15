<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubServerError extends Model {
    protected $fillable = ['noc_instance_id','location_id','event_id','date','class','type','sub_type','criticity','error_code','server_name','message','recommended_action','ip_projector','projector_brand','projector_ip','projector_model','sound_brand','screen_model','display_message','certificat_date','serial_number','show_title','product_name','synced_at'];
    protected $casts = ['date' => 'datetime', 'synced_at' => 'datetime'];

    public function nocInstance(): BelongsTo { return $this->belongsTo(NocInstance::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
