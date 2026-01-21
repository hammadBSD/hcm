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
    public $sortBy = '';
    public $sortDirection = 'asc';

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

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
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

        // Get unique departments for filter
        $departments = ['IT', 'Marketing', 'HR', 'Finance', 'Operations'];

        return view('livewire.recruitment.index', [
            'jobs' => $jobs,
            'departments' => $departments,
        ])->layout('components.layouts.app');
    }
}
