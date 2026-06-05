<?php
namespace App\Jobs;

use App\Models\HubSchedule;
use App\Models\HubScreen;
use App\Models\HubSyncLog;
use App\Models\Location;
use App\Models\NocInstance;
use App\Services\NocApiClient;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncNocSchedules implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 45;

    public function __construct(public readonly NocInstance $noc) {}

    public function handle(): void
    {
        $started = now();
        $client  = new NocApiClient($this->noc);

        // Fetch today + tomorrow schedules
        $dates  = [Carbon::today()->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')];
        $synced = 0;

        foreach ($dates as $date) {
            $data = $client->get('/api/mobile/schedules?date=' . $date);
            if (!$data || empty($data['schedules'])) continue;

            foreach ($data['schedules'] as $s) {
                $location = Location::where('noc_instance_id', $this->noc->id)
                    ->where('noc_location_id', $s['location']['id'] ?? null)
                    ->first();
                if (!$location) continue;

                $screen = HubScreen::where('noc_instance_id', $this->noc->id)
                    ->where('noc_screen_id', $s['screen']['id'] ?? null)
                    ->first();

                HubSchedule::updateOrCreate(
                    ['noc_instance_id' => $this->noc->id, 'noc_schedule_id' => $s['scheduleId'] ?? $s['id']],
                    [
                        'location_id'    => $location->id,
                        'screen_id'      => $screen?->id,
                        'display_title'  => $s['display_title'] ?? null,
                        'type'           => $s['type'] ?? null,
                        'date_start'     => $s['date_start'],
                        'date_end'       => $s['date_end'] ?? null,
                        'status'         => $s['status'] ?? 'unlinked',
                        'cpls'           => $s['cpls'] ?? 0,
                        'kdm'            => $s['kdm'] ?? 0,
                        'kdm_notes'      => $s['kdm_notes'] ?? null,
                        'list_cpl_notes' => $s['list_cpl_notes'] ?? null,
                        'uuid_spl'       => $s['uuid_spl'] ?? null,
                        'synced_at'      => now(),
                    ]
                );
                $synced++;
            }
        }

        $this->noc->update(['last_sync_at' => now()]);
        HubSyncLog::create([
            'noc_instance_id' => $this->noc->id,
            'sync_type'       => 'schedules',
            'status'          => 'success',
            'records_synced'  => $synced,
            'started_at'      => $started,
            'completed_at'    => now(),
        ]);
    }
}
