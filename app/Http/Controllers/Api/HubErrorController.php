<?php
namespace App\Http\Controllers\Api;

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
use App\Models\HubUnifiedError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HubErrorController extends Controller
{
    private function userLocationIds(Request $request): array
    {
        return $request->user()->locations()->pluck('locations.id')->toArray();
    }

    public function summary(Request $request): JsonResponse
    {
        $locationIds = $this->userLocationIds($request);
        $rows = HubErrorSummary::whereIn('location_id', $locationIds)->get();

        $kdm_errors = $nbr_sound_alert = $nbr_projector_alert = $nbr_server_alert = $nbr_storage_errors = $nbr_tms_alert = 0;
        foreach ($rows as $r) {
            $kdm_errors          += $r->kdm_errors;
            $nbr_sound_alert     += $r->nbr_sound_alert;
            $nbr_projector_alert += $r->nbr_projector_alert;
            $nbr_server_alert    += $r->nbr_server_alert;
            $nbr_storage_errors  += $r->nbr_storage_errors;
            $nbr_tms_alert       += $r->nbr_tms_alert ?? 0;
        }
        $total_errors = $kdm_errors + $nbr_sound_alert + $nbr_projector_alert + $nbr_server_alert + $nbr_storage_errors + $nbr_tms_alert;

        return response()->json(compact('kdm_errors','nbr_sound_alert','nbr_projector_alert','nbr_server_alert','nbr_storage_errors','nbr_tms_alert','total_errors'));
    }

    public function kdm(Request $request): JsonResponse
    {
        $list = HubKdmError::with(['location','nocInstance'])->whereIn('location_id', $this->userLocationIds($request))->get();
        return response()->json(['kdms_errors_list' => $list]);
    }

    public function server(Request $request): JsonResponse
    {
        $list = HubServerError::with(['location','nocInstance'])->whereIn('location_id', $this->userLocationIds($request))->get();
        return response()->json(['server_errors_list' => $list]);
    }

    public function projector(Request $request): JsonResponse
    {
        $list = HubProjectorError::with(['location','nocInstance'])->whereIn('location_id', $this->userLocationIds($request))->get();
        return response()->json(['projector_errors_list' => $list]);
    }

    public function sound(Request $request): JsonResponse
    {
        $list = HubSoundError::with(['location','nocInstance'])->whereIn('location_id', $this->userLocationIds($request))->get();
        return response()->json(['sounds_errors_list' => $list]);
    }

    public function storage(Request $request): JsonResponse
    {
        $list = HubStorageError::with(['location','nocInstance'])->whereIn('location_id', $this->userLocationIds($request))->get();
        return response()->json(['storage_errors_list' => $list]);
    }

    public function raid(Request $request): JsonResponse
    {
        $alerts = HubRaidAlert::with(['location','nocInstance'])->whereIn('location_id', $this->userLocationIds($request))->get();
        return response()->json(compact('alerts'));
    }

    public function serverAlarms(Request $request): JsonResponse
    {
        $alarms = HubServerAlarm::with(['location','nocInstance','screen'])->whereIn('location_id', $this->userLocationIds($request))->get();
        return response()->json(compact('alarms'));
    }

    public function tms(Request $request): JsonResponse
    {
        $list = HubTmsError::with(['location','nocInstance'])->whereIn('location_id', $this->userLocationIds($request))->get();
        return response()->json(['tms_errors_list' => $list]);
    }

    public function all(Request $request): JsonResponse
    {
        $query = HubUnifiedError::with(['location','nocInstance'])->whereIn('location_id', $this->userLocationIds($request));

        if ($request->filled('device_type')) {
            $query->where('device_type', $request->input('device_type'));
        }

        return response()->json(['errors_list' => $query->get()]);
    }
}
