<section class="w-full">
    @include('partials.leaves-heading')

    <x-leaves.layout :heading="__('Leave Request')" :subheading="__('Submit and manage your leave requests')">
        <div class="space-y-6">
            <!-- Employee Leave Summary -->
            <div class="space-y-4">
                @forelse($leaveBalances as $balance)
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon name="calendar-days" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ $balance['leave_type_name'] }}@if(!empty($balance['leave_type_code'])) ({{ $balance['leave_type_code'] }}) @endif - Leave Balance <span class="text-zinc-500 dark:text-zinc-400 font-normal">(Current Leave Quota Year)</span>
                                </span>
                            </div>
                            <div class="flex items-center gap-6 text-sm">
                                <div class="text-center">
                                    <div class="text-zinc-500 dark:text-zinc-400">{{ __('Entitled') }}</div>
                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ number_format($balance['entitled'] ?? 0, 1) }}
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-zinc-500 dark:text-zinc-400">{{ __('Taken') }}</div>
                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ number_format($balance['used'] ?? 0, 1) }}
                                    </div>
                                </div>
                                @php
                                    $pendingValue = $balance['pending'] ?? 0;
                                    $pendingTextClasses = $pendingValue > 0
                                        ? 'text-amber-600 dark:text-amber-300'
                                        : 'text-zinc-900 dark:text-zinc-100';
                                @endphp
                                <div class="text-center">
                                    <div class="text-zinc-500 dark:text-zinc-400">{{ __('Pending') }}</div>
                                    <div class="font-semibold {{ $pendingTextClasses }}">
                                        {{ number_format($pendingValue, 1) }}
                                    </div>
                                </div>
                                @php
                                    $balanceValue = $balance['balance'] ?? 0;
                                @endphp
                                <div class="text-center">
                                    <div class="text-zinc-500 dark:text-zinc-400">{{ __('Balance') }}</div>
                                    <div class="font-bold {{ $balanceValue >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ number_format($balanceValue, 1) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                        <div class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('No leave balances found') }}
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Leave Request Form -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-8">
                <div class="mb-8">
                    <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                        Add Leave Request
            </flux:heading>
                </div>

                <form wire:submit="submit" class="space-y-6 mt-5">
                    <!-- First Row: Leave Type, Leave Duration, Leave Days -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Leave Type -->
                        <flux:field>
                    <flux:label>Leave Type <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="leaveType" placeholder="Select One">
                        <option value="">{{ __('Select One') }}</option>
                        @foreach($leaveTypeOptions as $option)
                            <option value="{{ $option['id'] }}">
                                {{ $option['name'] }}@if(!empty($option['code'])) ({{ $option['code'] }}) @endif
                            </option>
                        @endforeach
                    </flux:select>
                            <flux:error name="leaveType" />
                        </flux:field>

                        <!-- Leave Duration -->
                        <flux:field>
                            <flux:label>Leave Duration <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model.live="leaveDuration" placeholder="Full Day">
                                <option value="full_day">Full Day</option>
                                <option value="half_day_morning">Half Day (Morning)</option>
                                <option value="half_day_afternoon">Half Day (Afternoon)</option>
                            </flux:select>
                            <flux:error name="leaveDuration" />
                        </flux:field>

                        <!-- Leave Days -->
                        <flux:field>
                            <flux:label>Leave Days</flux:label>
                            <flux:input 
                                wire:model="leaveDays" 
                                type="text" 
                                placeholder="0.0" 
                                pattern="[0-9.]+" 
                                inputmode="decimal"
                                readonly
                                disabled
                                class="bg-zinc-50 dark:bg-zinc-700/50 cursor-not-allowed"
                            />
                            <!-- <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                {{ __('Auto-calculated from dates and duration') }}
                            </div> -->
                        </flux:field>
                    </div>

                    <!-- Second Row: Leave From, Leave To -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Leave From -->
                        <flux:field>
                            <flux:label>Leave From <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model.live="leaveFrom" type="date" />
                            <flux:error name="leaveFrom" />
                        </flux:field>

                        <!-- Leave To -->
                        <flux:field>
                            <flux:label>Leave To <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model.live="leaveTo" type="date" />
                            <flux:error name="leaveTo" />
                        </flux:field>
                    </div>

                    <!-- Reason -->
                    <flux:field>
                        <flux:label>Reason</flux:label>
                        <flux:textarea wire:model="reason" rows="4" placeholder="Please provide a detailed reason for your leave request... (Optional)"></flux:textarea>
                        <flux:error name="reason" />
                    </flux:field>

                    <!-- Upload Attachment -->
                    <div class="space-y-2">
                        <flux:label>Upload Attachment</flux:label>
                        <input
                            type="file"
                            wire:model="attachment"
                            class="block w-full text-sm text-zinc-600 dark:text-zinc-300"
                        />
                        <flux:error name="attachment" />
                    </div>

                    <!-- Submit Button -->
                    @if($balanceWarning)
                        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/60 dark:bg-amber-900/30 dark:text-amber-200">
                            {{ $balanceWarning }}
                        </div>
                    @endif

                    <div class="flex justify-end pt-4">
                        <flux:button
                            type="submit"
                            variant="primary"
                            class="px-8"
                            wire:loading.attr="disabled"
                            :disabled="$submitDisabled"
                        >
                            {{ __('Submit') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </x-leaves.layout>
</section>
