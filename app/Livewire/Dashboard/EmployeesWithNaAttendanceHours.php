<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Attendance\Report;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class EmployeesWithNaAttendanceHours extends Component
{
    /** @var string Month (Y-m); empty means current month */
    public string $selectedMonth = '';

    /** @var list<array{id: int, name: string, employee_code: ?string, department: string, group: string, na_count: int}> */
    public array $rows = [];

    public function mount(): void
    {
        if ($this->selectedMonth === '') {
            $this->selectedMonth = Carbon::now()->format('Y-m');
        }
        $this->loadRows();
    }

    public function updatedSelectedMonth(): void
    {
        $this->loadRows();
    }

    public function refresh(): void
    {
        $this->loadRows();
    }

    public function loadRows(): void
    {
        $this->rows = [];

        if (! Auth::user()?->can('dashboard.view.attendance_na_total_hours')) {
            return;
        }

        $ym = $this->selectedMonth ?: Carbon::now()->format('Y-m');

        $employees = Employee::query()
            ->where('status', 'active')
            ->whereNotNull('punch_code')
            ->excludingAdminDepartment()
            ->with(['department', 'group'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $scanner = new Report;

        foreach ($employees as $employee) {
            $result = $scanner->evaluateEmployeeNaTotalHoursForDashboard($employee, $ym);
            if ($result === null) {
                continue;
            }

            $this->rows[] = [
                'id' => $employee->id,
                'name' => trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')),
                'employee_code' => $employee->employee_code,
                'department' => $this->departmentLabel($employee),
                'group' => $employee->group?->name ?? '—',
                'na_count' => $result['count'],
            ];
        }
    }

    private function departmentLabel(Employee $employee): string
    {
        if ($employee->relationLoaded('department')) {
            $dept = $employee->getRelation('department');

            return $dept && is_object($dept) ? (string) ($dept->title ?? '—') : '—';
        }

        $dept = $employee->department()->first();

        return $dept ? (string) ($dept->title ?? '—') : '—';
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function getAvailableMonths(): array
    {
        $months = [];
        $current = Carbon::now();
        $months[] = [
            'value' => $current->format('Y-m'),
            'label' => $current->format('F Y').' ('.__('Current').')',
        ];
        for ($i = 1; $i <= 11; $i++) {
            $date = $current->copy()->subMonths($i);
            $months[] = [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ];
        }

        return $months;
    }

    public function placeholder(): View
    {
        if (! Auth::user()?->can('dashboard.view.attendance_na_total_hours')) {
            return view('livewire.dashboard.placeholders.empty');
        }

        return view('components.dashboard.widget-skeleton', [
            'withSubtitle' => true,
            'skeletonColumns' => 5,
            'skeletonRows' => 4,
        ]);
    }

    public function render()
    {
        return view('livewire.dashboard.employees-with-na-attendance-hours', [
            'availableMonths' => $this->getAvailableMonths(),
        ]);
    }
}
