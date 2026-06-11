<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HubLightHistory;
use App\Models\HubPlayback;
use App\Models\HubProjectorDetail;
use App\Models\HubServerDetail;
use App\Models\HubServerSmart;
use App\Models\HubSoundDetail;
use App\Models\HubStorageDevice;
use App\Models\Location;
use App\Models\NocInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HubPlaybackController extends Controller
{
    public function index(Request $request): View
    {
        $nocInstances = NocInstance::where('is_active', true)->orderBy('name')->get();
        $locations    = Location::orderBy('name')->get();

        $query = HubPlayback::with(['nocInstance', 'location', 'screen'])
            ->orderBy('location_id');

        if ($request->filled('noc')) {
            $query->where('noc_instance_id', $request->noc);
        }
        if ($request->filled('location')) {
            $query->where('location_id', $request->location);
        }
        if ($request->filled('status')) {
            $query->where('playback_status', $request->status);
        }

        $playbacks = $query->get()->groupBy('location_id');

        return view('admin.hub-playback.index', compact('playbacks', 'nocInstances', 'locations'));
    }

    public function detail(int $id): JsonResponse
    {
        $playback = HubPlayback::with(['screen', 'location', 'nocInstance'])->findOrFail($id);
        $screenId = $playback->screen_id;

        return response()->json([
            'playback'        => $playback,
            'screen'          => $playback->screen,
            'location'        => $playback->location,
            'server_detail'   => HubServerDetail::where('screen_id', $screenId)->first(),
            'storage_devices' => HubStorageDevice::where('screen_id', $screenId)->get(),
            'server_smarts'   => HubServerSmart::where('screen_id', $screenId)->get(),
            'projector_detail'=> HubProjectorDetail::where('screen_id', $screenId)->first(),
            'light_histories' => HubLightHistory::where('screen_id', $screenId)->orderBy('index_lamp')->get(),
            'sound_detail'    => HubSoundDetail::where('screen_id', $screenId)->first(),
        ]);
    }
}
