<div class="flex items-start max-md:flex-col w-full">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <!-- Dashboard -->
            <div class="mb-3">
                <flux:navlist.item 
                    :href="route('system-management.index')" 
                    wire:navigate
                    :class="request()->routeIs('system-management.index') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                >
                    {{ __('Dashboard') }}
                </flux:navlist.item>
            </div>
            <!-- Organization Settings -->
            <div class="mb-3" x-data="{ open: {{ request()->routeIs('system-management.organization-setting.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('Organization Settings') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.department')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.department') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Department') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.designation')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.designation') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Designation') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.employment-status')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.employment-status') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Employment Status') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.employment-type')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.employment-type') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Employment Type') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.group')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.group') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Groups') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.country')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.country') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Country') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.province')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.province') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Provinces') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.organization-settings')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.organization-settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Organization Info') }}
                    </flux:navlist.item>
                </div>
            </div>

            <!-- User Management -->
            <div class="mb-3" x-data="{ open: {{ request()->routeIs('system-management.user-management.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('User Management') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item 
                        :href="route('system-management.user-management.user-roles')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.user-management.user-roles') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Roles & Permissions') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.user-management.users')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.user-management.users') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('System Users') }}
                    </flux:navlist.item>
                </div>
            </div>

            <!-- Financial Settings -->
            <div class="mb-3" x-data="{ open: {{ request()->routeIs('system-management.financial-settings.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('Financial Settings') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item 
                        :href="route('system-management.financial-settings.bank-info')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.financial-settings.bank-info') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Bank Info') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.financial-settings.currencies')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.financial-settings.currencies') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Currencies') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.financial-settings.vendors')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.financial-settings.vendors') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Vendor') }}
                    </flux:navlist.item>
                </div>
            </div>

            <!-- Attendance Settings -->
            <div class="mb-3" x-data="{ open: {{ request()->routeIs('system-management.attendance-settings.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('Attendance Settings') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item 
                        :href="route('system-management.attendance-settings.shift-schedule')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.attendance-settings.shift-schedule') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Shift Schedule') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.attendance-settings.work-schedule')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.attendance-settings.work-schedule') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Work Schedule') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.attendance-settings.attendance-rules')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.attendance-settings.attendance-rules') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Attendance Rules') }}
                    </flux:navlist.item>
                </div>
            </div>

            <!-- Leaves Management -->
            <div class="mb-3" x-data="{ open: {{ request()->routeIs('system-management.leaves-management.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('Leaves Management') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item 
                        :href="route('system-management.leaves-management.leave-types')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.leaves-management.leave-types') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Leave Types') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.leaves-management.leave-policies')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.leaves-management.leave-policies') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Leave Policies') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.leaves-management.leave-balances')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.leaves-management.leave-balances') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Leave Balances') }}
                    </flux:navlist.item>
                </div>
            </div>

            <!-- Payroll Settings -->
            <div class="mb-3" x-data="{ open: {{ request()->routeIs('system-management.payroll-settings.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('Payroll Settings') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item 
                        :href="route('system-management.payroll-settings.salary-components')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.payroll-settings.salary-components') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Salary Components') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.payroll-settings.payroll-periods')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.payroll-settings.payroll-periods') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Payroll Periods') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.payroll-settings.tax-settings')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.payroll-settings.tax-settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Tax Settings') }}
                    </flux:navlist.item>
                </div>
            </div>

            <!-- Security & Access -->
            <div class="mb-3" x-data="{ open: {{ request()->routeIs('system-management.security-access.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('Security & Access') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item 
                        :href="route('system-management.security-access.geo-restrictions')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.security-access.geo-restrictions') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Geo-Location Restriction') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.security-access.security')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.security-access.security') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Security Settings') }}
                    </flux:navlist.item>
                </div>
            </div>

            <!-- Operations -->
            <div class="mb-3" x-data="{ open: {{ request()->routeIs('system-management.operations.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('Operations') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item 
                        :href="route('system-management.operations.projects')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.operations.projects') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Project') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.operations.tasks')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.operations.tasks') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Task') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.operations.month-close')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.operations.month-close') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Month Close') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.operations.day-close')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.operations.day-close') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Day Close') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.operations.constants')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.operations.constants') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Constant') }}
                    </flux:navlist.item>
                </div>
            </div>

            <!-- System Configuration -->
            <div class="mb-3" x-data="{ open: {{ request()->routeIs('system-management.system-configuration.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center justify-between hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    {{ __('System Configuration') }}
                    <flux:icon name="chevron-down" class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" x-transition class="mt-1">
                    <flux:navlist.item 
                        :href="route('system-management.system-configuration.system-logs')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.system-configuration.system-logs') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('System Logs') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.system-configuration.announcements')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.system-configuration.announcements') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Announcement') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.system-configuration.polls')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.system-configuration.polls') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Poll') }}
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.system-configuration.holidays')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.system-configuration.holidays') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        {{ __('Gazetted Holidays') }}
                    </flux:navlist.item>
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
