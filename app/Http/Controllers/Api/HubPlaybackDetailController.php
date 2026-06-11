<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HubLightHistory;
use App\Models\HubPlayback;
use App\Models\HubProjectorDetail;
use App\Models\HubServerDetail;
use App\Models\HubServerSmart;
use App\Models\HubSoundDetail;
use App\Models\HubStorageDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HubPlaybackDetailController extends Controller
{
    private function userLocationIds(Request $request): array
    {
        return $request->user()->locations()->pluck('locations.id')->toArray();
    }

    /**
     * GET /api/hub/mobile/playback/{id}/server
     *
     * Returns server detail, storage devices, SMART data and server alarms for a screen.
     */
    public function serverDetail(Request $request, int $id): JsonResponse
    {
        $playback = HubPlayback::with('screen', 'location')->findOrFail($id);

        if (!in_array($playback->location_id, $this->userLocationIds($request))) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $server_detail   = HubServerDetail::where('screen_id', $playback->screen_id)->first();
        $storage_devices = HubStorageDevice::where('screen_id', $playback->screen_id)->get();
        $server_smarts   = HubServerSmart::where('screen_id', $playback->screen_id)->get();

        return response()->json(compact(
            'playback',
            'server_detail',
            'storage_devices',
            'server_smarts'
        ));
    }

    /**
     * GET /api/hub/mobile/playback/{id}/projector
     *
     * Returns projector detail and lamp replacement history for a screen.
     */
    public function projectorDetail(Request $request, int $id): JsonResponse
    {
        $playback = HubPlayback::with('screen', 'location')->findOrFail($id);

        if (!in_array($playback->location_id, $this->userLocationIds($request))) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $projector_detail = HubProjectorDetail::where('screen_id', $playback->screen_id)->first();
        $light_histories  = HubLightHistory::where('screen_id', $playback->screen_id)
                                ->orderBy('index_lamp', 'ASC')
                                ->get();

        return response()->json(compact(
            'playback',
            'projector_detail',
            'light_histories'
        ));
    }

    /**
     * GET /api/hub/mobile/playback/{id}/sound
     *
     * Returns sound/audio detail for a screen.
     */
    public function soundDetail(Request $request, int $id): JsonResponse
    {
        $playback = HubPlayback::with('screen', 'location')->findOrFail($id);

        if (!in_array($playback->location_id, $this->userLocationIds($request))) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $sound_detail = HubSoundDetail::where('screen_id', $playback->screen_id)->first();

        return response()->json(compact('playback', 'sound_detail'));
    }
}
