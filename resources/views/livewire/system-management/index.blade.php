<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('System Management')" :subheading="__('Manage system settings and configurations')">
        <!-- Dashboard Overview -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Quick Stats Cards -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Users</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">156</p>
                        </div>
                        <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <flux:icon.users class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Departments</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">12</p>
                        </div>
                        <div class="h-8 w-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <flux:icon.building-office class="h-4 w-4 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Active Roles</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">8</p>
                        </div>
                        <div class="h-8 w-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <flux:icon.shield-check class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">System Health</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">98%</p>
                        </div>
                        <div class="h-8 w-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <flux:icon.heart class="h-4 w-4 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-5">
            <!-- Organization Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <flux:icon.building-office class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Organization Settings</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage organizational structure</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('system-management.organization-setting.department') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Department</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.organization-setting.designation') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Designation</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.organization-setting.employment-status') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Employment Status</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.organization-setting.employment-type') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Employment Type</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.organization-setting.group') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Groups</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.organization-setting.region') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Region</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.organization-setting.cost-center') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Cost Center</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.organization-setting.brands') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Brands</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                </div>
                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="flex space-x-2">
                        <flux:button variant="primary" size="sm" :href="route('system-management.organization-setting.department')" wire:navigate>
                            Manage
                        </flux:button>
                        <flux:button variant="outline" size="sm" :href="route('system-management.organization-setting.department')" wire:navigate>
                            Add New
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- User Management -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <flux:icon.users class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">User Management</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage users and access</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('system-management.user-management.user-roles') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>User Roles</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.user-management.users') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>System Users</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                </div>
                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="flex space-x-2">
                        <flux:button variant="primary" size="sm" :href="route('system-management.user-management.user-roles')" wire:navigate>
                            Manage
                        </flux:button>
                        <flux:button variant="outline" size="sm" :href="route('system-management.user-management.users')" wire:navigate>
                            Add New
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- Financial Settings -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                            <flux:icon.banknotes class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Financial Settings</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage financial configurations</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('system-management.financial-settings.bank-info') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Bank Info</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.financial-settings.currencies') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Currencies</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.financial-settings.vendors') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Vendor</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                </div>
                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="flex space-x-2">
                        <flux:button variant="primary" size="sm" :href="route('system-management.financial-settings.bank-info')" wire:navigate>
                            Manage
                        </flux:button>
                        <flux:button variant="outline" size="sm" :href="route('system-management.financial-settings.bank-info')" wire:navigate>
                            Add New
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- System Configuration -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <flux:icon.cog-6-tooth class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">System Configuration</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Configure system settings</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('system-management.system-configuration.system-logs') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>System Logs</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.system-configuration.announcements') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Announcement</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.system-configuration.polls') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Poll</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.system-configuration.holidays') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Gazetted Holidays</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                </div>
                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="flex space-x-2">
                        <flux:button variant="primary" size="sm" :href="route('system-management.system-configuration.system-logs')" wire:navigate>
                            Manage
                        </flux:button>
                        <flux:button variant="outline" size="sm" :href="route('system-management.system-configuration.announcements')" wire:navigate>
                            Add New
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- Security & Access -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                            <flux:icon.shield-check class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Security & Access</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage security settings</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('system-management.security-access.geo-restrictions') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Geo-Location Restriction</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.security-access.security') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Security Settings</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                </div>
                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="flex space-x-2">
                        <flux:button variant="primary" size="sm" :href="route('system-management.security-access.security')" wire:navigate>
                            Manage
                        </flux:button>
                        <flux:button variant="outline" size="sm" :href="route('system-management.security-access.geo-restrictions')" wire:navigate>
                            Configure
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- Operations -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                            <flux:icon.clipboard-document-list class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Operations</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage operations</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('system-management.operations.projects') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Project</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.operations.tasks') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Task</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.operations.month-close') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Month Close</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.operations.day-close') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Day Close</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('system-management.operations.constants') }}" class="flex items-center justify-between p-2 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm text-zinc-700 dark:text-zinc-300">
                        <span>Constant</span>
                        <flux:icon.chevron-right class="h-4 w-4" />
                    </a>
                </div>
                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="flex space-x-2">
                        <flux:button variant="primary" size="sm" :href="route('system-management.operations.projects')" wire:navigate>
                            Manage
                        </flux:button>
                        <flux:button variant="outline" size="sm" :href="route('system-management.operations.tasks')" wire:navigate>
                            Add New
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </x-system-management.layout>
</section>
