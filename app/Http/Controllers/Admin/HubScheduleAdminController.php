<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HubSchedule;
use App\Models\HubScreen;
use App\Models\Location;
use App\Models\NocInstance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HubScheduleAdminController extends Controller
{
    public function index(Request $request): View
    {
        $nocInstances = NocInstance::where('is_active', true)->orderBy('name')->get();
        $locations    = Location::orderBy('name')->get();
        $date         = $request->input('date', Carbon::today()->format('Y-m-d'));
        $tab          = $request->input('tab', 'all');

        // 5h AM → 4h59 AM next day
        $start = Carbon::parse($date)->setTime(5, 0, 0);
        $end   = Carbon::parse($date)->addDay()->setTime(5, 0, 0);

        // Screens list filtered by location if selected
        $screensQuery = HubScreen::orderBy('screen_number');
        if ($request->filled('location')) {
            $screensQuery->where('location_id', $request->location);
        } elseif ($request->filled('noc')) {
            $screensQuery->where('noc_instance_id', $request->noc);
        }
        $screens = $screensQuery->get();

        $query = HubSchedule::with(['nocInstance', 'location', 'screen'])
            ->where('date_start', '>=', $start)
            ->where('date_start', '<', $end);

        if ($request->filled('noc')) {
            $query->where('noc_instance_id', $request->noc);
        }
        if ($request->filled('location')) {
            $query->where('location_id', $request->location);
        }
        if ($request->filled('screen')) {
            $query->where('screen_id', $request->screen);
        }

        $query = match($tab) {
            'unlinked'     => $query->where('status', '!=', 'linked'),
            'missing_cpls' => $query->where('status', 'linked')->where('cpls', '!=', 1),
            'missing_kdms' => $query->where('status', 'linked')->where('cpls', 1)->where('kdm', '!=', 1),
            'kdm_expired'  => $query->where('kdm', 2),
            'kdm_expiring' => (function() use ($query) {
                return $query->where('kdm', '!=', 2)->where('kdm_notes', 'like', '%warning%');
            })(),
            default        => $query,
        };

        $schedules = $query->orderBy('date_start')->get();

        // Counts for tab badges
        $baseCount = HubSchedule::where('date_start', '>=', $start)->where('date_start', '<', $end);
        if ($request->filled('noc'))      { $baseCount->where('noc_instance_id', $request->noc); }
        if ($request->filled('location')) { $baseCount->where('location_id', $request->location); }
        if ($request->filled('screen'))   { $baseCount->where('screen_id', $request->screen); }
        $counts = [
            'all'          => (clone $baseCount)->count(),
            'unlinked'     => (clone $baseCount)->where('status', '!=', 'linked')->count(),
            'missing_cpls' => (clone $baseCount)->where('status', 'linked')->where('cpls', '!=', 1)->count(),
            'missing_kdms' => (clone $baseCount)->where('status', 'linked')->where('cpls', 1)->where('kdm', '!=', 1)->count(),
            'kdm_expired'  => (clone $baseCount)->where('kdm', 2)->count(),
            'kdm_expiring' => (clone $baseCount)->where('kdm', '!=', 2)->where('kdm_notes', 'like', '%warning%')->count(),
        ];

        return view('admin.hub-schedules.index', compact('schedules', 'nocInstances', 'locations', 'screens', 'date', 'tab', 'counts'));
    }
}
