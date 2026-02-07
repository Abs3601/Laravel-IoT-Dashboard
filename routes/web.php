<?php

use App\Http\Controllers\deviceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [deviceController::class, 'index'])->name('home');
Route::get('/plug/{deviceGroup}', [deviceController::class, 'showPlug'])->name('plug.show');
