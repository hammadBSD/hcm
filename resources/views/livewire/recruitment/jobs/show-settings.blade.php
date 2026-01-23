<section class="w-full">
    @include('partials.recruitment-heading')
    
    <x-recruitment.layout>
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-8">
            <flux:heading size="lg" level="2" class="mb-2">
                {{ __('Job Post Settings') }}
            </flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400 mb-6">
                {{ __('Set up job post settings and pipeline defaults.') }}
            </flux:text>
            
            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6">
                <p class="text-zinc-500 dark:text-zinc-400 text-center">
                    {{ __('Job post settings management features will be implemented here.') }}
                </p>
            </div>
        </div>
    </x-recruitment.layout>
</section>
