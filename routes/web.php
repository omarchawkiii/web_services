<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HubErrorAdminController;
use App\Http\Controllers\Admin\HubPlaybackController;
use App\Http\Controllers\Admin\HubScheduleAdminController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\NocInstanceController;
use App\Http\Controllers\Admin\SyncLogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login',   [LoginController::class, 'showLogin'])->name('login');
Route::post('/login',  [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin (requires auth)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Hub Data ─────────────────────────────────────────────────────────────
    Route::get('/playback',  [HubPlaybackController::class,      'index'])->name('hub-playback.index');
    Route::get('/schedules', [HubScheduleAdminController::class, 'index'])->name('hub-schedules.index');
    Route::get('/errors',    [HubErrorAdminController::class,    'index'])->name('hub-errors.index');
    Route::get('/sync-logs', [SyncLogController::class,          'index'])->name('sync-logs.index');

    // ── Locations ─────────────────────────────────────────────────────────────
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');

    // ── Users ─────────────────────────────────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                      [UserController::class, 'index'])->name('index');
        Route::get('/create',                [UserController::class, 'create'])->name('create');
        Route::post('/',                     [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit',           [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}',                [UserController::class, 'update'])->name('update');
        Route::delete('/{user}',             [UserController::class, 'destroy'])->name('destroy');
        Route::get('/{user}/locations',      [UserController::class, 'showLocations'])->name('show-locations');
        Route::get('/{user}/locations/edit', [UserController::class, 'editLocations'])->name('edit-locations');
        Route::put('/{user}/locations',      [UserController::class, 'updateLocations'])->name('update-locations');
    });

    // ── NOC Instances ─────────────────────────────────────────────────────────
    Route::prefix('noc-instances')->name('noc-instances.')->group(function () {
        Route::get('/',                      [NocInstanceController::class, 'index'])->name('index');
        Route::get('/create',                [NocInstanceController::class, 'create'])->name('create');
        Route::post('/',                     [NocInstanceController::class, 'store'])->name('store');
        Route::get('/{nocInstance}/edit',    [NocInstanceController::class, 'edit'])->name('edit');
        Route::put('/{nocInstance}',         [NocInstanceController::class, 'update'])->name('update');
        Route::delete('/{nocInstance}',      [NocInstanceController::class, 'destroy'])->name('destroy');
        Route::post('/{nocInstance}/test',   [NocInstanceController::class, 'testConnection'])->name('test');
        Route::post('/{nocInstance}/toggle', [NocInstanceController::class, 'toggleActive'])->name('toggle');
    });
});

Route::get('/', fn() => redirect()->route('admin.dashboard'));
