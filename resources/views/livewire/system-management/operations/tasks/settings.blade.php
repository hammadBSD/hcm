<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Task Settings')" :subheading="__('Configure task logging settings and rules')">
        <form wire:submit.prevent="save" class="space-y-6">
            <!-- General Task Configuration -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('General Task Configuration') }}</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Enable or disable daily task logging and configure basic rules.') }}
                        </p>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="form.enabled"
                        label="{{ __('Enable Daily Task Logging') }}"
                        description="{{ __('When enabled, employees can log their daily tasks.') }}"
                    />

                    <flux:separator variant="subtle" />

                    <flux:switch
                        wire:model.live="form.mandatory"
                        label="{{ __('Make Task Logging Mandatory') }}"
                        description="{{ __('When enabled, employees must log tasks daily.') }}"
                    />

                    <flux:separator variant="subtle" />

                    <flux:switch
                        wire:model.live="form.split_periods"
                        label="{{ __('Enable First/Second Half Split') }}"
                        description="{{ __('Allow employees to log tasks separately for first half and second half of the day.') }}"
                    />
                </div>
            </div>

            <!-- Lock Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Lock Settings') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Configure when tasks should be locked to prevent further edits.') }}
                    </p>
                </div>

                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="form.lock_after_shift"
                        label="{{ __('Lock Tasks After Shift Ends') }}"
                        description="{{ __('When enabled, tasks will be locked after the employee's shift ends (with grace period).') }}"
                    />

                    @if($form['lock_after_shift'])
                        <div class="pl-4 border-l-2 border-zinc-200 dark:border-zinc-700">
                            <flux:input
                                type="number"
                                wire:model.defer="form.lock_grace_period_minutes"
                                label="{{ __('Grace Period (Minutes)') }}"
                                helper-text="{{ __('Additional time after shift ends before tasks are locked.') }}"
                                min="0"
                                max="1440"
                            />
                        </div>
                    @endif
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end gap-3">
                <flux:button type="submit" variant="primary">
                    {{ __('Save Settings') }}
                </flux:button>
            </div>
        </form>
    </x-system-management.layout>
</section>
