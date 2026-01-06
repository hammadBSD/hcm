<section class="w-full">
    @include('partials.attendance-heading')

    <x-attendance.layout :heading="__('My Attendance')" :subheading="__('Your attendance records for ' . ($selectedMonth ? \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') : $currentMonth))">
        <div class="space-y-6 w-full" style="max-width: 100%; overflow-x: hidden;">
            @if(session('success'))
                <flux:callout variant="success" icon="check-circle">
                    {{ session('success') }}
                </flux:callout>
            @endif

            @if(session('error'))
                <flux:callout variant="danger" icon="exclamation-circle">
                    {{ session('error') }}
                </flux:callout>
            @endif

            @if(!empty($employeesStats))
                <!-- Filters -->
                <div class="flex items-center justify-between gap-3 mb-4">
                    <!-- Search Bar (Left) -->
                    <div class="flex-1 max-w-md">
                        <flux:input
                            wire:model.live.debounce.300ms="employeeSearchTerm"
                            type="text"
                            placeholder="Search employees..."
                            class="w-full"
                        />
                    </div>

                    <!-- Dropdowns (Right) -->
                                    <div class="flex items-center gap-3">
                                            <flux:button 
                                                wire:click="exportToCsv" 
                                                variant="outline" 
                                                icon="arrow-down-tray"
                                                wire:loading.attr="disabled"
                                                wire:target="exportToCsv"
                                            >
                                                <span wire:loading.remove wire:target="exportToCsv">Export CSV</span>
                                                <span wire:loading wire:target="exportToCsv">Exporting...</span>
                                            </flux:button>
                                            <div
                                                class="text-zinc-400 dark:text-zinc-500 hidden md:flex items-center justify-center"
                                                wire:loading.flex
                            wire:target="selectedMonth"
                                            >
                                                <flux:icon name="arrow-path" class="w-5 h-5 animate-spin" />
                                            </div>
                                        <flux:select
                                            wire:model.live="selectedMonth"
                                            placeholder="{{ $currentMonth }}"
                                            class="w-40"
                                            wire:loading.attr="disabled"
                            wire:target="selectedMonth"
                                        >
                                            <option value="">{{ $currentMonth }} (Current)</option>
                                            @foreach($availableMonths as $month)
                                                <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                                            @endforeach
                                        </flux:select>
                                    </div>
                                </div>

                <!-- Attendance Statistics Table -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm" style="width: 100%; max-width: 100%; overflow: hidden; box-sizing: border-box;">
                    <div class="overflow-x-auto" style="width: 100%; max-width: 100%; display: block;">
                        <table class="divide-y divide-zinc-200 dark:divide-zinc-700" style="width: max-content;">
                                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                                        <tr>
                                            <th class="sticky left-0 z-10 bg-zinc-50 dark:bg-zinc-700 px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-r border-zinc-200 dark:border-zinc-600">
                                                {{ __('Employee') }}
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Working Days') }}
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Present Days') }}
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Leaves') }}
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Absent Days') }}
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Late Days') }}
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Total Break Time') }}
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Total Non-Allowed Break Time') }}
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Holidays') }}
                                                </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Total Hours Worked') }}
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Monthly Expected Hours') }}
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                {{ __('Short/Excess Hours') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @php
                                            $filteredEmployees = collect($employeesStats);
                                            if (!empty($employeeSearchTerm)) {
                                                $searchTerm = strtolower($employeeSearchTerm);
                                                $filteredEmployees = $filteredEmployees->filter(function($employeeData) use ($searchTerm) {
                                                    $emp = $employeeData['employee'];
                                                    $fullName = strtolower(trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')));
                                                    $employeeCode = strtolower($emp->employee_code ?? '');
                                                    return str_contains($fullName, $searchTerm) || str_contains($employeeCode, $searchTerm);
                                                });
                                            }
                                        @endphp
                                        @foreach($filteredEmployees as $employeeData)
                                            @php
                                                $emp = $employeeData['employee'];
                                                $stats = $employeeData['stats'];
                                            @endphp
                                            <tr class="group hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                                <td class="sticky left-0 z-10 bg-white dark:bg-zinc-800 group-hover:bg-zinc-100 dark:group-hover:bg-zinc-600 px-6 py-4 whitespace-nowrap border-r border-zinc-200 dark:border-zinc-700 transition-colors duration-150">
                                                    <div class="flex items-center gap-3">
                                                        <flux:avatar size="sm" :initials="strtoupper(substr($emp->first_name ?? '', 0, 1) . substr($emp->last_name ?? '', 0, 1))" />
                                                        <div>
                                                            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                                {{ trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) }}
                                                            </div>
                                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                                {{ $emp->employee_code ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $stats['working_days'] ?? 0 }}
                                                        </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-green-600 dark:text-green-400">
                                                        {{ $stats['attended_days'] ?? 0 }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-cyan-600 dark:text-cyan-400">
                                                        {{ $stats['on_leave_days'] ?? 0 }}
                                                        </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-red-600 dark:text-red-400">
                                                        {{ $stats['absent_days'] ?? 0 }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $stats['late_days'] ?? 0 }}
                                                                </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-orange-600 dark:text-orange-400">
                                                        {{ $stats['total_break_time'] ?? '0:00' }}
                                                                        </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-red-600 dark:text-red-400">
                                                        {{ $stats['total_non_allowed_break_time'] ?? '0:00' }}
                                                                                </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                                        {{ $stats['holiday_days'] ?? 0 }}
                                                            </div>
                                                    </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                                        {{ $stats['total_hours'] ?? '0:00' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-green-600 dark:text-green-400">
                                                        @if(($stats['on_leave_days'] ?? 0) > 0 || ($stats['holiday_days'] ?? 0) > 0 || ($stats['absent_days'] ?? 0) > 0)
                                                            {{ $stats['expected_hours_adjusted'] ?? '0:00' }}
                                                        @else
                                                            {{ $stats['expected_hours'] ?? '0:00' }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium {{ ($stats['short_excess_minutes'] ?? 0) < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                                        {{ $stats['short_excess_hours'] ?? '0:00' }}
                                            </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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
                    <flux:select wire:model.live="missingEntryType" placeholder="Select Entry Type">
                        <option value="">Select Entry Type</option>
                        <option value="IN">Check-in</option>
                        <option value="OUT">Check-out</option>
                        <option value="edit_checkin_checkout">Edit checkin and checkout</option>
                        <option value="edit_checkin_checkout_exclude_breaks">Edit checkin & checkout + exclude breaks</option>
                    </flux:select>
                    <flux:error name="missingEntryType" />
                </flux:field>

                @if(in_array($missingEntryType, ['IN', 'OUT']))
                    <!-- Single Entry Fields -->
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
                @elseif(in_array($missingEntryType, ['edit_checkin_checkout', 'edit_checkin_checkout_exclude_breaks']))
                    <!-- Date Range and Checkin/Checkout Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Date From <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="missingEntryDateFrom" type="date" />
                            <flux:error name="missingEntryDateFrom" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Date To <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="missingEntryDateTo" type="date" />
                            <flux:error name="missingEntryDateTo" />
                        </flux:field>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Checkin Time <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="missingEntryCheckinTime" type="time" step="1" />
                            <flux:error name="missingEntryCheckinTime" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Checkout Time <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="missingEntryCheckoutTime" type="time" step="1" />
                            <flux:error name="missingEntryCheckoutTime" />
                        </flux:field>
                    </div>
                @endif

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
            <form class="flex flex-col h-full" wire:submit.prevent="submitLeaveRequest">
                <div class="px-6 pt-6 pb-4 border-b border-zinc-200 dark:border-zinc-700">
                    <flux:heading size="lg">Add Leave Request</flux:heading>
                    <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                        {{ __('Request leave for the selected date.') }}
                    </flux:text>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                    <!-- Leave Balance Card -->
                    <div class="space-y-4">
                        @forelse($leaveBalances as $balance)
                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                                <div class="space-y-4">
                                    <!-- First Row: Title -->
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="calendar-days" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                            {{ $balance['leave_type_name'] }}@if(!empty($balance['leave_type_code'])) ({{ $balance['leave_type_code'] }}) @endif - Leave Balance <span class="text-zinc-500 dark:text-zinc-400 font-normal">(Current Leave Quota Year)</span>
                                        </span>
                                    </div>
                                    
                                    <!-- Second Row: Metrics -->
                                    <div class="flex items-center gap-6 text-sm">
                                        <div class="text-center">
                                            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Entitled') }}</div>
                                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($balance['entitled'] ?? 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Taken') }}</div>
                                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($balance['used'] ?? 0, 1) }}
                                            </div>
                                        </div>
                                        @php
                                            $pendingValue = $balance['pending'] ?? 0;
                                            $pendingTextClasses = $pendingValue > 0
                                                ? 'text-amber-600 dark:text-amber-300'
                                                : 'text-zinc-900 dark:text-zinc-100';
                                        @endphp
                                        <div class="text-center">
                                            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Pending') }}</div>
                                            <div class="font-semibold {{ $pendingTextClasses }}">
                                                {{ number_format($pendingValue, 1) }}
                                            </div>
                                        </div>
                                        @php
                                            $balanceValue = $balance['balance'] ?? 0;
                                        @endphp
                                        <div class="text-center">
                                            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Balance') }}</div>
                                            <div class="font-bold {{ $balanceValue >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ number_format($balanceValue, 1) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                                <div class="text-center text-zinc-500 dark:text-zinc-400">
                                    {{ __('No leave balances found') }}
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if($leaveBalanceDepleted)
                        <flux:callout variant="warning" icon="exclamation-triangle">
                            {{ __('You do not have sufficient leave balance to apply for leave. Please contact HR for assistance.') }}
                        </flux:callout>
                    @endif

                    <div class="space-y-4">
                        <!-- Leave Type -->
                        <flux:field>
                            <flux:label>{{ __('Leave Type') }} <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="leaveType" placeholder="{{ __('Select Leave Type') }}" :disabled="$leaveBalanceDepleted">
                                <option value="">{{ __('Select Leave Type') }}</option>
                                @foreach($leaveTypeOptions as $option)
                                    <option value="{{ $option['id'] }}">
                                        {{ $option['name'] }}@if(!empty($option['code'])) ({{ $option['code'] }}) @endif
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:error name="leaveType" />
                        </flux:field>

                        <!-- Leave Duration and Leave Days -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>{{ __('Leave Duration') }} <span class="text-red-500">*</span></flux:label>
                                <flux:select wire:model.live="leaveDuration" placeholder="{{ __('Select Duration') }}" :disabled="$leaveBalanceDepleted">
                                    <option value="">{{ __('Select Duration') }}</option>
                                    <option value="full_day">{{ __('Full Day') }}</option>
                                    <option value="half_day_morning">{{ __('Half Day (Morning)') }}</option>
                                    <option value="half_day_afternoon">{{ __('Half Day (Afternoon)') }}</option>
                                </flux:select>
                                <flux:error name="leaveDuration" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Leave Days') }}</flux:label>
                                <flux:input 
                                    wire:model="leaveDays" 
                                    type="text" 
                                    placeholder="1.00" 
                                    readonly
                                    disabled
                                    class="bg-zinc-50 dark:bg-zinc-700/50 cursor-not-allowed"
                                />
                            </flux:field>
                        </div>

                        <!-- Leave From and To -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>{{ __('Leave From') }}</flux:label>
                                <flux:input wire:model="leaveFrom" type="date" readonly disabled class="bg-zinc-50 dark:bg-zinc-700/50 cursor-not-allowed" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Leave To') }}</flux:label>
                                <flux:input wire:model="leaveTo" type="date" readonly disabled class="bg-zinc-50 dark:bg-zinc-700/50 cursor-not-allowed" />
                            </flux:field>
                        </div>

                        <!-- Reason -->
                        <flux:field>
                            <flux:label>{{ __('Reason') }}</flux:label>
                            <flux:textarea
                                wire:model="reason"
                                rows="4"
                                class="dark:bg-transparent!"
                                placeholder="{{ __('Please provide a detailed reason for the leave request... (Optional)') }}"
                                :disabled="$leaveBalanceDepleted"
                            ></flux:textarea>
                            <flux:error name="reason" />
                        </flux:field>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
                    <flux:button type="button" variant="outline" wire:click="closeLeaveRequestModal" wire:loading.attr="disabled" kbd="esc">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" wire:loading.attr="disabled" :disabled="$leaveBalanceDepleted" icon="plus">
                        <span wire:loading.remove wire:target="submitLeaveRequest">{{ __('Submit Request') }}</span>
                        <span wire:loading wire:target="submitLeaveRequest">{{ __('Submitting...') }}</span>
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    </x-attendance.layout>
</section>