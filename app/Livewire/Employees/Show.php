<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use App\Models\EmployeeIncrement;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Livewire\Component;

class Show extends Component
{
    public $employeeId;
    public $employee;
    public $additionalInfo;
    public $organizationalInfo;
    public $salaryLegalCompliance;
    public $user;

    public function mount($id)
    {
        $this->employeeId = $id;
        
        // Find the employee record by user_id (since the URL uses user_id)
        $this->employee = Employee::where('user_id', $this->employeeId)
            ->with(['user', 'group', 'additionalInfo', 'organizationalInfo', 'salaryLegalCompliance'])
            ->first();
            
        if ($this->employee) {
            $this->user = $this->employee->user;
            $this->additionalInfo = $this->employee->additionalInfo;
            $this->organizationalInfo = $this->employee->organizationalInfo;
            $this->salaryLegalCompliance = $this->employee->salaryLegalCompliance;
        }
    }

    public function render()
    {
        $increments = $this->employee
            ? EmployeeIncrement::where('employee_id', $this->employee->id)
                ->orderByDesc('last_increment_date')
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $approvedLeavesYear = collect();
        $rejectedLeavesYear = collect();
        if ($this->employee) {
            $approvedLeavesYear = $this->leavesCurrentYearPayload($this->employee->id, LeaveRequest::STATUS_APPROVED);
            $rejectedLeavesYear = $this->leavesCurrentYearPayload($this->employee->id, LeaveRequest::STATUS_REJECTED);
        }

        return view('livewire.employees.show', [
            'increments' => $increments,
            'approvedLeavesYear' => $approvedLeavesYear,
            'rejectedLeavesYear' => $rejectedLeavesYear,
            'leavesYear' => now()->year,
        ])->layout('components.layouts.app');
    }

    /**
     * Leave requests for the current calendar year by status.
     *
     * @return \Illuminate\Support\Collection<int, array{id: int, leave_type: string, leave_type_code: string|null, start_date: string, end_date: string, total_days: float, reason: string}>
     */
    protected function leavesCurrentYearPayload(int $employeeId, string $status)
    {
        $year = (int) now()->year;
        $yearStart = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfDay();

        return LeaveRequest::query()
            ->with([
                'leaveType:id,name,code',
                'events' => fn ($q) => $q->where('event_type', 'rejected')->latest(),
            ])
            ->where('employee_id', $employeeId)
            ->where('status', $status)
            ->where('start_date', '<=', $yearEnd)
            ->where('end_date', '>=', $yearStart)
            ->orderByDesc('start_date')
            ->get()
            ->map(function (LeaveRequest $r) use ($status) {
                $reason = trim((string) ($r->reason ?? ''));
                if ($reason === '' && $status === LeaveRequest::STATUS_REJECTED) {
                    $reason = trim((string) ($r->events->first()?->notes ?? ''));
                }

                return [
                    'id' => $r->id,
                    'leave_type' => $r->leaveType?->name ?? __('Unknown'),
                    'leave_type_code' => $r->leaveType?->code,
                    'start_date' => $r->start_date?->format('M d, Y') ?? '—',
                    'end_date' => $r->end_date?->format('M d, Y') ?? '—',
                    'total_days' => (float) $r->total_days,
                    'reason' => $reason !== '' ? $reason : '—',
                ];
            })
            ->values();
    }
}
