<?php

use Livewire\Component;

new class extends Component
{
    public $devices;

    public function mount($devices)
    {
        $this->devices = $devices;
    }
};
?>

<div class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(300px,1fr))]">
    @foreach($devices as $device)
        @php
            $componentName = 'device-card-' . $device->entity_type;
            $voltComponentPath = 'components.⚡' . $componentName;
        @endphp

        <div class="w-full">
            @if (view()->exists($voltComponentPath))
                @livewire($componentName, ['device' => $device], key($device->entity_id))
            @else
                @livewire('device-card-generic', ['device' => $device], key($device->entity_id))
            @endif
        </div>
    @endforeach
</div>



