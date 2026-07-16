<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HubUnifiedError;
use App\Models\Location;
use App\Models\NocInstance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HubUnifiedErrorAdminController extends Controller
{
    public function index(Request $request): View
    {
        $nocInstances = NocInstance::where('is_active', true)->orderBy('name')->get();
        $locations    = Location::orderBy('name')->get();
        $nocFilter    = $request->input('noc');
        $locFilter    = $request->input('location');
        $typeFilter   = $request->input('device_type');

        $query = HubUnifiedError::query()
            ->when($nocFilter, fn($q) => $q->where('noc_instance_id', $nocFilter))
            ->when($locFilter, fn($q) => $q->where('location_id', $locFilter))
            ->when($typeFilter, fn($q) => $q->where('device_type', $typeFilter))
            ->with(['location', 'nocInstance'])
            ->orderByDesc('synced_at');

        $data = $query->get();

        $totals = HubUnifiedError::query()
            ->when($nocFilter, fn($q) => $q->where('noc_instance_id', $nocFilter))
            ->when($locFilter, fn($q) => $q->where('location_id', $locFilter))
            ->selectRaw('device_type, COUNT(*) as c')
            ->groupBy('device_type')
            ->pluck('c', 'device_type');

        return view('admin.hub-unified-errors.index', compact('data', 'totals', 'nocInstances', 'locations', 'typeFilter'));
    }
}
