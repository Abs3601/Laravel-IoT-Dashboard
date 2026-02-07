<x-navbar/>
<div>
    <h1>Welcome to dulMQTT!</h1>
    <p>To get started, please follow the instructions below:</p>
  
    <div class="container">
        <h2>Please set your MQTT Host Address</h2>
        <p>Enter the IP address of your MQTT broker in the input field below. This is typically the local IP address of the machine running the broker (e.g., 192.168.0.2).</p>
        <form action="{{ route('onboarding.store') }}" method="POST">
            @csrf
            <input type="text" name="mqtt_host" placeholder="Enter MQTT Host Address" class="border p-2 w-full mb-4" required>
            <input type="text" name="port" placeholder="Enter MQTT Port " class="border p-2 w-full mb-4" required>
            <input type="text" name="mqtt_auth_username" placeholder="Enter MQTT Username" class="border p-2 w-full mb-4" required>
            <input type="text" name="mqtt_auth_password" placeholder="Enter MQTT Password" class="border p-2 w-full mb-4" required>
            <input type="text" name="mqtt_client_id" placeholder="Enter MQTT Client ID" class="border p-2 w-full mb-4" required>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
        </form>
    </div>
</div>