<x-navbar active='Devices'/>

    <div class="container mx-auto p-4 max-w-4xl">
        {{-- Back button --}}
        <a href="{{ url()->previous() }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </a>

        {{-- Device Header --}}
        @php
            $friendlyName = $device->friendly_name ?? ucfirst(str_replace('_', ' ', $device->entity_id));
            $state = $device->current_state;
            $isToggleable = in_array(strtolower($state), ['on', 'off']);
            $isOn = strtolower($state) === 'on';
        @endphp

        <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:text-white dark:border-gray-700 mb-6">
            <div class="card-body p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ $friendlyName }}</h1>
                        <p class="text-gray-400 mt-1">{{ $device->entity_type }} / {{ $device->entity_id }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        @if($isToggleable)
                            <div class="flex items-center justify-center w-16 h-16 rounded-full border-2 transition-all duration-300 {{ $isOn ? 'border-white bg-white text-gray-800 shadow-[0_0_15px_rgba(255,255,255,0.4)]' : 'border-gray-500 bg-transparent text-gray-400' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                                </svg>
                            </div>
                        @elseif($state)
                            <span class="badge text-white badge-info badge-lg text-lg p-4">
                                {{ ucfirst($state) }}
                            </span>
                        @endif
                    </div>
                </div>
                <p class="text-sm text-gray-400 mt-4">
                    Last updated: {{ optional($device->last_seen_at)->diffForHumans() ?? 'never' }}
                </p>
            </div>
        </div>

        {{-- Device Attributes --}}
        @if(!empty($device->attributes))
            <h2 class="text-xl font-bold mb-4 dark:text-white">Attributes</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                @foreach($device->attributes as $key => $value)
                    @if(!in_array($key, ['friendly_name', 'state']))
                        <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700">
                            <div class="card-body p-5 text-white">
                                <p class="text-sm text-gray-400 mb-1">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                                <p class="text-xl font-bold text-white break-words">
                                    @php
                                        if (is_array($value)) {
                                            $disp = implode(', ', $value);
                                        } elseif (is_bool($value)) {
                                            $disp = $value ? 'Yes' : 'No';
                                        } elseif (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
                                            try {
                                                $disp = \Carbon\Carbon::parse($value)->diffForHumans();
                                            } catch (\Exception $e) { $disp = $value; }
                                        } else {
                                            $disp = $value;
                                        }
                                    @endphp
                                    {{ $disp }}
                                </p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Related Sensors / Grouped Entities --}}
        @if($relatedDevices->count() > 0)
            <h2 class="text-xl font-bold mb-4 dark:text-white">Related Sensors</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($relatedDevices as $related)
                    @php
                        $unit = $related->attributes['unit_of_measurement'] ?? '';
                        $relatedName = $related->friendly_name
                            ?? ucfirst(str_replace('_', ' ', $related->entity_id));
                        $shortName = str_replace($friendlyName . ' ', '', $relatedName);
                        $shortName = str_replace(ucfirst(str_replace('_', ' ', $device->entity_id)) . ' ', '', $shortName);
                    @endphp
                    <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700">
                        <div class="card-body p-5 text-white">
                            <p class="text-sm text-gray-400">{{ $shortName }}</p>
                            <p class="text-2xl font-bold text-white">
                                {{ $related->current_state ?? '—' }}
                                <span class="text-base text-gray-400">{{ $unit }}</span>
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ optional($related->last_seen_at)->diffForHumans() ?? 'never' }}
                            </p>
                        </div>
                    </div>
                @endforeach
        @else
            <p class="text-gray-500">No related sensor data available for this device.</p>
        @endif
    </div>

    {{-- Bottom Section for Activity --}}
    <div class="container mx-auto p-4 max-w-4xl mt-8 border-t border-gray-100 dark:border-gray-800">
        <h2 class="text-xl font-bold dark:text-white mb-6">Activity</h2>
        
        <div class="bg-card-dark rounded-3xl border border-transparent dark:border-gray-700 overflow-hidden shadow-xl">
            <div class="max-h-[500px] overflow-y-auto custom-scrollbar">
                @php $allEvents = $historyGroups->flatten(); @endphp
                @forelse($historyGroups as $date => $events)
                    <div class="sticky top-0 bg-[#25211d] px-6 py-3 border-b border-gray-700/50 z-10">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                            {{ \Carbon\Carbon::parse($date)->format('j F Y') }}
                        </h3>
                    </div>
                    
                    <div class="divide-y divide-gray-700/30">
                        @foreach($events as $event)
                            <x-activity-log-item 
                                :event="$event" 
                                :prev="$allEvents->where('id', '<', $event->id)->first()" 
                                :device="$device" 
                            />
                        @endforeach
                    </div>
                @empty
                    <div class="p-12 text-center text-gray-500 text-sm italic">No recent activity.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
