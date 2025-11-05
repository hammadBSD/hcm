<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_name',
        'time_from',
        'time_to',
        'status',
        'grace_period_late_in',
        'grace_period_early_out',
        'disable_grace_period',
    ];

    protected $casts = [
        'disable_grace_period' => 'boolean',
    ];



    /**
     * Get employees assigned to this shift
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'shift_id');
    }

    /**
     * Get employee shift history for this shift
     */
    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    /**
     * Get departments assigned to this shift
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
