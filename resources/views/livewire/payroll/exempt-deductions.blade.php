<section class="w-full">
    <x-payroll.layout :heading="__('Exempt Deductions')" :subheading="__('Manage deduction exemptions by month for departments, roles, groups, or employees')">
        <div class="space-y-6 w-full max-w-none">
            @if(session('success'))
                <flux:callout variant="success" icon="check-circle">
                    {{ session('success') }}
                </flux:callout>
            @endif

            <!-- Action Bar -->
            <div class="flex justify-between items-center gap-4">
                <div class="flex-1"></div>
                <flux:button variant="primary" icon="plus" wire:click="openCreateFlyout">
                    {{ __('Create Exemption') }}
                </flux:button>
            </div>

            <!-- Exemptions Table -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Scope') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Target') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Year / Month') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Exemption Type') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Notes') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Created By') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($exemptions as $exemption)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                    <td class="px-6 py-6 whitespace-nowrap">
                                        <flux:badge color="blue" size="sm">
                                            {{ ucfirst($exemption->scope_type) }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                        @if($exemption->scope_type === 'all')
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('All Employees') }}</span>
                                        @elseif($exemption->scope_type === 'department' && $exemption->department)
                                            {{ $exemption->department->title }}
                                        @elseif($exemption->scope_type === 'role' && $exemption->role)
                                            {{ $exemption->role->name }}
                                        @elseif($exemption->scope_type === 'group' && $exemption->group)
                                            {{ $exemption->group->name }}
                                        @elseif($exemption->scope_type === 'user' && $exemption->user)
                                            {{ $exemption->user->name }}
                                        @else
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('N/A') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                        @php
                                            $ym = $exemption->year_month;
                                            $d = \Carbon\Carbon::createFromFormat('Y-m', $ym);
                                        @endphp
                                        {{ $d->format('F Y') }}
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ \App\Models\DeductionExemption::EXEMPTION_TYPES[$exemption->exemption_type] ?? $exemption->exemption_type }}
                                    </td>
                                    <td class="px-6 py-6 text-sm text-zinc-900 dark:text-zinc-100">
                                        <div class="max-w-md truncate" title="{{ $exemption->notes }}">
                                            {{ $exemption->notes ?: __('No notes') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $exemption->creator->name ?? __('Unknown') }}
                                    </td>
                                    <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                            <flux:menu>
                                                <flux:menu.item
                                                    icon="trash"
                                                    wire:click="delete({{ $exemption->id }})"
                                                    wire:confirm="{{ __('Are you sure you want to delete this exemption? This action cannot be undone.') }}"
                                                    class="text-red-600 dark:text-red-400"
                                                >
                                                    {{ __('Delete') }}
                                                </flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <flux:icon name="document-text" class="mx-auto h-12 w-12 text-zinc-400" />
                                        <flux:heading size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                                            {{ __('No exemption deductions found') }}
                                        </flux:heading>
                                        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                                            {{ __('Get started by creating your first exemption.') }}
                                        </flux:text>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($exemptions->hasPages())
                    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                        {{ $exemptions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-payroll.layout>

    <!-- Create Exemption Flyout -->
    <flux:modal variant="flyout" wire:model="showCreateFlyout">
        <form class="flex flex-col h-full" wire:submit.prevent="submit">
            <div class="px-6 pt-6 pb-4 border-b border-zinc-200 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Create Exempt Deduction') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                    {{ __('Create a deduction exemption for a month for all employees, a department, role, group, or specific employees.') }}
                </flux:text>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                <div class="space-y-4">
                    <!-- Scope Type -->
                    <flux:field>
                        <flux:label>{{ __('Scope Type') }} <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model.live="form.scope_type" placeholder="{{ __('Select Scope Type') }}">
                            <option value="all">{{ __('All Employees') }}</option>
                            <option value="department">{{ __('Department') }}</option>
                            <option value="role">{{ __('Role') }}</option>
                            <option value="group">{{ __('Group') }}</option>
                            <option value="user">{{ __('Employee') }}</option>
                        </flux:select>
                        <flux:error name="form.scope_type" />
                    </flux:field>

                    @if($form['scope_type'] === 'department')
                        <flux:field>
                            <flux:label>{{ __('Department') }} <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="form.department_id" placeholder="{{ __('Select Department') }}">
                                <option value="">{{ __('Select Department') }}</option>
                                @foreach($departmentOptions as $dept)
                                    <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="form.department_id" />
                        </flux:field>
                    @endif

                    @if($form['scope_type'] === 'role')
                        <flux:field>
                            <flux:label>{{ __('Role') }} <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="form.role_id" placeholder="{{ __('Select Role') }}">
                                <option value="">{{ __('Select Role') }}</option>
                                @foreach($roleOptions as $role)
                                    <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="form.role_id" />
                        </flux:field>
                    @endif

                    @if($form['scope_type'] === 'group')
                        <flux:field>
                            <flux:label>{{ __('Group') }} <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="form.group_id" placeholder="{{ __('Select Group') }}">
                                <option value="">{{ __('Select Group') }}</option>
                                @foreach($groupOptions as $group)
                                    <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="form.group_id" />
                        </flux:field>
                    @endif

                    @if($form['scope_type'] === 'user')
                        <flux:field>
                            <flux:label>{{ __('Employee') }} <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="form.user_id" placeholder="{{ __('Select Employee') }}">
                                <option value="">{{ __('Select Employee') }}</option>
                                @foreach($employeeOptions as $employee)
                                    <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="form.user_id" />
                        </flux:field>
                    @endif

                    <!-- Year / Month -->
                    <flux:field>
                        <flux:label>{{ __('Year / Month') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="form.year_month" type="month" />
                        <flux:error name="form.year_month" />
                    </flux:field>

                    <!-- Exemption Type -->
                    <flux:field>
                        <flux:label>{{ __('Exemption Type') }} <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="form.exemption_type" placeholder="{{ __('Select Exemption Type') }}">
                            <option value="absent_days">{{ __('Absent Days') }}</option>
                            <option value="hourly_deduction_short_hours">{{ __('Hourly Deduction (Short hours)') }}</option>
                            <option value="all">{{ __('All') }}</option>
                        </flux:select>
                        <flux:error name="form.exemption_type" />
                    </flux:field>

                    <!-- Notes -->
                    <flux:field>
                        <flux:label>{{ __('Notes') }}</flux:label>
                        <flux:textarea
                            wire:model="form.notes"
                            rows="4"
                            class="dark:bg-transparent!"
                            placeholder="{{ __('Optional notes about this exemption...') }}"
                        ></flux:textarea>
                        <flux:error name="form.notes" />
                    </flux:field>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
                <flux:button type="button" variant="outline" wire:click="closeCreateFlyout" wire:loading.attr="disabled" kbd="esc">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" wire:loading.attr="disabled" icon="plus">
                    <span wire:loading.remove wire:target="submit">{{ __('Create Exemption') }}</span>
                    <span wire:loading wire:target="submit">{{ __('Creating...') }}</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>
