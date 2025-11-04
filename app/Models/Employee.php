<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        // Foreign key relationships
        'department_id',
        'designation_id',
        'group_id',
        'employment_type_id',
        'employment_status_id',
        'country_id',
        'province_id',
        'shift_id',
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

    // Organization structure relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class);
    }

    public function employmentStatus(): BelongsTo
    {
        return $this->belongsTo(EmploymentStatus::class);
    }

    // Location relationships
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the current shift assigned to this employee
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    /**
     * Get the shift history for this employee
     */
    public function shiftHistory(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }
}
