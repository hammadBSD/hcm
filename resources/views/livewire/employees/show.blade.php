<section class="w-full">
    @include('partials.employees-heading')

    <x-employees.layout :heading="__('View Employee')" :subheading="__('View employee details and information')">
        <div class="space-y-6">
            @if($employee)
                <!-- Profile Header -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center gap-6">
                        <div class="relative">
                            @if($employee->profile_picture)
                                <img src="{{ asset('storage/' . $employee->profile_picture) }}" 
                                     alt="{{ $employee->first_name }} {{ $employee->last_name }}" 
                                     class="w-24 h-24 rounded-full object-cover border-4 border-zinc-200 dark:border-zinc-700">
                            @else
                                <div class="w-24 h-24 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center border-4 border-zinc-200 dark:border-zinc-700">
                                    <span class="text-2xl font-semibold text-zinc-600 dark:text-zinc-400">
                                        {{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </h1>
                            <p class="text-lg text-zinc-600 dark:text-zinc-400">
                                {{ $employee->designation ?? 'Not Assigned' }}
                            </p>
                            <div class="flex items-center gap-4 mt-2">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Employee ID: <span class="font-medium">{{ $employee->employee_code ?? 'Not Assigned' }}</span>
                                </span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Department: <span class="font-medium">{{ $employee->department ?? 'Not Assigned' }}</span>
                                </span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Status: 
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $employee->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                        {{ ucfirst($employee->status ?? 'Unknown') }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                            Personal Information
                        </flux:heading>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">First Name</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->first_name ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Last Name</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->last_name ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Father's Name</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->father_name ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Mobile</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->mobile ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $user->email ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Employee Code</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->employee_code ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                @if($additionalInfo)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                            Additional Information
                        </flux:heading>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Date of Birth</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->date_of_birth ? $additionalInfo->date_of_birth->format('d/m/Y') : '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Gender</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ ucfirst($additionalInfo->gender ?? '-') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Marital Status</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ ucfirst($additionalInfo->marital_status ?? '-') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nationality</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ ucfirst($additionalInfo->nationality ?? '-') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Blood Group</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->blood_group ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Place of Birth</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->place_of_birth ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Religion</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->religion ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Country</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->country ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Province/State</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->state ?? $additionalInfo->province ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">City</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->city ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Area</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->area ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Zip Code</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->zip_code ?? '-' }}</p>
                            </div>
                        </div>
                        @if($additionalInfo->address)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Address</label>
                            <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->address }}</p>
                        </div>
                        @endif
                        @if($additionalInfo->degree || $additionalInfo->institute)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Education</label>
                            <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Degree</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->degree ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Institute</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->institute ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Passing Year</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $additionalInfo->passing_year ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Organizational Information Section -->
                @if($organizationalInfo)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                            Organizational Information
                        </flux:heading>
                    </div>
                    <div class="p-6">
                        <!-- Employment Details -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Employment Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Joining Date</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->joining_date ? $organizationalInfo->joining_date->format('d/m/Y') : '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Confirmation Date</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->confirmation_date ? $organizationalInfo->confirmation_date->format('d/m/Y') : '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Expected Confirmation Days</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->expected_confirmation_days ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Contract Start Date</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->contract_start_date ? $organizationalInfo->contract_start_date->format('d/m/Y') : '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Contract End Date</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->contract_end_date ? $organizationalInfo->contract_end_date->format('d/m/Y') : '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Resign Date</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->resign_date ? $organizationalInfo->resign_date->format('d/m/Y') : '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Organizational Structure -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Organizational Structure</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Vendor</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->vendor ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Division</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->division ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Grade</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->grade ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Employee Status</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ ucfirst($organizationalInfo->employee_status ?? '-') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Employee Group</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->employee_group ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Cost Center</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->cost_center ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Region</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->region ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">GL Class</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->gl_class ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Position Type</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->position_type ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Position</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->position ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Station</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->station ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Sub Department</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->sub_department ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Previous Employment -->
                        @if($organizationalInfo->previous_company_name || $organizationalInfo->previous_designation)
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Previous Employment</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Company Name</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->previous_company_name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Designation</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->previous_designation ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">From Date</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->from_date ? $organizationalInfo->from_date->format('d/m/Y') : '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">To Date</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->to_date ? $organizationalInfo->to_date->format('d/m/Y') : '-' }}</p>
                                    </div>
                                </div>
                                @if($organizationalInfo->reason_for_leaving)
                                <div class="mt-4">
                                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Reason for Leaving</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $organizationalInfo->reason_for_leaving }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Salary & Legal Compliance Section -->
                @if($salaryLegalCompliance)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                            Salary & Legal Compliance
                        </flux:heading>
                    </div>
                    <div class="p-6">
                        <!-- Salary Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Salary Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Basic Salary</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                        @if($salaryLegalCompliance->basic_salary)
                                            {{ number_format($salaryLegalCompliance->basic_salary, 2) }} {{ $salaryLegalCompliance->currency ?? 'USD' }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Allowances</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                        @if($salaryLegalCompliance->allowances)
                                            {{ number_format($salaryLegalCompliance->allowances, 2) }} {{ $salaryLegalCompliance->currency ?? 'USD' }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Bonus</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                        @if($salaryLegalCompliance->bonus)
                                            {{ number_format($salaryLegalCompliance->bonus, 2) }} {{ $salaryLegalCompliance->currency ?? 'USD' }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Currency</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $salaryLegalCompliance->currency ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Payment Frequency</label>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ ucfirst($salaryLegalCompliance->payment_frequency ?? '-') }}</p>
                                </div>
                            </div>
                            @if($salaryLegalCompliance->salary_notes)
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Salary Notes</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $salaryLegalCompliance->salary_notes }}</p>
                            </div>
                            @endif
                        </div>

                        <!-- Banking Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Banking Information</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Bank Account</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $salaryLegalCompliance->bank_account ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Account Title</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $salaryLegalCompliance->account_title ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Bank</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $salaryLegalCompliance->bank ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Branch Code</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $salaryLegalCompliance->branch_code ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Legal Compliance -->
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Legal Compliance</h3>
                            <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Tax ID</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $salaryLegalCompliance->tax_id ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">EOBI Registration No</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $salaryLegalCompliance->eobi_registration_no ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">EOBI Entry Date</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $salaryLegalCompliance->eobi_entry_date ? $salaryLegalCompliance->eobi_entry_date->format('d/m/Y') : '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Social Security No</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $salaryLegalCompliance->social_security_no ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Documents & Emergency Contact Section -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                            Documents & Emergency Contact
                        </flux:heading>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Documents -->
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Documents</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Document Type</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ ucfirst($employee->document_type ?? '-') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Document Number</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->document_number ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Issue Date</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->issue_date ? $employee->issue_date->format('d/m/Y') : '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Expiry Date</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->expiry_date ? $employee->expiry_date->format('d/m/Y') : '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Passport No</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->passport_no ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Visa No</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->visa_no ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Visa Expiry</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->visa_expiry ? $employee->visa_expiry->format('d/m/Y') : '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Passport Expiry</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->passport_expiry ? $employee->passport_expiry->format('d/m/Y') : '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact -->
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Emergency Contact</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Contact Name</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->emergency_contact_name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Relation</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ ucfirst($employee->emergency_relation ?? '-') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Phone</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->emergency_phone ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Address</label>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $employee->emergency_address ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <!-- No Employee Data Found -->
                <div class="text-center py-12">
                    <flux:icon name="user-circle" class="mx-auto h-12 w-12 text-zinc-400" />
                    <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                        Employee Not Found
                    </flux:heading>
                    <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                        The employee you're looking for doesn't exist or has been removed.
                    </flux:text>
                </div>
            @endif
        </div>
    </x-employees.layout>
</section>
