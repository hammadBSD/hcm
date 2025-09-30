<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_code',
        'punch_code',
        'first_name',
        'last_name',
        'father_name',
        'mobile',
        'reports_to',
        'role',
        'manual_attendance',
        'status',
        'department',
        'designation',
        'shift',
        'document_type',
        'document_number',
        'issue_date',
        'expiry_date',
        'document_file',
        'passport_no',
        'visa_no',
        'visa_expiry',
        'passport_expiry',
        'profile_picture',
        'emergency_contact_name',
        'emergency_relation',
        'emergency_phone',
        'emergency_address',
        'allow_employee_login',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'visa_expiry' => 'date',
        'passport_expiry' => 'date',
        'allow_employee_login' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function additionalInfo(): HasOne
    {
        return $this->hasOne(EmployeeAdditionalInfo::class);
    }

    public function organizationalInfo(): HasOne
    {
        return $this->hasOne(EmployeeOrganizationalInfo::class);
    }

    public function salaryLegalCompliance(): HasOne
    {
        return $this->hasOne(EmployeeSalaryLegalCompliance::class);
    }
}
