<?php

use App\Console\Commands\MqttDeviceListener;

function parseValue(string $message): mixed
{
    $listener = new MqttDeviceListener();
    $method = new ReflectionMethod(MqttDeviceListener::class, 'parseValue');
    $method->setAccessible(true);
    return $method->invoke($listener, $message);
}

test('string ON is returned as-is', function () {
    expect(parseValue('ON'))->toBe('ON');
});

test('string true becomes boolean true', function () {
    expect(parseValue('true'))->toBe(true);
});

test('string false becomes boolean false', function () {
    expect(parseValue('false'))->toBe(false);
});

test('numeric integer string becomes int', function () {
    expect(parseValue('42'))->toBe(42);
});

test('numeric float string becomes float', function () {
    expect(parseValue('27.5'))->toBe(27.5);
});

test('valid JSON object becomes array', function () {
    $result = parseValue('{"state":"ON","brightness":215}');

    expect($result)->toBe(['state' => 'ON', 'brightness' => 215]);
});

test('valid JSON array becomes array', function () {
    $result = parseValue('[1,2,3]');

    expect($result)->toBe([1, 2, 3]);
});

test('empty string becomes null', function () {
    expect(parseValue(''))->toBeNull();
});

test('string null becomes null', function () {
    expect(parseValue('null'))->toBeNull();
});

test('quoted string has quotes stripped', function () {
    expect(parseValue('"hello"'))->toBe('hello');
});

test('plain string is returned as-is', function () {
    expect(parseValue('some_value'))->toBe('some_value');
});
