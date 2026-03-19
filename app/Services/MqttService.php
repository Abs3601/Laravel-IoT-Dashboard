<?php

namespace App\Services;

use App\Models\Device;
use PhpMqtt\Client\Facades\MQTT;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class MqttService
{
    /**
     * Send a command to a specific device.
     */
    public function sendCommand(Device $device, mixed $state): void
    {
        $topic = $this->getCommandTopic($device);
        $payload = $this->getCommandPayload($device, $state);

        if (!$topic) {
            Log::warning("Could not determine command topic for device: {$device->entity_id} (Type: {$device->entity_type})");
            return;
        }

        try {
            // Retrieve MQTT connection settings exactly like the listener does
            config([
                'mqtt-client.connections.default.host' => Setting::get('mqtt_host'), 
                'mqtt-client.connections.default.port' => Setting::get('port'), 
                'mqtt-client.connections.default.client_id' => Setting::get('mqtt_client_id') . '_publisher', 
                'mqtt-client.connections.default.auth.username' => Setting::get('mqtt_auth_username'), 
                'mqtt-client.connections.default.auth.password' => Setting::get('mqtt_auth_password')
            ]);

            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $payload, 0);
            Log::info("Published MQTT Command -> Topic: {$topic}, Payload: {$payload}");
            
        } catch (\Exception $e) {
            Log::error("Failed to publish MQTT message: " . $e->getMessage());
        }
    }

    /**
     * Send a brightness command to a device.
     */
    public function sendBrightness(Device $device, int $brightness): void
    {
        $topic = $this->getCommandTopic($device, 'set_brightness');
        if (!$topic) return;

        try {
            config([
                'mqtt-client.connections.default.host' => Setting::get('mqtt_host'), 
                'mqtt-client.connections.default.port' => Setting::get('port'), 
                'mqtt-client.connections.default.client_id' => Setting::get('mqtt_client_id') . '_publisher', 
                'mqtt-client.connections.default.auth.username' => Setting::get('mqtt_auth_username'), 
                'mqtt-client.connections.default.auth.password' => Setting::get('mqtt_auth_password')
            ]);
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, (string) $brightness, 0);
            Log::info("Published Brightness -> Topic: {$topic}, Payload: {$brightness}");
        } catch (\Exception $e) {
            Log::error("Failed to publish brightness: " . $e->getMessage());
        }
    }

    /**
     * Determine the correct command topic based on device type.
     */
    protected function getCommandTopic(Device $device, string $suffix = 'set'): ?string
    {
        // Home Assistant Discovery (prioritize attribute if exists)
        if (!empty($device->attributes['command_topic'])) {
            return $device->attributes['command_topic'];
        }

        $type = strtolower($device->entity_type);
        $id = $device->entity_id;

        // 2. Fallback for Home Assistant domains if no config was captured yet
        $haDomains = ['light', 'switch', 'button', 'fan', 'lock', 'cover', 'climate', 'siren', 'number', 'input_boolean'];
        if (in_array($type, $haDomains)) {
            // HA MQTT Discovery default fallback pattern: (best guess)
            return "homeassistant/{$type}/{$id}/{$suffix}";
        }

        return match ($type) {
            'zigbee', 'zigbee2mqtt' => "zigbee2mqtt/{$id}/set",
            'tasmota' => "cmnd/{$id}/POWER",
            'shelly' => "shellies/{$id}/relay/0/command",
            'esphome' => "esphome/{$id}/switch/command",
            default => null,
        };
    }

    /**
     * Format the payload appropriately based on device type.
     */
    protected function getCommandPayload(Device $device, mixed $state): string
    {
        // HA Discovery custom payloads
        $isOn = strtolower((string) $state) === 'on';
        if ($isOn && isset($device->attributes['payload_on'])) {
            return (string) $device->attributes['payload_on'];
        }
        if (!$isOn && isset($device->attributes['payload_off'])) {
            return (string) $device->attributes['payload_off'];
        }

        $type = strtolower($device->entity_type);

        if ($type === 'zigbee' || $type === 'zigbee2mqtt') {
            return json_encode(['state' => strtoupper((string) $state)]);
        }

        if ($type === 'tasmota') {
            return strtoupper((string) $state);
        }

        if ($type === 'shelly') {
            return strtolower((string) $state);
        }

        // Generic fallback
        return (string) $state;
    }
}
