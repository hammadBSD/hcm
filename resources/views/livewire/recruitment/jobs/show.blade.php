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
                                    class="bg-zinc-50 dark:bg-zinc-800 p-3 min-h-[500px] space-y-3"
                                    ondrop="event.preventDefault(); @this.call('dropCard', {{ $stage['id'] }})"
                                    ondragover="event.preventDefault(); event.currentTarget.classList.add('bg-zinc-100', 'dark:bg-zinc-700')"
                                    ondragleave="event.currentTarget.classList.remove('bg-zinc-100', 'dark:bg-zinc-700')"
                                >
                                @if(isset($stage['cards']) && count($stage['cards']) > 0)
                                    @foreach($stage['cards'] as $card)
                                        <div 
                                            class="bg-white dark:bg-zinc-700 rounded-lg border border-zinc-200 dark:border-zinc-600 p-4 cursor-move hover:shadow-md transition-shadow"
                                            draggable="true"
                                            ondragstart="@this.call('dragStart', {{ $card['id'] }}, {{ $stage['id'] }}); event.dataTransfer.effectAllowed = 'move';"
                                            ondragend="@this.call('dragEnd')"
                                        >
                                            <div class="flex items-start justify-between mb-3">
                                                <flux:heading size="sm" level="4" class="font-semibold text-zinc-900 dark:text-white pr-2">
                                                    {{ $card['title'] ?? 'Card Title' }}
                                                </flux:heading>
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="xs" icon="ellipsis-horizontal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300" />
                                                    <flux:menu>
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
                                            <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-700">
                                                <div class="w-5 h-5 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                                                    <flux:icon name="user" class="w-3 h-3" />
                                                </div>
                                                <span class="font-medium">{{ $card['candidate_name'] ?? 'Candidate Name' }}</span>
                                            </div>
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
                                    <div class="flex flex-col items-center justify-center h-full min-h-[400px] text-zinc-400 dark:text-zinc-500">
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

                <!-- Full Name -->
                <flux:field>
                    <flux:label>{{ __('Full Name') }} <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="candidateName" placeholder="Add name here" />
                    @error('candidateName') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

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
    </x-recruitment.layout>
</section>
