<div class="flex items-start max-md:flex-col w-full">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <!-- Dashboard -->
             <div class="mb-3">
                <flux:navlist.item :href="route('system-management.index')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
            </div>
            <!-- Organization Settings -->
            <div class="mb-3" x-data="{ open: false }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('Organization Settings') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item :href="route('system-management.organization-setting.department')" wire:navigate>{{ __('Department') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.organization-setting.designation')" wire:navigate>{{ __('Designation') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.organization-setting.employee-status')" wire:navigate>{{ __('Employee Status') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.organization-setting.group')" wire:navigate>{{ __('Groups') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.organization-setting.country')" wire:navigate>{{ __('Country') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.organization-setting.province')" wire:navigate>{{ __('Provinces') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.organization-setting.organization-settings')" wire:navigate>{{ __('Organization Settings') }}</flux:navlist.item>
                </div>
            </div>

            <!-- User Management -->
            <div class="mb-3" x-data="{ open: false }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('User Management') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item :href="route('system-management.user-management.user-roles')" wire:navigate>{{ __('User Roles') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.user-management.users')" wire:navigate>{{ __('System Users') }}</flux:navlist.item>
                </div>
            </div>

            <!-- Financial Settings -->
            <div class="mb-3" x-data="{ open: false }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('Financial Settings') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item :href="route('system-management.financial-settings.bank-info')" wire:navigate>{{ __('Bank Info') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.financial-settings.currencies')" wire:navigate>{{ __('Currencies') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.financial-settings.vendors')" wire:navigate>{{ __('Vendor') }}</flux:navlist.item>
                </div>
            </div>

            <!-- System Configuration -->
            <div class="mb-3" x-data="{ open: false }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('System Configuration') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item :href="route('system-management.system-configuration.system-logs')" wire:navigate>{{ __('System Logs') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.system-configuration.announcements')" wire:navigate>{{ __('Announcement') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.system-configuration.polls')" wire:navigate>{{ __('Poll') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.system-configuration.holidays')" wire:navigate>{{ __('Gazetted Holidays') }}</flux:navlist.item>
                </div>
            </div>

            <!-- Security & Access -->
            <div class="mb-3" x-data="{ open: false }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('Security & Access') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item :href="route('system-management.security-access.geo-restrictions')" wire:navigate>{{ __('Geo-Location Restriction') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.security-access.security')" wire:navigate>{{ __('Security Settings') }}</flux:navlist.item>
                </div>
            </div>

            <!-- Operations -->
            <div class="mb-3" x-data="{ open: false }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('Operations') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item :href="route('system-management.operations.projects')" wire:navigate>{{ __('Project') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.operations.tasks')" wire:navigate>{{ __('Task') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.operations.month-close')" wire:navigate>{{ __('Month Close') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.operations.day-close')" wire:navigate>{{ __('Day Close') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('system-management.operations.constants')" wire:navigate>{{ __('Constant') }}</flux:navlist.item>
                </div>
            </div>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6 min-w-0">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:heading>

        <div class="mt-5 w-full max-w-full overflow-hidden">
            {{ $slot }}
        </div>
    </div>
</div>
