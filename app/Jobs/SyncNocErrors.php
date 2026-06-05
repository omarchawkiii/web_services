<?php
namespace App\Jobs;

use App\Models\HubErrorSummary;
use App\Models\HubKdmError;
use App\Models\HubProjectorError;
use App\Models\HubRaidAlert;
use App\Models\HubServerAlarm;
use App\Models\HubServerError;
use App\Models\HubSoundError;
use App\Models\HubStorageError;
use App\Models\HubSyncLog;
use App\Models\Location;
use App\Models\NocInstance;
use App\Services\NocApiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncNocErrors implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(public readonly NocInstance $noc) {}

    public function handle(): void
    {
        $started = now();
        $client  = new NocApiClient($this->noc);
        $synced  = 0;

        // ── Error Summary ────────────────────────────────────────────────────
        $summary = $client->get('/api/mobile/errors/summary');
        if ($summary) {
            // Summary is global — store per NOC (no location breakdown in summary endpoint)
            // We find the first location of this NOC to anchor it
            $locations = Location::where('noc_instance_id', $this->noc->id)->get();
            foreach ($locations as $loc) {
                HubErrorSummary::updateOrCreate(
                    ['noc_instance_id' => $this->noc->id, 'location_id' => $loc->id],
                    [
                        'kdm_errors'          => $summary['kdm_errors'] ?? 0,
                        'nbr_sound_alert'     => $summary['nbr_sound_alert'] ?? 0,
                        'nbr_projector_alert' => $summary['nbr_projector_alert'] ?? 0,
                        'nbr_server_alert'    => $summary['nbr_server_alert'] ?? 0,
                        'nbr_storage_errors'  => $summary['nbr_storage_errors'] ?? 0,
                        'synced_at'           => now(),
                    ]
                );
                break; // one summary per NOC is enough
            }
            $synced++;
        }

        // ── KDM Errors ───────────────────────────────────────────────────────
        $kdmData = $client->get('/api/mobile/errors/kdm');
        if ($kdmData && !empty($kdmData['kdms_errors_list'])) {
            HubKdmError::where('noc_instance_id', $this->noc->id)->delete();
            foreach ($kdmData['kdms_errors_list'] as $item) {
                $location = $this->resolveLocation($item['location_id'] ?? null);
                if (!$location) continue;
                HubKdmError::create([
                    'noc_instance_id' => $this->noc->id,
                    'location_id'     => $location->id,
                    'cpl_id'          => $item['cpl_id'] ?? null,
                    'annotation_text' => $item['annotationText'] ?? null,
                    'details'         => $item['details'] ?? null,
                    'server_name'     => $item['serverName'] ?? null,
                    'date_time'       => $item['date_time'] ?? null,
                    'synced_at'       => now(),
                ]);
                $synced++;
            }
        }

        // ── Server Errors ────────────────────────────────────────────────────
        $serverData = $client->get('/api/mobile/errors/server');
        if ($serverData && !empty($serverData['server_errors_list'])) {
            HubServerError::where('noc_instance_id', $this->noc->id)->delete();
            foreach ($serverData['server_errors_list'] as $item) {
                $location = $this->resolveLocation($item['location_id'] ?? null);
                if (!$location) continue;
                HubServerError::create([
                    'noc_instance_id' => $this->noc->id,
                    'location_id'     => $location->id,
                    'event_id'        => $item['eventId'] ?? null,
                    'date'            => $item['date'] ?? null,
                    'class'           => $item['class'] ?? null,
                    'type'            => $item['type'] ?? null,
                    'sub_type'        => $item['subType'] ?? null,
                    'criticity'       => $item['criticity'] ?? null,
                    'error_code'      => $item['errorCode'] ?? null,
                    'server_name'     => $item['serverName'] ?? null,
                    'synced_at'       => now(),
                ]);
                $synced++;
            }
        }

        // ── Projector Errors ─────────────────────────────────────────────────
        $projData = $client->get('/api/mobile/errors/projector');
        if ($projData && !empty($projData['projector_errors_list'])) {
            HubProjectorError::where('noc_instance_id', $this->noc->id)->delete();
            foreach ($projData['projector_errors_list'] as $item) {
                $location = $this->resolveLocation($item['location_id'] ?? null);
                if (!$location) continue;
                HubProjectorError::create([
                    'noc_instance_id' => $this->noc->id,
                    'location_id'     => $location->id,
                    'title'           => $item['title'] ?? null,
                    'time_saved'      => $item['time_saved'] ?? null,
                    'code'            => $item['code'] ?? null,
                    'severity'        => $item['severity'] ?? null,
                    'message'         => $item['message'] ?? null,
                    'server_name'     => $item['serverName'] ?? null,
                    'synced_at'       => now(),
                ]);
                $synced++;
            }
        }

        // ── Sound Errors ─────────────────────────────────────────────────────
        $soundData = $client->get('/api/mobile/errors/sound');
        if ($soundData && !empty($soundData['sounds_errors_list'])) {
            HubSoundError::where('noc_instance_id', $this->noc->id)->delete();
            foreach ($soundData['sounds_errors_list'] as $item) {
                $location = $this->resolveLocation($item['location_id'] ?? null);
                if (!$location) continue;
                HubSoundError::create([
                    'noc_instance_id' => $this->noc->id,
                    'location_id'     => $location->id,
                    'alarm_id'        => $item['alarm_id'] ?? null,
                    'date_saved'      => $item['date_saved'] ?? null,
                    'severity'        => $item['severity'] ?? null,
                    'title'           => $item['title'] ?? null,
                    'clearable'       => $item['clearable'] ?? null,
                    'hardware'        => $item['hardware'] ?? null,
                    'screen'          => $item['screen'] ?? null,
                    'synced_at'       => now(),
                ]);
                $synced++;
            }
        }

        // ── Storage Errors ───────────────────────────────────────────────────
        $storageData = $client->get('/api/mobile/errors/storage');
        if ($storageData && !empty($storageData['storage_errors_list'])) {
            HubStorageError::where('noc_instance_id', $this->noc->id)->delete();
            foreach ($storageData['storage_errors_list'] as $item) {
                $location = $this->resolveLocation($item['location_id'] ?? null);
                if (!$location) continue;
                HubStorageError::create([
                    'noc_instance_id' => $this->noc->id,
                    'location_id'     => $location->id,
                    'server_name'     => $item['serverName'] ?? $item['screen'] ?? null,
                    'synced_at'       => now(),
                ]);
                $synced++;
            }
        }

        // ── RAID Alerts ──────────────────────────────────────────────────────
        $raidData = $client->get('/api/mobile/errors/raid');
        if ($raidData && !empty($raidData['alerts'])) {
            foreach ($raidData['alerts'] as $item) {
                $location = $this->resolveLocation($item['location']['id'] ?? null);
                if (!$location) continue;
                HubRaidAlert::updateOrCreate(
                    ['noc_instance_id' => $this->noc->id, 'location_id' => $location->id],
                    ['count_alerts' => $item['count'] ?? 0, 'alerts' => $item['alerts'] ?? null, 'synced_at' => now()]
                );
                $synced++;
            }
        }

        // ── Server Alarms ────────────────────────────────────────────────────
        $alarmsData = $client->get('/api/mobile/errors/server-alarms');
        if ($alarmsData && !empty($alarmsData['alarms'])) {
            HubServerAlarm::where('noc_instance_id', $this->noc->id)->delete();
            foreach ($alarmsData['alarms'] as $item) {
                $location = $this->resolveLocation($item['location_id'] ?? null);
                if (!$location) continue;
                HubServerAlarm::create([
                    'noc_instance_id'     => $this->noc->id,
                    'location_id'         => $location->id,
                    'alarm_working_state' => $item['Alarm_Working_State'] ?? null,
                    'index_alarm'         => $item['Index_alarm'] ?? null,
                    'title'               => $item['title'] ?? null,
                    'synced_at'           => now(),
                ]);
                $synced++;
            }
        }

        HubSyncLog::create([
            'noc_instance_id' => $this->noc->id,
            'sync_type'       => 'errors',
            'status'          => 'success',
            'records_synced'  => $synced,
            'started_at'      => $started,
            'completed_at'    => now(),
        ]);
    }

    private function resolveLocation(?int $nocLocationId): ?Location
    {
        if (!$nocLocationId) return null;
        return Location::where('noc_instance_id', $this->noc->id)
            ->where('noc_location_id', $nocLocationId)
            ->first();
    }
}
