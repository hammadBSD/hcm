<section class="w-full">
    @include('partials.system-management-heading')
    
    <x-system-management.layout :heading="__('Global Grace Time')" :subheading="__('Configure global grace periods for late check-in and early check-out')">
        <!-- Flash Messages -->
        @if (session()->has('message'))
            <flux:callout variant="success" icon="check-circle" dismissible>
                {{ session('message') }}
            </flux:callout>
        @endif

        <div class="space-y-6">
            <!-- Global Grace Period Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Grace Period Settings') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                        {{ __('These settings will be used as default for all shifts unless a shift has its own specific grace periods configured.') }}
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <flux:field>
                        <flux:label>{{ __('Late Check-in Grace Period') }}</flux:label>
                        <flux:description>{{ __('The number of minutes after the shift start time that will be considered acceptable for late check-in. This applies globally unless a shift has its own grace period or has grace periods disabled.') }}</flux:description>
                        <flux:input
                            type="number"
                            wire:model="gracePeriodLateIn"
                            placeholder="30"
                            min="0"
                            max="1440"
                            suffix="minutes"
                        />
                        <flux:error name="gracePeriodLateIn" />
                    </flux:field>
                    
                    <flux:separator variant="subtle" />
                    
                    <flux:field>
                        <flux:label>{{ __('Early Check-out Grace Period') }}</flux:label>
                        <flux:description>{{ __('The number of minutes before the shift end time that will be considered acceptable for early check-out. This applies globally unless a shift has its own grace period or has grace periods disabled.') }}</flux:description>
                        <flux:input
                            type="number"
                            wire:model="gracePeriodEarlyOut"
                            placeholder="30"
                            min="0"
                            max="1440"
                            suffix="minutes"
                        />
                        <flux:error name="gracePeriodEarlyOut" />
                    </flux:field>
                    
                    <flux:separator variant="subtle" />
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <flux:button variant="outline" wire:click="resetToDefaults">
                            {{ __('Reset to Defaults') }}
                        </flux:button>
                        <flux:button wire:click="saveGracePeriods">
                            {{ __('Save Settings') }}
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- Information Box -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 p-6">
                <div class="flex items-start gap-3">
                    <flux:icon name="information-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                    <div class="space-y-2">
                        <h4 class="font-semibold text-blue-900 dark:text-blue-100">{{ __('How Grace Periods Work') }}</h4>
                        <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-disc list-inside">
                            <li>{{ __('Global grace periods are used as defaults for all shifts.') }}</li>
                            <li>{{ __('Individual shifts can override these global settings with their own grace periods.') }}</li>
                            <li>{{ __('Shifts can also completely disable grace periods, ignoring both global and shift-specific settings.') }}</li>
                            <li>{{ __('If a shift has its own grace period configured, it will use that instead of the global setting.') }}</li>
                            <li>{{ __('If a shift has grace periods disabled, no grace period will be applied regardless of global or shift-specific settings.') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </x-system-management.layout>
</section>
