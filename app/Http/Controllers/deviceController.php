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
        $device = Device::where('device_group', $deviceGroup)
            ->where('entity_type', '!=', 'sensor')
            ->first();

        if (!$device) {
            $device = Device::where('device_group', $deviceGroup)->firstOrFail();
        }

        $relatedDevices = Device::where('device_group', $deviceGroup)
            ->where('id', '!=', $device->id)
            ->get();

        return view('device-group-detail', [
            'device' => $device,
            'relatedDevices' => $relatedDevices,
        ]);
    }
}
