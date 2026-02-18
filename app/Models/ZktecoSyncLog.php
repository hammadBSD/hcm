<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZktecoSyncLog extends Model
{
    protected $table = 'zkteco_sync_logs';

    protected $fillable = [
        'sync_type',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public const TYPE_ATTENDANCE = 'attendance';
    public const TYPE_EMPLOYEES = 'employees';
    public const TYPE_MONTHLY_ATTENDANCE = 'monthly_attendance';
}
