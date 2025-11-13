<?php

namespace App\Livewire\Leaves\EmployeesLeaves;

use App\Models\Employee;
use App\Models\LeaveRequest as LeaveRequestModel;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $search = '';
    public $employeeFilter = '';
    public $dateFilter = '';
    public $statusFilter = '';
    public $leaveTypeFilter = '';
    public $selectAll = false;
    public $selectedRequests = [];
    
    // Sorting Properties
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    public array $leaveTypeOptions = [];
    public array $employeeOptions = [];

    public function mount()
    {
        $user = Auth::user();

        if (!$user || (!$user->can('leaves.manage.all') && !$user->can('leaves.view.all'))) {
            abort(403);
        }

        $this->loadEmployeeOptions();
        $this->loadLeaveTypeOptions();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedEmployeeFilter()
    {
        $this->resetPage();
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
        $this->search = '';
        $this->employeeFilter = '';
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
        $query = LeaveRequestModel::query()
            ->with([
                'employee.user',
                'employee.department',
                'employee.designation',
                'leaveType',
            ]);

        if ($this->search) {
            $searchTerm = trim($this->search);
            $query->where(function ($builder) use ($searchTerm) {
                $builder->whereHas('employee.user', function ($sub) use ($searchTerm) {
                    $sub->where('name', 'like', '%' . $searchTerm . '%');
                })->orWhereHas('employee', function ($sub) use ($searchTerm) {
                    $sub->where('employee_code', 'like', '%' . $searchTerm . '%')
                        ->orWhereRaw("concat_ws(' ', first_name, last_name) like ?", ['%' . $searchTerm . '%']);
                });
            });
        }

        if ($this->employeeFilter) {
            $query->where('employee_id', $this->employeeFilter);
        }

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
            $employee = $request->employee;
            $user = $employee?->user;

            $name = $user?->name
                ?? trim($employee?->first_name . ' ' . $employee?->last_name)
                ?: __('Unknown Employee');

            $initials = collect(explode(' ', $name))
                ->filter()
                ->map(fn ($segment) => Str::upper(mb_substr($segment, 0, 1)))
                ->join('');

            if ($initials === '') {
                $initials = Str::upper(mb_substr($name, 0, 2));
            }

            $departmentName = optional($employee?->department)->title
                ?? $employee?->department
                ?? __('Not assigned');

            $designationName = optional($employee?->designation)->name
                ?? $employee?->designation
                ?? __('No designation');

            $startDate = $request->start_date?->format('Y-m-d');
            $endDate = $request->end_date?->format('Y-m-d');
            $createdAt = $request->created_at;

            return [
                'id' => $request->id,
                'employee_name' => $name,
                'employee_initials' => Str::upper(Str::limit($initials, 2, '')),
                'employee_code' => $employee?->employee_code ?? __('N/A'),
                'department' => $departmentName,
                'position' => $designationName,
                'leave_type' => $request->leaveType?->name ?? __('Unknown'),
                'leave_type_code' => $request->leaveType?->code,
                'total_days' => (float) $request->total_days,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $request->status,
                'created_at' => $createdAt?->toDateTimeString(),
                'created_date' => $createdAt?->format('M d, Y'),
                'created_time' => $createdAt?->format('h:i A'),
            ];
        });

        $requests = $requests->sortBy(function ($request) {
            return match ($this->sortBy) {
                'employee_name' => $request['employee_name'],
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
        $this->authorizeApproval();

        // Handle approval logic
        session()->flash('success', "Leave request #{$id} has been approved.");
    }

    public function rejectRequest($id)
    {
        $this->authorizeApproval();

        // Handle rejection logic
        session()->flash('error', "Leave request #{$id} has been rejected.");
    }

    public function editRequest($id)
    {
        $this->authorizeAllManagement();

        // Handle edit logic
        session()->flash('info', "Editing leave request #{$id}");
    }

    public function createLeaveRequest()
    {
        $this->authorizeAllManagement();

        // Handle create logic
        session()->flash('info', "Creating new leave request");
    }

    protected function authorizeApproval(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.approve.requests')) {
            abort(403);
        }
    }

    protected function authorizeAllManagement(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.manage.all')) {
            abort(403);
        }
    }

    public function render()
    {
        $filteredRequests = $this->getFilteredRequests();
        
        return view('livewire.leaves.employees-leaves.index', [
            'leaveRequests' => $filteredRequests,
            'employeeOptions' => $this->employeeOptions,
            'leaveTypeOptions' => $this->leaveTypeOptions,
        ])->layout('components.layouts.app');
    }

    protected function loadEmployeeOptions(): void
    {
        $this->employeeOptions = Employee::query()
            ->with('user')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'user_id', 'employee_code', 'first_name', 'last_name'])
            ->map(function (Employee $employee) {
                $name = $employee->user?->name
                    ?? trim($employee->first_name . ' ' . $employee->last_name)
                    ?: __('Employee #:id', ['id' => $employee->id]);

                return [
                    'id' => $employee->id,
                    'label' => $name,
                    'code' => $employee->employee_code,
                ];
            })
            ->toArray();
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
}
