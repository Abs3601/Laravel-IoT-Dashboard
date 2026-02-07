<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class OnboardingController extends Controller
{
    public function index(){
        return view("onboarding");
    }

    public function store(Request $request){
        Setting::set('mqtt_host', $request->mqtt_host);
        Setting::set('port', $request->port);
        Setting::set('mqtt_auth_username', $request->mqtt_auth_username);
        Setting::set('mqtt_auth_password', $request->mqtt_auth_password);
        Setting::set('mqtt_client_id', $request->mqtt_client_id);
        Setting::set('onboarding_completed', true);
        return redirect()->route('home');
    }
}
