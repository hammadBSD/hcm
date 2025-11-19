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

class Edit extends Component
{
    public $employeeId;
    
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
    public $employee_code = '';
    public $punch_code = '';
    public $first_name = '';
    public $last_name = '';
    public $father_name = '';
    public $mobile = '';
    public $email = '';
    public $reportsTo = '';
    public $role = '';
    public $manual_attendance = 'no';
    public $status = 'active';
    public $department = '';
    public $designation = '';
    public $password = '';
    public $shift = '';
    public $allow_employee_login = false;
    public $profile_picture;
    public $emergency_contact_name = '';
    public $emergency_relation = '';
    public $emergency_phone = '';
    public $emergency_address = '';
    public $document_type = '';
    public $document_number = '';
    public $issue_date = '';
    public $expiry_date = '';
    public $document_file;
    public $passport_no = '';
    public $visa_no = '';
    public $visa_expiry = '';
    public $passport_expiry = '';

    // Additional Info form properties
    public $date_of_birth = '';
    public $gender = '';
    public $marital_status = '';
    public $nationality = '';
    public $blood_group = '';
    public $address = '';
    public $place_of_birth = '';
    public $religion = '';
    public $state = '';
    public $country = '';
    public $province = '';
    public $city = '';
    public $area = '';
    public $zip_code = '';
    public $family_code = '';
    public $degree = '';
    public $institute = '';
    public $passing_year = '';
    public $grade = '';

    // Organizational Info form properties
    public $previous_company_name = '';
    public $previous_designation = '';
    public $from_date = '';
    public $to_date = '';
    public $reason_for_leaving = '';
    public $joining_date = '';
    public $confirmation_date = '';
    public $expected_confirmation_days = '';
    public $contract_start_date = '';
    public $contract_end_date = '';
    public $resign_date = '';
    public $leaving_date = '';
    public $leaving_reason = '';
    public $vendor = '';
    public $division = '';
    public $employee_status = '';
    public $employee_group = '';
    public $cost_center = '';
    public $region = '';
    public $gl_class = '';
    public $position_type = '';
    public $position = '';
    public $station = '';
    public $sub_department = '';

    // Salary form properties
    public $basic_salary = '';
    public $allowances = '';
    public $bonus = '';
    public $currency = 'USD';
    public $payment_frequency = 'monthly';
    public $bank_account = '';
    public $account_title = '';
    public $bank = '';
    public $branch_code = '';
    public $tax_id = '';
    public $salary_notes = '';
    public $eobi_registration_no = '';
    public $eobi_entry_date = '';
    public $social_security_no = '';

    public function mount($id)
    {
        // The ID passed might be user_id or employee_id
        // Try to find employee by employee_id first, then by user_id
        $employee = Employee::with(['user.roles', 'additionalInfo', 'organizationalInfo', 'salaryLegalCompliance'])
            ->where(function($query) use ($id) {
                $query->where('id', $id)
                      ->orWhere('user_id', $id);
            })
            ->firstOrFail();
        
        $this->employeeId = $employee->id;
        $this->loadEmployeeData();
        $this->loadOptions();
    }

