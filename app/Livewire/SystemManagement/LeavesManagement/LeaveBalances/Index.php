<?php

namespace App\Livewire\SystemManagement\LeavesManagement\LeaveBalances;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeavePolicy;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $leaveTypeFilter = 'all';
    public $perPage = 10;

    public $showAdjustmentModal = false;
    public $selectedBalanceId = null;
    public $adjustmentForm = [
        'amount' => null,
        'notes' => '',
    ];

    public $isGenerating = false;
    public $generationSummary = null;

    public $leaveTypes = [];
    public $selectedBalanceSummary = null;
    public $skipReasons = [];

    protected $listeners = [
        'refreshLeaveBalances' => '$refresh',
    ];

    public function mount(): void
    {
        $this->leaveTypes = LeaveType::orderBy('name')->get();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingLeaveTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function openAdjustmentModal(int $balanceId): void
    {
        $balance = EmployeeLeaveBalance::with([
            'employee.user:id,name,email',
            'leaveType:id,name,code',
        ])->findOrFail($balanceId);

        $this->selectedBalanceId = $balance->id;
        $this->adjustmentForm = [
            'amount' => null,
            'notes' => '',
        ];
        $this->selectedBalanceSummary = [
            'employee_name' => optional($balance->employee->user)->name ?? __('Unknown Employee'),
            'employee_email' => optional($balance->employee->user)->email,
            'leave_type_name' => optional($balance->leaveType)->name ?? __('N/A'),
            'leave_type_code' => optional($balance->leaveType)->code,
            'entitled' => $balance->entitled,
            'balance' => $balance->balance,
        ];
        $this->showAdjustmentModal = true;
    }

    public function applyAdjustment(): void
    {
        $this->validate([
            'selectedBalanceId' => ['required', 'exists:employee_leave_balances,id'],
            'adjustmentForm.amount' => ['required', 'numeric', 'not_in:0'],
            'adjustmentForm.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $balance = EmployeeLeaveBalance::with(['employee', 'leaveType'])->findOrFail($this->selectedBalanceId);
        $amount = (float) $this->adjustmentForm['amount'];

        DB::transaction(function () use ($balance, $amount) {
            $balance->manual_adjustment += $amount;
            $balance->balance += $amount;
            $balance->save();

            LeaveBalanceTransaction::create([
                'employee_id' => $balance->employee_id,
                'leave_type_id' => $balance->leave_type_id,
                'leave_policy_id' => $balance->leave_policy_id,
                'related_request_id' => null,
                'reference' => 'MANUAL-ADJUST-' . now()->format('Ymd-His'),
                'transaction_type' => 'adjustment',
                'amount' => $amount,
                'balance_after' => $balance->balance,
                'notes' => $this->adjustmentForm['notes'] ?: __('Manual adjustment via admin console'),
                'meta' => [
                    'source' => 'system-management',
                ],
                'performed_by' => Auth::id(),
                'transaction_date' => now(),
            ]);
        });

        $balance->refresh();
        if ($this->selectedBalanceSummary) {
            $this->selectedBalanceSummary['entitled'] = $balance->entitled;
            $this->selectedBalanceSummary['balance'] = $balance->balance;
        }

        $this->dispatch('notify', type: 'success', message: __('Balance adjustment recorded successfully.'));
        $this->closeAdjustmentFlyout();
    }

    public function generateBalances(): void
    {
        if ($this->isGenerating) {
            return;
        }

        $this->skipReasons = [];
        $this->isGenerating = true;
        $this->generationSummary = null;

        try {
            $policies = LeavePolicy::query()
                ->where('auto_assign', true)
                ->with('leaveType')
                ->get();

            if ($policies->isEmpty()) {
                $this->dispatch('notify', type: 'warning', message: __('No auto-assigned leave policies found. Create a policy first.'));
                return;
            }

            $created = 0;
            $updated = 0;
            $skipped = 0;

            DB::transaction(function () use ($policies, &$created, &$updated, &$skipped) {
                $now = Carbon::now();

                $employees = Employee::query()
                    ->where('status', 'active')
                    ->with([
                        'organizationalInfo',
                        'leaveBalances',
                        'user:id,name,email',
                    ])
                    ->whereHas('user') // ensure linked user exists
                    ->get();

                foreach ($policies as $policy) {
                    $leaveType = $policy->leaveType;

                    if (! $leaveType) {
                        continue;
                    }

                    $cycleStart = Carbon::parse($policy->effective_from);
                    $cycleEnd = $policy->effective_to
                        ? Carbon::parse($policy->effective_to)
                        : $cycleStart->copy()->addYear()->subDay();

                    foreach ($employees as $employee) {
                        $orgInfo = $employee->organizationalInfo;

                        if (! $orgInfo || ! $orgInfo->joining_date) {
                            $skipped++;
                            $this->skipReasons[] = [
                                'employee_id' => $employee->id,
                                'employee_name' => optional($employee->user)->name,
                                'reason' => 'missing_joining_date',
                            ];
                            continue;
                        }

                        if (! empty($policy->assign_only_to_permanent) && ($orgInfo->employee_status ?? '') !== 'permanent') {
                            $skipped++;
                            $this->skipReasons[] = [
                                'employee_id' => $employee->id,
                                'employee_name' => optional($employee->user)->name,
                                'reason' => 'not_permanent_employee',
                            ];
                            continue;
                        }

                        $joiningDate = Carbon::parse($orgInfo->joining_date);

                        $eligibleDate = $orgInfo->confirmation_date
                            ? Carbon::parse($orgInfo->confirmation_date)
                            : $joiningDate->copy()->addDays((int) $policy->probation_wait_days);

                        if ($eligibleDate->greaterThan($now)) {
                            // Employee is still in probation - remove/clear their leave balance if it exists
                            $existing = $employee->leaveBalances
                                ->firstWhere('leave_type_id', $leaveType->id);
                            
                            if ($existing) {
                                // Clear entitled leave for employees in probation
                                // Balance will be negative if they have used/pending leaves (which is correct)
                                $existing->entitled = 0;
                                $existing->balance = round(
                                    ($existing->entitled + $existing->carried_forward + $existing->manual_adjustment)
                                    - ($existing->used + $existing->pending),
                                    2
                                );
                                $existing->save();
                                $updated++; // Count as updated since we're clearing it
                            }
                            
                            $skipped++;
                            $this->skipReasons[] = [
                                'employee_id' => $employee->id,
                                'employee_name' => optional($employee->user)->name,
                                'reason' => 'still_in_probation',
                                'eligible_after' => $eligibleDate->toDateString(),
                            ];
                            continue;
                        }

                        $allocationStart = $eligibleDate->greaterThan($cycleStart)
                            ? $eligibleDate->copy()
                            : $cycleStart->copy();

                        if ($allocationStart->greaterThan($cycleEnd)) {
                            $skipped++;
                            $this->skipReasons[] = [
                                'employee_id' => $employee->id,
                                'employee_name' => optional($employee->user)->name,
                                'reason' => 'outside_policy_window',
                            ];
                            continue;
                        }

                        $entitled = (float) $policy->base_quota;

                        if ($policy->prorate_on_joining) {
                            $totalDays = max($cycleStart->diffInDays($cycleEnd) + 1, 1);
                            $eligibleDays = $allocationStart->diffInDays($cycleEnd) + 1;
                            $ratio = $eligibleDays / $totalDays;
                            $entitled = round($policy->base_quota * $ratio, 2);
                        }

                        if ($entitled < 0) {
                            $entitled = 0;
                        }

                        /** @var \App\Models\EmployeeLeaveBalance|null $existing */
                        $existing = $employee->leaveBalances
                            ->firstWhere('leave_type_id', $leaveType->id);

                        $payload = [
                            'leave_policy_id' => $policy->id,
                            'entitled' => $entitled,
                        ];

                        if ($existing) {
                            $existing->fill($payload);
                            $existing->balance = round(
                                ($existing->entitled + $existing->carried_forward + $existing->manual_adjustment)
                                - ($existing->used + $existing->pending),
                                2
                            );
                            $existing->save();
                            $updated++;
                        } else {
                            $payload = array_merge($payload, [
                                'carried_forward' => 0,
                                'manual_adjustment' => 0,
                                'used' => 0,
                                'pending' => 0,
                                'balance' => $entitled,
                                'metadata' => [
                                    'generated_at' => Carbon::now()->toDateTimeString(),
                                    'generated_by' => Auth::id(),
                                ],
                            ]);

                            EmployeeLeaveBalance::create([
                                'employee_id' => $employee->id,
                                'leave_type_id' => $leaveType->id,
                                ...$payload,
                            ]);

                            $created++;
                        }
                    }
                }
            });

            $this->generationSummary = [
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
            ];

            $this->dispatch('notify', type: 'success', message: __('Leave balances generated successfully.'));
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: __('Failed to generate leave balances. Please check the logs.'));
        } finally {
            $this->isGenerating = false;
            $this->dispatch('refreshLeaveBalances');
        }
    }

    public function closeAdjustmentFlyout(): void
    {
        $this->showAdjustmentModal = false;
    }

    public function updatedShowAdjustmentModal($value): void
    {
        if (! $value) {
            $this->selectedBalanceId = null;
            $this->selectedBalanceSummary = null;
            $this->adjustmentForm = [
                'amount' => null,
                'notes' => '',
            ];
        }
    }

    public function getBalancesProperty()
    {
        $query = EmployeeLeaveBalance::query()
            ->with([
                'employee.user:id,name,email',
                'leaveType:id,name,code',
            ]);

        $query->whereHas('employee', function ($builder) {
            $builder->where('status', 'active');
        });

        if ($this->leaveTypeFilter !== 'all') {
            $query->where('leave_type_id', $this->leaveTypeFilter);
        }

        if ($this->search) {
            $term = '%' . trim($this->search) . '%';
            $query->whereHas('employee.user', function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        return $query
            ->orderBy('employee_id')
            ->orderBy('leave_type_id')
            ->get()
            ->groupBy('employee_id');
    }

    public function getGroupedBalancesProperty()
    {
        $balances = $this->balances;
        $perPage = $this->perPage;
        
        // Get all unique employee IDs
        $employeeIds = $balances->keys()->toArray();
        
        // Calculate pagination
        $total = count($employeeIds);
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($employeeIds, $offset, $perPage);
        
        // Build paginated grouped data
        $grouped = collect($items)->mapWithKeys(function ($employeeId) use ($balances) {
            return [$employeeId => $balances->get($employeeId)];
        });
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $grouped,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function render()
    {
        return view('livewire.system-management.leaves-management.leave-balances.index', [
            'groupedBalances' => $this->groupedBalances,
        ])->layout('components.layouts.app');
    }
}

