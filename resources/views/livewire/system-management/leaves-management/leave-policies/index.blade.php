<section class="w-full">
    @include('partials.system-management-heading')

    <x-system-management.layout :heading="__('Leave Policies')" :subheading="__('Control how leave entitlements accrue, rollover, and are handled for each leave type')">
        <div class="grid gap-6 lg:grid-cols-[22rem_auto]">
            <!-- Leave Types Navigation -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 uppercase tracking-wide">
                                {{ __('Leave Types') }}
                            </h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Select a leave type to manage its policies.') }}
                            </p>
                        </div>
                        <flux:button variant="ghost" size="sm" icon="plus" href="{{ url('/system-management/leaves-management/leave-types') }}">
                            {{ __('Manage Types') }}
                        </flux:button>
                    </div>
                </div>

                <div class="max-h-[32rem] overflow-y-auto divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($leaveTypes as $leaveType)
                        <button
                            type="button"
                            wire:click="selectType({{ $leaveType->id }})"
                            class="w-full text-left px-5 py-4 transition-colors border-b border-zinc-200 dark:border-zinc-700 last:border-b-0
                                {{ $selectedTypeId === $leaveType->id ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800' }}"
                        >
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-lg flex items-center justify-center
                                    {{ $selectedTypeId === $leaveType->id ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300' }}">
                                    <flux:icon :name="$leaveType->icon ?? 'document-text'" class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="font-medium {{ $selectedTypeId === $leaveType->id ? 'text-blue-600 dark:text-blue-200' : 'text-zinc-900 dark:text-zinc-100' }}">
                                        {{ $leaveType->name }}
                                    </div>
                                    <div class="text-xs uppercase tracking-wide {{ $selectedTypeId === $leaveType->id ? 'text-blue-500 dark:text-blue-300' : 'text-zinc-500 dark:text-zinc-400' }}">
                                        {{ strtoupper($leaveType->code) }}
                                    </div>
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="px-5 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon name="folder" class="w-10 h-10 text-zinc-400 dark:text-zinc-600" />
                                <div>{{ __('No leave types configured yet.') }}</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Policies Panel -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ optional($leaveTypes->firstWhere('id', $selectedTypeId))->name ?? __('Select a leave type') }}
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Create policies for different eligibility periods, accrual rules and carry-forward controls.') }}
                        </p>
                    </div>

                    @if($selectedTypeId)
                        <flux:button variant="primary" icon="plus" wire:click="openCreatePolicyModal">
                            {{ __('New Policy') }}
                        </flux:button>
                    @endif
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    @if(!$selectedTypeId)
                        <div class="px-6 py-16 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon name="document-text" class="w-10 h-10 text-zinc-400 dark:text-zinc-600" />
                                <div>{{ __('Choose a leave type to review or create policies.') }}</div>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-zinc-50 dark:bg-zinc-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Effective Period') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Accrual & Base Quota') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Carry Forward') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Encashment') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Status') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Tiers') }}
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @forelse($policies as $policy)
                                        <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors duration-150">
                                            <td class="px-6 py-6">
                                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ optional($policy->effective_from)->format('M d, Y') ?? __('Immediate') }}
                                                </div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $policy->effective_to ? __('Until :date', ['date' => optional($policy->effective_to)->format('M d, Y')]) : __('Ongoing') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-6">
                                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ ucfirst($policy->accrual_frequency) }}
                                                    <span class="font-semibold">
                                                        {{ number_format($policy->base_quota, 1) }}
                                                        {{ $policy->quota_unit === 'hours' ? __('hours') : __('days') }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    <span>{{ __('Probation wait: :days days', ['days' => $policy->probation_wait_days]) }}</span>
                                                    @php $details = []; if($policy->prorate_on_joining) { $details[] = __('Prorated on joining'); } if(!$policy->auto_assign) { $details[] = __('Manual assignment only'); } @endphp
                                                    @if(!empty($details))
                                                        • {{ implode(' • ', $details) }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-6">
                                                <flux:badge :color="$policy->carry_forward_enabled ? 'green' : 'zinc'" size="sm">
                                                    {{ $policy->carry_forward_enabled ? __('Enabled') : __('Disabled') }}
                                                </flux:badge>
                                                @if($policy->carry_forward_enabled)
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                        {{ __('Cap: :cap', ['cap' => $policy->carry_forward_cap ?? __('Unlimited')]) }}
                                                        @if($policy->carry_forward_expiry_days)
                                                            • {{ __('Expires in :days days', ['days' => $policy->carry_forward_expiry_days]) }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-6">
                                                <flux:badge :color="$policy->encashment_enabled ? 'green' : 'zinc'" size="sm">
                                                    {{ $policy->encashment_enabled ? __('Allowed') : __('Not allowed') }}
                                                </flux:badge>
                                                @if($policy->encashment_enabled && $policy->encashment_cap)
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                        {{ __('Cap: :cap', ['cap' => number_format($policy->encashment_cap, 1)]) }}
                                                    </div>
                                                @endif
                                                @if($policy->allow_negative_balance)
                                                    <div class="text-xs text-amber-500 dark:text-amber-300 mt-1">
                                                        {{ __('Negative balances permitted') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-6">
                                                <flux:badge :color="($policy->is_active ?? true) ? 'green' : 'zinc'" size="sm">
                                                    {{ ($policy->is_active ?? true) ? __('Active') : __('Inactive') }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-6 py-6 align-top">
                                                @if($policy->tiers->isEmpty())
                                                    <div class="text-xs text-zinc-400 dark:text-zinc-500">
                                                        {{ __('No additional tiers') }}
                                                    </div>
                                                @else
                                                    <ul class="space-y-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                        @foreach($policy->tiers as $tier)
                                                            <li>
                                                                <span class="font-medium">{{ __('Year :year', ['year' => $tier->year_of_service]) }}</span> — {{ __('+ :days', ['days' => number_format($tier->additional_quota, 1)]) }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                            <td class="px-6 py-6 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center justify-center">
                                                    <flux:dropdown>
                                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                        <flux:menu>
                                                            <flux:menu.item icon="pencil" wire:click="openEditPolicyModal({{ $policy->id }})">
                                                                {{ __('Edit Policy') }}
                                                            </flux:menu.item>
                                                            <flux:menu.item
                                                                icon="{{ ($policy->is_active ?? true) ? 'pause' : 'play' }}"
                                                                wire:click="togglePolicyStatus({{ $policy->id }})"
                                                                class="{{ ($policy->is_active ?? true) ? '' : 'text-green-600 dark:text-green-400' }}"
                                                            >
                                                                {{ ($policy->is_active ?? true) ? __('Deactivate') : __('Activate') }}
                                                            </flux:menu.item>
                                                            <flux:menu.separator />
                                                            <flux:menu.item
                                                                icon="trash"
                                                                wire:click="deletePolicy({{ $policy->id }})"
                                                                wire:confirm="{{ __('Are you sure you want to delete this policy? This action cannot be undone.') }}"
                                                                class="text-red-600 dark:text-red-400"
                                                            >
                                                                {{ __('Delete') }}
                                                            </flux:menu.item>
                                                        </flux:menu>
                                                    </flux:dropdown>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                                <div class="flex flex-col items-center gap-3">
                                                    <flux:icon name="inbox" class="w-10 h-10 text-zinc-400 dark:text-zinc-600" />
                                                    <flux:heading size="sm" class="text-zinc-500 dark:text-zinc-400">
                                                        {{ __('No policies defined') }}
                                                    </flux:heading>
                                                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                                                        {{ __('Create your first policy to start automating leave accrual.') }}
                                                    </flux:text>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-system-management.layout>

    <!-- Create/Edit Policy Flyout -->
    <flux:modal wire:model="showPolicyModal" variant="flyout" :title="$editingPolicyId ? __('Edit Leave Policy') : __('Create Leave Policy')" class="w-[30rem] lg:w-[36rem]">
        <div class="space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input
                    type="date"
                    label="{{ __('Effective From') }}"
                    wire:model.defer="policyForm.effective_from"
                    required
                />

                <flux:input
                    type="date"
                    label="{{ __('Effective To') }}"
                    wire:model.defer="policyForm.effective_to"
                />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:select label="{{ __('Accrual Frequency') }}" wire:model.defer="policyForm.accrual_frequency">
                    <option value="none">{{ __('No automatic accrual') }}</option>
                    <option value="monthly">{{ __('Monthly') }}</option>
                    <option value="quarterly">{{ __('Quarterly') }}</option>
                    <option value="semi-annual">{{ __('Semi-Annual') }}</option>
                    <option value="annual">{{ __('Annual') }}</option>
                </flux:select>

                <div class="grid gap-3">
                    <flux:input
                        type="number"
                        step="0.5"
                        min="0"
                        label="{{ __('Base Quota') }}"
                        wire:model.defer="policyForm.base_quota"
                        required
                    />
                    <flux:select label="{{ __('Unit') }}" wire:model.defer="policyForm.quota_unit">
                        <option value="days">{{ __('Days') }}</option>
                        <option value="hours">{{ __('Hours') }}</option>
                    </flux:select>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input
                    type="number"
                    min="0"
                    label="{{ __('Probation Wait (days)') }}"
                    wire:model.defer="policyForm.probation_wait_days"
                />

                <div class="space-y-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:switch
                        label="{{ __('Auto Assign Quota') }}"
                        description="{{ __('Applies quota automatically after probation wait.') }}"
                        wire:model.defer="policyForm.auto_assign"
                    />
                </div>

                <div class="space-y-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:switch
                    label="{{ __('Prorate on Joining') }}"
                        description="{{ __('Distributes quota based on hire date in the first cycle.') }}"
                        wire:model.defer="policyForm.prorate_on_joining"
                    />
                </div>

                <div class="space-y-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:switch
                    label="{{ __('Allow Negative Balances') }}"
                        description="{{ __('Permits employees to go into deficit.') }}"
                        wire:model.defer="policyForm.allow_negative_balance"
                    />
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:switch
                        label="{{ __('Enable Carry Forward') }}"
                        description="{{ __('Unused leave will roll into the next period.') }}"
                        wire:model.defer="policyForm.carry_forward_enabled"
                    />

                    @if($policyForm['carry_forward_enabled'])
                        <flux:input
                            type="number"
                            step="0.5"
                            min="0"
                            label="{{ __('Carry Forward Cap') }}"
                            placeholder="{{ __('Leave empty for unlimited') }}"
                            wire:model.defer="policyForm.carry_forward_cap"
                        />

                        <flux:input
                            type="number"
                            min="0"
                            label="{{ __('Carry Forward Expiry (days)') }}"
                            placeholder="{{ __('Leave empty to never expire') }}"
                            wire:model.defer="policyForm.carry_forward_expiry_days"
                        />
                    @endif
                </div>

                <div class="space-y-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:switch
                        label="{{ __('Enable Encashment') }}"
                        description="{{ __('Allow converting remaining leave into pay.') }}"
                        wire:model.defer="policyForm.encashment_enabled"
                    />

                    @if($policyForm['encashment_enabled'])
                        <flux:input
                            type="number"
                            step="0.5"
                            min="0"
                            label="{{ __('Encashment Cap') }}"
                            placeholder="{{ __('Maximum encashable units') }}"
                            wire:model.defer="policyForm.encashment_cap"
                        />
                    @endif
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('Service-based increments') }}
                        </h4>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('Add automatic increases to the base quota based on years of service.') }}
                        </p>
                    </div>
                    <flux:button variant="outline" size="sm" icon="plus" wire:click="addTierRow">
                        {{ __('Add Tier') }}
                    </flux:button>
                </div>

                @if(empty($tiers))
                    <div class="border border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg px-4 py-6 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                        {{ __('No tiers defined. Add a tier to increase quota after certain service years.') }}
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($tiers as $index => $tier)
                            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]" wire:key="tier-row-{{ $index }}">
                                <flux:input
                                    type="number"
                                    min="1"
                                    label="{{ __('Year of Service') }}"
                                    wire:model.defer="tiers.{{ $index }}.year_of_service"
                                    required
                                />
                                <flux:input
                                    type="number"
                                    step="0.5"
                                    label="{{ __('Additional Quota') }}"
                                    wire:model.defer="tiers.{{ $index }}.additional_quota"
                                />
                                <flux:button
                                    variant="ghost"
                                    icon="trash"
                                    class="self-end"
                                    wire:click="removeTierRow({{ $index }})"
                                />
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-between w-full pt-4">
            <flux:button variant="ghost" wire:click="$set('showPolicyModal', false)">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button variant="primary" wire:click="savePolicy">
                {{ $editingPolicyId ? __('Update Policy') : __('Create Policy') }}
            </flux:button>
        </div>
    </flux:modal>
</section>