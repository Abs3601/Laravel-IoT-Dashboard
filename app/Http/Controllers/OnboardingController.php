<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

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
        
        $timezone = $request->timezone ?? 'UTC';
        $envFile = base_path('.env');
        if (file_exists($envFile)) {
            $env = file_get_contents($envFile);
            if (strpos($env, 'APP_TIMEZONE=') !== false) {
                $env = preg_replace('/^APP_TIMEZONE=.*$/m', 'APP_TIMEZONE=' . $timezone, $env);
            } else {
                $env .= "\nAPP_TIMEZONE=" . $timezone . "\n";
            }
            file_put_contents($envFile, $env);
            // Clear config cache to ensure the new timezone applies immediately
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        }

        Setting::set('onboarding_completed', true);
        return redirect()->route('home');
    }
}
