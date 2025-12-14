<?php

use Illuminate\Support\Facades\Route;
use App\Models\IoTEvent;

Route::get('/', function () {
    return view('welcome');
});
