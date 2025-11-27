<section class="w-full">
    @include('partials.system-management-heading')
    
    <x-system-management.layout :heading="__('Break Settings')" :subheading="__('Configure break tracking and related settings')">
        <!-- Flash Messages -->
        @if (session()->has('message'))
            <flux:callout variant="success" icon="check-circle" dismissible>
                {{ session('message') }}
            </flux:callout>
        @endif

        <div class="space-y-6">
            <!-- General Break Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('General Break Settings') }}</h3>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="settings.enable_break_tracking"
                        label="{{ __('Enable Break Tracking') }}"
                        description="{{ __('Track and calculate employee break times throughout the day') }}"
                    />
                    <flux:separator variant="subtle" />
                    
                    <flux:switch
                        wire:model.live="settings.show_in_attendance_grid"
                        label="{{ __('Show in Attendance Grid') }}"
                        description="{{ __('Display break information in the attendance records table') }}"
                    />
                    <flux:separator variant="subtle" />
                    
                    <flux:switch
                        wire:model.live="settings.break_notifications"
                        label="{{ __('Break Notifications') }}"
                        description="{{ __('Send notifications when employees take breaks or exceed break limits') }}"
                    />
                    <flux:separator variant="subtle" />
                    
                    <flux:field>
                        <flux:label>{{ __('Allowed Break Time (minutes)') }}</flux:label>
                        <flux:input 
                            type="number" 
                            min="0" 
                            wire:model="settings.allowed_break_time" 
                            placeholder="e.g. 60" 
                        />
                        <flux:description>{{ __('Allowed break time for employees (this time will not be deducted from working hours)') }}</flux:description>
                    </flux:field>
                </div>
            </div>

            <!-- Payroll Integration Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Payroll Integration') }}</h3>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="settings.use_breaks_in_payroll"
                        label="{{ __('Use Breaks in Payroll') }}"
                        description="{{ __('Include break times in payroll calculations and deductions') }}"
                    />
                    <flux:separator variant="subtle" />
                    
                    <flux:switch
                        wire:model.live="settings.use_in_salary_deductions"
                        label="{{ __('Use in Salary Deductions') }}"
                        description="{{ __('Apply break time deductions to employee salaries') }}"
                    />
                    <flux:separator variant="subtle" />
                    
                    <flux:switch
                        wire:model.live="settings.auto_deduct_breaks"
                        label="{{ __('Auto Deduct Breaks') }}"
                        description="{{ __('Automatically deduct break time from total working hours') }}"
                    />
                    <flux:separator variant="subtle" />
                    
                    <flux:switch
                        wire:model.live="settings.break_overtime_calculation"
                        label="{{ __('Break Overtime Calculation') }}"
                        description="{{ __('Consider break times when calculating overtime hours') }}"
                    />
                </div>
            </div>

            <!-- Advanced Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Advanced Settings') }}</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="space-y-4">
                        <flux:switch
                            wire:model.live="settings.mandatory_break_duration_enabled"
                            label="{{ __('Mandatory Break Duration') }}"
                            description="{{ __('Enforce minimum break duration requirements for employees') }}"
                        />

                        @if($settings['mandatory_break_duration_enabled'] ?? false)
                            <flux:field>
                                <flux:label>{{ __('Minimum Break Duration (minutes)') }}</flux:label>
                                <flux:input type="number" min="0" wire:model="settings.mandatory_break_duration_minutes" placeholder="e.g. 30" />
                            </flux:field>
                        @endif
                    </div>
                    <flux:separator variant="subtle" />
                    
                    <!-- Add Exclusion Button -->
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:label>{{ __('Employee Exclusions') }}</flux:label>
                            <flux:description>{{ __('Manage employees and roles excluded from break tracking') }}</flux:description>
                        </div>
                        <flux:button variant="outline" icon="plus" wire:click="openExclusionFlyout">
                            {{ __('Add Exclusion') }}
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="p-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="w-full lg:w-1/2 flex flex-col gap-3">
                            <div>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300 flex items-center gap-2">
                                    <flux:icon name="user" class="w-4 h-4" />
                                    {{ __('User Exclusions') }}
                                </span>
                                @if(!empty($existingUserExclusions))
                                    <ul class="mt-2 space-y-2">
                                        @foreach($existingUserExclusions as $exclusion)
                                            <li class="flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-200 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded px-3 py-2">
                                                <span>
                                                    {{ $exclusion['name'] }}
                                                    @if($exclusion['email'])
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ $exclusion['email'] }})</span>
                                                    @endif
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <flux:description class="mt-2">{{ __('No user exclusions configured.') }}</flux:description>
                                @endif
                            </div>

                            <div>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300 flex items-center gap-2">
                                    <flux:icon name="shield-check" class="w-4 h-4" />
                                    {{ __('Role Exclusions') }}
                                </span>
                                @if(!empty($existingRoleExclusions))
                                    <ul class="mt-2 space-y-2">
                                        @foreach($existingRoleExclusions as $exclusion)
                                            <li class="flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-200 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded px-3 py-2">
                                                <span>{{ $exclusion['name'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <flux:description class="mt-2">{{ __('No role exclusions configured.') }}</flux:description>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-3 ml-auto">
                            <flux:button variant="outline" wire:click="resetToDefaults">
                                {{ __('Reset to Defaults') }}
                            </flux:button>

                            <flux:button variant="primary" wire:click="saveAllSettings">
                                {{ __('Save All Settings') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Exclusion Flyout -->
        <flux:modal variant="flyout" :open="$showExclusionFlyout" wire:model="showExclusionFlyout">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Add Exclusion') }}</flux:heading>
                    <flux:subheading>{{ __('Select employees or roles to exclude from break tracking') }}</flux:subheading>
                </div>
                
                <!-- Exclusion Type Dropdown -->
                <flux:field>
                    <flux:label>{{ __('Add exclusion on') }}</flux:label>
                    <flux:select wire:model.live="exclusionType">
                        <option value="users">{{ __('Users') }}</option>
                        <option value="roles">{{ __('Roles') }}</option>
                    </flux:select>
                </flux:field>
                
                <!-- Conditional Multi-Select for Users -->
                @if($exclusionType === 'users')
                    <div class="space-y-3">
                        <flux:field>
                            <flux:label>{{ __('Select Users') }}</flux:label>
                            
                            <!-- Search Input for Users -->
                            <div class="mb-3">
                                <flux:field>
                                    <flux:input 
                                        wire:model.live.debounce.300ms="userSearchTerm"
                                        placeholder="Search users..."
                                        icon="magnifying-glass"
                                    />
                                </flux:field>
                            </div>
                            
                            <div class="relative">
                                <select 
                                    wire:model.live="selectedUsers" 
                                    multiple 
                                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                                    size="6"
                                    style="min-height: 150px;"
                                >
                                    @foreach($filteredEmployees as $employee)
                                        <option value="{{ $employee['value'] }}" class="py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:bg-blue-100 dark:focus:bg-blue-900">
                                            {{ $employee['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <flux:description>{{ __('Search and select specific users to exclude from break tracking. Hold Ctrl/Cmd to select multiple users.') }}</flux:description>
                        </flux:field>
                        
                        <!-- Selected Users Display -->
                        @if(count($selectedUsers) > 0)
                            <div class="mt-4">
                                <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3 flex items-center gap-2">
                                    <flux:icon name="check-circle" class="w-4 h-4 text-green-500" />
                                    Selected Users ({{ count($selectedUsers) }})
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($selectedUsers as $userId)
                                        @php
                                            $selectedEmployee = collect($employees)->firstWhere('value', $userId);
                                        @endphp
                                        @if($selectedEmployee)
                                            <span class="inline-flex items-center gap-2 px-3 py-2 bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 rounded-lg text-sm border border-blue-200 dark:border-blue-700 shadow-sm">
                                                <flux:icon name="user" class="w-3 h-3 text-blue-700 dark:text-blue-200" />
                                                {{ $selectedEmployee['name'] }}
                                                <button 
                                                    type="button" 
                                                    wire:click="removeUser({{ $userId }})" 
                                                    class="ml-1 text-blue-600 dark:text-blue-200 hover:text-blue-800 dark:hover:text-blue-100 transition-colors p-1 rounded hover:bg-blue-200 dark:hover:bg-blue-700"
                                                >
                                                    <flux:icon name="x-mark" class="w-3 h-3" />
                                                </button>
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
                
                <!-- Conditional Multi-Select for Roles -->
                @if($exclusionType === 'roles')
                    <div class="space-y-3">
                        <flux:field>
                            <flux:label>{{ __('Select Roles') }}</flux:label>
                            
                            <!-- Search Input for Roles -->
                            <div class="mb-3">
                                <flux:field>
                                    <flux:input 
                                        wire:model.live.debounce.300ms="roleSearchTerm"
                                        placeholder="Search roles..."
                                        icon="magnifying-glass"
                                    />
                                </flux:field>
                            </div>
                            
                            <div class="relative">
                                <select 
                                    wire:model.live="selectedRoles" 
                                    multiple 
                                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                                    size="6"
                                    style="min-height: 150px;"
                                >
                                    @foreach($filteredRoles as $role)
                                        <option value="{{ $role['value'] }}" class="py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:bg-green-100 dark:focus:bg-green-900">
                                            {{ $role['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <flux:description>{{ __('Search and select roles to exclude all users with these roles from break tracking. Hold Ctrl/Cmd to select multiple roles.') }}</flux:description>
                        </flux:field>
                        
                        <!-- Selected Roles Display -->
                        @if(count($selectedRoles) > 0)
                            <div class="mt-4">
                                <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3 flex items-center gap-2">
                                    <flux:icon name="shield-check" class="w-4 h-4 text-green-500" />
                                    Selected Roles ({{ count($selectedRoles) }})
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($selectedRoles as $roleId)
                                        @php
                                            $selectedRole = collect($roles)->firstWhere('value', $roleId);
                                        @endphp
                                        @if($selectedRole)
                                            <span class="inline-flex items-center gap-2 px-3 py-2 bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100 rounded-lg text-sm border border-green-200 dark:border-green-700 shadow-sm">
                                                <flux:icon name="shield-check" class="w-3 h-3 text-green-700 dark:text-green-200" />
                                                {{ $selectedRole['name'] }}
                                                <button 
                                                    type="button" 
                                                    wire:click="removeRole({{ $roleId }})" 
                                                    class="ml-1 text-green-600 dark:text-green-200 hover:text-green-800 dark:hover:text-green-100 transition-colors p-1 rounded hover:bg-green-200 dark:hover:bg-green-700"
                                                >
                                                    <flux:icon name="x-mark" class="w-3 h-3" />
                                                </button>
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
                
                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeExclusionFlyout">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="saveExclusions">
                        {{ __('Save Exclusions') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </x-system-management.layout>
</section>
