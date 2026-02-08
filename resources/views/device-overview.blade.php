<x-navbar active='home'/>

    <div class="container mx-1 p-4">
        @if ($devices->where('entity_type', 'switch')->count() > 0)
            <h2 class="text-xl font-bold mb-4 mt-6">Plugs</h2>
            <livewire:device-overview-card :devices="$devices" />
        @endif
    </div>