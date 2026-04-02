<x-navbar/>

<div class="max-w-2xl mx-auto mt-12 mb-12">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm rounded-xl overflow-hidden transition-colors">
        
        <div class="p-8 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Welcome to {{ config('app.name') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                To get started, please configure your MQTT broker connection details below. If you are unsure, these details can usually be found in your MQTT add-on or broker configuration.
            </p>
        </div>

        <form action="{{ route('onboarding.store') }}" method="POST" class="p-8 space-y-6">
            @csrf
            
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="md:col-span-2">
                        <label for="mqtt_host" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">MQTT Host Address</label>
                        <input type="text" id="mqtt_host" name="mqtt_host" placeholder="e.g. 192.168.1.100" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors" required>
                    </div>
                
                    <div class="md:col-span-1">
                        <label for="port" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Port</label>
                        <input type="text" id="port" name="port" placeholder="1883" value="1883" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="mqtt_auth_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">MQTT Username</label>
                        <input type="text" id="mqtt_auth_username" name="mqtt_auth_username" placeholder="MQTT Username" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors" required>
                    </div>

                    <div>
                        <label for="mqtt_auth_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">MQTT Password</label>
                        <input type="password" id="mqtt_auth_password" name="mqtt_auth_password" placeholder="••••••••" autocomplete="new-password" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors" required>
                    </div>
                </div>

                <div>
                    <label for="mqtt_client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client ID</label>
                    <input type="text" id="mqtt_client_id" name="mqtt_client_id" placeholder="e.g. dulmqtt_dashboard" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors" required>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">A unique identifier for this application instance to connect to the broker.</p>
                </div>
                <div class="mt-4 border-t border-gray-100 dark:border-gray-700 pt-5">
                    <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
                    <select id="timezone" name="timezone" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors" required>
                        @foreach(timezone_identifiers_list() as $tz)
                            <option value="{{ $tz }}" {{ $tz === 'Europe/London' ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Select your local timezone to ensure accurate dashboard timelines.</p>
                </div>
            </div>

            <div class="pt-6 mt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <button type="submit" class="inline-flex justify-center items-center rounded-md border border-transparent bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all active:scale-95">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>