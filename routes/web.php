<?php

use App\Http\Controllers\deviceController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

// Onboarding routes
Route::get("/", [OnboardingController::class, "index"])->name("onboarding");
Route::post("/", [OnboardingController::class, "store"])->name("onboarding.store");

Route::get('/index', function () {
    return view('index');
})->name('home');

Route::get('/device-overview', [deviceController::class, 'allDevices'])->name('device.overview');

Route::get('/device-details/{type}', [deviceController::class, 'deviceDetails'])->name('device.details');

Route::get('/device/{deviceGroup}', [deviceController::class, 'showDeviceGroup'])->name('device.group');

Route::get('/stats', [StatsController::class, 'index'])->name('stats');
Route::get('/api/stats', [StatsController::class, 'data'])->name('stats.data');
