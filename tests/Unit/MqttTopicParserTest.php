<?php

use App\Console\Commands\MqttDeviceListener;

function parseTopic(array $parts): ?array
{
    $listener = new MqttDeviceListener();
    $method = new ReflectionMethod(MqttDeviceListener::class, 'parseTopic');
    $method->setAccessible(true);
    return $method->invoke($listener, $parts);
}

// --- Home Assistant ---

test('HA light config topic is parsed correctly', function () {
    $result = parseTopic(['homeassistant', 'light', 'living_room', 'config']);

    expect($result)->toBe([
        'entity_type' => 'light',
        'entity_id'   => 'living_room',
        'attribute'   => 'config',
    ]);
});

test('HA sensor state topic is parsed correctly', function () {
    $result = parseTopic(['homeassistant', 'sensor', 'bedroom_temp', 'state']);

    expect($result)->toBe([
        'entity_type' => 'sensor',
        'entity_id'   => 'bedroom_temp',
        'attribute'   => 'state',
    ]);
});

test('HA status topic is filtered out', function () {
    $result = parseTopic(['homeassistant', 'status']);
    expect($result)->toBeNull();
});

test('HA topic with fewer than 4 parts returns null', function () {
    $result = parseTopic(['homeassistant', 'light', 'living_room']);
    expect($result)->toBeNull();
});

// --- Zigbee2MQTT ---

test('Zigbee2MQTT device topic is parsed correctly', function () {
    $result = parseTopic(['zigbee2mqtt', 'my_bulb']);

    expect($result)->toBe([
        'entity_type' => 'zigbee',
        'entity_id'   => 'my_bulb',
        'attribute'   => 'payload',
    ]);
});

test('Zigbee2MQTT bridge topics are filtered out', function () {
    $result = parseTopic(['zigbee2mqtt', 'bridge', 'state']);
    expect($result)->toBeNull();
});

test('Zigbee2MQTT /set command topics are filtered out', function () {
    $result = parseTopic(['zigbee2mqtt', 'my_bulb', 'set']);
    expect($result)->toBeNull();
});

test('Zigbee2MQTT /get command topics are filtered out', function () {
    $result = parseTopic(['zigbee2mqtt', 'my_bulb', 'get']);
    expect($result)->toBeNull();
});

// --- Tasmota ---

test('Tasmota tele/STATE topic is parsed correctly', function () {
    $result = parseTopic(['tele', 'my_switch', 'STATE']);

    expect($result)->toBe([
        'entity_type' => 'tasmota',
        'entity_id'   => 'my_switch',
        'attribute'   => 'state',
    ]);
});

test('Tasmota LWT topic is filtered out', function () {
    $result = parseTopic(['tele', 'my_switch', 'LWT']);
    expect($result)->toBeNull();
});

test('Tasmota cmnd topics are filtered out', function () {
    $result = parseTopic(['cmnd', 'my_switch', 'POWER']);
    expect($result)->toBeNull();
});

test('Tasmota stat topic is parsed correctly', function () {
    $result = parseTopic(['stat', 'my_switch', 'RESULT']);

    expect($result)->toBe([
        'entity_type' => 'tasmota',
        'entity_id'   => 'my_switch',
        'attribute'   => 'result',
    ]);
});

// --- ESPHome ---

test('ESPHome sensor state topic is parsed correctly', function () {
    $result = parseTopic(['esphome', 'my_device', 'temperature', 'state']);

    expect($result)->toBe([
        'entity_type' => 'esphome',
        'entity_id'   => 'my_device_temperature',
        'attribute'   => 'state',
    ]);
});

// --- Shelly ---

test('Shelly relay topic is parsed correctly', function () {
    $result = parseTopic(['shellies', 'shelly1-abc123', 'relay', '0', 'power']);

    expect($result)->toBe([
        'entity_type' => 'shelly',
        'entity_id'   => 'shelly1-abc123',
        'attribute'   => 'relay_0_power',
    ]);
});

// --- Edge Cases ---

test('single segment topic returns null', function () {
    $result = parseTopic(['justonepart']);
    expect($result)->toBeNull();
});

test('plex topics are filtered out', function () {
    $result = parseTopic(['plex', 'my_device', 'state']);
    expect($result)->toBeNull();
});
