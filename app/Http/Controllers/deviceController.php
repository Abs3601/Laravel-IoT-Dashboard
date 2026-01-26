<?php

namespace App\Http\Controllers;

use App\Models\IoTEvent;
use Illuminate\Http\Request;

class deviceController extends Controller
{
    public function index(){
        $devices = IoTEvent::orderBy('entity_id')
        ->latest()
        ->take(5)
        ->get();

    return view('index', ['devices' => $devices]);
    }
}
