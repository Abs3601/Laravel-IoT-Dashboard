<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $plug->friendly_name ?? $plug->entity_id }} - Details</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="container mx-auto p-4 max-w-4xl">
        {{-- Back button --}}
        <a href="{{ url('/') }}" class="btn btn-ghost mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </a>

        {{-- Plug Header --}}
        @php
            $friendlyName = $plug->friendly_name ?? str_replace('_', ' ', ucfirst($plug->entity_id));
            $isOn = strtolower($plug->current_state) === 'on';
        @endphp

        <div class="card bg-base-100 mb-6">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $friendlyName }}</h1>
                        <p class="text-gray-500 mt-1">{{ $plug->entity_id }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="badge {{ $isOn ? 'badge-success' : 'badge-neutral' }} badge-lg text-lg p-4">
                            {{ $isOn ? 'On' : 'Off' }}
                        </span>
                        <img src="{{ URL::asset('/images/Plug.svg') }}" alt="Plug icon" class="w-15 h-auto">
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">
                    Last updated: {{ optional($plug->last_seen_at)->diffForHumans() ?? 'never' }}
                </p>
            </div>
        </div>

        {{-- Sensor Grid --}}
        @if($sensors->count() > 0)
            <h2 class="text-xl font-bold mb-4">Sensor Readings</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($sensors as $sensor)
                    @php
                        $unit = $sensor->attributes['unit_of_measurement'] ?? '';
                        $sensorName = $sensor->attributes['friendly_name'] ?? $sensor->entity_id;
                        $shortName = str_replace($friendlyName . ' ', '', $sensorName);
                        $shortName = str_replace('Kitchen Counter Light ', '', $shortName);
                        $deviceClass = $sensor->attributes['device_class'] ?? null;
                    @endphp
                    <div class="card bg-base-100">
                        <div class="card-body p-4">
                            <p class="text-sm text-gray-500">{{ $shortName }}</p>
                            <p class="text-2xl font-bold text-white">
                                {{ $sensor->current_state }}
                                <span class="text-base text-gray-500">{{ $unit }}</span>
                            </p>
                            <p class="text-xs text-gray-600">
                                {{ optional($sensor->last_seen_at)->diffForHumans() ?? 'never' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No sensor data available.</p>
        @endif
    </div>
</body>

</html>