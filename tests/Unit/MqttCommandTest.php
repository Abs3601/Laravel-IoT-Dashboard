<?php

use App\Models\Device;
use App\Services\MqttService;

function callCommandTopic(Device $device, string $suffix = 'set'): ?string
{
    $service = new MqttService();
    $method = new ReflectionMethod(MqttService::class, 'getCommandTopic');
    $method->setAccessible(true);
    return $method->invoke($service, $device, $suffix);
}

function callCommandPayload(Device $device, mixed $state): string
{
    $service = new MqttService();
    $method = new ReflectionMethod(MqttService::class, 'getCommandPayload');
    $method->setAccessible(true);
    return $method->invoke($service, $device, $state);
}

function makeDevice(array $attrs): Device
{
    $device = new Device();
    foreach ($attrs as $key => $value) {
        $device->$key = $value;
    }
    return $device;
}

// --- Command Topics ---

test('device uses command_topic from attributes when present', function () {
    $device = makeDevice([
        'entity_type' => 'light',
        'entity_id'   => 'living_room',
        'attributes'  => ['command_topic' => 'custom/topic/set'],
    ]);

    expect(callCommandTopic($device))->toBe('custom/topic/set');
});

test('device follows unified ha bridge pattern by default', function () {
    $device = makeDevice([
        'entity_type' => 'light',
        'entity_id'   => 'living_room',
        'attributes'  => [],
    ]);

    expect(callCommandTopic($device))->toBe('homeassistant/light/living_room/set');
});

test('different entity types generate correct unified topics', function (string $type, string $id, string $expected) {
    $device = makeDevice([
        'entity_type' => $type,
        'entity_id'   => $id,
        'attributes'  => [],
    ]);

    expect(callCommandTopic($device))->toBe($expected);
})->with([
    ['switch', 'kitchen_plug', 'homeassistant/switch/kitchen_plug/set'],
    ['fan', 'bedroom_fan', 'homeassistant/fan/bedroom_fan/set'],
    ['button', 'doorbell', 'homeassistant/button/doorbell/set'],
]);

// --- Command Payloads ---

test('device uses payload_on attribute when turning on', function () {
    $device = makeDevice([
        'entity_type' => 'light',
        'entity_id'   => 'living_room',
        'attributes'  => ['payload_on' => 'TURN_ON'],
    ]);

    expect(callCommandPayload($device, 'on'))->toBe('TURN_ON');
});

test('device uses payload_off attribute when turning off', function () {
    $device = makeDevice([
        'entity_type' => 'light',
        'entity_id'   => 'living_room',
        'attributes'  => ['payload_off' => 'TURN_OFF'],
    ]);

    expect(callCommandPayload($device, 'off'))->toBe('TURN_OFF');
});

test('generic fallback returns state as-is', function () {
    $device = makeDevice(['entity_type' => 'generic', 'entity_id' => 'device_1', 'attributes' => []]);

    expect(callCommandPayload($device, 'ON'))->toBe('ON');
    expect(callCommandPayload($device, 'OFF'))->toBe('OFF');
});
