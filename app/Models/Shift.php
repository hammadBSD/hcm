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
}
