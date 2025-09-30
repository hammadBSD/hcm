<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class EmployeeList extends Component
{
    use WithPagination;

        // Search and filter properties
        public $search = '';
        public $filterDepartment = '';
        public $filterStatus = '';
        public $filterRole = '';
        public $hireDateFrom = '';
        public $hireDateTo = '';
        public $showAdvancedFilters = false;

        // Advanced filter properties
        public $filterCountry = '';
        public $filterProvince = '';
        public $filterCity = '';
        public $filterArea = '';
        public $filterVendor = '';
        public $filterStation = '';
        public $filterSubDepartment = '';
        public $filterEmployeeGroup = '';
        public $filterDesignation = '';
        public $filterDivision = '';
        public $filterEmployeeCode = '';
        public $filterEmployeeName = '';
        public $filterEmployeeStatus = '';
        public $filterDocumentsAttached = '';
        public $filterRolesTemplate = '';
        public $filterEmiratesId = '';
        public $filterFlag = '';
        public $filterReportsTo = '';
        public $filterBlacklistWhitelist = '';
        public $filterPositionCode = '';

    // Sorting properties
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    // Pagination
    protected $paginationTheme = 'tailwind';

    public function render()
    {
        return view('livewire.employees.list', [
            'employees' => $this->getEmployees()
        ])->layout('components.layouts.app');
    }

    public function getEmployees()
    {
        $query = User::query();

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('employee_id', 'like', '%' . $this->search . '%');
            });
        }

        // Apply department filter
        if ($this->filterDepartment) {
            $query->where('department', $this->filterDepartment);
        }

        // Apply status filter
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Apply role filter
        if ($this->filterRole) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->filterRole);
            });
        }

        // Apply date filters
        if ($this->hireDateFrom) {
            $query->whereDate('created_at', '>=', $this->hireDateFrom);
        }

        if ($this->hireDateTo) {
            $query->whereDate('created_at', '<=', $this->hireDateTo);
        }

        // Apply sorting
        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query->paginate(10);
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleAdvancedFilters()
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function refresh()
    {
        $this->resetPage();
    }

    public function export()
    {
        // Export functionality
        $this->dispatch('export-employees');
    }

    public function import()
    {
        // Redirect to import page
        return redirect()->route('employees.import');
    }

    public function resetPassword($employeeId)
    {
        // Reset password functionality
        $this->dispatch('reset-password', $employeeId);
    }

    public function deactivate($employeeId)
    {
        // Deactivate employee functionality
        $this->dispatch('deactivate-employee', $employeeId);
    }
}
