<?php

namespace App\Livewire\Leaves;

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

    // Sample Data (Replace with actual database queries later)
    public $leaveRequests = [];

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.view.self')) {
            abort(403);
        }

        $this->loadSampleData();
    }

    public function loadSampleData()
    {
        // Sample data for current logged-in user (Sarah Johnson) - replace with actual database queries
        $this->leaveRequests = collect([
            [
                'id' => 2,
                'employee_name' => 'Sarah Johnson',
                'employee_id' => 'EMP002',
                'department' => 'HR',
                'position' => 'HR Manager',
                'manager' => 'Mike Wilson',
                'leave_type' => 'Vacation Leave',
                'start_date' => '2025-10-20',
                'end_date' => '2025-10-25',
                'total_days' => 6,
                'status' => 'approved',
                'created_at' => '2025-09-28 14:15:00',
                'approved_by' => 'Mike Wilson',
                'approved_at' => '2025-09-29 10:00:00'
            ],
            [
                'id' => 4,
                'employee_name' => 'Sarah Johnson',
                'employee_id' => 'EMP002',
                'department' => 'HR',
                'position' => 'HR Manager',
                'manager' => 'Mike Wilson',
                'leave_type' => 'Sick Leave',
                'start_date' => '2025-11-01',
                'end_date' => '2025-11-01',
                'total_days' => 1,
                'status' => 'pending',
                'created_at' => '2025-10-03 10:00:00',
                'approved_by' => '',
                'approved_at' => ''
            ]
        ]);
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

    public function getFilteredRequests()
    {
        $requests = $this->leaveRequests;

        if ($this->dateFilter) {
            $requests = $requests->filter(function ($request) {
                $createdAt = strtotime($request['created_at']);
                $now = time();
                
                switch ($this->dateFilter) {
                    case 'this_month':
                        return date('Y-m', $createdAt) === date('Y-m', $now);
                    case 'last_month':
                        return date('Y-m', $createdAt) === date('Y-m', strtotime('-1 month', $now));
                    case 'this_quarter':
                        $quarter = ceil(date('n', $createdAt) / 3);
                        $currentQuarter = ceil(date('n', $now) / 3);
                        return date('Y', $createdAt) === date('Y', $now) && $quarter === $currentQuarter;
                    case 'this_year':
                        return date('Y', $createdAt) === date('Y', $now);
                    case 'last_year':
                        return date('Y', $createdAt) === date('Y', strtotime('-1 year', $now));
                    default:
                        return true;
                }
            });
        }

        if ($this->statusFilter) {
            $requests = $requests->filter(function ($request) {
                return $request['status'] === $this->statusFilter;
            });
        }

        if ($this->leaveTypeFilter) {
            $requests = $requests->filter(function ($request) {
                return stripos($request['leave_type'], $this->leaveTypeFilter) !== false;
            });
        }

        // Apply sorting
        $requests = $requests->sortBy(function ($request) {
            switch ($this->sortBy) {
                case 'employee_name':
                    return $request['employee_name'];
                case 'department':
                    return $request['department'];
                case 'leave_type':
                    return $request['leave_type'];
                case 'start_date':
                    return $request['start_date'];
                case 'status':
                    return $request['status'];
                case 'created_at':
                    return $request['created_at'];
                default:
                    return $request['created_at'];
            }
        }, SORT_REGULAR, $this->sortDirection === 'desc');

        return $requests;
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
        $filteredRequests = $this->getFilteredRequests();
        
        return view('livewire.leaves.index', [
            'leaveRequests' => $filteredRequests
        ])->layout('components.layouts.app');
    }
}
