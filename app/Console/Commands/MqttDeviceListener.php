<?php

namespace App\Console\Commands;

use App\Events\DeviceUpdated;
use Illuminate\Console\Command;
use App\Models\IoTEvent;
use App\Models\Device;
use Illuminate\Support\Facades\Cache;
use PhpMqtt\Client\Facades\MQTT;
use App\Models\Setting;

class MqttDeviceListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mqtt:listen-devices {--qos=0 : MQTT QoS level for subscription (0 or 1)} {--test : Connect to localhost and only listen to loadtest/ topics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for MQTT messages and dynamically discover all device types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $testMode = $this->option('test');
        $qos = (int) $this->option('qos');

        if ($testMode) {
            $this->info('TEST MODE — connecting to localhost, listening to loadtest/# only');
            
            $this->info('Clearing old load test data from the database...');
            \App\Models\IoTEvent::where('entity_type', 'loadtest')->delete();
            \App\Models\Device::where('entity_type', 'loadtest')->delete();
            $this->info('Clean up finished! Ready for a fresh test.');

            config([
                'mqtt-client.connections.default.host' => 'localhost',
                'mqtt-client.connections.default.port' => 1883,
                'mqtt-client.connections.default.client_id' => 'laravel_load_test_listener',
                'mqtt-client.connections.default.connection_settings.auth.username' => null,
                'mqtt-client.connections.default.connection_settings.auth.password' => null,
                'mqtt-client.connections.default.connection_settings.keep_alive_interval' => 600,
            ]);
        } else {
            if (!Setting::get('onboarding_completed')) {
                $this->error('Onboarding has not been completed yet.');
                $this->info('Please visit the web dashboard to complete the onboarding process and configure your MQTT broker settings.');
                return 1;
            }

            $this->info('Connecting to MQTT broker...');
            config([
                'mqtt-client.connections.default.host' => Setting::get('mqtt_host'),
                'mqtt-client.connections.default.port' => Setting::get('port'),
                'mqtt-client.connections.default.client_id' => Setting::get('mqtt_client_id'),
                'mqtt-client.connections.default.connection_settings.auth.username' => Setting::get('mqtt_auth_username') ?: null,
                'mqtt-client.connections.default.connection_settings.auth.password' => Setting::get('mqtt_auth_password') ?: null,
                'mqtt-client.connections.default.connection_settings.keep_alive_interval' => 600,
            ]);
        }

        $mqtt = MQTT::connection();
        $pendingBroadcasts = [];

        $subscribeTopic = $testMode ? 'loadtest/#' : '#';
        $this->info("Subscribing to '{$subscribeTopic}' at QoS {$qos}...");

