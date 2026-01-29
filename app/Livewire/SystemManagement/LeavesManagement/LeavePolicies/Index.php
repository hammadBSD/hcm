<?php

namespace App\Livewire\SystemManagement\LeavesManagement\LeavePolicies;

use App\Models\LeavePolicy;
use App\Models\LeavePolicyTier;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    public $leaveTypes = [];
    public $selectedTypeId;

    public $showPolicyModal = false;
    public $editingPolicyId = null;

    public $policyForm = [
        'effective_from' => null,
        'effective_to' => null,
        'accrual_frequency' => 'annual',
        'base_quota' => 0,
        'quota_unit' => 'days',
        'auto_assign' => true,
        'probation_wait_days' => 0,
        'prorate_on_joining' => true,
        'carry_forward_enabled' => false,
        'carry_forward_cap' => null,
        'carry_forward_expiry_days' => null,
        'encashment_enabled' => false,
        'encashment_cap' => null,
        'allow_negative_balance' => false,
    ];

    public $tiers = [];

    public function mount(): void
    {
        $this->leaveTypes = LeaveType::orderBy('name')->get();
        $this->selectedTypeId = $this->leaveTypes->first()->id ?? null;
    }

    public function selectType(int $typeId): void
    {
        $this->selectedTypeId = $typeId;
        $this->resetPolicyForm();
    }

    public function openCreatePolicyModal(): void
    {
        $this->editingPolicyId = null;
        $this->resetPolicyForm();
        $this->policyForm['effective_from'] = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->showPolicyModal = true;
    }

    public function openEditPolicyModal(int $policyId): void
    {
        $policy = LeavePolicy::with('tiers')->findOrFail($policyId);
        $this->editingPolicyId = $policy->id;

        $this->policyForm = [
            'effective_from' => optional($policy->effective_from)->format('Y-m-d'),
            'effective_to' => optional($policy->effective_to)->format('Y-m-d'),
            'accrual_frequency' => $policy->accrual_frequency,
            'base_quota' => $policy->base_quota,
            'quota_unit' => $policy->quota_unit,
            'auto_assign' => (bool) $policy->auto_assign,
            'probation_wait_days' => $policy->probation_wait_days,
            'prorate_on_joining' => (bool) $policy->prorate_on_joining,
            'carry_forward_enabled' => (bool) $policy->carry_forward_enabled,
            'carry_forward_cap' => $policy->carry_forward_cap,
            'carry_forward_expiry_days' => $policy->carry_forward_expiry_days,
            'encashment_enabled' => (bool) $policy->encashment_enabled,
            'encashment_cap' => $policy->encashment_cap,
            'allow_negative_balance' => (bool) $policy->allow_negative_balance,
        ];

        $this->tiers = $policy->tiers
            ->map(fn (LeavePolicyTier $tier) => [
                'id' => $tier->id,
                'year_of_service' => $tier->year_of_service,
                'additional_quota' => $tier->additional_quota,
            ])
            ->toArray();

        $this->showPolicyModal = true;
    }

    public function addTierRow(): void
    {
        $this->tiers[] = [
            'year_of_service' => count($this->tiers) + 1,
            'additional_quota' => 0,
        ];
    }

    public function removeTierRow($index): void
    {
        unset($this->tiers[$index]);
        $this->tiers = array_values($this->tiers);
    }

    public function savePolicy(): void
    {
        $this->validatePolicy();

        $payload = $this->policyForm;
        $payload['auto_assign'] = (bool) $payload['auto_assign'];
        $payload['prorate_on_joining'] = (bool) $payload['prorate_on_joining'];
        $payload['carry_forward_enabled'] = (bool) $payload['carry_forward_enabled'];
        $payload['encashment_enabled'] = (bool) $payload['encashment_enabled'];
        $payload['allow_negative_balance'] = (bool) $payload['allow_negative_balance'];
        if (! $payload['carry_forward_enabled']) {
            $payload['carry_forward_cap'] = null;
            $payload['carry_forward_expiry_days'] = null;
        }
        if (! $payload['encashment_enabled']) {
            $payload['encashment_cap'] = null;
        }

        $payload['carry_forward_cap'] = $payload['carry_forward_cap'] === '' ? null : $payload['carry_forward_cap'];
        $payload['carry_forward_expiry_days'] = $payload['carry_forward_expiry_days'] === '' ? null : $payload['carry_forward_expiry_days'];
        $payload['encashment_cap'] = $payload['encashment_cap'] === '' ? null : $payload['encashment_cap'];
        $payload['leave_type_id'] = $this->selectedTypeId;

        $policy = LeavePolicy::updateOrCreate(
            ['id' => $this->editingPolicyId],
            $payload
        );

        $tierPayloads = collect($this->tiers)
            ->filter(function ($tier) {
                return isset($tier['year_of_service'], $tier['additional_quota'])
                    && $tier['year_of_service'] !== ''
                    && $tier['additional_quota'] !== '';
            })
            ->map(function ($tier) {
                return [
                    'year_of_service' => (int) $tier['year_of_service'],
                    'additional_quota' => (float) $tier['additional_quota'],
                ];
            })
            ->values()
            ->toArray();

        $policy->tiers()->delete();

        if (! empty($tierPayloads)) {
            $policy->tiers()->createMany($tierPayloads);
        }

        $this->showPolicyModal = false;
        $this->resetPolicyForm();

        $this->dispatch(
            'notify',
            type: 'success',
            message: $this->editingPolicyId
                ? __('Policy updated successfully.')
                : __('Policy created successfully.')
        );
    }

    protected function validatePolicy(): void
    {
        $this->validate([
            'selectedTypeId' => ['required', Rule::exists('leave_types', 'id')],
            'policyForm.effective_from' => ['required', 'date'],
            'policyForm.effective_to' => ['nullable', 'date', 'after:policyForm.effective_from'],
            'policyForm.accrual_frequency' => ['required', Rule::in(['none', 'monthly', 'quarterly', 'semi-annual', 'annual'])],
            'policyForm.base_quota' => ['required', 'numeric', 'min:0'],
            'policyForm.quota_unit' => ['required', Rule::in(['days', 'hours'])],
            'policyForm.auto_assign' => ['boolean'],
            'policyForm.probation_wait_days' => ['nullable', 'integer', 'min:0'],
            'policyForm.prorate_on_joining' => ['boolean'],
            'policyForm.carry_forward_enabled' => ['boolean'],
            'policyForm.carry_forward_cap' => ['nullable', 'numeric', 'min:0'],
            'policyForm.carry_forward_expiry_days' => ['nullable', 'integer', 'min:0'],
            'policyForm.encashment_enabled' => ['boolean'],
            'policyForm.encashment_cap' => ['nullable', 'numeric', 'min:0'],
            'policyForm.allow_negative_balance' => ['boolean'],
            'tiers.*.year_of_service' => ['nullable', 'integer', 'min:1', 'distinct'],
            'tiers.*.additional_quota' => ['nullable', 'numeric'],
        ], [], [
            'tiers.*.year_of_service' => __('Years of service'),
            'tiers.*.additional_quota' => __('Additional quota'),
        ]);
    }

    protected function resetPolicyForm(): void
    {
        $this->policyForm = [
            'effective_from' => null,
            'effective_to' => null,
            'accrual_frequency' => 'annual',
            'base_quota' => 0,
            'quota_unit' => 'days',
            'auto_assign' => true,
            'probation_wait_days' => 0,
            'prorate_on_joining' => true,
            'carry_forward_enabled' => false,
            'carry_forward_cap' => null,
            'carry_forward_expiry_days' => null,
            'encashment_enabled' => false,
            'encashment_cap' => null,
            'allow_negative_balance' => false,
        ];

        $this->tiers = [];
        $this->editingPolicyId = null;
    }

    public function deletePolicy(int $policyId): void
    {
        $policy = LeavePolicy::where('leave_type_id', $this->selectedTypeId)->findOrFail($policyId);
        $policy->delete();

        $this->dispatch('notify', type: 'success', message: __('Policy deleted successfully.'));
    }

    public function togglePolicyStatus(int $policyId): void
    {
        $policy = LeavePolicy::where('leave_type_id', $this->selectedTypeId)->findOrFail($policyId);
        $policy->update(['is_active' => ! $policy->is_active]);

        $label = $policy->is_active ? __('Policy activated.') : __('Policy deactivated.');
        $this->dispatch('notify', type: 'success', message: $label);
    }

    public function getPoliciesProperty()
    {
        if (! $this->selectedTypeId) {
            return collect();
        }

        return LeavePolicy::where('leave_type_id', $this->selectedTypeId)
            ->with('tiers')
            ->orderByDesc('effective_from')
            ->get();
    }

    public function render()
    {
        return view('livewire.system-management.leaves-management.leave-policies.index', [
            'policies' => $this->policies,
        ])->layout('components.layouts.app');
    }
}

