<?php

namespace App\Livewire\Payroll;

use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollTaxAdjustment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TaxAdjustment extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $selectedMonth = '';
    public $sortBy = 'effective_from';
    public $sortDirection = 'desc';

    public $showAddAdjustmentModal = false;
    public $showViewAdjustmentModal = false;
    public $showEditAdjustmentModal = false;
    public $selectedAdjustmentId = null;
    public $employeeId = '';
    public $adjustedTaxAmount = '';
    public $effectiveFrom = '';
    public $notes = '';
    public $editEmployeeId = '';
    public $editAdjustedTaxAmount = '';
    public $editEffectiveFrom = '';
    public $editNotes = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || !$user->can('payroll.export')) {
            abort(403);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedMonth(): void
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            return;
        }
        $this->sortBy = $field;
        $this->sortDirection = 'asc';
    }

    public function openAddAdjustmentModal(): void
    {
        $this->employeeId = '';
        $this->adjustedTaxAmount = '';
        $this->effectiveFrom = now()->format('Y-m-d');
        $this->notes = '';
        $this->showAddAdjustmentModal = true;
    }

    public function closeAddAdjustmentModal(): void
    {
        $this->showAddAdjustmentModal = false;
    }

    public function addAdjustment(): void
    {
        $employeeId = (int) $this->employeeId;
        $amount = trim((string) $this->adjustedTaxAmount);
        $effectiveFrom = trim((string) $this->effectiveFrom);

        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }
        if ($amount === '' || !is_numeric($amount)) {
            session()->flash('error', __('Please enter a valid adjusted tax amount.'));
            return;
        }
        if ($effectiveFrom === '') {
            session()->flash('error', __('Please select an effective date.'));
            return;
        }

        PayrollTaxAdjustment::create([
            'employee_id' => $employeeId,
            'adjusted_tax_amount' => (float) $amount,
            'effective_from' => $effectiveFrom,
            'notes' => trim((string) $this->notes),
            'created_by' => Auth::id(),
        ]);

        $this->closeAddAdjustmentModal();
        session()->flash('message', __('Tax adjustment added successfully.'));
    }

    public function viewAdjustment(int $id): void
    {
        $this->selectedAdjustmentId = $id;
        $this->showViewAdjustmentModal = true;
    }

    public function closeViewAdjustmentModal(): void
    {
        $this->showViewAdjustmentModal = false;
        $this->selectedAdjustmentId = null;
    }

    public function editAdjustment(int $id): void
    {
        $record = PayrollTaxAdjustment::find($id);
        if (!$record) {
            session()->flash('error', __('Tax adjustment not found.'));
            return;
        }
        $this->selectedAdjustmentId = $record->id;
        $this->editEmployeeId = (string) $record->employee_id;
        $this->editAdjustedTaxAmount = (string) ((float) $record->adjusted_tax_amount);
        $this->editEffectiveFrom = $record->effective_from ? $record->effective_from->format('Y-m-d') : '';
        $this->editNotes = (string) ($record->notes ?? '');
        $this->showEditAdjustmentModal = true;
    }

    public function closeEditAdjustmentModal(): void
    {
        $this->showEditAdjustmentModal = false;
        $this->selectedAdjustmentId = null;
    }

    public function updateAdjustment(): void
    {
        $record = PayrollTaxAdjustment::find((int) $this->selectedAdjustmentId);
        if (!$record) {
            session()->flash('error', __('Tax adjustment not found.'));
            return;
        }

        $employeeId = (int) $this->editEmployeeId;
        $amount = trim((string) $this->editAdjustedTaxAmount);
        $effectiveFrom = trim((string) $this->editEffectiveFrom);

        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }
        if ($amount === '' || !is_numeric($amount)) {
            session()->flash('error', __('Please enter a valid adjusted tax amount.'));
            return;
        }
        if ($effectiveFrom === '') {
            session()->flash('error', __('Please select an effective date.'));
            return;
        }

        $record->update([
            'employee_id' => $employeeId,
            'adjusted_tax_amount' => (float) $amount,
            'effective_from' => $effectiveFrom,
            'notes' => trim((string) $this->editNotes),
        ]);

        $this->closeEditAdjustmentModal();
        session()->flash('message', __('Tax adjustment updated successfully.'));
    }

    public function deleteAdjustment(int $id): void
    {
        $record = PayrollTaxAdjustment::find($id);
        if (!$record) {
            session()->flash('error', __('Tax adjustment not found.'));
            return;
        }
        $record->delete();
        session()->flash('message', __('Tax adjustment deleted successfully.'));
    }

    public function getSelectedAdjustmentProperty(): ?PayrollTaxAdjustment
    {
        if (!$this->selectedAdjustmentId) {
            return null;
        }
        return PayrollTaxAdjustment::with(['employee.department', 'employee.salaryLegalCompliance'])
            ->find($this->selectedAdjustmentId);
    }

    public function render()
    {
        $query = PayrollTaxAdjustment::query()
            ->with(['employee.department', 'employee.salaryLegalCompliance'])
            ->when($this->search !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->whereHas('employee', function ($q2) use ($term) {
                    $q2->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('employee_code', 'like', $term);
                });
            })
            ->when($this->selectedDepartment !== '', function ($q) {
                $q->whereHas('employee.department', function ($q2) {
                    $q2->where('title', $this->selectedDepartment);
                });
            })
            ->when($this->selectedMonth !== '', function ($q) {
                $q->where('effective_from', '<=', $this->selectedMonth . '-31');
            });

        if ($this->sortBy === 'employee_name') {
            $query->join('employees', 'payroll_tax_adjustments.employee_id', '=', 'employees.id')
                ->orderByRaw('CONCAT(employees.first_name, " ", employees.last_name) ' . ($this->sortDirection === 'asc' ? 'asc' : 'desc'))
                ->select('payroll_tax_adjustments.*');
        } else {
            $allowed = ['effective_from', 'adjusted_tax_amount', 'created_at'];
            $field = in_array($this->sortBy, $allowed, true) ? $this->sortBy : 'effective_from';
            $query->orderBy($field, $this->sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $adjustments = $query->paginate(15);

        $departments = Department::where('status', 'active')->orderBy('title')->pluck('title')->toArray();
        $employees = Employee::where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn ($e) => ['id' => $e->id, 'label' => trim($e->first_name . ' ' . $e->last_name) . ' (' . ($e->employee_code ?? '') . ')'])
            ->toArray();

        return view('livewire.payroll.tax-adjustment', [
            'adjustments' => $adjustments,
            'departments' => $departments,
            'employees' => $employees,
        ])->layout('components.layouts.app');
    }
}
