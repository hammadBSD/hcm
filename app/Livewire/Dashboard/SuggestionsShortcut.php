<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\EmployeeSuggestion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SuggestionsShortcut extends Component
{
    public $suggestions = [];

    /** @var string Month filter (Y-m), default current month */
    public $selectedMonth = '';

    public function mount()
    {
        if ($this->selectedMonth === '') {
            $this->selectedMonth = Carbon::now()->format('Y-m');
        }
        $this->loadSuggestions();
    }

    public function loadSuggestions()
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();

        $query = EmployeeSuggestion::query()
            ->with(['employee.user', 'department'])
            ->orderByRaw("CASE WHEN status = 'resolved' THEN 1 ELSE 0 END ASC")
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END ASC")
            ->orderByDesc('created_at')
            ->limit(10);

        if ($this->selectedMonth) {
            $date = Carbon::parse($this->selectedMonth);
            $query->whereYear('created_at', $date->year)
                  ->whereMonth('created_at', $date->month);
        }

        if (!$user->hasRole('Super Admin') && !$user->can('complaints.view.all')) {
            if ($user->can('complaints.view.own_department') && $employee) {
                $query->where(function ($q) use ($employee) {
                    $q->where('employee_id', $employee->id)
                        ->orWhere('department_id', $employee->department_id);
                });
            } elseif ($user->can('complaints.view.self') && $employee) {
                $query->where('employee_id', $employee->id);
            } elseif (!$user->can('employees.manage.suggestions')) {
                if ($employee) {
                    $query->where('employee_id', $employee->id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } elseif ($employee) {
                $query->where(function ($q) use ($employee) {
                    $q->where('employee_id', $employee->id)
                        ->orWhere('department_id', $employee->department_id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $this->suggestions = $query->get();
    }

    public function updatedSelectedMonth()
    {
        $this->loadSuggestions();
    }

    public function refresh()
    {
        $this->loadSuggestions();
    }

    public function getAvailableMonths(): array
    {
        $months = [];
        $current = Carbon::now();
        $months[] = [
            'value' => $current->format('Y-m'),
            'label' => $current->format('F Y') . ' (' . __('Current') . ')',
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

    public function render()
    {
        return view('livewire.dashboard.suggestions-shortcut', [
            'availableMonths' => $this->getAvailableMonths(),
        ]);
    }
}
