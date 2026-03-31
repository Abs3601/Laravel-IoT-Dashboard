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

test('HA light uses command_topic from attributes when present', function () {
    $device = makeDevice([
        'entity_type' => 'light',
        'entity_id'   => 'living_room',
        'attributes'  => ['command_topic' => 'homeassistant/light/living_room/set'],
    ]);

    expect(callCommandTopic($device))->toBe('homeassistant/light/living_room/set');
});

test('HA light falls back to pattern when no command_topic attribute', function () {
    $device = makeDevice([
        'entity_type' => 'light',
        'entity_id'   => 'living_room',
        'attributes'  => [],
    ]);

    expect(callCommandTopic($device))->toBe('homeassistant/light/living_room/set');
});

test('Zigbee device generates correct command topic', function () {
    $device = makeDevice([
        'entity_type' => 'zigbee',
        'entity_id'   => 'my_bulb',
        'attributes'  => [],
    ]);

    expect(callCommandTopic($device))->toBe('zigbee2mqtt/my_bulb/set');
});

test('Tasmota device generates correct command topic', function () {
    $device = makeDevice([
        'entity_type' => 'tasmota',
        'entity_id'   => 'my_switch',
        'attributes'  => [],
    ]);

    expect(callCommandTopic($device))->toBe('cmnd/my_switch/POWER');
});

test('Shelly device generates correct command topic', function () {
    $device = makeDevice([
        'entity_type' => 'shelly',
        'entity_id'   => 'shelly1-abc123',
        'attributes'  => [],
    ]);

    expect(callCommandTopic($device))->toBe('shellies/shelly1-abc123/relay/0/command');
});

test('ESPHome device generates correct command topic', function () {
    $device = makeDevice([
        'entity_type' => 'esphome',
        'entity_id'   => 'my_device_switch',
        'attributes'  => [],
    ]);

    expect(callCommandTopic($device))->toBe('esphome/my_device_switch/switch/command');
});

test('unknown device type returns null', function () {
    $device = makeDevice([
        'entity_type' => 'unknown_platform',
        'entity_id'   => 'some_device',
        'attributes'  => [],
    ]);

    expect(callCommandTopic($device))->toBeNull();
});

// --- Command Payloads ---

test('Zigbee ON payload is JSON encoded', function () {
    $device = makeDevice(['entity_type' => 'zigbee', 'entity_id' => 'my_bulb', 'attributes' => []]);

    expect(callCommandPayload($device, 'ON'))->toBe('{"state":"ON"}');
});

test('Zigbee OFF payload is JSON encoded', function () {
    $device = makeDevice(['entity_type' => 'zigbee', 'entity_id' => 'my_bulb', 'attributes' => []]);

    expect(callCommandPayload($device, 'OFF'))->toBe('{"state":"OFF"}');
});

test('Tasmota ON payload is uppercase string', function () {
    $device = makeDevice(['entity_type' => 'tasmota', 'entity_id' => 'my_switch', 'attributes' => []]);

    expect(callCommandPayload($device, 'on'))->toBe('ON');
});

test('Shelly ON payload is lowercase string', function () {
    $device = makeDevice(['entity_type' => 'shelly', 'entity_id' => 'shelly1', 'attributes' => []]);

    expect(callCommandPayload($device, 'ON'))->toBe('on');
});

test('HA device uses payload_on attribute when turning on', function () {
    $device = makeDevice([
        'entity_type' => 'light',
        'entity_id'   => 'living_room',
        'attributes'  => ['payload_on' => 'TURN_ON', 'payload_off' => 'TURN_OFF'],
    ]);

    expect(callCommandPayload($device, 'on'))->toBe('TURN_ON');
});

test('HA device uses payload_off attribute when turning off', function () {
    $device = makeDevice([
        'entity_type' => 'light',
        'entity_id'   => 'living_room',
        'attributes'  => ['payload_on' => 'TURN_ON', 'payload_off' => 'TURN_OFF'],
    ]);

    expect(callCommandPayload($device, 'off'))->toBe('TURN_OFF');
});

test('generic fallback returns state as-is', function () {
    $device = makeDevice(['entity_type' => 'generic', 'entity_id' => 'device_1', 'attributes' => []]);

    expect(callCommandPayload($device, 'ON'))->toBe('ON');
});
