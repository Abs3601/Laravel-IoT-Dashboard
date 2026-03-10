<x-navbar active='Devices'/>

    <div class="container mx-auto p-4 max-w-4xl">
        {{-- Back button --}}
        <a href="{{ url()->previous() }}" class="btn btn-ghost mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
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

        <div class="card bg-base-100 mb-6">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $friendlyName }}</h1>
                        <p class="text-gray-500 mt-1">{{ $device->entity_type }} / {{ $device->entity_id }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        @if($isToggleable)
                            <span class="badge {{ $isOn ? 'badge-success' : 'badge-neutral' }} badge-lg text-lg p-4">
                                {{ $isOn ? 'On' : 'Off' }}
                            </span>
                        @elseif($state)
                            <span class="badge badge-info badge-lg text-lg p-4">
                                {{ ucfirst($state) }}
                            </span>
                        @endif
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">
                    Last updated: {{ optional($device->last_seen_at)->diffForHumans() ?? 'never' }}
                </p>
            </div>
        </div>

        {{-- Device Attributes --}}
        @if(!empty($device->attributes))
            <h2 class="text-xl font-bold mb-4">Attributes</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                @foreach($device->attributes as $key => $value)
                    @if(!in_array($key, ['friendly_name', 'state']))
                        <div class="card bg-base-100">
                            <div class="card-body p-4">
                                <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                                <p class="text-2xl font-bold text-white">
                                    @if(is_array($value))
                                        {{ json_encode($value) }}
                                    @elseif(is_bool($value))
                                        {{ $value ? 'Yes' : 'No' }}
                                    @else
                                        {{ $value }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Related Sensors / Grouped Entities --}}
        @if($relatedDevices->count() > 0)
            <h2 class="text-xl font-bold mb-4">Related Sensors</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($relatedDevices as $related)
                    @php
                        $unit = $related->attributes['unit_of_measurement'] ?? '';
                        $relatedName = $related->friendly_name
                            ?? ucfirst(str_replace('_', ' ', $related->entity_id));
                        // Remove the parent device name prefix for a cleaner label
                        $shortName = str_replace($friendlyName . ' ', '', $relatedName);
                        $shortName = str_replace(ucfirst(str_replace('_', ' ', $device->entity_id)) . ' ', '', $shortName);
                    @endphp
                    <div class="card bg-base-100">
                        <div class="card-body p-4">
                            <p class="text-sm text-gray-500">{{ $shortName }}</p>
                            <p class="text-2xl font-bold text-white">
                                {{ $related->current_state ?? '—' }}
                                <span class="text-base text-gray-500">{{ $unit }}</span>
                            </p>
                            <p class="text-xs text-gray-600">
                                {{ optional($related->last_seen_at)->diffForHumans() ?? 'never' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No related sensor data available for this device.</p>
        @endif
    </div>
