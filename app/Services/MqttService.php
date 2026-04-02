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
     * Send an RGB colour command to a device.
     */
    public function sendColor(Device $device, string $hexColor): void
    {
        $type = strtolower($device->entity_type);
        
        $topic = $device->attributes['rgb_command_topic'] 
            ?? $this->getCommandTopic($device, 'set_color');
            
        if (!$topic) return;

        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        if ($type === 'zigbee' || $type === 'zigbee2mqtt') {
            $payload = json_encode(['color' => ['r' => $r, 'g' => $g, 'b' => $b]]);
        } elseif ($type === 'tasmota') {
            $payload = strtoupper($hex);
        } else {
            // Home Assistant / Generic fallback
            $payload = "{$r},{$g},{$b}";
        }

        try {
            config([
                'mqtt-client.connections.default.host' => Setting::get('mqtt_host'), 
                'mqtt-client.connections.default.port' => Setting::get('port'), 
                'mqtt-client.connections.default.client_id' => Setting::get('mqtt_client_id') . '_colpub', 
                'mqtt-client.connections.default.auth.username' => Setting::get('mqtt_auth_username'), 
                'mqtt-client.connections.default.auth.password' => Setting::get('mqtt_auth_password')
            ]);
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $payload, 0);
            Log::info("Published Color -> Topic: {$topic}, Payload: {$payload}");
        } catch (\Exception $e) {
            Log::error("Failed to publish color: " . $e->getMessage());
        }
    }

    /**
     * Send a colour temperature command to a device.
     */
    public function sendColorTemp(Device $device, int $temp): void
    {
        $type = strtolower($device->entity_type);
        
        $topic = $device->attributes['color_temp_command_topic'] 
            ?? $this->getCommandTopic($device, 'set_temp');
            
        if (!$topic) return;

        if ($type === 'zigbee' || $type === 'zigbee2mqtt') {
            $payload = json_encode(['color_temp' => $temp]);
        } elseif ($type === 'tasmota') {
            $payload = (string) $temp;
        } else {
            $payload = (string) $temp;
        }

        try {
            config([
                'mqtt-client.connections.default.host' => Setting::get('mqtt_host'), 
                'mqtt-client.connections.default.port' => Setting::get('port'), 
                'mqtt-client.connections.default.client_id' => Setting::get('mqtt_client_id') . '_tempub', 
                'mqtt-client.connections.default.auth.username' => Setting::get('mqtt_auth_username'), 
                'mqtt-client.connections.default.auth.password' => Setting::get('mqtt_auth_password')
            ]);
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $payload, 0);
            Log::info("Published Color Temp -> Topic: {$topic}, Payload: {$payload}");
        } catch (\Exception $e) {
            Log::error("Failed to publish color temperature: " . $e->getMessage());
        }
    }

    /**
     * Determine the correct command topic based on device type.
     */
    protected function getCommandTopic(Device $device, string $suffix = 'set'): ?string
    {
        if (!empty($device->attributes['command_topic'])) {
            return $device->attributes['command_topic'];
        }

        $type = strtolower($device->entity_type);
        $id = $device->entity_id;

        $haDomains = ['light', 'switch', 'button', 'fan', 'lock', 'cover', 'climate', 'siren', 'number', 'input_boolean'];
        if (in_array($type, $haDomains)) {
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
