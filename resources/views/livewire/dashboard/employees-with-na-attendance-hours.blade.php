<div>
    @if(auth()->user()?->can('dashboard.view.attendance_na_total_hours'))
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-4">
                <div>
                    <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                        {{ __('Attendance: N/A total hours') }}
                    </flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Active employees with at least one N/A in Total hours from month start through yesterday (today excluded).') }}
                    </flux:text>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="min-w-[11rem]">
                        <flux:select wire:model.live="selectedMonth" class="text-sm">
                            @foreach($availableMonths as $m)
                                <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    <flux:button variant="ghost" size="sm" wire:click="refresh" icon="arrow-path">
                        {{ __('Refresh') }}
                    </flux:button>
                </div>
            </div>

            @if(count($rows) > 0)
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                    <div class="relative">
                        <div class="overflow-y-auto overflow-x-auto custom-scrollbar" style="max-height: 250px;">
                            <table class="w-full min-w-[32rem] text-sm">
                                <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            {{ __('Employee') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            {{ __('Code') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            {{ __('Department') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            {{ __('Group') }}
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            {{ __('N/A days') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($rows as $row)
                                        <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600">
                                            <td class="px-4 py-3 whitespace-nowrap text-zinc-900 dark:text-zinc-100 font-medium">
                                                {{ $row['name'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                                {{ $row['employee_code'] ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                                {{ $row['department'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                                {{ $row['group'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                                <flux:badge color="red" size="sm">{{ $row['na_count'] }}</flux:badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <flux:icon name="clock" class="w-12 h-12 mx-auto mb-4 text-zinc-400 dark:text-zinc-500" />
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('No employees with N/A total hours in this range.') }}
                    </p>
                </div>
            @endif
        </div>
    @endif
</div>