        $mqtt->subscribe($subscribeTopic, function (string $topic, string $message) use (&$pendingBroadcasts) {
            $parts = explode('/', $topic);
            if (count($parts) < 2) return;

            $parsed = $this->parseTopic($parts);
            if ($parsed === null) return;

            $entityType = $parsed['entity_type'];
            $entityId = $parsed['entity_id'];
            $attribute = $parsed['attribute'];

            $this->info("Received: {$entityType}/{$entityId}/{$attribute} = " . substr($message, 0, 50));

            $existingDevice = Device::where('entity_type', $entityType)
                ->where('entity_id', $entityId)
                ->first();

            $existingAttributes = $existingDevice?->attributes ?? [];
            $parsedValue = $this->parseValue($message);

            // Calculate end-to-end latency if payload contains a load-test timestamp
            $latencyMs = null;
            if (is_array($parsedValue) && isset($parsedValue['_test_ts'])) {
                $latencyMs = round((microtime(true) - (float) $parsedValue['_test_ts']) * 1000, 0);
            }

            $newAttributes = array_merge($existingAttributes, [$attribute => $parsedValue]);
            $deviceGroup = $existingDevice?->device_group ?: $this->extractDeviceGroup($entityId, $entityType);

            $updateData = [
                'attributes' => $newAttributes,
                'last_seen_at' => now(),
                'device_group' => $deviceGroup,
            ];

            if ($attribute === 'state') {
                $updateData['current_state'] = is_string($parsedValue) ? strtolower(trim($parsedValue)) : (string) $parsedValue;
            }

            // For JSON payloads (Zigbee2MQTT, Tasmota), extract individual attributes
            if ($attribute === 'payload' && is_array($parsedValue)) {
                if (isset($parsedValue['state'])) {
                    $updateData['current_state'] = strtolower(trim((string) $parsedValue['state']));
                }
                $newAttributes = array_merge($existingAttributes, $parsedValue);
                $updateData['attributes'] = $newAttributes;
            }

            if (isset($parsedValue['friendly_name'])) {
                $updateData['friendly_name'] = $parsedValue['friendly_name'];
            } elseif ($attribute === 'friendly_name') {
                $updateData['friendly_name'] = is_string($parsedValue) ? $parsedValue : null;
            }

            // Home Assistant MQTT Discovery
            if ($attribute === 'config' && is_array($parsedValue)) {
                if (isset($parsedValue['command_topic'])) $newAttributes['command_topic'] = $parsedValue['command_topic'];
                if (isset($parsedValue['payload_on'])) $newAttributes['payload_on'] = $parsedValue['payload_on'];
                if (isset($parsedValue['payload_off'])) $newAttributes['payload_off'] = $parsedValue['payload_off'];
                if (isset($parsedValue['name'])) $updateData['friendly_name'] = $parsedValue['name'];
                $updateData['attributes'] = $newAttributes;
            }

            $device = Device::updateOrCreate(
                ['entity_type' => $entityType, 'entity_id' => $entityId],
                $updateData
            );

            // Leading/trailing edge broadcast debounce
            $cacheKey = "broadcast:{$entityType}:{$entityId}";
            if (!Cache::has($cacheKey)) {
                DeviceUpdated::dispatch($device);
                Cache::put($cacheKey, true, now()->addSecond());
                $this->info("  -> Broadcast (leading): {$entityId}");
                unset($pendingBroadcasts["{$entityType}:{$entityId}"]);
            } else {
                $pendingBroadcasts["{$entityType}:{$entityId}"] = [
                    'device' => $device,
                    'queued_at' => microtime(true),
                ];
            }

            foreach ($pendingBroadcasts as $key => $pending) {
                $elapsed = microtime(true) - $pending['queued_at'];
                if ($elapsed >= 1.0) {
                    $freshDevice = Device::find($pending['device']->id);
                    if ($freshDevice) {
                        DeviceUpdated::dispatch($freshDevice);
                        $this->info("  -> Broadcast (trailing): {$freshDevice->entity_id}");
                    }
                    unset($pendingBroadcasts[$key]);
                }
            }

            $stateChanged = ($updateData['current_state'] ?? null) !== null;
            $attributesChanged = $existingAttributes !== $newAttributes;

            if ($stateChanged || $attributesChanged) {
                IoTEvent::create([
                    'entity_type' => $entityType,
                    'entity_id'   => $entityId,
                    'state'       => $device->current_state,
                    'attributes'  => $device->attributes,
                    'latency_ms'  => $latencyMs,
                    'created_at'  => now(),
                ]);

                $this->info("  -> Event logged: " . ($stateChanged ? "state={$device->current_state}" : "attributes updated") . ($latencyMs !== null ? " (latency: {$latencyMs}ms)" : ''));
            }
        }, $qos);

