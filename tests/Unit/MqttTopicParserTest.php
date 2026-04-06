<?php

use App\Console\Commands\MqttDeviceListener;

function parseTopic(array $parts): ?array
{
    $listener = new MqttDeviceListener();
    $method = new ReflectionMethod(MqttDeviceListener::class, 'parseTopic');
    $method->setAccessible(true);
    return $method->invoke($listener, $parts);
}

// Home Assistant topics
test('parses HA light configuration topics', function () {
    $result = parseTopic(['homeassistant', 'light', 'living_room', 'config']);

    expect($result)->toBe([
        'entity_type' => 'light',
        'entity_id'   => 'living_room',
        'attribute'   => 'config',
    ]);
});

test('parses HA sensor state updates', function () {
    $result = parseTopic(['homeassistant', 'sensor', 'bedroom_temp', 'state']);

    expect($result)->toBe([
        'entity_type' => 'sensor',
        'entity_id'   => 'bedroom_temp',
        'attribute'   => 'state',
    ]);
});

test('ignores generic HA status messages', function () {
    expect(parseTopic(['homeassistant', 'status']))->toBeNull();
});

test('rejects incomplete HA topics', function () {
    expect(parseTopic(['homeassistant', 'light', 'living_room']))->toBeNull();
});

// Zigbee2MQTT topics
test('extracts device data from Zigbee2MQTT', function () {
    $result = parseTopic(['zigbee2mqtt', 'my_bulb']);

    expect($result)->toBe([
        'entity_type' => 'zigbee',
        'entity_id'   => 'my_bulb',
        'attribute'   => 'payload',
    ]);
});

test('filters out bridge/command noise from Zigbee2MQTT', function () {
    expect(parseTopic(['zigbee2mqtt', 'bridge', 'state']))->toBeNull();
    expect(parseTopic(['zigbee2mqtt', 'my_bulb', 'set']))->toBeNull();
    expect(parseTopic(['zigbee2mqtt', 'my_bulb', 'get']))->toBeNull();
});

// Tasmota topics
test('parses Tasmota telemetry updates', function () {
    $result = parseTopic(['tele', 'my_switch', 'STATE']);

    expect($result)->toBe([
        'entity_type' => 'tasmota',
        'entity_id'   => 'my_switch',
        'attribute'   => 'state',
    ]);
});

test('ignores Tasmota LWT and command topics', function () {
    expect(parseTopic(['tele', 'my_switch', 'LWT']))->toBeNull();
    expect(parseTopic(['cmnd', 'my_switch', 'POWER']))->toBeNull();
});

test('parses Tasmota status responses', function () {
    $result = parseTopic(['stat', 'my_switch', 'RESULT']);

    expect($result)->toBe([
        'entity_type' => 'tasmota',
        'entity_id'   => 'my_switch',
        'attribute'   => 'result',
    ]);
});

// ESPHome topics
test('correctly maps ESPHome sensor paths', function () {
    $result = parseTopic(['esphome', 'my_device', 'temperature', 'state']);

    expect($result)->toBe([
        'entity_type' => 'esphome',
        'entity_id'   => 'my_device_temperature',
        'attribute'   => 'state',
    ]);
});

// Shelly topics
test('flattens complex Shelly relay paths', function () {
    $result = parseTopic(['shellies', 'shelly1-abc123', 'relay', '0', 'power']);

    expect($result)->toBe([
        'entity_type' => 'shelly',
        'entity_id'   => 'shelly1-abc123',
        'attribute'   => 'relay_0_power',
    ]);
});

// General constraints
test('ignores non-device data like Plex', function () {
    expect(parseTopic(['plex', 'my_device', 'state']))->toBeNull();
});

test('rejects malformed or short topics', function () {
    expect(parseTopic(['justonepart']))->toBeNull();
});
