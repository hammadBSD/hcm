<?php

namespace App\Livewire\Recruitment\Jobs;

use App\Models\Designation;
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

    public function mount($id)
    {
        $this->jobId = $id;
        
        // Load job data (mock for now, will be from database later)
        $this->job = [
            'id' => $id,
            'title' => 'Senior Software Developer',
            'description' => 'We are looking for an experienced Senior Software Developer to join our dynamic team. You will be responsible for designing, developing, and maintaining high-quality software solutions. The ideal candidate should have strong problem-solving skills, excellent communication abilities, and a passion for writing clean, efficient code.',
            'department' => 'IT',
            'entry_level' => 'Senior',
            'position_type' => 'Full Time',
            'work_type' => 'Remote',
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
            'candidateAvailabilityDate' => 'nullable|date',
            'previousCompanies.*.company' => 'nullable|string|max:255',
            'previousCompanies.*.position' => 'nullable|string|max:255',
            'previousCompanies.*.duration' => 'nullable|string|max:100',
            'candidateAttachments.*' => 'nullable|file|max:20480', // 20MB max per file
        ]);

        // Handle file uploads
        $attachmentPaths = [];
        if (!empty($this->candidateAttachments)) {
            foreach ($this->candidateAttachments as $attachment) {
                $path = $attachment->store('candidate-attachments', 'public');
                $attachmentPaths[] = $path;
            }
        }
        
        // TODO: Save application to database
        // For now, just show success message
        $this->showSuccessMessage = true;
        
        // Reset form after successful submission
        $this->resetForm();
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
