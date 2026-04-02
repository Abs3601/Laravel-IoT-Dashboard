<?php

use App\Models\Device;

new class extends \Livewire\Component
{
    public $devices;

    public function mount()
    {
        $this->refreshDevices();
    }

    public function getListeners(): array
    {
        $listeners = ['device-restored' => 'refreshDevices'];
        if ($this->devices) {
            foreach ($this->devices as $device) {
                $listeners["echo:devices.{$device->device_group},DeviceUpdated"] = 'refreshDevices';
            }
        }
        return $listeners;
    }

    public function refreshDevices($payload = null)
    {
        $this->devices = Device::where('is_pinned', true)
            ->where('is_hidden', false)
            ->latest('last_seen_at')
            ->get();
    }

    public function toggleDevice($deviceId)
    {
        $device = Device::find($deviceId);
        if ($device) {
            $newState = strtolower($device->current_state) === 'on' ? 'OFF' : 'ON';
            $device->sendCommand($newState);

            $this->devices->transform(function ($d) use ($deviceId, $newState) {
                if ($d->id === $deviceId) {
                    $d->current_state = strtolower($newState);
                }
                return $d;
            });

            //  Give the device some brightness if its off and 0 brightness
            if ($newState === 'ON' && $device->entity_type === 'light') {
                $attrs = $device->attributes ?? [];
                if (isset($attrs['brightness']) && $attrs['brightness'] == 0) {
                    $device->setBrightness(26);
                    
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
            
            // Convert hex to rgb
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
            $this->refreshDevices();
        }
    }
};
?>

<div>
    @if($devices->isEmpty())
        <div class="card bg-card-light dark:bg-card-dark rounded-3xl shadow-md overflow-hidden border border-transparent dark:border-gray-700">
            <div class="card-body p-6">
                <p class="text-gray-400 text-sm">No devices pinned yet. Go to the Devices page and click the star icon on your favorites to pin them here!</p>
            </div>
        </div>
    @else
        <div class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(300px,1fr))]">
            @foreach($devices as $device)
                @php
                    $componentName = 'device-card-' . $device->entity_type;
                    $ComponentPath = 'components.' . $componentName;
                @endphp

                <div class="w-full" wire:key="pinned-card-{{ $device->id }}">
                    @if (view()->exists($ComponentPath))
                        @include($ComponentPath, ['device' => $device])
                    @else
                        @include('components.device-card-generic', ['device' => $device])
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
