<?php

namespace App\Livewire\Payroll;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeIncrement;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Increments extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $showAddIncrementModal = false;

    /** Add Increment form */
    public $selectedEmployeeId = '';
    public $numberOfIncrements = '0';
    public $incrementDueDate = '';
    public $lastIncrementDate = '';
    public $incrementAmount = '';
    public $grossSalaryAfter = '';
    public $basicSalaryAfter = '';

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

    public function openAddIncrementModal()
    {
        $this->selectedEmployeeId = '';
        $this->numberOfIncrements = '0';
        $this->incrementDueDate = '';
        $this->lastIncrementDate = '';
        $this->incrementAmount = '';
        $this->grossSalaryAfter = '';
        $this->basicSalaryAfter = '';
        $this->showAddIncrementModal = true;
    }

    public function closeAddIncrementModal()
    {
        $this->showAddIncrementModal = false;
    }

    public function addIncrement()
    {
        $employeeId = (int) $this->selectedEmployeeId;
        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }

        EmployeeIncrement::create([
            'employee_id' => $employeeId,
            'number_of_increments' => (int) $this->numberOfIncrements ?: 0,
            'increment_due_date' => $this->incrementDueDate ?: null,
            'last_increment_date' => $this->lastIncrementDate ?: null,
            'increment_amount' => (float) $this->incrementAmount ?: 0,
            'gross_salary_after' => $this->grossSalaryAfter !== '' ? (float) $this->grossSalaryAfter : null,
            'basic_salary_after' => $this->basicSalaryAfter !== '' ? (float) $this->basicSalaryAfter : null,
            'updated_by' => Auth::id(),
        ]);

        $this->closeAddIncrementModal();
        session()->flash('message', __('Increment record added successfully.'));
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
