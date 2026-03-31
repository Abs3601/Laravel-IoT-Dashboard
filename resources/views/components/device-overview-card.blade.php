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
            <div class="card bg-card-light dark:bg-card-dark rounded-3xl shadow-md overflow-hidden h-full hover:scale-[1.02] hover:brightness-110 transition-all duration-300 ease-in-out cursor-pointer border border-transparent dark:border-gray-700">
                <a href="{{ route('device.details', ['type' => $device->entity_type]) }}" class="card-body p-6 flex flex-col h-full">
                    <h2 class="card-title text-white">{{ ucfirst(str_replace('_', ' ', $device->entity_type)) }}</h2>
                </a>
            </div>
        @endforeach
</div>