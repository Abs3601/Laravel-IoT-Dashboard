<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body>
            @foreach($devices as $device)
            <div class="card bg-base-100 shadow mt-8">
                <div class="card-body">
                    <div class="card-container">
                        <h1 class=" text-3xl font-bold">{{ $device['entity_type'] }}</h1>
                        <p class="mt-4 text-base-content/60">{{ $device['entity_id'] }}</p>
                        <div class="text-sm text-gray-500 mt-2">{{ $device['current_state'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

    </body>
</html>
