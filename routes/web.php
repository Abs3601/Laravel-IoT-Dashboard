<?php

use App\Http\Controllers\deviceController;
use App\Http\Controllers\OnboardingController;
use App\Http\Middleware\OnboardingComplete;
use Illuminate\Support\Facades\Route;

// Onboarding routes
Route::get("/", [OnboardingController::class, "index"])->name("onboarding")->middleware(OnboardingComplete::class);
Route::post("/", [OnboardingController::class, "store"])->name("onboarding.store")->middleware(OnboardingComplete::class);

Route::get('/index', function () {
    return view('index');
})->name('home')->middleware(OnboardingComplete::class);
Route::get('/all-devices', [deviceController::class, 'allDevices'])->name('devices.all')->middleware(OnboardingComplete::class);
Route::get('/plug/{deviceGroup}', [deviceController::class, 'showPlug'])->name('plug.show')->middleware(OnboardingComplete::class);



