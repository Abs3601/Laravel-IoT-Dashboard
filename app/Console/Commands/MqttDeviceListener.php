<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IoTEvent;
use App\Models\Device;
use PhpMqtt\Client\Facades\MQTT;

class MqttDeviceListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mqtt:listen-devices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for MQTT messages from all Home Assistant device types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Connecting to MQTT broker...');

        /** @var \PhpMqtt\Client\Contracts\MqttClient $mqtt */
        $mqtt = MQTT::connection();

        // Format: homeassistant/{entity_type}/{entity_id}/{attribute}
        $mqtt->subscribe('homeassistant/#', function (string $topic, string $message) {

            $parts = explode('/', $topic);

            if (count($parts) < 4) {
                return;
            }

            $entityType = $parts[1];
            $entityId = $parts[2];
            $attribute = $parts[3];

            if ($entityType === 'status' || $attribute === 'config') {
                return;
            }

            $this->info("Received: {$entityType}/{$entityId}/{$attribute} = " . substr($message, 0, 50));

            $existingDevice = Device::where('entity_type', $entityType)
                ->where('entity_id', $entityId)
                ->first();

            $existingAttributes = $existingDevice?->attributes ?? [];
            $parsedValue = $this->parseValue($message);
            $newAttributes = array_merge($existingAttributes, [$attribute => $parsedValue]);

            $updateData = [
                'attributes' => $newAttributes,
                'last_seen_at' => now(),
                'device_group' => $this->extractDeviceGroup($entityId),
            ];

            if ($attribute === 'state') {
                $updateData['current_state'] = is_string($parsedValue)
                    ? strtolower(trim($parsedValue))
                    : (string) $parsedValue;
            }

            if ($attribute === 'friendly_name') {
                $updateData['friendly_name'] = is_string($parsedValue)
                    ? $parsedValue
                    : null;
            }

            $device = Device::updateOrCreate(
                ['entity_type' => $entityType, 'entity_id' => $entityId],
                $updateData
            );

            if ($attribute === 'state') {
                IoTEvent::create([
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'state' => $device->current_state,
                    'attributes' => $device->attributes,
                    'created_at' => now(),
                ]);

                $this->info("  → State change logged: {$device->current_state}");
            }

        }, 0);

        $this->info('Listening for all device updates... (Press Ctrl+C to stop)');
        $mqtt->loop(true);
    }

    /**
     * Parse MQTT message value into appropriate PHP type.
     */
    private function parseValue(string $message): mixed
    {
        $trimmed = trim($message);

        if ($trimmed === 'null' || $trimmed === '') {
            return null;
        }

        if ($trimmed === 'true') {
            return true;
        }
        if ($trimmed === 'false') {
            return false;
        }

        if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        if (str_starts_with($trimmed, '"') && str_ends_with($trimmed, '"')) {
            return substr($trimmed, 1, -1);
        }

        if (is_numeric($trimmed)) {
            return str_contains($trimmed, '.') ? (float) $trimmed : (int) $trimmed;
        }

        return $trimmed;
    }

    /**
     * Extract the base device group from an entity ID.
     * Removes common sensor suffixes to group related entities together.
     */
    private function extractDeviceGroup(string $entityId): string
    {
        $suffixes = [
            '_voltage',
            '_current',
            '_power',
            '_signal_level',
            '_current_consumption',
            '_today_s_consumption',
            '_this_month_s_consumption',
            '_summation_delivered',
            '_auto_off_at',
            '_auto_off_enabled',
            '_led',
            '_cloud_connection',
            '_overheated',
            '_firmware',
            '_turn_off_in',
            '_start_up_behaviour',
            '_identify',
        ];

        foreach ($suffixes as $suffix) {
            if (str_ends_with($entityId, $suffix)) {
                return str_replace($suffix, '', $entityId);
            }
        }

        return $entityId;
    }
}
