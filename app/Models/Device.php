<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'devices';
    public $timestamps = false;
    protected $fillable = [
        'entity_type',
        'entity_id',
        'device_group',
        'friendly_name',
        'current_state',
        'attributes',
        'last_seen_at',
        'is_hidden',
        'is_pinned',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'attributes' => 'array',
        'is_hidden' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    /**
     * Send an MQTT command to this device.
     */
    public function sendCommand(mixed $state): void
    {
        app(\App\Services\MqttService::class)->sendCommand($this, $state);
    }

    /**
     * Send an MQTT brightness command to this device.
     */
    public function setBrightness(int $brightness): void
    {
        app(\App\Services\MqttService::class)->sendBrightness($this, $brightness);
    }
}
