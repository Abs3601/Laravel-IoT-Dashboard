
<div>
    <div class="card bg-base-100 h-full">
    <div class="card-body flex flex-col h-full">
        <div class="flex items-start">
            <div class="flex-1">
                <h1 class="text-2xl font-semibold">
                    {{ $device->friendly_name ?? str_replace('_', ' ', ucfirst($device->entity_id)) }}
                </h1>
                <p class="text-base font-normal">{{ ucfirst($device->current_state) }}</p>
                <p class="text-sm font-light text-gray-500">
                    Last Update: {{ optional($device->last_seen_at)->diffForHumans() ?? 'never' }}
                </p>
            </div>
            <div class="ml-auto">
                {{-- <img src="{{ URL::asset('/images/Plug.svg') }}" alt="device icon" class="w-15 h-auto"> --}}
            </div>
        </div>
    </div>
</div>

</div>