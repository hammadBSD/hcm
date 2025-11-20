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
        'reports_to_id',
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

    /**
     * Get the department change history for this employee
     */
    public function departmentChanges(): HasMany
    {
        return $this->hasMany(EmployeeDepartmentChange::class);
    }

    public function leaveSettings(): HasMany
    {
        return $this->hasMany(EmployeeLeaveSetting::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function leaveTransactions(): HasMany
    {
        return $this->hasMany(LeaveBalanceTransaction::class);
    }

    /**
     * Get the effective shift for this employee
     * Priority:
     * 1. Employee's individual shift (shift_id)
     * 2. Department's shift (if employee has department_id)
     * 3. null (no shift assigned)
     * 
     * @return Shift|null
     */
    public function getEffectiveShift()
    {
        // First priority: Check if employee has individual shift assigned
        if ($this->shift_id) {
            return $this->shift;
        }

        // Second priority: Check if employee has department and department has shift
        if ($this->department_id) {
            // Always use the relationship method to avoid conflict with old varchar column
            // Don't use $this->department attribute as it might return the old varchar value
            $department = $this->department()->with('shift')->first();
            
            // Verify it's an object and has shift_id
            if ($department && is_object($department) && $department->shift_id) {
                // Ensure shift is loaded
                if (!$department->relationLoaded('shift')) {
                    $department->load('shift');
                }
                return $department->shift;
            }
        }

        // No shift assigned
        return null;
    }

    /**
     * Get the effective shift for a specific date
     * This considers EmployeeShift assignments with start_date and end_date
     * Falls back to department shift if no EmployeeShift assignment found
     */
    public function getEffectiveShiftForDate($date)
    {
        $dateCarbon = \Carbon\Carbon::parse($date)->startOfDay();
        
        // First, check for EmployeeShift assignments that are active on this date
        $employeeShift = \App\Models\EmployeeShift::where('employee_id', $this->id)
            ->where('start_date', '<=', $dateCarbon->format('Y-m-d'))
            ->where(function($query) use ($dateCarbon) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $dateCarbon->format('Y-m-d'));
            })
            ->orderBy('start_date', 'desc')
            ->with('shift')
            ->first();
        
        if ($employeeShift && $employeeShift->shift) {
            return $employeeShift->shift;
        }
        
        // If no EmployeeShift assignment found, fallback to department shift
        // (This handles dates before the first EmployeeShift assignment)
        if ($this->department_id) {
            // Always use the relationship method to avoid conflict with old varchar column
            $department = $this->department()->with('shift')->first();
            
            // Verify it's an object and has shift_id
            if ($department && is_object($department) && $department->shift_id) {
                // Ensure shift is loaded
                if (!$department->relationLoaded('shift')) {
                    $department->load('shift');
                }
                return $department->shift;
            }
        }
        
        // No shift assigned (neither EmployeeShift nor department shift)
        return null;
    }
}
