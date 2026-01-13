<div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex items-center justify-between mb-4">
        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
            {{ __('Absent & Late Employees') }}
        </flux:heading>
        <div class="flex items-center gap-3">
            <flux:field class="mb-0">
                <flux:input 
                    type="date" 
                    wire:model.live="selectedDate"
                    class="w-auto"
                />
            </flux:field>
            <flux:button variant="ghost" size="sm" wire:click="refresh" icon="arrow-path">
                {{ __('Refresh') }}
            </flux:button>
        </div>
    </div>

    @if(count($absentEmployees) > 0 || count($lateEmployees) > 0)
        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
            <div class="relative">
                <div class="overflow-y-auto custom-scrollbar" style="max-height: 250px;">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Status') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Employee') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Department') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Group') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($absentEmployees as $employee)
                            <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <flux:badge color="red" size="sm">
                                        <flux:icon name="x-circle" class="w-3 h-3 mr-1" />
                                        {{ __('Absent') }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-zinc-900 dark:text-zinc-100 font-medium">
                                    {{ $employee['name'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                    {{ $employee['department'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                    {{ $employee['group'] }}
                                </td>
                            </tr>
                        @endforeach
                        @foreach($lateEmployees as $employee)
                            <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <flux:badge color="amber" size="sm">
                                        <flux:icon name="clock" class="w-3 h-3 mr-1" />
                                        {{ __('Late') }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-zinc-900 dark:text-zinc-100 font-medium">
                                    {{ $employee['name'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                    {{ $employee['department'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                    {{ $employee['group'] }}
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
            <flux:icon name="check-circle" class="w-12 h-12 mx-auto mb-4 text-green-500" />
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('All employees are present and on time!') }}
            </p>
        </div>
    @endif
</div>
