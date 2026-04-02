<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoTEvent extends Model
{
    protected $table = 'iot_events';
    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'state',
        'attributes',
        'latency_ms',
        'created_at',
    ];

    protected $casts = [
        'attributes' => 'array',
        'created_at' => 'datetime',
        'latency_ms' => 'float',
    ];
}
