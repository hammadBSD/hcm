<?php

namespace App\Livewire\Payroll;

use App\Models\Department;
use App\Models\PayrollLateDeductionAdjustment;
use App\Models\PayrollMonthLock;
use App\Services\PayrollLatesAdjustmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LatesAdjustment extends Component
{
    public string $search = '';

    public string $selectedDepartment = '';

    public string $selectedMonth = '';

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    public bool $showEditFlyout = false;

    public ?int $editingEmployeeId = null;

    public string $editWaivedDays = '';

    public string $editNotes = '';

    public ?int $editCalculatedDays = null;

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || !$user->can('payroll.export')) {
            abort(403);
        }

        $this->selectedMonth = now()->format('Y-m');
        $this->loadRows();
    }

    public function updatedSearch(): void
    {
        $this->loadRows();
    }

    public function updatedSelectedDepartment(): void
    {
        $this->loadRows();
    }

    public function updatedSelectedMonth(): void
    {
        $this->closeEditFlyout();
        $this->loadRows();
    }

    public function loadRows(): void
    {
        $month = $this->selectedMonth ?: now()->format('Y-m');

        $this->rows = app(PayrollLatesAdjustmentService::class)->qualifyingEmployees(
            $month,
            $this->search,
            $this->selectedDepartment,
        );
    }

    public function openEditFlyout(int $employeeId): void
    {
        if ($this->isMonthLocked()) {
            session()->flash('error', __('This month is locked. Late adjustments cannot be changed.'));
            return;
        }

        $row = collect($this->rows)->firstWhere('employee_id', $employeeId);
        if (!$row) {
            session()->flash('error', __('Employee not found in the qualifying list.'));
            return;
        }

        $this->editingEmployeeId = $employeeId;
        $this->editCalculatedDays = (int) $row['calculated_deduction_late_days'];
        $this->editWaivedDays = (string) (int) $row['waived_deduction_late_days'];
        $this->editNotes = (string) (PayrollLateDeductionAdjustment::query()
            ->where('year_month', $this->selectedMonth)
            ->where('employee_id', $employeeId)
            ->value('notes') ?? '');
        $this->showEditFlyout = true;
    }

    public function closeEditFlyout(): void
    {
        $this->showEditFlyout = false;
        $this->editingEmployeeId = null;
        $this->editWaivedDays = '';
        $this->editNotes = '';
        $this->editCalculatedDays = null;
    }

    public function saveAdjustment(): void
    {
        if ($this->isMonthLocked()) {
            session()->flash('error', __('This month is locked. Late adjustments cannot be changed.'));
            return;
        }

        $employeeId = (int) $this->editingEmployeeId;
        $calculatedDays = (int) $this->editCalculatedDays;
        $waivedDays = trim($this->editWaivedDays);

        if ($employeeId <= 0 || $calculatedDays <= 0) {
            session()->flash('error', __('Invalid adjustment request.'));
            return;
        }

        if ($waivedDays === '' || !ctype_digit($waivedDays)) {
            session()->flash('error', __('Please enter a valid number of salary days to waive.'));
            return;
        }

        $waivedDaysInt = (int) $waivedDays;
        if ($waivedDaysInt < 0 || $waivedDaysInt > $calculatedDays) {
            session()->flash('error', __('Waived days must be between 0 and :max.', ['max' => $calculatedDays]));
            return;
        }

        $month = $this->selectedMonth;

        if ($waivedDaysInt === 0) {
            PayrollLateDeductionAdjustment::query()
                ->where('year_month', $month)
                ->where('employee_id', $employeeId)
                ->delete();

            $this->patchRowAfterSave($employeeId, 0, null);
            $this->closeEditFlyout();
            session()->flash('message', __('Late deduction reset to the calculated amount.'));
            return;
        }

        $existing = PayrollLateDeductionAdjustment::query()
            ->where('year_month', $month)
            ->where('employee_id', $employeeId)
            ->first();

        if ($existing) {
            $existing->update([
                'waived_deduction_late_days' => $waivedDaysInt,
                'notes' => trim($this->editNotes) ?: null,
                'updated_by' => Auth::id(),
            ]);
            $adjustmentId = $existing->id;
        } else {
            $created = PayrollLateDeductionAdjustment::create([
                'year_month' => $month,
                'employee_id' => $employeeId,
                'waived_deduction_late_days' => $waivedDaysInt,
                'notes' => trim($this->editNotes) ?: null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            $adjustmentId = $created->id;
        }

        $this->patchRowAfterSave($employeeId, $waivedDaysInt, $adjustmentId);
        $this->closeEditFlyout();
        session()->flash('message', __('Late deduction adjustment saved.'));
    }

    public function removeAdjustment(int $employeeId): void
    {
        if ($this->isMonthLocked()) {
            session()->flash('error', __('This month is locked. Late adjustments cannot be changed.'));
            return;
        }

        PayrollLateDeductionAdjustment::query()
            ->where('year_month', $this->selectedMonth)
            ->where('employee_id', $employeeId)
            ->delete();

        $this->patchRowAfterSave($employeeId, 0, null);
        session()->flash('message', __('Late deduction adjustment removed.'));
    }

    /**
     * Update only the saved employee row in memory — avoid rebuilding attendance for the whole company.
     */
    protected function patchRowAfterSave(int $employeeId, int $waivedDays, ?int $adjustmentId): void
    {
        foreach ($this->rows as $index => $row) {
            if ((int) ($row['employee_id'] ?? 0) !== $employeeId) {
                continue;
            }

            $calculatedDays = (int) ($row['calculated_deduction_late_days'] ?? 0);
            $calculatedAmount = (float) ($row['calculated_deduction_late_amount'] ?? 0);
            $waived = max(0, min($waivedDays, $calculatedDays));
            $finalDays = max(0, $calculatedDays - $waived);
            $perDay = $calculatedDays > 0 ? ($calculatedAmount / $calculatedDays) : 0.0;

            $this->rows[$index]['waived_deduction_late_days'] = $waived;
            $this->rows[$index]['final_deduction_late_days'] = $finalDays;
            $this->rows[$index]['final_deduction_late_amount'] = round($finalDays * $perDay, 2);
            $this->rows[$index]['has_adjustment'] = $waived > 0;
            $this->rows[$index]['adjustment_id'] = $waived > 0 ? $adjustmentId : null;
            break;
        }
    }

    protected function isMonthLocked(): bool
    {
        return PayrollMonthLock::isLocked($this->selectedMonth ?: now()->format('Y-m'));
    }

    public function getEditingRowProperty(): ?array
    {
        if (!$this->editingEmployeeId) {
            return null;
        }

        return collect($this->rows)->firstWhere('employee_id', $this->editingEmployeeId);
    }

    public function render()
    {
        $month = $this->selectedMonth ?: now()->format('Y-m');
        $service = app(PayrollLatesAdjustmentService::class);

        return view('livewire.payroll.lates-adjustment', [
            'rows' => $this->rows,
            'departments' => Department::where('status', 'active')->orderBy('title')->pluck('title')->toArray(),
            'ruleSummary' => $service->currentRuleSummary($month),
            'monthLabel' => Carbon::createFromFormat('Y-m', $month)->format('F Y'),
            'isMonthLocked' => $this->isMonthLocked(),
        ])->layout('components.layouts.app');
    }
}
