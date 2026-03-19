
<a href="{{ route('device.group', $device->device_group) }}" class="block">
    <div class="card bg-base-100 h-full hover:bg-base-200 transition-colors cursor-pointer">
    <div class="card-body flex flex-col h-full">
        <div class="flex items-start">
            <div class="flex-1">
                <h1 class="text-2xl font-semibold">
                    {{ $device->friendly_name ?? str_replace('_', ' ', ucfirst($device->entity_id)) }}
                </h1>
                <div class="flex items-center gap-4 mt-2 relative z-10">
                    <p class="text-base font-normal badge {{ strtolower($device->current_state) === 'on' ? 'badge-success' : 'badge-neutral' }} mb-0">{{ ucfirst($device->current_state) }}</p>
                    
                    <label class="cursor-pointer flex items-center gap-2">
                        <span class="text-sm font-medium">Power</span>
                        <input type="checkbox" class="toggle toggle-primary" 
                            wire:click.prevent.stop="toggleDevice({{ $device->id }})" 
                            {{ strtolower($device->current_state) === 'on' ? 'checked' : '' }} />
                    </label>
                </div>
                <p class="text-sm font-light text-gray-500 mt-2">
                    Last Update: {{ optional($device->last_seen_at)->diffForHumans() ?? 'never' }}
                </p>
            </div>
            <div class="ml-auto">
                {{-- <img src="{{ URL::asset('/images/Plug.svg') }}" alt="device icon" class="w-15 h-auto"> --}}
            </div>
        </div>
    </div>
</div>
</a>