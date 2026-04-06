<?php

use App\Console\Commands\MqttDeviceListener;

function parseValue(string $message): mixed
{
    $listener = new MqttDeviceListener();
    $method = new ReflectionMethod(MqttDeviceListener::class, 'parseValue');
    $method->setAccessible(true);
    return $method->invoke($listener, $message);
}

test('returns string ON as-is', function () {
    expect(parseValue('ON'))->toBe('ON');
});

test('converts boolean strings to boolean types', function () {
    expect(parseValue('true'))->toBeTrue();
    expect(parseValue('false'))->toBeFalse();
});

test('converts numeric strings to int or float', function () {
    expect(parseValue('42'))->toBe(42);
    expect(parseValue('27.5'))->toBe(27.5);
});

test('decodes valid JSON objects and arrays', function () {
    expect(parseValue('{"state":"ON","brightness":215}'))->toBe(['state' => 'ON', 'brightness' => 215]);
    expect(parseValue('[1,2,3]'))->toBe([1, 2, 3]);
});

test('handles nulls and empty strings accurately', function () {
    expect(parseValue(''))->toBeNull();
    expect(parseValue('null'))->toBeNull();
});

test('strips surrounding quotes from strings', function () {
    expect(parseValue('"hello"'))->toBe('hello');
});

test('returns plain strings unmodified', function () {
    expect(parseValue('some_value'))->toBe('some_value');
});
