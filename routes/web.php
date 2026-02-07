<?php

use App\Http\Controllers\deviceController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

// Onboarding routes
Route::get("/", [OnboardingController::class, "index"])->name("onboarding");
Route::post("/", [OnboardingController::class, "store"])->name("onboarding.store");

Route::get('/index', function () {
    return view('index');
})->name('home');
Route::get('/all-devices', [deviceController::class, 'allDevices'])->name('devices.all');
Route::get('/plug/{deviceGroup}', [deviceController::class, 'showPlug'])->name('plug.show');



