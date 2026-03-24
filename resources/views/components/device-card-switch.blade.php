        @php
            $friendlyName = $device->friendly_name ?? str_replace('_', ' ', ucfirst($device->entity_id));
            $isOn = strtolower($device->current_state) === 'on';
        @endphp

        <a href="{{ route('device.group', $device->device_group) }}"
            class="card bg-card-light dark:bg-card-dark rounded-3xl shadow-md overflow-hidden h-full hover:scale-[1.02] hover:brightness-110 transition-all duration-300 ease-in-out cursor-pointer border border-transparent dark:border-gray-700">
            <div class="card-body p-6 flex flex-col h-full">
                <div class="card-container flex items-start">
                    <div class="left-side flex-1 pr-2">
                        <div class="flex items-start justify-between">
                            <h1 class="text-2xl font-semibold text-white">{{ $friendlyName }}</h1>
                            <button wire:click.prevent.stop="togglePin({{ $device->id }})" class="btn btn-ghost btn-circle btn-sm text-yellow-500 hover:text-yellow-400 hover:bg-transparent relative z-20 shrink-0 mt-1">
                                @if($device->is_pinned)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" /></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                                @endif
                            </button>
                        </div>
                        <div class="flex items-center gap-4 mt-4 relative z-10">
                            <span class="badge text-white {{ $isOn ? 'badge-success' : 'badge-neutral' }} badge-lg whitespace-nowrap">
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
