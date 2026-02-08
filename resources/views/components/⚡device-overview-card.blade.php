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
        <div class="card bg-base-100 h-full hover:bg-base-200 transition-colors cursor-pointer">
            <div class="card-body flex flex-col h-full">
                <h2 class="card-title">{{ $device->entity_type }}</h2>
            </div>
        </div>
    @endforeach
</div>