<?php

use App\Http\Controllers\Api\HubAuthController;
use App\Http\Controllers\Api\HubErrorController;
use App\Http\Controllers\Api\HubPlaybackController;
use App\Http\Controllers\Api\HubPlaybackDetailController;
use App\Http\Controllers\Api\HubScheduleController;
use App\Http\Controllers\Api\NocSyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| NOC → Hub Sync API
|--------------------------------------------------------------------------
| Called by NOC instances to push data to the hub.
| Authentication: Authorization: Bearer {noc_instance.api_key}
*/
Route::prefix('noc')->group(function () {
    Route::post('/locations/sync', [NocSyncController::class, 'syncLocations']);
    Route::post('/users/sync',     [NocSyncController::class, 'syncUsers']);
    Route::post('/playback/sync',         [NocSyncController::class, 'syncPlayback']);
    Route::post('/playback-details/sync', [NocSyncController::class, 'syncPlaybackDetails']);
    Route::post('/schedules/clear',    [NocSyncController::class, 'clearSchedules']);
    Route::post('/schedules/sync',     [NocSyncController::class, 'syncSchedules']);
    Route::post('/schedules/finalize', [NocSyncController::class, 'finalizeSchedules']);
    Route::post('/errors/sync',           [NocSyncController::class, 'syncErrors']);
});

/*
|--------------------------------------------------------------------------
| Hub Mobile API
|--------------------------------------------------------------------------
| Called by the mobile app. All data is served from the hub's local tables.
| Authentication: Authorization: Bearer {hub sanctum token}
*/
Route::prefix('hub/mobile')->name('hub.mobile.')->group(function () {

    // Auth (no token required)
    Route::post('/login',  [HubAuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {

        // Profile
        Route::get('/profile', [HubAuthController::class, 'profile'])->name('profile');
        Route::post('/logout', [HubAuthController::class, 'logout'])->name('logout');

        // Playback
        Route::prefix('playback')->name('playback.')->group(function () {
            Route::get('/',                [HubPlaybackController::class,       'index'])->name('index');
            Route::get('/{id}',            [HubPlaybackController::class,       'show'])->name('show');
            Route::get('/{id}/server',     [HubPlaybackDetailController::class, 'serverDetail'])->name('server');
            Route::get('/{id}/projector',  [HubPlaybackDetailController::class, 'projectorDetail'])->name('projector');
            Route::get('/{id}/sound',      [HubPlaybackDetailController::class, 'soundDetail'])->name('sound');
        });

        // Schedules
        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/',                       [HubScheduleController::class, 'index'])->name('index');
            Route::prefix('issues')->name('issues.')->group(function () {
                Route::get('/unlinked',           [HubScheduleController::class, 'unlinked'])->name('unlinked');
                Route::get('/missing-cpls',       [HubScheduleController::class, 'missingCpls'])->name('missing-cpls');
                Route::get('/missing-kdms',       [HubScheduleController::class, 'missingKdms'])->name('missing-kdms');
                Route::get('/kdm-expired',        [HubScheduleController::class, 'kdmExpired'])->name('kdm-expired');
                Route::get('/kdm-expiring',       [HubScheduleController::class, 'kdmExpiring'])->name('kdm-expiring');
            });
        });

        // Errors
        Route::prefix('errors')->name('errors.')->group(function () {
            Route::get('/summary',       [HubErrorController::class, 'summary'])->name('summary');
            Route::get('/kdm',           [HubErrorController::class, 'kdm'])->name('kdm');
            Route::get('/server',        [HubErrorController::class, 'server'])->name('server');
            Route::get('/projector',     [HubErrorController::class, 'projector'])->name('projector');
            Route::get('/sound',         [HubErrorController::class, 'sound'])->name('sound');
            Route::get('/storage',       [HubErrorController::class, 'storage'])->name('storage');
            Route::get('/raid',          [HubErrorController::class, 'raid'])->name('raid');
            Route::get('/server-alarms', [HubErrorController::class, 'serverAlarms'])->name('server-alarms');
        });
    });
});
