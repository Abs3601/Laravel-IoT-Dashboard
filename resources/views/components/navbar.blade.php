<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-200">
    <nav class="bg-white dark:bg-gray-800 shadow transition-colors">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800 dark:text-white">{{ config('app.name') }}</a>
            <div>
                <a href="{{ route('home') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white mx-2 transition-colors">Home</a>
                <a href="{{ route('device.overview') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white mx-2 transition-colors">Devices</a>
                <a href="{{ route('home') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white mx-2 transition-colors">Groups</a>
                <a href="{{ route('home') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white mx-2 transition-colors">Automations</a>
                
                <button id="theme-toggle" class="p-2 ml-4 rounded-md focus:outline-none hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors hidden sm:inline-flex align-middle">
                    <svg id="theme-toggle-light-icon" class="hidden w-5 h-5 text-gray-800 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 3.364a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zm-3.364 4.22l-.707-.707a1 1 0 011.414-1.414l.707.707a1 1 0 01-1.414 1.414zM10 18a1 1 0 01-1-1v-1a1 1 0 112 0v1a1 1 0 01-1 1zm-4.22-3.364l.707-.707a1 1 0 011.414 1.414l-.707.707a1 1 0 01-1.414-1.414zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zm3.364-4.22a1 1 0 011.414 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 010-1.414zM10 5a5 5 0 100 10 5 5 0 000-10z"></path>
                    </svg>
                    <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5 text-gray-800 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-6">
        {{ $slot }}
    </main>

    <script>
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');

        themeToggleBtn.addEventListener('click', function() {
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            if (localStorage.getItem('color-theme')) {
                if (localStorage.getItem('color-theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                }
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
            }
        });
    </script>
    @livewireScripts
</body>
</html>