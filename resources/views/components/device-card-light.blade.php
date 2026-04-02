<div class="card bg-card-light dark:bg-card-dark min-h-[224px] rounded-3xl shadow-md overflow-hidden h-full hover:scale-[1.02] hover:brightness-110 transition-all duration-300 ease-in-out cursor-pointer border border-transparent dark:border-gray-700 relative isolate">
    <a href="{{ route('device.group', $device->device_group) }}" class="absolute inset-0 z-0 rounded-2xl"></a>
    <div class="card-body p-6 flex flex-col h-full relative z-10 pointer-events-none">
        
        <div class="flex items-start justify-between gap-4 w-full">
            <h1 class="text-2xl font-semibold text-white break-words line-clamp-2">
                {{ $device->friendly_name ?? str_replace('_', ' ', ucfirst($device->entity_id)) }}
            </h1>
            <div class="shrink-0 mt-1">
                <img src="{{ URL::asset('/images/Lamp-Icon.svg') }}" alt="lamp icon" class="w-10 h-auto opacity-80">
            </div>
        </div>

        <div class="flex flex-grow items-center justify-between gap-4 mt-6 mb-4 w-full">
            <div class="flex items-center gap-3 shrink-0">
                @php $isOn = strtolower($device->current_state) === 'on'; @endphp
                <button wire:click.prevent.stop="toggleDevice({{ $device->id }})" 
                    class="flex items-center justify-center w-12 h-12 rounded-full border-2 transition-all duration-300 pointer-events-auto {{ $isOn ? 'border-white bg-white text-gray-800 shadow-[0_0_15px_rgba(255,255,255,0.4)]' : 'border-gray-500 bg-transparent text-gray-400 hover:text-gray-300 hover:border-gray-400' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                    </svg>
                </button>
                <span class="text-lg font-medium text-white">{{ $isOn ? 'On' : 'Off' }}</span>
            </div>
            
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
            
            @php
                $colorModes = $device->attributes['supported_color_modes'] ?? [];
                $supportsColor = in_array('xy', $colorModes) || in_array('hs', $colorModes) || in_array('rgb', $colorModes) || isset($device->attributes['color']);
                $supportsColorTemp = in_array('color_temp', $colorModes) || isset($device->attributes['color_temp']);
                $colorTemp = $device->attributes['color_temp'] ?? 300;
                $deviceColorHex = $deviceColorHex ?? '#ffffff';
            @endphp
            
            <div class="flex flex-1 items-center justify-end gap-3 ml-auto">
                @if($isOn)
                    <div class="flex flex-col gap-2 w-full max-w-[140px] justify-center">
                        @if($brightnessPercent !== null)
                            <div class="w-full relative h-7">
                                <input type="range" wire:key="slider-{{ $device->id }}" min="0" max="100" value="{{ $brightnessPercent }}"
                                    class="brightness-slider w-full h-7 rounded appearance-none cursor-pointer pointer-events-auto"
                                    style="background: linear-gradient(90deg, {{ $deviceColorRGB ?? '#FFBF00' }} {{ $brightnessPercent }}%, #e5e7eb {{ $brightnessPercent }}%);"
                                    wire:change="setBrightness({{ $device->id }}, $event.target.value)"
                                    oninput="this.style.background = 'linear-gradient(90deg, {{ $deviceColorRGB ?? '#FFBF00' }} '+this.value+'%, #e5e7eb '+this.value+'%)';" />
                                <span class="absolute inset-0 flex items-center justify-center text-[12px] leading-none text-shadow-md font-semibold text-white pointer-events-none drop-shadow-[0_1px_2px_rgba(0,0,0,0.8)]">
                                    {{ $brightnessPercent }}%
                                </span>
                            </div>
                        @endif

                        @if($supportsColorTemp)
                            <div class="w-full relative h-3 mt-1">
                                <input type="range" wire:key="temp-{{ $device->id }}" min="153" max="500" value="{{ $colorTemp }}"
                                    class="brightness-slider w-full h-3 rounded-full appearance-none cursor-pointer pointer-events-auto shadow-inner"
                                    style="background: linear-gradient(90deg, #a6d8ff, #ffffff, #ffb347);"
                                    wire:input.throttle.500ms="setColorTemp({{ $device->id }}, $event.target.value)"
                                    wire:change="setColorTemp({{ $device->id }}, $event.target.value)" />
                            </div>
                        @endif
                    </div>

                    @if($supportsColor)
                        <div class="shrink-0 flex items-center">
                            <label style="background-color: {{ $deviceColorHex }};" 
                                class="cursor-pointer pointer-events-auto shadow-[0_0_8px_rgba(255,255,255,0.3)] w-10 h-10 flex-shrink-0 rounded-full border-2 border-white/80 block relative overflow-hidden transition-all duration-300">
                                <input type="color" wire:key="color-{{ $device->id }}" value="{{ $deviceColorHex }}" 
                                    oninput="this.parentElement.style.backgroundColor = this.value;"
                                    wire:input.debounce.400ms="setColor({{ $device->id }}, $event.target.value)" 
                                    wire:change="setColor({{ $device->id }}, $event.target.value)"
                                    class="absolute inset-0 w-[200%] h-[200%] -top-1/2 -left-1/2 opacity-0 cursor-pointer pointer-events-auto" />
                            </label>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="flex items-end justify-between mt-auto pt-3 border-t border-gray-700/50">
            <p class="text-sm font-light text-gray-400 pb-1">
                Last Update: {{ optional($device->last_seen_at)->diffForHumans() ?? 'never' }}
            </p>
            <button wire:click.prevent.stop="togglePin({{ $device->id }})" class="btn btn-ghost btn-circle btn-sm text-yellow-500 hover:text-yellow-400 hover:bg-transparent -mb-1 -mr-2 shrink-0 pointer-events-auto">
                @if($device->is_pinned)
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" /></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                @endif
            </button>
        </div>
    </div>
</div>