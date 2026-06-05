<?php

use App\Jobs\SyncNocErrors;
use App\Jobs\SyncNocPlayback;
use App\Jobs\SyncNocSchedules;
use App\Models\NocInstance;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduler — sync automatique toutes les X secondes/minutes
| Lance avec : php artisan schedule:work
|--------------------------------------------------------------------------
| Les jobs s'exécutent SYNCHRONIQUEMENT (handle() direct, sans queue).
*/

// Playback — toutes les 30 secondes
Schedule::call(function () {
    NocInstance::where('is_active', true)->each(function (NocInstance $noc) {
        (new SyncNocPlayback($noc))->handle();
    });
})->everyThirtySeconds()->name('sync-playback')->withoutOverlapping(5);

// Errors — toutes les minutes
Schedule::call(function () {
    NocInstance::where('is_active', true)->each(function (NocInstance $noc) {
        (new SyncNocErrors($noc))->handle();
    });
})->everyMinute()->name('sync-errors')->withoutOverlapping(5);

// Schedules — toutes les 5 minutes
Schedule::call(function () {
    NocInstance::where('is_active', true)->each(function (NocInstance $noc) {
        (new SyncNocSchedules($noc))->handle();
    });
})->everyFiveMinutes()->name('sync-schedules')->withoutOverlapping(5);
