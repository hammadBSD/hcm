<section class="w-full">
    @include('partials.tasks-heading')
    <x-tasks.layout :heading="__('Daily Task Log')" :subheading="__('Log your daily tasks')">
        <div class="space-y-6">
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

        @if(!$settings->enabled)
            <flux:callout variant="warning" icon="exclamation-triangle">
                {{ __('Daily task logging is currently disabled.') }}
            </flux:callout>
        @endif

        @if($settings->enabled)
            <!-- Message for employees who haven't logged today -->
            @if(!$isAdminView && !$hasLogToday)
                @php
                    $selectedDateCarbon = \Carbon\Carbon::parse($selectedDateFilter ?? \Carbon\Carbon::today()->format('Y-m-d'));
                    $isToday = $selectedDateCarbon->isToday();
                @endphp
                @if($isToday)
                    <div class="mb-4">
                        <flux:callout variant="info" icon="information-circle">
                            {{ __('You have not logged your work for today. Don\'t forget to log your work before your shift ends.') }}
                        </flux:callout>
                    </div>
                @endif
            @endif
            
            <!-- Daily Logs Table -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                            {{ $isAdminView ? __('All Employees Daily Logs') : __('Daily Logs History') }}
                        </flux:heading>
                    </div>
                    
                    <div class="flex items-center justify-between gap-3">
                        <flux:field class="w-64">
                            <flux:input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="{{ __('Search by employee name, code, department, or group...') }}"
                            />
                        </flux:field>
                        <div class="flex items-center gap-3">
                            <flux:field class="w-48">
                                <flux:input
                                    type="date"
                                    wire:model.live="selectedDateFilter"
                                />
                            </flux:field>
                            @if($canCreateSelf || $canCreateAll)
                                <flux:button wire:click="openCreateLogFlyout()" variant="primary">
                                    {{ __('Create Log') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                @if($isAdminView)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Employee') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Department') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Group') }}
                                    </th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Date') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Status') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Created At') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($dailyLogs as $log)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 {{ !$log['has_log'] ? 'opacity-60' : '' }}">
                                    @if($isAdminView)
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-900 dark:text-zinc-100">
                                            <div>
                                                <div class="font-medium">{{ $log['employee_name'] }}</div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $log['employee_code'] }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                            {{ $log['department'] ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                            {{ $log['group'] ?? 'N/A' }}
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-zinc-900 dark:text-zinc-100">
                                        {{ $log['formatted_date'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(!$log['has_log'])
                                            <flux:badge color="gray" size="sm">
                                                <flux:icon name="x-circle" class="w-3 h-3 mr-1" />
                                                {{ __('Not Logged') }}
                                            </flux:badge>
                                        @elseif($log['is_locked'])
                                            <flux:badge color="amber" size="sm">
                                                <flux:icon name="lock-closed" class="w-3 h-3 mr-1" />
                                                {{ __('Locked') }}
                                            </flux:badge>
                                        @else
                                            <flux:badge color="green" size="sm">
                                                <flux:icon name="check-circle" class="w-3 h-3 mr-1" />
                                                {{ __('Logged') }}
                                            </flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                        {{ $log['created_at'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $user = Auth::user();
                                            // Only show actions if there's an actual log
                                            if (!$log['has_log'] || !$log['id']) {
                                                $canView = false;
                                                $canEdit = false;
                                                $canDelete = false;
                                            } else {
                                                $canView = $canViewLog || ($user && $user->employee && $log['employee_id'] == $user->employee->id && ($user->hasRole('Super Admin') || $user->can('daily-logs.create.self') || $user->can('daily-logs.create.all')));
                                                $canEdit = $canEditLog && !$log['is_locked'];
                                                $canDelete = $canDeleteLog;
                                            }
                                            $hasAnyAction = $canView || $canEdit || $canDelete;
                                        @endphp
                                        
                                        @if($hasAnyAction)
                                            @if($canEdit || $canDelete)
                                                <div class="flex items-center gap-1">
                                                    <flux:dropdown>
                                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                        <flux:menu>
                                                            @if($canView)
                                                                <flux:menu.item icon="eye" wire:click="openViewFlyout({{ $log['id'] }})">
                                                                    {{ __('View') }}
                                                                </flux:menu.item>
                                                            @endif
                                                            @if($canEdit)
                                                                <flux:menu.item icon="pencil" wire:click="openEditFlyout({{ $log['id'] }})">
                                                                    {{ __('Edit') }}
                                                                </flux:menu.item>
                                                            @endif
                                                            @if($canDelete)
                                                                <flux:menu.separator />
                                                                <flux:menu.item icon="trash" wire:click="confirmDelete({{ $log['id'] }})" class="text-red-600 dark:text-red-400">
                                                                    {{ __('Delete') }}
                                                                </flux:menu.item>
                                                            @endif
                                                        </flux:menu>
                                                    </flux:dropdown>
                                                </div>
                                            @elseif($canView)
                                                <flux:button wire:click="openViewFlyout({{ $log['id'] }})" variant="ghost" size="sm">
                                                    <flux:icon name="eye" class="w-4 h-4" />
                                                </flux:button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdminView ? (Auth::user()->hasRole('Super Admin') || Auth::user()->can('daily-logs.delete') ? '8' : '7') : '4' }}" class="px-6 py-12 text-center">
                                        <div class="text-zinc-500 dark:text-zinc-400">
                                            <flux:icon name="clipboard-document-list" class="w-12 h-12 mx-auto mb-4 text-zinc-400" />
                                            <p>{{ __('No daily logs found.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if(isset($totalLogs) && $totalLogs > $perPage)
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('Showing') }} {{ (($currentPage - 1) * $perPage) + 1 }} {{ __('to') }} {{ min($currentPage * $perPage, $totalLogs) }} {{ __('of') }} {{ $totalLogs }} {{ __('results') }}
                            </div>
                            <div class="flex items-center gap-2">
                                @if($currentPage > 1)
                                    <flux:button wire:click="previousPage" variant="ghost" size="sm">
                                        {{ __('Previous') }}
                                    </flux:button>
                                @endif
                                
                                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                    <flux:button 
                                        wire:click="gotoPage({{ $i }})" 
                                        variant="{{ $i === $currentPage ? 'primary' : 'ghost' }}" 
                                        size="sm"
                                    >
                                        {{ $i }}
                                    </flux:button>
                                @endfor
                                
                                @if($currentPage < $totalPages)
                                    <flux:button wire:click="nextPage" variant="ghost" size="sm">
                                        {{ __('Next') }}
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if(!$isAdminView && $template)
                <!-- Header -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                            {{ __('Daily Task Log') }}
                        </flux:heading>
                        <flux:subheading class="text-zinc-600 dark:text-zinc-400">
                            {{ __('Template:') }} {{ $template->name }}
                        </flux:subheading>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Date') }}</flux:label>
                        <flux:input
                            type="date"
                            wire:model.live="selectedDate"
                        />
                    </flux:field>

                    @if($settings->split_periods)
                        <flux:field>
                            <flux:label>{{ __('Period') }}</flux:label>
                            <flux:select wire:model.live="selectedPeriod">
                                <option value="full_day">{{ __('Full Day') }}</option>
                                <option value="first_half">{{ __('First Half') }}</option>
                                <option value="second_half">{{ __('Second Half') }}</option>
                            </flux:select>
                        </flux:field>
                    @endif
                </div>

                @if($isLocked)
                    <div class="mt-4">
                        <flux:callout variant="warning" icon="lock-closed">
                            {{ __('This task log is locked and cannot be edited.') }}
                            @if($existingLog && $existingLog->locked_at)
                                {{ __('Locked at:') }} {{ $existingLog->locked_at->format('M d, Y h:i A') }}
                            @endif
                        </flux:callout>
                    </div>
                @elseif(!$canEdit)
                    @php
                        $isWorkingDay = $this->isWorkingDay($selectedDate);
                        $isPresent = $this->isEmployeePresent($selectedDate);
                    @endphp
                    <div class="mt-4">
                        @if(!$isWorkingDay)
                            <flux:callout variant="info" icon="information-circle">
                                {{ __('Daily logs can only be created on working days.') }}
                            </flux:callout>
                        @elseif(!$isPresent)
                            <flux:callout variant="warning" icon="exclamation-triangle">
                                {{ __('You must be present (checked in) to create a daily log for this date.') }}
                            </flux:callout>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Task Form -->
            <form wire:submit.prevent="save" class="space-y-6">
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="space-y-6">
                        @if($template && count($template->fields) > 0)
                            @foreach($template->fields as $field)
                                @php
                                    $fieldName = 'formData.' . $field['name'];
                                    $fieldValue = $formData[$field['name']] ?? '';
                                    $isRequired = $field['required'] ?? false;
                                    $requiredLabel = $isRequired ? ' <span class="text-red-500">*</span>' : '';
                                    $extraAttrs = [];
                                    if ($isRequired) {
                                        $extraAttrs[] = 'required';
                                    }
                                    if (!$canEdit) {
                                        $extraAttrs[] = 'disabled';
                                    }
                                    $extraAttrsStr = !empty($extraAttrs) ? ' ' . implode(' ', $extraAttrs) : '';
                                @endphp

<div>
                                    @switch($field['type'])
                                        @case('text')
                                            <flux:input
                                                label="{{ $field['label'] }}{!! $requiredLabel !!}"
                                                wire:model.defer="{{ $fieldName }}"
                                                placeholder="{{ __('Enter') }} {{ strtolower($field['label']) }}"{!! $extraAttrsStr !!}
                                            />
                                            <flux:error name="{{ $fieldName }}" />
                                            @break

                                        @case('number')
                                            <flux:input
                                                type="number"
                                                label="{{ $field['label'] }}{!! $requiredLabel !!}"
                                                wire:model.defer="{{ $fieldName }}"
                                                placeholder="{{ __('Enter') }} {{ strtolower($field['label']) }}"{!! $extraAttrsStr !!}
                                            />
                                            <flux:error name="{{ $fieldName }}" />
                                            @break

                                        @case('textarea')
                                            <flux:textarea
                                                label="{{ $field['label'] }}{!! $requiredLabel !!}"
                                                wire:model.defer="{{ $fieldName }}"
                                                placeholder="{{ __('Enter') }} {{ strtolower($field['label']) }}"
                                                rows="4"{!! $extraAttrsStr !!}
                                            />
                                            <flux:error name="{{ $fieldName }}" />
                                            @break

                                        @case('date')
                                            <flux:input
                                                type="date"
                                                label="{{ $field['label'] }}{!! $requiredLabel !!}"
                                                wire:model.defer="{{ $fieldName }}"{!! $extraAttrsStr !!}
                                            />
                                            <flux:error name="{{ $fieldName }}" />
                                            @break

                                        @case('time')
                                            <flux:input
                                                type="time"
                                                label="{{ $field['label'] }}{!! $requiredLabel !!}"
                                                wire:model.defer="{{ $fieldName }}"{!! $extraAttrsStr !!}
                                            />
                                            <flux:error name="{{ $fieldName }}" />
                                            @break

                                        @case('checkbox')
                                            <flux:checkbox
                                                label="{{ $field['label'] }}{!! $requiredLabel !!}"
                                                wire:model.defer="{{ $fieldName }}"{!! $extraAttrsStr !!}
                                            />
                                            <flux:error name="{{ $fieldName }}" />
                                            @break
                                    @endswitch
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-8">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('No fields defined in this template.') }}
                                </p>
                            </div>
                        @endif
</div>

                    @if($canEdit)
                        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary">
                                {{ $existingLog ? __('Update Task Log') : __('Save Task Log') }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            </form>
            @endif
        @endif
        
        @if(($canCreateSelf || $canCreateAll) && $showCreateLogFlyout)
            <!-- Create Log Flyout -->
            <flux:modal variant="flyout" wire:model="showCreateLogFlyout" name="create-log">
                <form wire:submit.prevent="saveCreateLog">
                    <flux:heading size="lg" class="mb-4">
                        {{ __('Create Daily Log') }}
                    </flux:heading>
                    
                    @if($createLogError)
                        <flux:callout variant="danger" icon="exclamation-circle" class="mb-4">
                            {{ $createLogError }}
                        </flux:callout>
                    @endif
                    
                    <div class="space-y-4">
                        @if($canCreateAll)
                            <flux:field>
                                <flux:label>{{ __('Employee') }}</flux:label>
                                <flux:select wire:model="createLogForm.employee_id" required>
                                    <option value="">{{ __('Select Employee') }}</option>
                                    @foreach(\App\Models\Employee::where('status', 'active')->with(['department', 'group'])->orderBy('first_name')->orderBy('last_name')->get() as $emp)
                                        <option value="{{ $emp->id }}">
                                            {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_code }})
                                        </option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="createLogForm.employee_id" />
                            </flux:field>
                        @else
                            <flux:field>
                                <flux:label>{{ __('Employee') }}</flux:label>
                                <flux:select wire:model="createLogForm.employee_id" disabled>
                                    <option value="{{ Auth::user()->employee->id ?? '' }}">
                                        {{ Auth::user()->employee->first_name ?? '' }} {{ Auth::user()->employee->last_name ?? '' }} ({{ Auth::user()->employee->employee_code ?? '' }})
                                    </option>
                                </flux:select>
                            </flux:field>
                        @endif
                        
                        @if($canCreateAll)
                            <flux:field>
                                <flux:label>{{ __('Date') }}</flux:label>
                                <flux:input
                                    type="date"
                                    wire:model="createLogForm.date"
                                    required
                                />
                                <flux:error name="createLogForm.date" />
                            </flux:field>
                        @endif
                        
                        <flux:field>
                            <flux:label>{{ __('Notes') }}</flux:label>
                            <flux:textarea
                                wire:model="createLogForm.notes"
                                rows="6"
                                placeholder="{{ __('Enter daily log notes...') }}"
                                required
                            />
                            <flux:error name="createLogForm.notes" />
                        </flux:field>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button type="button" wire:click="closeCreateLogFlyout" variant="ghost">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            {{ __('Create Log') }}
                        </flux:button>
                    </div>
                </form>
            </flux:modal>
        @endif
        
        @if($showEditFlyout && $editLogData)
            <!-- Edit Log Flyout -->
            <flux:modal wire:model="showEditFlyout" variant="flyout" name="edit-log" class="max-w-2xl">
                <flux:heading size="lg" class="mb-4">
                    {{ __('Edit Daily Log') }}
                </flux:heading>
                
                <form wire:submit="updateLog">
                    <div class="space-y-6">
                        <!-- Employee Information (Read-only) -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-1">
                                    {{ __('Employee') }}
                                </flux:subheading>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $editLogData['employee_name'] }}</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $editLogData['employee_code'] }}</p>
                            </div>
                            <div>
                                <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-1">
                                    {{ __('Date') }}
                                </flux:subheading>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $editLogData['formatted_date'] }}</p>
                            </div>
                        </div>
                        
                        <!-- Log Entries -->
                        <div>
                            <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-3">
                                {{ __('Log Entries') }} ({{ count($editLogData['entries'] ?? []) }})
                            </flux:subheading>
                            @if(!empty($editLogData['entries']) && is_array($editLogData['entries']))
                                <div class="space-y-4">
                                    @foreach($editLogData['entries'] as $index => $entry)
                                        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1">
                                                        {{ __('Entry') }} #{{ $index + 1 }}
                                                    </p>
                                                    @if(isset($entry['created_at']))
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                            {{ \Carbon\Carbon::parse($entry['created_at'])->format('M d, Y h:i A') }}
                                                        </p>
                                                    @endif
                                                </div>
                                                @if(isset($entry['created_by_name']))
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        {{ __('By') }}: {{ $entry['created_by_name'] }}
                                                    </p>
                                                @endif
                                            </div>
                                            <flux:field>
                                                <flux:label>{{ __('Notes') }}</flux:label>
                                                <flux:textarea
                                                    wire:model="editLogData.entries.{{ $index }}.notes"
                                                    rows="4"
                                                    placeholder="{{ __('Enter log entry notes...') }}"
                                                    required
                                                />
                                                <flux:error name="editLogData.entries.{{ $index }}.notes" />
                                            </flux:field>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                    <p class="text-zinc-900 dark:text-zinc-100">{{ __('No log entries found.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button type="button" wire:click="closeEditFlyout" variant="ghost">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            {{ __('Update Log') }}
                        </flux:button>
                    </div>
                </form>
            </flux:modal>
        @endif
        
        @if($showViewFlyout && $viewLogData)
            <!-- View Log Flyout -->
            <flux:modal wire:model="showViewFlyout" variant="flyout" name="view-log" class="max-w-2xl">
                <flux:heading size="lg" class="mb-4">
                    {{ __('Daily Log Details') }}
                </flux:heading>
                
                <div class="space-y-6">
                    <!-- Employee Information -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-1">
                                {{ __('Employee') }}
                            </flux:subheading>
                            <p class="text-zinc-900 dark:text-zinc-100">{{ $viewLogData['employee_name'] }}</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $viewLogData['employee_code'] }}</p>
                        </div>
                        <div>
                            <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-1">
                                {{ __('Date') }}
                            </flux:subheading>
                            <p class="text-zinc-900 dark:text-zinc-100">{{ $viewLogData['formatted_date'] }}</p>
                        </div>
                    </div>
                    
                    @if($isAdminView)
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-1">
                                    {{ __('Department') }}
                                </flux:subheading>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $viewLogData['department'] }}</p>
                            </div>
                            <div>
                                <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-1">
                                    {{ __('Group') }}
                                </flux:subheading>
                                <p class="text-zinc-900 dark:text-zinc-100">{{ $viewLogData['group'] }}</p>
                            </div>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-1">
                                {{ __('Period') }}
                            </flux:subheading>
                            <p class="text-zinc-900 dark:text-zinc-100">{{ $viewLogData['period_label'] }}</p>
                        </div>
                        <div>
                            <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-1">
                                {{ __('Status') }}
                            </flux:subheading>
                            @if($viewLogData['is_locked'])
                                <flux:badge color="amber" size="sm">
                                    <flux:icon name="lock-closed" class="w-3 h-3 mr-1" />
                                    {{ __('Locked') }}
                                </flux:badge>
                            @else
                                <flux:badge color="green" size="sm">
                                    <flux:icon name="check-circle" class="w-3 h-3 mr-1" />
                                    {{ __('Logged') }}
                                </flux:badge>
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-1">
                            {{ __('Created At') }}
                        </flux:subheading>
                        <p class="text-zinc-900 dark:text-zinc-100">{{ $viewLogData['created_at'] }}</p>
                        @if($viewLogData['created_by'])
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Created by') }}: {{ $viewLogData['created_by'] }}</p>
                        @endif
                    </div>
                    
                    <!-- Log Entries -->
                    <div>
                        <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-3">
                            {{ __('Log Entries') }} ({{ count($viewLogData['entries'] ?? []) }})
                        </flux:subheading>
                        @if(!empty($viewLogData['entries']) && is_array($viewLogData['entries']))
                            <div class="space-y-4">
                                @foreach($viewLogData['entries'] as $index => $entry)
                                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                        <div class="flex items-start justify-between mb-2">
                                            <div>
                                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ __('Entry') }} #{{ $index + 1 }}
                                                </p>
                                                @if(isset($entry['created_at']))
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        {{ \Carbon\Carbon::parse($entry['created_at'])->format('M d, Y h:i A') }}
                                                    </p>
                                                @endif
                                            </div>
                                            @if(isset($entry['created_by_name']))
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ __('By') }}: {{ $entry['created_by_name'] }}
                                                </p>
                                            @endif
                                        </div>
                                        <p class="text-zinc-900 dark:text-zinc-100 whitespace-pre-wrap">{{ $entry['notes'] ?? __('No notes provided.') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                <p class="text-zinc-900 dark:text-zinc-100">{{ __('No log entries found.') }}</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Custom Fields Data -->
                    @if(!empty($viewLogData['data']) && is_array($viewLogData['data']))
                        @foreach($viewLogData['data'] as $key => $value)
                            @if($key !== 'notes' && $key !== 'entries' && !empty($value))
                                <div>
                                    <flux:subheading class="text-zinc-600 dark:text-zinc-400 mb-1">
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}
                                    </flux:subheading>
                                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                        <p class="text-zinc-900 dark:text-zinc-100">
                                            @if(is_array($value))
                                                {{ json_encode($value, JSON_PRETTY_PRINT) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
                
                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button wire:click="closeViewFlyout" variant="ghost">
                        {{ __('Close') }}
                    </flux:button>
                </div>
            </flux:modal>
        @endif
        
        @if($showDeleteModal)
            <!-- Delete Confirmation Modal -->
            <flux:modal wire:model="showDeleteModal" name="delete-log">
                <flux:heading size="lg" class="mb-4">
                    {{ __('Delete Daily Log') }}
                </flux:heading>
                
                <flux:callout variant="warning" icon="exclamation-triangle" class="mb-4">
                    {{ __('Are you sure you want to delete this daily log? This action cannot be undone.') }}
                </flux:callout>
                
                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button type="button" wire:click="closeDeleteModal" variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="button" wire:click="deleteLog" variant="danger">
                        {{ __('Delete') }}
                    </flux:button>
                </div>
            </flux:modal>
        @endif
    </div>
    </x-tasks.layout>
</section>
