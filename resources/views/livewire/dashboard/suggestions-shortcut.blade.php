<div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
            {{ __('Suggestions & Complaints') }}
        </flux:heading>
        <div class="flex items-center gap-3 flex-wrap">
            <div class="min-w-[140px]">
                <flux:select
                    wire:model.live="selectedMonth"
                    placeholder="{{ __('Month') }}"
                    class="text-sm"
                >
                    @foreach($availableMonths as $month)
                        <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                    @endforeach
                </flux:select>
            </div>
            @if(auth()->user()->can('employees.manage.suggestions') || auth()->user()->can('complaints.view.all') || auth()->user()->can('complaints.view.own_department') || auth()->user()->can('complaints.view.self'))
                <a href="{{ route('employees.suggestions') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                    {{ __('View All') }}
                </a>
            @endif
            <flux:button variant="ghost" size="sm" wire:click="refresh" icon="arrow-path">
                {{ __('Refresh') }}
            </flux:button>
        </div>
    </div>

    @if($suggestions->isNotEmpty())
        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
            <div class="relative">
                <div class="overflow-y-auto custom-scrollbar" style="max-height: 250px;">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Date') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Priority') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Employee') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Department') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Status') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($suggestions as $suggestion)
                                @php
                                    $priority = $suggestion->priority ?? 'medium';
                                    $priorityColor = match($priority) {
                                        'urgent' => 'red',
                                        'high' => 'amber',
                                        'medium' => 'yellow',
                                        'low' => 'zinc',
                                        default => 'zinc',
                                    };
                                    $statusColor = match($suggestion->status) {
                                        'pending' => 'yellow',
                                        'in_progress' => 'blue',
                                        'resolved' => 'green',
                                        'dismissed' => 'zinc',
                                        default => 'zinc',
                                    };
                                    $employeeName = $suggestion->employee
                                        ? (optional($suggestion->employee->user)->name ?? trim($suggestion->employee->first_name . ' ' . $suggestion->employee->last_name) ?: __('N/A'))
                                        : __('N/A');
                                    $departmentName = $suggestion->department?->title ?? 'N/A';
                                @endphp
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $suggestion->created_at->format('M d, Y') }}<br>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $suggestion->created_at->format('h:i A') }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <flux:badge :color="$priorityColor" size="sm">
                                            {{ ucfirst($priority) }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-zinc-900 dark:text-zinc-100 font-medium">
                                        {{ $employeeName }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                        {{ $departmentName }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <flux:badge :color="$statusColor" size="sm">
                                            {{ ucfirst(str_replace('_', ' ', $suggestion->status)) }}
                                        </flux:badge>
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
            <flux:icon name="chat-bubble-left-right" class="w-12 h-12 mx-auto mb-4 text-zinc-400 dark:text-zinc-500" />
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('No suggestions or complaints yet.') }}
            </p>
        </div>
    @endif
</div>
