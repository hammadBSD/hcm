<section class="w-full">
@include('partials.employees-heading')
    <x-employees.layout :heading="__('Suggestions')" :subheading="__('View and manage employee suggestions and complaints')">
        <div class="space-y-6">
            <!-- Filters -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <flux:label for="filterType" class="mb-2">{{ __('Type') }}</flux:label>
                        <flux:select wire:model.live="filterType" id="filterType" placeholder="{{ __('All Types') }}">
                            <option value="">{{ __('All Types') }}</option>
                            <option value="suggestion">{{ __('Suggestion') }}</option>
                            <option value="complaint">{{ __('Complaint') }}</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:label for="filterStatus" class="mb-2">{{ __('Status') }}</flux:label>
                        <flux:select wire:model.live="filterStatus" id="filterStatus" placeholder="{{ __('All Statuses') }}">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="in_progress">{{ __('In Progress') }}</option>
                            <option value="resolved">{{ __('Resolved') }}</option>
                            <option value="dismissed">{{ __('Dismissed') }}</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:label for="filterMonth" class="mb-2">{{ __('Month') }}</flux:label>
                        <flux:select wire:model.live="filterMonth" id="filterMonth" placeholder="{{ __('All Months') }}">
                            <option value="">{{ __('All Months') }}</option>
                            @foreach($availableMonths as $month)
                                <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>

            <!-- Suggestions/Complaints Table -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Employee') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Type') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Message') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Last Updated By') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($suggestions as $suggestion)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $suggestion->created_at->format('M d, Y') }}<br>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $suggestion->created_at->format('h:i A') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $suggestion->employee->first_name }} {{ $suggestion->employee->last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $suggestion->type === 'suggestion' 
                                                    ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' 
                                                    : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' }}">
                                                {{ ucfirst($suggestion->type) }}
                                            </span>
                                            @if($suggestion->complaint_type)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                                                    {{ ucfirst(str_replace('_', ' ', $suggestion->complaint_type)) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400 max-w-md">
                                        <div class="line-clamp-2">{{ \Illuminate\Support\Str::limit($suggestion->message, 100) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $suggestion->status === 'pending' 
                                                ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'
                                                : ($suggestion->status === 'in_progress'
                                                ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
                                                : ($suggestion->status === 'resolved'
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                                : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300')) }}">
                                            {{ ucfirst(str_replace('_', ' ', $suggestion->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                        @if($suggestion->statusHistory->isNotEmpty())
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $suggestion->statusHistory->first()->changedBy->name ?? 'N/A' }}
                                                </div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $suggestion->statusHistory->first()->created_at->format('M d, Y h:i A') }}
                                                </div>
                                            </div>
                                        @elseif($suggestion->respondedBy)
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $suggestion->respondedBy->name }}
                                                </div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $suggestion->responded_at ? $suggestion->responded_at->format('M d, Y h:i A') : 'N/A' }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-zinc-400 dark:text-zinc-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @can('employees.manage.suggestions')
                                            <flux:button variant="ghost" size="sm" wire:click="openStatusFlyout({{ $suggestion->id }})" icon="pencil">
                                                {{ __('Change Status') }}
                                            </flux:button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <flux:icon name="inbox" class="w-12 h-12 text-zinc-400 dark:text-zinc-500 mx-auto mb-4" />
                                        <flux:heading size="sm" class="text-zinc-500 dark:text-zinc-400 mb-2">{{ __('No suggestions or complaints found') }}</flux:heading>
                                        <flux:text class="text-zinc-400 dark:text-zinc-500">{{ __('There are no suggestions or complaints matching your filters.') }}</flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($suggestions->hasPages())
                <div class="mt-4">
                    {{ $suggestions->links() }}
                </div>
            @endif
        </div>

        <!-- Status Change Flyout -->
        @if($selectedSuggestion)
            <flux:modal variant="flyout" wire:model="showStatusFlyout" class="max-w-2xl">
                <flux:heading size="lg" class="mb-2">{{ __('Change Status') }}</flux:heading>
                <flux:subheading class="mb-6">{{ __('Update the status and add notes for this suggestion/complaint') }}</flux:subheading>

                @if (session()->has('success'))
                    <flux:callout variant="success" icon="check-circle" class="mb-4">
                        {{ session('success') }}
                    </flux:callout>
                @endif

                @if (session()->has('error'))
                    <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">
                        {{ session('error') }}
                    </flux:callout>
                @endif

                <form wire:submit="updateStatus" class="space-y-6">
                    <!-- Current Info -->
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <flux:icon name="user" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Employee') }}:</span>
                                <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $selectedSuggestion->employee->first_name }} {{ $selectedSuggestion->employee->last_name }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:icon name="tag" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Type') }}:</span>
                                <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ ucfirst($selectedSuggestion->type) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:icon name="calendar" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Submitted') }}:</span>
                                <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $selectedSuggestion->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                            <p class="text-sm text-zinc-600 dark:text-zinc-300 whitespace-pre-wrap">{{ $selectedSuggestion->message }}</p>
                        </div>
                    </div>

                    <!-- Status Selection -->
                    <div>
                        <flux:label for="newStatus" class="mb-2">{{ __('Status') }}</flux:label>
                        <flux:select wire:model="newStatus" id="newStatus">
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="in_progress">{{ __('In Progress') }}</option>
                            <option value="resolved">{{ __('Resolved') }}</option>
                            <option value="dismissed">{{ __('Dismissed') }}</option>
                        </flux:select>
                        @error('newStatus')
                            <flux:error class="mt-1">{{ $message }}</flux:error>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <flux:label for="statusNotes" class="mb-2">{{ __('Notes') }}</flux:label>
                        <flux:textarea 
                            wire:model="statusNotes" 
                            id="statusNotes" 
                            rows="4"
                            placeholder="{{ __('Add notes about this status change...') }}"
                        ></flux:textarea>
                        @error('statusNotes')
                            <flux:error class="mt-1">{{ $message }}</flux:error>
                        @enderror
                        <flux:description class="mt-1">{{ __('Optional: Add notes to maintain history of status changes') }}</flux:description>
                    </div>

                    <!-- Status History -->
                    @if($selectedSuggestion->statusHistory->isNotEmpty())
                        <div>
                            <flux:label class="mb-2">{{ __('Status History') }}</flux:label>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @foreach($selectedSuggestion->statusHistory as $history)
                                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $history->status === 'pending' 
                                                    ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'
                                                    : ($history->status === 'in_progress'
                                                    ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
                                                    : ($history->status === 'resolved'
                                                    ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                                    : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                                            </span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $history->created_at->format('M d, Y h:i A') }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-zinc-600 dark:text-zinc-300 mb-1">
                                            {{ __('By') }} {{ $history->changedBy->name ?? 'N/A' }}
                                        </div>
                                        @if($history->notes)
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 whitespace-pre-wrap">{{ $history->notes }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button variant="ghost" wire:click="closeStatusFlyout" type="button">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="updateStatus">
                                {{ __('Update Status') }}
                            </span>
                            <span wire:loading wire:target="updateStatus">
                                {{ __('Updating...') }}
                            </span>
                        </flux:button>
                    </div>
                </form>
            </flux:modal>
        @endif
    </x-employees.layout>
</section>
