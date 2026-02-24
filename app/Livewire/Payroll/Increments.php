<?php

namespace App\Livewire\Payroll;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeIncrement;
use App\Models\EmployeeSalaryLegalCompliance;
use App\Services\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Increments extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $showAddIncrementModal = false;
    public $showViewIncrementModal = false;
    public $showEditIncrementModal = false;

    /** View/Edit: selected increment record */
    public $selectedIncrementId = null;

    /** Add Increment form */
    public $selectedEmployeeId = '';
    public $numberOfIncrements = '0';
    public $incrementDueDate = '';
    public $lastIncrementDate = '';
    public $lastIncrementAmount = '';
    public $timeSinceLastIncrement = '';
    public $incrementAmount = '';
    public $grossSalaryAfter = '';
    public $basicSalaryAfter = '';

    /** Increment effective date – from this date onwards the increment is applied */
    public $incrementEffectiveDate = '';

    /** For history only: record past increment for reporting; does not change current gross/basic salary */
    public $forHistory = false;

    /** Fetched employee data (read-only display) */
    public $employeeSalary = null;
    public $employeeBasicSalary = 0;
    public $employeeAllowances = 0;
    public $employeeGrossSalary = 0;

    public function mount()
    {
        $user = Auth::user();
        if (!$user || !$user->can('payroll.view')) {
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

    public function updatedSelectedEmployeeId()
    {
        $this->fetchEmployeeData();
    }

    public function updatedIncrementAmount()
    {
        $this->computeNewSalaryValues();
    }

    protected function fetchEmployeeData(): void
    {
        $id = (int) $this->selectedEmployeeId;
        if ($id <= 0) {
            $this->employeeSalary = null;
            $this->employeeBasicSalary = 0;
            $this->employeeAllowances = 0;
            $this->employeeGrossSalary = 0;
            $this->lastIncrementDate = '';
            $this->lastIncrementAmount = '';
            $this->timeSinceLastIncrement = '';
            $this->incrementAmount = '';
            $this->computeNewSalaryValues();
            return;
        }

        $employee = Employee::with(['salaryLegalCompliance', 'increments', 'organizationalInfo'])
            ->where('status', 'active')
            ->find($id);

        if (!$employee) {
            $this->employeeSalary = null;
            $this->employeeBasicSalary = 0;
            $this->employeeAllowances = 0;
            $this->employeeGrossSalary = 0;
            $this->lastIncrementDate = '';
            $this->lastIncrementAmount = '';
            $this->timeSinceLastIncrement = '';
            return;
        }

        $salary = $employee->salaryLegalCompliance;
        $basic = $salary ? (float) $salary->basic_salary : 0;
        $allowances = $salary ? (float) ($salary->allowances ?? 0) : 0;
        $gross = $basic + $allowances;

        $this->employeeSalary = $salary;
        $this->employeeBasicSalary = $basic;
        $this->employeeAllowances = $allowances;
        $this->employeeGrossSalary = $gross;

        $lastIncrement = $employee->increments()->where('for_history', false)->orderByDesc('last_increment_date')->first();
        if ($lastIncrement) {
            $this->lastIncrementDate = $lastIncrement->last_increment_date
                ? $lastIncrement->last_increment_date->format('Y-m-d')
                : ($lastIncrement->updated_at ? $lastIncrement->updated_at->format('Y-m-d') : '');
            $this->lastIncrementAmount = (float) $lastIncrement->increment_amount;
            $this->numberOfIncrements = (string) (($lastIncrement->number_of_increments ?? 0) + 1);

            if ($this->lastIncrementDate) {
                $since = Carbon::parse($this->lastIncrementDate)->diffForHumans(now(), true);
                $this->timeSinceLastIncrement = $since;
            } else {
                $this->timeSinceLastIncrement = __('N/A');
            }
        } else {
            $this->lastIncrementDate = '';
            $this->lastIncrementAmount = '';
            $this->numberOfIncrements = '1';
            $doj = $employee->organizationalInfo?->joining_date;
            $this->timeSinceLastIncrement = $doj ? Carbon::parse($doj)->diffForHumans(now(), true) : __('N/A');
        }

        $this->incrementAmount = '';
        $this->computeNewSalaryValues();
    }

    protected function computeNewSalaryValues(): void
    {
        $amount = (float) ($this->incrementAmount ?? 0);
        $newBasic = $this->employeeBasicSalary + $amount;
        $newGross = $newBasic + $this->employeeAllowances;
        $taxYear = (int) date('Y');
        $newTax = PayrollCalculationService::getTaxAmount($newGross, $taxYear);

        $this->grossSalaryAfter = $amount > 0 ? (string) $newGross : '';
        $this->basicSalaryAfter = $amount > 0 ? (string) $newBasic : '';
    }

    public function openAddIncrementModal()
    {
        $this->selectedEmployeeId = '';
        $this->numberOfIncrements = '0';
        $this->incrementDueDate = '';
        $this->lastIncrementDate = '';
        $this->lastIncrementAmount = '';
        $this->timeSinceLastIncrement = '';
        $this->incrementAmount = '';
        $this->incrementEffectiveDate = now()->format('Y-m-d');
        $this->grossSalaryAfter = '';
        $this->basicSalaryAfter = '';
        $this->forHistory = false;
        $this->employeeSalary = null;
        $this->employeeBasicSalary = 0;
        $this->employeeAllowances = 0;
        $this->employeeGrossSalary = 0;
        $this->showAddIncrementModal = true;
    }

    public function closeAddIncrementModal()
    {
        $this->showAddIncrementModal = false;
    }

    public function viewIncrement(int $id): void
    {
        $this->selectedIncrementId = $id;
        $this->showViewIncrementModal = true;
    }

    public function closeViewIncrementModal(): void
    {
        $this->showViewIncrementModal = false;
        $this->selectedIncrementId = null;
    }

    public function editIncrement(int $id): void
    {
        $inc = EmployeeIncrement::with('employee.salaryLegalCompliance')->find($id);
        if (!$inc) {
            session()->flash('error', __('Increment record not found.'));
            return;
        }
        $this->selectedIncrementId = $id;
        $this->selectedEmployeeId = (string) $inc->employee_id;
        $this->fetchEmployeeData();
        $this->numberOfIncrements = (string) $inc->number_of_increments;
        $this->incrementDueDate = $inc->increment_due_date?->format('Y-m-d') ?? '';
        $this->lastIncrementDate = $inc->last_increment_date?->format('Y-m-d') ?? '';
        $this->incrementEffectiveDate = $inc->last_increment_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->incrementAmount = (string) $inc->increment_amount;
        $this->forHistory = (bool) $inc->for_history;
        $this->computeNewSalaryValues();
        $this->showEditIncrementModal = true;
    }

    public function closeEditIncrementModal(): void
    {
        $this->showEditIncrementModal = false;
        $this->selectedIncrementId = null;
    }

    public function updateIncrement(): void
    {
        $id = (int) $this->selectedIncrementId;
        if ($id <= 0) {
            session()->flash('error', __('Invalid increment record.'));
            return;
        }
        $inc = EmployeeIncrement::find($id);
        if (!$inc) {
            session()->flash('error', __('Increment record not found.'));
            return;
        }
        $amount = (float) $this->incrementAmount;
        if ($amount <= 0) {
            session()->flash('error', __('Please enter a valid increment amount.'));
            return;
        }

        $forHistory = (bool) $this->forHistory;
        if ($forHistory && $this->incrementEffectiveDate) {
            $maxDate = $this->maxIncrementDateForHistory;
            if ($this->incrementEffectiveDate > $maxDate) {
                session()->flash('error', __('For history-only increments, the date must be before the current month (on or before :date).', ['date' => \Carbon\Carbon::parse($maxDate)->format('M d, Y')]));
                return;
            }
        }

        $newBasic = $this->employeeBasicSalary + $amount;
        $newGross = $newBasic + $this->employeeAllowances;

        $inc->update([
            'number_of_increments' => (int) $this->numberOfIncrements ?: 1,
            'increment_due_date' => $this->incrementDueDate ?: null,
            'last_increment_date' => $this->incrementEffectiveDate ?: now()->format('Y-m-d'),
            'increment_amount' => $amount,
            'gross_salary_after' => $newGross,
            'basic_salary_after' => $newBasic,
            'for_history' => $forHistory,
            'updated_by' => Auth::id(),
        ]);
        if (!$forHistory) {
            $this->syncEmployeeSalary($inc->employee_id, $newBasic);
        }

        $this->closeEditIncrementModal();
        session()->flash('message', __('Increment record updated successfully.'));
    }

    public function deleteIncrement(int $id): void
    {
        $inc = EmployeeIncrement::find($id);
        if ($inc) {
            $inc->delete();
            session()->flash('message', __('Increment record deleted successfully.'));
        } else {
            session()->flash('error', __('Increment record not found.'));
        }
    }

    public function addIncrement()
    {
        $employeeId = (int) $this->selectedEmployeeId;
        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }

        $amount = (float) $this->incrementAmount;
        if ($amount <= 0) {
            session()->flash('error', __('Please enter a valid increment amount.'));
            return;
        }

        $forHistory = (bool) $this->forHistory;
        if ($forHistory && $this->incrementEffectiveDate) {
            $maxDate = $this->maxIncrementDateForHistory;
            if ($this->incrementEffectiveDate > $maxDate) {
                session()->flash('error', __('For history-only increments, the date must be before the current month (on or before :date).', ['date' => \Carbon\Carbon::parse($maxDate)->format('M d, Y')]));
                return;
            }
        }

        $newBasic = $this->employeeBasicSalary + $amount;
        $newGross = $newBasic + $this->employeeAllowances;

        EmployeeIncrement::create([
            'employee_id' => $employeeId,
            'number_of_increments' => (int) $this->numberOfIncrements ?: 1,
            'increment_due_date' => $this->incrementDueDate ?: null,
            'last_increment_date' => $this->incrementEffectiveDate ?: now()->format('Y-m-d'),
            'increment_amount' => $amount,
            'gross_salary_after' => $newGross,
            'basic_salary_after' => $newBasic,
            'for_history' => $forHistory,
            'updated_by' => Auth::id(),
        ]);

        if (!$forHistory) {
            $this->syncEmployeeSalary($employeeId, $newBasic);
        }

        $this->closeAddIncrementModal();
        session()->flash('message', $forHistory ? __('Increment history record added.') : __('Increment record added successfully.'));
    }

    public function getCalculatedTaxAmountProperty(): float
    {
        $amount = (float) ($this->incrementAmount ?? 0);
        if ($amount <= 0) {
            return 0;
        }
        $newGross = $this->employeeBasicSalary + $amount + $this->employeeAllowances;
        return PayrollCalculationService::getTaxAmount($newGross, (int) date('Y'));
    }

    public function getCalculatedNewGrossProperty(): float
    {
        $amount = (float) ($this->incrementAmount ?? 0);
        return $this->employeeBasicSalary + $amount + $this->employeeAllowances;
    }

    public function getCalculatedNewBasicProperty(): float
    {
        $amount = (float) ($this->incrementAmount ?? 0);
        return $this->employeeBasicSalary + $amount;
    }

    public function getCalculatedNetSalaryProperty(): float
    {
        $newGross = $this->calculatedNewGross;
        $tax = $this->calculatedTaxAmount;
        return round($newGross - $tax, 2);
    }

    public function getSelectedIncrementProperty(): ?EmployeeIncrement
    {
        if (!$this->selectedIncrementId) {
            return null;
        }
        return EmployeeIncrement::with(['employee.department', 'updatedByUser'])->find($this->selectedIncrementId);
    }

    /**
     * When "For history / reporting only" is checked, increment date must be before current month (last day of previous month).
     */
    public function getMaxIncrementDateForHistoryProperty(): string
    {
        return Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
    }

    public function updatedForHistory($value): void
    {
        if ($value && $this->incrementEffectiveDate) {
            $max = $this->maxIncrementDateForHistory;
            if ($this->incrementEffectiveDate > $max) {
                $this->incrementEffectiveDate = $max;
            }
        }
    }

    /**
     * Update employee's current basic salary (used when increment is not for_history).
     * Creates a compliance record if missing so salary is persisted for payroll/reports.
     */
    protected function syncEmployeeSalary(int $employeeId, float $newBasic): void
    {
        $allowances = (float) $this->employeeAllowances;
        $salary = EmployeeSalaryLegalCompliance::where('employee_id', $employeeId)->first();
        if ($salary) {
            $salary->update(['basic_salary' => $newBasic]);
        } else {
            EmployeeSalaryLegalCompliance::create([
                'employee_id' => $employeeId,
                'basic_salary' => $newBasic,
                'allowances' => $allowances,
            ]);
        }
    }

    public function render()
    {
        $query = EmployeeIncrement::query()
            ->with(['employee.department', 'updatedByUser'])
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
            ->orderBy('updated_at', 'desc');

        $increments = $query->paginate(15);

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

        return view('livewire.payroll.increments', [
            'increments' => $increments,
            'departments' => $departments,
            'activeEmployees' => $activeEmployees,
        ])->layout('components.layouts.app');
    }
}
