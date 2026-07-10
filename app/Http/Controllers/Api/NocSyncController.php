<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HubErrorSummary;
use App\Models\HubKdmError;
use App\Models\HubLightHistory;
use App\Models\HubPlayback;
use App\Models\HubProjectorDetail;
use App\Models\HubProjectorError;
use App\Models\HubRaidAlert;
use App\Models\HubSchedule;
use App\Models\HubScreen;
use App\Models\HubServerAlarm;
use App\Models\HubServerDetail;
use App\Models\HubServerError;
use App\Models\HubServerSmart;
use App\Models\HubSoundDetail;
use App\Models\HubSoundError;
use App\Models\HubStorageDevice;
use App\Models\HubStorageError;
use App\Models\HubTmsError;
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
                'address'    => $locationData['address']    ?? null,
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
            'locations.*.address'         => 'nullable|string|max:500',
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
                    'address'    => $loc['address']    ?? null,
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
            'users.*.password_hash'      => 'nullable|string',
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
                if (!empty($userData['password_hash'])) {
                    \DB::table('users')->where('id', $existing->id)->update(['password' => $userData['password_hash']]);
                }
                $user = $existing;
                $synced++;
            } else {
                if (User::where('email', $userData['email'])->exists()) { $skipped++; continue; }
                $user = User::create(array_merge($attributes, ['email' => $userData['email'], 'password' => Str::random(32)]));
                if (!empty($userData['password_hash'])) {
                    \DB::table('users')->where('id', $user->id)->update(['password' => $userData['password_hash']]);
                }
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
     * POST /api/noc/schedules/clear
     * @deprecated Remplacé par finalize — conservé pour compatibilité.
     */
    public function clearSchedules(Request $request): JsonResponse
    {
        $noc = $this->authenticateNoc($request);
        if (!$noc) return response()->json(['message' => 'Invalid API key.'], 401);

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $deleted = HubSchedule::where('noc_instance_id', $noc->id)
            ->where('date_start', '>=', $dateFrom . ' 00:00:00')
            ->where('date_start', '<=', $dateTo   . ' 23:59:59')
            ->delete();

        return response()->json(['message' => "{$deleted} schedule(s) cleared."]);
    }

    /**
     * POST /api/noc/schedules/finalize
     * Appelé après tous les chunks de sync.
     * Supprime les schedules futurs qui ne sont plus dans le NOC (absents de keep_ids).
     * Les anciens schedules (date_start < now) ne sont jamais touchés.
     */
    public function finalizeSchedules(Request $request): JsonResponse
    {
        $noc = $this->authenticateNoc($request);
        if (!$noc) return response()->json(['message' => 'Invalid API key.'], 401);

        $keepIds = $request->input('keep_ids', []);

        $deleted = HubSchedule::where('noc_instance_id', $noc->id)
            ->where('date_start', '>=', now())
            ->whereNotIn('noc_schedule_id', $keepIds)
            ->delete();

        return response()->json(['message' => "{$deleted} stale future schedule(s) removed.", 'deleted' => $deleted]);
    }

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

    // ── Playback Details ──────────────────────────────────────────────────────

    /**
     * POST /api/noc/playback-details/sync
     * NOC pushes server/projector/sound/storage/smart/lamp detail per screen.
     */
    public function syncPlaybackDetails(Request $request): JsonResponse
    {
        $noc = $this->authenticateNoc($request);
        if (!$noc) return response()->json(['message' => 'Invalid API key.'], 401);

        $started = now();
        $synced  = 0;

        // ── Server details ─────────────────────────────────────────────────
        foreach ($request->input('server_details', []) as $d) {
            $nocLocationId = $d['noc_location_id'] ?? null;
            $nocScreenId   = $d['noc_screen_id']   ?? null;
            if (!$nocLocationId || !$nocScreenId) continue;

            $location = $this->resolveOrCreateLocation($noc, $nocLocationId, $d['location'] ?? []);
            $screen   = $this->resolveOrCreateScreen($noc, $location, $nocScreenId, $d['screen'] ?? []);

            HubServerDetail::updateOrCreate(
                ['noc_instance_id' => $noc->id, 'screen_id' => $screen->id],
                [
                    'location_id'           => $location->id,
                    'screen_number'         => $d['screen_number']          ?? null,
                    'show_title'            => $d['showTitle']               ?? $d['show_title'] ?? null,
                    'serial_number'         => $d['serialNumber']            ?? $d['serial_number'] ?? null,
                    'main_software_version' => $d['mainSoftwareVersion']     ?? $d['main_software_version'] ?? null,
                    'main_firmware_version' => $d['mainFirmwareVersion']     ?? $d['main_firmware_version'] ?? null,
                    'bundle_version'        => $d['bundleVersion']           ?? $d['bundle_version'] ?? null,
                    'certificat_date'       => $d['certificat_date']         ?? null,
                    'synced_at'             => now(),
                ]
            );
            $synced++;
        }

        // ── Projector details ──────────────────────────────────────────────
        foreach ($request->input('projector_details', []) as $d) {
            $nocLocationId = $d['noc_location_id'] ?? null;
            $nocScreenId   = $d['noc_screen_id']   ?? null;
            if (!$nocLocationId || !$nocScreenId) continue;

            $location = $this->resolveOrCreateLocation($noc, $nocLocationId, $d['location'] ?? []);
            $screen   = $this->resolveOrCreateScreen($noc, $location, $nocScreenId, $d['screen'] ?? []);

            HubProjectorDetail::updateOrCreate(
                ['noc_instance_id' => $noc->id, 'screen_id' => $screen->id],
                [
                    'location_id'             => $location->id,
                    'system_model'            => $d['System_Details_Model']            ?? $d['system_model'] ?? null,
                    'system_serial_number'    => $d['System_Details_Serial_Number']    ?? $d['system_serial_number'] ?? null,
                    'system_manufacture_date' => $d['System_Details_Manufacture_Date'] ?? $d['system_manufacture_date'] ?? null,
                    'system_software_version' => $d['System_Details_Software_Version'] ?? $d['system_software_version'] ?? null,
                    'operating_hours'         => $d['System_Status_Operating_Hours']   ?? $d['operating_hours'] ?? null,
                    'power_status'            => $d['System_Status_Power_Status']      ?? $d['power_status'] ?? null,
                    'shutter_status'          => $d['System_Status_Shutter_Status']    ?? $d['shutter_status'] ?? null,
                    'light_status'            => $d['System_Status_Light_Status']      ?? $d['light_status'] ?? null,
                    'light_model'             => $d['Light_Model']                     ?? $d['light_model'] ?? null,
                    'light_serial'            => $d['Light_Serial']                    ?? $d['light_serial'] ?? null,
                    'light_hours'             => $d['Light_Hours']                     ?? $d['light_hours'] ?? null,
                    'synced_at'               => now(),
                ]
            );
            $synced++;
        }

        // ── Sound details ──────────────────────────────────────────────────
        foreach ($request->input('sound_details', []) as $d) {
            $nocLocationId = $d['noc_location_id'] ?? null;
            $nocScreenId   = $d['noc_screen_id']   ?? null;
            if (!$nocLocationId || !$nocScreenId) continue;

            $location = $this->resolveOrCreateLocation($noc, $nocLocationId, $d['location'] ?? []);
            $screen   = $this->resolveOrCreateScreen($noc, $location, $nocScreenId, $d['screen'] ?? []);

            HubSoundDetail::updateOrCreate(
                ['noc_instance_id' => $noc->id, 'screen_id' => $screen->id],
                [
                    'location_id'          => $location->id,
                    'model'                => $d['model']                  ?? null,
                    'serial_number'        => $d['serial_number']          ?? null,
                    'cat1700_serial_number'=> $d['cat_1700serial_number']  ?? $d['cat1700_serial_number'] ?? null,
                    'software'             => $d['software']               ?? null,
                    'bypass'               => $d['bypass']                 ?? null,
                    'power_supply'         => $d['power_supply']           ?? null,
                    'aes_status'           => $d['aes_status']             ?? null,
                    'alert'                => $d['alert']                  ?? null,
                    'screen_number'        => $d['screen_number']          ?? null,
                    'synced_at'            => now(),
                ]
            );
            $synced++;
        }

        // ── Storage devices ────────────────────────────────────────────────
        if ($request->has('storage_devices')) {
            foreach ($request->input('storage_devices', []) as $d) {
                $nocLocationId = $d['noc_location_id'] ?? null;
                $nocScreenId   = $d['noc_screen_id']   ?? null;
                if (!$nocLocationId || !$nocScreenId) continue;

                $location = $this->resolveOrCreateLocation($noc, $nocLocationId, $d['location'] ?? []);
                $screen   = $this->resolveOrCreateScreen($noc, $location, $nocScreenId, $d['screen'] ?? []);

                HubStorageDevice::updateOrCreate(
                    ['noc_instance_id' => $noc->id, 'screen_id' => $screen->id, 'index_storage' => $d['Index_storage'] ?? $d['index_storage'] ?? null],
                    [
                        'location_id'  => $location->id,
                        'bus'          => $d['Bus']                     ?? $d['bus'] ?? null,
                        'capacity'     => $d['Capacity']                ?? $d['capacity'] ?? null,
                        'model'        => $d['Model']                   ?? $d['model'] ?? null,
                        'serial_number'=> $d['Storage_Serial_Number']   ?? $d['serial_number'] ?? null,
                        'working_state'=> $d['Storage_Working_State']   ?? $d['working_state'] ?? null,
                        'title'        => $d['Title']                   ?? $d['title'] ?? null,
                        'type'         => $d['Type']                    ?? $d['type'] ?? null,
                        'version'      => $d['Version_Storage_Device']  ?? $d['version'] ?? null,
                        'synced_at'    => now(),
                    ]
                );
                $synced++;
            }
        }

        // ── Server SMART ───────────────────────────────────────────────────
        if ($request->has('server_smarts')) {
            foreach ($request->input('server_smarts', []) as $d) {
                $nocLocationId = $d['noc_location_id'] ?? null;
                $nocScreenId   = $d['noc_screen_id']   ?? null;
                if (!$nocLocationId || !$nocScreenId) continue;

                $location = $this->resolveOrCreateLocation($noc, $nocLocationId, $d['location'] ?? []);
                $screen   = $this->resolveOrCreateScreen($noc, $location, $nocScreenId, $d['screen'] ?? []);

                HubServerSmart::updateOrCreate(
                    ['noc_instance_id' => $noc->id, 'screen_id' => $screen->id],
                    [
                        'location_id'              => $location->id,
                        'overall_health'           => $d['Overall_Health']            ?? $d['overall_health'] ?? null,
                        'power_on_hours'           => $d['Power_On_Hours']            ?? $d['power_on_hours'] ?? null,
                        'raw_read_error'           => $d['Raw_Read_Error']            ?? $d['raw_read_error'] ?? null,
                        'reallocated_event'        => $d['Reallocated_Event']         ?? $d['reallocated_event'] ?? null,
                        'reallocated_sector_count' => $d['Reallocated_Sector_Count']  ?? $d['reallocated_sector_count'] ?? null,
                        'smart_support'            => $d['SMART_Support']             ?? $d['smart_support'] ?? null,
                        'scan_date'                => $d['Scan_Date']                 ?? $d['scan_date'] ?? null,
                        'seek_error_rate'          => $d['Seek_Error_Rate']           ?? $d['seek_error_rate'] ?? null,
                        'temperature'              => $d['Temperature']               ?? $d['temperature'] ?? null,
                        'udma_error'               => $d['UDMA_Error']               ?? $d['udma_error'] ?? null,
                        'synced_at'                => now(),
                    ]
                );
                $synced++;
            }
        }

        // ── Light histories ────────────────────────────────────────────────
        if ($request->has('light_histories')) {
            foreach ($request->input('light_histories', []) as $d) {
                $nocLocationId = $d['noc_location_id'] ?? null;
                $nocScreenId   = $d['noc_screen_id']   ?? null;
                if (!$nocLocationId || !$nocScreenId) continue;

                $location = $this->resolveOrCreateLocation($noc, $nocLocationId, $d['location'] ?? []);
                $screen   = $this->resolveOrCreateScreen($noc, $location, $nocScreenId, $d['screen'] ?? []);

                HubLightHistory::updateOrCreate(
                    ['noc_instance_id' => $noc->id, 'screen_id' => $screen->id, 'index_lamp' => $d['Index_lamp'] ?? $d['index_lamp'] ?? null],
                    [
                        'location_id'   => $location->id,
                        'hours'         => $d['Hours']          ?? $d['hours'] ?? null,
                        'power_range'   => $d['Power_Range']    ?? $d['power_range'] ?? null,
                        'rotation_state'=> $d['Rotation_State'] ?? $d['rotation_state'] ?? null,
                        'serial_number' => $d['Serial_Number']  ?? $d['serial_number'] ?? null,
                        'type'          => $d['Type']           ?? $d['type'] ?? null,
                        'date_lamp'     => $d['Date_lamp']      ?? $d['date_lamp'] ?? null,
                        'synced_at'     => now(),
                    ]
                );
                $synced++;
            }
        }

        $noc->update(['last_sync_at' => now(), 'sync_status' => 'online']);
        HubSyncLog::create(['noc_instance_id' => $noc->id, 'sync_type' => 'playback_details', 'status' => 'success', 'records_synced' => $synced, 'started_at' => $started, 'completed_at' => now()]);

        return response()->json(['message' => "{$synced} detail record(s) synced.", 'synced' => $synced]);
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
                    'nbr_tms_alert'       => $s['nbr_tms_alert']       ?? 0,
                    'synced_at'           => now(),
                ]
            );
            $synced++;
        }

        // ── KDM errors ─────────────────────────────────────────────────────
        if ($request->has('kdm_errors')) {
            $syncedKdmKeys = [];
            foreach ($request->input('kdm_errors', []) as $e) {
                $loc        = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                $cplId      = $e['cpl_id'] ?? null;
                $serverName = $e['serverName'] ?? $e['server_name'] ?? null;
                HubKdmError::updateOrCreate(
                    ['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'cpl_id' => $cplId, 'server_name' => $serverName],
                    ['annotation_text' => $e['annotationText'] ?? $e['annotation_text'] ?? null, 'details' => $e['details'] ?? null, 'message' => $e['message'] ?? null, 'recommended_action' => $e['recommended_action'] ?? null, 'date_time' => $e['date_time'] ?? null, 'synced_at' => now()]
                );
                $syncedKdmKeys[] = [$loc->id, $cplId, $serverName];
                $synced++;
            }
            if (!empty($syncedKdmKeys)) {
                $placeholders = implode(',', array_fill(0, count($syncedKdmKeys), '(?,?,?)'));
                $bindings     = array_merge(...$syncedKdmKeys);
                HubKdmError::where('noc_instance_id', $noc->id)
                    ->whereRaw("(location_id, cpl_id, server_name) NOT IN ({$placeholders})", $bindings)
                    ->delete();
            }
        }

        // ── Server errors ──────────────────────────────────────────────────
        if ($request->has('server_errors')) {
            foreach ($request->input('server_errors', []) as $e) {
                $loc        = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                $eventId    = $e['id_server_error'] ?? $e['eventId'] ?? null;
                $serverName = $e['serverName'] ?? null;
                HubServerError::updateOrCreate(
                    ['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'event_id' => $eventId, 'server_name' => $serverName],
                    [
                        'date'             => $e['date'] ?? null,
                        'class'            => $e['class'] ?? null,
                        'type'             => $e['type'] ?? null,
                        'sub_type'         => $e['subType'] ?? null,
                        'criticity'        => $e['criticity'] ?? null,
                        'error_code'       => $e['errorCode'] ?? null,
                        'message'          => $e['message'] ?? null,
                        'recommended_action' => $e['recommended_action'] ?? null,
                        'ip_projector'     => $e['ip_projector'] ?? null,
                        'projector_brand'  => $e['projector_brand'] ?? null,
                        'projector_ip'     => $e['projector_ip'] ?? null,
                        'projector_model'  => $e['projector_model'] ?? null,
                        'sound_brand'      => $e['sound_brand'] ?? null,
                        'screen_model'     => $e['screenModel'] ?? $e['screen_model'] ?? null,
                        'display_message'  => $e['display_message'] ?? null,
                        'certificat_date'  => $e['certificat_date'] ?? null,
                        'serial_number'    => $e['serialNumber'] ?? $e['serial_number'] ?? null,
                        'show_title'       => $e['showTitle'] ?? $e['show_title'] ?? null,
                        'synced_at'        => now(),
                    ]
                );
                $synced++;
            }
        }

        // ── Projector errors ───────────────────────────────────────────────
        if ($request->has('projector_errors')) {
            foreach ($request->input('projector_errors', []) as $e) {
                $loc        = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                $code       = $e['code'] ?? null;
                $serverName = $e['serverName'] ?? null;
                HubProjectorError::updateOrCreate(
                    ['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'code' => $code, 'server_name' => $serverName],
                    ['title' => $e['title'] ?? null, 'time_saved' => $e['time_saved'] ?? null, 'severity' => $e['severity'] ?? null, 'message' => $e['message'] ?? null, 'recommended_action' => $e['recommended_action'] ?? null, 'projector_brand' => $e['projector_brand'] ?? null, 'projector_model' => $e['projector_model'] ?? null, 'display_message' => $e['display_message'] ?? null, 'synced_at' => now()]
                );
                $synced++;
            }
        }

        // ── Sound errors ───────────────────────────────────────────────────
        if ($request->has('sound_errors')) {
            foreach ($request->input('sound_errors', []) as $e) {
                $loc     = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                $alarmId = $e['alarm_id'] ?? null;
                HubSoundError::updateOrCreate(
                    ['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'alarm_id' => $alarmId],
                    ['date_saved' => $e['date_saved'] ?? null, 'severity' => $e['severity'] ?? null, 'title' => $e['title'] ?? null, 'clearable' => $e['clearable'] ?? null, 'hardware' => $e['hardware'] ?? null, 'screen' => $e['screen'] ?? null, 'message' => $e['message'] ?? null, 'recommended_action' => $e['recommended_action'] ?? null, 'device_sub_type_model' => $e['device_sub_type_model'] ?? null, 'device_sub_type_title' => $e['device_sub_type_title'] ?? null, 'sound_ip' => $e['sound_ip'] ?? null, 'display_message' => $e['display_message'] ?? null, 'synced_at' => now()]
                );
                $synced++;
            }
        }

        // ── Storage errors ─────────────────────────────────────────────────
        if ($request->has('storage_errors')) {
            foreach ($request->input('storage_errors', []) as $e) {
                $loc        = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                $serverName = $e['serverName'] ?? $e['server_name'] ?? null;
                HubStorageError::updateOrCreate(
                    ['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'server_name' => $serverName],
                    ['message' => $e['message'] ?? null, 'recommended_action' => $e['recommended_action'] ?? null, 'storage_generale_status' => $e['storage_generale_status'] ?? $e['severity'] ?? null, 'projector_brand' => $e['projector_brand'] ?? null, 'projector_ip' => $e['projector_ip'] ?? null, 'projector_model' => $e['projector_model'] ?? null, 'sound_brand' => $e['sound_brand'] ?? null, 'screen_model' => $e['screenModel'] ?? $e['screen_model'] ?? null, 'display_message' => $e['display_message'] ?? null, 'synced_at' => now()]
                );
                $synced++;
            }
        }

        // ── TMS errors ─────────────────────────────────────────────────────
        if ($request->has('tms_errors')) {
            foreach ($request->input('tms_errors', []) as $e) {
                $loc       = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                $idTmsError = $e['id_tms_error'] ?? null;
                HubTmsError::updateOrCreate(
                    ['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'id_tms_error' => $idTmsError],
                    [
                        'title'                 => $e['title']                 ?? null,
                        'code'                  => $e['code']                  ?? null,
                        'severity'              => $e['severity']              ?? null,
                        'message'               => $e['message']               ?? null,
                        'time_saved'            => $e['time_saved']            ?? null,
                        'id_screen'             => $e['id_screen']             ?? null,
                        'ip_projector'          => $e['ip_projector']          ?? null,
                        'display_message'       => $e['display_message']       ?? null,
                        'recommended_action'    => $e['recommended_action']    ?? null,
                        'device_sub_type'       => $e['device_sub_type']       ?? null,
                        'device_sub_type_ip'    => $e['device_sub_type_ip']    ?? null,
                        'device_sub_type_model' => $e['device_sub_type_model'] ?? null,
                        'device_sub_type_title' => $e['device_sub_type_title'] ?? null,
                        'server_name'           => $e['serverName']            ?? null,
                        'screen_model'          => $e['screenModel']           ?? null,
                        'projector_ip'          => $e['projector_ip']          ?? null,
                        'sound_ip'              => $e['sound_ip']              ?? null,
                        'number'                => $e['number']                ?? null,
                        'projector_brand'       => $e['projector_brand']       ?? null,
                        'projector_model'       => $e['projector_model']       ?? null,
                        'sound_brand'           => $e['sound_brand']           ?? null,
                        'sound_model'           => $e['sound_model']           ?? null,
                        'synced_at'             => now(),
                    ]
                );
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
            $syncedIndexes = [];
            foreach ($request->input('server_alarms', []) as $e) {
                $loc        = $this->resolveOrCreateLocation($noc, $e['noc_location_id'], $e['location'] ?? []);
                $screen     = null;
                if (!empty($e['noc_screen_id'])) {
                    $screen = HubScreen::where('noc_instance_id', $noc->id)->where('noc_screen_id', $e['noc_screen_id'])->first();
                }
                $indexAlarm = isset($e['index_alarm']) ? (int) preg_replace('/\D/', '', $e['index_alarm']) : null;
                HubServerAlarm::updateOrCreate(
                    ['noc_instance_id' => $noc->id, 'location_id' => $loc->id, 'index_alarm' => $indexAlarm],
                    ['screen_id' => $screen?->id, 'alarm_working_state' => $e['alarm_working_state'] ?? null, 'title' => $e['title'] ?? null, 'synced_at' => now()]
                );
                if ($indexAlarm !== null) $syncedIndexes[] = $indexAlarm;
                $synced++;
            }
            if (!empty($syncedIndexes)) {
                HubServerAlarm::where('noc_instance_id', $noc->id)->whereNotIn('index_alarm', $syncedIndexes)->delete();
            }
        }

        $noc->update(['last_sync_at' => now(), 'sync_status' => 'online']);
        HubSyncLog::create(['noc_instance_id' => $noc->id, 'sync_type' => 'errors', 'status' => 'success', 'records_synced' => $synced, 'started_at' => $started, 'completed_at' => now()]);

        return response()->json(['message' => "{$synced} error record(s) synced.", 'synced' => $synced]);
    }
}
