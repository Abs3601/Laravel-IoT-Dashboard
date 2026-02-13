<?php

use Livewire\Component;
use App\Models\Device;

new class extends Component
{
    public $devices;
    public string $type = '';

    public function mount($devices)
    {
        $this->devices = $devices;
        $this->type = $devices->first()?->entity_type ?? '';
    }

    public function getListeners(): array
    {
        $listeners = [];
        foreach ($this->devices as $device) {
            $listeners["echo:devices.{$device->device_group},DeviceUpdated"] = 'refreshDevices';
        }
        return $listeners;
    }

    public function refreshDevices($payload = null)
    {
        if (isset($payload['device']['id'])) {
            $updatedId = $payload['device']['id'];
            $freshDevice = Device::find($updatedId);

            if ($freshDevice) {
                $this->devices = $this->devices->map(function ($device) use ($freshDevice) {
                    return $device->id === $freshDevice->id ? $freshDevice : $device;
                });
            }
        } else {
            $this->devices = Device::where('entity_type', $this->type)
                ->where('entity_id', 'NOT LIKE', '%browser%')
                ->latest('last_seen_at')
                ->get();
        }
    }
};
?>

<div class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(300px,1fr))]">
    @foreach($devices as $device)
        @php
            $componentName = 'device-card-' . $device->entity_type;
            $ComponentPath = 'components.' . $componentName;
        @endphp

        <div class="w-full">
            @if (view()->exists($ComponentPath))
                @include($ComponentPath, ['device' => $device])
            @else
                @include('components.device-card-generic', ['device' => $device])
            @endif
        </div>
    @endforeach
</div>



