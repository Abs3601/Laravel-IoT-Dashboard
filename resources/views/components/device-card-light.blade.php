        <div class="card bg-card-light dark:bg-card-dark h-full hover:brightness-110 transition-colors relative isolate border border-transparent dark:border-gray-700">
            <a href="{{ route('device.group', $device->device_group) }}" class="absolute inset-0 z-0"></a>
            <div class="card-body flex flex-col h-full">
                <div class="card-container flex items-start">
                    <div class="left-side">
                        <h1 class="text-2xl font-semibold text-white">
                            {{ $device->friendly_name ?? str_replace('_', ' ', ucfirst($device->entity_id)) }}
                        </h1>
                        <div class="flex items-center gap-4 mb-2 mt-1 relative z-10">
                            <p class="text-base font-normal badge {{ strtolower($device->current_state) === 'on' ? 'badge-success' : 'badge-neutral' }} mb-0">{{ ucfirst($device->current_state) }}</p>
                            
                            <label class="cursor-pointer flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-200 dark:text-gray-300">Power</span>
                                <input type="checkbox" class="toggle toggle-primary" 
                                    wire:click.prevent.stop="toggleDevice({{ $device->id }})" 
                                    {{ strtolower($device->current_state) === 'on' ? 'checked' : '' }} />
                            </label>
                        </div>
                        <div class="mt-2 relative z-10">
                            @php
                                $brightness = $device->attributes['brightness'] ?? null;
                                $brightnessPercent = $brightness !== null ? round(($brightness / 255) * 100) : null;
                                $deviceColorMode = $device->attributes['rgb_color'] ?? null;
                                $deviceColorRGB = null;
                                if ($deviceColorMode) {
                                    $r = $deviceColorMode[0];
                                    $g = $deviceColorMode[1];
                                    $b = $deviceColorMode[2];
                                    $deviceColorRGB = "rgb({$r},{$g},{$b})";
                                }
                            @endphp
                            @if($brightnessPercent !== null)
                                <div class="relative mb-2 z-10">
                                    <input type="range" wire:key="slider-{{ $device->id }}" min="0" max="100" value="{{ $brightnessPercent }}"
                                        class="brightness-slider w-full h-8 rounded appearance-none cursor-pointer"
                                        style="background: linear-gradient(90deg, {{ $deviceColorRGB ?? '#FFBF00' }} {{ $brightnessPercent }}%, #e5e7eb {{ $brightnessPercent }}%);"
                                        wire:change="setBrightness({{ $device->id }}, $event.target.value)"
                                        oninput="this.style.background = 'linear-gradient(90deg, {{ $deviceColorRGB ?? '#FFBF00' }} '+this.value+'%, #e5e7eb '+this.value+'%)'; this.nextElementSibling.textContent = this.value + '%';" />
                                    <span
                                        class="absolute top-0 left-0 right-0 h-8 flex items-center justify-center text-sm text-white pointer-events-none text-shadow-md">
                                        {{ $brightnessPercent }}%
                                    </span>
                                </div>
                            @endif
                            <p class="text-sm font-light text-gray-400">Last Update:
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