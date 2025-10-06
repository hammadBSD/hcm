<section class="w-full">
    @include('partials.payroll-heading')
    
    <x-payroll.layout :heading="__('Salary Reports')" :subheading="__('Generate and view salary reports')">
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Simple Salary Reports Page</h3>
            <p class="text-zinc-500 dark:text-zinc-400">This is a simplified version to test the layout.</p>
            
            <!-- Simple dropdown test -->
            <div class="mt-4">
                <flux:field>
                    <flux:label>Test Department</flux:label>
                    <flux:select>
                        <option value="it">IT</option>
                        <option value="hr">HR</option>
                    </flux:select>
                </flux:field>
            </div>
        </div>
    </x-payroll.layout>
</section>
