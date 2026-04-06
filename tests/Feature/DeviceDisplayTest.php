<?php

use App\Models\Device;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard page loads successfully', function () {
    // Overcome onboarding middleware
    Setting::set('onboarding_completed', true);

    $this->get('/index')
        ->assertStatus(200);
});

test('visible devices appear on the dashboard', function () {
    Setting::set('onboarding_completed', true);

    Device::create([
        'entity_type' => 'light',
        'entity_id' => 'visible_bulb',
        'friendly_name' => 'Visible bulb', // Match UI formatting
        'device_group' => 'visible_bulb',
        'is_hidden' => false,
    ]);

    $this->get('/index')
        ->assertSee('Visible bulb');
});

test('hidden devices do NOT appear on the dashboard', function () {
    Setting::set('onboarding_completed', true);

    Device::create([
        'entity_type' => 'light',
        'entity_id' => 'secret_bulb',
        'device_group' => 'secret_bulb',
        'is_hidden' => true,
    ]);

    $this->get('/index')
        ->assertDontSee('secret_bulb');
});

test('device detail page loads for a valid device', function () {
    Setting::set('onboarding_completed', true);

    $device = Device::create([
        'entity_type' => 'light',
        'entity_id' => 'test_lamp',
        'device_group' => 'test_lamp',
    ]);

    // Use the actual route defined in web.php
    $this->get('/device/test_lamp')
        ->assertStatus(200)
        ->assertSee('test_lamp');
});
