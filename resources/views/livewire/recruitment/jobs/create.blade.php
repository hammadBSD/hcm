<section class="w-full">
    @include('partials.recruitment-heading')
    
    <x-recruitment.layout>
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <flux:heading size="lg">{{ $id ? __('Edit Job Post') : __('Create Job Post') }}</flux:heading>
                <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                    {{ $id ? __('Update the job posting details') : __('Fill in the details to create a new job posting') }}
                </flux:text>
            </div>

            <form wire:submit="save" class="p-6 space-y-6">
                <!-- Flash Messages -->
                @if (session()->has('message'))
                    <flux:callout variant="success" icon="check-circle" dismissible>
                        {{ session('message') }}
                    </flux:callout>
                @endif

                <!-- Basic Information Section -->
                <div class="space-y-4">
                    <flux:heading size="md" level="3" class="mb-4">{{ __('Basic Information') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Job Title') }} <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="jobTitle" placeholder="e.g., Senior Software Developer" required />
                            @error('jobTitle') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Designation') }}</flux:label>
                            <flux:select wire:model="designation">
                                <option value="">{{ __('Select Designation') }}</option>
                                @foreach($designations as $des)
                                    <option value="{{ $des['id'] }}">{{ $des['name'] }}</option>
                                @endforeach
                            </flux:select>
                            @error('designation') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Department') }}</flux:label>
                            <flux:select wire:model="department">
                                <option value="">{{ __('Select Department') }}</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept['id'] }}">{{ $dept['title'] }}</option>
                                @endforeach
                            </flux:select>
                            @error('department') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Number of Positions') }}</flux:label>
                            <flux:input type="number" wire:model="numberOfPositions" min="1" />
                            @error('numberOfPositions') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Line Manager') }}</flux:label>
                            <flux:select wire:model="lineManager">
                                <option value="">{{ __('Select Line Manager') }}</option>
                                @foreach($lineManagers as $manager)
                                    <option value="{{ $manager['id'] }}">{{ $manager['name'] }}</option>
                                @endforeach
                            </flux:select>
                            @error('lineManager') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>{{ __('Job Description') }} <span class="text-red-500">*</span></flux:label>
                        <flux:textarea wire:model="jobDescription" rows="6" placeholder="Describe the role, responsibilities, and requirements..." required />
                        @error('jobDescription') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>

                <!-- Requirements & Experience Section -->
                <div class="space-y-4 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:heading size="md" level="3" class="mb-4">{{ __('Requirements & Experience') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Entry Level') }}</flux:label>
                            <flux:select wire:model="entryLevel">
                                <option value="">{{ __('Select Entry Level') }}</option>
                                @foreach($entryLevelOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                            @error('entryLevel') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Candidate Experience') }}</flux:label>
                            <flux:input wire:model="candidateExperience" placeholder="e.g., 3-5 years" />
                            @error('candidateExperience') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Required Skills') }}</flux:label>
                            <flux:textarea wire:model="requiredSkills" rows="3" placeholder="List required skills, separated by commas or new lines" />
                            @error('requiredSkills') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>
                    </div>
                </div>

                <!-- Job Details Section -->
                <div class="space-y-4 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:heading size="md" level="3" class="mb-4">{{ __('Job Details') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Position Type') }}</flux:label>
                            <flux:select wire:model="position">
                                <option value="">{{ __('Select Position Type') }}</option>
                                @foreach($positionOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                            @error('position') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Work Type') }}</flux:label>
                            <flux:select wire:model="workType">
                                @foreach($workTypeOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                            @error('workType') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Location') }}</flux:label>
                            <flux:input wire:model="location" placeholder="e.g., New York, NY or Remote" />
                            @error('location') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Hiring Priority') }}</flux:label>
                            <flux:select wire:model="hiringPriority">
                                @foreach($hiringPriorityOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                            @error('hiringPriority') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Start Date') }}</flux:label>
                            <flux:input type="date" wire:model="startDate" />
                            @error('startDate') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Application Deadline') }}</flux:label>
                            <flux:input type="date" wire:model="applicationDeadline" />
                            @error('applicationDeadline') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>
                    </div>
                </div>

                <!-- Compensation & Benefits Section -->
                <div class="space-y-4 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:heading size="md" level="3" class="mb-4">{{ __('Compensation & Benefits') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Budget') }}</flux:label>
                            <flux:input type="number" wire:model="budget" placeholder="e.g., 50000" step="0.01" />
                            <flux:description>{{ __('Expected salary range or budget for this position') }}</flux:description>
                            @error('budget') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Benefits') }}</flux:label>
                            <flux:textarea wire:model="benefits" rows="3" placeholder="List benefits offered (health insurance, 401k, etc.)" />
                            @error('benefits') <flux:error>{{ $message }}</flux:error> @enderror
                        </flux:field>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" type="button" wire:click="$dispatch('cancel')">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" type="submit">
                        {{ $id ? __('Update Job Post') : __('Create Job Post') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </x-recruitment.layout>
</section>
