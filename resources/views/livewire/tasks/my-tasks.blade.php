<section class="w-full">
    @include('partials.tasks-heading')
    <x-tasks.layout :heading="__('Tasks')" :subheading="__('View and manage all tasks')">
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

        <!-- Header with Search, Filters, and Create Button -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Search') }}</flux:label>
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('Search tasks...') }}"
                            icon="magnifying-glass"
                        />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Status') }}</flux:label>
                        <flux:select wire:model.live="statusFilter">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="completed">{{ __('Completed') }}</option>
                            <option value="rejected">{{ __('Rejected') }}</option>
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Frequency') }}</flux:label>
                        <flux:select wire:model.live="frequencyFilter">
                            <option value="">{{ __('All Frequencies') }}</option>
                            <option value="daily">{{ __('Daily') }}</option>
                            <option value="weekly">{{ __('Weekly') }}</option>
                        </flux:select>
                    </flux:field>
                </div>
                @canany(['tasks.create', 'tasks.assign'])
                    <div class="flex-shrink-0">
                        <flux:button icon="plus" wire:click="openCreateFlyout">
                            {{ __('Create Task') }}
                        </flux:button>
                    </div>
                @endcanany
            </div>
        </div>

        <!-- Tasks Table -->
        @if($tasks->count() > 0)
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('description')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Task') }}
                                        @if($sortBy === 'description')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Assigned To') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button wire:click="sort('due_date')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Due Date') }}
                                        @if($sortBy === 'due_date')
                                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Frequency') }}
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
                                    <button wire:click="sort('created_at')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        {{ __('Created') }}
                                        @if($sortBy === 'created_at')
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
                            @foreach($tasks as $task)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $task->name ?: $task->title }}
                                        </div>
                                        @if($task->description)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1 mt-1">
                                                {{ $task->description }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $task->assignedTo->first_name }} {{ $task->assignedTo->last_name }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $task->assignedTo->employee_code }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        @if($task->due_date)
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $task->due_date->format('M d, Y') }}
                                            </div>
                                            @if($task->isOverdue())
                                                <div class="text-xs text-red-600 dark:text-red-400">
                                                    {{ __('Overdue') }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No due date') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge color="blue" size="sm">
                                            {{ ucfirst($task->frequency) }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        @php
                                            $statusColor = match($task->status) {
                                                'pending' => 'yellow',
                                                'completed' => 'green',
                                                'rejected' => 'red',
                                                default => 'zinc'
                                            };
                                        @endphp
                                        <flux:badge color="{{ $statusColor }}" size="sm">
                                            {{ ucfirst($task->status) }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $task->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-1">
                                            <flux:button
                                                variant="ghost"
                                                size="sm"
                                                icon="eye"
                                                wire:click="viewTask({{ $task->id }})"
                                            >
                                                {{ __('View') }}
                                            </flux:button>
                                            @if($canEditTasks)
                                                <flux:button
                                                    variant="ghost"
                                                    size="sm"
                                                    icon="pencil"
                                                    wire:click="openEditFlyout({{ $task->id }})"
                                                    class="text-blue-600 hover:text-blue-700"
                                                >
                                                </flux:button>
                                            @endif
                                            @if($task->status === 'pending')
                                                @php
                                                    $shiftEnded = $isEmployeeRole ? $this->hasShiftEnded($task) : false;
                                                @endphp
                                                @if(!$shiftEnded)
                                                    <flux:button
                                                        variant="ghost"
                                                        size="sm"
                                                        icon="check-circle"
                                                        wire:click="openActionModal({{ $task->id }}, 'complete')"
                                                        class="text-green-600 hover:text-green-700"
                                                    >
                                                    </flux:button>
                                                    <flux:button
                                                        variant="ghost"
                                                        size="sm"
                                                        icon="x-circle"
                                                        wire:click="openActionModal({{ $task->id }}, 'reject')"
                                                        class="text-red-600 hover:text-red-700"
                                                    >
                                                    </flux:button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($tasks->hasPages())
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                        {{ $tasks->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                <flux:icon name="clipboard-document-list" class="w-16 h-16 mx-auto mb-4 text-zinc-400" />
                <p class="text-zinc-500 dark:text-zinc-400">{{ __('No tasks found') }}</p>
            </div>
        @endif
    </div>

    <!-- Create Task Flyout -->
    <flux:modal variant="flyout" wire:model="showCreateFlyout" title="{{ __('Assign New Task') }}" class="w-[40rem]">
        <form wire:submit="saveTask" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('Task Name') }} <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="form.name" placeholder="{{ __('Enter task name...') }}" required />
                <flux:error name="form.name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Task Description') }} <span class="text-red-500">*</span></flux:label>
                <flux:textarea wire:model="form.description" rows="6" placeholder="{{ __('Enter task description...') }}" required />
                <flux:error name="form.description" />
            </flux:field>

            <!-- Custom Fields -->
            @if(!empty($form['custom_fields']))
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:subheading>{{ __('Custom Fields') }}</flux:subheading>
                    </div>
                    @foreach($form['custom_fields'] as $index => $field)
                        <div class="flex items-start gap-3 p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <div class="flex-1 space-y-2">
                                <flux:field>
                                    <flux:label>{{ __('Field Name') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:input wire:model="form.custom_fields.{{ $index }}.name" placeholder="{{ __('Enter field name...') }}" required />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('Field Type') }}</flux:label>
                                    <flux:select wire:model="form.custom_fields.{{ $index }}.type">
                                        <option value="text">{{ __('Text') }}</option>
                                        <option value="number">{{ __('Number') }}</option>
                                        <option value="textarea">{{ __('Textarea') }}</option>
                                        <option value="date">{{ __('Date') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>
                            <button
                                type="button"
                                wire:click="removeCustomField({{ $index }})"
                                class="mt-6 p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                title="{{ __('Remove Field') }}"
                            >
                                <flux:icon name="trash" class="w-5 h-5" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Add Field Button -->
            <div>
                <flux:button type="button" variant="outline" wire:click="addCustomField" icon="plus">
                    {{ __('Add Field') }}
                </flux:button>
            </div>

            <!-- Attachments -->
            <flux:field>
                <flux:label>{{ __('Attachments') }}</flux:label>
                <flux:input 
                    type="file" 
                    wire:model="attachments" 
                    multiple
                    accept="*/*"
                />
                <flux:description>{{ __('You can attach multiple files. Maximum file size: 20MB per file.') }}</flux:description>
                <flux:error name="attachments.*" />
                
                @if(count($attachments) > 0)
                    <div class="mt-3 space-y-2">
                        @foreach($attachments as $index => $file)
                            @if($file)
                                <div class="flex items-center justify-between p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <flux:icon name="paper-clip" class="w-4 h-4 text-zinc-500 dark:text-zinc-400 flex-shrink-0" />
                                        <span class="text-sm text-zinc-900 dark:text-zinc-100 truncate">{{ $file->getClientOriginalName() }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ number_format($file->getSize() / 1024, 2) }} KB)</span>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="removeAttachment({{ $index }})"
                                        class="ml-2 p-1 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                        title="{{ __('Remove') }}"
                                    >
                                        <flux:icon name="x-mark" class="w-4 h-4" />
                                    </button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Assign To') }} <span class="text-red-500">*</span></flux:label>
                
                <!-- Search Input for Employees -->
                <div class="mb-3">
                    <flux:input 
                        wire:model.live.debounce.300ms="employeeSearchTerm"
                        placeholder="Search employees..."
                        icon="magnifying-glass"
                    />
                </div>
                
                <div class="relative">
                    <select 
                        wire:model="form.assigned_to" 
                        multiple 
                        required
                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                        size="6"
                        style="min-height: 150px;"
                    >
                        @foreach($this->filteredEmployeeOptions as $option)
                            <option value="{{ $option['value'] }}" class="py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <flux:description>{{ __('Search and select employees. Hold Ctrl/Cmd to select multiple employees.') }}</flux:description>
                <flux:error name="form.assigned_to" />
            </flux:field>

            <!-- Selected Employees Display -->
            @if(count($form['assigned_to']) > 0)
                <div class="mt-4">
                    <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3 flex items-center gap-2">
                        <flux:icon name="check-circle" class="w-4 h-4 text-green-500" />
                        {{ __('Selected Employees') }} ({{ count($form['assigned_to']) }})
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($form['assigned_to'] as $employeeId)
                            @php
                                $selectedEmployee = collect($this->employeeOptions)->firstWhere('value', $employeeId);
                            @endphp
                            @if($selectedEmployee)
                                <span class="inline-flex items-center gap-2 px-3 py-2 bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 rounded-lg text-sm border border-blue-200 dark:border-blue-700 shadow-sm">
                                    <flux:icon name="user" class="w-3 h-3 text-blue-700 dark:text-blue-200" />
                                    {{ $selectedEmployee['name'] }}
                                    <button 
                                        type="button" 
                                        wire:click="removeEmployee({{ $employeeId }})" 
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

            <flux:field>
                <flux:label>{{ __('Due Date') }}</flux:label>
                <flux:input type="date" wire:model="form.due_date" />
                <flux:error name="form.due_date" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Frequency') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model.live="form.frequency" required>
                    <option value="one-time">{{ __('One-time') }}</option>
                    <option value="daily">{{ __('Daily') }}</option>
                    <option value="weekly">{{ __('Weekly') }}</option>
                </flux:select>
                <flux:error name="form.frequency" />
                <flux:description>
                    @if($form['frequency'] === 'daily')
                        {{ __('This task will be automatically created daily for the selected employees.') }}
                    @elseif($form['frequency'] === 'weekly')
                        {{ __('This task will be automatically created weekly (every 7 days) for the selected employees.') }}
                    @else
                        {{ __('This task will be assigned once to the selected employees.') }}
                    @endif
                </flux:description>
            </flux:field>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeCreateFlyout" type="button">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit">{{ __('Assign Task') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Edit Task Flyout -->
    <flux:modal variant="flyout" wire:model="showEditFlyout" title="{{ __('Edit Task') }}" class="w-[40rem]">
        <form wire:submit="updateTask" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('Task Name') }} <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="form.name" placeholder="{{ __('Enter task name...') }}" required />
                <flux:error name="form.name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Task Description') }} <span class="text-red-500">*</span></flux:label>
                <flux:textarea wire:model="form.description" rows="6" placeholder="{{ __('Enter task description...') }}" required />
                <flux:error name="form.description" />
            </flux:field>

            <!-- Custom Fields -->
            @if(!empty($form['custom_fields']))
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:subheading>{{ __('Custom Fields') }}</flux:subheading>
                    </div>
                    @foreach($form['custom_fields'] as $index => $field)
                        <div class="flex items-start gap-3 p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <div class="flex-1 space-y-2">
                                <flux:field>
                                    <flux:label>{{ __('Field Name') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:input wire:model="form.custom_fields.{{ $index }}.name" placeholder="{{ __('Enter field name...') }}" required />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('Field Type') }}</flux:label>
                                    <flux:select wire:model="form.custom_fields.{{ $index }}.type">
                                        <option value="text">{{ __('Text') }}</option>
                                        <option value="number">{{ __('Number') }}</option>
                                        <option value="textarea">{{ __('Textarea') }}</option>
                                        <option value="date">{{ __('Date') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>
                            <button
                                type="button"
                                wire:click="removeCustomField({{ $index }})"
                                class="mt-6 p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                title="{{ __('Remove Field') }}"
                            >
                                <flux:icon name="trash" class="w-5 h-5" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Add Field Button -->
            <div>
                <flux:button type="button" variant="outline" wire:click="addCustomField" icon="plus">
                    {{ __('Add Field') }}
                </flux:button>
            </div>

            <!-- Attachments -->
            <flux:field>
                <flux:label>{{ __('Attachments') }}</flux:label>
                <flux:input 
                    type="file" 
                    wire:model="attachments" 
                    multiple
                    accept="*/*"
                />
                <flux:description>{{ __('You can attach multiple files. Maximum file size: 20MB per file.') }}</flux:description>
                <flux:error name="attachments.*" />
                
                @if(count($attachments) > 0)
                    <div class="mt-3 space-y-2">
                        @foreach($attachments as $index => $file)
                            @if($file)
                                <div class="flex items-center justify-between p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <flux:icon name="paper-clip" class="w-4 h-4 text-zinc-500 dark:text-zinc-400 flex-shrink-0" />
                                        <span class="text-sm text-zinc-900 dark:text-zinc-100 truncate">{{ $file->getClientOriginalName() }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ number_format($file->getSize() / 1024, 2) }} KB)</span>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="removeAttachment({{ $index }})"
                                        class="ml-2 p-1 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                        title="{{ __('Remove') }}"
                                    >
                                        <flux:icon name="x-mark" class="w-4 h-4" />
                                    </button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                @if(count($existingAttachments) > 0)
                    <div class="mt-3 space-y-2">
                        <p class="text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-2">{{ __('Existing Attachments') }}:</p>
                        @foreach($existingAttachments as $index => $attachment)
                            <div class="flex items-center justify-between p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <flux:icon name="paper-clip" class="w-4 h-4 text-zinc-500 dark:text-zinc-400 flex-shrink-0" />
                                    <a 
                                        href="{{ \Illuminate\Support\Facades\Storage::url($attachment['path']) }}" 
                                        target="_blank"
                                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline truncate"
                                    >
                                        {{ $attachment['name'] ?? 'Attachment' }}
                                    </a>
                                    @if(isset($attachment['size']))
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ number_format($attachment['size'] / 1024, 2) }} KB)</span>
                                    @endif
                                </div>
                                <button
                                    type="button"
                                    wire:click="removeExistingAttachment({{ $index }})"
                                    class="ml-2 p-1 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                    title="{{ __('Remove') }}"
                                >
                                    <flux:icon name="x-mark" class="w-4 h-4" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Assign To') }} <span class="text-red-500">*</span></flux:label>
                
                <!-- Search Input for Employees -->
                <div class="mb-3">
                    <flux:input 
                        wire:model.live.debounce.300ms="employeeSearchTerm"
                        placeholder="Search employees..."
                        icon="magnifying-glass"
                    />
                </div>
                
                <div class="relative">
                    <select 
                        wire:model="form.assigned_to" 
                        required
                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                    >
                        <option value="">{{ __('Select Employee') }}</option>
                        @foreach($this->filteredEmployeeOptions as $option)
                            <option value="{{ $option['value'] }}" class="py-2 px-3 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <flux:description>{{ __('Select the employee to assign this task to.') }}</flux:description>
                <flux:error name="form.assigned_to" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Due Date') }}</flux:label>
                <flux:input type="date" wire:model="form.due_date" />
                <flux:error name="form.due_date" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Frequency') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model.live="form.frequency" required>
                    <option value="one-time">{{ __('One-time') }}</option>
                    <option value="daily">{{ __('Daily') }}</option>
                    <option value="weekly">{{ __('Weekly') }}</option>
                </flux:select>
                <flux:error name="form.frequency" />
                <flux:description>
                    @if($form['frequency'] === 'daily')
                        {{ __('This task will be automatically created daily for the selected employee.') }}
                    @elseif($form['frequency'] === 'weekly')
                        {{ __('This task will be automatically created weekly (every 7 days) for the selected employee.') }}
                    @else
                        {{ __('This task will be assigned once to the selected employee.') }}
                    @endif
                </flux:description>
            </flux:field>

            <flux:field>
                <flux:checkbox wire:model="editForAll" />
                <flux:label>{{ __('Edit task for all?') }}</flux:label>
                <flux:description>{{ __('If checked, this will also update the master task template. Future tasks created from this template will use the updated values.') }}</flux:description>
            </flux:field>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeEditFlyout" type="button">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit">{{ __('Update Task') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- View Task Flyout -->
    <flux:modal variant="flyout" wire:model="showViewFlyout" title="{{ __('Task Details') }}" class="w-[40rem]">
        @if($selectedTask)
            <div class="space-y-6">
                <div>
                    <flux:subheading>{{ __('Task Name') }}</flux:subheading>
                    <div class="mt-2 text-sm">
                        <p class="text-zinc-900 dark:text-zinc-100 font-medium">{{ $selectedTask->name ?: $selectedTask->title }}</p>
                    </div>
                </div>
                @if($selectedTask->description)
                    <div>
                        <flux:subheading>{{ __('Description') }}</flux:subheading>
                        <div class="mt-2 text-sm">
                            <p class="text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ $selectedTask->description }}</p>
                        </div>
                    </div>
                @endif
                <div>
                    <flux:subheading>{{ __('Details') }}</flux:subheading>
                    <div class="mt-2 space-y-2 text-sm">
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Assigned By:') }}</span>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ $selectedTask->assignedBy->name }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Due Date:') }}</span>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ $selectedTask->due_date ? $selectedTask->due_date->format('M d, Y') : __('No due date') }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Frequency:') }}</span>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ ucfirst($selectedTask->frequency) }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Status:') }}</span>
                            @php
                                $statusColor = match($selectedTask->status) {
                                    'pending' => 'yellow',
                                    'completed' => 'green',
                                    'rejected' => 'red',
                                    default => 'zinc'
                                };
                            @endphp
                            <flux:badge color="{{ $statusColor }}" size="sm" class="mt-1">
                                {{ ucfirst($selectedTask->status) }}
                            </flux:badge>
                        </div>
                        @if($selectedTask->completed_at)
                            <div>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Completed At:') }}</span>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ $selectedTask->completed_at->format('M d, Y h:i A') }}</p>
                            </div>
                        @endif
                        @if($selectedTask->rejected_at)
                            <div>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Rejected At:') }}</span>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ $selectedTask->rejected_at->format('M d, Y h:i A') }}</p>
                            </div>
                            @if($selectedTask->rejection_reason)
                                <div>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Rejection Reason:') }}</span>
                                    <p class="text-zinc-600 dark:text-zinc-400">{{ $selectedTask->rejection_reason }}</p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                @if(!empty($selectedTask->attachments) && is_array($selectedTask->attachments))
                    <div>
                        <flux:subheading>{{ __('Attachments') }}</flux:subheading>
                        <div class="mt-2 space-y-2">
                            @foreach($selectedTask->attachments as $attachment)
                                <div class="flex items-center justify-between p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <flux:icon name="paper-clip" class="w-4 h-4 text-zinc-500 dark:text-zinc-400 flex-shrink-0" />
                                        <a 
                                            href="{{ \Illuminate\Support\Facades\Storage::url($attachment['path']) }}" 
                                            target="_blank"
                                            class="text-sm text-blue-600 dark:text-blue-400 hover:underline truncate"
                                        >
                                            {{ $attachment['name'] ?? 'Attachment' }}
                                        </a>
                                        @if(isset($attachment['size']))
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ number_format($attachment['size'] / 1024, 2) }} KB)</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($selectedTask->status === 'pending')
                    @php
                        $shiftEnded = $isEmployeeRole ? $this->hasShiftEnded($selectedTask) : false;
                    @endphp
                    
                    @if($shiftEnded)
                        <!-- Shift has ended - show message -->
                        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:callout variant="warning" icon="exclamation-triangle">
                                <div class="space-y-2">
                                    <p class="font-medium">{{ __('Task Submission Time Has Expired') }}</p>
                                    <p class="text-sm">{{ __('The shift for this task has ended. You can no longer complete or reject this task. Please contact your supervisor if you need assistance.') }}</p>
                                </div>
                            </flux:callout>
                        </div>
                        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button variant="ghost" wire:click="closeViewFlyout">{{ __('Close') }}</flux:button>
                        </div>
                    @else
                        <!-- Custom Fields -->
                        @if($selectedTask->custom_fields && count($selectedTask->custom_fields) > 0)
                            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700 space-y-4">
                                <flux:subheading>{{ __('Custom Fields') }}</flux:subheading>
                                @foreach($selectedTask->custom_fields as $field)
                                    @php
                                        $fieldName = $field['name'];
                                        $fieldLabel = ucfirst(str_replace('_', ' ', $field['label'] ?? $field['name']));
                                        $fieldType = $field['type'] ?? 'text';
                                    @endphp
                                    <flux:field>
                                        <flux:label>{{ $fieldLabel }} <span class="text-red-500">*</span></flux:label>
                                        @if($fieldType === 'textarea')
                                            <flux:textarea 
                                                wire:model.live="customFieldValues.{{ $fieldName }}" 
                                                rows="4"
                                                placeholder="{{ __('Enter') }} {{ strtolower($fieldLabel) }}..."
                                                required
                                            />
                                        @elseif($fieldType === 'number')
                                            <flux:input 
                                                type="number"
                                                wire:model.live="customFieldValues.{{ $fieldName }}" 
                                                placeholder="{{ __('Enter') }} {{ strtolower($fieldLabel) }}..."
                                                required
                                            />
                                        @elseif($fieldType === 'date')
                                            <flux:input 
                                                type="date"
                                                wire:model.live="customFieldValues.{{ $fieldName }}" 
                                                required
                                            />
                                        @else
                                            <flux:input 
                                                wire:model.live="customFieldValues.{{ $fieldName }}" 
                                                placeholder="{{ __('Enter') }} {{ strtolower($fieldLabel) }}..."
                                                required
                                            />
                                        @endif
                                    </flux:field>
                                @endforeach
                            </div>
                        @endif

                        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700 space-y-4">
                            <flux:field>
                                <flux:label>{{ __('Notes') }} <span class="text-red-500">*</span></flux:label>
                                <flux:textarea 
                                    wire:model="taskNotes" 
                                    rows="4" 
                                    placeholder="{{ __('Enter notes about completing or rejecting this task...') }}" 
                                    required
                                />
                                <flux:error name="taskNotes" />
                                <flux:description>{{ __('Please provide notes when completing or rejecting this task.') }}</flux:description>
                            </flux:field>
                            <div class="flex justify-end gap-3">
                                <flux:button variant="outline" wire:click="markAsCompleted({{ $selectedTask->id }})" class="flex items-center">
                                    <span>{{ __('Done') }}</span>
                                    <flux:icon name="check-circle" class="w-4 h-4 ml-2 text-green-600" />
                                </flux:button>
                                <flux:button variant="outline" wire:click="markAsRejected({{ $selectedTask->id }})" class="flex items-center">
                                    <span>{{ __('Rejected') }}</span>
                                    <flux:icon name="x-circle" class="w-4 h-4 ml-2 text-red-600" />
                                </flux:button>
                                <flux:button variant="ghost" wire:click="closeViewFlyout">{{ __('Close') }}</flux:button>
                            </div>
                        </div>
                    @endif
                @else
                    @if($selectedTask->completion_notes)
                        <div>
                            <flux:subheading>{{ __('Completion Notes') }}</flux:subheading>
                            <div class="mt-2 text-sm">
                                <p class="text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ $selectedTask->completion_notes }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button variant="ghost" wire:click="closeViewFlyout">{{ __('Close') }}</flux:button>
                    </div>
                @endif
            </div>
        @endif
    </flux:modal>

    <!-- Action Modal (Complete/Reject) -->
    <flux:modal wire:model="showActionModal" class="md:w-96">
        <div class="space-y-6">
            <div class="text-center">
                @if($actionType === 'complete')
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                            <flux:icon name="check-circle" class="w-10 h-10 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                    <flux:heading size="lg">{{ __('Complete Task') }}</flux:heading>
                @elseif($actionType === 'reject')
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                            <flux:icon name="x-circle" class="w-10 h-10 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                    <flux:heading size="lg">{{ __('Reject Task') }}</flux:heading>
                @endif
            </div>

            @if($actionType === 'complete' && $actionTaskId)
                @php
                    $actionTask = \App\Models\Task::find($actionTaskId);
                @endphp
                @if($actionTask && $actionTask->custom_fields && count($actionTask->custom_fields) > 0)
                    <!-- Custom Fields -->
                    <div class="space-y-4">
                        <flux:subheading>{{ __('Custom Fields') }}</flux:subheading>
                        @foreach($actionTask->custom_fields as $field)
                            @php
                                $fieldName = $field['name'];
                                $fieldLabel = ucfirst(str_replace('_', ' ', $field['label'] ?? $field['name']));
                                $fieldType = $field['type'] ?? 'text';
                            @endphp
                            <flux:field>
                                <flux:label>{{ $fieldLabel }} <span class="text-red-500">*</span></flux:label>
                                @if($fieldType === 'textarea')
                                    <flux:textarea 
                                        wire:model="customFieldValues.{{ $fieldName }}" 
                                        rows="4"
                                        placeholder="{{ __('Enter') }} {{ strtolower($fieldLabel) }}..."
                                        required
                                    />
                                @elseif($fieldType === 'number')
                                    <flux:input 
                                        type="number"
                                        wire:model="customFieldValues.{{ $fieldName }}" 
                                        placeholder="{{ __('Enter') }} {{ strtolower($fieldLabel) }}..."
                                        required
                                    />
                                @elseif($fieldType === 'date')
                                    <flux:input 
                                        type="date"
                                        wire:model="customFieldValues.{{ $fieldName }}" 
                                        required
                                    />
                                @else
                                    <flux:input 
                                        wire:model="customFieldValues.{{ $fieldName }}" 
                                        placeholder="{{ __('Enter') }} {{ strtolower($fieldLabel) }}..."
                                        required
                                    />
                                @endif
                            </flux:field>
                        @endforeach
                    </div>
                @endif
            @endif

            <flux:field>
                <flux:label>{{ __('Notes') }} <span class="text-red-500">*</span></flux:label>
                <flux:textarea 
                    wire:model="taskNotes" 
                    rows="4" 
                    placeholder="{{ __('Enter notes...') }}" 
                    required
                />
                <flux:error name="taskNotes" />
                <flux:description>
                    @if($actionType === 'complete')
                        {{ __('Please provide notes about completing this task.') }}
                    @elseif($actionType === 'reject')
                        {{ __('Please provide notes about rejecting this task.') }}
                    @endif
                </flux:description>
            </flux:field>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeActionModal">{{ __('Cancel') }}</flux:button>
                @if($actionType === 'complete')
                    <flux:button wire:click="markAsCompleted">
                        {{ __('Complete') }}
                    </flux:button>
                @elseif($actionType === 'reject')
                    <flux:button variant="danger" wire:click="markAsRejected">
                        {{ __('Reject') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>
    </x-tasks.layout>
</section>
