<x-navbar active='home'/>
<body>
    <div class="container mx-1 p-4">
        <h1 class="text-2xl font-bold mb-4">Welcome to {{ config('app.name') }}</h1>
        <p class="text-gray-700 mb-6">Manage your smart home devices with ease. Use the navigation above to view and control your devices, groups, and automations.</p>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Getting Started</h2>
            <p class="text-gray-700 mb-4">To get started, add your smart home devices and create groups or automations to make your life easier.</p>
            <a href="{{ route('devices.all') }}" class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">View Devices</a>
        </div>
    </div>

</body>


