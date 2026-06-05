<?php

namespace App\Jobs;

use App\Models\HubPlayback;
use App\Models\HubScreen;
use App\Models\HubSyncLog;
use App\Models\Location;
use App\Models\NocInstance;
use App\Services\NocApiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncNocPlayback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public readonly NocInstance $noc) {}

    public function handle(): void
    {
        $started = now();
        $client  = new NocApiClient($this->noc);
        $data    = $client->get('/api/mobile/playback');

        if (!$data || empty($data['playbacks'])) {
            $this->log('playback', 'error', 0, 'No data returned from ' . $this->noc->url, $started);
            $this->noc->update(['sync_status' => 'offline']);
            return;
        }

        $synced = 0;

        foreach ($data['playbacks'] as $pb) {
            // ── 1. Resolve or auto-create the hub Location ────────────────────
            $nocLocationId = $pb['location_id']
                          ?? $pb['location']['id']
                          ?? null;

            if (!$nocLocationId) continue;

            $location = Location::firstOrCreate(
                [
                    'noc_instance_id' => $this->noc->id,
                    'noc_location_id' => $nocLocationId,
                ],
                [
                    'name'       => $pb['location']['name']    ?? 'Location ' . $nocLocationId,
                    'city'       => $pb['location']['city']    ?? null,
                    'country'    => $pb['location']['country'] ?? null,
                    'state'      => $pb['location']['state']   ?? null,
                    'company'    => $pb['location']['company'] ?? null,
                    'tms_system' => $pb['location']['tms_system'] ?? null,
                ]
            );

            // ── 2. Resolve or auto-create the hub Screen ──────────────────────
            $nocScreenId = $pb['screen_id']
                        ?? $pb['screen']['id']
                        ?? null;

            if (!$nocScreenId) continue;

            $screen = HubScreen::updateOrCreate(
                [
                    'noc_instance_id' => $this->noc->id,
                    'noc_screen_id'   => $nocScreenId,
                ],
                [
                    'location_id'   => $location->id,
                    'screen_name'   => $pb['screen']['screen_name']  ?? ('Screen ' . $nocScreenId),
                    'screen_number' => $pb['screen']['screen_number'] ?? null,
                    'screen_model'  => $pb['screen']['screenModel']  ?? null,
                    'synced_at'     => now(),
                ]
            );

            // ── 3. Upsert the playback record ─────────────────────────────────
            HubPlayback::updateOrCreate(
                ['noc_instance_id' => $this->noc->id, 'screen_id' => $screen->id],
                [
                    'location_id'                 => $location->id,
                    'playback_status'             => $pb['playback_status']             ?? 'Unknown',
                    'spl_title'                   => $pb['spl_title']                   ?? null,
                    'cpl_title'                   => $pb['cpl_title']                   ?? null,
                    'elapsed_runtime'             => $pb['elapsed_runtime']             ?? null,
                    'remaining_runtime'           => $pb['remaining_runtime']           ?? null,
                    'progress_bar'                => $pb['progress_bar']                ?? null,
                    'projector_status'            => $pb['projector_status']            ?? null,
                    'projector_lamp_stat'         => $pb['projector_lamp_stat']         ?? null,
                    'lamp_status'                 => $pb['lamp_status']                 ?? null,
                    'dowser_status'               => $pb['dowser_status']               ?? null,
                    'ip_management_server_status' => $pb['ip_management_server_status'] ?? null,
                    'storage_generale_status'     => $pb['storage_generale_status']     ?? null,
                    'schedule_mode'               => $pb['schedule_mode']               ?? null,
                    'security_manager'            => $pb['securityManager']             ?? null,
                    'soap_session'                => $pb['soap_session']                ?? null,
                    'sound_model'                 => $pb['sound_model']                 ?? null,
                    'ip_sound_status'             => $pb['ip_sound_status']             ?? null,
                    'mute_status'                 => $pb['mute_status']                 ?? null,
                    'fader_status'                => $pb['fader_status']                ?? null,
                    'format_status'               => $pb['format_status']               ?? null,
                    'bit_stream'                  => $pb['bit_stream']                  ?? null,
                    'synced_at'                   => now(),
                ]
            );

            $synced++;
        }

        $this->noc->update(['last_sync_at' => now(), 'sync_status' => 'online']);
        $this->log('playback', 'success', $synced, null, $started);
    }

    private function log(string $type, string $status, int $count, ?string $error, $started): void
    {
        HubSyncLog::create([
            'noc_instance_id' => $this->noc->id,
            'sync_type'       => $type,
            'status'          => $status,
            'records_synced'  => $count,
            'error_message'   => $error,
            'started_at'      => $started,
            'completed_at'    => now(),
        ]);
    }
}
