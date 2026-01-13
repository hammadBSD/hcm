<div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <flux:icon name="clipboard-document-check" class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Daily Tasks') }}</span>
            </div>
            @if($hasTemplate && $template)
                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                    {{ $todayLog ? __('Completed') : __('Pending') }}
                </flux:heading>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ $template->name }}
                </div>
            @else
                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                    {{ __('No Template') }}
                </flux:heading>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ __('No task template assigned') }}
                </div>
            @endif
        </div>
        <div class="w-12 h-12 {{ $hasTemplate && $todayLog ? 'bg-green-100 dark:bg-green-900' : 'bg-blue-100 dark:bg-blue-900' }} rounded-full flex items-center justify-center">
            <flux:icon name="{{ $hasTemplate && $todayLog ? 'check-circle' : 'clipboard-document-check' }}" class="w-6 h-6 {{ $hasTemplate && $todayLog ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}" />
        </div>
    </div>
    
    @if($hasTemplate && $template)
        @if($isLocked)
            <div class="mb-4">
                <flux:callout variant="warning" icon="lock-closed" size="sm">
                    {{ __('Task log is locked') }}
                </flux:callout>
            </div>
        @endif
        
        <div class="flex items-center gap-3">
            <flux:button 
                variant="primary" 
                icon="pencil"
                :href="route('tasks.daily-log')"
                wire:navigate
            >
                {{ $todayLog ? __('View/Edit Tasks') : __('Log Tasks') }}
            </flux:button>
            @if($todayLog && !$isLocked)
                <flux:badge color="green" size="sm">
                    {{ __('Saved') }}
                </flux:badge>
            @endif
        </div>
    @elseif($settings && $settings->enabled)
        <div class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('Contact your administrator to assign a task template.') }}
        </div>
    @else
        <div class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('Daily task logging is disabled.') }}
        </div>
    @endif
</div>
