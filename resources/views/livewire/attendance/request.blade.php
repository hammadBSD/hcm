<section class="w-full">
    @include('partials.attendance-heading')

    <x-attendance.layout :heading="__('Attendance Request')" :subheading="__('Request attendance corrections for missing check-ins/outs')">
        <div class="space-y-6">
            <!-- Attendance Request Form -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-8">
                <div class="mb-8">
                    <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                        Add Attendance Request
                    </flux:heading>
                </div>

                <form wire:submit="submitAttendanceRequest" class="space-y-6 mt-5">
                    <!-- First Row: Employee, Attendance Date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Employee -->
                        <flux:field>
                            <flux:label>Employee <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="employeeId" placeholder="Select One">
                                <option value="">Select One</option>
                                <option value="1">John Doe (EMP001)</option>
                                <option value="2">Jane Smith (EMP002)</option>
                                <option value="3">Mike Johnson (EMP003)</option>
                                <option value="4">Sarah Wilson (EMP004)</option>
                            </flux:select>
                            <flux:error name="employeeId" />
                        </flux:field>

                        <!-- Attendance Date -->
                        <flux:field>
                            <flux:label>Attendance Date</flux:label>
                            <flux:input wire:model="attendanceDate" type="date" />
                            <flux:error name="attendanceDate" />
                        </flux:field>
                    </div>

                    <!-- Second Row: In Date, In Time -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- In Date -->
                        <flux:field>
                            <flux:label>In Date</flux:label>
                            <flux:input wire:model="inDate" type="date" />
                            <flux:error name="inDate" />
                        </flux:field>

                        <!-- In Time -->
                        <flux:field>
                            <flux:label>In Time <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="inTime" type="time" placeholder="Select Time" />
                            <flux:error name="inTime" />
                        </flux:field>
                    </div>

                    <!-- Third Row: Out Date, Out Time -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Out Date -->
                        <flux:field>
                            <flux:label>Out Date</flux:label>
                            <flux:input wire:model="outDate" type="date" />
                            <flux:error name="outDate" />
                        </flux:field>

                        <!-- Out Time -->
                        <flux:field>
                            <flux:label>Out Time <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="outTime" type="time" placeholder="Select Time" />
                            <flux:error name="outTime" />
                        </flux:field>
                    </div>

                    <!-- Fourth Row: Attendance Type -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Attendance Type -->
                        <flux:field>
                            <flux:label>Attendance Type</flux:label>
                            <flux:select wire:model="attendanceType" placeholder="Other">
                                <option value="">Other</option>
                                <option value="late_arrival">Late Arrival</option>
                                <option value="early_departure">Early Departure</option>
                                <option value="missed_checkin">Missed Check-in</option>
                                <option value="missed_checkout">Missed Check-out</option>
                                <option value="system_error">System Error</option>
                                <option value="device_malfunction">Device Malfunction</option>
                                <option value="emergency">Emergency</option>
                            </flux:select>
                            <flux:error name="attendanceType" />
                        </flux:field>

                        <!-- Empty column for spacing -->
                        <div></div>
                    </div>

                    <!-- Reason -->
                    <flux:field>
                        <flux:label>Reason <span class="text-red-500">*</span></flux:label>
                        <flux:textarea 
                            wire:model="reason" 
                            rows="4" 
                            placeholder="Please provide a detailed reason for this attendance request..."
                        ></flux:textarea>
                        <flux:error name="reason" />
                    </flux:field>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4">
                        <flux:button type="submit" variant="primary" class="px-8">
                            Submit
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </x-attendance.layout>
</section>
