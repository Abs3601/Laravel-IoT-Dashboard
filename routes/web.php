<?php

use App\Http\Controllers\deviceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [deviceController::class, 'index']);
