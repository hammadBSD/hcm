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
                    <div class="flex items-center">
                        <flux:icon name="squares-2x2" class="w-4 h-4 mr-3" />
                        {{ __('Dashboard') }}
                    </div>
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
                        <div class="flex items-center">
                            <flux:icon name="building-office-2" class="w-4 h-4 mr-3" />
                            {{ __('Department') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.designation')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.designation') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="briefcase" class="w-4 h-4 mr-3" />
                            {{ __('Designation') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.employment-status')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.employment-status') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="check-circle" class="w-4 h-4 mr-3" />
                            {{ __('Employment Status') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.employment-type')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.employment-type') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="identification" class="w-4 h-4 mr-3" />
                            {{ __('Employment Type') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.group')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.group') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="users" class="w-4 h-4 mr-3" />
                            {{ __('Groups') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.region')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.region') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="map-pin" class="w-4 h-4 mr-3" />
                            {{ __('Region') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.cost-center')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.cost-center') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="building-library" class="w-4 h-4 mr-3" />
                            {{ __('Cost Center') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.brands')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.brands') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="tag" class="w-4 h-4 mr-3" />
                            {{ __('Brands') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.country')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.country') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="globe-alt" class="w-4 h-4 mr-3" />
                            {{ __('Country') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.province')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.province') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="map" class="w-4 h-4 mr-3" />
                            {{ __('Provinces') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.organization-setting.organization-settings')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.organization-setting.organization-settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="building-office-2" class="w-4 h-4 mr-3" />
                            {{ __('Organization Info') }}
                        </div>
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
                        <div class="flex items-center">
                            <flux:icon name="shield-check" class="w-4 h-4 mr-3" />
                            {{ __('Roles & Permissions') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.user-management.users')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.user-management.users') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="users" class="w-4 h-4 mr-3" />
                            {{ __('System Users') }}
                        </div>
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
                        <div class="flex items-center">
                            <flux:icon name="building-library" class="w-4 h-4 mr-3" />
                            {{ __('Bank Info') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.financial-settings.currencies')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.financial-settings.currencies') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="currency-dollar" class="w-4 h-4 mr-3" />
                            {{ __('Currencies') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.financial-settings.vendors')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.financial-settings.vendors') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="truck" class="w-4 h-4 mr-3" />
                            {{ __('Vendor') }}
                        </div>
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
                        <div class="flex items-center">
                            <flux:icon name="clock" class="w-4 h-4 mr-3" />
                            {{ __('Shift Schedule') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.attendance-settings.work-schedule')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.attendance-settings.work-schedule') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="calendar-days" class="w-4 h-4 mr-3" />
                            {{ __('Work Schedule') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.attendance-settings.attendance-rules')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.attendance-settings.attendance-rules') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="clipboard-document" class="w-4 h-4 mr-3" />
                            {{ __('Attendance Rules') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.attendance-settings.break-settings')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.attendance-settings.break-settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="pause" class="w-4 h-4 mr-3" />
                            {{ __('Break Settings') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.attendance-settings.global-grace-time')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.attendance-settings.global-grace-time') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="clock" class="w-4 h-4 mr-3" />
                            {{ __('Global Grace Time') }}
                        </div>
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
                        :href="route('system-management.leaves-management.settings')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.leaves-management.settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="adjustments-horizontal" class="w-4 h-4 mr-3" />
                            {{ __('Leave Settings') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.leaves-management.leave-types')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.leaves-management.leave-types') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="document-text" class="w-4 h-4 mr-3" />
                            {{ __('Leave Types') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.leaves-management.leave-policies')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.leaves-management.leave-policies') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="document-check" class="w-4 h-4 mr-3" />
                            {{ __('Leave Policies') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.leaves-management.leave-balances')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.leaves-management.leave-balances') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="chart-bar" class="w-4 h-4 mr-3" />
                            {{ __('Leave Balances') }}
                        </div>
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
                        <div class="flex items-center">
                            <flux:icon name="banknotes" class="w-4 h-4 mr-3" />
                            {{ __('Salary Components') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.payroll-settings.payroll-periods')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.payroll-settings.payroll-periods') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="calendar-days" class="w-4 h-4 mr-3" />
                            {{ __('Payroll Periods') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.payroll-settings.tax-settings')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.payroll-settings.tax-settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="calculator" class="w-4 h-4 mr-3" />
                            {{ __('Tax Settings') }}
                        </div>
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
                        <div class="flex items-center">
                            <flux:icon name="map-pin" class="w-4 h-4 mr-3" />
                            {{ __('Geo-Location Restriction') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.security-access.security')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.security-access.security') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="shield-exclamation" class="w-4 h-4 mr-3" />
                            {{ __('Security Settings') }}
                        </div>
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
                        <div class="flex items-center">
                            <flux:icon name="folder" class="w-4 h-4 mr-3" />
                            {{ __('Project') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.operations.tasks')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.operations.tasks') || request()->routeIs('system-management.operations.tasks.settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="clipboard-document-check" class="w-4 h-4 mr-3" />
                            {{ __('Tasks') }}
                        </div>
                    </flux:navlist.item>
                    @can('tasks.manage.settings')
                    <flux:navlist.item 
                        :href="route('system-management.operations.tasks.settings')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.operations.tasks.settings') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="cog-6-tooth" class="w-4 h-4 mr-3" />
                            {{ __('Task Settings') }}
                        </div>
                    </flux:navlist.item>
                    @endcan
                    <flux:navlist.item 
                        :href="route('system-management.operations.month-close')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.operations.month-close') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="calendar-days" class="w-4 h-4 mr-3" />
                            {{ __('Month Close') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.operations.day-close')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.operations.day-close') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="sun" class="w-4 h-4 mr-3" />
                            {{ __('Day Close') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.operations.constants')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.operations.constants') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="adjustments-horizontal" class="w-4 h-4 mr-3" />
                            {{ __('Constant') }}
                        </div>
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
                        <div class="flex items-center">
                            <flux:icon name="document-text" class="w-4 h-4 mr-3" />
                            {{ __('System Logs') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.system-configuration.announcements')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.system-configuration.announcements') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="megaphone" class="w-4 h-4 mr-3" />
                            {{ __('Announcement') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.system-configuration.polls')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.system-configuration.polls') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="chart-bar" class="w-4 h-4 mr-3" />
                            {{ __('Poll') }}
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item 
                        :href="route('system-management.system-configuration.holidays')" 
                        wire:navigate
                        :class="request()->routeIs('system-management.system-configuration.holidays') ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : ''"
                    >
                        <div class="flex items-center">
                            <flux:icon name="calendar-days" class="w-4 h-4 mr-3" />
                            {{ __('Gazetted Holidays') }}
                        </div>
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
