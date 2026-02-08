<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800">{{ config('app.name') }}</a>
            <div>
                <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-800 mx-2">Home</a>
                <a href="{{ route('device.overview') }}" class="text-gray-600 hover:text-gray-800 mx-2">Devices</a>
                <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-800 mx-2">Groups</a>
                <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-800 mx-2">Automations</a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>