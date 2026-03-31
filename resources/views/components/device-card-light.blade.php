<div class="card bg-card-light dark:bg-card-dark rounded-3xl shadow-md overflow-hidden h-full hover:scale-[1.02] hover:brightness-110 transition-all duration-300 ease-in-out cursor-pointer border border-transparent dark:border-gray-700 relative isolate">
    <a href="{{ route('device.group', $device->device_group) }}" class="absolute inset-0 z-0 rounded-2xl"></a>
    <div class="card-body p-6 flex flex-col h-full relative z-10 pointer-events-none">
        <div class="card-container flex items-start">
            <div class="left-side w-full">
                <div class="flex items-start justify-between w-full pointer-events-auto">
                    <h1 class="text-2xl font-semibold text-white">
                        {{ $device->friendly_name ?? str_replace('_', ' ', ucfirst($device->entity_id)) }}
                    </h1>
                    <button wire:click.prevent.stop="togglePin({{ $device->id }})" class="btn btn-ghost btn-circle btn-sm text-yellow-500 hover:text-yellow-400 hover:bg-transparent -mt-1 -mr-2">
                        @if($device->is_pinned)
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" /></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                        @endif
                    </button>
                </div>
                <div class="flex items-center gap-4 mb-2 mt-1 pointer-events-auto">
                    <p class="text-base font-normal badge text-white {{ strtolower($device->current_state) === 'on' ? 'badge-success' : 'badge-neutral' }} mb-0">{{ ucfirst($device->current_state) }}</p>
                    
                    <label class="cursor-pointer flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-200 dark:text-gray-300">Power</span>
                        <input type="checkbox" class="toggle toggle-primary" 
                            wire:click.prevent.stop="toggleDevice({{ $device->id }})" 
                            {{ strtolower($device->current_state) === 'on' ? 'checked' : '' }} />
                    </label>
                </div>
                <div class="mt-2 pointer-events-auto">
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
                        <div class="relative mb-2">
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
            <div class="shrink-0 ml-auto pointer-events-auto">
                <img src="{{ URL::asset('/images/Lamp-Icon.svg') }}" alt="lamp icon" class="w-15 h-auto">
            </div>
        </div>
    </div>
</div>