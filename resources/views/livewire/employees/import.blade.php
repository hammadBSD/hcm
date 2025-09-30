<section class="w-full">
    @include('partials.employees-heading')
    
    <x-employees.layout :heading="__('Import Employees')" :subheading="__('Import employees from Excel file')">
        <div class="max-w-4xl mx-auto">
            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <flux:icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" />
                        <div class="text-green-800 dark:text-green-200">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <flux:icon name="exclamation-triangle" class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" />
                        <div class="text-red-800 dark:text-red-200">
                            {{ session('error') }}
                        </div>
                    </div>
                </div>
            @endif
            <!-- Import Instructions -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-6">
                <div class="flex items-start">
                    <flux:icon name="information-circle" class="w-6 h-6 text-blue-600 dark:text-blue-400 mt-0.5 mr-3" />
                    <div>
                        <flux:heading size="lg" class="text-blue-900 dark:text-blue-100 mb-2">
                            {{ __('Import Instructions') }}
                        </flux:heading>
                        <div class="text-blue-800 dark:text-blue-200 space-y-2">
                            <p>{{ __('Please ensure your Excel file contains the following required columns:') }}</p>
                            <ul class="list-disc list-inside space-y-1 ml-4">
                                <li><strong>{{ __('firstname') }}</strong> - Employee's first name</li>
                                <li><strong>{{ __('lastname') }}</strong> - Employee's last name</li>
                                <li><strong>{{ __('email') }}</strong> - Employee's email address</li>
                                <li><strong>{{ __('mobileno') }}</strong> - Employee's mobile number</li>
                            </ul>
                            <p class="mt-3">{{ __('All other fields are optional. Empty fields will be set to null.') }}</p>
                            <p>{{ __('Standard password for all imported employees:') }} <code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">admin123</code></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Form -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <form wire:submit.prevent="import">
                    <div class="space-y-6">
                        <!-- File Upload -->
                        <div>
                            <flux:field>
                                <flux:label>{{ __('Select Excel File') }} <span class="text-red-500">*</span></flux:label>
                                <flux:input 
                                    type="file" 
                                    wire:model="file" 
                                    accept=".xlsx,.xls,.csv"
                                    class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                />
                                <flux:error name="file" />
                                
                                @if($file)
                                    <div class="mt-2 text-sm text-green-600 dark:text-green-400 flex items-center">
                                        <flux:icon name="check-circle" class="w-4 h-4 mr-2" />
                                        File selected: {{ $file->getClientOriginalName() }}
                                    </div>
                                @else
                                    <div class="mt-2 text-sm text-amber-600 dark:text-amber-400 flex items-center">
                                        <flux:icon name="exclamation-triangle" class="w-4 h-4 mr-2" />
                                        Please select an Excel file (.xlsx, .xls, or .csv)
                                    </div>
                                @endif
                            </flux:field>
                        </div>

                        <!-- Import Button -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <flux:button 
                                    type="submit" 
                                    variant="primary" 
                                    icon="arrow-up-tray"
                                    :disabled="$importing || !$file"
                                    wire:loading.attr="disabled"
                                    wire:target="import"
                                >
                                    <span wire:loading.remove wire:target="import">
                                        @if(!$file)
                                            {{ __('Select File First') }}
                                        @else
                                            {{ __('Import Employees') }}
                                        @endif
                                    </span>
                                    <span wire:loading wire:target="import" class="flex items-center">
                                        <flux:icon name="arrow-path" class="w-4 h-4 mr-2 animate-spin" />
                                        {{ __('Importing...') }}
                                    </span>
                                </flux:button>

                                <flux:button 
                                    type="button" 
                                    variant="outline" 
                                    href="{{ route('employees.list') }}"
                                >
                                    {{ __('Cancel') }}
                                </flux:button>
                            </div>

                            @if($importing)
                                <div class="flex items-center text-sm text-blue-600 dark:text-blue-400">
                                    <flux:icon name="arrow-path" class="w-4 h-4 mr-2 animate-spin" />
                                    {{ __('Processing file...') }}
                                    <div class="ml-4">
                                        <div class="w-32 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                                 style="width: {{ $importing ? '50' : '0' }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Import Results -->
            @if(!empty($importResults))
                <div class="mt-6 space-y-4">
                    <!-- Success Summary -->
                    @if($successCount > 0)
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <div class="flex items-center">
                                <flux:icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" />
                                <div>
                                    <flux:heading size="md" class="text-green-900 dark:text-green-100">
                                        {{ __('Import Successful') }}
                                    </flux:heading>
                                    <p class="text-green-800 dark:text-green-200">
                                        {{ __('Successfully imported :count employees', ['count' => $successCount]) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Error Summary -->
                    @if($errorCount > 0)
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <flux:icon name="exclamation-triangle" class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" />
                                <div>
                                    <flux:heading size="md" class="text-red-900 dark:text-red-100">
                                        {{ __('Import Errors') }}
                                    </flux:heading>
                                    <p class="text-red-800 dark:text-red-200">
                                        {{ __('Failed to import :count employees', ['count' => $errorCount]) }}
                                    </p>
                                </div>
                            </div>

                            @if(!empty($errors))
                                <div class="max-h-60 overflow-y-auto">
                                    <div class="space-y-2">
                                        @foreach($errors as $error)
                                            <div class="text-sm text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-800/50 px-3 py-2 rounded">
                                                {{ $error }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-4">
                        <flux:button 
                            variant="outline" 
                            href="{{ route('employees.list') }}"
                        >
                            {{ __('View Employee List') }}
                        </flux:button>

                        <flux:button 
                            variant="primary" 
                            wire:click="openModal"
                        >
                            {{ __('Import More Employees') }}
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </x-employees.layout>
</section>

