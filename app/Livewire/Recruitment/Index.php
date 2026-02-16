<?php

namespace App\Livewire\Recruitment;

use App\Models\Department;
use App\Models\Recruitment\JobPost;
use App\Models\Recruitment\JobPostHistory;
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
        
        // Check if user is Super Admin or HR Manager
        if (!$user || (!$user->hasRole('Super Admin') && !$user->hasRole('HR Manager'))) {
            abort(403, 'Unauthorized access. Only Super Admin and HR Manager can access this module.');
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

    public function updateStatus($jobId, $status)
    {
        $user = Auth::user();
        
        // Check if user is Super Admin or HR Manager
        if (!$user || (!$user->hasRole('Super Admin') && !$user->hasRole('HR Manager'))) {
            session()->flash('error', 'Unauthorized access.');
            return;
        }

        $validStatuses = ['active', 'paused', 'closed'];
        if (!in_array($status, $validStatuses)) {
            session()->flash('error', 'Invalid status.');
            return;
        }

        try {
            $jobPost = JobPost::findOrFail($jobId);
            $oldStatus = $jobPost->status;
            
            $jobPost->update(['status' => $status]);
            
            // Create history entry
            JobPostHistory::create([
                'job_post_id' => $jobPost->id,
                'action_type' => 'status_changed',
                'notes' => "Status changed from {$oldStatus} to {$status}",
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            session()->flash('message', 'Job post status updated successfully.');
            
            // Reset pagination to first page to show updated data
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    public function deleteJobPost($jobId)
    {
        $user = Auth::user();

        if (!$user || (!$user->hasRole('Super Admin') && !$user->hasRole('HR Manager'))) {
            session()->flash('error', 'Unauthorized access.');
            return;
        }

        try {
            $jobPost = JobPost::findOrFail($jobId);
            $jobPost->delete();
            session()->flash('message', 'Job post deleted successfully.');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete job post: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Build query with relationships
        $query = JobPost::with(['department', 'candidates'])
            ->withCount('candidates as applications_count');

        // Apply search filter
        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        // Apply department filter
        if ($this->selectedDepartment) {
            $query->where('department_id', $this->selectedDepartment);
        }

        // Apply status filter
        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        // Apply entry level filter
        if ($this->selectedEntryLevel) {
            $query->where('entry_level', $this->selectedEntryLevel);
        }

        // Apply position type filter
        if ($this->selectedPositionType) {
            $query->where('position_type', $this->selectedPositionType);
        }

        // Apply work type filter
        if ($this->selectedWorkType) {
            $query->where('work_type', $this->selectedWorkType);
        }

        // Apply priority filter
        if ($this->selectedPriority) {
            $query->where('hiring_priority', $this->selectedPriority);
        }

        // Apply sorting
        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Get paginated results
        $jobPosts = $query->paginate(10);

        // Format jobs for view
        $jobs = $jobPosts->map(function ($jobPost) {
            return [
                'id' => $jobPost->id,
                'title' => $jobPost->title,
                'department' => $jobPost->department?->title ?? 'N/A',
                'entry_level' => $this->formatEntryLevel($jobPost->entry_level),
                'position_type' => $this->formatPositionType($jobPost->position_type),
                'work_type' => $this->formatWorkType($jobPost->work_type),
                'hiring_priority' => $this->formatPriority($jobPost->hiring_priority),
                'number_of_positions' => $jobPost->number_of_positions,
                'applications_count' => $jobPost->applications_count,
                'status' => $jobPost->status,
                'created_at' => $jobPost->created_at,
            ];
        });

        // Get departments from database
        $departments = Department::where('status', 'active')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->toArray();

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
            'jobPosts' => $jobPosts, // For pagination
            'departments' => $departments,
            'entryLevelOptions' => $entryLevelOptions,
            'positionTypeOptions' => $positionTypeOptions,
            'workTypeOptions' => $workTypeOptions,
            'priorityOptions' => $priorityOptions,
        ])->layout('components.layouts.app');
    }

    private function formatEntryLevel($level)
    {
        $levels = [
            'intern' => 'Intern',
            'junior' => 'Junior',
            'mid-junior' => 'Mid-Junior',
            'mid-level' => 'Mid Level',
            'mid-senior' => 'Mid-Senior',
            'senior' => 'Senior',
            'team-lead' => 'Team Lead',
            'above' => 'Team Lead and Above',
        ];
        return $levels[$level] ?? $level;
    }

    private function formatPositionType($type)
    {
        $types = [
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'half-day' => 'Half Day',
            'contract' => 'Contract',
            'freelance' => 'Freelance',
        ];
        return $types[$type] ?? $type;
    }

    private function formatWorkType($type)
    {
        $types = [
            'remote' => 'Remote',
            'on-site' => 'On-Site',
            'hybrid' => 'Hybrid',
        ];
        return $types[$type] ?? $type;
    }

    private function formatPriority($priority)
    {
        $priorities = [
            'low' => 'Low',
            'medium' => 'Medium',
            'urgent' => 'Urgent',
            'very-urgent' => 'Very Urgent',
        ];
        return $priorities[$priority] ?? $priority;
    }
}
