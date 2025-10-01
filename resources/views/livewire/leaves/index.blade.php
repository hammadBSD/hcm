<section class="w-full">
    @include('partials.leaves-heading')

    <x-leaves.layout :heading="__('Leaves')" :subheading="__('Manage and approve employee leave requests')">
        <div class="space-y-6">
            <!-- Search and Filters -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Search -->
                    <div class="flex-1 min-w-[200px]">
                        <flux:input 
                            wire:model.live="search" 
                            placeholder="Search by employee name or ID..." 
                            icon="magnifying-glass"
                        />
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="w-32">
                        <flux:select wire:model.live="statusFilter" placeholder="All Status">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </flux:select>
                    </div>
                    
                    <!-- Leave Type Filter -->
                    <div class="w-32">
                        <flux:select wire:model.live="leaveTypeFilter" placeholder="All Types">
                            <option value="">All Types</option>
                            <option value="sick">Sick Leave</option>
                            <option value="personal">Personal Leave</option>
                            <option value="vacation">Vacation Leave</option>
                            <option value="emergency">Emergency Leave</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="paternity">Paternity Leave</option>
                        </flux:select>
                    </div>
                    
                    <!-- Clear Filters Button -->
                    <flux:button variant="outline" wire:click="resetFilters">
                        Clear Filters
                    </flux:button>
                </div>
            </div>

            {{-- Employee Leave Summary - Commented out from index page
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:icon name="calendar-days" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Leave Balance</span>
                    </div>
                    <div class="flex items-center gap-6 text-sm">
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Entitled</div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">3.2</div>
                        </div>
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Taken</div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">1</div>
                        </div>
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Pending</div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">0</div>
                        </div>
                        <div class="text-center">
                            <div class="text-zinc-500 dark:text-zinc-400">Balance</div>
                            <div class="font-bold text-green-600 dark:text-green-400">2.2</div>
                        </div>
                    </div>
                </div>
            </div>
            --}}

            <!-- Leave Requests Table -->
            <div class="mt-8">
                @if(count($leaveRequests) > 0)
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-zinc-50 dark:bg-zinc-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            <button wire:click="sort('employee_name')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                {{ __('Employee') }}
                                                @if($sortBy === 'employee_name')
                                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            <button wire:click="sort('department')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                {{ __('Department') }}
                                                @if($sortBy === 'department')
                                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            <button wire:click="sort('leave_type')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                {{ __('Leave Type') }}
                                                @if($sortBy === 'leave_type')
                                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            <button wire:click="sort('start_date')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                {{ __('Duration') }}
                                                @if($sortBy === 'start_date')
                                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            <button wire:click="sort('status')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                {{ __('Status') }}
                                                @if($sortBy === 'status')
                                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            <button wire:click="sort('created_at')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                                {{ __('Added On') }}
                                                @if($sortBy === 'created_at')
                                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($leaveRequests as $index => $request)
                                        <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <flux:avatar size="sm" :initials="strtoupper(substr($request['employee_name'], 0, 2))" />
                                                    <div>
                                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                            {{ $request['employee_name'] }}
                                                        </div>
                                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                            {{ $request['employee_id'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $request['department'] ?? 'Not assigned' }}
                                                </div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $request['position'] ?? 'No position' }}
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                <flux:badge color="blue" size="sm">
                                                    {{ $request['leave_type'] }}
                                                </flux:badge>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                                    {{ $request['total_days'] }} days
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $request['start_date'] }} - {{ $request['end_date'] }}
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                @php
                                                    $statusColor = match($request['status']) {
                                                        'pending' => 'yellow',
                                                        'approved' => 'green',
                                                        'rejected' => 'red',
                                                        'cancelled' => 'zinc',
                                                        default => 'yellow'
                                                    };
                                                @endphp
                                                <flux:badge color="{{ $statusColor }}" size="sm">
                                                    {{ ucfirst($request['status']) }}
                                                </flux:badge>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap">
                                                <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ date('M d, Y', strtotime($request['created_at'])) }}
                                                </div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ date('h:i A', strtotime($request['created_at'])) }}
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center gap-1">
                                                    <flux:button variant="ghost" size="sm" icon="eye" wire:click="viewRequest({{ $request['id'] }})" />
                                                    @if($request['status'] === 'pending')
                                                        <flux:button variant="ghost" size="sm" icon="check" wire:click="approveRequest({{ $request['id'] }})" />
                                                        <flux:button variant="ghost" size="sm" icon="x-mark" wire:click="rejectRequest({{ $request['id'] }})" />
                                                    @endif
                                                    <flux:button variant="ghost" size="sm" icon="pencil" wire:click="editRequest({{ $request['id'] }})" />
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <flux:icon name="document-text" class="mx-auto h-12 w-12 text-zinc-400" />
                        <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                            No leave requests found
                        </flux:heading>
                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                            Select criteria and press apply button to view leave requests.
                        </flux:text>
                    </div>
                @endif
            </div>
        </div>
    </x-leaves.layout>
</section>
