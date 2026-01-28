<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Home</title>
</head>
<body>
    <h1>Welcome to the epicness</h1>

     <div class="max-w-2xl mx-auto">
            @foreach($devices as $device)
            <div class="card bg-base-100 shadow mt-8">
                <div class="card-body">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $device['entity_type'] }}</h1>
                        <p class="mt-4 text-base-content/60">{{ $device['entity_id'] }}</p>
                        <div class="text-sm text-gray-500 mt-2">{{ $device['current_state'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
</body>
</html>