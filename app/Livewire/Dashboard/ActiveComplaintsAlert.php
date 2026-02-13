<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\EmployeeSuggestion;
use Illuminate\Support\Facades\Auth;

class ActiveComplaintsAlert extends Component
{
    public function getActiveComplaintsCount(): int
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();

        $query = EmployeeSuggestion::query()
            ->where('status', '!=', 'resolved');

        if ($user->hasRole('Super Admin') || $user->can('complaints.view.all')) {
            // Alert is department-specific: show only for auth user's department (don't show if no department)
            if ($employee && $employee->department_id) {
                $query->where('department_id', $employee->department_id);
            } else {
                return 0;
            }
        } elseif ($user->can('complaints.view.own_department') && $employee) {
            $query->where(function ($q) use ($employee) {
                $q->where('employee_id', $employee->id)
                    ->orWhere('department_id', $employee->department_id);
            });
        } elseif ($user->can('complaints.view.self') && $employee) {
            $query->where('employee_id', $employee->id);
        } elseif ($user->can('employees.manage.suggestions') && $employee) {
            $query->where(function ($q) use ($employee) {
                $q->where('employee_id', $employee->id)
                    ->orWhere('department_id', $employee->department_id);
            });
        } elseif ($employee) {
            $query->where('employee_id', $employee->id);
        } else {
            return 0;
        }

        return $query->count();
    }

    public function render()
    {
        $count = $this->getActiveComplaintsCount();

        return view('livewire.dashboard.active-complaints-alert', [
            'activeComplaintsCount' => $count,
        ]);
    }
}
