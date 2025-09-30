<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('API Monitor') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                {{ __('Monitor ZKTeco API status and sync performance') }}
            </flux:text>
        </div>
        <flux:button 
            wire:click="refreshData" 
            variant="outline" 
            icon="arrow-path" 
            :disabled="$isRefreshing"
            class="{{ $isRefreshing ? 'animate-spin' : '' }}"
        >
            {{ $isRefreshing ? __('Refreshing...') : __('Refresh') }}
        </flux:button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <flux:icon name="check-circle" class="w-5 h-5 text-green-500 mr-3" />
                <flux:text class="text-green-800 dark:text-green-200">
                    {{ session('message') }}
                </flux:text>
            </div>
        </div>
    @endif

    <!-- API Status Card -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <flux:heading size="md">{{ __('API Connection Status') }}</flux:heading>
        </div>
        <div class="px-6 py-4 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        @if($apiStatus['status'] === 'success')
                            <flux:icon name="check-circle" class="w-5 h-5 text-green-500" />
                            <flux:badge color="green">{{ $apiStatus['connection'] }}</flux:badge>
                        @else
                            <flux:icon name="x-circle" class="w-5 h-5 text-red-500" />
                            <flux:badge color="red">{{ $apiStatus['connection'] }}</flux:badge>
                        @endif
                    </div>
                </div>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Last checked:') }} {{ $apiStatus['last_check'] }}
                </flux:text>
            </div>

            <flux:text class="text-sm">
                {{ $apiStatus['message'] }}
            </flux:text>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <div>
                    <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('API URL') }}</flux:text>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $apiStatus['api_url'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Rate Limit') }}</flux:text>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $apiStatus['rate_limit'] }} {{ __('requests/min') }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Timeout') }}</flux:text>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $apiStatus['timeout'] }} {{ __('seconds') }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="px-6 py-4 text-center">
                <flux:icon name="users" class="w-8 h-8 text-blue-500 mx-auto mb-2" />
                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                    {{ $syncStats['total_employees'] }}
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Device Employees') }}
                </flux:text>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="px-6 py-4 text-center">
                <flux:icon name="clock" class="w-8 h-8 text-green-500 mx-auto mb-2" />
                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                    {{ $syncStats['total_attendance_records'] }}
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Attendance Records') }}
                </flux:text>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="px-6 py-4 text-center">
                <flux:icon name="exclamation-triangle" class="w-8 h-8 text-yellow-500 mx-auto mb-2" />
                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                    {{ $syncStats['unprocessed_records'] }}
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Unprocessed') }}
                </flux:text>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="px-6 py-4 text-center">
                <flux:icon name="chart-bar" class="w-8 h-8 text-purple-500 mx-auto mb-2" />
                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                    {{ $syncStats['processed_percentage'] }}%
                </flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Processed') }}
                </flux:text>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <flux:heading size="md">{{ __('Recent Activity') }}</flux:heading>
            @if($syncStats['last_sync'])
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Last sync:') }} {{ $syncStats['last_sync']->format('Y-m-d H:i:s') }}
                </flux:text>
            @endif
        </div>
        <div class="px-6 py-4">
            @if(count($recentActivity) > 0)
                <div class="space-y-3">
                    @foreach($recentActivity as $activity)
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                            <div class="flex items-center gap-3">
                                <flux:icon name="clock" class="w-4 h-4 text-zinc-500" />
                                <div>
                                    <flux:text class="text-sm font-medium">
                                        {{ $activity['punch_code'] }} - {{ $activity['device_ip'] }}
                                    </flux:text>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $activity['punch_time']->format('Y-m-d H:i:s') }}
                                    </flux:text>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:badge 
                                    color="{{ $activity['device_type'] === 'IN' ? 'green' : 'blue' }}" 
                                    size="sm"
                                >
                                    {{ $activity['device_type'] }}
                                </flux:badge>
                                @if($activity['is_processed'])
                                    <flux:icon name="check-circle" class="w-4 h-4 text-green-500" />
                                @else
                                    <flux:icon name="clock" class="w-4 h-4 text-yellow-500" />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <flux:icon name="clock" class="w-12 h-12 text-zinc-400 mx-auto mb-4" />
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        {{ __('No recent activity found') }}
                    </flux:text>
                </div>
            @endif
        </div>
    </div>
</div>
