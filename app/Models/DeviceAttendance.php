<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceAttendance extends Model
{
    protected $table = 'device_attendances';

    protected $fillable = [
        'punch_code',
        'device_ip',
        'device_type',
        'punch_time',
        'punch_type',
        'status',
        'verify_mode',
        'is_processed',
        'sync_timestamp',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'sync_timestamp' => 'datetime',
        'is_processed' => 'boolean',
        'verify_mode' => 'integer',
    ];
}
