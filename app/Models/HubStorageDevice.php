<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubStorageDevice extends Model
{
    protected $fillable = [
        'noc_instance_id', 'location_id', 'screen_id',
        'bus', 'capacity', 'index_storage', 'model',
        'serial_number', 'working_state', 'title', 'type', 'version', 'synced_at',
    ];

    protected $casts = ['synced_at' => 'datetime'];

    protected $appends = ['bus_label', 'type_label', 'working_state_label'];

    private static array $busMap = [
        1 => 'Unknown', 2 => 'IDE', 3 => 'USB',
        4 => 'SATA',    5 => 'SAS', 6 => 'Firewire',
    ];

    private static array $typeMap = [
        1 => 'Unknown', 2 => 'CF', 3 => 'SSD', 4 => 'HDD',
    ];

    private static array $stateMap = [
        1 => 'Undefined', 2 => 'Not Applicable', 3 => 'Normal',
        4 => 'Warning',   5 => 'Error',
    ];

    public function getBusLabelAttribute(): ?string
    {
        return $this->bus !== null ? (self::$busMap[$this->bus] ?? (string) $this->bus) : null;
    }

    public function getTypeLabelAttribute(): ?string
    {
        return $this->type !== null ? (self::$typeMap[$this->type] ?? (string) $this->type) : null;
    }

    public function getWorkingStateLabelAttribute(): ?string
    {
        return $this->working_state !== null ? (self::$stateMap[$this->working_state] ?? (string) $this->working_state) : null;
    }

    public function screen(): BelongsTo   { return $this->belongsTo(HubScreen::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
}
