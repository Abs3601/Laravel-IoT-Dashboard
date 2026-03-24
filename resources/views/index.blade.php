<x-navbar active='home'/>

<div class="max-w-6xl mx-auto space-y-8">

    {{-- Hero / Welcome Section --}}
    <div class="py-12 px-6">
        <div class="text-center mx-auto max-w-lg">
            <h1 class="text-4xl font-bold dark:text-white">{{ config('app.name') }}</h1>
            <p class="py-4 text-base-content/70 dark:text-white">
                Your smart home at a glance. Monitor devices, manage groups, and automate your space.
            </p>
        </div>
    </div>

    {{-- Quick Access Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <a href="{{ route('device.overview') }}" class="card bg-card-light dark:bg-card-dark rounded-3xl overflow-hidden shadow-sm hover:shadow-md hover:scale-[1.02] hover:brightness-110 transition-all duration-300 cursor-pointer group border border-transparent dark:border-gray-700">
            <div class="card-body p-6 items-center text-center text-white">
                <h2 class="card-title">Devices</h2>
                <p class="text-gray-400 text-sm">Browse and manage all your connected devices</p>
            </div>
        </a>

        <div class="card bg-card-light dark:bg-card-dark border border-transparent dark:border-gray-700 rounded-3xl overflow-hidden shadow-sm opacity-60">
            <div class="card-body p-6 items-center text-center text-white">
                <h2 class="card-title">Groups</h2>
                <p class="text-gray-400 text-sm">Organise devices into rooms and zones</p>
            </div>
        </div>

        <div class="card bg-card-light dark:bg-card-dark border border-transparent dark:border-gray-700 rounded-3xl overflow-hidden shadow-sm opacity-60">
            <div class="card-body p-6 items-center text-center text-white">
                <h2 class="card-title">Automations</h2>
                <p class="text-gray-400 text-sm">Create rules to automate your home</p>
            </div>
        </div>

    </div>

    {{-- Pinned Devices Dashboard --}}
    <div>
        <h2 class="text-2xl font-bold mb-4 ml-1 dark:text-white">Pinned Devices</h2>
        <livewire:pinned-devices />
    </div>

    {{-- Recent Activity / Stats Section --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card bg-card-light dark:bg-card-dark border border-transparent dark:border-gray-700 rounded-3xl overflow-hidden shadow-sm">
            <div class="card-body p-6 text-white">
                <h2 class="card-title text-lg">Quick Stats</h2>
                <div class="stats stats-vertical lg:stats-horizontal w-full text-white mt-2">
                    <div class="stat">
                        <div class="stat-title text-gray-300">Total Devices</div>
                        <div class="stat-value text-primary">{{ \App\Models\Device::distinct('entity_id')->count('entity_id') }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title text-gray-300">Device Types</div>
                        <div class="stat-value text-secondary">{{ \App\Models\Device::distinct('entity_type')->count('entity_type') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-card-light dark:bg-card-dark border border-transparent dark:border-gray-700 rounded-3xl overflow-hidden shadow-sm">
            <div class="card-body p-6 text-white">
                <h2 class="card-title text-lg">Recent Activity</h2>
                @php
                    $recentDevices = \App\Models\Device::whereNotNull('last_seen_at')
                        ->where('entity_id', 'NOT LIKE', '%browser%')
                        ->latest('last_seen_at')
                        ->take(5)
                        ->get();
                @endphp
                @if($recentDevices->isEmpty())
                    <p class="text-gray-400 text-sm">No recent activity yet.</p>
                @else
                    <ul class="space-y-2 mt-2">
                        @foreach($recentDevices as $d)
                            <li class="flex justify-between items-center text-sm border-b border-white/10 pb-2 last:border-0 last:pb-0">
                                <span class="font-medium">{{ $d->friendly_name ?? str_replace('_', ' ', ucfirst($d->entity_id)) }}</span>
                                <span class="text-gray-400">{{ $d->last_seen_at->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

</div>
