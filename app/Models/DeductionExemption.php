<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

class DeductionExemption extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'year_month',
        'scope_type',
        'department_id',
        'role_id',
        'user_id',
        'group_id',
        'exemption_type',
        'notes',
        'created_by',
    ];

    public const EXEMPTION_TYPES = [
        'absent_days' => 'Absent Days',
        'hourly_deduction_short_hours' => 'Hourly Deduction (Short hours)',
        'all' => 'All',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(SpatieRole::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
