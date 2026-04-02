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
    <nav class="bg-white dark:bg-gray-800 shadow transition-colors relative z-50" x-data="{ open: false }">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
            <div class="flex justify-between items-center">
                <a href="{{ route('home') }}" class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white tracking-tight flex-shrink-0">
                    {{ config('app.name') }}
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-brand dark:hover:text-white transition-colors">Home</a>
                    <a href="{{ route('device.overview') }}" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-brand dark:hover:text-white transition-colors">Devices</a>
                    <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-brand dark:hover:text-white transition-colors">Groups</a>
                    <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-brand dark:hover:text-white transition-colors">Automations</a>
                    <a href="{{ route('stats') }}" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-brand dark:hover:text-white transition-colors">Stats</a>
                    
                    <button class="theme-toggle p-2 rounded-md focus:outline-none hover:text-gray-900 dark:hover:text-white transition-colors text-gray-500 dark:text-gray-400">
                        <svg class="theme-toggle-dark-icon hidden w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 3.364a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zm-3.364 4.22l-.707-.707a1 1 0 011.414-1.414l.707.707a1 1 0 01-1.414 1.414zM10 18a1 1 0 01-1-1v-1a1 1 0 112 0v1a1 1 0 01-1 1zm-4.22-3.364l.707-.707a1 1 0 011.414 1.414l-.707.707a1 1 0 01-1.414-1.414zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zm3.364-4.22a1 1 0 011.414 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 010-1.414zM10 5a5 5 0 100 10 5 5 0 000-10z"></path>
                        </svg>
                        <svg class="theme-toggle-light-icon hidden w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>
                </div>

                <!-- Mobile Menu Button & Theme Toggle -->
                <div class="flex md:hidden items-center gap-3">
                    <button class="theme-toggle p-2 rounded-md focus:outline-none hover:text-gray-900 dark:hover:text-white transition-colors text-gray-500 dark:text-gray-400">
                        <svg class="theme-toggle-dark-icon hidden w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 3.364a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zm-3.364 4.22l-.707-.707a1 1 0 011.414-1.414l.707.707a1 1 0 01-1.414 1.414zM10 18a1 1 0 01-1-1v-1a1 1 0 112 0v1a1 1 0 01-1 1zm-4.22-3.364l.707-.707a1 1 0 011.414 1.414l-.707.707a1 1 0 01-1.414-1.414zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zm3.364-4.22a1 1 0 011.414 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 010-1.414zM10 5a5 5 0 100 10 5 5 0 000-10z"></path>
                        </svg>
                        <svg class="theme-toggle-light-icon hidden w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>
                    
                    <button @click="open = !open" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:outline-none transition-colors" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-7 w-7" x-show="!open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="block h-7 w-7 hidden" :class="{'hidden': !open, 'block': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu Dropdown -->
            <div x-show="open" 
                x-transition:enter="transition ease-out duration-100 transform" 
                x-transition:enter-start="opacity-0 scale-95" 
                x-transition:enter-end="opacity-100 scale-100" 
                x-transition:leave="transition ease-in duration-75 transform" 
                x-transition:leave-start="opacity-100 scale-100" 
                x-transition:leave-end="opacity-0 scale-95" 
                class="md:hidden mt-4 Absolute z-50 w-full"
                id="mobile-menu"
                style="display: none;">
                <div class="px-2 pt-2 pb-5 space-y-2 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <a href="{{ route('home') }}" class="block px-4 py-3 rounded-lg text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 hover:text-brand dark:hover:text-white transition-colors">Home</a>
                    <a href="{{ route('device.overview') }}" class="block px-4 py-3 rounded-lg text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 hover:text-brand dark:hover:text-white transition-colors">Devices</a>
                    <a href="{{ route('home') }}" class="block px-4 py-3 rounded-lg text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 hover:text-brand dark:hover:text-white transition-colors">Groups</a>
                    <a href="{{ route('home') }}" class="block px-4 py-3 rounded-lg text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 hover:text-brand dark:hover:text-white transition-colors">Automations</a>
                    <a href="{{ route('stats') }}" class="block px-4 py-3 rounded-lg text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 hover:text-brand dark:hover:text-white transition-colors">Stats</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="w-full pt-8 pb-24 md:pt-10 md:pb-32">
        {{ $slot }}
    </main>

    <script>
        function updateThemeIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            document.querySelectorAll('.theme-toggle-dark-icon').forEach(el => {
                if (isDark) el.classList.remove('hidden');
                else el.classList.add('hidden');
            });
            document.querySelectorAll('.theme-toggle-light-icon').forEach(el => {
                if (!isDark) el.classList.remove('hidden');
                else el.classList.add('hidden');
            });
        }

        updateThemeIcons();

        // Attach event listeners to all theme toggle buttons
        document.querySelectorAll('.theme-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const isCurrentlyDark = document.documentElement.classList.contains('dark');
                
                if (isCurrentlyDark) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
                
                updateThemeIcons();
            });
        });
    </script>
    @livewireScripts
</body>
</html>