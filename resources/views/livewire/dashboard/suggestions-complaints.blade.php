<div>
    <!-- Card -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <flux:icon name="light-bulb" class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Suggestions / Complaints') }}</span>
                    </div>
                    @can('employees.manage.suggestions')
                        <a href="{{ route('employees.suggestions') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1">
                            {{ __('View All') }} <flux:icon name="arrow-right" class="w-4 h-4" />
                        </a>
                    @endcan
                </div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                    {{ __('Share Feedback') }}
                </flux:heading>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1 mb-4">
                    {{ __('Submit suggestions or raise complaints') }}
                </div>
            </div>
            <div class="flex items-center justify-end ml-4 pr-2">
                <flux:button variant="primary" icon="chat-bubble-left-right" wire:click="openFlyout">
                    {{ __('Share') }}
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Flyout -->
    <flux:modal variant="flyout" wire:model="showFlyout" class="max-w-2xl">
        <flux:heading size="lg" class="mb-2">{{ __('Suggestions / Complaints') }}</flux:heading>
        <flux:subheading class="mb-6">{{ __('Share your suggestions or raise a complaint') }}</flux:subheading>

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

        <form wire:submit="submit" class="space-y-6">
            <!-- Type Selection -->
            <div>
                <flux:label for="type" class="mb-2">{{ __('Type') }}</flux:label>
                <flux:select wire:model.live="type" id="type" placeholder="{{ __('Select type') }}">
                    <option value="suggestion">{{ __('Suggestion') }}</option>
                    <option value="complaint">{{ __('Complaint') }}</option>
                </flux:select>
                @error('type')
                    <flux:error class="mt-1">{{ $message }}</flux:error>
                @enderror
            </div>

            <!-- Complaint Type (only shown when type is complaint) -->
            @if($type === 'complaint')
                <div>
                    <flux:label for="complaintType" class="mb-2">{{ __('Complaint Type') }}</flux:label>
                    <flux:select wire:model="complaintType" id="complaintType" placeholder="{{ __('Select complaint type') }}">
                        <option value="">{{ __('Select complaint type') }}</option>
                        <option value="system_issues">{{ __('System Issues') }}</option>
                        <option value="attendance">{{ __('Attendance') }}</option>
                        <option value="leaves">{{ __('Leaves') }}</option>
                        <option value="employee">{{ __('Employee') }}</option>
                        <option value="payroll">{{ __('Payroll') }}</option>
                        <option value="work_environment">{{ __('Work Environment') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </flux:select>
                    @error('complaintType')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @enderror
                </div>
            @endif

            <!-- Message Textarea -->
            <div>
                <flux:label for="message" class="mb-2">
                    @if($type === 'suggestion')
                        {{ __('Your Suggestion') }}
                    @else
                        {{ __('Your Complaint') }}
                    @endif
                </flux:label>
                <flux:textarea 
                    wire:model="message" 
                    id="message" 
                    rows="6"
                    placeholder="{{ $type === 'suggestion' ? __('Please share your suggestion...') : __('Please describe your complaint...') }}"
                ></flux:textarea>
                @error('message')
                    <flux:error class="mt-1">{{ $message }}</flux:error>
                @enderror
                <flux:description class="mt-1">{{ __('Minimum 10 characters required') }}</flux:description>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button variant="ghost" wire:click="closeFlyout" type="button">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">
                        {{ __('Submit') }}
                    </span>
                    <span wire:loading wire:target="submit">
                        {{ __('Submitting...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
