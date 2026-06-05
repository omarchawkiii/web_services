<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HubErrorSummary;
use App\Models\HubPlayback;
use App\Models\HubSyncLog;
use App\Models\NocInstance;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $nocStats = NocInstance::withCount(['users', 'locations'])
            ->orderBy('name')
            ->get()
            ->map(fn($noc) => [
                'id'           => $noc->id,
                'name'         => $noc->name,
                'sync_status'  => $noc->sync_status,
                'status_class' => $noc->getStatusColorClass(),
                'last_sync_at' => $noc->last_sync_at,
                'is_active'    => $noc->is_active,
                'users_count'  => $noc->users_count,
                'locations_count' => $noc->locations_count,
            ]);

        $globalStats = [
            'total_nocs'      => NocInstance::count(),
            'active_nocs'     => NocInstance::where('is_active', true)->count(),
            'online_nocs'     => NocInstance::where('sync_status', 'online')->count(),
            'total_playbacks' => HubPlayback::count(),
            'playing'         => HubPlayback::where('playback_status', 'Play')->count(),
            'offline_screens' => HubPlayback::where('playback_status', 'Offline')->count(),
        ];

        $errorTotals = HubErrorSummary::selectRaw('
            SUM(kdm_errors) as kdm,
            SUM(nbr_sound_alert) as sound,
            SUM(nbr_projector_alert) as projector,
            SUM(nbr_server_alert) as server,
            SUM(nbr_storage_errors) as storage
        ')->first();

        $recentLogs = HubSyncLog::with('nocInstance')
            ->latest('started_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('nocStats', 'globalStats', 'errorTotals', 'recentLogs'));
    }
}
