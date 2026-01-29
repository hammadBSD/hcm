<section class="w-full">
    @include('partials.employees-heading')
    
    <x-employees.layout :heading="__('Employee Registration')" :subheading="__('Register new employees in the system')">
        @if(session('message'))
            <flux:callout variant="success" icon="check-circle" class="mb-4">
                {{ session('message') }}
            </flux:callout>
        @endif

        @if(session('error'))
            <flux:callout variant="danger" icon="exclamation-circle" class="mb-4">
                {{ session('error') }}
            </flux:callout>
        @endif

        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <!-- Custom Tabs Implementation -->
            <div class="px-6 pt-6">
                <div class="border-b border-zinc-200 dark:border-zinc-700">
                    <nav class="-mb-px flex space-x-8">
                        <button wire:click="$set('activeTab', 'general')" 
                                class="flex items-center gap-1 py-3 px-3 border-b-2 font-medium text-sm {{ $activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                            <flux:icon name="user" class="w-4 h-4" />
                            {{ __('General Info') }}
                        </button>
                        <button wire:click="$set('activeTab', 'additional')" 
                                class="flex items-center gap-1 py-3 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'additional' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                            <flux:icon name="plus-circle" class="w-4 h-4" />
                            {{ __('Additional Info') }}
                        </button>
                        <button wire:click="$set('activeTab', 'company')" 
                                class="flex items-center gap-1 py-3 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'company' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                            <flux:icon name="building-office" class="w-4 h-4" />
                            {{ __('Organizational Info') }}
                        </button>
                        <button wire:click="$set('activeTab', 'salary')" 
                                class="flex items-center gap-1 py-3 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'salary' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                            <flux:icon name="currency-dollar" class="w-4 h-4" />
                            {{ __('Salary & Legal Compliance') }}
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Tab Content -->
            @if($activeTab === 'general')
                <div class="p-6">
                    <div class="space-y-6">
                        <!-- Personal Details - Row 1 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Prefix -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Prefix') }}</flux:label>
                                    <flux:select wire:model="prefix">
                                        <option value="">{{ __('Select Prefix') }}</option>
                                        <option value="Mr">{{ __('Mr') }}</option>
                                        <option value="Mrs">{{ __('Mrs') }}</option>
                                        <option value="Ms">{{ __('Ms') }}</option>
                                        <option value="Dr">{{ __('Dr') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>

                            <!-- Employee Code -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Employee Code') }}</flux:label>
                                    <flux:input wire:model="employeeCode" placeholder="EMP001" />
                                </flux:field>
                            </div>

                            <!-- Punch Code -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Punch Code') }}</flux:label>
                                    <flux:input wire:model="punchCode" placeholder="PC001" />
                                </flux:field>
                            </div>

                            <!-- Mobile -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Mobile') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:input wire:model="mobile" type="tel" placeholder="+1 (555) 123-4567" required />
                                </flux:field>
                            </div>
                        </div>

                        <!-- Personal Details - Row 2 (Name Fields) -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- First Name -->
                            <div class="w-full md:w-1/3 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('First Name') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:input wire:model="firstName" placeholder="John" required />
                                </flux:field>
                            </div>

                            <!-- Last Name -->
                            <div class="w-full md:w-1/3 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Last Name') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:input wire:model="lastName" placeholder="Doe" required />
                                </flux:field>
                            </div>

                            <!-- Father's Name -->
                            <div class="w-full md:w-1/3 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Father\'s Name') }}</flux:label>
                                    <flux:input wire:model="fatherName" placeholder="Robert Doe" />
                                </flux:field>
                            </div>
                        </div>


                        <!-- Organizational Details - Row 1 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Reports To -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Reports To') }}</flux:label>
                                    <flux:select wire:model="reportsTo">
                                        <option value="">{{ __('-- Select Manager --') }}</option>
                                        @foreach($reportsToOptions as $employee)
                                            <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>
                            </div>

                            <!-- Role -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Role') }}</flux:label>
                                    <flux:select wire:model="role">
                                        <option value="">{{ __('-- Select Role --') }}</option>
                                        @foreach($roleOptions as $role)
                                            <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>
                            </div>

                            <!-- Manual Attendance -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Manual Attendance') }}</flux:label>
                                    <flux:select wire:model="manualAttendance">
                                        <option value="no">{{ __('No') }}</option>
                                        <option value="yes">{{ __('Yes') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>

                            <!-- Status -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Status') }}</flux:label>
                                    <flux:select wire:model="status">
                                        <option value="active">{{ __('Active') }}</option>
                                        <option value="inactive">{{ __('Inactive') }}</option>
                                        <option value="on-leave">{{ __('On Leave') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>
                        </div>

                        <!-- Organizational Details - Row 2 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Department -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Department') }}</flux:label>
                                    <flux:select wire:model="department">
                                        <option value="">{{ __('-- Select --') }}</option>
                                        @foreach($departmentOptions as $dept)
                                            <option value="{{ $dept['id'] }}">{{ $dept['title'] }}</option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>
                            </div>

                            <!-- Designation -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Designation') }}</flux:label>
                                    <flux:select wire:model="designation">
                                        <option value="">{{ __('-- Select --') }}</option>
                                        @foreach($designationOptions as $designation)
                                            <option value="{{ $designation['id'] }}">{{ $designation['name'] }}</option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>
                            </div>
                        </div>

                        <!-- Account Details -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Email -->
                            <div class="w-full md:w-1/2 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Email') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:input wire:model="email" type="email" placeholder="john.doe@company.com" required />
                                </flux:field>
                            </div>

                            <!-- Password -->
                            <div class="w-full md:w-1/2 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Password') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:input wire:model="password" type="password" placeholder="••••••••" required />
                                </flux:field>
                            </div>

                            <!-- Shift -->
                            <div class="w-full md:w-1/2 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Shift') }}</flux:label>
                                    <flux:select wire:model="shift">
                                        <option value="">{{ __('-- Select Shift --') }}</option>
                                        @foreach($shiftOptions as $shift)
                                            <option value="{{ $shift['id'] }}">
                                                {{ $shift['name'] }}@if($shift['time_from'] && $shift['time_to']) ({{ $shift['time_from'] }} - {{ $shift['time_to'] }})@endif
                                            </option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>
                            </div>
                        </div>

                        <!-- Documents Section -->
                        <div class="space-y-4">
                            <flux:heading size="md">{{ __('Documents') }}</flux:heading>
                            
                            <div class="flex flex-wrap -mx-2">
                                <!-- Document Type -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Document Type') }}</flux:label>
                                        <flux:select wire:model="documentType">
                                            <option value="">{{ __('-- Select --') }}</option>
                                            <option value="passport">{{ __('Passport') }}</option>
                                            <option value="id-card">{{ __('ID Card') }}</option>
                                            <option value="driving-license">{{ __('Driving License') }}</option>
                                            <option value="birth-certificate">{{ __('Birth Certificate') }}</option>
                                            <option value="other">{{ __('Other') }}</option>
                                        </flux:select>
                                    </flux:field>
                                </div>

                                <!-- Document Number -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Document Number') }}</flux:label>
                                        <flux:input wire:model="documentNumber" placeholder="Document number" />
                                    </flux:field>
                                </div>

                                <!-- Issue Date -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Issue Date') }}</flux:label>
                                        <flux:input wire:model="issueDate" type="date" />
                                    </flux:field>
                                </div>

                                <!-- Expiry Date -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Expiry Date') }}</flux:label>
                                        <flux:input wire:model="expiryDate" type="date" />
                                    </flux:field>
                                </div>
                            </div>

                            <!-- Additional Documents - Row 2 -->
                            <div class="flex flex-wrap -mx-2">
                                <!-- Passport Number -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Passport Number') }}</flux:label>
                                        <flux:input wire:model="passport_no" placeholder="Passport number" />
                                    </flux:field>
                                </div>

                                <!-- Visa Number -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Visa Number') }}</flux:label>
                                        <flux:input wire:model="visa_no" placeholder="Visa number" />
                                    </flux:field>
                                </div>

                                <!-- Visa Expiry -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Visa Expiry') }}</flux:label>
                                        <flux:input wire:model="visa_expiry" type="date" />
                                    </flux:field>
                                </div>

                                <!-- Passport Expiry -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Passport Expiry') }}</flux:label>
                                        <flux:input wire:model="passport_expiry" type="date" />
                                    </flux:field>
                                </div>
                            </div>

                            <!-- File Upload -->
                            <div class="space-y-4">
                                <flux:heading size="md">{{ __('File Upload') }}</flux:heading>
                                <div class="w-full px-2 mb-4">
                                    <flux:field>
                                        <flux:input type="file" wire:model="documentFile" accept=".pdf,.jpg,.png,.doc,.docx" />
                                    </flux:field>
                                </div>
                            </div>
                        </div>

                        <!-- Allow Employee Login -->
                        <div class="flex flex-wrap -mx-2">
                            <div class="w-full px-2 mb-4">
                                <flux:field variant="inline">
                                    <flux:checkbox wire:model="allowEmployeeLogin" />
                                    <flux:label>{{ __('Allow Employee Login') }}</flux:label>
                                </flux:field>
                            </div>
                        </div>

                        <!-- Profile Picture -->
                        <flux:field>
                            <flux:label>{{ __('Profile Picture') }}</flux:label>
                            <flux:input type="file" wire:model="profilePicture" accept="image/*" />
                        </flux:field>

                        <!-- Emergency Contact -->
                        <div class="space-y-3">
                            <flux:heading size="md">{{ __('Emergency Contact') }}</flux:heading>
                            
                            <div class="flex flex-wrap -mx-2">
                                <div class="w-full md:w-1/2 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Contact Name') }}</flux:label>
                                        <flux:input wire:model="emergencyContactName" placeholder="Emergency contact name" />
                                    </flux:field>
                                </div>

                                <div class="w-full md:w-1/2 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Relation') }}</flux:label>
                                        <flux:input wire:model="emergencyRelation" placeholder="Spouse, Parent, etc." />
                                    </flux:field>
                                </div>

                                <div class="w-full md:w-1/2 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Phone') }}</flux:label>
                                        <flux:input wire:model="emergencyPhone" type="tel" placeholder="+1 (555) 123-4567" />
                                    </flux:field>
                                </div>

                                <div class="w-full md:w-1/2 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Address') }}</flux:label>
                                        <flux:textarea wire:model="emergencyAddress" placeholder="Emergency contact address" />
                                    </flux:field>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($activeTab === 'additional')
                <div class="p-6">
                    <div class="space-y-4">
                        <flux:heading size="md">{{ __('Additional Information') }}</flux:heading>
                        
                        <!-- Additional Info - Row 1 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Date of Birth -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Date of Birth') }}</flux:label>
                                    <flux:input wire:model="dateOfBirth" type="date" />
                                </flux:field>
                            </div>

                            <!-- Gender -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Gender') }}</flux:label>
                                    <flux:select wire:model="gender">
                                        <option value="">{{ __('Select Gender') }}</option>
                                        <option value="male">{{ __('Male') }}</option>
                                        <option value="female">{{ __('Female') }}</option>
                                        <option value="other">{{ __('Other') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>

                            <!-- Marital Status -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Marital Status') }}</flux:label>
                                    <flux:select wire:model="maritalStatus">
                                        <option value="">{{ __('Select Status') }}</option>
                                        <option value="single">{{ __('Single') }}</option>
                                        <option value="married">{{ __('Married') }}</option>
                                        <option value="divorced">{{ __('Divorced') }}</option>
                                        <option value="widowed">{{ __('Widowed') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>

                            <!-- Nationality -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Nationality') }}</flux:label>
                                    <flux:input wire:model="nationality" placeholder="American" />
                                </flux:field>
                            </div>
                        </div>

                        <!-- Additional Info - Row 2 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Blood Group -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Blood Group') }}</flux:label>
                                    <flux:select wire:model="bloodGroup">
                                        <option value="">{{ __('Select Blood Group') }}</option>
                                        <option value="A+">{{ __('A+') }}</option>
                                        <option value="A-">{{ __('A-') }}</option>
                                        <option value="B+">{{ __('B+') }}</option>
                                        <option value="B-">{{ __('B-') }}</option>
                                        <option value="AB+">{{ __('AB+') }}</option>
                                        <option value="AB-">{{ __('AB-') }}</option>
                                        <option value="O+">{{ __('O+') }}</option>
                                        <option value="O-">{{ __('O-') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>

                            <!-- Place of Birth -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Place of Birth') }}</flux:label>
                                    <flux:input wire:model="place_of_birth" placeholder="City, Country" />
                                </flux:field>
                            </div>

                            <!-- Religion -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Religion') }}</flux:label>
                                    <flux:input wire:model="religion" placeholder="Religion" />
                                </flux:field>
                            </div>

                            <!-- State -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('State') }}</flux:label>
                                    <flux:input wire:model="state" placeholder="State/Province" />
                                </flux:field>
                            </div>
                        </div>

                        <!-- Address Details - Row 3 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Country -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Country') }}</flux:label>
                                    <flux:input wire:model="country" placeholder="Country" />
                                </flux:field>
                            </div>

                            <!-- Province -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Province') }}</flux:label>
                                    <flux:input wire:model="province" placeholder="Province" />
                                </flux:field>
                            </div>

                            <!-- City -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('City') }}</flux:label>
                                    <flux:input wire:model="city" placeholder="City" />
                                </flux:field>
                            </div>

                            <!-- Area -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Area') }}</flux:label>
                                    <flux:input wire:model="area" placeholder="Area/Neighborhood" />
                                </flux:field>
                            </div>
                        </div>

                        <!-- Address Details - Row 4 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Zip Code -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Zip Code') }}</flux:label>
                                    <flux:input wire:model="zip_code" placeholder="12345" />
                                </flux:field>
                            </div>

                            <!-- Family Code -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Family Code') }}</flux:label>
                                    <flux:input wire:model="family_code" placeholder="Family code" />
                                </flux:field>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="flex flex-wrap -mx-2">
                            <div class="w-full px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Address') }}</flux:label>
                                    <flux:textarea wire:model="address" placeholder="Full address" />
                                </flux:field>
                            </div>
                        </div>

                        <!-- Qualifications Section -->
                        <div class="space-y-4">
                            <flux:heading size="md">{{ __('Qualifications') }}</flux:heading>
                            
                            <div class="flex flex-wrap -mx-2">
                                <!-- Degree -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Degree') }}</flux:label>
                                        <flux:input wire:model="degree" placeholder="Degree" />
                                    </flux:field>
                                </div>

                                <!-- Institute -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Institute') }}</flux:label>
                                        <flux:input wire:model="institute" placeholder="Institute" />
                                    </flux:field>
                                </div>

                                <!-- Passing Year -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Passing Year') }}</flux:label>
                                        <flux:input wire:model="passing_year" placeholder="2020" />
                                    </flux:field>
                                </div>

                                <!-- Grade/CGPA -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Grade/CGPA') }}</flux:label>
                                        <flux:input wire:model="grade" placeholder="3.5" />
                                    </flux:field>
                                </div>

                                <!-- Remove Button -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4 flex items-end">
                                    <flux:button variant="danger" size="sm" icon="trash" wire:click="removeQualification">
                                        {{ __('Remove') }}
                                    </flux:button>
                                </div>
                            </div>

                            <!-- Add Qualification Button -->
                            <flux:button variant="outline" icon="plus" wire:click="addQualification">
                                {{ __('Add Qualification') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            @elseif($activeTab === 'company')
                <div class="p-6">
                    <div class="space-y-4">
                        <flux:heading size="md">{{ __('Company Information') }}</flux:heading>
                        
                        <!-- Company Info - Row 1 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Company Name -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Previous Company Name') }}</flux:label>
                                    <flux:input wire:model="previous_company_name" placeholder="Previous company name" />
                                </flux:field>
                            </div>

                            <!-- Designation -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Previous Designation') }}</flux:label>
                                    <flux:input wire:model="previousDesignation" placeholder="Previous designation" />
                                </flux:field>
                            </div>

                            <!-- From Date -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('From Date') }}</flux:label>
                                    <flux:input wire:model="fromDate" type="date" />
                                </flux:field>
                            </div>

                            <!-- To Date -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('To Date') }}</flux:label>
                                    <flux:input wire:model="toDate" type="date" />
                                </flux:field>
                            </div>
                        </div>

                        <!-- Company Info - Row 2 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Reason for Leaving -->
                            <div class="w-full px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Reason for Leaving') }}</flux:label>
                                    <flux:textarea wire:model="reasonForLeaving" placeholder="Reason for leaving previous company" />
                                </flux:field>
                            </div>
                        </div>

                        <!-- Employment Details Section -->
                        <div class="space-y-4">
                            <flux:heading size="md">{{ __('Employment Details') }}</flux:heading>
                            
                            <!-- Employment Details - Row 1 -->
                            <div class="flex flex-wrap -mx-2">
                                <!-- Joining Date -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Joining Date') }}</flux:label>
                                        <flux:input wire:model="joining_date" type="date" />
                                    </flux:field>
                                </div>

                                <!-- Confirmation Date -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Confirmation Date') }}</flux:label>
                                        <flux:input wire:model="confirmation_date" type="date" />
                                    </flux:field>
                                </div>

                                <!-- Expected Confirmation Days -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Expected Confirmation Days') }}</flux:label>
                                        <flux:input wire:model="expected_confirmation_days" type="number" placeholder="90" />
                                    </flux:field>
                                </div>

                                <!-- Contract Start Date -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Contract Start Date') }}</flux:label>
                                        <flux:input wire:model="contract_start_date" type="date" />
                                    </flux:field>
                                </div>
                            </div>

                            <!-- Employment Details - Row 2 -->
                            <div class="flex flex-wrap -mx-2">
                                <!-- Contract End Date -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Contract End Date') }}</flux:label>
                                        <flux:input wire:model="contract_end_date" type="date" />
                                    </flux:field>
                                </div>

                                <!-- Resign Date -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Resign Date') }}</flux:label>
                                        <flux:input wire:model="resign_date" type="date" />
                                    </flux:field>
                                </div>

                                <!-- Leaving Date -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Leaving Date') }}</flux:label>
                                        <flux:input wire:model="leaving_date" type="date" />
                                    </flux:field>
                                </div>
                            </div>

                            <!-- Leaving Reason -->
                            <div class="flex flex-wrap -mx-2">
                                <div class="w-full px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Leaving Reason') }}</flux:label>
                                        <flux:textarea wire:model="leaving_reason" placeholder="Reason for leaving current employment" />
                                    </flux:field>
                                </div>
                            </div>
                        </div>

                        <!-- Organizational Section -->
                        <div class="space-y-4">
                            <flux:heading size="md">{{ __('Organizational') }}</flux:heading>
                            
                            <!-- Organizational - Row 1 -->
                            <div class="flex flex-wrap -mx-2">
                                <!-- Vendor -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Vendor') }}</flux:label>
                                        <flux:input wire:model="vendor" placeholder="Vendor name" />
                                    </flux:field>
                                </div>

                                <!-- Division -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Division') }}</flux:label>
                                        <flux:input wire:model="division" placeholder="Division" />
                                    </flux:field>
                                </div>

                                <!-- Grade -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Grade') }}</flux:label>
                                        <flux:input wire:model="grade" placeholder="Grade" />
                                    </flux:field>
                                </div>

                                <!-- Employee Status -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Employee Status') }}</flux:label>
                                        <flux:select wire:model="employee_status">
                                            <option value="">{{ __('Select Status') }}</option>
                                            <option value="permanent">{{ __('Permanent') }}</option>
                                            <option value="probation">{{ __('Probation') }}</option>
                                            <option value="terminated">{{ __('Terminated') }}</option>
                                            <option value="resigned">{{ __('Resigned') }}</option>
                                        </flux:select>
                                    </flux:field>
                                </div>
                            </div>

                            <!-- Organizational - Row 2 -->
                            <div class="flex flex-wrap -mx-2">
                                <!-- Employee Group -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Employee Group') }}</flux:label>
                                        <flux:select wire:model="group_id">
                                            <option value="">{{ __('Select Group') }}</option>
                                            @foreach($groupOptions as $group)
                                                <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                                            @endforeach
                                        </flux:select>
                                    </flux:field>
                                </div>

                                <!-- Cost Center -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Cost Center') }}</flux:label>
                                        <flux:input wire:model="cost_center" placeholder="Cost center" />
                                    </flux:field>
                                </div>

                                <!-- Region -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Region') }}</flux:label>
                                        <flux:input wire:model="region" placeholder="Region" />
                                    </flux:field>
                                </div>

                                <!-- GL Class -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('GL Class') }}</flux:label>
                                        <flux:input wire:model="gl_class" placeholder="GL class" />
                                    </flux:field>
                                </div>
                            </div>

                            <!-- Organizational - Row 3 -->
                            <div class="flex flex-wrap -mx-2">
                                <!-- Position Type -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Position Type') }}</flux:label>
                                        <flux:select wire:model="position_type">
                                            <option value="">{{ __('Select Position Type') }}</option>
                                            <option value="full-time">{{ __('Full Time') }}</option>
                                            <option value="part-time">{{ __('Part Time') }}</option>
                                            <option value="contract">{{ __('Contract') }}</option>
                                            <option value="temporary">{{ __('Temporary') }}</option>
                                            <option value="intern">{{ __('Intern') }}</option>
                                        </flux:select>
                                    </flux:field>
                                </div>

                                <!-- Position -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Position') }}</flux:label>
                                        <flux:input wire:model="position" placeholder="Position title" />
                                    </flux:field>
                                </div>

                                <!-- Station -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Station') }}</flux:label>
                                        <flux:input wire:model="station" placeholder="Station/Location" />
                                    </flux:field>
                                </div>

                                <!-- Sub Department -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Sub Department') }}</flux:label>
                                        <flux:input wire:model="sub_department" placeholder="Sub department" />
                                    </flux:field>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($activeTab === 'salary')
                <div class="p-6">
                    <div class="space-y-4">
                        <flux:heading size="md">{{ __('Salary Information') }}</flux:heading>
                        
                        <!-- Salary Info - Row 1 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Basic Salary -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Basic Salary') }}</flux:label>
                                    <flux:input wire:model="basicSalary" type="number" placeholder="50000" />
                                </flux:field>
                            </div>

                            <!-- Allowances -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Allowances') }}</flux:label>
                                    <flux:input wire:model="allowances" type="number" placeholder="5000" />
                                </flux:field>
                            </div>

                            <!-- Bonus -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Annual Bonus') }}</flux:label>
                                    <flux:input wire:model="bonus" type="number" placeholder="10000" />
                                </flux:field>
                            </div>

                            <!-- Currency -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Currency') }}</flux:label>
                                    <flux:select wire:model="currency">
                                        <option value="USD">{{ __('USD ($)') }}</option>
                                        <option value="EUR">{{ __('EUR (€)') }}</option>
                                        <option value="GBP">{{ __('GBP (£)') }}</option>
                                        <option value="PKR">{{ __('PKR (₨)') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>
                        </div>

                        <!-- Salary Info - Row 2 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Payment Frequency -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Payment Frequency') }}</flux:label>
                                    <flux:select wire:model="paymentFrequency">
                                        <option value="monthly">{{ __('Monthly') }}</option>
                                        <option value="bi-weekly">{{ __('Bi-weekly') }}</option>
                                        <option value="weekly">{{ __('Weekly') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>

                            <!-- Bank Account Number -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Bank Account Number') }}</flux:label>
                                    <flux:input wire:model="bankAccount" placeholder="1234567890" />
                                </flux:field>
                            </div>

                            <!-- Account Title -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Account Title') }}</flux:label>
                                    <flux:input wire:model="account_title" placeholder="Account holder name" />
                                </flux:field>
                            </div>

                            <!-- Tax ID -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Tax ID/SSN') }}</flux:label>
                                    <flux:input wire:model="tax_id" placeholder="123-45-6789" />
                                </flux:field>
                            </div>
                        </div>

                        <!-- Banking Details - Row 3 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Bank -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Bank') }}</flux:label>
                                    <flux:input wire:model="bank" placeholder="Bank name" />
                                </flux:field>
                            </div>

                            <!-- Branch Code -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Branch Code') }}</flux:label>
                                    <flux:input wire:model="branch_code" placeholder="Branch code" />
                                </flux:field>
                            </div>
                        </div>

                        <!-- Salary Info - Row 3 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Salary Notes -->
                            <div class="w-full px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Salary Notes') }}</flux:label>
                                    <flux:textarea 
                                        wire:model="salaryNotes" 
                                        placeholder="Additional notes about salary, benefits, or compensation details..."
                                        rows="3"
                                        resize="vertical"
                                    />
                                </flux:field>
                            </div>
                        </div>

                        <!-- Legal/Compliance Section -->
                        <div class="space-y-4">
                            <flux:heading size="md">{{ __('Legal/Compliance') }}</flux:heading>
                            
                            <!-- Legal/Compliance - Row 1 -->
                            <div class="flex flex-wrap -mx-2">
                                <!-- EOBI Registration Number -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('EOBI Registration Number') }}</flux:label>
                                        <flux:input wire:model="eobi_registration_no" placeholder="EOBI registration number" />
                                    </flux:field>
                                </div>

                                <!-- EOBI Entry Date -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('EOBI Entry Date') }}</flux:label>
                                        <flux:input wire:model="eobi_entry_date" type="date" />
                                    </flux:field>
                                </div>

                                <!-- Social Security Number -->
                                <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                    <flux:field>
                                        <flux:label>{{ __('Social Security Number') }}</flux:label>
                                        <flux:input wire:model="social_security_no" placeholder="SSN" />
                                    </flux:field>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Actions -->
            <div class="flex items-center justify-between p-6 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-700">
                <div class="flex items-center gap-3">
                    <flux:button variant="ghost" wire:click="resetForm">
                        {{ __('Reset') }}
                    </flux:button>
                    <flux:button variant="outline" wire:click="saveDraft">
                        {{ __('Save Draft') }}
                    </flux:button>
                </div>
                
                <div class="flex items-center gap-3">
                    <flux:button variant="outline" wire:click="previousTab" :disabled="$activeTab === 'general'">
                        {{ __('Previous') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="nextTab" :disabled="$activeTab === 'salary'">
                        {{ __('Next') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="submit">
                        {{ __('Create Employee') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </x-employees.layout>
</section>
