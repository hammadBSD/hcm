<?php

namespace App\Livewire\Leaves;

use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest as LeaveRequestModel;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $dateFilter = '';
    public $statusFilter = '';
    public $leaveTypeFilter = '';
    public $selectAll = false;
    public $selectedRequests = [];
    
    // Sorting Properties
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    public array $summary = [
        'entitled' => 0.0,
        'used' => 0.0,
        'pending' => 0.0,
        'balance' => 0.0,
    ];
    public $leaveTypeOptions = [];

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.view.self')) {
            abort(403);
        }

        $this->loadLeaveTypeOptions();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedLeaveTypeFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->dateFilter = '';
        $this->statusFilter = '';
        $this->leaveTypeFilter = '';
        $this->selectedRequests = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedRequests = $this->getFilteredRequests()->pluck('id')->toArray();
        } else {
            $this->selectedRequests = [];
        }
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getFilteredRequests(): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        $user->loadMissing('employee.department', 'employee.designation');
        $employee = $user->employee;

        if (! $employee) {
            return collect();
        }

        $query = LeaveRequestModel::query()
            ->with([
                'leaveType:id,name,code',
                'employee.department',
                'employee.designation',
            ])
            ->where('employee_id', $employee->id);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->leaveTypeFilter) {
            $query->where('leave_type_id', $this->leaveTypeFilter);
        }

        if ($this->dateFilter) {
            [$start, $end] = $this->resolveDateRange($this->dateFilter);
            $query->whereBetween('created_at', [$start, $end]);
        }

        $requests = $query->get()->map(function (LeaveRequestModel $request) {
            $departmentName = optional($request->employee->department)->title
                ?? $request->employee->department
                ?? __('Not assigned');

            $designationName = optional($request->employee->designation)->name
                ?? $request->employee->designation
                ?? __('No designation');

            return [
                'id' => $request->id,
                'department' => $departmentName,
                'position' => $designationName,
                'leave_type' => $request->leaveType?->name ?? __('Unknown'),
                'leave_type_code' => $request->leaveType?->code,
                'start_date' => optional($request->start_date)->format('Y-m-d'),
                'end_date' => optional($request->end_date)->format('Y-m-d'),
                'total_days' => (float) $request->total_days,
                'status' => $request->status,
                'created_at' => optional($request->created_at)->toDateTimeString(),
            ];
        });

        $requests = $requests->sortBy(function ($request) {
            return match ($this->sortBy) {
                'department' => $request['department'],
                'leave_type' => $request['leave_type'],
                'start_date' => $request['start_date'],
                'status' => $request['status'],
                'created_at' => $request['created_at'],
                default => $request['created_at'],
            };
        }, SORT_NATURAL | SORT_FLAG_CASE, $this->sortDirection === 'desc');

        return $requests->values();
    }

    public function viewRequest($id)
    {
        // Handle view logic
        session()->flash('info', "Viewing leave request #{$id}");
    }

    public function approveRequest($id)
    {
        $this->authorizeTeamApproval();

        // Handle approval logic
        session()->flash('success', "Leave request #{$id} has been approved.");
    }

    public function rejectRequest($id)
    {
        $this->authorizeTeamApproval();

        // Handle rejection logic
        session()->flash('error', "Leave request #{$id} has been rejected.");
    }

    public function editRequest($id)
    {
        // Handle edit logic
        session()->flash('info', "Editing leave request #{$id}");
    }

    public function createLeaveRequest()
    {
        $this->authorizeRequestSubmission();

        // Handle create logic
        session()->flash('info', "Creating new leave request");
    }

    protected function loadSummary(): void
    {
        $user = Auth::user();

        if (!$user || ! $user->relationLoaded('employee')) {
            $user?->loadMissing('employee');
        }

        $employee = $user?->employee;

        if (! $employee) {
            $this->summary = [
                'entitled' => 0.0,
                'used' => 0.0,
                'pending' => 0.0,
                'balance' => 0.0,
            ];
            return;
        }

        $balances = EmployeeLeaveBalance::query()
            ->where('employee_id', $employee->id)
            ->get();

        if ($balances->isEmpty()) {
            $this->summary = [
                'entitled' => 0.0,
                'used' => 0.0,
                'pending' => 0.0,
                'balance' => 0.0,
            ];
            return;
        }

        $this->summary = [
            'entitled' => (float) $balances->sum('entitled'),
            'used' => (float) $balances->sum('used'),
            'pending' => (float) $balances->sum('pending'),
            'balance' => (float) $balances->sum('balance'),
        ];
    }

    protected function loadLeaveTypeOptions(): void
    {
        $this->leaveTypeOptions = LeaveType::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->toArray();
    }

    protected function resolveDateRange(string $filter): array
    {
        $now = Carbon::now();

        return match ($filter) {
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ],
            'this_quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_year' => [
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
            ],
            default => [$now->copy()->startOfCentury(), $now->copy()->endOfCentury()],
        };
    }

    protected function authorizeTeamApproval(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.approve.requests')) {
            abort(403);
        }
    }

    protected function authorizeRequestSubmission(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.request.submit')) {
            abort(403);
        }
    }

    public function render()
    {
        $this->loadSummary();

        $filteredRequests = $this->getFilteredRequests();
        
        return view('livewire.leaves.index', [
            'leaveRequests' => $filteredRequests
        ])->layout('components.layouts.app');
    }
}
