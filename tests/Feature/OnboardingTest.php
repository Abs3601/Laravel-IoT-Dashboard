<?php

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('onboarding screen is accessible', function () {
    $this->get('/')
        ->assertStatus(200)
        ->assertSee('MQTT'); // Check for a common keyword on the setup page
});

test('submitting onboarding form saves settings to database', function () {
    $data = [
        'mqtt_host' => '192.168.1.50',
        'port' => '1883',
        'mqtt_client_id' => 'dissertation_test',
        'mqtt_auth_username' => 'user',
        'mqtt_auth_password' => 'secret123',
    ];

    $this->post('/', $data)
        ->assertRedirect(route('home'));

    // Verify stored values
    expect(Setting::get('mqtt_host'))->toBe('192.168.1.50');
    expect(Setting::get('port'))->toBe('1883');
    expect(Setting::get('mqtt_client_id'))->toBe('dissertation_test');
    expect(Setting::get('onboarding_completed'))->toBe('1');
});
