<div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex items-center justify-between mb-4">
        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
            {{ __('My Tasks') }}
        </flux:heading>
        <flux:button variant="outline" size="sm" href="{{ route('tasks.my-tasks') }}">
            {{ __('View All') }}
        </flux:button>
    </div>
    
    @if(session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout variant="danger" icon="exclamation-circle" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <div class="space-y-3">
        @forelse($tasks as $task)
            <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                {{ $task->name ?: $task->title }}
                            </div>
                            @if($task->description)
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1 mt-1">
                                    {{ $task->description }}
                                </div>
                            @endif
                            @if($task->due_date)
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                    {{ __('Due') }}: {{ $task->due_date->format('M d, Y') }}
                                    @if($task->isOverdue())
                                        <span class="text-red-600 dark:text-red-400 ml-2">{{ __('Overdue') }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button
                                wire:click="openActionModal({{ $task->id }}, 'complete')"
                                class="w-8 h-8 rounded-full bg-white dark:bg-zinc-700 border border-green-200 dark:border-green-800 hover:bg-green-50 dark:hover:bg-green-900/30 transition-colors flex items-center justify-center"
                                title="{{ __('Mark as Completed') }}"
                            >
                                <flux:icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400" />
                            </button>
                            <button
                                wire:click="openActionModal({{ $task->id }}, 'reject')"
                                class="w-8 h-8 rounded-full bg-white dark:bg-zinc-700 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors flex items-center justify-center"
                                title="{{ __('Mark as Rejected') }}"
                            >
                                <flux:icon name="x-circle" class="w-5 h-5 text-red-600 dark:text-red-400" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8">
                <flux:icon name="check-circle" class="w-12 h-12 mx-auto mb-4 text-green-500" />
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('No pending tasks.') }}
                </p>
            </div>
        @endforelse
    </div>

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

            @if($actionType === 'complete' && $actionTask && $actionTask->custom_fields && count($actionTask->custom_fields) > 0)
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
</div>
