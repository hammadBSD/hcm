<section class="w-full">
    @include('partials.employees-heading')

    <x-employees.layout :heading="__('All Employees')" :subheading="__('Manage and view all employees in your organization')">
        <form class="my-6 w-full space-y-6">
            <flux:input wire:model.live.debounce.300ms="search" :label="__('Search')" type="text" placeholder="Search by name or email..." />

            <div>
                <flux:label>{{ __('Department') }}</flux:label>
                <select wire:model.live="filterDepartment" class="w-full rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach($departments as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="button" class="w-full">{{ __('Add Employee') }}</flux:button>
                </div>

                <flux:button variant="outline" type="button" icon="arrow-down-tray">
                    {{ __('Export') }}
                </flux:button>
            </div>
        </form>

        <!-- Employee List -->
        <div class="mt-8 space-y-4">
            @forelse($employees as $employee)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <flux:avatar size="md" :initials="$employee->initials()" />
                            <div>
                                <flux:heading size="md" level="3" class="text-zinc-900 dark:text-zinc-100">
                                    {{ $employee->name }}
                                </flux:heading>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $employee->email }}
                                </flux:text>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($employee->roles->count() > 0)
                                        <flux:badge color="blue" size="sm">
                                            {{ $employee->roles->first()->name }}
                                        </flux:badge>
                                    @endif
                                    <flux:badge color="green" size="sm">
                                        Active
                                    </flux:badge>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:button variant="ghost" size="sm" icon="eye" href="#" />
                            <flux:button variant="ghost" size="sm" icon="pencil" href="#" />
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="user">View Profile</flux:menu.item>
                                    <flux:menu.item icon="pencil">Edit Details</flux:menu.item>
                                    <flux:menu.item icon="key">Reset Password</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" color="red">Deactivate</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <flux:heading size="lg" level="3" class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('No employees found') }}
                    </flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                        {{ __('Get started by adding your first employee.') }}
                    </flux:text>
                    <flux:button href="#" icon="plus" class="mt-4">
                        {{ __('Add Employee') }}
                    </flux:button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($employees->hasPages())
            <div class="mt-6">
                {{ $employees->links() }}
            </div>
        @endif
    </x-employees.layout>
</section>