<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AttendanceBreakExclusion extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'role_id',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    protected $casts = [
        'user_id' => 'integer',
        'role_id' => 'integer',
    ];
}
