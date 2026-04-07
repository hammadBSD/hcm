<?php

namespace App\Livewire\Payroll;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LoanManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $loanStatus = '';
    public $showAddLoanModal = false;
    public $showViewLoanModal = false;
    public $showApproveLoanModal = false;
    public $showRejectLoanModal = false;
    public $selectedLoanId = null;
    public $sortBy = '';
    public $sortDirection = 'asc';

    /** Add Loan form (flyout) */
    public $selectedEmployeeId = '';
    public $loanType = 'Personal';
    public $loanAmount = '';
    public $totalInstallments = '12';
    public $loanDate = '';
    public $loanDescription = '';
    public $approvalDate = '';
    public $approvalComments = '';
    public $rejectDate = '';
    public $rejectComments = '';

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

    public function updatedLoanStatus()
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

    public function openAddLoanModal()
    {
        $this->authorizeLoanRequest();
        $this->selectedEmployeeId = '';
        $this->loanType = 'Personal';
        $this->loanAmount = '';
        $this->totalInstallments = '12';
        $this->loanDate = now()->format('Y-m-d');
        $this->loanDescription = '';
        $this->showAddLoanModal = true;
    }

    public function closeAddLoanModal()
    {
        $this->showAddLoanModal = false;
        $this->loanDate = '';
    }

    public function addLoan()
    {
        $this->authorizeLoanRequest();

        $employeeId = (int) $this->selectedEmployeeId;
        $amount = (float) $this->loanAmount;
        $installments = (int) $this->totalInstallments;
        $loanDate = trim((string) $this->loanDate);
        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }
        if ($amount <= 0) {
            session()->flash('error', __('Loan amount must be greater than zero.'));
            return;
        }
        if ($installments < 1) {
            session()->flash('error', __('Number of installments must be at least 1.'));
            return;
        }
        if ($loanDate === '') {
            session()->flash('error', __('Please select a loan issue date.'));
            return;
        }

        $installmentAmount = round($amount / $installments, 2);

        Loan::create([
            'employee_id' => $employeeId,
            'loan_type' => $this->loanType,
            'loan_amount' => $amount,
            'installment_amount' => $installmentAmount,
            'total_installments' => $installments,
            'remaining_installments' => $installments,
            'loan_date' => $loanDate,
            'description' => trim((string) $this->loanDescription),
            'status' => Loan::STATUS_PENDING,
            'requested_by' => Auth::id(),
        ]);

        $this->closeAddLoanModal();
        session()->flash('message', __('Loan request submitted successfully.'));
    }

    public function approveLoan($id)
    {
        $this->authorizeLoanManagement();
        $loan = Loan::find($id);
        if (!$loan || !$loan->isPending()) {
            session()->flash('error', __('Loan not found or already processed.'));
            return;
        }

        $this->selectedLoanId = (int) $id;
        $this->approvalDate = now()->format('Y-m-d');
        $this->approvalComments = '';
        $this->showApproveLoanModal = true;
    }

    public function closeApproveLoanModal(): void
    {
        $this->showApproveLoanModal = false;
        $this->selectedLoanId = null;
        $this->approvalDate = '';
        $this->approvalComments = '';
    }

    public function confirmApproveLoan(): void
    {
        $this->authorizeLoanManagement();
        $loan = Loan::find($this->selectedLoanId);
        if (!$loan || !$loan->isPending()) {
            session()->flash('error', __('Loan not found or already processed.'));
            $this->closeApproveLoanModal();
            return;
        }
        if (trim((string) $this->approvalDate) === '') {
            session()->flash('error', __('Please select an approval date.'));
            return;
        }

        $loan->update([
            'status' => Loan::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => $this->approvalDate . ' 00:00:00',
            'decision_comments' => trim((string) $this->approvalComments),
        ]);
        $this->closeApproveLoanModal();
        session()->flash('message', __('Loan approved successfully.'));
    }

    public function rejectLoan($id)
    {
        $this->authorizeLoanManagement();
        $loan = Loan::find($id);
        if (!$loan || !$loan->isPending()) {
            session()->flash('error', __('Loan not found or already processed.'));
            return;
        }

        $this->selectedLoanId = (int) $id;
        $this->rejectDate = now()->format('Y-m-d');
        $this->rejectComments = '';
        $this->showRejectLoanModal = true;
    }

    public function closeRejectLoanModal(): void
    {
        $this->showRejectLoanModal = false;
        $this->selectedLoanId = null;
        $this->rejectDate = '';
        $this->rejectComments = '';
    }

    public function confirmRejectLoan(): void
    {
        $this->authorizeLoanManagement();
        $loan = Loan::find($this->selectedLoanId);
        if (!$loan || !$loan->isPending()) {
            session()->flash('error', __('Loan not found or already processed.'));
            $this->closeRejectLoanModal();
            return;
        }
        if (trim((string) $this->rejectDate) === '') {
            session()->flash('error', __('Please select a rejection date.'));
            return;
        }

        $loan->update([
            'status' => Loan::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => $this->rejectDate . ' 00:00:00',
            'decision_comments' => trim((string) $this->rejectComments),
        ]);
        $this->closeRejectLoanModal();
        session()->flash('message', __('Loan rejected.'));
    }

    public function viewLoan($id)
    {
        $loan = Loan::find($id);
        if (!$loan) {
            session()->flash('error', __('Loan record not found.'));
            return;
        }

        $this->selectedLoanId = (int) $id;
        $this->showViewLoanModal = true;
    }

    public function closeViewLoanModal(): void
    {
        $this->showViewLoanModal = false;
        $this->selectedLoanId = null;
    }

    public function getSelectedLoanProperty(): ?Loan
    {
        if (!$this->selectedLoanId) {
            return null;
        }

        return Loan::with(['employee.department', 'requestedByUser', 'approvedByUser'])->find($this->selectedLoanId);
    }

    protected function authorizeLoanRequest(): void
    {
        $user = Auth::user();

        if (!$user || (!$user->can('payroll.loan.manage') && !$user->can('payroll.loan.request'))) {
            abort(403);
        }
    }

    protected function authorizeLoanManagement(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.loan.manage')) {
            abort(403);
        }
    }

    public function render()
    {
        $query = Loan::query()
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
            ->when($this->loanStatus !== '', function ($q) {
                $q->where('status', $this->loanStatus);
            });

        $sortField = $this->sortBy ?: 'created_at';
        $sortDir = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['loan_amount', 'installment_amount', 'status', 'created_at', 'loan_type', 'employee_name', 'department', 'loan_date'];
        if (in_array($sortField, $allowedSort, true)) {
            if ($sortField === 'employee_name') {
                $query->join('employees', 'loans.employee_id', '=', 'employees.id')
                    ->orderByRaw('CONCAT(employees.first_name, " ", employees.last_name) ' . $sortDir)
                    ->select('loans.*');
            } elseif ($sortField === 'department') {
                $query->join('employees', 'loans.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                    ->orderBy('departments.title', $sortDir)
                    ->select('loans.*');
            } else {
                $query->orderBy($sortField, $sortDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $loans = $query->paginate(15);

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

        $loanTypes = ['Personal', 'Housing', 'Vehicle', 'Education', 'Medical'];
        $statuses = [Loan::STATUS_PENDING, Loan::STATUS_APPROVED, Loan::STATUS_REJECTED, Loan::STATUS_COMPLETED];

        return view('livewire.payroll.loan-management', [
            'loans' => $loans,
            'departments' => $departments,
            'loanTypes' => $loanTypes,
            'statuses' => $statuses,
            'activeEmployees' => $activeEmployees,
        ])->layout('components.layouts.app');
    }
}
