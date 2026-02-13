<div class="{{ $activeComplaintsCount > 0 ? '' : 'hidden' }}">
    @if($activeComplaintsCount > 0)
        <div
            id="active-complaints-alert"
            role="button"
            tabindex="0"
            onclick="document.getElementById('dashboard-suggestions-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); document.getElementById('dashboard-suggestions-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }"
            class="flex items-center gap-3 p-4 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 cursor-pointer hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors"
        >
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                <flux:icon name="exclamation-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                    {{ $activeComplaintsCount === 1
                        ? __('You have 1 active complaint')
                        : __('You have :count active complaints', ['count' => $activeComplaintsCount]) }}
                </p>
                <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                    {{ __('Click to scroll to complaints table') }}
                </p>
            </div>
            <flux:icon name="chevron-down" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" />
        </div>
    @endif
</div>
