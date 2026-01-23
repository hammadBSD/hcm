<?php

namespace App\Livewire\Recruitment;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $selectedStatus = '';
    public $selectedEntryLevel = '';
    public $selectedPositionType = '';
    public $selectedWorkType = '';
    public $selectedPriority = '';
    public $sortBy = '';
    public $sortDirection = 'asc';
    public $viewMode = 'grid'; // 'grid' or 'kanban'
    public $showFilters = false;

    public function mount()
    {
        $user = Auth::user();
        
        // Check if user is Super Admin
        if (!$user || !$user->hasRole('Super Admin')) {
            abort(403, 'Unauthorized access. Only Super Admin can access this module.');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatedSelectedEntryLevel()
    {
        $this->resetPage();
    }

    public function updatedSelectedPositionType()
    {
        $this->resetPage();
    }

    public function updatedSelectedWorkType()
    {
        $this->resetPage();
    }

    public function updatedSelectedPriority()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedDepartment = '';
        $this->selectedStatus = '';
        $this->selectedEntryLevel = '';
        $this->selectedPositionType = '';
        $this->selectedWorkType = '';
        $this->selectedPriority = '';
        $this->resetPage();
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
    
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function render()
    {
        // Mock data for now - will be replaced with actual database queries later
        $jobs = collect([
            [
                'id' => 1,
                'title' => 'Senior Software Developer',
                'department' => 'IT',
                'entry_level' => 'Senior',
                'position_type' => 'Full Time',
                'work_type' => 'Remote',
                'hiring_priority' => 'Urgent',
                'number_of_positions' => 2,
                'applications_count' => 15,
                'status' => 'active',
                'created_at' => now()->subDays(5),
            ],
            [
                'id' => 2,
                'title' => 'Marketing Manager',
                'department' => 'Marketing',
                'entry_level' => 'Mid-Senior',
                'position_type' => 'Full Time',
                'work_type' => 'Hybrid',
                'hiring_priority' => 'Medium',
                'number_of_positions' => 1,
                'applications_count' => 8,
                'status' => 'active',
                'created_at' => now()->subDays(10),
            ],
            [
                'id' => 3,
                'title' => 'HR Intern',
                'department' => 'HR',
                'entry_level' => 'Intern',
                'position_type' => 'Part Time',
                'work_type' => 'On-Site',
                'hiring_priority' => 'Low',
                'number_of_positions' => 3,
                'applications_count' => 25,
                'status' => 'active',
                'created_at' => now()->subDays(2),
            ],
        ]);

        // Apply filters
        if ($this->search) {
            $jobs = $jobs->filter(function ($job) {
                return stripos($job['title'], $this->search) !== false;
            });
        }

        if ($this->selectedDepartment) {
            $jobs = $jobs->filter(function ($job) {
                return $job['department'] === $this->selectedDepartment;
            });
        }

        if ($this->selectedStatus) {
            $jobs = $jobs->filter(function ($job) {
                return $job['status'] === $this->selectedStatus;
            });
        }

        if ($this->selectedEntryLevel) {
            $jobs = $jobs->filter(function ($job) {
                return strtolower($job['entry_level']) === strtolower($this->selectedEntryLevel);
            });
        }

        if ($this->selectedPositionType) {
            $jobs = $jobs->filter(function ($job) {
                return strtolower($job['position_type']) === strtolower($this->selectedPositionType);
            });
        }

        if ($this->selectedWorkType) {
            $jobs = $jobs->filter(function ($job) {
                return strtolower($job['work_type']) === strtolower($this->selectedWorkType);
            });
        }

        if ($this->selectedPriority) {
            $jobs = $jobs->filter(function ($job) {
                return strtolower($job['hiring_priority']) === strtolower($this->selectedPriority);
            });
        }

        // Get unique departments for filter
        $departments = ['IT', 'Marketing', 'HR', 'Finance', 'Operations'];

        // Filter options
        $entryLevelOptions = [
            'intern' => 'Intern',
            'junior' => 'Junior',
            'mid-junior' => 'Mid-Junior',
            'mid-level' => 'Mid Level',
            'mid-senior' => 'Mid-Senior',
            'senior' => 'Senior',
            'team-lead' => 'Team Lead',
            'above' => 'Team Lead and Above',
        ];

        $positionTypeOptions = [
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'half-day' => 'Half Day',
            'contract' => 'Contract',
            'freelance' => 'Freelance',
        ];

        $workTypeOptions = [
            'remote' => 'Remote',
            'on-site' => 'On-Site',
            'hybrid' => 'Hybrid',
        ];

        $priorityOptions = [
            'low' => 'Low',
            'medium' => 'Medium',
            'urgent' => 'Urgent',
            'very-urgent' => 'Very Urgent',
        ];

        return view('livewire.recruitment.index', [
            'jobs' => $jobs,
            'departments' => $departments,
            'entryLevelOptions' => $entryLevelOptions,
            'positionTypeOptions' => $positionTypeOptions,
            'workTypeOptions' => $workTypeOptions,
            'priorityOptions' => $priorityOptions,
        ])->layout('components.layouts.app');
    }
}
