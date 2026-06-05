<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HubSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HubScheduleController extends Controller
{
    private function userLocationIds(Request $request): array
    {
        return $request->user()->locations()->pluck('locations.id')->toArray();
    }

    private function baseQuery(Request $request)
    {
        return HubSchedule::with(['nocInstance', 'location', 'screen'])
            ->whereIn('location_id', $this->userLocationIds($request));
    }

    private function forDate(Request $request)
    {
        $date  = $request->input('date', Carbon::today()->format('Y-m-d'));
        $start = Carbon::parse($date)->startOfDay();
        $end   = Carbon::parse($date)->addDay()->startOfDay();
        return $this->baseQuery($request)
            ->where('date_start', '>=', $start)
            ->where('date_start', '<', $end);
    }

    public function index(Request $request): JsonResponse
    {
        $schedules = $this->forDate($request)->orderBy('date_start')->get();
        return response()->json(compact('schedules'));
    }

    public function unlinked(Request $request): JsonResponse
    {
        $schedules = $this->forDate($request)->where('status', '!=', 'linked')->orderBy('date_start')->get();
        return response()->json(compact('schedules'));
    }

    public function missingCpls(Request $request): JsonResponse
    {
        $schedules = $this->forDate($request)->where('status', 'linked')->where('cpls', '!=', 1)->orderBy('date_start')->get();
        return response()->json(compact('schedules'));
    }

    public function missingKdms(Request $request): JsonResponse
    {
        $schedules = $this->forDate($request)->where('status', 'linked')->where('cpls', 1)->where('kdm', '!=', 1)->orderBy('date_start')->get();
        return response()->json(compact('schedules'));
    }

    public function kdmExpired(Request $request): JsonResponse
    {
        $schedules = $this->forDate($request)->where('kdm', 2)->orderBy('date_start')->get();
        return response()->json(compact('schedules'));
    }

    public function kdmExpiring(Request $request): JsonResponse
    {
        $hours     = (int) $request->input('hours', 48);
        $threshold = Carbon::now()->addHours($hours)->toDateTimeString();
        $schedules = $this->baseQuery($request)
            ->where('date_start', '>=', now())
            ->where('kdm', '!=', 2)
            ->whereJsonContains('kdm_notes', ['status' => 'warning'])
            ->orderBy('date_start')
            ->get();
        return response()->json(compact('schedules'));
    }
}
