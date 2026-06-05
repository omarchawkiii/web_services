<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HubSyncLog;
use App\Models\NocInstance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SyncLogController extends Controller
{
    public function index(Request $request): View
    {
        $nocInstances = NocInstance::orderBy('name')->get();

        $query = HubSyncLog::with('nocInstance')->latest('started_at');

        if ($request->filled('noc')) {
            $query->where('noc_instance_id', $request->noc);
        }
        if ($request->filled('type')) {
            $query->where('sync_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.sync-logs.index', compact('logs', 'nocInstances'));
    }
}
