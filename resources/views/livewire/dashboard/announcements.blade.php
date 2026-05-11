<div wire:key="dashboard-announcements">
    <div @class(['hidden' => $items->isEmpty()])>
        @if($items->isNotEmpty())
            <div class="space-y-3">
                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                    {{ __('Announcements') }}
                </flux:heading>
                @foreach($items as $announcement)
                    @php
                        $styles = match ($announcement->type) {
                            'warning' => 'border-amber-200 dark:border-amber-800 bg-amber-50/80 dark:bg-amber-900/15',
                            'success' => 'border-emerald-200 dark:border-emerald-800 bg-emerald-50/80 dark:bg-emerald-900/15',
                            'error' => 'border-red-200 dark:border-red-900 bg-red-50/80 dark:bg-red-900/15',
                            default => 'border-blue-200 dark:border-blue-800 bg-blue-50/80 dark:bg-blue-900/15',
                        };
                        $iconColor = match ($announcement->type) {
                            'warning' => 'text-amber-600 dark:text-amber-400',
                            'success' => 'text-emerald-600 dark:text-emerald-400',
                            'error' => 'text-red-600 dark:text-red-400',
                            default => 'text-blue-600 dark:text-blue-400',
                        };
                    @endphp
                    <div class="rounded-lg border p-4 md:p-5 {{ $styles }}">
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 pt-0.5">
                                <flux:icon name="megaphone" class="w-6 h-6 {{ $iconColor }}" />
                            </div>
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">
                                        {{ $announcement->title }}
                                    </flux:heading>
                                    @if($announcement->is_pinned)
                                        <flux:badge size="sm" color="zinc">{{ __('Pinned') }}</flux:badge>
                                    @endif
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Visible') }}:
                                    {{ $announcement->start_date->format('M j, Y') }}
                                    —
                                    {{ $announcement->end_date->format('M j, Y') }}
                                </p>
                                <div class="prose prose-sm dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300">
                                    {!! nl2br(e($announcement->content)) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <flux:modal wire:model.live="showLoginAnnouncementModal" size="3xl" class="md:min-w-[36rem]">
        <div class="announcement-login-popup-frame border-zinc-200/40 bg-white p-6 dark:border-zinc-600/30 dark:bg-zinc-900 md:p-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="xl" class="text-zinc-900 dark:text-zinc-100">
                        {{ __('Announcements') }}
                    </flux:heading>
                    <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                        {{ __('Please read the following updates for your organization.') }}
                    </flux:text>
                </div>
                <flux:icon name="megaphone" class="hidden h-10 w-10 shrink-0 text-amber-500 dark:text-amber-400 sm:block" />
            </div>

            <div class="custom-scrollbar max-h-[min(60vh,28rem)] space-y-5 overflow-y-auto pr-1">
                @foreach($items as $announcement)
                    @php
                        $panelStyles = match ($announcement->type) {
                            'warning' => 'border-amber-200/80 dark:border-amber-800/80 bg-amber-50/90 dark:bg-amber-950/30',
                            'success' => 'border-emerald-200/80 dark:border-emerald-800/80 bg-emerald-50/90 dark:bg-emerald-950/30',
                            'error' => 'border-red-200/80 dark:border-red-900/80 bg-red-50/90 dark:bg-red-950/30',
                            default => 'border-blue-200/80 dark:border-blue-800/80 bg-blue-50/90 dark:bg-blue-950/30',
                        };
                        $iconColor = match ($announcement->type) {
                            'warning' => 'text-amber-600 dark:text-amber-400',
                            'success' => 'text-emerald-600 dark:text-emerald-400',
                            'error' => 'text-red-600 dark:text-red-400',
                            default => 'text-blue-600 dark:text-blue-400',
                        };
                    @endphp
                    <div class="rounded-lg border p-4 md:p-5 {{ $panelStyles }}">
                        <div class="flex gap-4">
                            <flux:icon name="megaphone" class="h-7 w-7 shrink-0 {{ $iconColor }}" />
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                                        {{ $announcement->title }}
                                    </flux:heading>
                                    @if($announcement->is_pinned)
                                        <flux:badge size="sm" color="zinc">{{ __('Pinned') }}</flux:badge>
                                    @endif
                                </div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Visible') }}:
                                    {{ $announcement->start_date->format('M j, Y') }}
                                    —
                                    {{ $announcement->end_date->format('M j, Y') }}
                                </p>
                                <div class="prose prose-base dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-200">
                                    {!! nl2br(e($announcement->content)) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end border-t border-zinc-200 pt-6 dark:border-zinc-700">
                <flux:button variant="primary" wire:click="dismissLoginAnnouncementPopup">
                    {{ __('Got it') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
