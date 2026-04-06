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
        [$toBasic, $toAllowances] = PayrollCalculationService::splitIncrementBetweenBasicAndAllowances($amount);
        $newBasic = $this->employeeBasicSalary + $toBasic;
        $newAllowances = $this->employeeAllowances + $toAllowances;
        $newGross = $newBasic + $newAllowances;

        $this->grossSalaryAfter = $amount != 0.0 ? (string) $newGross : '';
        $this->basicSalaryAfter = $amount != 0.0 ? (string) $newBasic : '';
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
            session()->flash('error', __('Increment/decrement record not found.'));
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
            session()->flash('error', __('Invalid increment/decrement record.'));
            return;
        }
        $inc = EmployeeIncrement::find($id);
        if (!$inc) {
            session()->flash('error', __('Increment/decrement record not found.'));
            return;
        }
        $amount = (float) $this->incrementAmount;
        if ($amount == 0.0) {
            session()->flash('error', __('Please enter a non-zero amount (positive for an increase, negative for a decrease).'));
            return;
        }

        $forHistory = (bool) $this->forHistory;
        if ($forHistory && $this->incrementEffectiveDate) {
            $maxDate = $this->maxIncrementDateForHistory;
            if ($this->incrementEffectiveDate > $maxDate) {
                session()->flash('error', __('For history-only records, the date must be before the current month (on or before :date).', ['date' => \Carbon\Carbon::parse($maxDate)->format('M d, Y')]));
                return;
            }
        }

        [$toBasic, $toAllowances] = PayrollCalculationService::splitIncrementBetweenBasicAndAllowances($amount);
        $newBasic = $this->employeeBasicSalary + $toBasic;
        $newAllowances = $this->employeeAllowances + $toAllowances;
        $newGross = $newBasic + $newAllowances;
        if ($newBasic < 0 || $newAllowances < 0 || $newGross < 0) {
            session()->flash('error', __('The resulting basic, allowances, or gross salary cannot be negative. Use a smaller decrease.'));
            return;
        }

        $removedFromApplied = !$inc->for_history && $forHistory;
        $snapshotBeforeUpdate = $removedFromApplied ? clone $inc : null;

        $inc->update([
            'number_of_increments' => (int) $this->numberOfIncrements ?: 1,
            'increment_due_date' => $this->incrementDueDate ?: null,
            'last_increment_date' => $this->incrementEffectiveDate ?: now()->format('Y-m-d'),
            'increment_amount' => $amount,
            'gross_salary_after' => $newGross,
            'basic_salary_after' => $newBasic,
            'allowances_after' => $newAllowances,
            'for_history' => $forHistory,
            'updated_by' => Auth::id(),
        ]);

        if ($removedFromApplied && $snapshotBeforeUpdate) {
            $this->reconcileEmployeeSalaryFromNonHistoryIncrements($inc->employee_id, $snapshotBeforeUpdate);
        } else {
            $this->reconcileEmployeeSalaryFromNonHistoryIncrements($inc->employee_id);
        }

        $this->closeEditIncrementModal();
        session()->flash('message', __('Increment/decrement record updated successfully.'));
    }

    public function deleteIncrement(int $id): void
    {
        $inc = EmployeeIncrement::find($id);
        if (!$inc) {
            session()->flash('error', __('Increment/decrement record not found.'));
            return;
        }

        $employeeId = $inc->employee_id;
        $wasNonHistory = !$inc->for_history;
        $deleted = $wasNonHistory ? clone $inc : null;
        $inc->delete();

        if ($wasNonHistory) {
            $this->reconcileEmployeeSalaryFromNonHistoryIncrements($employeeId, $deleted);
        }

        session()->flash('message', __('Increment/decrement record deleted successfully.'));
    }

    public function addIncrement()
    {
        $employeeId = (int) $this->selectedEmployeeId;
        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }

        $amount = (float) $this->incrementAmount;
        if ($amount == 0.0) {
            session()->flash('error', __('Please enter a non-zero amount (positive for an increase, negative for a decrease).'));
            return;
        }

        $forHistory = (bool) $this->forHistory;
        if ($forHistory && $this->incrementEffectiveDate) {
            $maxDate = $this->maxIncrementDateForHistory;
            if ($this->incrementEffectiveDate > $maxDate) {
                session()->flash('error', __('For history-only records, the date must be before the current month (on or before :date).', ['date' => \Carbon\Carbon::parse($maxDate)->format('M d, Y')]));
                return;
            }
        }

        [$toBasic, $toAllowances] = PayrollCalculationService::splitIncrementBetweenBasicAndAllowances($amount);
        $newBasic = $this->employeeBasicSalary + $toBasic;
        $newAllowances = $this->employeeAllowances + $toAllowances;
        $newGross = $newBasic + $newAllowances;
        if ($newBasic < 0 || $newAllowances < 0 || $newGross < 0) {
            session()->flash('error', __('The resulting basic, allowances, or gross salary cannot be negative. Use a smaller decrease.'));
            return;
        }

        EmployeeIncrement::create([
            'employee_id' => $employeeId,
            'number_of_increments' => (int) $this->numberOfIncrements ?: 1,
            'increment_due_date' => $this->incrementDueDate ?: null,
            'last_increment_date' => $this->incrementEffectiveDate ?: now()->format('Y-m-d'),
            'increment_amount' => $amount,
            'gross_salary_after' => $newGross,
            'basic_salary_after' => $newBasic,
            'allowances_after' => $newAllowances,
            'for_history' => $forHistory,
            'updated_by' => Auth::id(),
        ]);

        if (!$forHistory) {
            $this->reconcileEmployeeSalaryFromNonHistoryIncrements($employeeId);
        }

        $this->closeAddIncrementModal();
        session()->flash('message', $forHistory ? __('Increment/decrement history record added.') : __('Increment/decrement record added successfully.'));
    }

    public function getCalculatedTaxAmountProperty(): float
    {
        $amount = (float) ($this->incrementAmount ?? 0);
        if ($amount == 0.0) {
            return 0;
        }
        [$toBasic, $toAllowances] = PayrollCalculationService::splitIncrementBetweenBasicAndAllowances($amount);
        $newGross = $this->employeeBasicSalary + $toBasic + $this->employeeAllowances + $toAllowances;
        if ($newGross <= 0) {
            return 0;
        }

        return PayrollCalculationService::getTaxAmount($newGross, (int) date('Y'));
    }

    public function getCalculatedNewGrossProperty(): float
    {
        $amount = (float) ($this->incrementAmount ?? 0);
        [$toBasic, $toAllowances] = PayrollCalculationService::splitIncrementBetweenBasicAndAllowances($amount);

        return $this->employeeBasicSalary + $toBasic + $this->employeeAllowances + $toAllowances;
    }

    public function getCalculatedNewBasicProperty(): float
    {
        $amount = (float) ($this->incrementAmount ?? 0);
        [$toBasic] = PayrollCalculationService::splitIncrementBetweenBasicAndAllowances($amount);

        return $this->employeeBasicSalary + $toBasic;
    }

    public function getCalculatedNewAllowancesProperty(): float
    {
        $amount = (float) ($this->incrementAmount ?? 0);
        [, $toAllowances] = PayrollCalculationService::splitIncrementBetweenBasicAndAllowances($amount);

        return $this->employeeAllowances + $toAllowances;
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
     * Recompute basic_salary_after / allowances_after / gross_salary_after for all non-history increments
     * in chronological order (60% basic / 40% allowances per row when allowances_after is stored; legacy rows use 100% basic),
     * then sync EmployeeSalaryLegalCompliance basic and allowances.
     *
     * When no non-history rows remain, restores salary from the deleted row's implied previous state.
     */
    protected function reconcileEmployeeSalaryFromNonHistoryIncrements(int $employeeId, ?EmployeeIncrement $deleted = null): void
    {
        $rows = EmployeeIncrement::query()
            ->where('employee_id', $employeeId)
            ->where('for_history', false)
            ->orderByRaw('COALESCE(last_increment_date, updated_at) ASC')
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            if ($deleted && !$deleted->for_history) {
                $useSplit = $deleted->allowances_after !== null;
                [$db, $da] = PayrollCalculationService::incrementAmountToBasicAndAllowances((float) $deleted->increment_amount, $useSplit);
                $allowOnDeleted = $deleted->allowances_after !== null
                    ? (float) $deleted->allowances_after
                    : (float) $deleted->gross_salary_after - (float) $deleted->basic_salary_after;
                $newBasic = max(0, round((float) $deleted->basic_salary_after - $db, 2));
                $newAllowances = max(0, round($allowOnDeleted - $da, 2));
                $this->syncEmployeeSalaryCompliance($employeeId, $newBasic, $newAllowances);
            }

            return;
        }

        $first = $rows[0];
        $useSplitFirst = $first->allowances_after !== null;
        [$d0b, $d0a] = PayrollCalculationService::incrementAmountToBasicAndAllowances((float) $first->increment_amount, $useSplitFirst);
        $allow0 = $first->allowances_after !== null
            ? (float) $first->allowances_after
            : (float) $first->gross_salary_after - (float) $first->basic_salary_after;
        $baseBasic = round((float) $first->basic_salary_after - $d0b, 2);
        $baseAllow = round($allow0 - $d0a, 2);
        $runningB = $baseBasic;
        $runningA = $baseAllow;

        foreach ($rows as $r) {
            $useSplit = $r->allowances_after !== null;
            [$db, $da] = PayrollCalculationService::incrementAmountToBasicAndAllowances((float) $r->increment_amount, $useSplit);
            $runningB = round($runningB + $db, 2);
            $runningA = round($runningA + $da, 2);
            $payload = [
                'basic_salary_after' => $runningB,
                'gross_salary_after' => round($runningB + $runningA, 2),
            ];
            if ($r->allowances_after !== null) {
                $payload['allowances_after'] = $runningA;
            }
            $r->update($payload);
        }

        $this->syncEmployeeSalaryCompliance($employeeId, $runningB, $runningA);
    }

    /**
     * Persist current basic and allowances on the employee salary compliance record.
     */
    protected function syncEmployeeSalaryCompliance(int $employeeId, float $newBasic, float $newAllowances): void
    {
        $salary = EmployeeSalaryLegalCompliance::where('employee_id', $employeeId)->first();
        if ($salary) {
            $salary->update([
                'basic_salary' => $newBasic,
                'allowances' => $newAllowances,
            ]);
        } else {
            EmployeeSalaryLegalCompliance::create([
                'employee_id' => $employeeId,
                'basic_salary' => $newBasic,
                'allowances' => $newAllowances,
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
