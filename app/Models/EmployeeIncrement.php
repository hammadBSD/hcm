<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeIncrement extends Model
{
    use HasFactory;

    protected $table = 'employee_increments';

    protected $fillable = [
        'employee_id',
        'number_of_increments',
        'increment_due_date',
        'last_increment_date',
        'increment_amount',
        'gross_salary_after',
        'basic_salary_after',
        'updated_by',
    ];

    protected $casts = [
        'increment_due_date' => 'date',
        'last_increment_date' => 'date',
        'increment_amount' => 'decimal:2',
        'gross_salary_after' => 'decimal:2',
        'basic_salary_after' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
