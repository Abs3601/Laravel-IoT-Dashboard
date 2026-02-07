<x-navbar active='home'/>
<body>
    <div class="container mx-1 p-4">
        {{-- Plugs Section --}}
        @if($plugs->count() > 0)
            <h2 class="text-xl font-bold mb-4 mt-6">Plugs</h2>
            <livewire:plug-card :plugs="$plugs" />
        @endif

        {{-- Lights --}}
        @if($lights->count() > 0)
            <h2 class="text-xl font-bold mb-4 mt-6">Lights</h2>
            <livewire:light-card :lights="$lights" />
        @endif
    </div>

</body>


