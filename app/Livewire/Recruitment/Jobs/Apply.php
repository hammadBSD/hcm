<?php

namespace App\Livewire\Recruitment\Jobs;

use App\Models\Designation;
use App\Models\Recruitment\JobPost;
use App\Models\Recruitment\Pipeline;
use App\Models\Recruitment\PipelineStage;
use App\Models\Recruitment\Candidate;
use App\Models\Recruitment\CandidateAttachment;
use App\Models\Recruitment\CandidatePreviousCompany;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Apply extends Component
{
    use WithFileUploads;

    public $jobId;
    public $job = null;
    
    // Candidate fields
    public $candidateFirstName = '';
    public $candidateLastName = '';
    public $candidateEmail = '';
    public $candidatePhone = '';
    public $candidateDob = '';
    public $candidatePosition = '';
    public $candidateDesignation = '';
    public $candidateExperience = '';
    public $candidateCurrentAddress = '';
    public $candidateCurrentCompany = '';
    public $candidateCity = '';
    public $candidateCountry = '';
    public $candidateSource = '';
    public $candidateNoticePeriod = '';
    public $candidateLinkedIn = '';
    public $candidateExpectedSalary = '';
    public $candidateCurrentSalary = '';
    public $candidateAvailabilityDate = '';
    
    // Previous companies (dynamic array)
    public $previousCompanies = [];
    
    // Attachments
    public $candidateAttachments = [];
    
    // Description
    public $candidateDescription = '';
    
    // Options
    public $designations = [];
    public $positionOptions = [];
    public $sourceOptions = [];
    
    public $showSuccessMessage = false;
    public $referrerId = null;

    public function mount($uniqueId)
    {
        // Load job post from database by unique_id
        $jobPost = JobPost::where('unique_id', $uniqueId)
            ->with(['department', 'designation'])
            ->firstOrFail();
        
        $this->jobId = $jobPost->id;
        
        // Capture referrer from query parameter
        $this->referrerId = request()->query('ref');
        
        $this->job = [
            'id' => $jobPost->id,
            'title' => $jobPost->title,
            'description' => $jobPost->description,
            'department' => $jobPost->department?->title,
            'entry_level' => $jobPost->entry_level,
            'position_type' => $jobPost->position_type,
            'work_type' => $jobPost->work_type,
        ];
        
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
        
        // Position options
        $this->positionOptions = [
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'half-day' => 'Half Day',
            'contract' => 'Contract',
            'freelance' => 'Freelance',
        ];
        
        // Source options
        $this->sourceOptions = [
            'linkedin' => 'LinkedIn',
            'glassdoor' => 'Glassdoor',
            'indeed' => 'Indeed',
            'company-website' => 'Company Website',
            'referral' => 'Referral',
            'job-board' => 'Job Board',
            'self' => 'Self Applied',
            'recruitment-agency' => 'Recruitment Agency',
            'other' => 'Other',
        ];
        
        // Initialize with one default "Current Employment" row
        $this->previousCompanies = [
            [
                'company' => '',
                'position' => '',
                'duration' => '',
            ]
        ];
    }

    public function submitApplication()
    {
        // Validate required fields
        $this->validate([
            'candidateFirstName' => 'required|string|max:255',
            'candidateLastName' => 'required|string|max:255',
            'candidateEmail' => 'required|email|max:255',
            'candidatePhone' => 'nullable|string|max:20',
            'candidateDob' => 'nullable|date',
            'candidatePosition' => 'nullable|string|max:255',
            'candidateDesignation' => 'nullable|string|max:255',
            'candidateExperience' => 'nullable|numeric|min:0|max:50',
            'candidateCurrentAddress' => 'nullable|string|max:500',
            'candidateCurrentCompany' => 'nullable|string|max:255',
            'candidateCity' => 'nullable|string|max:100',
            'candidateCountry' => 'nullable|string|max:100',
            'candidateSource' => 'nullable|string|max:100',
            'candidateNoticePeriod' => 'nullable|numeric|min:0|max:365',
            'candidateLinkedIn' => 'nullable|url|max:255',
            'candidateExpectedSalary' => 'nullable|numeric|min:0',
            'candidateCurrentSalary' => 'nullable|numeric|min:0',
            'candidateAvailabilityDate' => 'nullable|date',
            'previousCompanies.*.company' => 'nullable|string|max:255',
            'previousCompanies.*.position' => 'nullable|string|max:255',
            'previousCompanies.*.duration' => 'nullable|string|max:100',
            'candidateAttachments.*' => 'nullable|file|max:20480', // 20MB max per file
        ]);

        try {
            DB::beginTransaction();

            // Get job post and default pipeline
            $jobPost = JobPost::with('defaultPipeline.stages')->findOrFail($this->jobId);
            $pipeline = $jobPost->defaultPipeline;
            
            if (!$pipeline) {
                // Get or create default pipeline
                $pipeline = Pipeline::where('is_default', true)->first();
                if (!$pipeline) {
                    // Create default pipeline
                    $pipeline = Pipeline::create([
                        'name' => 'Default Pipeline',
                        'description' => 'Default recruitment pipeline',
                        'is_default' => true,
                        'created_by' => 1, // System user
                    ]);

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
                
                // Update job post to use this pipeline
                $jobPost->update(['default_pipeline_id' => $pipeline->id]);
            }

            // Get first stage (Applied)
            $firstStage = $pipeline->stages()->orderBy('order')->first();
            if (!$firstStage) {
                throw new \Exception('No pipeline stages found');
            }

            // Get applicant number
            $lastApplicant = Candidate::where('job_post_id', $this->jobId)
                ->orderBy('applicant_number', 'desc')
                ->first();
            $applicantNumber = $lastApplicant ? ($lastApplicant->applicant_number + 1) : 1;

            // Create candidate
            $candidate = Candidate::create([
                'job_post_id' => $this->jobId,
                'pipeline_stage_id' => $firstStage->id,
                'applicant_number' => $applicantNumber,
                'first_name' => $this->candidateFirstName,
                'last_name' => $this->candidateLastName,
                'date_of_birth' => $this->candidateDob ?: null,
                'description' => $this->candidateDescription ?: null,
                'email' => $this->candidateEmail,
                'phone' => $this->candidatePhone ?: null,
                'linkedin_url' => $this->candidateLinkedIn ?: null,
                'position' => $this->candidatePosition ?: null,
                'designation_id' => $this->candidateDesignation ?: null,
                'experience' => $this->candidateExperience ?: null,
                'source' => $this->candidateSource ?: 'self',
                'current_address' => $this->candidateCurrentAddress ?: null,
                'city' => $this->candidateCity ?: null,
                'country_id' => $this->candidateCountry ?: null,
                'current_company' => $this->candidateCurrentCompany ?: null,
                'notice_period' => $this->candidateNoticePeriod ?: null,
                'expected_salary' => $this->candidateExpectedSalary ?: null,
                'current_salary' => $this->candidateCurrentSalary ?: null,
                'availability_date' => $this->candidateAvailabilityDate ?: null,
                'rating' => 0,
                'status' => 'active',
                'created_by' => $this->referrerId ?: null, // Set referrer if provided
            ]);

            // Handle file uploads
            if (!empty($this->candidateAttachments)) {
                foreach ($this->candidateAttachments as $attachment) {
                    $path = $attachment->store('candidate-attachments', 'public');
                    CandidateAttachment::create([
                        'candidate_id' => $candidate->id,
                        'file_path' => $path,
                        'file_name' => $attachment->getClientOriginalName(),
                        'file_type' => $attachment->getMimeType(),
                        'file_size' => $attachment->getSize(),
                    ]);
                }
            }

            // Handle previous companies
            if (!empty($this->previousCompanies)) {
                foreach ($this->previousCompanies as $index => $company) {
                    if (!empty($company['company'])) {
                        // Parse duration if provided
                        $fromDate = null;
                        $toDate = null;
                        if (!empty($company['duration'])) {
                            $parts = explode(' - ', $company['duration']);
                            if (count($parts) == 2) {
                                $fromDate = !empty(trim($parts[0])) ? trim($parts[0]) . '-01' : null;
                                $toDate = !empty(trim($parts[1])) ? trim($parts[1]) . '-01' : null;
                            }
                        }

                        CandidatePreviousCompany::create([
                            'candidate_id' => $candidate->id,
                            'company_name' => $company['company'],
                            'position' => $company['position'] ?? null,
                            'from_date' => $fromDate,
                            'to_date' => $toDate,
                            'order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();

            $this->showSuccessMessage = true;
            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to submit application: ' . $e->getMessage());
        }
    }
    
    private function resetForm()
    {
        $this->candidateFirstName = '';
        $this->candidateLastName = '';
        $this->candidateEmail = '';
        $this->candidatePhone = '';
        $this->candidateDob = '';
        $this->candidatePosition = '';
        $this->candidateDesignation = '';
        $this->candidateExperience = '';
        $this->candidateCurrentAddress = '';
        $this->candidateCurrentCompany = '';
        $this->candidateCity = '';
        $this->candidateCountry = '';
        $this->candidateSource = '';
        $this->candidateNoticePeriod = '';
        $this->candidateLinkedIn = '';
        $this->candidateExpectedSalary = '';
        $this->candidateCurrentSalary = '';
        $this->candidateAvailabilityDate = '';
        $this->previousCompanies = [];
        $this->candidateAttachments = [];
        $this->candidateDescription = '';
    }
    
    public function removeAttachment($index)
    {
        unset($this->candidateAttachments[$index]);
        $this->candidateAttachments = array_values($this->candidateAttachments);
    }
    
    public function addPreviousCompany()
    {
        $this->previousCompanies[] = [
            'company' => '',
            'position' => '',
            'duration' => '',
        ];
    }
    
    public function removePreviousCompany($index)
    {
        // Don't allow removing the first row (Current Employment)
        if ($index === 0) {
            return;
        }
        unset($this->previousCompanies[$index]);
        $this->previousCompanies = array_values($this->previousCompanies);
    }

    public function render()
    {
        return view('livewire.recruitment.jobs.apply')
            ->layout('components.layouts.recruitment.public');
    }
}
