<section class="w-full">
    @include('partials.employees-heading')
    
    <x-employees.layout :heading="__('Employee Registration')" :subheading="__('Register new employees in the system')">
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
                            {{ __('Additional') }}
                        </button>
                        <button wire:click="$set('activeTab', 'company')" 
                                class="flex items-center gap-1 py-3 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'company' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                            <flux:icon name="building-office" class="w-4 h-4" />
                            {{ __('Company Info') }}
                        </button>
                        <button wire:click="$set('activeTab', 'documents')" 
                                class="flex items-center gap-1 py-3 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'documents' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                            <flux:icon name="document-text" class="w-4 h-4" />
                            {{ __('Documents & Qualifications') }}
                        </button>
                        <button wire:click="$set('activeTab', 'salary')" 
                                class="flex items-center gap-1 py-3 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'salary' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                            <flux:icon name="currency-dollar" class="w-4 h-4" />
                            {{ __('Salary') }}
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
                                        <option value="1">{{ __('John Smith') }}</option>
                                        <option value="2">{{ __('Jane Doe') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>

                            <!-- Role -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Role') }}</flux:label>
                                    <flux:input wire:model="role" placeholder="Enter role" />
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
                                        <option value="hr">{{ __('Human Resources') }}</option>
                                        <option value="it">{{ __('Information Technology') }}</option>
                                        <option value="finance">{{ __('Finance') }}</option>
                                        <option value="marketing">{{ __('Marketing') }}</option>
                                        <option value="sales">{{ __('Sales') }}</option>
                                        <option value="operations">{{ __('Operations') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>

                            <!-- Designation -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Designation') }}</flux:label>
                                    <flux:select wire:model="designation">
                                        <option value="">{{ __('-- Select --') }}</option>
                                        <option value="ceo">{{ __('CEO') }}</option>
                                        <option value="manager">{{ __('Manager') }}</option>
                                        <option value="senior">{{ __('Senior') }}</option>
                                        <option value="junior">{{ __('Junior') }}</option>
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
                                        <option value="morning">{{ __('Morning (9 AM - 5 PM)') }}</option>
                                        <option value="evening">{{ __('Evening (2 PM - 10 PM)') }}</option>
                                        <option value="night">{{ __('Night (10 PM - 6 AM)') }}</option>
                                    </flux:select>
                                </flux:field>
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

                            <!-- Address -->
                            <div class="w-full md:w-1/2 lg:w-3/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Address') }}</flux:label>
                                    <flux:textarea wire:model="address" placeholder="Full address" />
                                </flux:field>
                            </div>
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
                                    <flux:label>{{ __('Company Name') }}</flux:label>
                                    <flux:input wire:model="companyName" placeholder="Previous company name" />
                                </flux:field>
                            </div>

                            <!-- Designation -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Designation') }}</flux:label>
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
                    </div>
                </div>
            @elseif($activeTab === 'documents')
                <div class="p-6">
                    <div class="space-y-6">
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
                                        <flux:input wire:model="passingYear" placeholder="2020" />
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
            @elseif($activeTab === 'salary')
                <div class="p-6">
                    <div class="space-y-4">
                        <flux:heading size="md">{{ __('Salary Information') }}</flux:heading>
                        
                        <!-- Salary Info - Row 1 -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Basic Salary -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Basic Salary') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:input wire:model="basicSalary" type="number" placeholder="50000" required />
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

                            <!-- Bank Details -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Bank Account Number') }}</flux:label>
                                    <flux:input wire:model="bankAccount" placeholder="1234567890" />
                                </flux:field>
                            </div>

                            <!-- Tax ID -->
                            <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                                <flux:field>
                                    <flux:label>{{ __('Tax ID/SSN') }}</flux:label>
                                    <flux:input wire:model="taxId" placeholder="123-45-6789" />
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
