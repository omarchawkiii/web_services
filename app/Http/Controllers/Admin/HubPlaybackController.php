<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HubPlayback;
use App\Models\NocInstance;
use App\Models\Location;
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
}
