<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IoTEvent;
use PhpMqtt\Client\Facades\MQTT;


class MqttLightListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mqtt-light-listener';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Connecting to MQTT broker...');


        /** @var \PhpMqtt\Client\Contracts\MqttClient $mqtt */
        $mqtt = MQTT::connection();

        $mqtt->subscribe('homeassistant/light/+/state', function (string $topic, string $message) {

            $parts = explode('/', $topic);

            if (count($parts) < 4) {
                return;
            }

            $entityType = $parts[1];
            $entityId = $parts[2];

            $state = strtolower(trim($message));

            IoTEvent::create([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'state' => $state,
                'recorded_at' => now(),
            ]);

            $this->info("Stored: {$entityType} {$entityId} = {$state}");
        }, 0);

        $this->info('Listening for light state updates...');
        $mqtt->loop(true);
    }
}
