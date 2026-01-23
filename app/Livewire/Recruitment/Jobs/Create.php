<?php

namespace App\Livewire\Recruitment\Jobs;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public $jobTitle = '';
    public $jobDescription = '';
    public $budget = '';
    public $candidateExperience = '';
    public $entryLevel = '';
    public $position = '';
    public $designation = '';
    public $hiringPriority = '';
    public $department = '';
    public $numberOfPositions = 1;
    public $workType = 'full-time';
    public $location = '';
    public $applicationDeadline = '';
    public $startDate = '';
    public $requiredSkills = '';
    public $benefits = '';
    public $reportingTo = '';

    public $entryLevelOptions = [
        'intern' => 'Intern',
        'junior' => 'Junior',
        'mid-junior' => 'Mid-Junior',
        'mid-level' => 'Mid Level',
        'mid-senior' => 'Mid-Senior',
        'senior' => 'Senior',
        'team-lead' => 'Team Lead',
        'above' => 'Team Lead and Above',
    ];

    public $positionOptions = [
        'full-time' => 'Full Time',
        'part-time' => 'Part Time',
        'half-day' => 'Half Day',
        'contract' => 'Contract',
        'freelance' => 'Freelance',
    ];

    public $hiringPriorityOptions = [
        'low' => 'Low',
        'medium' => 'Medium',
        'urgent' => 'Urgent',
        'very-urgent' => 'Very Urgent',
    ];

    public $workTypeOptions = [
        'remote' => 'Remote',
        'on-site' => 'On-Site',
        'hybrid' => 'Hybrid',
    ];

    public $departments = [];
    public $designations = [];
    public $reportingToOptions = [];
    public $lineManagers = [];
    public $lineManager = '';

    public function mount()
    {
        $user = Auth::user();
        
        // Check if user is Super Admin
        if (!$user || !$user->hasRole('Super Admin')) {
            abort(403, 'Unauthorized access. Only Super Admin can access this module.');
        }

        // Load departments
        $this->departments = Department::where('status', 'active')
            ->orderBy('title')
            ->get()
            ->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'title' => $dept->title,
                ];
            })->toArray();

        // Load designations
        $this->designations = Designation::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($designation) {
                return [
                    'id' => $designation->id,
                    'name' => $designation->name,
                ];
            })->toArray();

        // Load active employees for Line Manager
        $this->lineManagers = Employee::where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => trim($employee->first_name . ' ' . $employee->last_name) . ($employee->employee_code ? ' (' . $employee->employee_code . ')' : ''),
                ];
            })->toArray();

        // Set default values
        $this->hiringPriority = 'medium';
        $this->workType = 'on-site';
    }

    public function save()
    {
        // UI only - will implement functionality later
        session()->flash('message', 'Job post created successfully!');
        return $this->redirect(route('recruitment.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.recruitment.jobs.create')
            ->layout('components.layouts.app');
    }
}
