<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class deviceController extends Controller
{
    public function index(){
        $devices = Device::orderBy('entity_id')
        ->latest('last_seen_at')
        ->take(50)
        ->where('entity_type', 'light')
        ->where('entity_id', 'NOT LIKE', '%browser%')
        ->get();

    return view('index', ['devices' => $devices]);
    }
}