    protected function loadEmployeeData()
    {
        $employee = Employee::with(['user.roles', 'additionalInfo', 'organizationalInfo', 'salaryLegalCompliance'])
            ->findOrFail($this->employeeId);

        // Load general info
        $this->prefix = $employee->prefix ?? '';
        $this->employee_code = $employee->employee_code ?? '';
        $this->punch_code = $employee->punch_code ?? '';
        $this->first_name = $employee->first_name ?? '';
        $this->last_name = $employee->last_name ?? '';
        $this->father_name = $employee->father_name ?? '';
        $this->mobile = $employee->mobile ?? '';
        $this->email = $employee->user->email ?? '';
        $this->reportsTo = $employee->reports_to_id ?? '';
        // Get user's first role
        if ($employee->user && $employee->user->roles) {
            $this->role = $employee->user->roles->first()?->id ?? '';
        }
        $this->manual_attendance = $employee->manual_attendance ?? 'no';
        $this->status = $employee->status ?? 'active';
        $this->department = $employee->department_id ?? '';
        $this->designation = $employee->designation_id ?? '';
        $this->shift = $employee->shift_id ?? '';
        $this->allow_employee_login = $employee->allow_employee_login ?? false;
        $this->profile_picture = $employee->profile_picture ?? '';
        $this->emergency_contact_name = $employee->emergency_contact_name ?? '';
        $this->emergency_relation = $employee->emergency_relation ?? '';
        $this->emergency_phone = $employee->emergency_phone ?? '';
        $this->emergency_address = $employee->emergency_address ?? '';
        $this->document_type = $employee->document_type ?? '';
        $this->document_number = $employee->document_number ?? '';
        $this->issue_date = $employee->issue_date ? $employee->issue_date->format('Y-m-d') : '';
        $this->expiry_date = $employee->expiry_date ? $employee->expiry_date->format('Y-m-d') : '';
        $this->passport_no = $employee->passport_no ?? '';
        $this->visa_no = $employee->visa_no ?? '';
        $this->visa_expiry = $employee->visa_expiry ? $employee->visa_expiry->format('Y-m-d') : '';
        $this->passport_expiry = $employee->passport_expiry ? $employee->passport_expiry->format('Y-m-d') : '';

        // Load additional info
        if ($employee->additionalInfo) {
            $additional = $employee->additionalInfo;
            $this->date_of_birth = $additional->date_of_birth ? $additional->date_of_birth->format('Y-m-d') : '';
            $this->gender = $additional->gender ?? '';
            $this->marital_status = $additional->marital_status ?? '';
            $this->nationality = $additional->nationality ?? '';
            $this->blood_group = $additional->blood_group ?? '';
            $this->address = $additional->address ?? '';
            $this->place_of_birth = $additional->place_of_birth ?? '';
            $this->religion = $additional->religion ?? '';
            $this->state = $additional->state ?? '';
            $this->country = $additional->country ?? '';
            $this->province = $additional->province ?? '';
            $this->city = $additional->city ?? '';
            $this->area = $additional->area ?? '';
            $this->zip_code = $additional->zip_code ?? '';
            $this->family_code = $additional->family_code ?? '';
            $this->degree = $additional->degree ?? '';
            $this->institute = $additional->institute ?? '';
            $this->passing_year = $additional->passing_year ?? '';
            $this->grade = $additional->grade ?? '';
        }

        // Load organizational info
        if ($employee->organizationalInfo) {
            $org = $employee->organizationalInfo;
            $this->previous_company_name = $org->previous_company_name ?? '';
            $this->previous_designation = $org->previous_designation ?? '';
            $this->from_date = $org->from_date ? $org->from_date->format('Y-m-d') : '';
            $this->to_date = $org->to_date ? $org->to_date->format('Y-m-d') : '';
            $this->reason_for_leaving = $org->reason_for_leaving ?? '';
            $this->joining_date = $org->joining_date ? $org->joining_date->format('Y-m-d') : '';
            $this->confirmation_date = $org->confirmation_date ? $org->confirmation_date->format('Y-m-d') : '';
            $this->expected_confirmation_days = $org->expected_confirmation_days ?? '';
            $this->contract_start_date = $org->contract_start_date ? $org->contract_start_date->format('Y-m-d') : '';
            $this->contract_end_date = $org->contract_end_date ? $org->contract_end_date->format('Y-m-d') : '';
            $this->resign_date = $org->resign_date ? $org->resign_date->format('Y-m-d') : '';
            $this->leaving_date = $org->leaving_date ? $org->leaving_date->format('Y-m-d') : '';
            $this->leaving_reason = $org->leaving_reason ?? '';
            $this->vendor = $org->vendor ?? '';
            $this->division = $org->division ?? '';
            $this->employee_status = $org->employee_status ?? '';
            $this->employee_group = $org->employee_group ?? '';
            $this->cost_center = $org->cost_center ?? '';
            $this->region = $org->region ?? '';
            $this->gl_class = $org->gl_class ?? '';
            $this->position_type = $org->position_type ?? '';
            $this->position = $org->position ?? '';
            $this->station = $org->station ?? '';
            $this->sub_department = $org->sub_department ?? '';
        }

        // Load salary/legal compliance
        if ($employee->salaryLegalCompliance) {
            $salary = $employee->salaryLegalCompliance;
            $this->basic_salary = $salary->basic_salary ?? '';
            $this->allowances = $salary->allowances ?? '';
            $this->bonus = $salary->bonus ?? '';
            $this->currency = $salary->currency ?? 'USD';
            $this->payment_frequency = $salary->payment_frequency ?? 'monthly';
            $this->bank_account = $salary->bank_account ?? '';
            $this->account_title = $salary->account_title ?? '';
            $this->bank = $salary->bank ?? '';
            $this->branch_code = $salary->branch_code ?? '';
            $this->tax_id = $salary->tax_id ?? '';
            $this->salary_notes = $salary->salary_notes ?? '';
            $this->eobi_registration_no = $salary->eobi_registration_no ?? '';
            $this->eobi_entry_date = $salary->eobi_entry_date ? $salary->eobi_entry_date->format('Y-m-d') : '';
            $this->social_security_no = $salary->social_security_no ?? '';
        }
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

        // Load active employees for Reports To (exclude current employee)
        $this->reportsToOptions = Employee::where('status', 'active')
            ->where('id', '!=', $this->employeeId)
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
        return view('livewire.employees.edit', [
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

    public function update()
    {
        // Validate form
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Employee::find($this->employeeId)->user_id,
            'mobile' => 'required|string|max:20',
            'department' => 'nullable|exists:departments,id',
            'designation' => 'nullable|exists:designations,id',
            'reportsTo' => 'nullable|exists:employees,id',
            'shift' => 'nullable|exists:shifts,id',
            'role' => 'nullable|exists:roles,id',
        ]);

        try {
            DB::transaction(function () {
                $employee = Employee::findOrFail($this->employeeId);
                $user = $employee->user;

                // Update user
                $user->name = trim($this->first_name . ' ' . $this->last_name);
                $user->email = $this->email;
                
                // Update password only if provided
                if (!empty($this->password)) {
                    $user->password = Hash::make($this->password);
                }
                
                $user->save();

                // Update user role if provided
                if ($this->role) {
                    $role = Role::find($this->role);
                    if ($role) {
                        $user->syncRoles([$role]);
                    }
                } else {
                    $user->syncRoles([]);
                }

                // Update employee
                $employee->prefix = $this->prefix ?: null;
                $employee->employee_code = $this->employee_code ?: null;
                $employee->punch_code = $this->punch_code ?: null;
                $employee->mobile = $this->mobile;
                $employee->first_name = $this->first_name;
                $employee->last_name = $this->last_name ?: null;
                $employee->father_name = $this->father_name ?: null;
                $employee->reports_to_id = $this->reportsTo ?: null;
                $employee->manual_attendance = $this->manual_attendance;
                $employee->status = $this->status;
                $employee->department_id = $this->department ?: null;
                $employee->designation_id = $this->designation ?: null;
                $employee->shift_id = $this->shift ?: null;
                $employee->document_type = $this->document_type ?: null;
                $employee->document_number = $this->document_number ?: null;
                $employee->issue_date = $this->issue_date ?: null;
                $employee->expiry_date = $this->expiry_date ?: null;
                $employee->passport_no = $this->passport_no ?: null;
                $employee->visa_no = $this->visa_no ?: null;
                $employee->visa_expiry = $this->visa_expiry ?: null;
                $employee->passport_expiry = $this->passport_expiry ?: null;
                $employee->allow_employee_login = $this->allow_employee_login;
                $employee->emergency_contact_name = $this->emergency_contact_name ?: null;
                $employee->emergency_relation = $this->emergency_relation ?: null;
                $employee->emergency_phone = $this->emergency_phone ?: null;
                $employee->emergency_address = $this->emergency_address ?: null;
                
                // Handle file uploads if provided
                if ($this->profile_picture) {
                    // TODO: Handle file upload
                    // $employee->profile_picture = $this->profile_picture->store('profile-pictures');
                }
                
                if ($this->document_file) {
                    // TODO: Handle file upload
                    // $employee->document_file = $this->document_file->store('documents');
                }
                
                $employee->save();

                // Update or create additional info
                $additionalInfo = $employee->additionalInfo;
                if (!$additionalInfo) {
                    $additionalInfo = new EmployeeAdditionalInfo();
                    $additionalInfo->employee_id = $employee->id;
                }
                
                $additionalInfo->date_of_birth = $this->date_of_birth ?: null;
                $additionalInfo->gender = $this->gender ?: null;
                $additionalInfo->marital_status = $this->marital_status ?: null;
                $additionalInfo->nationality = $this->nationality ?: null;
                $additionalInfo->blood_group = $this->blood_group ?: null;
                $additionalInfo->address = $this->address ?: null;
                $additionalInfo->place_of_birth = $this->place_of_birth ?: null;
                $additionalInfo->religion = $this->religion ?: null;
                $additionalInfo->state = $this->state ?: null;
                $additionalInfo->country = $this->country ?: null;
                $additionalInfo->province = $this->province ?: null;
                $additionalInfo->city = $this->city ?: null;
                $additionalInfo->area = $this->area ?: null;
                $additionalInfo->zip_code = $this->zip_code ?: null;
                $additionalInfo->family_code = $this->family_code ?: null;
                $additionalInfo->degree = $this->degree ?: null;
                $additionalInfo->institute = $this->institute ?: null;
                $additionalInfo->passing_year = $this->passing_year ?: null;
                $additionalInfo->grade = $this->grade ?: null;
                $additionalInfo->save();

                // Update or create organizational info
                $orgInfo = $employee->organizationalInfo;
                if (!$orgInfo) {
                    $orgInfo = new EmployeeOrganizationalInfo();
                    $orgInfo->employee_id = $employee->id;
                }
                
                $orgInfo->previous_company_name = $this->previous_company_name ?: null;
                $orgInfo->previous_designation = $this->previous_designation ?: null;
                $orgInfo->from_date = $this->from_date ?: null;
                $orgInfo->to_date = $this->to_date ?: null;
                $orgInfo->reason_for_leaving = $this->reason_for_leaving ?: null;
                $orgInfo->joining_date = $this->joining_date ?: null;
                $orgInfo->confirmation_date = $this->confirmation_date ?: null;
                $orgInfo->expected_confirmation_days = $this->expected_confirmation_days ?: null;
                $orgInfo->contract_start_date = $this->contract_start_date ?: null;
                $orgInfo->contract_end_date = $this->contract_end_date ?: null;
                $orgInfo->resign_date = $this->resign_date ?: null;
                $orgInfo->leaving_date = $this->leaving_date ?: null;
                $orgInfo->leaving_reason = $this->leaving_reason ?: null;
                $orgInfo->vendor = $this->vendor ?: null;
                $orgInfo->division = $this->division ?: null;
                $orgInfo->employee_status = $this->employee_status ?: null;
                $orgInfo->employee_group = $this->employee_group ?: null;
                $orgInfo->cost_center = $this->cost_center ?: null;
                $orgInfo->region = $this->region ?: null;
                $orgInfo->gl_class = $this->gl_class ?: null;
                $orgInfo->position_type = $this->position_type ?: null;
                $orgInfo->position = $this->position ?: null;
                $orgInfo->station = $this->station ?: null;
                $orgInfo->sub_department = $this->sub_department ?: null;
                $orgInfo->save();

                // Update or create salary/legal compliance
                $salaryInfo = $employee->salaryLegalCompliance;
                if (!$salaryInfo) {
                    $salaryInfo = new EmployeeSalaryLegalCompliance();
                    $salaryInfo->employee_id = $employee->id;
                }
                
                $salaryInfo->basic_salary = $this->basic_salary ?: null;
                $salaryInfo->allowances = $this->allowances ?: null;
                $salaryInfo->bonus = $this->bonus ?: null;
                $salaryInfo->currency = $this->currency;
                $salaryInfo->payment_frequency = $this->payment_frequency;
                $salaryInfo->bank_account = $this->bank_account ?: null;
                $salaryInfo->account_title = $this->account_title ?: null;
                $salaryInfo->bank = $this->bank ?: null;
                $salaryInfo->branch_code = $this->branch_code ?: null;
                $salaryInfo->tax_id = $this->tax_id ?: null;
                $salaryInfo->salary_notes = $this->salary_notes ?: null;
                $salaryInfo->eobi_registration_no = $this->eobi_registration_no ?: null;
                $salaryInfo->eobi_entry_date = $this->eobi_entry_date ?: null;
                $salaryInfo->social_security_no = $this->social_security_no ?: null;
                $salaryInfo->save();
            });

            session()->flash('message', 'Employee updated successfully!');
            return redirect()->route('employees.list');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }
}
