@props([
    'heading',
    'description' => null,
    'leaves' => [],
    'emptyText' => null,
])

<div class="bg-zinc-50 dark:bg-zinc-800/70 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 h-full">
    <flux:heading size="sm" class="mb-1">{{ $heading }}</flux:heading>
    @if($description)
        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">{{ $description }}</flux:text>
    @endif
    <div class="space-y-3">
        @forelse($leaves as $lv)
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-3 text-sm">
                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                    {{ $lv['leave_type'] }}
                    @if(!empty($lv['leave_type_code']))
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ $lv['leave_type_code'] }})</span>
                    @endif
                    <span class="text-zinc-500 dark:text-zinc-400 font-normal"> · {{ number_format($lv['total_days'], 1) }} {{ __('days') }}</span>
                </div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ $lv['start_date'] }} – {{ $lv['end_date'] }}
                </div>
                <div class="mt-2 text-zinc-700 dark:text-zinc-200">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Reason') }}</span>
                    <p class="mt-0.5 whitespace-pre-line">{{ $lv['reason'] }}</p>
                </div>
            </div>
        @empty
            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $emptyText ?? __('No leave records for this year yet.') }}
            </div>
        @endforelse
    </div>
</div>
