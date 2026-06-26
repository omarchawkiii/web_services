<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HubErrorSummary;
use App\Models\HubKdmError;
use App\Models\HubProjectorError;
use App\Models\HubRaidAlert;
use App\Models\HubServerAlarm;
use App\Models\HubServerError;
use App\Models\HubSoundError;
use App\Models\HubStorageError;
use App\Models\HubTmsError;
use App\Models\NocInstance;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HubErrorAdminController extends Controller
{
    public function index(Request $request): View
    {
        $nocInstances = NocInstance::where('is_active', true)->orderBy('name')->get();
        $locations    = Location::orderBy('name')->get();
        $tab          = $request->input('tab', 'summary');
        $nocFilter    = $request->input('noc');
        $locFilter    = $request->input('location');

        // Summary totals
        $summaryQuery = HubErrorSummary::query();
        if ($nocFilter) $summaryQuery->where('noc_instance_id', $nocFilter);
        if ($locFilter) $summaryQuery->where('location_id', $locFilter);
        $summaries = $summaryQuery->with(['location','nocInstance'])->get();

        $totals = [
            'kdm'       => $summaries->sum('kdm_errors'),
            'sound'     => $summaries->sum('nbr_sound_alert'),
            'projector' => $summaries->sum('nbr_projector_alert'),
            'server'    => $summaries->sum('nbr_server_alert'),
            'storage'   => $summaries->sum('nbr_storage_errors'),
            'tms'       => $summaries->sum('nbr_tms_alert'),
        ];
        $totals['total'] = array_sum($totals);

        $filter = fn($q) => $q
            ->when($nocFilter, fn($q) => $q->where('noc_instance_id', $nocFilter))
            ->when($locFilter, fn($q) => $q->where('location_id', $locFilter))
            ->with(['location','nocInstance']);

        $data = match($tab) {
            'kdm'      => $filter(HubKdmError::query())->get(),
            'server'   => $filter(HubServerError::query())->get(),
            'projector'=> $filter(HubProjectorError::query())->get(),
            'sound'    => $filter(HubSoundError::query())->get(),
            'storage'  => $filter(HubStorageError::query())->get(),
            'raid'     => $filter(HubRaidAlert::query())->get(),
            'alarms'   => $filter(HubServerAlarm::query())->with('screen')->get(),
            'tms'      => $filter(HubTmsError::query())->get(),
            default    => collect(),
        };

        return view('admin.hub-errors.index', compact('totals','summaries','data','tab','nocInstances','locations'));
    }
}