        $this->info('Listening for all device updates... (Press Ctrl+C to stop)');
        $mqtt->loop(true);
    }

    /**
     * Dynamically parse an MQTT topic into entity_type, entity_id, and attribute.
     *
     * @return array{entity_type: string, entity_id: string, attribute: string}|null
     */
    private function parseTopic(array $parts): ?array
    {
        $prefix = strtolower($parts[0]);

        // Ignore plex!!!! AHHHHHH!!!
        if (str_contains(strtolower(implode('/', $parts)), 'plex')) {
            return null;
        }

        // Home Assistant discovery format
        // homeassistant/{entity_type}/{entity_id}/{attribute}
        if ($prefix === 'homeassistant') {
            if (count($parts) < 4) {
                return null;
            }
            if ($parts[1] === 'status') {
                return null;
            }

            // Filter out HA topics we dont want
            $allowedDomains = [
                'light', 'switch', 'sensor', 'binary_sensor', 'button', 'fan', 
                'cover', 'climate', 'lock', 'alarm_control_panel', 'number', 'select', 'siren'
            ];

            $updateData['is_hidden'] = !in_array($parts[1], $allowedDomains);

            return [
                'entity_type' => $parts[1],
                'entity_id'   => $parts[2],
                'attribute'   => $parts[3],
            ];
        }

        // Zigbee2MQTT format
        // zigbee2mqtt/{device_name}          JSON payload with state + attributes
        // zigbee2mqtt/{device_name}/set      command (ignore)
        // zigbee2mqtt/{device_name}/get      request (ignore)
        // zigbee2mqtt/bridge/{...}           bridge info (ignore)
        if ($prefix === 'zigbee2mqtt') {
            if (count($parts) < 2 || $parts[1] === 'bridge') {
                return null;
            }
            // Ignore /set and /get command topics
            if (count($parts) >= 3 && in_array(strtolower($parts[count($parts) - 1]), ['set', 'get'])) {
                return null;
            }
            return [
                'entity_type' => 'zigbee',
                'entity_id'   => $parts[1],
                'attribute'   => 'payload',
            ];
        }

        // Tasmota format
        // tele/{device}/STATE|SENSOR|LWT|INFO...
        // stat/{device}/RESULT|POWER|...
        // cmnd/{device}/...  commands (ignore)
        if (in_array($prefix, ['tele', 'stat', 'cmnd'])) {
            if (count($parts) < 3) {
                return null;
            }
            if ($prefix === 'cmnd') {
                return null; // ignore outgoing commands
            }
            $attr = strtolower($parts[2]);
            // LWT (Last Will and Testament) is availability, not a device update
            if ($attr === 'lwt') {
                return null;
            }
            return [
                'entity_type' => 'tasmota',
                'entity_id'   => $parts[1],
                'attribute'   => $attr,
            ];
        }

        // ESPHome format
        // esphome/{device}/{sensor_type}/state
        if ($prefix === 'esphome') {
            if (count($parts) < 3) {
                return null;
            }
            $attribute = count($parts) >= 4 ? $parts[3] : 'payload';
            return [
                'entity_type' => 'esphome',
                'entity_id'   => $parts[1] . '_' . $parts[2],
                'attribute'   => $attribute,
            ];
        }

        // Shelly format
        // shellies/{device_id}/{component}/{index}/{property}
        // shellyplus-{id}/status/{component}:{index}
        if ($prefix === 'shellies' || str_starts_with($prefix, 'shelly')) {
            if (count($parts) < 3) {
                return null;
            }
            return [
                'entity_type' => 'shelly',
                'entity_id'   => $parts[1] ?? $parts[0],
                'attribute'   => implode('_', array_slice($parts, 2)),
            ];
        }

        // Generic fallback: 3+ segments
        // {prefix}/{type_or_id}/{id_or_attr}/{...}
        if (count($parts) >= 4) {
            return [
                'entity_type' => $parts[1],
                'entity_id'   => $parts[2],
                'attribute'   => implode('_', array_slice($parts, 3)),
            ];
        }

        // Generic fallback: 3 segments
        // {prefix}/{id}/{attribute}
        if (count($parts) === 3) {
            return [
                'entity_type' => $prefix,
                'entity_id'   => $parts[1],
                'attribute'   => $parts[2],
            ];
        }

        // Generic fallback: 2 segments
        // {prefix}/{id} expects JSON payload
        if (count($parts) === 2) {
            return [
                'entity_type' => $prefix,
                'entity_id'   => $parts[1],
                'attribute'   => 'payload',
            ];
        }

        return null;
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
     * Accurately determine the device group based on Home Assistant parent/child naming conventions. (Will need to test with other formats)
     */
    private function extractDeviceGroup(string $entityId, string $entityType): string
    {
        $primaryTypes = ['light', 'switch', 'fan', 'climate', 'media_player', 'cover', 'lock', 'siren', 'button', 'number', 'input_boolean', 'input_select'];
        
        if (in_array($entityType, $primaryTypes)) {
            Device::where('entity_id', 'LIKE', $entityId . '_%')
                ->where('device_group', '!=', $entityId)
                ->update(['device_group' => $entityId]);
                
            return $entityId;
        }

        $parentDevice = Device::whereIn('entity_type', $primaryTypes)
            ->whereRaw("? LIKE entity_id || '_%'", [$entityId])
            ->orderByRaw('LENGTH(entity_id) DESC')
            ->first();

        if ($parentDevice) {
            return $parentDevice->entity_id;
        }
        return $entityId;
    }
}
