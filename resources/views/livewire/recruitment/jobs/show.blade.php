<section class="w-full max-w-full overflow-x-hidden">
    @include('partials.recruitment-heading')
    
    <x-recruitment.layout>
        <!-- Job Header -->
        <div class="mb-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <flux:heading size="xl" level="2">
                            {{ $job['title'] ?? 'Job Title' }}
                        </flux:heading>
                        @if($job && $job['status'] === 'active')
                            <flux:badge color="green" size="sm">
                                {{ __('Active') }}
                            </flux:badge>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-zinc-600 dark:text-zinc-400">
                        <div class="flex items-center gap-1">
                            <flux:icon name="building-office" class="w-4 h-4" />
                            <span>{{ $job['department'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:icon name="user" class="w-4 h-4" />
                            <span>{{ $job['entry_level'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:icon name="briefcase" class="w-4 h-4" />
                            <span>{{ $job['position_type'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:icon name="map-pin" class="w-4 h-4" />
                            <span>{{ $job['work_type'] ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button variant="outline" icon="arrow-left" href="{{ route('recruitment.index') }}" wire:navigate>
                        {{ __('Back to Jobs') }}
                    </flux:button>
                    <flux:button variant="ghost" size="sm" icon="pencil">
                        {{ __('Edit') }}
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Pipeline Selector and Actions -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <flux:field>
                    <flux:label>{{ __('Pipeline') }}</flux:label>
                    <flux:select wire:model.live="selectedPipelineId">
                        @foreach($pipelines as $pipeline)
                            <option value="{{ $pipeline['id'] }}">{{ $pipeline['name'] }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
            <div class="flex items-center gap-3">
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
                <flux:button variant="outline" icon="plus" wire:click="openAddPipelineModal">
                    {{ __('New Pipeline') }}
                </flux:button>
            </div>
        </div>

        <!-- Move Success Callout -->
        @if($showMoveCallout)
            <div 
                class="mb-6" 
                x-data="{ 
                    visible: true,
                    init() {
                        setTimeout(() => {
                            this.visible = false;
                            setTimeout(() => @this.call('dismissMoveCallout'), 300);
                        }, 3000);
                    }
                }" 
                x-show="visible" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <flux:callout color="cyan" icon="check-circle">
                    <flux:callout.heading>
                        {{ __('Card Moved Successfully') }}
                    </flux:callout.heading>
                    <flux:callout.text>
                        <strong>{{ $moveCalloutCardTitle }}</strong> {{ __('has been moved from') }} <strong>{{ $moveCalloutFromStage }}</strong> {{ __('to') }} <strong>{{ $moveCalloutToStage }}</strong>.
                    </flux:callout.text>
                    <x-slot name="controls">
                        <flux:button 
                            icon="x-mark" 
                            variant="ghost" 
                            wire:click="dismissMoveCallout"
                            x-on:click="visible = false; setTimeout(() => @this.call('dismissMoveCallout'), 300)"
                        />
                    </x-slot>
                </flux:callout>
            </div>
        @endif

        <!-- Grid View -->
        @if($viewMode === 'grid')
        <div class="mb-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                {{ __('Candidate') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                {{ __('Email') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                {{ __('Phone') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                {{ __('Position') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                {{ __('Experience') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                {{ __('Stage') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @if($this->allCandidates->count() > 0)
                            @foreach($this->allCandidates as $candidate)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6">
                                        <div class="flex items-center gap-3">
                                            <flux:avatar size="sm" :initials="substr($candidate['candidate_name'] ?? $candidate['title'] ?? 'N', 0, 2)" />
                                            <div class="space-y-1">
                                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $candidate['candidate_name'] ?? $candidate['title'] ?? 'N/A' }}
                                                </div>
                                                @if(isset($candidate['description']))
                                                    <div class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-1">
                                                        {{ $candidate['description'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $candidate['candidate_email'] ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $candidate['candidate_phone'] ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $candidate['candidate_position'] ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <div class="text-sm text-zinc-900 dark:text-zinc-100">
                                            @if(isset($candidate['candidate_experience']))
                                                {{ $candidate['candidate_experience'] }} {{ __('years') }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        @php
                                            $stageColors = [
                                                'Applied' => 'blue',
                                                'Screening' => 'yellow',
                                                'Interview' => 'purple',
                                                'Offer' => 'green',
                                                'Hired' => 'emerald',
                                            ];
                                            $stageName = $candidate['stage_name'] ?? 'Unknown';
                                            $color = $stageColors[$stageName] ?? 'gray';
                                        @endphp
                                        <flux:badge color="{{ $color }}" size="sm">
                                            {{ $stageName }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-1">
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye">
                                                        {{ __('View Details') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="pencil">
                                                        {{ __('Edit') }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="trash" variant="danger">
                                                        {{ __('Delete') }}
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <flux:icon name="user-group" class="w-12 h-12 mx-auto mb-4 text-zinc-400" />
                                        <flux:heading size="lg" level="3" class="mt-4 text-zinc-600 dark:text-zinc-400">
                                            {{ __('No candidates found') }}
                                        </flux:heading>
                                        <flux:text class="mt-2 text-zinc-500 dark:text-zinc-500">
                                            {{ __('Get started by adding candidates to the pipeline.') }}
                                        </flux:text>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <!-- Kanban Board -->
        <div class="mb-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden w-full max-w-full">
            <div class="p-6 overflow-x-auto" style="scrollbar-width: thin;">
                <div class="flex gap-4 pb-4" style="min-width: max-content;">
                @if($this->selectedPipeline && isset($this->selectedPipeline['stages']))
                    @foreach($this->selectedPipeline['stages'] as $stage)
                        <div class="flex-shrink-0" style="width: 280px; min-width: 280px;">
                            <!-- Stage Column Container -->
                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm w-full">
                                <!-- Stage Header -->
                                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-600">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            @php
                                                $colorClasses = [
                                                    'blue' => 'bg-blue-500',
                                                    'yellow' => 'bg-yellow-500',
                                                    'purple' => 'bg-purple-500',
                                                    'green' => 'bg-green-500',
                                                    'emerald' => 'bg-emerald-500',
                                                ];
                                                $colorClass = $colorClasses[$stage['color']] ?? 'bg-zinc-500';
                                            @endphp
                                            <div class="w-3 h-3 rounded-full {{ $colorClass }}"></div>
                                            <flux:heading size="sm" level="3" class="font-semibold">
                                                {{ $stage['name'] }}
                                            </flux:heading>
                                            <flux:badge size="sm" color="gray">
                                                {{ count($stage['cards'] ?? []) }}
                                            </flux:badge>
                                        </div>
                                        <flux:button 
                                            variant="ghost" 
                                            size="xs" 
                                            icon="plus"
                                            wire:click="openAddCardModal({{ $stage['id'] }})"
                                        >
                                        </flux:button>
                                    </div>
                                </div>

                                <!-- Stage Cards Container -->
                                <div 
                                    class="bg-zinc-50 dark:bg-zinc-800 p-3 space-y-3"
                                    ondrop="event.preventDefault(); @this.call('dropCard', {{ $stage['id'] }})"
                                    ondragover="event.preventDefault(); event.currentTarget.classList.add('bg-zinc-100', 'dark:bg-zinc-700')"
                                    ondragleave="event.currentTarget.classList.remove('bg-zinc-100', 'dark:bg-zinc-700')"
                                >
                                @if(isset($stage['cards']) && count($stage['cards']) > 0)
                                    @foreach($stage['cards'] as $card)
                                        <div 
                                            class="bg-white dark:bg-zinc-700 rounded-lg border border-zinc-200 dark:border-zinc-600 p-4 cursor-pointer hover:shadow-md transition-shadow"
                                            draggable="true"
                                            ondragstart="@this.call('dragStart', {{ $card['id'] }}, {{ $stage['id'] }}); event.dataTransfer.effectAllowed = 'move';"
                                            ondragend="@this.call('dragEnd')"
                                            onclick="event.stopPropagation(); @this.call('openCardDetail', {{ $card['id'] }}, {{ $stage['id'] }})"
                                        >
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                                            Applicant #{{ $card['applicant_number'] ?? $card['id'] ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <flux:heading size="sm" level="4" class="font-semibold text-zinc-900 dark:text-white pr-2">
                                                            {{ $card['candidate_name'] ?? $card['title'] ?? 'Card Title' }}
                                                        </flux:heading>
                                                        <!-- Hardcoded Badges for Testing -->
                                                        @if($loop->index === 0)
                                                            <!-- LinkedIn Badge -->
                                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#0077b5] border border-[#0077b5]">
                                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                                                </svg>
                                                                <span class="text-xs font-medium text-white">LinkedIn</span>
                                                            </div>
                                                        @elseif($loop->index === 1)
                                                            <!-- Glassdoor Badge -->
                                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#0CAA41] border border-[#0CAA41]">
                                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M17.144 20.572H3.43A3.43 3.43 0 0 1 0 17.14V6.43A3.43 3.43 0 0 1 3.429 3H17.14a3.43 3.43 0 0 1 3.429 3.43V17.14a3.43 3.43 0 0 1-3.43 3.43zM3.43 4.286a2.143 2.143 0 0 0-2.144 2.143V17.14a2.143 2.143 0 0 0 2.144 2.143H17.14a2.143 2.143 0 0 0 2.143-2.143V6.43a2.143 2.143 0 0 0-2.143-2.144z"/>
                                                                    <path d="M5.143 8.571h13.714v1.714H5.143zm0 3.429h13.714v1.714H5.143zm0 3.428h9.143v1.715H5.143z"/>
                                                                </svg>
                                                                <span class="text-xs font-medium text-white">Glassdoor</span>
                                                            </div>
                                                        @else
                                                            <!-- Indeed Badge -->
                                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#003A9B]/10 dark:bg-[#003A9B]/20 border border-[#003A9B]/30 dark:border-[#003A9B]/40">
                                                                <svg class="w-3 h-3 text-[#003A9B] dark:text-[#003A9B]" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M11.853 0C5.31 0 0 5.314 0 11.853c0 5.23 3.33 9.684 8.005 11.302.585.109.8-.254.8-.567 0-.28-.01-1.022-.015-2.005-3.233.703-3.917-1.56-3.917-1.56-.532-1.352-1.299-1.713-1.299-1.713-1.062-.726.08-.712.08-.712 1.175.083 1.793 1.206 1.793 1.206 1.044 1.79 2.74 1.273 3.404.974.106-.757.408-1.273.743-1.566-2.6-.295-5.332-1.3-5.332-5.79 0-1.28.457-2.326 1.206-3.145-.12-.295-.522-1.483.114-3.09 0 0 .984-.314 3.22 1.2.934-.26 1.937-.39 2.934-.394.997.004 2 .134 2.934.393 2.236-1.513 3.22-1.2 3.22-1.2.636 1.607.234 2.795.114 3.09.75.82 1.206 1.866 1.206 3.145 0 4.5-2.733 5.493-5.337 5.787.42.362.793 1.075.793 2.166 0 1.566-.014 2.826-.014 3.206 0 .315.214.68.805.566C20.67 21.537 24 17.083 24 11.853 24 5.314 18.69 0 11.853 0"/>
                                                                </svg>
                                                                <span class="text-xs font-medium text-[#003A9B] dark:text-[#003A9B]">Indeed</span>
                                                            </div>
                                                        @endif
                                                        @if(isset($card['candidate_source']) && $card['candidate_source'])
                                                            @php
                                                                $sourceIcons = [
                                                                    'linkedin' => 'briefcase',
                                                                    'glassdoor' => 'building-office',
                                                                    'indeed' => 'briefcase',
                                                                    'company-website' => 'globe-alt',
                                                                    'referral' => 'user-group',
                                                                    'job-board' => 'clipboard-document-list',
                                                                    'self-applied' => 'user',
                                                                    'self' => 'user',
                                                                    'recruitment-agency' => 'building-office-2',
                                                                    'other' => 'ellipsis-horizontal-circle'
                                                                ];
                                                                $sourceColors = [
                                                                    'linkedin' => 'blue',
                                                                    'glassdoor' => 'green',
                                                                    'indeed' => 'indigo',
                                                                    'company-website' => 'cyan',
                                                                    'referral' => 'purple',
                                                                    'job-board' => 'amber',
                                                                    'self-applied' => 'zinc',
                                                                    'self' => 'zinc',
                                                                    'recruitment-agency' => 'teal',
                                                                    'other' => 'gray'
                                                                ];
                                                                $icon = $sourceIcons[$card['candidate_source']] ?? 'ellipsis-horizontal-circle';
                                                                $color = $sourceColors[$card['candidate_source']] ?? 'zinc';
                                                                $sourceLabel = ucfirst(str_replace(['-', '_'], ' ', $card['candidate_source']));
                                                            @endphp
                                                            <flux:badge rounded size="sm" color="{{ $color }}" icon="{{ $icon }}">
                                                                {{ $sourceLabel }}
                                                            </flux:badge>
                                                        @endif
                                                    </div>
                                                </div>
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="xs" icon="ellipsis-horizontal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300" onclick="event.stopPropagation()" />
                                                    <flux:menu>
                                                        <flux:menu.item 
                                                            icon="eye"
                                                            wire:click.stop="openCardDetail({{ $card['id'] }}, {{ $stage['id'] }})"
                                                        >
                                                            {{ __('View Details') }}
                                                        </flux:menu.item>
                                                        <flux:menu.item icon="pencil">{{ __('Edit') }}</flux:menu.item>
                                                        <flux:menu.item icon="trash" variant="danger">{{ __('Delete') }}</flux:menu.item>
                                                    </flux:menu>
                                                </flux:dropdown>
                                            </div>
                                            @if(isset($card['description']))
                                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-3 line-clamp-2">
                                                    {{ $card['description'] }}
                                                </flux:text>
                                            @endif
                                        </div>
                                    @endforeach
                                    
                                    <!-- Add Card Button for columns with cards -->
                                    <div class="pt-2">
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            wire:click="openAddCardModal({{ $stage['id'] }})"
                                            class="w-full text-xs justify-center"
                                            icon="plus"
                                        >
                                            {{ __('Add Card') }}
                                        </flux:button>
                                    </div>
                                    
                                @else
                                    <!-- Empty State -->
                                    <div class="flex flex-col items-center justify-center py-8 text-zinc-400 dark:text-zinc-500">
                                        <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-3">
                                            <flux:icon name="document-plus" class="w-6 h-6 opacity-50" />
                                        </div>
                                        <p class="text-sm font-medium mb-2">{{ __('No cards') }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-600 mb-4">{{ __('Drag cards here or add a new one') }}</p>
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            wire:click="openAddCardModal({{ $stage['id'] }})"
                                            class="text-xs"
                                            icon="plus"
                                        >
                                            {{ __('Add Card') }}
                                        </flux:button>
                                    </div>
                                @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Add Pipeline Modal -->
        <flux:modal wire:model="showAddPipelineModal">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Create New Pipeline') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                        {{ __('Create a custom recruitment pipeline with your own stages.') }}
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>{{ __('Pipeline Name') }}</flux:label>
                    <flux:input wire:model="newPipelineName" placeholder="e.g., Software Engineering Pipeline" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" wire:click="closeAddPipelineModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="addPipeline">
                        {{ __('Create Pipeline') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <!-- Add Card Modal -->
        <flux:modal wire:model="showAddCardModal" size="4xl">
            <div class="space-y-6">
                <!-- Header -->
                <div>
                    <flux:heading size="lg" class="font-semibold">{{ __('Add new candidate') }}</flux:heading>
                </div>

                <!-- Name Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('First Name') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="candidateFirstName" placeholder="First name" />
                        @error('candidateFirstName') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Last Name') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="candidateLastName" placeholder="Last name" />
                        @error('candidateLastName') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>

                <!-- Description with formatting toolbar -->
                <flux:field>
                    <flux:label>{{ __('Description') }}</flux:label>
                    <div class="mb-2 flex items-center gap-2 flex-wrap">
                        <button type="button" class="p-2 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded transition-colors">
                            <flux:icon name="link" class="w-4 h-4" />
                        </button>
                        <button type="button" class="p-2 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded transition-colors">
                            <flux:icon name="map-pin" class="w-4 h-4" />
                        </button>
                        <button type="button" class="p-2 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded transition-colors">
                            <flux:icon name="photo" class="w-4 h-4" />
                        </button>
                        <button type="button" class="p-2 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded transition-colors">
                            <flux:icon name="code-bracket" class="w-4 h-4" />
                        </button>
                        <button type="button" class="p-2 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded transition-colors">
                            <flux:icon name="face-smile" class="w-4 h-4" />
                        </button>
                        <button type="button" class="p-2 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded transition-colors">
                            <flux:icon name="calendar" class="w-4 h-4" />
                        </button>
                        <button type="button" class="p-2 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded transition-colors">
                            <flux:icon name="arrow-down-tray" class="w-4 h-4" />
                        </button>
                        <button type="button" class="p-2 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded transition-colors ml-auto">
                            <flux:icon name="arrows-pointing-out" class="w-4 h-4" />
                        </button>
                    </div>
                    <flux:textarea wire:model="newCardDescription" placeholder="Write a description here" rows="6" class="resize-none" />
                </flux:field>

                <!-- Contact Information -->
                <div class="space-y-4">
                    <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-medium">{{ __('Contact Information') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Email') }} <span class="text-red-500">*</span></flux:label>
                            <flux:input type="email" wire:model="candidateEmail" placeholder="e.g., john.doe@example.com" />
                            @error('candidateEmail') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Phone') }}</flux:label>
                            <flux:input type="tel" wire:model="candidatePhone" placeholder="e.g., +1 234 567 8900" />
                            @error('candidatePhone') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>
                    </div>
                </div>

                <!-- Professional Details -->
                <div class="space-y-4">
                    <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-medium">{{ __('Professional Details') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Position') }}</flux:label>
                            <flux:select wire:model="candidatePosition">
                                <option value="">{{ __('Select Position') }}</option>
                                @foreach($positionOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                            @error('candidatePosition') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Designation') }}</flux:label>
                            <flux:select wire:model="candidateDesignation">
                                <option value="">{{ __('Select Designation') }}</option>
                                @foreach($designations as $des)
                                    <option value="{{ $des['id'] }}">{{ $des['name'] }}</option>
                                @endforeach
                            </flux:select>
                            @error('candidateDesignation') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Experience (Years)') }}</flux:label>
                            <flux:input type="number" wire:model="candidateExperience" placeholder="e.g., 5" min="0" max="50" step="0.5" />
                            @error('candidateExperience') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Date of Birth') }}</flux:label>
                            <flux:input type="date" wire:model="candidateDob" />
                            @error('candidateDob') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>
                    </div>
                </div>

                <!-- Location & Address -->
                <div class="space-y-4">
                    <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-medium">{{ __('Location & Address') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Current Address') }}</flux:label>
                            <flux:textarea wire:model="candidateCurrentAddress" placeholder="Enter current address" rows="2" />
                            @error('candidateCurrentAddress') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('City') }}</flux:label>
                            <flux:input wire:model="candidateCity" placeholder="e.g., New York" />
                            @error('candidateCity') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Country') }}</flux:label>
                            <flux:input wire:model="candidateCountry" placeholder="e.g., United States" />
                            @error('candidateCountry') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>
                    </div>
                </div>

                <!-- Current Employment -->
                <div class="space-y-4">
                    <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-medium">{{ __('Current Employment') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Current Company Name') }}</flux:label>
                            <flux:input wire:model="candidateCurrentCompany" placeholder="e.g., Tech Corp Inc." />
                            @error('candidateCurrentCompany') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Notice Period (Days)') }}</flux:label>
                            <flux:input type="number" wire:model="candidateNoticePeriod" placeholder="e.g., 30" min="0" max="365" />
                            @error('candidateNoticePeriod') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>
                    </div>
                </div>

                <!-- Previous Companies -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-medium">{{ __('Previous Companies') }}</flux:heading>
                        <flux:button variant="ghost" size="sm" icon="plus" wire:click="addPreviousCompany">
                            {{ __('Add Company') }}
                        </flux:button>
                    </div>
                    
                    @if(!empty($previousCompanies))
                        @foreach($previousCompanies as $index => $company)
                            <div class="space-y-3 p-4 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center justify-between mb-2">
                                    <flux:heading size="xs" class="text-zinc-600 dark:text-zinc-400">Company {{ $index + 1 }}</flux:heading>
                                    <flux:button variant="ghost" size="xs" icon="trash" wire:click="removePreviousCompany({{ $index }})" class="text-red-500 hover:text-red-600" />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <flux:field>
                                        <flux:label>{{ __('Company Name') }}</flux:label>
                                        <flux:input wire:model="previousCompanies.{{ $index }}.company" placeholder="Company name" />
                                        @error("previousCompanies.{$index}.company") <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>{{ __('Position') }}</flux:label>
                                        <flux:input wire:model="previousCompanies.{{ $index }}.position" placeholder="Position held" />
                                        @error("previousCompanies.{$index}.position") <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>{{ __('Duration') }}</flux:label>
                                        <flux:input wire:model="previousCompanies.{{ $index }}.duration" placeholder="e.g., 2 years" />
                                        @error("previousCompanies.{$index}.duration") <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8 text-zinc-400 dark:text-zinc-500 text-sm">
                            {{ __('No previous companies added. Click "Add Company" to add one.') }}
                        </div>
                    @endif
                </div>

                <!-- Additional Information -->
                <div class="space-y-4">
                    <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-medium">{{ __('Additional Information') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Source') }}</flux:label>
                            <flux:select wire:model="candidateSource">
                                <option value="">{{ __('Select Source') }}</option>
                                @foreach($sourceOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                            @error('candidateSource') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('LinkedIn Profile') }}</flux:label>
                            <flux:input type="url" wire:model="candidateLinkedIn" placeholder="https://linkedin.com/in/..." />
                            @error('candidateLinkedIn') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Expected Salary') }}</flux:label>
                            <flux:input type="number" wire:model="candidateExpectedSalary" placeholder="e.g., 50000" min="0" step="0.01" />
                            @error('candidateExpectedSalary') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Availability Date') }}</flux:label>
                            <flux:input type="date" wire:model="candidateAvailabilityDate" />
                            @error('candidateAvailabilityDate') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="space-y-4">
                    <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-medium">{{ __('Attachments') }}</flux:heading>
                    
                    <flux:field>
                        <flux:label>{{ __('Upload Files') }}</flux:label>
                        <flux:input type="file" wire:model="candidateAttachments" multiple />
                        <flux:description>{{ __('Maximum 20MB per file. Accepted file types: PDF, DOC, DOCX, images, etc.') }}</flux:description>
                        @error('candidateAttachments.*') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    @if(!empty($candidateAttachments))
                        <div class="space-y-2">
                            @foreach($candidateAttachments as $index => $attachment)
                                <div class="flex items-center justify-between p-2 bg-zinc-50 dark:bg-zinc-900/50 rounded border border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="document" class="w-4 h-4 text-zinc-500" />
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $attachment->getClientOriginalName() }}</span>
                                        <span class="text-xs text-zinc-500">({{ number_format($attachment->getSize() / 1024, 2) }} KB)</span>
                                    </div>
                                    <flux:button variant="ghost" size="xs" icon="x-mark" wire:click="removeAttachment({{ $index }})" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" wire:click="closeAddCardModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="addCard" icon="plus">
                        {{ __('Add new candidate') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <!-- Card Detail Modal (Native Dialog with Trello Style Layout) -->
        <dialog 
            id="cardDetailModal" 
            class="modal"
            x-data="{ 
                show: @entangle('showCardDetailModal'),
                init() {
                    this.$watch('show', value => {
                        const modal = this.$el;
                        if (value) {
                            modal.showModal();
                        } else {
                            modal.close();
                        }
                    });
                    // Initial state
                    if (this.show) {
                        this.$el.showModal();
                    }
                }
            }"
            wire:ignore.self
        >
            <style>
                #cardDetailModal::backdrop {
                    background-color: rgba(17, 24, 39, 0.8);
                    backdrop-filter: blur(8px);
                }
                .dark #cardDetailModal::backdrop {
                    background-color: rgba(0, 0, 0, 0.7);
                    backdrop-filter: blur(8px);
                }
            </style>
            <div class="modal-box w-[100%] max-w-[1200px] p-0 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                @if($selectedCard)
                <div class="space-y-0">
                    <!-- Header -->
                    <div class="flex items-start justify-between px-6 pt-6 pb-4 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex-1 flex items-start gap-4">
                        <div class="flex items-center gap-2">
                            <flux:field class="mb-0">
                                <flux:select wire:model.live="selectedCardStageId" class="w-40 text-sm">
                                    @if($this->selectedPipeline && isset($this->selectedPipeline['stages']))
                                        @foreach($this->selectedPipeline['stages'] as $stage)
                                            <option value="{{ $stage['id'] }}">{{ $stage['name'] }}</option>
                                        @endforeach
                                    @endif
                                </flux:select>
                            </flux:field>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                    Applicant #{{ $selectedCard['applicant_number'] ?? $selectedCard['id'] ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:heading size="xl" level="2" class="mb-0 font-semibold">
                                    {{ $selectedCard['candidate_name'] ?? $selectedCard['title'] ?? 'Candidate' }}
                                </flux:heading>
                                <!-- Hardcoded LinkedIn Badge for Testing -->
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0077b5] border border-[#0077b5]">
                                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                    <span class="px-1 text-xs font-medium text-white">LinkedIn</span>
                                </div>
                                @if(isset($selectedCard['candidate_source']) && $selectedCard['candidate_source'])
                                    @php
                                        $sourceIcons = [
                                            'linkedin' => 'briefcase',
                                            'glassdoor' => 'building-office',
                                            'indeed' => 'briefcase',
                                            'company-website' => 'globe-alt',
                                            'referral' => 'user-group',
                                            'job-board' => 'clipboard-document-list',
                                            'self-applied' => 'user',
                                            'recruitment-agency' => 'building-office-2',
                                            'other' => 'ellipsis-horizontal-circle'
                                        ];
                                        $sourceColors = [
                                            'linkedin' => 'blue',
                                            'glassdoor' => 'green',
                                            'indeed' => 'indigo',
                                            'company-website' => 'cyan',
                                            'referral' => 'purple',
                                            'job-board' => 'amber',
                                            'self-applied' => 'zinc',
                                            'recruitment-agency' => 'teal',
                                            'other' => 'gray'
                                        ];
                                        $icon = $sourceIcons[$selectedCard['candidate_source']] ?? 'ellipsis-horizontal-circle';
                                        $color = $sourceColors[$selectedCard['candidate_source']] ?? 'zinc';
                                    @endphp
                                    <flux:badge rounded size="sm" color="{{ $color }}" icon="{{ $icon }}">
                                        {{ ucfirst(str_replace(['-', '_'], ' ', $selectedCard['candidate_source'])) }}
                                    </flux:badge>
                                @endif
                            </div>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        wire:click="closeCardDetail"
                        class="text-zinc-400 bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors"
                    >
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <!-- Content -->
                <div class="overflow-y-auto px-6 py-6" style="max-height: calc(90vh - 100px);">
                    <div class="flex gap-6">
                        <!-- Main Content (Left - 1/3 width) -->
                        <div class="w-80 flex-shrink-0 space-y-6">
                            <!-- Basic Information -->
                            <div class="space-y-2">
                                <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-semibold">
                                    {{ __('Basic Information') }}
                                </flux:heading>
                                <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-zinc-200 dark:border-zinc-700">
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('First Name') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">{{ $selectedCard['candidate_first_name'] ?? ($selectedCard['candidate_name'] ? explode(' ', $selectedCard['candidate_name'])[0] : '-') }}</span>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Last Name') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">{{ $selectedCard['candidate_last_name'] ?? ($selectedCard['candidate_name'] ? (explode(' ', $selectedCard['candidate_name'])[1] ?? '-') : '-') }}</span>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Date of Birth') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">
                                                @if(isset($selectedCard['candidate_dob']) && $selectedCard['candidate_dob'])
                                                    {{ \Carbon\Carbon::parse($selectedCard['candidate_dob'])->format('M d, Y') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-semibold">
                                        {{ __('Description') }}
                                    </flux:heading>
                                    <flux:button variant="ghost" size="xs" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
                                        {{ __('Edit') }}
                                    </flux:button>
                                </div>
                                <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 min-h-[80px] border border-zinc-200 dark:border-zinc-700">
                                    @if(isset($selectedCard['description']) && $selectedCard['description'])
                                        @php
                                            $description = $selectedCard['description'];
                                            $maxCharsPerLine = 60;
                                            
                                            // Break text into lines of exactly 60 characters
                                            $lines = [];
                                            $currentLine = '';
                                            $words = explode(' ', $description);
                                            
                                            foreach ($words as $word) {
                                                $testLine = $currentLine ? $currentLine . ' ' . $word : $word;
                                                $testLength = mb_strlen($testLine);
                                                
                                                if ($testLength <= $maxCharsPerLine) {
                                                    $currentLine = $testLine;
                                                } else {
                                                    if ($currentLine) {
                                                        $lines[] = $currentLine;
                                                    }
                                                    // If a single word is longer than maxCharsPerLine, break it
                                                    if (mb_strlen($word) > $maxCharsPerLine) {
                                                        $wordChunks = mb_str_split($word, $maxCharsPerLine);
                                                        foreach ($wordChunks as $i => $chunk) {
                                                            if ($i === 0) {
                                                                $currentLine = $chunk;
                                                            } else {
                                                                $lines[] = $currentLine;
                                                                $currentLine = $chunk;
                                                            }
                                                        }
                                                    } else {
                                                        $currentLine = $word;
                                                    }
                                                }
                                            }
                                            if ($currentLine) {
                                                $lines[] = $currentLine;
                                            }
                                            
                                            $formattedDescription = trim(implode("\n", $lines));
                                        @endphp
                                        <div class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed" style="font-family: 'Courier New', monospace; white-space: pre-line; word-break: break-word; overflow-wrap: break-word; max-width: 70ch; width: 100%;">{{ $formattedDescription }}</div>
                                    @else
                                        <p class="text-sm text-zinc-400 dark:text-zinc-500 italic">
                                            {{ __('No description added.') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="space-y-2">
                                <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-semibold">
                                    {{ __('Contact Information') }}
                                </flux:heading>
                                <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-zinc-200 dark:border-zinc-700">
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Email') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">{{ $selectedCard['candidate_email'] ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">{{ $selectedCard['candidate_phone'] ?? '-' }}</span>
                                        </div>
                                        <div class="col-span-2">
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('LinkedIn') }}:</span>
                                            @if(isset($selectedCard['candidate_linkedin']) && $selectedCard['candidate_linkedin'])
                                                <a href="{{ $selectedCard['candidate_linkedin'] }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline ml-2">
                                                    {{ __('View Profile') }}
                                                </a>
                                            @else
                                                <span class="text-zinc-900 dark:text-zinc-100 ml-2">-</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Details -->
                            <div class="space-y-2">
                                <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-semibold">
                                    {{ __('Professional Details') }}
                                </flux:heading>
                                <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-zinc-200 dark:border-zinc-700">
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Position') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">{{ $selectedCard['candidate_position'] ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Designation') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">{{ $selectedCard['candidate_designation'] ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Experience') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">
                                                @if(isset($selectedCard['candidate_experience']))
                                                    {{ $selectedCard['candidate_experience'] }} {{ __('years') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Source') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">{{ $selectedCard['candidate_source'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location & Address -->
                            <div class="space-y-2">
                                <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-semibold">
                                    {{ __('Location & Address') }}
                                </flux:heading>
                                <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-zinc-200 dark:border-zinc-700">
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div class="col-span-2">
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Current Address') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">{{ $selectedCard['candidate_current_address'] ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('City') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">{{ $selectedCard['candidate_city'] ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Country') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">{{ $selectedCard['candidate_country'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Employment -->
                            <div class="space-y-2">
                                <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-semibold">
                                    {{ __('Current Employment') }}
                                </flux:heading>
                                <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-zinc-200 dark:border-zinc-700">
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Current Company') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">{{ $selectedCard['candidate_current_company'] ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Notice Period') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">
                                                @if(isset($selectedCard['candidate_notice_period']) && $selectedCard['candidate_notice_period'])
                                                    {{ $selectedCard['candidate_notice_period'] }} {{ __('days') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Previous Companies -->
                            <div class="space-y-3">
                                <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-medium">
                                    {{ __('Previous Companies') }}
                                </flux:heading>
                                @if(isset($selectedCard['candidate_previous_companies']) && !empty($selectedCard['candidate_previous_companies']))
                                    <div class="space-y-3">
                                        @foreach($selectedCard['candidate_previous_companies'] as $index => $company)
                                            <div class="bg-zinc-50 dark:bg-zinc-900/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                                <div class="grid grid-cols-3 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Company') }}</label>
                                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $company['company'] ?? '-' }}</p>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Position') }}</label>
                                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $company['position'] ?? '-' }}</p>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Duration') }}</label>
                                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $company['duration'] ?? '-' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-400 dark:text-zinc-500 italic">
                                        {{ __('No previous companies added.') }}
                                    </div>
                                @endif
                            </div>

                            <!-- Additional Information -->
                            <div class="space-y-2">
                                <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-semibold">
                                    {{ __('Additional Information') }}
                                </flux:heading>
                                <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-zinc-200 dark:border-zinc-700">
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Expected Salary') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">
                                                @if(isset($selectedCard['candidate_expected_salary']) && $selectedCard['candidate_expected_salary'])
                                                    ${{ number_format($selectedCard['candidate_expected_salary'], 0) }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Availability Date') }}:</span>
                                            <span class="text-zinc-900 dark:text-zinc-100 ml-2">
                                                @if(isset($selectedCard['candidate_availability_date']) && $selectedCard['candidate_availability_date'])
                                                    {{ \Carbon\Carbon::parse($selectedCard['candidate_availability_date'])->format('M d, Y') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Attachments -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-semibold">
                                        {{ __('Attachments') }}
                                    </flux:heading>
                                    <flux:button variant="ghost" size="xs" icon="plus" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
                                        {{ __('Add') }}
                                    </flux:button>
                                </div>
                                @if(isset($selectedCard['candidate_attachments']) && !empty($selectedCard['candidate_attachments']))
                                    <div class="space-y-2">
                                        @foreach($selectedCard['candidate_attachments'] as $attachment)
                                            <div class="flex items-center justify-between p-2 bg-white dark:bg-zinc-800 rounded border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                                <div class="flex items-center gap-2">
                                                    <flux:icon name="document" class="w-4 h-4 text-zinc-500" />
                                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ basename($attachment) }}</span>
                                                </div>
                                                <flux:button variant="ghost" size="xs" icon="arrow-down-tray" class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">
                                                    {{ __('Download') }}
                                                </flux:button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-400 dark:text-zinc-500 italic">
                                        {{ __('No attachments') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Sidebar (Right - 2/3 width) -->
                        <div class="flex-1 min-w-0 space-y-6 border-l border-zinc-200 dark:border-zinc-700 pl-6" style="padding-left: 1.5rem;">
                            <!-- Actions -->
                            <div class="space-y-2">
                                <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-semibold mb-2">
                                    {{ __('Add to card') }}
                                </flux:heading>
                                <div class="flex flex-wrap gap-2">
                                    <flux:badge as="button" rounded icon="user-plus" size="sm" color="zinc" class="cursor-pointer">
                                        {{ __('Members') }}
                                    </flux:badge>
                                    <flux:badge as="button" rounded icon="tag" size="sm" color="zinc" class="cursor-pointer">
                                        {{ __('Labels') }}
                                    </flux:badge>
                                    <flux:badge as="button" rounded icon="calendar" size="sm" color="zinc" class="cursor-pointer">
                                        {{ __('Dates') }}
                                    </flux:badge>
                                    <flux:badge as="button" rounded icon="check-circle" size="sm" color="zinc" class="cursor-pointer">
                                        {{ __('Checklist') }}
                                    </flux:badge>
                                    <flux:badge as="button" rounded icon="paper-clip" size="sm" color="zinc" class="cursor-pointer">
                                        {{ __('Attachment') }}
                                    </flux:badge>
                                </div>
                            </div>

                            <!-- Comments and Activity -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-semibold">
                                        {{ __('Comments and activity') }}
                                    </flux:heading>
                                    <flux:button variant="ghost" size="xs" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
                                        {{ __('Show details') }}
                                    </flux:button>
                                </div>
                                
                                <!-- Comment Input -->
                                <div class="space-y-2">
                                    <div class="flex items-start gap-2">
                                        @php
                                            $userInitials = substr(auth()->user()->name ?? 'U', 0, 2);
                                        @endphp
                                        <flux:avatar size="sm" :initials="$userInitials" />
                                        <div class="flex-1">
                                            <flux:field>
                                                <flux:textarea 
                                                    rows="2" 
                                                    placeholder="{{ __('Write a comment...') }}"
                                                    class="text-sm"
                                                />
                                            </flux:field>
                                        </div>
                                    </div>
                                </div>

                                <!-- Activity Feed -->
                                <div class="space-y-3 mt-4">
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                                        {{ __('Activity') }}
                                    </div>
                                    <div class="space-y-3 text-sm">
                                        <div class="flex items-start gap-2">
                                            <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0 mt-0.5">
                                                <span class="text-xs font-medium text-blue-700 dark:text-blue-300">JD</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-zinc-900 dark:text-zinc-100">
                                                    <span class="font-medium">John Doe</span> was added to <span class="font-medium">Applied</span>
                                                </p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                    {{ __('Just now') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                @endif
            </div>
            <form method="dialog" class="modal-backdrop" wire:click="closeCardDetail">
                <button type="button"></button>
            </form>
        </dialog>

    </x-recruitment.layout>
</section>
