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

        if (!$topic) return;

        try {
            $this->configureMqtt('_publisher');
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $payload, 0);
            Log::info("Published Command -> Topic: {$topic}, Payload: {$payload}");
        } catch (\Exception $e) {
            Log::error("Failed to publish command: " . $e->getMessage());
        }
    }

    public function sendBrightness(Device $device, int $brightness): void
    {
        $topic = $this->getCommandTopic($device, 'set_brightness');
        if (!$topic) return;

        try {
            $this->configureMqtt('_bright');
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, (string) $brightness, 0);
            Log::info("Published Brightness -> Topic: {$topic}, Payload: {$brightness}");
        } catch (\Exception $e) {
            Log::error("Failed to publish brightness: " . $e->getMessage());
        }
    }

    public function sendColor(Device $device, string $hexColor): void
    {
        $topic = $this->getCommandTopic($device, 'set_color');
        if (!$topic) return;

        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $payload = "{$r},{$g},{$b}";

        try {
            $this->configureMqtt('_color');
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $payload, 0);
            Log::info("Published Colour -> Topic: {$topic}, Payload: {$payload}");
        } catch (\Exception $e) {
            Log::error("Failed to publish colour: " . $e->getMessage());
        }
    }

    public function sendColorTemp(Device $device, int $temp): void
    {
        $topic = $this->getCommandTopic($device, 'set_temp');
        if (!$topic) return;

        try {
            $this->configureMqtt('_temp');
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, (string) $temp, 0);
            Log::info("Published Colour Temp -> Topic: {$topic}, Payload: {$temp}");
        } catch (\Exception $e) {
            Log::error("Failed to publish colour temperature: " . $e->getMessage());
        }
    }

    protected function getCommandTopic(Device $device, string $suffix = 'set'): ?string
    {
        $commandTopic = $device->attributes['command_topic'] ?? null;
        if ($commandTopic) return $commandTopic;

        $type = strtolower($device->entity_type);
        $id = $device->entity_id;

        return "homeassistant/{$type}/{$id}/{$suffix}";
    }

    protected function getCommandPayload(Device $device, mixed $state): string
    {
        $isOn = strtolower((string) $state) === 'on';
        
        if ($isOn && isset($device->attributes['payload_on'])) return (string) $device->attributes['payload_on'];
        if (!$isOn && isset($device->attributes['payload_off'])) return (string) $device->attributes['payload_off'];

        return (string) $state;
    }

    private function configureMqtt(string $suffix): void
    {
        config([
            'mqtt-client.connections.default.host' => Setting::get('mqtt_host'), 
            'mqtt-client.connections.default.port' => Setting::get('port'), 
            'mqtt-client.connections.default.client_id' => Setting::get('mqtt_client_id') . $suffix, 
            'mqtt-client.connections.default.auth.username' => Setting::get('mqtt_auth_username'), 
            'mqtt-client.connections.default.auth.password' => Setting::get('mqtt_auth_password')
        ]);
    }
}
