<?php

use App\Console\Commands\MqttDeviceListener;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function callExtractDeviceGroup(string $entityId, string $entityType): string
{
    $listener = new MqttDeviceListener();
    $method = new ReflectionMethod(MqttDeviceListener::class, 'extractDeviceGroup');
    $method->setAccessible(true);
    return $method->invoke($listener, $entityId, $entityType);
}

test('primary devices are assigned as their own group root', function () {
    $group = callExtractDeviceGroup('living_room_lamp', 'light');

    expect($group)->toBe('living_room_lamp');
});

test('sensors are automatically grouped with matching primary devices', function () {
    Device::create([
        'entity_type' => 'light',
        'entity_id'   => 'office_bulb',
        'device_group' => 'office_bulb',
    ]);

    $group = callExtractDeviceGroup('office_bulb_power', 'sensor');

    expect($group)->toBe('office_bulb');
});

test('existing orphan sensors are re-parented when parent is discovered', function () {
    Device::create([
        'entity_type' => 'sensor',
        'entity_id'   => 'kitchen_light_energy',
        'device_group' => 'kitchen_light_energy',
    ]);

    $group = callExtractDeviceGroup('kitchen_light', 'light');

    expect($group)->toBe('kitchen_light');

    $sensor = Device::where('entity_id', 'kitchen_light_energy')->first();
    expect($sensor->device_group)->toBe('kitchen_light');
});

test('standalone sensors without parents remain independent', function () {
    $group = callExtractDeviceGroup('outside_temp', 'sensor');

    expect($group)->toBe('outside_temp');
});
