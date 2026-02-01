<?php

use Livewire\Component;

new class extends Component {
    public $devices;

    public function mount($devices)
    {
        $this->devices = $devices;
    }
};
?>

{{-- wire:poll.500ms --}}
<div wire:poll.500ms class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(300px,1fr))]">
    @foreach($devices as $device)
        <div class="card bg-base-100 h-full">
            <div class="card-body flex flex-col h-full">
                <div class="card-container flex items-start">
                    <div class="left-side">
                        <h1 class="text-2xl font-semibold">
                            {{ str_replace('_', ' ', ucfirst($device->attributes['friendly_name'] ?? $device->entity_id)) }}
                        </h1>
                        <p class="text-base font-normal">{{ ucfirst($device->current_state) }}</p>
                        <div class="mt-2">
                            @php
                                $brightness = $device->attributes['brightness'] ?? null;
                                $brightnessPercent = $brightness !== null ? round(($brightness / 255) * 100) : null;
                            @endphp
                            @if($brightnessPercent !== null)
                                <div class="relative mb-2">
                                    <input type="range" min="0" max="100" value="{{ $brightnessPercent }}"
                                        class="brightness-slider w-full h-8 rounded appearance-none cursor-pointer"
                                        style="background: linear-gradient(90deg, #fcd34d {{ $brightnessPercent }}%, #e5e7eb {{ $brightnessPercent }}%);"
                                        oninput="this.style.background = 'linear-gradient(90deg, #fcd34d '+this.value+'%, #e5e7eb '+this.value+'%)'; this.nextElementSibling.textContent = this.value + '%';" />
                                    <span
                                        class="absolute top-0 left-0 right-0 h-8 flex items-center justify-center text-sm font-light text-white pointer-events-none">
                                        {{ $brightnessPercent }}%
                                    </span>
                                </div>
                            @endif
                            <p class="text-sm font-light text-gray-500">Last Update:
                                {{ optional($device->last_seen_at)->diffForHumans() ?? 'never' }}
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 ml-auto">
                        <img src="{{ URL::asset('/images/Lamp-Icon.svg') }}" alt="lamp icon" class="w-15 h-auto">
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>