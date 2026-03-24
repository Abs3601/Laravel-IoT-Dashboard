        @php
            $friendlyName = $device->friendly_name ?? str_replace('_', ' ', ucfirst($device->entity_id));
            $isOn = strtolower($device->current_state) === 'on';
        @endphp

        <a href="{{ route('device.group', $device->device_group) }}"
            class="card bg-card-light dark:bg-card-dark h-full hover:brightness-110 transition-colors cursor-pointer border border-transparent dark:border-gray-700">
            <div class="card-body flex flex-col h-full">
                <div class="card-container flex items-start">
                    <div class="left-side flex-1">
                        <h1 class="text-2xl font-semibold text-white">{{ $friendlyName }}</h1>
                        <div class="flex items-center gap-4 mt-4 relative z-10">
                            <span class="badge {{ $isOn ? 'badge-success' : 'badge-neutral' }} badge-lg whitespace-nowrap">
                                {{ $isOn ? 'On' : 'Off' }}
                            </span>
                            
                            <label class="cursor-pointer flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-200 dark:text-gray-300">Power</span>
                                <input type="checkbox" class="toggle toggle-primary" 
                                    wire:click.prevent.stop="toggleDevice({{ $device->id }})" 
                                    {{ $isOn ? 'checked' : '' }} />
                            </label>
                        </div>
                        <p class="text-sm font-light text-gray-400 mt-2">
                            Last Update: {{ optional($device->last_seen_at)->diffForHumans() ?? 'never' }}
                        </p>
                    </div>
                    <div class="shrink-0 ml-auto">
                        <img src="{{ URL::asset('/images/plug.svg') }}" alt="device icon" class="w-15 h-auto">
                    </div>
                </div>
            </div>
        </a>
