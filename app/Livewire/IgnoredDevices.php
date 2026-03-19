<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Device;

class IgnoredDevices extends Component
{
    public function getListeners(): array
    {
        // Listen for new non-IoT devices discovering themselves so the list updates live!!!
        return [
            "echo:devices,DeviceUpdated" => '$refresh',
        ];
    }

    public function restoreDevice($id)
    {
        $device = Device::find($id);
        if ($device) {
            $device->update(['is_hidden' => false]);
            $this->dispatch('device-restored');
        }
    }

    public function render()
    {
        return view('livewire.ignored-devices', [
            'devices' => Device::where('is_hidden', true)->latest('last_seen_at')->get()
        ]);
    }
}
