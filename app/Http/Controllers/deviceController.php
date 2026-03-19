<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class deviceController extends Controller
{
    public function allDevices()
    {
        $devices = Device::select('entity_type')
            ->distinct()
            ->whereNotNull('current_state')
            ->where('is_hidden', false)
            ->get();

        return view('device-overview', [
            'devices' => $devices,
        ]);
    }

    /**
     * Show all devices of a given type.
     */
    public function deviceDetails(string $type)
    {
        $devices = Device::where('entity_type', $type)
            ->whereNotNull('current_state')
            ->where('is_hidden', false)
            ->latest('last_seen_at')
            ->get();

        return view('device-details', [
            'devices' => $devices,
            'type' => $type,
        ]);
    }

    /**
     * Show a device and all its related sensors/entities.
     */
    public function showDeviceGroup(string $deviceGroup)
    {
        $priorities = "CASE 
            WHEN entity_type = 'light' THEN 1
            WHEN entity_type = 'switch' THEN 2
            WHEN entity_type = 'climate' THEN 3
            WHEN entity_type = 'fan' THEN 4
            WHEN entity_type = 'media_player' THEN 5
            WHEN entity_type = 'cover' THEN 6
            WHEN entity_type = 'lock' THEN 7
            WHEN entity_type = 'siren' THEN 8
            ELSE 99 END";

        $device = Device::where('device_group', $deviceGroup)
            ->orderByRaw($priorities)
            ->firstOrFail();

        $relatedDevices = Device::where('device_group', $deviceGroup)
            ->where('id', '!=', $device->id)
            ->get();

        return view('device-group-detail', [
            'device' => $device,
            'relatedDevices' => $relatedDevices,
        ]);
    }
}
