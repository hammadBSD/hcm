<div>
    @if (session()->has('success'))
        <div
            x-data="{ visible: true }"
            x-init="setTimeout(() => visible = false, 2000)"
            x-show="visible"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-4"
        >
            <flux:callout variant="success" icon="check-circle" dismissible>
                {{ session('success') }}
            </flux:callout>
        </div>
    @endif

    <!-- Card -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <flux:icon name="light-bulb" class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Suggestions / Complaints') }}</span>
                    </div>
                    @if(auth()->user()->can('employees.manage.suggestions') || auth()->user()->can('complaints.view.all') || auth()->user()->can('complaints.view.own_department') || auth()->user()->can('complaints.view.self'))
                        <a href="{{ route('employees.suggestions') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1">
                            {{ __('View') }} <flux:icon name="arrow-right" class="w-4 h-4" />
                        </a>
                    @endif
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
                    {{ __('Complain') }}
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
            <input type="hidden" wire:model="type" value="complaint" />

            <!-- Department -->
            <div>
                <flux:label for="departmentId" class="mb-2">{{ __('Department') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="departmentId" id="departmentId" placeholder="{{ __('Select department') }}">
                    <option value="">{{ __('Select department') }}</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept['id'] }}">{{ $dept['label'] }}</option>
                    @endforeach
                </flux:select>
                @error('departmentId')
                    <flux:error class="mt-1">{{ $message }}</flux:error>
                @enderror
            </div>

            <!-- Priority -->
            <div>
                <flux:label for="priority" class="mb-2">{{ __('Priority') }}</flux:label>
                <flux:select wire:model="priority" id="priority" placeholder="{{ __('Select priority') }}">
                    <option value="low">{{ __('Low') }}</option>
                    <option value="medium">{{ __('Medium') }}</option>
                    <option value="high">{{ __('High') }}</option>
                    <option value="urgent">{{ __('Urgent') }}</option>
                </flux:select>
                @error('priority')
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
                <flux:label for="message" class="mb-2">{{ __('Your Complaint') }}</flux:label>
                <flux:textarea 
                    wire:model="message" 
                    id="message" 
                    rows="6"
                    placeholder="{{ __('Please describe your complaint...') }}"
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
