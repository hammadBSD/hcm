<div class="w-full">
    <!-- Header with Logo -->
    <div class="mb-8 pb-6 border-b border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center gap-3">
            <div class="flex aspect-square size-10 items-center justify-center rounded-md bg-zinc-900 dark:bg-white">
                <img src="{{ asset('bsd-logo-dark.svg') }}" alt="HCRM Logo" class="size-6" />
            </div>
            <div class="text-lg font-semibold text-zinc-900 dark:text-white">
                HCRM
            </div>
        </div>
    </div>

    <!-- Job Title and Description -->
    <div class="mb-8 mt-4">
        <flux:heading size="xl" level="1" class="mb-4">
            {{ $job['title'] ?? 'Job Application' }}
        </flux:heading>
        <flux:subheading size="lg" class="mb-6 text-zinc-600 dark:text-zinc-400">
            {{ $job['description'] ?? 'Please fill in all required fields to submit your application.' }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <!-- Success Message -->
    @if($showSuccessMessage)
        <div class="mb-6">
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>
                    {{ __('Application Submitted Successfully') }}
                </flux:callout.heading>
                <flux:callout.text>
                    {{ __('Thank you for your interest! We have received your application and will review it shortly.') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif

    <!-- Application Form -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="p-6 space-y-6">
            <div>
                <flux:heading size="lg" class="font-semibold">{{ __('Application Form') }}</flux:heading>
                <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                    {{ __('Please fill in all required fields to submit your application.') }}
                </flux:text>
            </div>

            <form wire:submit="submitApplication" class="space-y-6">
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
                    <flux:textarea wire:model="candidateDescription" placeholder="Write a description here" rows="6" class="resize-none" />
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

                <!-- Current Employment & Previous Companies -->
                <div class="space-y-4">
                    <!-- Current Employment (First Row - Always Visible) -->
                    @if(!empty($previousCompanies) && isset($previousCompanies[0]))
                        <div class="space-y-3">
                            <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-medium">{{ __('Current Employment') }}</flux:heading>
                            <div class="p-4 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <flux:field>
                                        <flux:label>{{ __('Company Name') }}</flux:label>
                                        <flux:input wire:model="previousCompanies.0.company" placeholder="Company name" />
                                        @error("previousCompanies.0.company") <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>{{ __('Position') }}</flux:label>
                                        <flux:input wire:model="previousCompanies.0.position" placeholder="Position held" />
                                        @error("previousCompanies.0.position") <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>{{ __('Duration') }}</flux:label>
                                        <flux:input wire:model="previousCompanies.0.duration" placeholder="e.g., 2 years" />
                                        @error("previousCompanies.0.duration") <flux:error>{{ $message }}</flux:error> @enderror
                                    </flux:field>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Previous Companies -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300 font-medium">{{ __('Previous Companies') }}</flux:heading>
                            <flux:button type="button" variant="ghost" size="sm" icon="plus" wire:click="addPreviousCompany">
                                {{ __('Add Company') }}
                            </flux:button>
                        </div>
                        
                        @if(count($previousCompanies) > 1)
                            @foreach($previousCompanies as $index => $company)
                                @if($index > 0)
                                    <div class="space-y-3 p-4 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                        <div class="flex items-center justify-between mb-2">
                                            <flux:heading size="xs" class="text-zinc-600 dark:text-zinc-400">{{ __('Previous Company') }} {{ $index }}</flux:heading>
                                            <flux:button type="button" variant="ghost" size="xs" icon="trash" wire:click="removePreviousCompany({{ $index }})" class="text-red-500 hover:text-red-600" />
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
                                @endif
                            @endforeach
                        @else
                            <div class="text-center py-4 text-zinc-400 dark:text-zinc-500 text-sm bg-zinc-50 dark:bg-zinc-900/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                {{ __('No previous companies added. Click "Add Company" to add one.') }}
                            </div>
                        @endif
                    </div>
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
                            <flux:label>{{ __('Current Salary') }}</flux:label>
                            <flux:input type="number" wire:model="candidateCurrentSalary" placeholder="e.g., 45000" min="0" step="0.01" />
                            @error('candidateCurrentSalary') <flux:error>{{ $message }}</flux:error> @enderror
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
                                    <flux:button type="button" variant="ghost" size="xs" icon="x-mark" wire:click="removeAttachment({{ $index }})" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button type="submit" variant="primary" icon="paper-airplane">
                        {{ __('Submit Application') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
