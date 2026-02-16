<section class="w-full">
    @include('partials.recruitment-heading')
    
    <x-recruitment.layout>
        <!-- Flash Messages -->
        @if (session()->has('message'))
            <flux:callout variant="success" icon="check-circle" dismissible class="mb-6">
                {{ session('message') }}
            </flux:callout>
        @endif

        <!-- Search and Filter Controls -->
        <div class="my-6 w-full space-y-4">
            <!-- Top Row: Search and Quick Filters -->
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <flux:input 
                        wire:model.live.debounce.300ms="search" 
                        :label="__('Search')" 
                        type="text" 
                        placeholder="Search by job title..." 
                        icon="magnifying-glass"
                    />
                </div>
                
                <!-- Status Filter -->
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Status') }}</flux:label>
                        <flux:select wire:model.live="selectedStatus">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="active">{{ __('Open') }}</option>
                            <option value="paused">{{ __('Paused') }}</option>
                            <option value="closed">{{ __('Closed') }}</option>
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Priority Filter -->
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Priority') }}</flux:label>
                        <flux:select wire:model.live="selectedPriority">
                            <option value="">{{ __('All Priorities') }}</option>
                            @foreach($priorityOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Toggle Filters Button -->
                <div class="flex items-end">
                    <flux:button 
                        variant="ghost" 
                        size="sm" 
                        :icon="$showFilters ? 'funnel' : 'funnel'" 
                        wire:click="$toggle('showFilters')"
                    >
                        {{ $showFilters ? __('Hide Filters') : __('More Filters') }}
                    </flux:button>
                </div>
            </div>

            <!-- Expanded Filters Section -->
            @if($showFilters)
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm" level="3">{{ __('Advanced Filters') }}</flux:heading>
                    <flux:button variant="ghost" size="xs" wire:click="clearFilters">
                        {{ __('Clear All') }}
                    </flux:button>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Department Filter -->
                    <flux:field>
                        <flux:label>{{ __('Department') }}</flux:label>
                        <flux:select wire:model.live="selectedDepartment">
                            <option value="">{{ __('All Departments') }}</option>
                            @foreach($departments as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <!-- Entry Level Filter -->
                    <flux:field>
                        <flux:label>{{ __('Entry Level') }}</flux:label>
                        <flux:select wire:model.live="selectedEntryLevel">
                            <option value="">{{ __('All Levels') }}</option>
                            @foreach($entryLevelOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <!-- Position Type Filter -->
                    <flux:field>
                        <flux:label>{{ __('Position Type') }}</flux:label>
                        <flux:select wire:model.live="selectedPositionType">
                            <option value="">{{ __('All Types') }}</option>
                            @foreach($positionTypeOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <!-- Work Type Filter -->
                    <flux:field>
                        <flux:label>{{ __('Work Type') }}</flux:label>
                        <flux:select wire:model.live="selectedWorkType">
                            <option value="">{{ __('All Work Types') }}</option>
                            @foreach($workTypeOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Active Filters Display -->
                @if($search || $selectedDepartment || $selectedStatus || $selectedEntryLevel || $selectedPositionType || $selectedWorkType || $selectedPriority)
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Active Filters:') }}</span>
                    @if($search)
                        <flux:badge color="blue" size="sm" dismissible wire:click="$set('search', '')">
                            {{ __('Search') }}: {{ $search }}
                        </flux:badge>
                    @endif
                    @if($selectedDepartment && isset($departments[$selectedDepartment]))
                        <flux:badge color="blue" size="sm" dismissible wire:click="$set('selectedDepartment', '')">
                            {{ __('Department') }}: {{ $departments[$selectedDepartment] }}
                        </flux:badge>
                    @endif
                    @if($selectedStatus)
                        <flux:badge color="blue" size="sm" dismissible wire:click="$set('selectedStatus', '')">
                            {{ __('Status') }}: {{ $selectedStatus === 'active' ? __('Open') : ucfirst($selectedStatus) }}
                        </flux:badge>
                    @endif
                    @if($selectedEntryLevel)
                        <flux:badge color="blue" size="sm" dismissible wire:click="$set('selectedEntryLevel', '')">
                            {{ __('Level') }}: {{ $entryLevelOptions[$selectedEntryLevel] ?? $selectedEntryLevel }}
                        </flux:badge>
                    @endif
                    @if($selectedPositionType)
                        <flux:badge color="blue" size="sm" dismissible wire:click="$set('selectedPositionType', '')">
                            {{ __('Position') }}: {{ $positionTypeOptions[$selectedPositionType] ?? $selectedPositionType }}
                        </flux:badge>
                    @endif
                    @if($selectedWorkType)
                        <flux:badge color="blue" size="sm" dismissible wire:click="$set('selectedWorkType', '')">
                            {{ __('Work Type') }}: {{ $workTypeOptions[$selectedWorkType] ?? $selectedWorkType }}
                        </flux:badge>
                    @endif
                    @if($selectedPriority)
                        <flux:badge color="blue" size="sm" dismissible wire:click="$set('selectedPriority', '')">
                            {{ __('Priority') }}: {{ $priorityOptions[$selectedPriority] ?? $selectedPriority }}
                        </flux:badge>
                    @endif
                </div>
                @endif
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:button variant="primary" icon="plus" href="{{ route('recruitment.jobs.create') }}" wire:navigate>
                        {{ __('Create Job Post') }}
                    </flux:button>
                </div>
                
                <!-- View Toggle and Additional Actions -->
                <div class="flex items-center gap-2">
                    <!-- View Mode Toggle -->
                    <div class="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-700 rounded-lg p-1">
                        <flux:button 
                            variant="{{ $viewMode === 'grid' ? 'primary' : 'ghost' }}" 
                            size="sm" 
                            icon="table-cells" 
                            wire:click="setViewMode('grid')"
                            class="min-w-0"
                        />
                        <flux:button 
                            variant="{{ $viewMode === 'kanban' ? 'primary' : 'ghost' }}" 
                            size="sm" 
                            icon="squares-2x2" 
                            wire:click="setViewMode('kanban')"
                            class="min-w-0"
                        />
                    </div>
                    <flux:button variant="ghost" size="sm" icon="arrow-path" wire:click="$refresh">
                        {{ __('Refresh') }}
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Grid View -->
        @if($viewMode === 'grid')
        <!-- Jobs Table -->
        <div class="mt-8">
            @if($jobs->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('title')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Job Title') }}
                                            @if($sortBy === 'title')
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
                                        <button wire:click="sort('entry_level')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Entry Level') }}
                                            @if($sortBy === 'entry_level')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('position_type')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Position Type') }}
                                            @if($sortBy === 'position_type')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('work_type')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Work Type') }}
                                            @if($sortBy === 'work_type')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <button wire:click="sort('hiring_priority')" class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-200">
                                            {{ __('Priority') }}
                                            @if($sortBy === 'hiring_priority')
                                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        {{ __('Applications') }}
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
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($jobs as $job)
                                    <tr 
                                        class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150 cursor-pointer"
                                        onclick="window.location.href='{{ route('recruitment.jobs.show', $job['id']) }}'"
                                    >
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $job['title'] }}
                                            </div>
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $job['number_of_positions'] }} {{ __('position(s)') }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $job['department'] }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $job['entry_level'] }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $job['position_type'] }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $job['work_type'] }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            @php
                                                $priorityColors = [
                                                    'Low' => 'gray',
                                                    'Medium' => 'blue',
                                                    'Urgent' => 'yellow',
                                                    'Very Urgent' => 'red',
                                                ];
                                                $color = $priorityColors[$job['hiring_priority']] ?? 'gray';
                                            @endphp
                                            <flux:badge color="{{ $color }}" size="sm">
                                                {{ $job['hiring_priority'] }}
                                            </flux:badge>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $job['applications_count'] }}
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap">
                                            @if($job['status'] === 'active')
                                                <flux:badge color="green" size="sm">
                                                    {{ __('Open') }}
                                                </flux:badge>
                                            @elseif($job['status'] === 'paused')
                                                <flux:badge color="yellow" size="sm">
                                                    {{ __('Paused') }}
                                                </flux:badge>
                                            @else
                                                <flux:badge color="gray" size="sm">
                                                    {{ __('Closed') }}
                                                </flux:badge>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-6 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation()">
                                            <div class="flex items-center gap-1">
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                    <flux:menu>
                                                        <flux:menu.item icon="pencil" href="{{ route('recruitment.jobs.edit', $job['id']) }}" wire:navigate>
                                                            {{ __('Edit') }}
                                                        </flux:menu.item>
                                                        <flux:menu.separator />
                                                        <flux:menu.heading>{{ __('Change Status') }}</flux:menu.heading>
                                                        <flux:menu.item 
                                                            icon="check-circle" 
                                                            wire:click="updateStatus({{ $job['id'] }}, 'active')"
                                                            wire:confirm="{{ __('Are you sure you want to change the status to Open?') }}"
                                                            :disabled="$job['status'] === 'active'"
                                                        >
                                                            {{ __('Open') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item 
                                                            icon="pause-circle" 
                                                            wire:click="updateStatus({{ $job['id'] }}, 'paused')"
                                                            wire:confirm="{{ __('Are you sure you want to pause this job post?') }}"
                                                            :disabled="$job['status'] === 'paused'"
                                                        >
                                                            {{ __('Paused') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item 
                                                            icon="x-circle" 
                                                            wire:click="updateStatus({{ $job['id'] }}, 'closed')"
                                                            wire:confirm="{{ __('Are you sure you want to close this job post?') }}"
                                                            :disabled="$job['status'] === 'closed'"
                                                        >
                                                            {{ __('Closed') }}
                                                        </flux:menu.item>
                                                        <flux:menu.separator />
                                                        <flux:menu.item 
                                                            icon="trash" 
                                                            variant="danger"
                                                            wire:click="deleteJobPost({{ $job['id'] }})"
                                                            wire:confirm="{{ __('Are you sure you want to delete this job post? This cannot be undone.') }}"
                                                        >
                                                            {{ __('Delete') }}
                                                        </flux:menu.item>
                                                    </flux:menu>
                                                </flux:dropdown>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        @if(isset($jobPosts) && method_exists($jobPosts, 'links'))
                            <div class="px-6 py-4">
                                {{ $jobPosts->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <flux:icon name="briefcase" class="w-16 h-16 mx-auto mb-4 text-zinc-400" />
                    <flux:heading size="lg" level="3" class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('No job posts found') }}
                    </flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                        {{ __('Get started by creating your first job post.') }}
                    </flux:text>
                    <div class="mt-6">
                        <flux:button variant="primary" icon="plus" href="{{ route('recruitment.jobs.create') }}" wire:navigate>
                            {{ __('Create Job Post') }}
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
        @else
        <!-- Kanban View -->
        <div class="mt-8">
            <div class="mb-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="p-6">
                    <div class="overflow-x-auto -mx-6 px-6" style="scrollbar-width: thin;">
                        <div class="flex gap-4 pb-4" style="min-width: max-content;">
                            <!-- Active Jobs Column -->
                            <div class="flex-shrink-0" style="width: 320px; min-width: 320px;">
                                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm w-full">
                                    <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-600">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                                <flux:heading size="sm" level="3" class="font-semibold text-zinc-900 dark:text-white">
                                                    {{ __('Open Jobs') }}
                                                </flux:heading>
                                                <flux:badge size="sm" color="gray" class="ml-1">
                                                    {{ $jobs->where('status', 'active')->count() }}
                                                </flux:badge>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3 flex-1 min-h-[400px] space-y-3 bg-zinc-50 dark:bg-zinc-900/50">
                                        @foreach($jobs->where('status', 'active') as $job)
                                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('recruitment.jobs.show', $job['id']) }}'">
                                                <div class="flex items-start justify-between mb-2">
                                                    <flux:heading size="sm" level="4" class="font-semibold text-zinc-900 dark:text-white pr-2">
                                                        {{ $job['title'] }}
                                                    </flux:heading>
                                                </div>
                                                <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                                    <div class="flex items-center gap-2">
                                                        <flux:icon name="building-office" class="w-4 h-4" />
                                                        <span>{{ $job['department'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <flux:icon name="user" class="w-4 h-4" />
                                                        <span>{{ $job['entry_level'] }}</span>
                                                    </div>
                                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-700">
                                                        <span class="text-xs">{{ $job['applications_count'] }} {{ __('applications') }}</span>
                                                        @php
                                                            $priorityColors = [
                                                                'Low' => 'gray',
                                                                'Medium' => 'blue',
                                                                'Urgent' => 'yellow',
                                                                'Very Urgent' => 'red',
                                                            ];
                                                            $color = $priorityColors[$job['hiring_priority']] ?? 'gray';
                                                        @endphp
                                                        <flux:badge color="{{ $color }}" size="xs">
                                                            {{ $job['hiring_priority'] }}
                                                        </flux:badge>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($jobs->where('status', 'active')->count() === 0)
                                            <div class="flex flex-col items-center justify-center h-full min-h-[300px] text-zinc-400 dark:text-zinc-500">
                                                <flux:icon name="briefcase" class="w-8 h-8 mb-2 opacity-50" />
                                                <p class="text-sm">{{ __('No open jobs') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Paused Jobs Column -->
                            <div class="flex-shrink-0" style="width: 320px; min-width: 320px;">
                                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm w-full">
                                    <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-600">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                                                <flux:heading size="sm" level="3" class="font-semibold text-zinc-900 dark:text-white">
                                                    {{ __('Paused') }}
                                                </flux:heading>
                                                <flux:badge size="sm" color="gray" class="ml-1">
                                                    {{ $jobs->where('status', 'paused')->count() }}
                                                </flux:badge>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3 flex-1 min-h-[400px] space-y-3 bg-zinc-50 dark:bg-zinc-900/50">
                                        @foreach($jobs->where('status', 'paused') as $job)
                                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('recruitment.jobs.show', $job['id']) }}'">
                                                <div class="flex items-start justify-between mb-2">
                                                    <flux:heading size="sm" level="4" class="font-semibold text-zinc-900 dark:text-white pr-2">
                                                        {{ $job['title'] }}
                                                    </flux:heading>
                                                </div>
                                                <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                                    <div class="flex items-center gap-2">
                                                        <flux:icon name="building-office" class="w-4 h-4" />
                                                        <span>{{ $job['department'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <flux:icon name="user" class="w-4 h-4" />
                                                        <span>{{ $job['entry_level'] }}</span>
                                                    </div>
                                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-700">
                                                        <span class="text-xs">{{ $job['applications_count'] }} {{ __('applications') }}</span>
                                                        @php
                                                            $priorityColors = [
                                                                'Low' => 'gray',
                                                                'Medium' => 'blue',
                                                                'Urgent' => 'yellow',
                                                                'Very Urgent' => 'red',
                                                            ];
                                                            $color = $priorityColors[$job['hiring_priority']] ?? 'gray';
                                                        @endphp
                                                        <flux:badge color="{{ $color }}" size="xs">
                                                            {{ $job['hiring_priority'] }}
                                                        </flux:badge>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($jobs->where('status', 'paused')->count() === 0)
                                            <div class="flex flex-col items-center justify-center h-full min-h-[300px] text-zinc-400 dark:text-zinc-500">
                                                <flux:icon name="briefcase" class="w-8 h-8 mb-2 opacity-50" />
                                                <p class="text-sm">{{ __('No paused jobs') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Closed Jobs Column -->
                            <div class="flex-shrink-0" style="width: 320px; min-width: 320px;">
                                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm w-full">
                                    <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-600">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 rounded-full bg-gray-500"></div>
                                                <flux:heading size="sm" level="3" class="font-semibold text-zinc-900 dark:text-white">
                                                    {{ __('Closed') }}
                                                </flux:heading>
                                                <flux:badge size="sm" color="gray" class="ml-1">
                                                    {{ $jobs->where('status', 'closed')->count() }}
                                                </flux:badge>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3 flex-1 min-h-[400px] space-y-3 bg-zinc-50 dark:bg-zinc-900/50">
                                        @foreach($jobs->where('status', 'closed') as $job)
                                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('recruitment.jobs.show', $job['id']) }}'">
                                                <div class="flex items-start justify-between mb-2">
                                                    <flux:heading size="sm" level="4" class="font-semibold text-zinc-900 dark:text-white pr-2">
                                                        {{ $job['title'] }}
                                                    </flux:heading>
                                                </div>
                                                <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                                    <div class="flex items-center gap-2">
                                                        <flux:icon name="building-office" class="w-4 h-4" />
                                                        <span>{{ $job['department'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <flux:icon name="user" class="w-4 h-4" />
                                                        <span>{{ $job['entry_level'] }}</span>
                                                    </div>
                                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-700">
                                                        <span class="text-xs">{{ $job['applications_count'] }} {{ __('applications') }}</span>
                                                        @php
                                                            $priorityColors = [
                                                                'Low' => 'gray',
                                                                'Medium' => 'blue',
                                                                'Urgent' => 'yellow',
                                                                'Very Urgent' => 'red',
                                                            ];
                                                            $color = $priorityColors[$job['hiring_priority']] ?? 'gray';
                                                        @endphp
                                                        <flux:badge color="{{ $color }}" size="xs">
                                                            {{ $job['hiring_priority'] }}
                                                        </flux:badge>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($jobs->where('status', 'closed')->count() === 0)
                                            <div class="flex flex-col items-center justify-center h-full min-h-[300px] text-zinc-400 dark:text-zinc-500">
                                                <flux:icon name="briefcase" class="w-8 h-8 mb-2 opacity-50" />
                                                <p class="text-sm">{{ __('No closed jobs') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </x-recruitment.layout>
</section>
