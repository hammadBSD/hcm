<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Users')" :subheading="__('Manage system users')">
        <div class="space-y-6">
            {{-- Unmatched device employees (not in HR employees by punch code) --}}
            <div class="{{ $unmatchedDeviceCount > 0 ? '' : 'hidden' }}">
                @if ($unmatchedDeviceCount > 0)
                    <div
                        id="device-not-in-hr-alert"
                        role="button"
                        tabindex="0"
                        onclick="document.getElementById('system-users-table')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); document.getElementById('system-users-table')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }"
                        class="flex items-center gap-3 p-4 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 cursor-pointer hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors"
                    >
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                            <flux:icon name="exclamation-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                {{ $unmatchedDeviceCount === 1
                                    ? __('1 person is on a device but not matched in Employees')
                                    : __(':count people are on a device but not matched in Employees', ['count' => $unmatchedDeviceCount]) }}
                            </p>
                            <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                                {{ __('Click to scroll to the users table. Use the filter to show only these rows.') }}
                            </p>
                        </div>
                        <flux:icon name="chevron-down" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" />
                    </div>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-end justify-between">
                <div class="flex-1 max-w-md">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        type="search"
                        :label="__('Search')"
                        :placeholder="__('Punch code, name, email, department, device IP…')"
                        icon="magnifying-glass"
                    />
                </div>
                <div class="w-full sm:w-80">
                    <flux:field>
                        <flux:label>{{ __('Show') }}</flux:label>
                        <flux:select wire:model.live="viewFilter">
                            <option value="all">{{ __('All (HR + unmatched device)') }}</option>
                            <option value="hr_only">{{ __('HR employees only') }}</option>
                            <option value="device_not_in_hr">{{ __('Device attendance not in Employees') }}</option>
                        </flux:select>
                    </flux:field>
                </div>
            </div>

            <div
                id="system-users-table"
                class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Source') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Punch code') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Employee code') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Name') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Email') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Department') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('HR status / Device') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($rows as $row)
                                <tr
                                    wire:key="{{ $row['row_key'] }}"
                                    class="hover:bg-zinc-100 dark:hover:bg-zinc-600/50 transition-colors duration-150"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if (($row['source'] ?? '') === 'hr')
                                            <flux:badge color="blue" size="sm">{{ __('HR') }}</flux:badge>
                                        @else
                                            <flux:badge color="amber" size="sm">{{ __('Device only') }}</flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100 font-mono">
                                        {{ $row['punch_code'] ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $row['employee_code'] ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $row['name'] ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $row['email'] ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $row['department'] ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                                        @if (($row['source'] ?? '') === 'hr')
                                            <span class="text-zinc-900 dark:text-zinc-100">{{ $row['hr_status'] ?? '—' }}</span>
                                        @else
                                            <div class="flex flex-col gap-0.5">
                                                @if (! empty($row['device_ip']))
                                                    <span class="font-mono text-xs">{{ $row['device_ip'] }}</span>
                                                @endif
                                                @if (! empty($row['device_type']))
                                                    <flux:badge color="zinc" size="sm">{{ $row['device_type'] }}</flux:badge>
                                                @endif
                                                @if (empty($row['device_ip']) && empty($row['device_type']))
                                                    <span>—</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        <div class="flex flex-col items-center gap-3">
                                            <flux:icon name="users" class="w-10 h-10 text-zinc-400 dark:text-zinc-600" />
                                            <div>{{ __('No users match your search or filter.') }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-2">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-zinc-700 dark:text-zinc-300">
                        @if ($rows->total())
                            {{ __('Showing :from to :to of :total results', ['from' => $rows->firstItem(), 'to' => $rows->lastItem(), 'total' => $rows->total()]) }}
                        @else
                            {{ __('Showing 0 results') }}
                        @endif
                    </div>
                    <div>
                        {{ $rows->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </x-system-management.layout>
</section>
