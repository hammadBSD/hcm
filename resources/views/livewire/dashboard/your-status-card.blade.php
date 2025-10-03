<div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <flux:icon name="clock" class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Your Status</span>
                </div>
                <button wire:click="refresh" class="text-xs text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <flux:icon name="arrow-path" class="w-3 h-3" />
                </button>
            </div>
            <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                {{ $status }}
            </flux:heading>
            <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                @if($status === 'Present' && $checkInTime)
                    Checked in at {{ $checkInTime }}
                @elseif($status === 'Partial')
                    Partial attendance today
                @elseif($status === 'Not Found')
                    Employee record not found
                @else
                    Not checked in today
                @endif
            </div>
        </div>
        <div class="w-12 h-12 {{ $this->getStatusColor() }} rounded-full flex items-center justify-center">
            <flux:icon name="{{ $this->getStatusIcon() }}" class="w-6 h-6 {{ $this->getStatusIconColor() }}" />
        </div>
    </div>
</div>