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
    <div class="container mx-1 p-4">
        {{-- Plugs Section --}}
        @if($plugs->count() > 0)
            <h2 class="text-xl font-bold mb-4 mt-6">Plugs</h2>
            <livewire:plug-card :plugs="$plugs" />
        @endif

        {{-- Lights --}}
        @if($lights->count() > 0)
            <h2 class="text-xl font-bold mb-4 mt-6">Lights</h2>
            <livewire:light-card :lights="$lights" />
        @endif
    </div>

</body>

</html>