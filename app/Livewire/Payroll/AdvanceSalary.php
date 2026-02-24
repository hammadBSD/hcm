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
    public $showAddAdvanceModal = false;
    public $sortBy = '';
    public $sortDirection = 'asc';

    /** Request Advance form (flyout) */
    public $selectedEmployeeId = '';
    public $advanceAmount = '';
    public $advanceReason = '';
    public $expectedPaybackDate = '';

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
        $this->expectedPaybackDate = '';
        $this->showAddAdvanceModal = true;
    }

    public function closeAddAdvanceModal()
    {
        $this->showAddAdvanceModal = false;
    }

    public function addAdvanceSalary()
    {
        $this->authorizeAdvanceRequest();

        $employeeId = (int) $this->selectedEmployeeId;
        $amount = (float) $this->advanceAmount;
        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }
        if ($amount <= 0) {
            session()->flash('error', __('Amount must be greater than zero.'));
            return;
        }

        AdvanceSalaryRequest::create([
            'employee_id' => $employeeId,
            'amount' => $amount,
            'reason' => trim((string) $this->advanceReason),
            'expected_payback_date' => $this->expectedPaybackDate ?: null,
            'status' => AdvanceSalaryRequest::STATUS_PENDING,
            'requested_by' => Auth::id(),
        ]);

        $this->closeAddAdvanceModal();
        session()->flash('message', __('Advance salary request submitted successfully.'));
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

    public function viewRequest($id)
    {
        session()->flash('message', __('View not implemented for this request.'));
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
