<?php

namespace App\Livewire\Payroll;

use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollEmployeeBonus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BonusManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedDepartment = '';

    public string $bonusType = '';

    public string $selectedMonth = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public bool $showAddBonusModal = false;

    public string $employeeId = '';

    public string $formBonusType = 'Performance';

    public string $amount = '';

    public string $description = '';

    public string $formYearMonth = '';

    public const BONUS_TYPES = ['Performance', 'Annual', 'Festival', 'Project', 'Reimbursement', 'Other'];

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('payroll.bonus.manage')) {
            abort(403);
        }

        $this->selectedMonth = now()->format('Y-m');
        $this->formYearMonth = $this->selectedMonth;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment(): void
    {
        $this->resetPage();
    }

    public function updatedBonusType(): void
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

    public function openAddBonusModal(?int $employeeId = null): void
    {
        $this->employeeId = $employeeId ? (string) $employeeId : '';
        $this->formBonusType = 'Performance';
        $this->amount = '';
        $this->description = '';
        $this->formYearMonth = $this->selectedMonth ?: now()->format('Y-m');
        $this->showAddBonusModal = true;
    }

    public function closeAddBonusModal(): void
    {
        $this->showAddBonusModal = false;
    }

    public function addBonus(): void
    {
        $employeeId = (int) $this->employeeId;
        $amount = trim($this->amount);
        $yearMonth = trim($this->formYearMonth);

        if ($employeeId <= 0) {
            session()->flash('error', __('Please select an employee.'));
            return;
        }

        if ($amount === '' || !is_numeric($amount) || (float) $amount < 0) {
            session()->flash('error', __('Please enter a valid amount.'));
            return;
        }

        if ($yearMonth === '' || !preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            session()->flash('error', __('Please select a valid month.'));
            return;
        }

        if (!in_array($this->formBonusType, self::BONUS_TYPES, true)) {
            session()->flash('error', __('Please select a valid bonus type.'));
            return;
        }

        PayrollEmployeeBonus::create([
            'year_month' => $yearMonth,
            'employee_id' => $employeeId,
            'bonus_type' => $this->formBonusType,
            'amount' => (float) $amount,
            'description' => trim($this->description) ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->selectedMonth = $yearMonth;
        $this->resetPage();
        $this->closeAddBonusModal();
        session()->flash('message', __('Bonus added successfully for :month.', [
            'month' => Carbon::createFromFormat('Y-m', $yearMonth)->format('F Y'),
        ]));
    }

    public function deleteBonus(int $id): void
    {
        $record = PayrollEmployeeBonus::find($id);
        if (!$record) {
            session()->flash('error', __('Bonus record not found.'));
            return;
        }

        $record->delete();
        session()->flash('message', __('Bonus deleted successfully.'));
    }

    public function render()
    {
        $query = PayrollEmployeeBonus::query()
            ->with(['employee.department', 'employee.salaryLegalCompliance'])
            ->when($this->selectedMonth !== '', fn ($q) => $q->where('year_month', $this->selectedMonth))
            ->when($this->bonusType !== '', fn ($q) => $q->where('bonus_type', $this->bonusType))
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
            });

        if ($this->sortBy === 'employee_name') {
            $query->join('employees', 'payroll_employee_bonuses.employee_id', '=', 'employees.id')
                ->orderByRaw('CONCAT(employees.first_name, " ", employees.last_name) ' . ($this->sortDirection === 'asc' ? 'asc' : 'desc'))
                ->select('payroll_employee_bonuses.*');
        } else {
            $allowed = ['year_month', 'bonus_type', 'amount', 'created_at'];
            $field = in_array($this->sortBy, $allowed, true) ? $this->sortBy : 'created_at';
            $query->orderBy($field, $this->sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $bonuses = $query->paginate(15);

        $departments = Department::where('status', 'active')->orderBy('title')->pluck('title')->toArray();

        $employeeOptions = Employee::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'employee_code', 'status']);

        $availableMonths = PayrollEmployeeBonus::query()
            ->select('year_month')
            ->distinct()
            ->orderByDesc('year_month')
            ->pluck('year_month')
            ->map(fn (string $ym) => [
                'value' => $ym,
                'label' => Carbon::createFromFormat('Y-m', $ym)->format('F Y'),
            ])
            ->values()
            ->toArray();

        $currentMonth = now()->format('Y-m');
        if (!collect($availableMonths)->contains('value', $currentMonth)) {
            array_unshift($availableMonths, [
                'value' => $currentMonth,
                'label' => Carbon::createFromFormat('Y-m', $currentMonth)->format('F Y'),
            ]);
        }

        return view('livewire.payroll.bonus-management', [
            'bonuses' => $bonuses,
            'departments' => $departments,
            'employeeOptions' => $employeeOptions,
            'bonusTypes' => self::BONUS_TYPES,
            'availableMonths' => $availableMonths,
        ])->layout('components.layouts.app');
    }
}
