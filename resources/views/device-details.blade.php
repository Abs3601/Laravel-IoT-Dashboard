<x-navbar active='home'/>

    <div class="container mx-1 p-4">
        @if ($devices->where('entity_type')->count() > 0)
            <h2 class="text-xl font-bold mb-4 mt-6">Devices</h2>
            <livewire:device-details-card :devices="$devices" />
        @endif
    </div>