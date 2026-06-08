<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HubErrorSummary;
use App\Models\HubKdmError;
use App\Models\HubPlayback;
use App\Models\HubProjectorError;
use App\Models\HubRaidAlert;
use App\Models\HubSchedule;
use App\Models\HubScreen;
use App\Models\HubServerAlarm;
use App\Models\HubServerError;
use App\Models\HubSoundError;
use App\Models\HubStorageError;
use App\Models\HubSyncLog;
use App\Models\Location;
use App\Models\NocInstance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NocSyncController extends Controller
{
    // ── Auth ─────────────────────────────────────────────────────────────────

    private function authenticateNoc(Request $request): ?NocInstance
    {
        $token = $request->bearerToken();
        if (!$token) return null;
        return NocInstance::where('api_key', $token)->where('is_active', true)->first();
    }

    private function resolveOrCreateLocation(NocInstance $noc, int $nocLocationId, array $locationData = []): ?Location
    {
        return Location::firstOrCreate(
            ['noc_instance_id' => $noc->id, 'noc_location_id' => $nocLocationId],
            [
                'name'       => $locationData['name']       ?? 'Location ' . $nocLocationId,
                'city'       => $locationData['city']       ?? null,
                'country'    => $locationData['country']    ?? null,
                'state'      => $locationData['state']      ?? null,
                'company'    => $locationData['company']    ?? null,
                'tms_system' => $locationData['tms_system'] ?? null,
            ]
        );
    }

    private function resolveOrCreateScreen(NocInstance $noc, Location $location, int $nocScreenId, array $screenData = []): HubScreen
    {
        // location_id est dans la clé : deux locations peuvent avoir le même noc_screen_id
        return HubScreen::updateOrCreate(
            [
                'noc_instance_id' => $noc->id,
                'location_id'     => $location->id,
                'noc_screen_id'   => $nocScreenId,
            ],
            [
                'screen_name'   => $screenData['screen_name']  ?? ('Screen ' . $nocScreenId),
                'screen_number' => $screenData['screen_number'] ?? null,
                'screen_model'  => $screenData['screenModel']  ?? $screenData['screen_model'] ?? null,
                'synced_at'     => now(),
            ]
        );
    }

    // ── Locations ─────────────────────────────────────────────────────────────

    /**
     * POST /api/noc/locations/sync
     */
    public function syncLocations(Request $request): JsonResponse
    {
        $noc = $this->authenticateNoc($request);
        if (!$noc) return response()->json(['message' => 'Invalid API key.'], 401);

        $payload = $request->validate([
            'locations'                   => 'required|array',
            'locations.*.noc_location_id' => 'required|integer',
            'locations.*.name'            => 'required|string|max:255',
            'locations.*.city'            => 'nullable|string|max:255',
            'locations.*.country'         => 'nullable|string|max:255',
            'locations.*.state'           => 'nullable|string|max:255',
            'locations.*.company'         => 'nullable|string|max:255',
            'locations.*.tms_system'      => 'nullable|string|max:255',
        ]);

        $synced = 0;
        foreach ($payload['locations'] as $loc) {
            Location::updateOrCreate(
                ['noc_instance_id' => $noc->id, 'noc_location_id' => $loc['noc_location_id']],
                [
                    'name'       => $loc['name'],
                    'city'       => $loc['city']       ?? null,
                    'country'    => $loc['country']    ?? null,
                    'state'      => $loc['state']      ?? null,
                    'company'    => $loc['company']    ?? null,
                    'tms_system' => $loc['tms_system'] ?? null,
                ]
            );
            $synced++;
        }

        return response()->json(['message' => "{$synced} location(s) synced.", 'synced' => $synced]);
    }

    // ── Users ─────────────────────────────────────────────────────────────────

    /**
     * POST /api/noc/users/sync
     */
    public function syncUsers(Request $request): JsonResponse
    {
        $noc = $this->authenticateNoc($request);
        if (!$noc) return response()->json(['message' => 'Invalid API key.'], 401);

        $payload = $request->validate([
            'users'                      => 'required|array',
            'users.*.name'               => 'required|string|max:255',
            'users.*.last_name'          => 'nullable|string|max:255',
            'users.*.username'           => 'nullable|string|max:255',
            'users.*.email'              => 'required|email',
            'users.*.role'               => 'required|integer|in:1,2,3',
            'users.*.is_active'          => 'nullable|boolean',
            'users.*.noc_location_ids'   => 'nullable|array',
            'users.*.noc_location_ids.*' => 'integer',
        ]);

        $synced = $skipped = 0;

        foreach ($payload['users'] as $userData) {
            $existing = User::where('email', $userData['email'])->where('noc_instance_id', $noc->id)->first();
            $attributes = [
                'name'            => $userData['name'],
                'last_name'       => $userData['last_name'] ?? null,
                'username'        => $userData['username']  ?? null,
                'role'            => $userData['role'],
                'is_active'       => $userData['is_active'] ?? true,
                'noc_instance_id' => $noc->id,
            ];

            if ($existing) {
                $existing->update($attributes);
                $user = $existing;
                $synced++;
            } else {
                if (User::where('email', $userData['email'])->exists()) { $skipped++; continue; }
                $user = User::create(array_merge($attributes, ['email' => $userData['email'], 'password' => Str::random(32)]));
                $synced++;
            }

            if (!empty($userData['noc_location_ids'])) {
                $locationIds = Location::where('noc_instance_id', $noc->id)
                    ->whereIn('noc_location_id', $userData['noc_location_ids'])
                    ->pluck('id')->toArray();
                $user->locations()->sync($locationIds);
            }
        }

        $noc->update(['last_sync_at' => now(), 'sync_status' => 'online']);
        return response()->json(['message' => "Sync: {$synced} synced, {$skipped} skipped.", 'synced' => $synced, 'skipped' => $skipped]);
    }

    // ── Playback ──────────────────────────────────────────────────────────────

    /**
     * POST /api/noc/playback/sync
     * NOC pushes its playback data to the hub.
     *
     * Expected payload:
     * {
     *   "playbacks": [
     *     {
     *       "noc_location_id": 3,
     *       "location": { "name": "...", "city": "...", ... },
     *       "noc_screen_id": 7,
     *       "screen": { "screen_name": "Screen 2", "screen_number": 2, "screenModel": "..." },
     *       "playback_status": "Play",
     *       "spl_title": "...",
     *       ... (all other playback fields)
     *     }
     *   ]
     * }
     */
    public function syncPlayback(Request $request): JsonResponse
    {
        $noc = $this->authenticateNoc($request);
        if (!$noc) return response()->json(['message' => 'Invalid API key.'], 401);

        $started = now();
        $items   = $request->input('playbacks', []);

        if (empty($items)) {
            return response()->json(['message' => 'No playback data received.', 'synced' => 0]);
        }

        $synced = 0;

        foreach ($items as $pb) {
            $nocLocationId = $pb['noc_location_id'] ?? null;
            $nocScreenId   = $pb['noc_screen_id']   ?? null;
            if (!$nocLocationId || !$nocScreenId) continue;

            $location = $this->resolveOrCreateLocation($noc, $nocLocationId, $pb['location'] ?? []);
            $screen   = $this->resolveOrCreateScreen($noc, $location, $nocScreenId, $pb['screen'] ?? []);

            HubPlayback::updateOrCreate(
                ['noc_instance_id' => $noc->id, 'screen_id' => $screen->id],
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
                    'security_manager'            => $pb['securityManager']             ?? $pb['security_manager'] ?? null,
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

        $noc->update(['last_sync_at' => now(), 'sync_status' => 'online']);
        HubSyncLog::create(['noc_instance_id' => $noc->id, 'sync_type' => 'playback', 'status' => 'success', 'records_synced' => $synced, 'started_at' => $started, 'completed_at' => now()]);

        return response()->json(['message' => "{$synced} playback(s) synced.", 'synced' => $synced]);
    }

    // ── Schedules ─────────────────────────────────────────────────────────────

    /**
     * POST /api/noc/schedules/sync
     */
    public function syncSchedules(Request $request): JsonResponse
    {
        $noc = $this->authenticateNoc($request);
        if (!$noc) return response()->json(['message' => 'Invalid API key.'], 401);

        $started = now();
        $items   = $request->input('schedules', []);
        $synced  = 0;

        foreach ($items as $s) {
            $nocLocationId = $s['noc_location_id'] ?? null;
            if (!$nocLocationId) continue;

            $location = $this->resolveOrCreateLocation($noc, $nocLocationId, $s['location'] ?? []);

            $screen = null;
            if (!empty($s['noc_screen_id'])) {
                $screen = $this->resolveOrCreateScreen($noc, $location, $s['noc_screen_id'], $s['screen'] ?? []);
            }

            HubSchedule::updateOrCreate(
                ['noc_instance_id' => $noc->id, 'noc_schedule_id' => $s['noc_schedule_id']],
                [
                    'location_id'    => $location->id,
                    'screen_id'      => $screen?->id,
                    'display_title'  => $s['display_title']  ?? null,
                    'type'           => $s['type']           ?? null,
                    'date_start'     => $s['date_start'],
                    'date_end'       => $s['date_end']       ?? null,
                    'status'         => $s['status']         ?? 'unlinked',
                    'cpls'           => is_numeric($s['cpls'] ?? null) ? (int) $s['cpls'] : 0,
                    'kdm'            => is_numeric($s['kdm']  ?? null) ? (int) $s['kdm']  : 0,
                    'kdm_notes'      => $s['kdm_notes']      ?? null,
                    'list_cpl_notes' => $s['list_cpl_notes'] ?? null,
                    'uuid_spl'       => $s['uuid_spl']       ?? null,
                    'synced_at'      => now(),
                ]
            );
            $synced++;
        }

        $noc->update(['last_sync_at' => now(), 'sync_status' => 'online']);
        HubSyncLog::create(['noc_instance_id' => $noc->id, 'sync_type' => 'schedules', 'status' => 'success', 'records_synced' => $synced, 'started_at' => $started, 'completed_at' => now()]);

        return response()->json(['message' => "{$synced} schedule(s) synced.", 'synced' => $synced]);
    }

    // ── Errors ────────────────────────────────────────────────────────────────

    /**
     * POST /api/noc/errors/sync
     * Receives all error types in one payload.
     */
    public function syncErrors(Request $request): JsonResponse
    {
        $noc = $this->authenticateNoc($request);
        if (!$noc) return response()->json(['message' => 'Invalid API key.'], 401);

        $started = now();
        $synced  = 0;

        // ── Summaries ──────────────────────────────────────────────────────
        foreach ($request->input('summaries', []) as $s) {
            $loc = $this->resolveOrCreateLocation($noc, $s['noc_location_id'], $s['location'] ?? []);
            HubErrorSummary::updateOrCreate(
                ['noc_instance_id' => $noc->id, 'location_id' => $loc->id],
                [
                    'kdm_errors'          => $s['kdm_errors']          ?? 0,
                    'nbr_sound_alert'     => $s['nbr_sound_alert']     ?? 0,
                    'nbr_projector_alert' => $s['nbr_projector_alert'] ?? 0,
                    'nbr_server_alert'    => $s['nbr_server_alert']    ?? 0,
                    'nbr_storage_errors'  => $s['nbr_storage_errors']  ?? 0,
                    'synced_at'           => now(),
                ]
            );
            $synced++;
        }

        // ── KDM errors ─────────────────────────────────────────────────────
        if ($request->has('kdm_errors')) {
            HubKdmError::where('noc_instance_id', $noc->id)->delete();
            foreach ($request->input('kdm_errors', []) as $e) {
                $loc = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                HubKdmError::create(['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'cpl_id' => $e['cpl_id'] ?? null, 'annotation_text' => $e['annotationText'] ?? $e['annotation_text'] ?? null, 'details' => $e['details'] ?? null, 'server_name' => $e['serverName'] ?? $e['server_name'] ?? null, 'date_time' => $e['date_time'] ?? null, 'synced_at' => now()]);
                $synced++;
            }
        }

        // ── Server errors ──────────────────────────────────────────────────
        if ($request->has('server_errors')) {
            HubServerError::where('noc_instance_id', $noc->id)->delete();
            foreach ($request->input('server_errors', []) as $e) {
                $loc = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                HubServerError::create(['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'event_id' => $e['eventId'] ?? null, 'date' => $e['date'] ?? null, 'class' => $e['class'] ?? null, 'type' => $e['type'] ?? null, 'sub_type' => $e['subType'] ?? null, 'criticity' => $e['criticity'] ?? null, 'error_code' => $e['errorCode'] ?? null, 'server_name' => $e['serverName'] ?? null, 'synced_at' => now()]);
                $synced++;
            }
        }

        // ── Projector errors ───────────────────────────────────────────────
        if ($request->has('projector_errors')) {
            HubProjectorError::where('noc_instance_id', $noc->id)->delete();
            foreach ($request->input('projector_errors', []) as $e) {
                $loc = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                HubProjectorError::create(['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'title' => $e['title'] ?? null, 'time_saved' => $e['time_saved'] ?? null, 'code' => $e['code'] ?? null, 'severity' => $e['severity'] ?? null, 'message' => $e['message'] ?? null, 'server_name' => $e['serverName'] ?? null, 'synced_at' => now()]);
                $synced++;
            }
        }

        // ── Sound errors ───────────────────────────────────────────────────
        if ($request->has('sound_errors')) {
            HubSoundError::where('noc_instance_id', $noc->id)->delete();
            foreach ($request->input('sound_errors', []) as $e) {
                $loc = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                HubSoundError::create(['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'alarm_id' => $e['alarm_id'] ?? null, 'date_saved' => $e['date_saved'] ?? null, 'severity' => $e['severity'] ?? null, 'title' => $e['title'] ?? null, 'clearable' => $e['clearable'] ?? null, 'hardware' => $e['hardware'] ?? null, 'screen' => $e['screen'] ?? null, 'synced_at' => now()]);
                $synced++;
            }
        }

        // ── Storage errors ─────────────────────────────────────────────────
        if ($request->has('storage_errors')) {
            HubStorageError::where('noc_instance_id', $noc->id)->delete();
            foreach ($request->input('storage_errors', []) as $e) {
                $loc = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                HubStorageError::create(['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'server_name' => $e['serverName'] ?? $e['server_name'] ?? null, 'synced_at' => now()]);
                $synced++;
            }
        }

        // ── RAID alerts ────────────────────────────────────────────────────
        foreach ($request->input('raid_alerts', []) as $e) {
            $loc = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
            HubRaidAlert::updateOrCreate(
                ['noc_instance_id' => $noc->id, 'location_id' => $loc->id],
                ['count_alerts' => $e['count_alerts'] ?? 0, 'alerts' => $e['alerts'] ?? null, 'synced_at' => now()]
            );
            $synced++;
        }

        // ── Server alarms ──────────────────────────────────────────────────
        if ($request->has('server_alarms')) {
            HubServerAlarm::where('noc_instance_id', $noc->id)->delete();
            foreach ($request->input('server_alarms', []) as $e) {
                $loc    = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                $screen = null;
                if (!empty($e['noc_screen_id'])) {
                    $screen = HubScreen::where('noc_instance_id', $noc->id)->where('noc_screen_id', $e['noc_screen_id'])->first();
                }
                HubServerAlarm::create(['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'screen_id' => $screen?->id, 'alarm_working_state' => $e['alarm_working_state'] ?? null, 'index_alarm' => $e['index_alarm'] ?? null, 'title' => $e['title'] ?? null, 'synced_at' => now()]);
                $synced++;
            }
        }

        $noc->update(['last_sync_at' => now(), 'sync_status' => 'online']);
        HubSyncLog::create(['noc_instance_id' => $noc->id, 'sync_type' => 'errors', 'status' => 'success', 'records_synced' => $synced, 'started_at' => $started, 'completed_at' => now()]);

        return response()->json(['message' => "{$synced} error record(s) synced.", 'synced' => $synced]);
    }
}
