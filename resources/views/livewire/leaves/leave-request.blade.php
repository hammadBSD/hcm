<section class="w-full">
    @include('partials.leaves-heading')

    <x-leaves.layout :heading="__('Leave Request')" :subheading="__('Submit and manage your leave requests')">
        <div class="space-y-6">
            <!-- Employee Leave Summary -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:icon name="calendar-days" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Leave Balance <span class="text-zinc-500 dark:text-zinc-400 font-normal">(Current Leave Quota Year)</span></span>
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
                                <option value="">Select One</option>
                                <option value="sick">Sick Leave</option>
                                <option value="personal">Personal Leave</option>
                                <option value="vacation">Vacation Leave</option>
                                <option value="emergency">Emergency Leave</option>
                                <option value="maternity">Maternity Leave</option>
                                <option value="paternity">Paternity Leave</option>
                                <option value="bereavement">Bereavement Leave</option>
                            </flux:select>
                            <flux:error name="leaveType" />
                        </flux:field>

                        <!-- Leave Duration -->
                        <flux:field>
                            <flux:label>Leave Duration <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="leaveDuration" placeholder="Full Day">
                                <option value="full_day">Full Day</option>
                                <option value="half_day_morning">Half Day (Morning)</option>
                                <option value="half_day_afternoon">Half Day (Afternoon)</option>
                            </flux:select>
                            <flux:error name="leaveDuration" />
                        </flux:field>

                        <!-- Leave Days -->
                        <flux:field>
                            <flux:label>Leave Days</flux:label>
                            <flux:input wire:model="leaveDays" type="text" placeholder="1.0" pattern="[0-9.]+" inputmode="decimal" />
                        </flux:field>
                    </div>

                    <!-- Second Row: Leave From, Leave To -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Leave From -->
                        <flux:field>
                            <flux:label>Leave From</flux:label>
                            <flux:input wire:model="leaveFrom" type="date" />
                            <flux:error name="leaveFrom" />
                        </flux:field>

                        <!-- Leave To -->
                        <flux:field>
                            <flux:label>Leave To</flux:label>
                            <flux:input wire:model="leaveTo" type="date" />
                            <flux:error name="leaveTo" />
                        </flux:field>
                    </div>

                    <!-- Reason -->
                    <flux:field>
                        <flux:label>Reason <span class="text-red-500">*</span></flux:label>
                        <flux:textarea wire:model="reason" rows="4" placeholder="Please provide a detailed reason for your leave request..."></flux:textarea>
                        <flux:error name="reason" />
                    </flux:field>

                    <!-- Upload Attachment -->
                    <div class="space-y-2">
                        <flux:label>Upload Attachment</flux:label>
                        <div class="flex items-center gap-4">
                            <flux:button variant="outline" type="button">
                                Upload File
                            </flux:button>
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">No File Selected</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4">
                        <flux:button type="submit" variant="primary" class="px-8">
                            Submit
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </x-leaves.layout>
</section>
