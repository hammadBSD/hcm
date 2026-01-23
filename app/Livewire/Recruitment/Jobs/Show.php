<?php

namespace App\Livewire\Recruitment\Jobs;

use App\Models\Designation;
use App\Models\Recruitment\JobPost;
use App\Models\Recruitment\Pipeline;
use App\Models\Recruitment\PipelineStage;
use App\Models\Recruitment\Candidate;
use App\Models\Recruitment\CandidateAttachment;
use App\Models\Recruitment\CandidatePreviousCompany;
use App\Models\Recruitment\CandidateHistory;
use App\Models\Recruitment\CandidateStageHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public $jobId;
    public $pipelines = [];
    public $selectedPipelineId = 1;
    public $showAddPipelineModal = false;
    public $showAddCardModal = false;
    public $newPipelineName = '';
    public $newCardTitle = '';
    public $newCardDescription = '';
    public $selectedColumn = null;
    public $job = null;
    public $viewMode = 'kanban'; // 'kanban' or 'grid'
    
    // Search and filters
    public $searchQuery = '';
    public $filterStage = '';
    public $filterPosition = '';
    public $filterSource = '';
    
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
    
    // Options
    public $designations = [];
    public $positionOptions = [];
    public $sourceOptions = [];

    public function mount($id)
    {
        $user = Auth::user();
        
        // Check if user is Super Admin
        if (!$user->hasRole('Super Admin')) {
            abort(403, 'Unauthorized access. Only Super Admin can access this module.');
        }

        $this->jobId = $id;
        
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

        // Load job post from database
        $jobPost = JobPost::with(['department', 'designation', 'defaultPipeline.stages'])
            ->findOrFail($id);
        
        $this->job = [
            'id' => $jobPost->id,
            'title' => $jobPost->title,
            'description' => $jobPost->description,
            'department' => $jobPost->department ? $jobPost->department->title : null,
            'entry_level' => $jobPost->entry_level,
            'position_type' => $jobPost->position_type,
            'work_type' => $jobPost->work_type,
            'hiring_priority' => $jobPost->hiring_priority,
            'number_of_positions' => $jobPost->number_of_positions,
            'status' => $jobPost->status,
        ];

        // Load pipelines - use default pipeline for this job post
        $defaultPipeline = $jobPost->defaultPipeline;
        if (!$defaultPipeline) {
            // Get or create default pipeline
            $defaultPipeline = Pipeline::where('is_default', true)->first();
            if (!$defaultPipeline) {
                // Create default pipeline (same logic as in Create.php)
                $defaultPipeline = $this->getOrCreateDefaultPipeline($user->id);
            }
            // Update job post to use this pipeline
            $jobPost->update(['default_pipeline_id' => $defaultPipeline->id]);
        }

        $this->selectedPipelineId = $defaultPipeline->id;
        $this->loadPipelines();
    }

    public $draggedCardId = null;
    public $draggedFromStageId = null;
    public $showMoveCallout = false;
    public $moveCalloutMessage = '';
    public $moveCalloutCardTitle = '';
    public $moveCalloutFromStage = '';
    public $moveCalloutToStage = '';
    
    // Card detail view
    public $showCardDetailModal = false;
    public $selectedCard = null;
    public $selectedCardStageId = null;
    public $cardRating = 0; // Rating for the selected card (0-5, can be 0.5 increments)

    public function dragStart($cardId, $stageId)
    {
        $this->draggedCardId = $cardId;
        $this->draggedFromStageId = $stageId;
    }

    public function dragEnd()
    {
        $this->draggedCardId = null;
        $this->draggedFromStageId = null;
    }

    public function dropCard($targetStageId)
    {
        if (!$this->draggedCardId || !$this->draggedFromStageId || !$targetStageId) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Load candidate from database
            $candidate = Candidate::find($this->draggedCardId);
            if (!$candidate || $candidate->job_post_id != $this->jobId) {
                DB::rollBack();
                return;
            }

            // Get stage names for callout
            $fromStage = PipelineStage::find($this->draggedFromStageId);
            $toStage = PipelineStage::find($targetStageId);
            $fromStageName = $fromStage?->name ?? 'Unknown';
            $toStageName = $toStage?->name ?? 'Unknown';

            // Update candidate stage
            $candidate->update([
                'pipeline_stage_id' => $targetStageId,
            ]);

            // Create stage history entry
            CandidateStageHistory::create([
                'candidate_id' => $candidate->id,
                'from_stage_id' => $this->draggedFromStageId,
                'to_stage_id' => $targetStageId,
                'moved_by' => $user->id,
                'moved_at' => now(),
            ]);

            // Create candidate history entry
            CandidateHistory::create([
                'candidate_id' => $candidate->id,
                'field_name' => 'pipeline_stage_id',
                'old_value' => $fromStageName,
                'new_value' => $toStageName,
                'action_type' => 'stage_changed',
                'notes' => "Moved from {$fromStageName} to {$toStageName}",
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            // Reload pipelines to reflect changes
            $this->loadPipelines();

            // Show success callout
            $this->moveCalloutCardTitle = $candidate->full_name;
            $this->moveCalloutFromStage = $fromStageName;
            $this->moveCalloutToStage = $toStageName;
            $this->moveCalloutMessage = __('Card moved successfully from :from to :to', [
                'from' => $fromStageName,
                'to' => $toStageName
            ]);
            $this->showMoveCallout = true;

            // Reset drag state
            $this->draggedCardId = null;
            $this->draggedFromStageId = null;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to move candidate: ' . $e->getMessage());
        }
    }

    public function dismissMoveCallout()
    {
        $this->showMoveCallout = false;
        $this->moveCalloutMessage = '';
        $this->moveCalloutCardTitle = '';
        $this->moveCalloutFromStage = '';
        $this->moveCalloutToStage = '';
    }

    public function openCardDetail($cardId, $stageId)
    {
        // Load candidate from database
        $candidate = Candidate::with(['designation', 'country', 'province', 'previousCompanies', 'attachments', 'pipelineStage'])
            ->find($cardId);

        if (!$candidate || $candidate->job_post_id != $this->jobId) {
            return;
        }

        // Format candidate as card
        $card = $this->formatCandidateForCard($candidate);

        $this->selectedCard = $card;
        $this->selectedCardStageId = $candidate->pipeline_stage_id;
        $this->cardRating = $candidate->rating ?? 0;
        $this->showCardDetailModal = true;
    }

    public function closeCardDetail()
    {
        // Save rating to card before closing
        if ($this->selectedCard && $this->cardRating > 0) {
            $this->saveCardRating();
        }
        
        $this->showCardDetailModal = false;
        $this->selectedCard = null;
        $this->selectedCardStageId = null;
        $this->cardRating = 0;
    }

    public function saveCardRating()
    {
        if (!$this->selectedCard || !isset($this->selectedCard['id'])) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Load candidate from database
            $candidate = Candidate::find($this->selectedCard['id']);
            if (!$candidate || $candidate->job_post_id != $this->jobId) {
                DB::rollBack();
                return;
            }

            $oldRating = $candidate->rating ?? 0;

            // Update rating
            $candidate->update([
                'rating' => $this->cardRating,
            ]);

            // Create history entry if rating changed
            if ($oldRating != $this->cardRating) {
                CandidateHistory::create([
                    'candidate_id' => $candidate->id,
                    'field_name' => 'rating',
                    'old_value' => (string) $oldRating,
                    'new_value' => (string) $this->cardRating,
                    'action_type' => 'rating_changed',
                    'notes' => "Rating changed from {$oldRating} to {$this->cardRating}",
                    'changed_by' => $user->id,
                    'changed_at' => now(),
                ]);
            }

            DB::commit();

            // Update selected card
            $this->selectedCard['rating'] = $this->cardRating;

            // Reload pipelines to reflect changes
            $this->loadPipelines();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save rating: ' . $e->getMessage());
        }
    }

    public function updatedCardRating($value)
    {
        // Auto-save rating when changed
        $this->saveCardRating();
    }

    public function updatedSelectedCardStageId($value)
    {
        if ($this->selectedCard && $this->selectedCardStageId && $value != $this->selectedCardStageId) {
            $this->moveCardToStage($value);
        }
    }

    public function moveCardToStage($targetStageId)
    {
        if (!$this->selectedCard || !$this->selectedCardStageId || !$targetStageId) {
            return;
        }

        // Use the existing dropCard logic
        $this->draggedCardId = $this->selectedCard['id'];
        $this->draggedFromStageId = $this->selectedCardStageId;
        $this->dropCard($targetStageId);
        
        // Refresh the selected card
        $pipeline = collect($this->pipelines)->firstWhere('id', $this->selectedPipelineId);
        if ($pipeline) {
            $targetStage = collect($pipeline['stages'])->firstWhere('id', $targetStageId);
            if ($targetStage && isset($targetStage['cards'])) {
                $updatedCard = collect($targetStage['cards'])->firstWhere('id', $this->selectedCard['id']);
                if ($updatedCard) {
                    $this->selectedCard = $updatedCard;
                    $this->selectedCardStageId = $targetStageId;
                }
            }
        }
    }

    public function getSelectedPipelineProperty()
    {
        if (empty($this->pipelines)) {
            return null;
        }
        return collect($this->pipelines)->firstWhere('id', $this->selectedPipelineId) ?? $this->pipelines[0] ?? null;
    }

    public function getAllCandidatesProperty()
    {
        // Reload from database to get latest data
        $query = Candidate::where('job_post_id', $this->jobId)
            ->with(['designation', 'pipelineStage']);

        // Apply search filter
        if (!empty($this->searchQuery)) {
            $search = $this->searchQuery;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply stage filter
        if (!empty($this->filterStage)) {
            $query->where('pipeline_stage_id', $this->filterStage);
        }

        // Apply position filter
        if (!empty($this->filterPosition)) {
            $query->where('position', $this->filterPosition);
        }

        // Apply source filter
        if (!empty($this->filterSource)) {
            $query->where('source', $this->filterSource);
        }

        $candidates = $query->get();

        // Format candidates for display
        return $candidates->map(function ($candidate) {
            $card = $this->formatCandidateForCard($candidate);
            $card['stage_name'] = $candidate->pipelineStage?->name ?? 'Unknown';
            return $card;
        });
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function openAddPipelineModal()
    {
        $this->newPipelineName = '';
        $this->showAddPipelineModal = true;
    }

    public function closeAddPipelineModal()
    {
        $this->showAddPipelineModal = false;
    }

    public function addPipeline()
    {
        // UI only - will implement functionality later
        $this->closeAddPipelineModal();
    }

    public function openAddCardModal($stageId)
    {
        $this->selectedColumn = $stageId;
        $this->resetCandidateFields();
        $this->showAddCardModal = true;
    }

    public function closeAddCardModal()
    {
        $this->showAddCardModal = false;
        $this->selectedColumn = null;
        $this->resetCandidateFields();
    }

    private function resetCandidateFields()
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
        $this->newCardTitle = '';
        $this->newCardDescription = '';
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
        unset($this->previousCompanies[$index]);
        $this->previousCompanies = array_values($this->previousCompanies);
    }

    public function addCard()
    {
        // Validate required fields
        $this->validate([
            'candidateFirstName' => 'required|string|max:255',
            'candidateLastName' => 'required|string|max:255',
            'candidateEmail' => 'required|email|max:255',
            'candidatePhone' => 'nullable|string|max:20',
            'candidateDob' => 'nullable|date',
            'candidatePosition' => 'nullable|string|max:255',
            'candidateDesignation' => 'nullable|exists:designations,id',
            'candidateExperience' => 'nullable|numeric|min:0|max:50',
            'candidateCurrentAddress' => 'nullable|string|max:500',
            'candidateCurrentCompany' => 'nullable|string|max:255',
            'candidateCity' => 'nullable|string|max:100',
            'candidateCountry' => 'nullable|exists:countries,id',
            'candidateSource' => 'nullable|string|max:100',
            'candidateNoticePeriod' => 'nullable|string|max:100',
            'candidateLinkedIn' => 'nullable|url|max:255',
            'candidateExpectedSalary' => 'nullable|numeric|min:0',
            'candidateAvailabilityDate' => 'nullable|date',
            'previousCompanies.*.company' => 'nullable|string|max:255',
            'previousCompanies.*.position' => 'nullable|string|max:255',
            'previousCompanies.*.duration' => 'nullable|string|max:100',
            'candidateAttachments.*' => 'nullable|file|max:20480', // 20MB max per file
        ]);

        if (!$this->selectedColumn) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Get applicant number (auto-increment per job post)
            $lastApplicant = Candidate::where('job_post_id', $this->jobId)
                ->orderBy('applicant_number', 'desc')
                ->first();
            $applicantNumber = $lastApplicant ? ($lastApplicant->applicant_number + 1) : 1;

            // Create candidate
            $candidate = Candidate::create([
                'job_post_id' => $this->jobId,
                'pipeline_stage_id' => $this->selectedColumn,
                'applicant_number' => $applicantNumber,
                'first_name' => $this->candidateFirstName,
                'last_name' => $this->candidateLastName,
                'date_of_birth' => $this->candidateDob ?: null,
                'description' => $this->newCardDescription ?: null,
                'email' => $this->candidateEmail,
                'phone' => $this->candidatePhone ?: null,
                'linkedin_url' => $this->candidateLinkedIn ?: null,
                'position' => $this->candidatePosition ?: null,
                'designation_id' => $this->candidateDesignation ?: null,
                'experience' => $this->candidateExperience ?: null,
                'source' => $this->candidateSource ?: null,
                'current_address' => $this->candidateCurrentAddress ?: null,
                'city' => $this->candidateCity ?: null,
                'country_id' => $this->candidateCountry ?: null,
                'current_company' => $this->candidateCurrentCompany ?: null,
                'notice_period' => $this->candidateNoticePeriod ?: null,
                'expected_salary' => $this->candidateExpectedSalary ?: null,
                'availability_date' => $this->candidateAvailabilityDate ?: null,
                'rating' => 0,
                'status' => 'active',
                'created_by' => $user->id,
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
                        // Parse duration if provided (format: "YYYY-MM - YYYY-MM")
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

            // Create history entry
            CandidateHistory::create([
                'candidate_id' => $candidate->id,
                'action_type' => 'created',
                'notes' => 'Candidate added',
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            // Reload pipelines to show new candidate
            $this->loadPipelines();
            $this->closeAddCardModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to add candidate: ' . $e->getMessage());
        }
    }

    /**
     * Load pipelines with stages and candidates
     */
    private function loadPipelines()
    {
        $pipeline = Pipeline::with(['stages' => function ($query) {
            $query->orderBy('order');
        }])->find($this->selectedPipelineId);

        if (!$pipeline) {
            $this->pipelines = [];
            return;
        }

        // Load candidates for this job post grouped by stage
        $candidates = Candidate::where('job_post_id', $this->jobId)
            ->with(['designation', 'previousCompanies', 'attachments'])
            ->get();

        // Build stages with candidates
        $stages = $pipeline->stages->map(function ($stage) use ($candidates) {
            $stageCandidates = $candidates->where('pipeline_stage_id', $stage->id)
                ->map(function ($candidate) {
                    return $this->formatCandidateForCard($candidate);
                })
                ->values()
                ->toArray();

            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color,
                'cards' => $stageCandidates,
            ];
        })->toArray();

        $this->pipelines = [[
            'id' => $pipeline->id,
            'name' => $pipeline->name,
            'stages' => $stages,
        ]];
    }

    /**
     * Format candidate model to card array format
     */
    private function formatCandidateForCard($candidate)
    {
        return [
            'id' => $candidate->id,
            'title' => $candidate->full_name,
            'description' => $candidate->description ?: '',
            'candidate_name' => $candidate->full_name,
            'candidate_first_name' => $candidate->first_name,
            'candidate_last_name' => $candidate->last_name,
            'candidate_email' => $candidate->email,
            'candidate_phone' => $candidate->phone,
            'candidate_dob' => $candidate->date_of_birth?->format('Y-m-d'),
            'candidate_position' => $candidate->position,
            'candidate_designation' => $candidate->designation?->name,
            'candidate_experience' => $candidate->experience,
            'candidate_current_address' => $candidate->current_address,
            'candidate_current_company' => $candidate->current_company,
            'candidate_city' => $candidate->city,
            'candidate_country' => $candidate->country?->name,
            'candidate_source' => $candidate->source,
            'candidate_notice_period' => $candidate->notice_period,
            'candidate_linkedin' => $candidate->linkedin_url,
            'candidate_expected_salary' => $candidate->expected_salary,
            'candidate_availability_date' => $candidate->availability_date?->format('Y-m-d'),
            'candidate_previous_companies' => $candidate->previousCompanies->map(function ($pc) {
                return [
                    'company' => $pc->company_name,
                    'position' => $pc->position,
                    'duration' => ($pc->from_date ? $pc->from_date->format('Y-m') : '') . ' - ' . ($pc->to_date ? $pc->to_date->format('Y-m') : ''),
                ];
            })->toArray(),
            'candidate_attachments' => $candidate->attachments->pluck('file_path')->toArray(),
            'applicant_number' => $candidate->applicant_number,
            'rating' => $candidate->rating ?? 0,
            'stage_id' => $candidate->pipeline_stage_id,
        ];
    }

    /**
     * Get or create default pipeline with stages
     */
    private function getOrCreateDefaultPipeline($userId)
    {
        $pipeline = Pipeline::where('is_default', true)->first();

        if (!$pipeline) {
            $pipeline = Pipeline::create([
                'name' => 'Default Pipeline',
                'description' => 'Default recruitment pipeline',
                'is_default' => true,
                'created_by' => $userId,
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

        return $pipeline;
    }

    public function render()
    {
        return view('livewire.recruitment.jobs.show')
            ->layout('components.layouts.app');
    }
}
