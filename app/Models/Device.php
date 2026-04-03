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

    public function sendCommand(mixed $state): void
    {
        app(\App\Services\MqttService::class)->sendCommand($this, $state);
    }

    public function setBrightness(int $brightness): void
    {
        app(\App\Services\MqttService::class)->sendBrightness($this, $brightness);
    }

    public function setColor(string $hexColor): void
    {
        app(\App\Services\MqttService::class)->sendColor($this, $hexColor);
    }

    public function setColorTemp(int $temp): void
    {
        app(\App\Services\MqttService::class)->sendColorTemp($this, $temp);
    }

    public function history()
    {
        return $this->hasMany(IoTEvent::class, 'entity_id', 'entity_id')
            ->orderByDesc('id');
    }
}
