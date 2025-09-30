<section class="w-full">
    @include('partials.attendance-heading')

    <x-attendance.layout :heading="__('My Attendance')" :subheading="__('Your attendance records for ' . $currentMonth)">
        <div class="space-y-6 w-full max-w-none">
            @if($employee && $punchCode)
                <!-- Attendance Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Working Days -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Working Days</flux:text>
                                <flux:heading size="xl" class="text-zinc-900 dark:text-zinc-100">{{ $attendanceStats['working_days'] ?? 0 }}</flux:heading>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <flux:icon name="calendar-days" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Present Days -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Present Days</flux:text>
                                <flux:heading size="xl" class="text-green-600 dark:text-green-400">{{ $attendanceStats['attended_days'] ?? 0 }}</flux:heading>
                            </div>
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <flux:icon name="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Absent Days -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Absent Days</flux:text>
                                <flux:heading size="xl" class="text-red-600 dark:text-red-400">{{ $attendanceStats['absent_days'] ?? 0 }}</flux:heading>
                            </div>
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                <flux:icon name="x-circle" class="w-6 h-6 text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Percentage -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Attendance Rate</flux:text>
                                <flux:heading size="xl" class="text-zinc-900 dark:text-zinc-100">{{ $attendanceStats['attendance_percentage'] ?? 0 }}%</flux:heading>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <flux:icon name="chart-bar" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Working Hours Summary -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="lg">Working Hours Summary</flux:heading>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <div>
                                    <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Hours Worked</flux:text>
                                    <flux:heading size="xl" class="text-blue-600 dark:text-blue-400">{{ $attendanceStats['total_hours'] ?? '0:00' }}</flux:heading>
                                </div>
                                <flux:icon name="clock" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div>
                                    <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Expected Hours</flux:text>
                                    <flux:heading size="xl" class="text-green-600 dark:text-green-400">{{ $attendanceStats['expected_hours'] ?? '0:00' }}</flux:heading>
                                </div>
                                <flux:icon name="check-circle" class="w-8 h-8 text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Attendance Records -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 w-full">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="lg">Recent Attendance Records</flux:heading>
                    </div>
                    <div class="p-0 w-full">
                        @if(count($attendanceData) > 0)
                            <div class="overflow-x-auto w-full">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 w-full">
                                    <thead class="bg-zinc-50 dark:bg-zinc-700 w-full">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Day</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Check In</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Check Out</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Total Hours</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach($attendanceData as $record)
                                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $record['formatted_date'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $record['day_name'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $record['check_in'] ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $record['check_out'] ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $record['total_hours'] ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($record['status'] === 'present')
                                                        <flux:badge variant="success">Present</flux:badge>
                                                    @else
                                                        <flux:badge variant="danger">Absent</flux:badge>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <flux:icon name="document" class="mx-auto h-12 w-12 text-zinc-400" />
                                <flux:heading size="sm" class="mt-2 text-zinc-900 dark:text-zinc-100">No attendance records</flux:heading>
                                <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">No attendance data found for {{ $currentMonth }}.</flux:text>
                            </div>
                        @endif
                    </div>
                </div>

            @else
                <!-- No Employee Record or Punch Code -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                    <div class="flex items-center">
                        <flux:icon name="exclamation-triangle" class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3" />
                        <div>
                            <flux:heading size="sm" class="text-yellow-800 dark:text-yellow-200">Employee Record Not Found</flux:heading>
                            <flux:text class="text-yellow-700 dark:text-yellow-300">
                                @if(!$employee)
                                    No employee record found for your user account. Please contact HR to set up your employee profile.
                                @elseif(!$punchCode)
                                    No punch code assigned to your employee record. Please contact HR to set up your attendance tracking.
                                @endif
                            </flux:text>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-attendance.layout>
</section>