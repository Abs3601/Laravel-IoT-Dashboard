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
        $listeners = ['device-restored' => 'refreshDevices'];
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
                ->where('is_hidden', false)
                ->latest('last_seen_at')
                ->get();
        }
    }

    public function toggleDevice($deviceId)
    {
        $device = Device::find($deviceId);
        if ($device) {
            $newState = strtolower($device->current_state) === 'on' ? 'OFF' : 'ON';
            $device->sendCommand($newState);

            // Optimistic UI update
            $this->devices->transform(function ($d) use ($deviceId, $newState) {
                if ($d->id === $deviceId) {
                    $d->current_state = strtolower($newState);
                }
                return $d;
            });

            // Prevent "turning on to 0% brightness" feedback loop
            if ($newState === 'ON' && $device->entity_type === 'light') {
                $attrs = $device->attributes ?? [];
                if (isset($attrs['brightness']) && $attrs['brightness'] == 0) {
                    $device->setBrightness(26); // Force to ~10% Brightness if it was 0
                    
                    $this->devices->transform(function ($d) use ($deviceId) {
                        if ($d->id === $deviceId) {
                            $a = $d->attributes;
                            $a['brightness'] = 26;
                            $d->attributes = $a;
                        }
                        return $d;
                    });
                }
            }
        }
    }

    public function setBrightness($deviceId, $brightnessPercent)
    {
        $device = Device::find($deviceId);
        if ($device) {
            $brightness255 = (int) round(($brightnessPercent / 100) * 255);
            $device->setBrightness($brightness255);

            // Optimistic update
            $this->devices->transform(function ($d) use ($deviceId, $brightness255) {
                if ($d->id == $deviceId) {
                    $attrs = $d->attributes;
                    $attrs['brightness'] = $brightness255;
                    if ($brightness255 > 0) {
                        $d->current_state = 'on';
                    }
                    $d->attributes = $attrs;
                }
                return $d;
            });
        }
    }

    public function setColor($deviceId, $hexColor)
    {
        $device = Device::find($deviceId);
        if ($device) {
            $device->setColor($hexColor);
            
            // Convert hex to rgb for optimistic update
            $hex = ltrim($hexColor, '#');
            if (strlen($hex) == 6) {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                
                $this->devices->transform(function ($d) use ($deviceId, $r, $g, $b) {
                    if ($d->id == $deviceId) {
                        $attrs = $d->attributes;
                        $attrs['rgb_color'] = [$r, $g, $b];
                        $d->attributes = $attrs;
                    }
                    return $d;
                });
            }
        }
    }

    public function setColorTemp($deviceId, $temp)
    {
        $device = Device::find($deviceId);
        if ($device) {
            $device->setColorTemp($temp);
            $this->devices->transform(function ($d) use ($deviceId, $temp) {
                if ($d->id == $deviceId) {
                    $attrs = $d->attributes;
                    $attrs['color_temp'] = $temp;
                    $d->attributes = $attrs;
                }
                return $d;
            });
        }
    }

    public function togglePin($deviceId)
    {
        $device = Device::find($deviceId);
        if ($device) {
            $device->is_pinned = !$device->is_pinned;
            $device->save();

            // Optimistic UI update
            $this->devices->transform(function ($d) use ($deviceId, $device) {
                if ($d->id === $deviceId) {
                    $d->is_pinned = $device->is_pinned;
                }
                return $d;
            });
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

            <div class="w-full" wire:key="device-card-{{ $device->id }}">
                @if (view()->exists($ComponentPath))
                    @include($ComponentPath, ['device' => $device])
                @else
                    @include('components.device-card-generic', ['device' => $device])
                @endif
            </div>
        @endforeach
    </div>
