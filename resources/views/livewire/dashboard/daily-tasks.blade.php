@php
    $user = Auth::user();
    $isSuperAdmin = $user && $user->hasRole('Super Admin');
@endphp
<div class="bg-white dark:bg-zinc-800 rounded-lg border {{ $hasLogToday ? 'border-zinc-200 dark:border-zinc-700' : ($isSuperAdmin ? 'border-zinc-200 dark:border-zinc-700' : 'border-red-500 dark:border-red-600') }} p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <flux:icon name="clipboard-document-check" class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Daily Logs') }}</span>
            </div>
            @if($hasLogToday)
                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                    {{ __('Logged') }}
                </flux:heading>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ __('You have saved the work log for today.') }}
                </div>
            @elseif(!$isSuperAdmin)
                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                    {{ __('Not Logged') }}
                </flux:heading>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ __('You have not logged your work for today. Don\'t forget to log your work before your shift ends.') }}
                </div>
            @endif
        </div>
        @if($hasLogToday || $isSuperAdmin)
            <div class="w-12 h-12 {{ $hasLogToday ? 'bg-green-100 dark:bg-green-900' : 'bg-zinc-100 dark:bg-zinc-700' }} rounded-full flex items-center justify-center">
                @if($hasLogToday)
                    <flux:icon name="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
                @else
                    <flux:icon name="clipboard-document-check" class="w-6 h-6 text-zinc-600 dark:text-zinc-400" />
                @endif
            </div>
        @else
            <div class="w-12 h-12 bg-red-500 dark:bg-red-600 rounded-full flex items-center justify-center">
                <flux:icon name="x-mark" class="w-6 h-6 text-white" />
            </div>
        @endif
    </div>
    
    @if($settings && $settings->enabled)
        @if(!$hasLogToday)
            <div class="flex items-center gap-3">
                <flux:button 
                    variant="primary" 
                    icon="plus"
                    href="{{ route('tasks.daily-log') }}"
                    wire:navigate
                >
                    {{ __('Add Log') }}
                </flux:button>
            </div>
        @endif
    @else
        <div class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('Daily task logging is disabled.') }}
        </div>
    @endif
</div>

@if($showCreateLogFlyout)
    <!-- Create Log Flyout -->
    <flux:modal wire:model="showCreateLogFlyout" variant="flyout" name="create-daily-log" class="max-w-2xl">
        <flux:heading size="lg" class="mb-4">
            {{ __('Create Daily Log') }}
        </flux:heading>
        
        <form wire:submit="saveLog">
            <div class="space-y-6">
                <!-- Notes Field -->
                <flux:field>
                    <flux:label>{{ __('Notes') }} <span class="text-red-500">*</span></flux:label>
                    <flux:textarea
                        wire:model="createLogForm.notes"
                        rows="6"
                        placeholder="{{ __('Enter daily log notes...') }}"
                        required
                    />
                    <flux:error name="createLogForm.notes" />
                </flux:field>
            </div>
            
            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button type="button" wire:click="closeCreateLogFlyout" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Create Log') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
@endif
