<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Leave Settings')" :subheading="__('Configure organisation-wide leave defaults, carry-forward rules, and notifications.')" >
        <form wire:submit.prevent="save" class="space-y-6">
            <!-- General Leave Configuration -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('General Leave Configuration') }}</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Define how leave quotas are generated and whether manual overrides are available.') }}
                        </p>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <flux:switch
                        wire:model.live="form.auto_assign_enabled"
                        label="{{ __('Enable Automatic Leave Quotas') }}"
                        description="{{ __('When enabled, employees receive leave quotas based on the configured policies.') }}"
                    />

                    <flux:separator variant="subtle" />

                    <flux:switch
                        wire:model.live="form.allow_manual_overrides"
                        label="{{ __('Allow Manual Adjustments') }}"
                        description="{{ __('HR can allocate additional leave or make deductions on top of automated quotas.') }}"
                    />
                </div>
            </div>

            <!-- Accrual Defaults -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Accrual Defaults') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Baseline rules applied to new leave policies unless overridden per leave type.') }}
                    </p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('Default Accrual Frequency') }}</flux:label>
                            <flux:select wire:model="form.default_accrual_frequency">
                                @foreach($frequencies as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Probation Wait (days)') }}</flux:label>
                            <flux:input type="number" min="0" wire:model.defer="form.default_probation_wait_days" placeholder="{{ __('e.g. 90') }}" />
                        </flux:field>
                    </div>

                    <flux:switch
                        wire:model.live="form.default_prorate_on_joining"
                        label="{{ __('Prorate First Cycle on Joining') }}"
                        description="{{ __('Distribute the first accrual based on the employee’s joining date.') }}"
                    />
                </div>
            </div>

            <!-- Carry Forward & Encashment -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Carry-forward & Encashment') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Set default rollover rules and whether unused leave can be cashed out.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="space-y-4">
                        <flux:switch
                            wire:model.live="form.carry_forward_enabled"
                            label="{{ __('Enable Carry-forward') }}"
                            description="{{ __('Allow unused leave to move into the next cycle.') }}"
                        />

                        @if($form['carry_forward_enabled'])
                            <div class="grid gap-6 md:grid-cols-2">
                                <flux:field>
                                    <flux:label>{{ __('Carry-forward Cap') }}</flux:label>
                                    <flux:input type="number" step="0.1" min="0" wire:model.defer="form.carry_forward_cap" placeholder="{{ __('Leave days or hours') }}" />
                                    <flux:description>{{ __('Leave empty for unlimited rollover.') }}</flux:description>
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('Carry-forward Expiry (days)') }}</flux:label>
                                    <flux:input type="number" min="0" wire:model.defer="form.carry_forward_expiry_days" placeholder="{{ __('e.g. 90') }}" />
                                    <flux:description>{{ __('Leave empty if carry-forward never expires.') }}</flux:description>
                                </flux:field>
                            </div>
                        @endif
                    </div>

                    <flux:separator variant="subtle" />

                    <div class="space-y-4">
                        <flux:switch
                            wire:model.live="form.encashment_enabled"
                            label="{{ __('Enable Leave Encashment') }}"
                            description="{{ __('Allow employees to encash unused leave based on policy rules.') }}"
                        />

                        @if($form['encashment_enabled'])
                            <flux:field>
                                <flux:label>{{ __('Encashment Cap') }}</flux:label>
                                <flux:input type="number" step="0.1" min="0" wire:model.defer="form.encashment_cap" placeholder="{{ __('Maximum encashable units') }}" />
                                <flux:description>{{ __('Leave empty for no cap.') }}</flux:description>
                            </flux:field>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Working Days & Notifications -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Working Day Template & Notifications') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Define default working days and who should be notified about leave activity.') }}
                    </p>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <flux:label>{{ __('Default Working Days') }}</flux:label>
                        <flux:description>{{ __('Used when policies prorate leave or check eligibility.') }}</flux:description>

                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($weekdays as $key => $label)
                                @php
                                    $selected = in_array($key, $form['working_day_rules']['working_days'] ?? []);
                                @endphp
                                <button
                                    type="button"
                                    wire:click="toggleWorkingDay('{{ $key }}')"
                                    class="px-3 py-1 text-sm font-medium rounded-md border transition-colors
                                        {{ $selected
                                            ? 'bg-blue-600 border-blue-600 text-white'
                                            : 'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        @error('form.working_day_rules.working_days')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <flux:separator variant="subtle" />

                    <div class="grid gap-6 md:grid-cols-2">
                        <flux:switch
                            wire:model.live="form.notification_preferences.notify_manager_on_request"
                            label="{{ __('Notify Managers on New Requests') }}"
                            description="{{ __('Send an alert to the reporting manager when an employee submits a leave request.') }}"
                        />

                        <flux:switch
                            wire:model.live="form.notification_preferences.notify_employee_on_status_change"
                            label="{{ __('Notify Employees on Status Updates') }}"
                            description="{{ __('Employees receive a notification when their leave request is approved or rejected.') }}"
                        />

                        <flux:switch
                            wire:model.live="form.notification_preferences.notify_hr_on_low_balance"
                            label="{{ __('Alert HR on Low Balances') }}"
                            description="{{ __('Send a reminder to HR when an employee’s balance drops below policy thresholds.') }}"
                        />
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <flux:button
                    type="submit"
                    variant="primary"
                    icon="check-circle"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>{{ __('Save Settings') }}</span>
                    <span wire:loading>{{ __('Saving...') }}</span>
                </flux:button>
            </div>
        </form>
    </x-system-management.layout>
</section>
