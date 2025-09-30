<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceEmployee extends Model
{
    protected $table = 'device_employees';

    protected $fillable = [
        'punch_code_id',
        'name',
        'email',
        'department',
        'position',
        'is_active',
        'device_ip',
        'device_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
