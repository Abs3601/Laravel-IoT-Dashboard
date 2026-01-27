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
        'current_state',
        'last_seen_at',
    ];
}
