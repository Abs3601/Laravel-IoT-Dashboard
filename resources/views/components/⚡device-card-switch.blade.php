<?php

use Livewire\Component;

new class extends Component {
    public $device;

    public function mount($device)
    {
        $this->device = $device;
    }
};
?>
        @php
            $friendlyName = $device->friendly_name ?? str_replace('_', ' ', ucfirst($device->entity_id));
            $isOn = strtolower($device->current_state) === 'on';
        @endphp

        <a href="{{ route('plug.show', $device->device_group) }}"
            class="card bg-base-100 h-full hover:bg-base-200 transition-colors cursor-pointer">
            <div class="card-body flex flex-col h-full">
                <div class="card-container flex items-start">
                    <div class="left-side flex-1">
                        <h1 class="text-2xl font-semibold">{{ $friendlyName }}</h1>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="badge {{ $isOn ? 'badge-success' : 'badge-neutral' }} badge-lg">
                                {{ $isOn ? 'On' : 'Off' }}
                            </span>
                        </div>
                        <p class="text-sm font-light text-gray-500 mt-2">
                            Last Update: {{ optional($device->last_seen_at)->diffForHumans() ?? 'never' }}
                        </p>
                    </div>
                    <div class="shrink-0 ml-auto">
                        <img src="{{ URL::asset('/images/plug.svg') }}" alt="device icon" class="w-15 h-auto">
                    </div>
                </div>
            </div>
        </a>
