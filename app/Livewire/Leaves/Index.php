<?php

namespace App\Livewire\Leaves;

use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $search = '';
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
        $this->loadSampleData();
    }

    public function loadSampleData()
    {
        // Sample data - replace with actual database queries
        $this->leaveRequests = collect([
            [
                'id' => 1,
                'employee_name' => 'John Doe',
                'employee_id' => 'EMP001',
                'department' => 'IT',
                'position' => 'Software Developer',
                'manager' => 'Jane Smith',
                'leave_type' => 'Sick Leave',
                'start_date' => '2025-10-15',
                'end_date' => '2025-10-17',
                'total_days' => 3,
                'status' => 'pending',
                'created_at' => '2025-10-01 09:30:00',
                'approved_by' => '',
                'approved_at' => ''
            ],
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
                'id' => 3,
                'employee_name' => 'Michael Brown',
                'employee_id' => 'EMP003',
                'department' => 'Finance',
                'position' => 'Accountant',
                'manager' => 'Lisa Davis',
                'leave_type' => 'Personal Leave',
                'start_date' => '2025-10-10',
                'end_date' => '2025-10-10',
                'total_days' => 1,
                'status' => 'rejected',
                'created_at' => '2025-09-30 11:45:00',
                'approved_by' => 'Lisa Davis',
                'approved_at' => '2025-10-01 08:30:00'
            ]
        ]);
    }

    public function updatedSearch()
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

        if ($this->search) {
            $requests = $requests->filter(function ($request) {
                return stripos($request['employee_name'], $this->search) !== false ||
                       stripos($request['employee_id'], $this->search) !== false;
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
        // Handle approval logic
        session()->flash('success', "Leave request #{$id} has been approved.");
    }

    public function rejectRequest($id)
    {
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
        // Handle create logic
        session()->flash('info', "Creating new leave request");
    }

    public function render()
    {
        $filteredRequests = $this->getFilteredRequests();
        
        return view('livewire.leaves.index', [
            'leaveRequests' => $filteredRequests
        ])->layout('components.layouts.app');
    }
}
