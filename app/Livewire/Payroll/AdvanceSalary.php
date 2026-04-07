<?php

namespace App\Livewire\Payroll;

use App\Models\AdvanceSalaryRequest;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AdvanceSalary extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $status = '';
    public $requestPeriod = 'current_month';
    public $customDateFrom = '';
    public $customDateTo = '';
    public $confirmationStatus = '';
    public $showAddAdvanceModal = false;
    public $showEditAdvanceModal = false;
    public $showViewRequestModal = false;
    public $selectedRequestId = null;
    public $editingRequestId = null;
    public $sortBy = '';
    public $sortDirection = 'desc';

    /** Request Advance form (flyout) */
    public $selectedEmployeeId = '';
    public $advanceAmount = '';
    public $advanceReason = '';
    public $expectedPaybackDate = '';
    public $advanceMonths = '1';
    public $paybackMode = 'all_at_once';
    public $expectedReceivingDate = '';

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('salary.edit')) {
            abort(403);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedRequestPeriod()
    {
        $this->resetPage();
    }

    public function updatedCustomDateFrom()
    {
        $this->resetPage();
    }

    public function updatedCustomDateTo()
    {
        $this->resetPage();
    }

    public function updatedConfirmationStatus()
    {
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openAddAdvanceModal()
    {
        $this->authorizeAdvanceRequest();
        $this->selectedEmployeeId = '';
        $this->advanceAmount = '';
        $this->advanceReason = '';
        $this->expectedPaybackDate = now()->format('Y-m-d');
        $this->advanceMonths = '1';
        $this->paybackMode = 'all_at_once';
        $this->expectedReceivingDate = '';
        $this->showAddAdvanceModal = true;
    }

    public function closeAddAdvanceModal()
    {
        $this->showAddAdvanceModal = false;
    }

    public function openEditAdvanceModal(int $id): void
    {
        $this->authorizeAdvanceRequest();
        $request = AdvanceSalaryRequest::find($id);
        if (!$request) {
            session()->flash('error', __('Request not found.'));
            return;
        }
        if ($request->confirmed_at) {
            session()->flash('error', __('Confirmed advance requests cannot be edited.'));
            return;
        }

        $this->editingRequestId = $request->id;
        $this->selectedEmployeeId = (string) $request->employee_id;
        $this->advanceAmount = (string) ((float) $request->amount);
        $this->advanceReason = (string) ($request->reason ?? '');
        $this->expectedPaybackDate = $request->expected_payback_date ? $request->expected_payback_date->format('Y-m-d') : '';
        $this->advanceMonths = (string) max(1, min(12, (int) ($request->payback_months ?? 1)));
        $this->paybackMode = (string) ($request->payback_mode ?? 'all_at_once');
        $this->expectedReceivingDate = $request->expected_receiving_date ? $request->expected_receiving_date->format('Y-m-d') : '';
        $this->showEditAdvanceModal = true;
    }

    public function closeEditAdvanceModal(): void
    {
        $this->showEditAdvanceModal = false;
        $this->editingRequestId = null;
        $this->expectedReceivingDate = '';
    }

    public function addAdvanceSalary()
    {
        $this->authorizeAdvanceRequest();

        $employeeId = (int) $this->selectedEmployeeId;
        $amount = (float) $this->advanceAmount;
        $months = max(1, min(12, (int) $this->advanceMonths));
        $paybackMode = (string) $this->paybackMode;
        $expectedReceivingDate = $this->expectedReceivingDate ?: null;
        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }
        if ($amount <= 0) {
            session()->flash('error', __('Amount must be greater than zero.'));
            return;
        }
        if (!$this->expectedPaybackDate) {
            session()->flash('error', __('Please select a payback month/date.'));
            return;
        }
        if ($months > 1) {
            $allowedModes = ['all_at_once', 'divide_by_months'];
            if (!in_array($paybackMode, $allowedModes, true)) {
                session()->flash('error', __('Please select a valid payback mode.'));
                return;
            }
        } else {
            $paybackMode = 'all_at_once';
        }

        AdvanceSalaryRequest::create([
            'employee_id' => $employeeId,
            'amount' => $amount,
            'reason' => trim((string) $this->advanceReason),
            'expected_payback_date' => $this->expectedPaybackDate ?: null,
            'payback_transaction_type' => 'deduct_from_salary',
            'payback_months' => $months,
            'payback_mode' => $paybackMode,
            'expected_receiving_date' => $expectedReceivingDate,
            'status' => AdvanceSalaryRequest::STATUS_PENDING,
            'requested_by' => Auth::id(),
        ]);

        $this->closeAddAdvanceModal();
        session()->flash('message', __('Advance salary request submitted successfully.'));
    }

    public function updateAdvanceSalary(): void
    {
        $this->authorizeAdvanceRequest();
        $request = AdvanceSalaryRequest::find($this->editingRequestId);
        if (!$request) {
            session()->flash('error', __('Request not found.'));
            return;
        }
        if ($request->confirmed_at) {
            session()->flash('error', __('Confirmed advance requests cannot be edited.'));
            return;
        }

        $employeeId = (int) $this->selectedEmployeeId;
        $amount = (float) $this->advanceAmount;
        $months = max(1, min(12, (int) $this->advanceMonths));
        $paybackMode = (string) $this->paybackMode;
        $expectedReceivingDate = $this->expectedReceivingDate ?: null;

        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }
        if ($amount <= 0) {
            session()->flash('error', __('Amount must be greater than zero.'));
            return;
        }
        if (!$this->expectedPaybackDate) {
            session()->flash('error', __('Please select a payback month/date.'));
            return;
        }
        if ($months > 1) {
            $allowedModes = ['all_at_once', 'divide_by_months'];
            if (!in_array($paybackMode, $allowedModes, true)) {
                session()->flash('error', __('Please select a valid payback mode.'));
                return;
            }
        } else {
            $paybackMode = 'all_at_once';
        }

        $request->update([
            'employee_id' => $employeeId,
            'amount' => $amount,
            'reason' => trim((string) $this->advanceReason),
            'expected_payback_date' => $this->expectedPaybackDate ?: null,
            'payback_transaction_type' => 'deduct_from_salary',
            'payback_months' => $months,
            'payback_mode' => $paybackMode,
            'expected_receiving_date' => $expectedReceivingDate,
        ]);

        $this->closeEditAdvanceModal();
        session()->flash('message', __('Advance salary request updated successfully.'));
    }

    public function approveAdvance($id)
    {
        $this->authorizeAdvanceManagement();
        $request = AdvanceSalaryRequest::find($id);
        if ($request && $request->isPending()) {
            $request->update([
                'status' => AdvanceSalaryRequest::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            session()->flash('message', __('Advance salary approved successfully.'));
        } else {
            session()->flash('error', __('Request not found or already processed.'));
        }
    }

    public function rejectAdvance($id)
    {
        $this->authorizeAdvanceManagement();
        $request = AdvanceSalaryRequest::find($id);
        if ($request && $request->isPending()) {
            $request->update([
                'status' => AdvanceSalaryRequest::STATUS_REJECTED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            session()->flash('message', __('Advance salary rejected.'));
        } else {
            session()->flash('error', __('Request not found or already processed.'));
        }
    }

    public function viewRequest(int $id): void
    {
        $this->selectedRequestId = $id;
        $this->showViewRequestModal = true;
    }

    public function closeViewRequestModal(): void
    {
        $this->showViewRequestModal = false;
        $this->selectedRequestId = null;
    }

    public function confirmTransaction(int $id): void
    {
        $this->authorizeAdvanceManagement();
        $request = AdvanceSalaryRequest::find($id);
        if (!$request) {
            session()->flash('error', __('Request not found.'));
            return;
        }
        if ($request->status !== AdvanceSalaryRequest::STATUS_APPROVED) {
            session()->flash('error', __('Only approved advances can be confirmed.'));
            return;
        }
        if ($request->confirmed_at) {
            session()->flash('error', __('This transaction is already confirmed.'));
            return;
        }
        $type = (string) ($request->payback_transaction_type ?? 'deduct_from_salary');
        if ($type !== 'deduct_from_salary') {
            session()->flash('error', __('Use "Confirm Received" for cash or account transfer requests.'));
            return;
        }
        $request->update(['confirmed_at' => now()]);
        session()->flash('message', __('Transaction confirmed successfully.'));
    }

    public function confirmReceived(int $id): void
    {
        $this->authorizeAdvanceManagement();
        $request = AdvanceSalaryRequest::find($id);
        if (!$request) {
            session()->flash('error', __('Request not found.'));
            return;
        }
        if ($request->status !== AdvanceSalaryRequest::STATUS_APPROVED) {
            session()->flash('error', __('Only approved advances can be marked as received.'));
            return;
        }
        $type = (string) ($request->payback_transaction_type ?? 'deduct_from_salary');
        if (!in_array($type, ['cash', 'account_transfer'], true)) {
            session()->flash('error', __('Confirm received is only for cash/account transfer requests.'));
            return;
        }
        if ($request->received_at) {
            session()->flash('error', __('This payback is already marked as received.'));
            return;
        }

        $request->update([
            'received_at' => now(),
            'received_amount' => (float) $request->amount,
        ]);
        session()->flash('message', __('Payback marked as received.'));
    }

    public function deleteRequest(int $id): void
    {
        $this->authorizeAdvanceManagement();
        $request = AdvanceSalaryRequest::find($id);
        if (!$request) {
            session()->flash('error', __('Request not found.'));
            return;
        }
        if ($request->confirmed_at) {
            session()->flash('error', __('Confirmed requests cannot be deleted.'));
            return;
        }

        $request->delete();
        session()->flash('message', __('Advance salary request deleted successfully.'));
    }

    public function getSelectedAdvanceRequestProperty(): ?AdvanceSalaryRequest
    {
        if (!$this->selectedRequestId) {
            return null;
        }
        return AdvanceSalaryRequest::with(['employee', 'requestedByUser', 'approvedByUser'])
            ->find($this->selectedRequestId);
    }

    public function getMonthlyPaybackAmountProperty(): float
    {
        $amount = (float) $this->advanceAmount;
        $months = max(1, min(12, (int) $this->advanceMonths));
        if ($amount <= 0) {
            return 0.0;
        }
        if ($months <= 1 || $this->paybackMode === 'all_at_once') {
            return round($amount, 2);
        }

        return round($amount / $months, 2);
    }

    protected function authorizeAdvanceRequest(): void
    {
        $user = Auth::user();

        if (!$user || (!$user->can('payroll.advance.manage') && !$user->can('payroll.advance.request'))) {
            abort(403);
        }
    }

    protected function authorizeAdvanceManagement(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.advance.manage')) {
            abort(403);
        }
    }

    public function render()
    {
        $query = AdvanceSalaryRequest::query()
            ->with(['employee.department'])
            ->when($this->search !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->whereHas('employee', function ($q2) use ($term) {
                    $q2->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('employee_code', 'like', $term);
                });
            })
            ->when($this->selectedDepartment !== '', function ($q) {
                $q->whereHas('employee', function ($q2) {
                    $q2->whereHas('department', function ($q3) {
                        $q3->where('title', $this->selectedDepartment);
                    });
                });
            })
            ->when($this->status !== '', function ($q) {
                $q->where('status', $this->status);
            })
            ->when($this->requestPeriod === 'current_month', function ($q) {
                $q->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            })
            ->when($this->requestPeriod === 'last_month', function ($q) {
                $start = now()->subMonthNoOverflow()->startOfMonth();
                $end = now()->subMonthNoOverflow()->endOfMonth();
                $q->whereBetween('created_at', [$start, $end]);
            })
            ->when($this->requestPeriod === 'custom', function ($q) {
                if ($this->customDateFrom) {
                    $q->whereDate('created_at', '>=', $this->customDateFrom);
                }
                if ($this->customDateTo) {
                    $q->whereDate('created_at', '<=', $this->customDateTo);
                }
            })
            ->when($this->confirmationStatus !== '', function ($q) {
                if ($this->confirmationStatus === 'confirmed') {
                    $q->where(function ($q2) {
                        $q2->where(function ($x) {
                            $x->where('payback_transaction_type', 'deduct_from_salary')->whereNotNull('confirmed_at');
                        })->orWhere(function ($x) {
                            $x->whereIn('payback_transaction_type', ['cash', 'account_transfer'])->whereNotNull('received_at');
                        });
                    });
                } elseif ($this->confirmationStatus === 'unconfirmed') {
                    $q->where(function ($q2) {
                        $q2->where(function ($x) {
                            $x->where('payback_transaction_type', 'deduct_from_salary')->whereNull('confirmed_at');
                        })->orWhere(function ($x) {
                            $x->whereIn('payback_transaction_type', ['cash', 'account_transfer'])->whereNull('received_at');
                        });
                    });
                }
            });

        $sortField = $this->sortBy ?: 'created_at';
        $sortDir = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['amount', 'request_date', 'status', 'created_at', 'employee_name', 'department'];
        if (in_array($sortField, $allowedSort, true)) {
            if ($sortField === 'employee_name') {
                $query->join('employees', 'advance_salary_requests.employee_id', '=', 'employees.id')
                    ->orderByRaw('CONCAT(employees.first_name, " ", employees.last_name) ' . $sortDir)
                    ->select('advance_salary_requests.*');
            } elseif ($sortField === 'department') {
                $query->join('employees', 'advance_salary_requests.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                    ->orderBy('departments.title', $sortDir)
                    ->select('advance_salary_requests.*');
            } else {
                $orderCol = $sortField === 'request_date' ? 'created_at' : $sortField;
                $query->orderBy($orderCol, $sortDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $advanceRequests = $query->paginate(15);

        $departments = Department::where('status', 'active')
            ->orderBy('title')
            ->pluck('title')
            ->toArray();

        $activeEmployees = Employee::where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'label' => trim($e->first_name . ' ' . $e->last_name) . ' (' . ($e->employee_code ?? '') . ')',
            ])
            ->toArray();

        $statuses = [
            AdvanceSalaryRequest::STATUS_PENDING,
            AdvanceSalaryRequest::STATUS_APPROVED,
            AdvanceSalaryRequest::STATUS_REJECTED,
        ];

        return view('livewire.payroll.advance-salary', [
            'advanceRequests' => $advanceRequests,
            'departments' => $departments,
            'statuses' => $statuses,
            'activeEmployees' => $activeEmployees,
        ])->layout('components.layouts.app');
    }
}
