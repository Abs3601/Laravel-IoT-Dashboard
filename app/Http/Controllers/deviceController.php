<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class deviceController extends Controller
{
    public function devicesByType()
    {

        $lights = Device::where('entity_type', 'light')
            ->latest('last_seen_at')
            ->where('entity_id', 'NOT LIKE', '%browser%')
            ->get();

        // Get plug switches with their related sensors
        $plugs = Device::where('entity_type', 'switch')
            // ->where('entity_id', 'LIKE', '%plug%')
            ->where('entity_id', 'NOT LIKE', '%auto_off%')
            ->where('entity_id', 'NOT LIKE', '%led%')
            ->get()
            ->map(function ($plug) {
                // Find all related sensors by device_group
                $plug->sensors = Device::where('device_group', $plug->device_group)
                    ->where('entity_type', 'sensor')
                    ->get();
                return $plug;
            });

        return view('all-devices', [
            'lights' => $lights,
            'plugs' => $plugs,
        ]);
    }


    public function allDevices()
    {

        $displayableTypes = config('devices.displayable_types');

        $devices = Device::distinct()
            ->whereIn('entity_type', $displayableTypes)
            ->get('entity_type');

        return view('device-overview', [
            'devices' => $devices,
        ]);
    }

    public function showPlug(string $deviceGroup)
    {
        $plug = Device::where('entity_type', 'switch')
            ->where('device_group', $deviceGroup)
            ->firstOrFail();

        $sensors = Device::where('device_group', $deviceGroup)
            ->where('entity_type', 'sensor')
            ->get();

        return view('plug-detail', [
            'plug' => $plug,
            'sensors' => $sensors,
        ]);
    }
}
