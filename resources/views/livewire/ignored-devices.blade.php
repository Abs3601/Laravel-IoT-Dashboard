<div>
@if($devices->isNotEmpty())
<div class="mt-12 mb-8">
    <h2 class="text-xl font-bold mb-4 text-gray-400 border-b pb-2">Ignored Background Entities</h2>
    <p class="text-sm text-gray-500 mb-4">
        These are background services, scripts, automations, or internal Home Assistant entities that were automatically ignored to keep your dashboard clean. 
    </p>

    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
        @foreach($devices as $device)
            <div class="card bg-base-200/50 outline outline-1 outline-base-300 p-4 rounded-xl flex flex-row items-center justify-between" wire:key="ignored-{{ $device->id }}">
                <div class="flex-1 min-w-0 mr-4">
                    <h3 class="font-semibold text-sm truncate">{{ $device->friendly_name ?? str_replace('_', ' ', ucfirst($device->entity_id)) }}</h3>
                    <p class="text-xs text-gray-500 truncate">Domain: <span class="badge badge-sm badge-ghost">{{ $device->entity_type }}</span></p>
                </div>
                <button wire:click="restoreDevice({{ $device->id }})" class="btn btn-sm btn-outline">
                    Show on UI
                </button>
            </div>
        @endforeach
    </div>
</div>
@endif
</div>
