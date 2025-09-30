<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryLegalCompliance extends Model
{
    use HasFactory;

    protected $table = 'employee_salary_legal_compliance';

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'allowances',
        'bonus',
        'currency',
        'payment_frequency',
        'bank_account',
        'account_title',
        'bank',
        'branch_code',
        'tax_id',
        'salary_notes',
        'eobi_registration_no',
        'eobi_entry_date',
        'social_security_no',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonus' => 'decimal:2',
        'eobi_entry_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
