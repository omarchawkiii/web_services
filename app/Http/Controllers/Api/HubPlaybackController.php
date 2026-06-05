<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HubPlayback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HubPlaybackController extends Controller
{
    private function userLocationIds(Request $request): array
    {
        return $request->user()->locations()->pluck('locations.id')->toArray();
    }

    /**
     * GET /api/hub/mobile/playback
     * All playbacks for the user's locations, grouped across all NOCs.
     */
    public function index(Request $request): JsonResponse
    {
        $locationIds = $this->userLocationIds($request);

        $playbacks = HubPlayback::with(['nocInstance', 'location', 'screen'])
            ->whereIn('location_id', $locationIds)
            ->orderBy('location_id')
            ->get()
            ->map(fn($p) => array_merge($p->toArray(), [
                'noc_name'    => $p->nocInstance?->name,
                'location_name' => $p->location?->name,
                'screen_name'   => $p->screen?->screen_name,
                'screen_model'  => $p->screen?->screen_model,
            ]));

        return response()->json(compact('playbacks'));
    }

    /**
     * GET /api/hub/mobile/playback/{id}
     * Single playback detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $playback = HubPlayback::with(['nocInstance', 'location', 'screen'])->findOrFail($id);

        if (!in_array($playback->location_id, $this->userLocationIds($request))) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json([
            'playback'      => $playback,
            'screen_info'   => $playback->screen,
            'location_info' => $playback->location,
            'noc'           => ['id' => $playback->nocInstance?->id, 'name' => $playback->nocInstance?->name],
        ]);
    }
}
