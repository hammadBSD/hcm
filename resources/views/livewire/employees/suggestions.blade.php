<section class="w-full">
@include('partials.employees-heading')
    <x-employees.layout :heading="__('Complaints')" :subheading="__('View and manage employee suggestions and complaints')">
        <div class="space-y-6">
            <!-- Filters -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <flux:label for="filterPriority" class="mb-2">{{ __('Priority') }}</flux:label>
                        <flux:select wire:model.live="filterPriority" id="filterPriority" placeholder="{{ __('All Priorities') }}">
                            <option value="">{{ __('All Priorities') }}</option>
                            <option value="low">{{ __('Low') }}</option>
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="high">{{ __('High') }}</option>
                            <option value="urgent">{{ __('Urgent') }}</option>
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Employee') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Type') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('For Dept') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Priority') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Note') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('Last Updated') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($suggestions as $suggestion)
                                <tr
                                    role="button"
                                    tabindex="0"
                                    class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30 cursor-pointer"
                                    wire:click="openStatusFlyout({{ $suggestion->id }})"
                                    wire:key="suggestion-row-{{ $suggestion->id }}"
                                >
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $suggestion->created_at->format('M d, Y') }}<br>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $suggestion->created_at->format('h:i A') }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                        <span class="inline-flex items-center gap-2 flex-wrap">
                                            {{ $suggestion->employee->first_name }} {{ $suggestion->employee->last_name }}
                                            @if($currentEmployee && (int) $suggestion->employee_id === (int) $currentEmployee->id)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">{{ __('Self') }}</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
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
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $suggestion->department?->title ?? __('-') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @php
                                            $priority = $suggestion->priority ?? 'medium';
                                            $priorityColor = match($priority) {
                                                'urgent' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                                'high' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
                                                'medium' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
                                                'low' => 'bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300',
                                                default => 'bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColor }}">
                                            {{ ucfirst($priority) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400 max-w-md">
                                        <div class="line-clamp-2">{{ \Illuminate\Support\Str::limit($suggestion->message, 100) }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
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
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center">
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
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Complaint type') }}:</span>
                                <span class="text-sm text-zinc-600 dark:text-zinc-300">
                                    @if($selectedSuggestion->complaint_type)
                                        {{ ucfirst(str_replace('_', ' ', $selectedSuggestion->complaint_type)) }}
                                    @else
                                        {{ ucfirst($selectedSuggestion->type) }}
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:icon name="calendar" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Submitted') }}:</span>
                                <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $selectedSuggestion->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:icon name="building-office" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Department') }}:</span>
                                <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $selectedSuggestion->department?->title ?? __('N/A') }}</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                            <p class="text-sm text-zinc-600 dark:text-zinc-300 whitespace-pre-wrap">{{ $selectedSuggestion->message }}</p>
                        </div>
                        @if($selectedSuggestion->status !== 'pending')
                        <!-- Resolve actions: show only when status is In Progress or Resolved. Both buttons always visible so purpose is clear. -->
                        <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-700 space-y-3">
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-2">{{ __('Resolution actions') }}</div>
                            <div class="flex flex-wrap gap-4">
                                <!-- Button 1: For person fixing the issue (department resolver) -->
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('For person fixing the issue (department)') }}</span>
                                    @if($selectedSuggestion->status === 'resolved' && $selectedSuggestion->responded_at)
                                        <span class="inline-flex items-center gap-1.5 text-sm text-green-600 dark:text-green-400">
                                            <flux:icon name="check-circle" class="w-4 h-4" />
                                            {{ __('Resolved') }} ({{ $selectedSuggestion->responded_at->format('M d, Y h:i A') }})
                                        </span>
                                    @elseif(!$isLodger && ($isResolver || $canResolveAny))
                                        <flux:button type="button" size="sm" variant="primary" wire:click="resolverResolve" icon="check-circle" class="!bg-green-600 hover:!bg-green-700">
                                            {{ __('Mark as Resolved') }}
                                        </flux:button>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-zinc-100 dark:bg-zinc-700/50 text-zinc-500 dark:text-zinc-400 text-sm cursor-not-allowed" title="{{ __('For department member resolving the issue') }}">
                                            <flux:icon name="check-circle" class="w-4 h-4" />
                                            {{ __('Mark as Resolved') }}
                                        </span>
                                    @endif
                                </div>
                                <!-- Button 2: For complaint lodger -->
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('For complaint lodger') }}</span>
                                    @if($selectedSuggestion->lodger_acknowledged_at)
                                        <span class="inline-flex items-center gap-1.5 text-sm text-green-600 dark:text-green-400">
                                            <flux:icon name="check-circle" class="w-4 h-4" />
                                            {{ __('Acknowledged') }} ({{ $selectedSuggestion->lodger_acknowledged_at->format('M d, Y h:i A') }})
                                        </span>
                                    @elseif($isLodger || $canAcknowledgeAny)
                                        <flux:button type="button" size="sm" variant="primary" wire:click="lodgerAcknowledge" icon="check-circle" class="!bg-green-600 hover:!bg-green-700">
                                            {{ __('I acknowledge resolution') }}
                                        </flux:button>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-zinc-100 dark:bg-zinc-700/50 text-zinc-500 dark:text-zinc-400 text-sm cursor-not-allowed" title="{{ __('For the person who lodged the complaint') }}">
                                            <flux:icon name="check-circle" class="w-4 h-4" />
                                            {{ __('I acknowledge resolution') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Status + Notes: show when user has resolve or acknowledge_resolution -->
                    @if($canChangeStatus)
                    <div>
                        <flux:label for="newStatus" class="mb-2">{{ __('Status') }}</flux:label>
                        @if($selectedSuggestion->status === 'resolved')
                            <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-sm font-medium">
                                <flux:icon name="check-circle" class="w-4 h-4" />
                                {{ __('Resolved') }}
                            </span>
                        @else
                            <flux:select wire:model="newStatus" id="newStatus">
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="in_progress">{{ __('In Progress') }}</option>
                                <option value="dismissed">{{ __('Dismissed') }}</option>
                            </flux:select>
                        @endif
                        @error('newStatus')
                            <flux:error class="mt-1">{{ $message }}</flux:error>
                        @enderror
                    </div>

                    @if($selectedSuggestion->status !== 'resolved')
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
                    @endif
                    @else
                    <!-- Read-only status when user cannot change status -->
                    <div>
                        <flux:label class="mb-2">{{ __('Status') }}</flux:label>
                        <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium
                            {{ $selectedSuggestion->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400' : ($selectedSuggestion->status === 'in_progress' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : ($selectedSuggestion->status === 'resolved' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300')) }}">
                            {{ ucfirst(str_replace('_', ' ', $selectedSuggestion->status)) }}
                        </span>
                    </div>
                    @endif

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
                                                    : ($history->status === 'lodger_acknowledged'
                                                    ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                                    : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300'))) }}">
                                                {{ $history->status === 'lodger_acknowledged' ? __('Lodger acknowledged') : ucfirst(str_replace('_', ' ', $history->status)) }}
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
                    <div class="flex items-center justify-between gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            @if($canShowEditButton)
                                <flux:button variant="ghost" size="sm" wire:click="startEdit" type="button" icon="pencil">
                                    {{ __('Edit') }}
                                </flux:button>
                            @endif
                            @if($canDelete)
                                <flux:button variant="danger" size="sm" type="button" icon="trash"
                                    wire:click="deleteSuggestion"
                                    wire:confirm="{{ __('Are you sure you want to delete this suggestion/complaint? This cannot be undone.') }}"
                                >
                                    {{ __('Delete') }}
                                </flux:button>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <flux:button variant="ghost" wire:click="closeStatusFlyout" type="button">
                                {{ __('Cancel') }}
                            </flux:button>
                            @if($canChangeStatus && $selectedSuggestion->status !== 'resolved')
                            <flux:button type="submit" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updateStatus">
                                    {{ __('Update Status') }}
                                </span>
                                <span wire:loading wire:target="updateStatus">
                                    {{ __('Updating...') }}
                                </span>
                            </flux:button>
                            @endif
                        </div>
                    </div>
                </form>
            </flux:modal>

        <!-- Edit complaint flyout (opens when Edit is clicked from status flyout) -->
        @if($showEditFlyout && $selectedSuggestionId)
            <flux:modal variant="flyout" wire:model="showEditFlyout" class="max-w-2xl">
                <flux:heading size="lg" class="mb-2">{{ __('Edit complaint') }}</flux:heading>
                <flux:subheading class="mb-6">{{ __('Update the complaint details') }}</flux:subheading>

                @if (session()->has('success'))
                    <flux:callout variant="success" icon="check-circle" class="mb-4">
                        {{ session('success') }}
                    </flux:callout>
                @endif

                <form wire:submit="saveEditComplaint" class="space-y-6">
                    <div>
                        <flux:label for="editMessage" class="mb-2">{{ __('Message') }} <span class="text-red-500">*</span></flux:label>
                        <flux:textarea wire:model="editMessage" id="editMessage" rows="4" placeholder="{{ __('Complaint message...') }}" />
                        @error('editMessage')
                            <flux:error class="mt-1">{{ $message }}</flux:error>
                        @enderror
                    </div>
                    <div>
                        <flux:label for="editPriority" class="mb-2">{{ __('Priority') }}</flux:label>
                        <flux:select wire:model="editPriority" id="editPriority">
                            <option value="low">{{ __('Low') }}</option>
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="high">{{ __('High') }}</option>
                            <option value="urgent">{{ __('Urgent') }}</option>
                        </flux:select>
                        @error('editPriority')
                            <flux:error class="mt-1">{{ $message }}</flux:error>
                        @enderror
                    </div>
                    <div>
                        <flux:label for="editDepartmentId" class="mb-2">{{ __('Department') }}</flux:label>
                        <flux:select wire:model="editDepartmentId" id="editDepartmentId" placeholder="{{ __('Select department') }}">
                            <option value="">{{ __('Select department') }}</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->title }}</option>
                            @endforeach
                        </flux:select>
                        @error('editDepartmentId')
                            <flux:error class="mt-1">{{ $message }}</flux:error>
                        @enderror
                    </div>
                    <div class="flex items-center justify-between gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <div></div>
                        <div class="flex items-center gap-3">
                            <flux:button type="button" variant="ghost" wire:click="closeEditFlyout">
                                {{ __('Cancel') }}
                            </flux:button>
                            <flux:button type="submit" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveEditComplaint">{{ __('Save changes') }}</span>
                                <span wire:loading wire:target="saveEditComplaint">{{ __('Saving...') }}</span>
                            </flux:button>
                        </div>
                    </div>
                </form>
            </flux:modal>
        @endif
        @endif
    </x-employees.layout>
</section>
