<x-navbar active='home'/>

<div class="max-w-6xl mx-auto space-y-8">

    {{-- Hero / Welcome Section --}}
    <div class="py-12 px-6">
        <div class="text-center mx-auto max-w-lg">
            <h1 class="text-4xl font-bold">{{ config('app.name') }}</h1>
            <p class="py-4 text-base-content/70">
                Your smart home at a glance. Monitor devices, manage groups, and automate your space.
            </p>
        </div>
    </div>

    {{-- Quick Access Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <a href="{{ route('device.overview') }}" class="card bg-base-100 border-base-300 hover:shadow-md transition-shadow cursor-pointer group">
            <div class="card-body items-center text-center">
                <h2 class="card-title">Devices</h2>
                <p class="text-base-content/60 text-sm">Browse and manage all your connected devices</p>
            </div>
        </a>

        <div class="card bg-base-100 border-base-300 opacity-60">
            <div class="card-body items-center text-center">
                <h2 class="card-title">Groups</h2>
                <p class="text-base-content/60 text-sm">Organise devices into rooms and zones</p>
            </div>
        </div>

        <div class="card bg-base-100 border-base-300 opacity-60">
            <div class="card-body items-center text-center">
                <h2 class="card-title">Automations</h2>
                <p class="text-base-content/60 text-sm">Create rules to automate your home</p>
            </div>
        </div>

    </div>

    {{-- Pinned Devices Placeholder --}}
    <div class="card bg-base-100 border-base-300">
        <div class="card-body">
            <h2 class="card-title text-lg">Pinned Devices</h2>
            <p class="text-base-content/60 text-sm">Pin your most‑used devices here for quick access.</p>
            <div class="flex flex-wrap gap-4 mt-4">
                <div class="border-2 border-dashed border-base-300 rounded-xl w-40 h-28 flex items-center justify-center text-base-content/30 text-sm">
                    + Pin a device
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity / Stats Section --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card bg-base-100 border-base-300">
            <div class="card-body">
                <h2 class="card-title text-lg">Quick Stats</h2>
                <div class="stats stats-vertical lg:stats-horizontal w-full">
                    <div class="stat">
                        <div class="stat-title">Total Devices</div>
                        <div class="stat-value text-primary">{{ \App\Models\Device::distinct('entity_id')->count('entity_id') }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Device Types</div>
                        <div class="stat-value text-secondary">{{ \App\Models\Device::distinct('entity_type')->count('entity_type') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border-base-300">
            <div class="card-body">
                <h2 class="card-title text-lg">Recent Activity</h2>
                @php
                    $recentDevices = \App\Models\Device::whereNotNull('last_seen_at')
                        ->where('entity_id', 'NOT LIKE', '%browser%')
                        ->latest('last_seen_at')
                        ->take(5)
                        ->get();
                @endphp
                @if($recentDevices->isEmpty())
                    <p class="text-base-content/60 text-sm">No recent activity yet.</p>
                @else
                    <ul class="space-y-2 mt-2">
                        @foreach($recentDevices as $d)
                            <li class="flex justify-between items-center text-sm">
                                <span class="font-medium">{{ $d->friendly_name ?? str_replace('_', ' ', ucfirst($d->entity_id)) }}</span>
                                <span class="text-base-content/50">{{ $d->last_seen_at->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

</div>
