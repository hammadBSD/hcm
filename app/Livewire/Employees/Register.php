<?php

namespace App\Livewire\Employees;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeAdditionalInfo;
use App\Models\EmployeeOrganizationalInfo;
use App\Models\EmployeeSalaryLegalCompliance;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Register extends Component
{
    // Tab management
    public $activeTab = 'general';
    
    // Dynamic options
    public $roleOptions = [];
    public $departmentOptions = [];
    public $designationOptions = [];
    public $reportsToOptions = [];
    public $shiftOptions = [];

    // General Info form properties
    public $prefix = '';
    public $employeeCode = '';
    public $punchCode = '';
    public $firstName = '';
    public $lastName = '';
    public $fatherName = '';
    public $mobile = '';
    public $email = '';
    public $reportsTo = '';
    public $role = '';
    public $manualAttendance = 'no';
    public $status = 'active';
    public $department = '';
    public $designation = '';
    public $password = '';
    public $shift = '';
    public $allowEmployeeLogin = false;
    public $profilePicture;
    public $emergencyContactName = '';
    public $emergencyRelation = '';
    public $emergencyPhone = '';
    public $emergencyAddress = '';

    // Additional Info form properties
    public $dateOfBirth = '';
    public $gender = '';
    public $maritalStatus = '';
    public $nationality = '';
    public $bloodGroup = '';
    public $address = '';

    // Company Info form properties
    public $companyName = '';
    public $previousDesignation = '';
    public $fromDate = '';
    public $toDate = '';
    public $reasonForLeaving = '';

    // Documents form properties
    public $documentType = '';
    public $documentNumber = '';
    public $issueDate = '';
    public $expiryDate = '';
    public $documentFile;
    public $degree = '';
    public $institute = '';
    public $passingYear = '';
    public $grade = '';

    // Salary form properties
    public $basicSalary = '';
    public $allowances = '';
    public $bonus = '';
    public $currency = 'USD';
    public $paymentFrequency = 'monthly';
    public $bankAccount = '';
    public $taxId = '';
    public $salaryNotes = '';

    public function mount()
    {
        $this->loadOptions();
    }

    protected function loadOptions()
    {
        // Load roles (from Spatie Permission)
        $this->roleOptions = Role::orderBy('name')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
            ];
        })->toArray();

        // Load departments (active only)
        $this->departmentOptions = Department::where('status', 'active')
            ->orderBy('title')
            ->get()
            ->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'title' => $dept->title,
                ];
            })->toArray();

        // Load designations (active only)
        $this->designationOptions = Designation::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($designation) {
                return [
                    'id' => $designation->id,
                    'name' => $designation->name,
                ];
            })->toArray();

        // Load active employees for Reports To
        $this->reportsToOptions = Employee::where('status', 'active')
            ->with('user:id,name,email')
            ->whereHas('user')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => optional($employee->user)->name ?? __('Unknown Employee'),
                    'email' => optional($employee->user)->email ?? '',
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        // Load shifts (active only)
        $this->shiftOptions = Shift::where('status', 'active')
            ->orderBy('shift_name')
            ->get()
            ->map(function ($shift) {
                return [
                    'id' => $shift->id,
                    'name' => $shift->shift_name,
                    'time_from' => $shift->time_from,
                    'time_to' => $shift->time_to,
                ];
            })->toArray();
    }

    public function render()
    {
        return view('livewire.employees.register', [
            'roleOptions' => $this->roleOptions,
            'departmentOptions' => $this->departmentOptions,
            'designationOptions' => $this->designationOptions,
            'reportsToOptions' => $this->reportsToOptions,
            'shiftOptions' => $this->shiftOptions,
        ])->layout('components.layouts.app');
    }

    public function nextTab()
    {
        $tabs = ['general', 'additional', 'company', 'salary'];
        $currentIndex = array_search($this->activeTab, $tabs);
        
        if ($currentIndex !== false && $currentIndex < count($tabs) - 1) {
            $this->activeTab = $tabs[$currentIndex + 1];
        }
    }

    public function previousTab()
    {
        $tabs = ['general', 'additional', 'company', 'salary'];
        $currentIndex = array_search($this->activeTab, $tabs);
        
        if ($currentIndex !== false && $currentIndex > 0) {
            $this->activeTab = $tabs[$currentIndex - 1];
        }
    }

    public function resetForm()
    {
        $this->reset();
    }

    public function saveDraft()
    {
        // Save draft functionality
        session()->flash('message', 'Draft saved successfully!');
    }

    public function submit()
    {
        // Validate and submit form
        $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'department' => 'nullable|exists:departments,id',
            'designation' => 'nullable|exists:designations,id',
            'reportsTo' => 'nullable|exists:employees,id',
            'shift' => 'nullable|exists:shifts,id',
            'role' => 'nullable|exists:roles,id',
        ]);

        try {
            DB::transaction(function () {
                // Create user first
                $user = User::create([
                    'name' => trim($this->firstName . ' ' . $this->lastName),
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                ]);

                // Assign role to user if provided
                if ($this->role) {
                    $role = Role::find($this->role);
                    if ($role) {
                        $user->assignRole($role);
                    }
                }

                // Create employee with foreign keys
                $employee = Employee::create([
                    'user_id' => $user->id,
                    'prefix' => $this->prefix ?: null,
                    'employee_code' => $this->employeeCode ?: null,
                    'punch_code' => $this->punchCode ?: null,
                    'mobile' => $this->mobile,
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName ?: null,
                    'father_name' => $this->fatherName ?: null,
                    'reports_to_id' => $this->reportsTo ?: null,
                    'manual_attendance' => $this->manualAttendance,
                    'status' => $this->status,
                    'department_id' => $this->department ?: null,
                    'designation_id' => $this->designation ?: null,
                    'shift_id' => $this->shift ?: null,
                    'document_type' => $this->documentType ?: null,
                    'document_number' => $this->documentNumber ?: null,
                    'issue_date' => $this->issueDate ?: null,
                    'expiry_date' => $this->expiryDate ?: null,
                    'document_file' => $this->documentFile ?: null,
                    'allow_employee_login' => $this->allowEmployeeLogin,
                    'profile_picture' => $this->profilePicture ?: null,
                    'emergency_contact_name' => $this->emergencyContactName ?: null,
                    'emergency_relation' => $this->emergencyRelation ?: null,
                    'emergency_phone' => $this->emergencyPhone ?: null,
                    'emergency_address' => $this->emergencyAddress ?: null,
                ]);

                // Create additional info if data exists
                if ($this->dateOfBirth || $this->gender || $this->maritalStatus || $this->nationality || $this->bloodGroup || $this->address) {
                    EmployeeAdditionalInfo::create([
                        'employee_id' => $employee->id,
                        'date_of_birth' => $this->dateOfBirth ?: null,
                        'gender' => $this->gender ?: null,
                        'marital_status' => $this->maritalStatus ?: null,
                        'nationality' => $this->nationality ?: null,
                        'blood_group' => $this->bloodGroup ?: null,
                        'address' => $this->address ?: null,
                    ]);
                }

                // Create organizational info if data exists
                if ($this->companyName || $this->previousDesignation || $this->fromDate || $this->toDate || $this->reasonForLeaving) {
                    EmployeeOrganizationalInfo::create([
                        'employee_id' => $employee->id,
                        'company_name' => $this->companyName ?: null,
                        'previous_designation' => $this->previousDesignation ?: null,
                        'from_date' => $this->fromDate ?: null,
                        'to_date' => $this->toDate ?: null,
                        'reason_for_leaving' => $this->reasonForLeaving ?: null,
                    ]);
                }

                // Create salary/legal compliance if data exists
                if ($this->basicSalary || $this->allowances || $this->bonus || $this->bankAccount || $this->taxId || $this->salaryNotes) {
                    EmployeeSalaryLegalCompliance::create([
                        'employee_id' => $employee->id,
                        'basic_salary' => $this->basicSalary ?: null,
                        'allowances' => $this->allowances ?: null,
                        'bonus' => $this->bonus ?: null,
                        'currency' => $this->currency,
                        'payment_frequency' => $this->paymentFrequency,
                        'bank_account' => $this->bankAccount ?: null,
                        'tax_id' => $this->taxId ?: null,
                        'salary_notes' => $this->salaryNotes ?: null,
                    ]);
                }
            });

            session()->flash('message', 'Employee created successfully!');
            $this->resetForm();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create employee: ' . $e->getMessage());
        }
    }

    public function addQualification()
    {
        // Add qualification logic
    }

    public function removeQualification()
    {
        // Remove qualification logic
    }
}
