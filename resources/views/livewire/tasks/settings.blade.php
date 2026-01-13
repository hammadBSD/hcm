<section class="w-full">
    @include('partials.tasks-heading')
    <x-tasks.layout :heading="__('Tasks Settings')" :subheading="__('Configure task management settings and rules')">
        @if(session('success'))
            <flux:callout variant="success" icon="check-circle" class="mb-6">
                {{ session('success') }}
            </flux:callout>
        @endif

        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Task Completion Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Task Completion Settings') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Configure requirements for completing or rejecting tasks.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="form.require_notes_on_completion"
                        label="{{ __('Require Notes on Completion') }}"
                        description="{{ __('When enabled, employees must provide notes when marking a task as completed.') }}"
                    />

                    <flux:separator variant="subtle" />

                    <flux:switch
                        wire:model.live="form.require_notes_on_rejection"
                        label="{{ __('Require Notes on Rejection') }}"
                        description="{{ __('When enabled, employees must provide notes when marking a task as rejected.') }}"
                    />
                </div>
            </div>

            <!-- Auto-Assignment Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Auto-Assignment Settings') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Configure automatic task assignment behavior.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="form.auto_assign_daily_tasks"
                        label="{{ __('Auto-Assign Daily Tasks') }}"
                        description="{{ __('When enabled, daily tasks will be automatically created for assigned employees.') }}"
                    />

                    <flux:separator variant="subtle" />

                    <flux:switch
                        wire:model.live="form.auto_assign_weekly_tasks"
                        label="{{ __('Auto-Assign Weekly Tasks') }}"
                        description="{{ __('When enabled, weekly tasks will be automatically created for assigned employees.') }}"
                    />
                </div>
            </div>

            <!-- Default Task Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Default Task Settings') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Configure default values for new tasks.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <flux:input
                        type="number"
                        wire:model.defer="form.default_due_date_days"
                        label="{{ __('Default Due Date (Days)') }}"
                        helper-text="{{ __('Default number of days from creation date for task due date. Set to 0 to disable default due date.') }}"
                        min="0"
                        max="365"
                    />
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Notification Settings') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Configure task-related notifications.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="form.enable_task_notifications"
                        label="{{ __('Enable Task Notifications') }}"
                        description="{{ __('When enabled, users will receive notifications for task assignments, completions, and rejections.') }}"
                    />
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end gap-3">
                <flux:button type="submit" variant="primary">
                    {{ __('Save Settings') }}
                </flux:button>
            </div>
        </form>
    </x-tasks.layout>
</section>
