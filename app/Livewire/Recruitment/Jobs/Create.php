<?php

namespace App\Livewire\Recruitment\Jobs;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Recruitment\JobPost;
use App\Models\Recruitment\Pipeline;
use App\Models\Recruitment\PipelineStage;
use App\Models\Recruitment\JobPostHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        
        // Check if user is Super Admin or HR Manager
        if (!$user || (!$user->hasRole('Super Admin') && !$user->hasRole('HR Manager'))) {
            abort(403, 'Unauthorized access. Only Super Admin and HR Manager can access this module.');
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
        // Validation
        $this->validate([
            'jobTitle' => 'required|string|max:255',
            'jobDescription' => 'nullable|string',
            'department' => 'required|exists:departments,id',
            'designation' => 'required|exists:designations,id',
            'entryLevel' => 'nullable|in:' . implode(',', array_keys($this->entryLevelOptions)),
            'position' => 'required|in:' . implode(',', array_keys($this->positionOptions)),
            'workType' => 'required|in:' . implode(',', array_keys($this->workTypeOptions)),
            'hiringPriority' => 'required|in:' . implode(',', array_keys($this->hiringPriorityOptions)),
            'numberOfPositions' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'applicationDeadline' => 'nullable|date',
            'startDate' => 'nullable|date',
            'requiredSkills' => 'nullable|string',
            'benefits' => 'nullable|string',
            'reportingTo' => 'nullable|exists:employees,id',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Get or create default pipeline
            $defaultPipeline = $this->getOrCreateDefaultPipeline($user->id);

            // Create job post
            $jobPost = JobPost::create([
                'title' => $this->jobTitle,
                'description' => $this->jobDescription ?: null,
                'department_id' => $this->department,
                'designation_id' => $this->designation,
                'entry_level' => $this->entryLevel ?: null,
                'position_type' => $this->position,
                'work_type' => $this->workType,
                'hiring_priority' => $this->hiringPriority,
                'number_of_positions' => $this->numberOfPositions,
                'status' => 'draft',
                'location' => $this->location ?: null,
                'budget' => $this->budget ? (float) $this->budget : null,
                'application_deadline' => $this->applicationDeadline ?: null,
                'start_date' => $this->startDate ?: null,
                'required_skills' => $this->requiredSkills ?: null,
                'benefits' => $this->benefits ?: null,
                'reporting_to_id' => $this->reportingTo ?: null,
                'created_by' => $user->id,
                'default_pipeline_id' => $defaultPipeline->id,
            ]);

            // Create history entry
            JobPostHistory::create([
                'job_post_id' => $jobPost->id,
                'action_type' => 'created',
                'notes' => 'Job post created',
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            session()->flash('message', 'Job post created successfully!');
            return $this->redirect(route('recruitment.jobs.show', $jobPost->id), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to create job post: ' . $e->getMessage());
        }
    }

    /**
     * Get or create default pipeline with stages
     */
    private function getOrCreateDefaultPipeline($userId)
    {
        // Check if default pipeline exists
        $pipeline = Pipeline::where('is_default', true)->first();

        if (!$pipeline) {
            // Create default pipeline
            $pipeline = Pipeline::create([
                'name' => 'Default Pipeline',
                'description' => 'Default recruitment pipeline',
                'is_default' => true,
                'created_by' => $userId,
            ]);

            // Create default stages
            $defaultStages = [
                ['name' => 'Applied', 'color' => 'blue', 'order' => 1],
                ['name' => 'Screening', 'color' => 'yellow', 'order' => 2],
                ['name' => 'Interview', 'color' => 'purple', 'order' => 3],
                ['name' => 'Offer', 'color' => 'green', 'order' => 4],
                ['name' => 'Hired', 'color' => 'emerald', 'order' => 5],
            ];

            foreach ($defaultStages as $stage) {
                PipelineStage::create([
                    'pipeline_id' => $pipeline->id,
                    'name' => $stage['name'],
                    'color' => $stage['color'],
                    'order' => $stage['order'],
                    'is_default' => false,
                ]);
            }
        }

        return $pipeline;
    }

    public function render()
    {
        return view('livewire.recruitment.jobs.create')
            ->layout('components.layouts.app');
    }
}
