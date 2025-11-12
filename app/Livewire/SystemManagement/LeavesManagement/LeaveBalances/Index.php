<?php

namespace App\Livewire\SystemManagement\LeavesManagement\LeaveBalances;

use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveType;
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

    public $leaveTypes = [];

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
        $this->selectedBalanceId = $balanceId;
        $this->adjustmentForm = [
            'amount' => null,
            'notes' => '',
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

        $this->showAdjustmentModal = false;

        $this->dispatch('notify', type: 'success', message: __('Balance adjustment recorded successfully.'));
    }

    public function getBalancesProperty()
    {
        $query = EmployeeLeaveBalance::query()
            ->with([
                'employee.user:id,name,email',
                'leaveType:id,name,code',
            ]);

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
            ->orderByDesc('updated_at')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.system-management.leaves-management.leave-balances.index', [
            'balances' => $this->balances,
        ])->layout('components.layouts.app');
    }
}

