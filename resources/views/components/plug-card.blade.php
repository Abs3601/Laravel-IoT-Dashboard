<?php

use Livewire\Component;

new class extends Component {
    public $plugs;

    public function mount($plugs)
    {
        $this->plugs = $plugs;
    }
};
?>

<div wire:poll.500ms class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(300px,1fr))]">
    @foreach($plugs as $plug)
        @php
            $friendlyName = $plug->friendly_name ?? str_replace('_', ' ', ucfirst($plug->entity_id));
            $isOn = strtolower($plug->current_state) === 'on';
        @endphp

        <a href="{{ route('plug.show', $plug->device_group) }}"
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
                            Last Update: {{ optional($plug->last_seen_at)->diffForHumans() ?? 'never' }}
                        </p>
                    </div>
                    <div class="shrink-0 ml-auto">
                        <img src="{{ URL::asset('/images/Plug.svg') }}" alt="Plug icon" class="w-15 h-auto">
                    </div>
                </div>
            </div>
        </a>
    @endforeach
</div>