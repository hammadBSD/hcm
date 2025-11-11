<section class="w-full">
    @include('partials.attendance-heading')

    <x-attendance.layout :heading="__('My Attendance')" :subheading="__('Your attendance records for ' . ($selectedMonth ? \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') : $currentMonth))">
        <div class="space-y-6 w-full max-w-none">
            @if($employee && $punchCode)
                <!-- Attendance Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Working Days -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Working Days</flux:text>
                                <flux:heading size="xl" class="text-zinc-900 dark:text-zinc-100">{{ $attendanceStats['working_days'] ?? 0 }}</flux:heading>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <flux:icon name="calendar-days" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Present Days -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Present Days</flux:text>
                                <flux:heading size="xl" class="text-green-600 dark:text-green-400">{{ $attendanceStats['attended_days'] ?? 0 }}</flux:heading>
                            </div>
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <flux:icon name="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Absent Days -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Absent Days</flux:text>
                                <flux:heading size="xl" class="text-red-600 dark:text-red-400">{{ $attendanceStats['absent_days'] ?? 0 }}</flux:heading>
                            </div>
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                <flux:icon name="x-circle" class="w-6 h-6 text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Late Days -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Late Days</flux:text>
                                <flux:heading size="xl" class="text-zinc-900 dark:text-zinc-100">{{ $attendanceStats['late_days'] ?? 0 }}</flux:heading>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <flux:icon name="clock" class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Working Hours Summary -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="lg">Working Hours Summary</flux:heading>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <div>
                                    <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Hours Worked</flux:text>
                                    <flux:heading size="xl" class="text-blue-600 dark:text-blue-400">{{ $attendanceStats['total_hours'] ?? '0:00' }}</flux:heading>
                                </div>
                                <flux:icon name="clock" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div>
                                    <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Expected Hours</flux:text>
                                    <flux:heading size="xl" class="text-green-600 dark:text-green-400">{{ $attendanceStats['expected_hours'] ?? '0:00' }}</flux:heading>
                                </div>
                                <flux:icon name="check-circle" class="w-8 h-8 text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Attendance Records -->
                <div class="mt-8">
                    @if(count($attendanceData) > 0)
                        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center justify-between">
                                    <flux:heading size="lg">Recent Attendance Records</flux:heading>
                                    <div class="flex items-center gap-3">
                                        @php
                                            $currentUser = auth()->user();
                                        @endphp
                                        @if($currentUser && ($currentUser->can('attendance.switch_user') || $currentUser->hasRole('Super Admin')))
                                            <div
                                                class="text-zinc-400 dark:text-zinc-500 hidden md:flex items-center justify-center"
                                                wire:loading.flex
                                                wire:target="selectedUserId, selectedMonth"
                                            >
                                                <flux:icon name="arrow-path" class="w-5 h-5 animate-spin" />
                                            </div>
                                            <flux:select
                                                wire:model.live="selectedUserId"
                                                placeholder="Select User"
                                                class="w-64"
                                                wire:loading.attr="disabled"
                                                wire:target="selectedUserId, selectedMonth"
                                            >
                                                @if(!$selectedUserId)
                                                    <option value="">{{ Auth::user()->name ?? 'Current User' }}</option>
                                                @endif
                                                @foreach($availableUsers as $user)
                                                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                                                @endforeach
                                            </flux:select>
                                        @endif
                                        <flux:select
                                            wire:model.live="selectedMonth"
                                            placeholder="{{ $currentMonth }}"
                                            class="w-40"
                                            wire:loading.attr="disabled"
                                            wire:target="selectedUserId, selectedMonth"
                                        >
                                            <option value="">{{ $currentMonth }} (Current)</option>
                                            @foreach($availableMonths as $month)
                                                <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                                            @endforeach
                                        </flux:select>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto" wire:loading.class="opacity-50" wire:target="selectedUserId, selectedMonth">
                                <table class="w-full">
                                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                <button wire:click="sort('date')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                    {{ __('Date') }}
                                                    @if($sortBy === 'date')
                                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                    @endif
                                                </button>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                <button wire:click="sort('day_name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                    {{ __('Day') }}
                                                    @if($sortBy === 'day_name')
                                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                    @endif
                                                </button>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                <button wire:click="sort('check_in')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                    {{ __('Check In') }}
                                                    @if($sortBy === 'check_in')
                                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                    @endif
                                                </button>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                <button wire:click="sort('check_out')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                    {{ __('Check Out') }}
                                                    @if($sortBy === 'check_out')
                                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                    @endif
                                                </button>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                <button wire:click="sort('breaks')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                    {{ __('Breaks') }}
                                                    @if($sortBy === 'breaks')
                                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                    @endif
                                                </button>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                <button wire:click="sort('total_hours')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                    {{ __('Total Hours') }}
                                                    @if($sortBy === 'total_hours')
                                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                    @endif
                                                </button>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                <button wire:click="sort('status')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                    {{ __('Status') }}
                                                    @if($sortBy === 'status')
                                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                    @endif
                                                </button>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Actions') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach($attendanceData as $record)
                                            <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                                <td class="px-6 py-6 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $record['formatted_date'] }}
                                                    </div>
                                                </td>
                                                
                                                <td class="px-6 py-6 whitespace-nowrap">
                                                    <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                        {{ $record['day_name'] }}
                                                    </div>
                                                </td>
                                                
                                                <td class="px-6 py-6 whitespace-nowrap">
                                                    <div class="flex flex-col gap-1">
                                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                            {{ $record['check_in'] ?? '-' }}
                                                        </div>
                                                        @if(isset($record['is_late']) && $record['is_late'])
                                                            <!-- <flux:badge color="red" size="xs" class="w-fit">
                                                                <flux:icon name="clock" class="w-3 h-3 mr-1" />
                                                                Late
                                                            </flux:badge> -->
                                                        @endif
                                                    </div>
                                                </td>
                                                
                                                <td class="px-6 py-6 whitespace-nowrap">
                                                    <div class="flex flex-col gap-1">
                                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                            {{ $record['check_out'] ?? '-' }}
                                                        </div>
                                                        @if(isset($record['is_early']) && $record['is_early'])
                                                            <!-- <flux:badge color="orange" size="xs" class="w-fit">
                                                                <flux:icon name="clock" class="w-3 h-3 mr-1" />
                                                                Early
                                                            </flux:badge> -->
                                                        @endif
                                                    </div>
                                                </td>
                                                
                                                <td class="px-6 py-6 whitespace-nowrap">
                                                    @if(isset($record['break_details']) && count($record['break_details']) > 0)
                                                        <flux:tooltip>
                                                            <div class="text-sm text-zinc-900 dark:text-zinc-100 cursor-help">
                                                                {{ $record['breaks'] ?? '-' }}
                                                            </div>
                                                            <flux:tooltip.content class="max-w-[20rem]">
                                                                <div class="space-y-2">
                                                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                                        {{ __('Break Details') }}
                                                                    </div>
                                                                    @foreach($record['break_details'] as $index => $break)
                                                                        <div class="text-sm py-1">
                                                                            <div class="flex items-center gap-2">
                                                                                @if($break['start'] === '--')
                                                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-medium">
                                                                                        <flux:icon name="exclamation-circle" class="w-3 h-3" />
                                                                                        Missing Check-out
                                                                                    </span>
                                                                                @else
                                                                                    <span class="font-medium {{ isset($break['start_manual']) && $break['start_manual'] ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                                                                                        {{ $break['start'] }}
                                                                                        @if(isset($break['start_manual']) && $break['start_manual'])
                                                                                            <flux:icon name="pencil" class="w-3 h-3 inline ml-1" />
                                                                                        @endif
                                                                                    </span>
                                                                                @endif
                                                                                
                                                                                <flux:icon name="arrow-right" class="w-3 h-3 text-zinc-500 dark:text-zinc-400" />
                                                                                
                                                                                @if($break['end'] === '--')
                                                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-medium">
                                                                                        <flux:icon name="exclamation-circle" class="w-3 h-3" />
                                                                                        Missing Check-in
                                                                                    </span>
                                                                                @else
                                                                                    <span class="font-medium {{ isset($break['end_manual']) && $break['end_manual'] ? 'text-blue-600 dark:text-blue-400' : 'text-green-600 dark:text-green-400' }}">
                                                                                        {{ $break['end'] }}
                                                                                        @if(isset($break['end_manual']) && $break['end_manual'])
                                                                                            <flux:icon name="pencil" class="w-3 h-3 inline ml-1" />
                                                                                        @endif
                                                                                    </span>
                                                                                @endif
                                                                                
                                                                                @if($break['duration'] === '--')
                                                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-medium">
                                                                                        N/A
                                                                                    </span>
                                                                                @else
                                                                                    <span class="text-zinc-500 dark:text-zinc-400">({{ $break['duration'] }})</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </flux:tooltip.content>
                                                        </flux:tooltip>
                                                    @else
                                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                            {{ $record['breaks'] ?? '-' }}
                                                        </div>
                                                    @endif
                                                </td>
                                                
                                                <td class="px-6 py-6 whitespace-nowrap">
                                                    <div class="flex items-center gap-2">
                                                        <div class="text-sm {{ ($record['total_hours'] ?? '-') === 'N/A' ? 'text-red-600 dark:text-red-400 font-medium' : 'text-zinc-900 dark:text-zinc-100' }}">
                                                            {{ $record['total_hours'] ?? '-' }}
                                                        </div>
                                                        @if(isset($record['has_manual_entries']) && $record['has_manual_entries'])
                                                            <flux:tooltip>
                                                                <flux:badge color="blue" size="xs" class="cursor-help">
                                                                    M
                                                                </flux:badge>
                                                                <flux:tooltip.content>
                                                                    <div class="text-sm">
                                                                        {{ __('Manual Entry') }}
                                                                    </div>
                                                                </flux:tooltip.content>
                                                            </flux:tooltip>
                                                        @endif
                                                    </div>
                                                </td>
                                                
                                                <td class="px-6 py-6 whitespace-nowrap">
                                                    @php
                                                        $statusColor = match($record['status']) {
                                                            'present' => 'green',
                                                            'present_late' => 'yellow',
                                                            'present_early' => 'orange',
                                                            'present_late_early' => 'amber',
                                                            'off' => 'zinc',
                                                            'absent' => 'red',
                                                            default => 'zinc'
                                                        };
                                                        
                                                        $statusLabel = match($record['status']) {
                                                            'present' => 'Present',
                                                            'present_late' => 'Present (Late)',
                                                            'present_early' => 'Present (Early)',
                                                            'present_late_early' => 'Present (Late & Early)',
                                                            'off' => 'Off Day',
                                                            'absent' => 'Absent',
                                                            default => ucfirst($record['status'])
                                                        };
                                                    @endphp
                                                    <div class="flex flex-col gap-1">
                                                        <flux:badge color="{{ $statusColor }}" size="sm">
                                                            {{ $statusLabel }}
                                                        </flux:badge>
                                                        @if(isset($record['shift_name']) && $record['shift_name'])
                                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                                Shift: {{ $record['shift_name'] }}
                                                            </div>
                                                        @else
                                                            <div class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                                                <flux:icon name="exclamation-triangle" class="w-3 h-3" />
                                                                No Shift Assigned
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                
                                                <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                                    @php
                                                        $canManageMissing = auth()->user()?->can('attendance.manage.missing_entries');
                                                        $hasManualEntries = isset($record['has_manual_entries']) && $record['has_manual_entries'];
                                                        $isAbsent = $record['status'] === 'absent';
                                                        $shouldShowMenu = ($canManageMissing || $hasManualEntries || $isAbsent);
                                                    @endphp

                                                    @if($shouldShowMenu)
                                                        <div class="flex items-center gap-1">
                                                            <flux:dropdown>
                                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                                <flux:menu>
                                                                    @if($canManageMissing)
                                                                        <flux:menu.item icon="plus-circle" wire:click="openMissingEntryFlyout('{{ $record['date'] }}')">
                                                                            {{ __('Add Missing Entry') }}
                                                                        </flux:menu.item>
                                                                        @if($hasManualEntries || $isAbsent)
                                                                            <flux:menu.separator />
                                                                        @endif
                                                                    @endif

                                                                    @if($hasManualEntries)
                                                                        <flux:menu.item icon="eye" wire:click="openViewChangesFlyout('{{ $record['date'] }}')">
                                                                            {{ __('View Changes') }}
                                                                        </flux:menu.item>
                                                                        @if($isAbsent)
                                                                            <flux:menu.separator />
                                                                        @endif
                                                                    @endif

                                                                    @if($isAbsent)
                                                                        <flux:menu.item icon="calendar-days" wire:click="requestLeave('{{ $record['date'] }}')">
                                                                            {{ __('Request Leave') }}
                                                                        </flux:menu.item>
                                                                    @endif
                                                                </flux:menu>
                                                            </flux:dropdown>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <flux:icon name="calendar-days" class="mx-auto h-12 w-12 text-zinc-400" />
                            <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                                No attendance records found
                            </flux:heading>
                            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                                No attendance data found for {{ $selectedMonth ? \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') : $currentMonth }}.
                            </flux:text>
                        </div>
                    @endif
                </div>

            @else
                <!-- No Employee Record or Punch Code -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                    <div class="flex items-center">
                        <flux:icon name="exclamation-triangle" class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3" />
                        <div>
                            <flux:heading size="sm" class="text-yellow-800 dark:text-yellow-200">Employee Record Not Found</flux:heading>
                            <flux:text class="text-yellow-700 dark:text-yellow-300">
                                @if(!$employee)
                                    No employee record found for your user account. Please contact HR to set up your employee profile.
                                @elseif(!$punchCode)
                                    No punch code assigned to your employee record. Please contact HR to set up your attendance tracking.
                                @endif
                            </flux:text>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Missing Entry Flyout -->
        <flux:modal wire:model.self="showMissingEntryFlyout" variant="flyout" class="w-[32rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Add Missing Entry</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mt-1">
                        Add a missing check-in or check-out entry for {{ $missingEntryDate ? \Carbon\Carbon::parse($missingEntryDate)->format('F d, Y') : '' }}
                    </flux:text>
                </div>
                
                @if(session()->has('success'))
                    <flux:callout color="green" icon="check-circle">
                        {{ session('success') }}
                    </flux:callout>
                @endif

                @if(session()->has('error'))
                    <flux:callout color="red" icon="exclamation-circle">
                        {{ session('error') }}
                    </flux:callout>
                @endif

                <!-- Entry Type -->
                <flux:field>
                    <flux:label>Entry Type <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="missingEntryType" placeholder="Select Entry Type">
                        <option value="">Select Entry Type</option>
                        <option value="IN">Check-in</option>
                        <option value="OUT">Check-out</option>
                    </flux:select>
                    <flux:error name="missingEntryType" />
                </flux:field>

                <!-- Date -->
                <flux:field>
                    <flux:label>Date <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="missingEntryDate" type="date" />
                    <flux:error name="missingEntryDate" />
                    @if($dateAdjusted)
                        <flux:callout color="blue" icon="information-circle" class="mt-2">
                            Date automatically adjusted to next day because your shift starts in PM and you entered an AM time.
                        </flux:callout>
                    @endif
                </flux:field>

                <!-- Time -->
                <flux:field>
                    <flux:label>Time <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model.live="missingEntryTime" type="time" step="1" />
                    <flux:error name="missingEntryTime" />
                </flux:field>

                <!-- Notes -->
                <flux:field>
                    <flux:label>Notes</flux:label>
                    <flux:textarea wire:model="missingEntryNotes" rows="3" placeholder="Optional: Add any notes about this entry..."></flux:textarea>
                    <flux:error name="missingEntryNotes" />
                </flux:field>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:button wire:click="closeMissingEntryFlyout" variant="ghost">Cancel</flux:button>
                    <flux:button wire:click="saveMissingEntry" variant="primary">Add Entry</flux:button>
                </div>
            </div>
        </flux:modal>

        <!-- View Changes Flyout -->
        <flux:modal wire:model.self="showViewChangesFlyout" variant="flyout" class="w-[40rem] max-w-[50vw]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Manual Entry Changes</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mt-1">
                        Manual entries for {{ $viewChangesDate ? \Carbon\Carbon::parse($viewChangesDate)->format('F d, Y') : '' }}
                    </flux:text>
                </div>
                
                @if(empty($manualEntries))
                    <div class="text-center py-8">
                        <flux:icon name="information-circle" class="mx-auto h-12 w-12 text-zinc-400" />
                        <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                            No manual entries found for this date.
                        </flux:text>
                    </div>
                @else
                    <div class="space-y-4 max-h-[calc(100vh-12rem)] overflow-y-auto">
                        @foreach($manualEntries as $index => $entry)
                            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <flux:badge color="{{ $entry['type'] === 'IN' ? 'green' : 'red' }}" size="sm">
                                            {{ $entry['type_label'] }}
                                        </flux:badge>
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $entry['time'] }}
                                        </span>
                                    </div>
                                    <flux:button 
                                        variant="ghost" 
                                        size="sm" 
                                        icon="trash" 
                                        wire:click="deleteManualEntry({{ $entry['id'] }})"
                                        wire:confirm="Are you sure you want to delete this manual entry? This action cannot be undone."
                                        class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
                                    >
                                        Delete
                                    </flux:button>
                                </div>
                                
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-start gap-2">
                                        <span class="text-zinc-500 dark:text-zinc-400 min-w-[5rem]">Created:</span>
                                        <span class="text-zinc-900 dark:text-zinc-100">
                                            {{ $entry['created_at'] ?? $entry['date_time'] }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-start gap-2">
                                        <span class="text-zinc-500 dark:text-zinc-400 min-w-[5rem]">By:</span>
                                        <span class="text-zinc-900 dark:text-zinc-100">
                                            {{ $entry['updated_by'] }}
                                        </span>
                                    </div>
                                    
                                    @if(!empty($entry['notes']))
                                        <div class="flex items-start gap-2">
                                            <span class="text-zinc-500 dark:text-zinc-400 min-w-[5rem]">Notes:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 break-words" style="word-break: break-word; max-width: 100%;">
                                                @php
                                                    $words = explode(' ', $entry['notes']);
                                                    $wordCount = count($words);
                                                    $maxWordsPerLine = 15; // Approximately 15 words per line
                                                    $chunks = array_chunk($words, $maxWordsPerLine);
                                                @endphp
                                                @foreach($chunks as $chunk)
                                                    {{ implode(' ', $chunk) }}@if(!$loop->last)<br />@endif
                                                @endforeach
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:button wire:click="closeViewChangesFlyout" variant="primary">Close</flux:button>
                </div>
            </div>
        </flux:modal>

        <!-- Leave Request Modal -->
        <flux:modal wire:model.self="showLeaveRequestModal" variant="flyout" class="w-[48rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Add Leave Request</flux:heading>
                </div>
                
                <!-- First Row: Leave Type, Leave Duration, Leave Days -->
                <div class="grid grid-cols-3 gap-4">
                    <!-- Leave Type -->
                    <flux:field>
                        <flux:label>Leave Type <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="leaveType" placeholder="Select One">
                            <option value="">Select One</option>
                            <option value="sick">Sick Leave</option>
                            <option value="personal">Personal Leave</option>
                            <option value="vacation">Vacation Leave</option>
                            <option value="emergency">Emergency Leave</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="paternity">Paternity Leave</option>
                            <option value="bereavement">Bereavement Leave</option>
                        </flux:select>
                        <flux:error name="leaveType" />
                    </flux:field>

                    <!-- Leave Duration -->
                    <flux:field>
                        <flux:label>Leave Duration <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="leaveDuration" placeholder="Select Duration">
                            <option value="">Select Duration</option>
                            <option value="full_day">Full Day</option>
                            <option value="half_day_morning">Half Day (Morning)</option>
                            <option value="half_day_afternoon">Half Day (Afternoon)</option>
                        </flux:select>
                        <flux:error name="leaveDuration" />
                    </flux:field>
                </div>

                <!-- Second Row: Leave From, Leave To -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Leave From (Disabled) -->
                    <flux:field>
                        <flux:label>Leave From</flux:label>
                        <flux:input wire:model="leaveFrom" type="date" disabled />
                    </flux:field>

                    <!-- Leave To (Disabled) -->
                    <flux:field>
                        <flux:label>Leave To</flux:label>
                        <flux:input wire:model="leaveTo" type="date" disabled />
                    </flux:field>
                </div>

                <!-- Third Row: Reason -->
                <flux:field>
                    <flux:label>Reason <span class="text-red-500">*</span></flux:label>
                    <flux:textarea wire:model="reason" rows="4" placeholder="Enter reason for leave request..."></flux:textarea>
                    <flux:error name="reason" />
                </flux:field>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:button wire:click="closeLeaveRequestModal" variant="ghost">Cancel</flux:button>
                    <flux:button wire:click="submitLeaveRequest" variant="primary">Submit Request</flux:button>
                </div>
            </div>
        </flux:modal>
    </x-attendance.layout>
</section>