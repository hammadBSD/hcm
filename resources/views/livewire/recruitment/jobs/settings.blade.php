<section class="w-full">
    @include('partials.recruitment-heading')
    
    <x-recruitment.layout>
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-8">
            <div class="text-center">
                <flux:icon name="cog-6-tooth" class="w-16 h-16 mx-auto mb-4 text-zinc-400" />
                <flux:heading size="lg" level="3" class="mb-2">
                    {{ __('Job Post Settings') }}
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Configure job post settings and pipeline defaults.') }}
                </flux:text>
            </div>
        </div>
    </x-recruitment.layout>
</section>
