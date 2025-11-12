<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeavePolicyTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_policy_id',
        'year_of_service',
        'additional_quota',
    ];

    protected $casts = [
        'additional_quota' => 'float',
    ];

    public function policy()
    {
        return $this->belongsTo(LeavePolicy::class, 'leave_policy_id');
    }
}
