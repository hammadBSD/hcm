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
use App\Models\Recruitment\RecruitmentSetting;
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
    public $candidateCurrentSalary = '';
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
        
        // Check if user is Super Admin or HR Manager
        if (!$user || (!$user->hasRole('Super Admin') && !$user->hasRole('HR Manager'))) {
            abort(403, 'Unauthorized access. Only Super Admin and HR Manager can access this module.');
        }

        $this->jobId = $id;
        
        // Load recruitment settings
        $this->settings = RecruitmentSetting::getInstance();
        
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
        $jobPost = JobPost::with(['department', 'designation', 'defaultPipeline.stages', 'createdBy'])
            ->withCount('candidates')
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
            'candidates_count' => $jobPost->candidates_count ?? 0,
            'created_by_name' => $jobPost->createdBy ? $jobPost->createdBy->name : 'Unknown',
            'unique_id' => $jobPost->unique_id,
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
    public $commentText = ''; // Comment input text
    public $activities = []; // Activity feed for selected card
    public $settings = null; // Recruitment settings
    public $negotiatedSalary = null; // Negotiated salary
    public $offeredSalary = null; // Offered salary
    public $modalAttachments = []; // Attachments for the modal (existing candidate)
    public $showAttachmentInput = false; // Toggle for showing attachment input

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

            // Check access restriction
            if ($this->settings && $this->settings->restrict_applicant_access) {
                if ($candidate->created_by != $user->id) {
                    DB::rollBack();
                    session()->flash('error', 'You do not have permission to move this applicant. Only the user who added this applicant can move it.');
                    return;
                }
            }

            // Check rating requirement
            if ($this->settings && $this->settings->require_rating_before_move) {
                if (!$candidate->rating || $candidate->rating == 0) {
                    DB::rollBack();
                    session()->flash('error', 'Please rate this candidate before moving to the next stage.');
                    return;
                }
            }

            // Check if rejected candidates can be moved
            if ($this->settings && $this->settings->prevent_move_rejected_candidates) {
                if ($candidate->status === 'rejected') {
                    DB::rollBack();
                    session()->flash('error', 'Rejected candidates cannot be moved to any stage.');
                    return;
                }
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

            // Reload activities if modal is open
            if ($this->showCardDetailModal && $this->selectedCard && $this->selectedCard['id'] == $candidate->id) {
                $this->loadActivities($candidate->id);
            }

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
        $candidate = Candidate::with(['designation', 'country', 'province', 'previousCompanies', 'attachments', 'pipelineStage', 'createdBy'])
            ->find($cardId);

        if (!$candidate || $candidate->job_post_id != $this->jobId) {
            return;
        }

        // Check access restriction
        if ($this->settings && $this->settings->restrict_applicant_access) {
            $user = Auth::user();
            if ($candidate->created_by != $user->id) {
                session()->flash('error', 'You do not have permission to view this applicant. Only the user who added this applicant can access it.');
                return;
            }
        }

        // Format candidate as card
        $card = $this->formatCandidateForCard($candidate);

        $this->selectedCard = $card;
        $this->selectedCardStageId = $candidate->pipeline_stage_id;
        $this->cardRating = (int) round($candidate->rating ?? 0);
        $this->commentText = '';
        $this->negotiatedSalary = $candidate->negotiated_salary;
        $this->offeredSalary = $candidate->offered_salary;
        $this->loadActivities($cardId);
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
        $this->commentText = '';
        $this->negotiatedSalary = null;
        $this->offeredSalary = null;
        $this->modalAttachments = [];
        $this->showAttachmentInput = false;
        $this->activities = [];
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
            $newRating = (int) round($this->cardRating);

            // Update rating
            $candidate->update([
                'rating' => $newRating,
            ]);
            
            // Update local property to match saved value
            $this->cardRating = $newRating;

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

            // Reload activities
            $this->loadActivities($candidate->id);

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
        // This method is called when selectedCardStageId is updated via wire:model
        // But we'll handle the move via wire:change instead
    }

    public function moveCardToStage($targetStageId)
    {
        if (!$this->selectedCard || !isset($this->selectedCard['id']) || !$targetStageId) {
            return;
        }

        // Get the current candidate to find the current stage
        $candidate = Candidate::find($this->selectedCard['id']);
        if (!$candidate || $candidate->job_post_id != $this->jobId) {
            return;
        }

        $fromStageId = $candidate->pipeline_stage_id;
        
        // Don't move if already in the target stage
        if ($fromStageId == $targetStageId) {
            return;
        }

        // Use the existing dropCard logic
        $this->draggedCardId = $this->selectedCard['id'];
        $this->draggedFromStageId = $fromStageId;
        $this->dropCard($targetStageId);
        
        // After dropCard completes, refresh the selected card
        $candidate->refresh();
        $candidate->load(['designation', 'country', 'province', 'previousCompanies', 'attachments', 'pipelineStage', 'createdBy']);
        
        $card = $this->formatCandidateForCard($candidate);
        $this->selectedCard = $card;
        $this->selectedCardStageId = $candidate->pipeline_stage_id;
        $this->loadActivities($candidate->id);
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
        $this->candidateCurrentSalary = '';
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
            'candidateCurrentSalary' => 'nullable|numeric|min:0',
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
                'current_salary' => $this->candidateCurrentSalary ?: null,
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
            ->with(['designation', 'previousCompanies', 'attachments', 'createdBy'])
            ->get();

        $outcomeStatuses = ['rejected', 'no_show', 'not_reachable', 'not_interested'];

        // Build stages with candidates: cards with no outcome status first, outcome-status cards last
        $stages = $pipeline->stages->map(function ($stage) use ($candidates, $outcomeStatuses) {
            $stageCandidates = $candidates->where('pipeline_stage_id', $stage->id)
                ->map(function ($candidate) {
                    return $this->formatCandidateForCard($candidate);
                })
                ->sortBy(function ($card) use ($outcomeStatuses) {
                    $status = $card['status'] ?? '';
                    // No status / active / any non-outcome → 0 (show first). Outcome status → 1 (show last).
                    return in_array($status, $outcomeStatuses) ? 1 : 0;
                })
                ->values()
                ->toArray();

            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color,
                'order' => $stage->order,
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
            'candidate_current_salary' => $candidate->current_salary,
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
            'status' => $candidate->status,
            'referrer_id' => $candidate->created_by,
            'referrer_name' => $candidate->createdBy?->name ?? null,
        ];
    }

    /**
     * Add a comment to the candidate
     */
    public function addComment()
    {
        if (!$this->selectedCard || !isset($this->selectedCard['id']) || empty(trim($this->commentText))) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Create comment history entry
            CandidateHistory::create([
                'candidate_id' => $this->selectedCard['id'],
                'action_type' => 'comment',
                'notes' => trim($this->commentText),
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            // Reload activities
            $this->loadActivities($this->selectedCard['id']);
            $this->commentText = '';
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to add comment: ' . $e->getMessage());
        }
    }

    /**
     * Hire the candidate
     */
    public function hireCandidate()
    {
        if (!$this->selectedCard || !isset($this->selectedCard['id'])) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $candidate = Candidate::find($this->selectedCard['id']);
            if (!$candidate || $candidate->job_post_id != $this->jobId) {
                DB::rollBack();
                return;
            }

            // Update candidate status
            $candidate->update([
                'status' => 'hired',
                'is_hired' => true,
                'hired_by' => $user->id,
                'hired_at' => now(),
            ]);

            // Create history entry
            CandidateHistory::create([
                'candidate_id' => $candidate->id,
                'action_type' => 'hired',
                'notes' => 'Candidate was hired',
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            // Reload activities and refresh card
            $this->loadActivities($candidate->id);
            $candidate->refresh();
            $this->selectedCard = $this->formatCandidateForCard($candidate);
            $this->loadPipelines();
            
            session()->flash('message', 'Candidate has been hired successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to hire candidate: ' . $e->getMessage());
        }
    }

    /**
     * Reject the candidate
     */
    public function rejectCandidate()
    {
        if (!$this->selectedCard || !isset($this->selectedCard['id'])) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $candidate = Candidate::find($this->selectedCard['id']);
            if (!$candidate || $candidate->job_post_id != $this->jobId) {
                DB::rollBack();
                return;
            }

            // Update candidate status
            $candidate->update([
                'status' => 'rejected',
            ]);

            // Create history entry
            CandidateHistory::create([
                'candidate_id' => $candidate->id,
                'action_type' => 'rejected',
                'notes' => 'Candidate was rejected',
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            // Reload activities and refresh card
            $this->loadActivities($candidate->id);
            $candidate->refresh();
            $this->selectedCard = $this->formatCandidateForCard($candidate);
            $this->loadPipelines();
            
            session()->flash('message', 'Candidate has been rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to reject candidate: ' . $e->getMessage());
        }
    }

    /**
     * Undo reject - change candidate status back to active
     */
    public function undoRejectCandidate()
    {
        if (!$this->selectedCard || !isset($this->selectedCard['id'])) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $candidate = Candidate::find($this->selectedCard['id']);
            if (!$candidate || $candidate->job_post_id != $this->jobId) {
                DB::rollBack();
                return;
            }

            // Update candidate status back to active
            $candidate->update([
                'status' => 'active',
            ]);

            // Create history entry
            CandidateHistory::create([
                'candidate_id' => $candidate->id,
                'action_type' => 'status_changed',
                'notes' => ' undone Rejection, candidate status changed back to active',
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            // Reload activities and refresh card
            $this->loadActivities($candidate->id);
            $candidate->refresh();
            $this->selectedCard = $this->formatCandidateForCard($candidate);
            $this->loadPipelines();
            
            session()->flash('message', 'Candidate rejection has been undone.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to undo reject: ' . $e->getMessage());
        }
    }

    /**
     * Mark candidate as Not Reachable
     */
    public function markCandidateNotReachable()
    {
        $this->setCandidateOutcomeStatus('not_reachable', 'Not Reachable', 'Candidate marked as not reachable.');
    }

    /**
     * Mark candidate as Not Interested
     */
    public function markCandidateNotInterested()
    {
        $this->setCandidateOutcomeStatus('not_interested', 'Not Interested', 'Candidate marked as not interested.');
    }

    /**
     * Mark candidate as No Show
     */
    public function markCandidateNoShow()
    {
        // $this->setCandidateOutcomeStatus('no_show', 'No Show', 'Candidate marked as no show.');
        if (!$this->selectedCard || !isset($this->selectedCard['id'])) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $candidate = Candidate::find($this->selectedCard['id']);
            if (!$candidate || $candidate->job_post_id != $this->jobId) {
                DB::rollBack();
                return;
            }

            // Update candidate status
            $candidate->update([
                'status' => 'no_show',
            ]);

            // Create history entry
            CandidateHistory::create([
                'candidate_id' => $candidate->id,
                'action_type' => 'no_show',
                'notes' => 'Candidate marked as no show',
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            // Reload activities and refresh card
            $this->loadActivities($candidate->id);
            $candidate->refresh();
            $this->selectedCard = $this->formatCandidateForCard($candidate);
            $this->loadPipelines();
            
            session()->flash('message', 'Candidate is a No Show.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to set no show for candidate: ' . $e->getMessage());
        }
    }

    /**
     * Undo No Show - change candidate status back to active
     */
    public function undoNoShow()
    {
        $this->undoOutcomeStatus('no_show', 'No Show');
    }

    /**
     * Undo Not Reachable - change candidate status back to active
     */
    public function undoNotReachable()
    {
        $this->undoOutcomeStatus('not_reachable', 'Not Reachable');
    }

    /**
     * Undo Not Interested - change candidate status back to active
     */
    public function undoNotInterested()
    {
        $this->undoOutcomeStatus('not_interested', 'Not Interested');
    }

    /**
     * Shared undo logic for outcome statuses (no_show, not_reachable, not_interested)
     */
    private function undoOutcomeStatus(string $fromStatus, string $label)
    {
        if (!$this->selectedCard || !isset($this->selectedCard['id'])) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $candidate = Candidate::find($this->selectedCard['id']);
            if (!$candidate || $candidate->job_post_id != $this->jobId || $candidate->status !== $fromStatus) {
                DB::rollBack();
                return;
            }

            $candidate->update(['status' => 'active']);

            CandidateHistory::create([
                'candidate_id' => $candidate->id,
                'action_type' => 'status_changed',
                'notes' => "Undone {$label}, candidate status changed back to active",
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            $this->loadActivities($candidate->id);
            $candidate->refresh();
            $this->selectedCard = $this->formatCandidateForCard($candidate);
            $this->loadPipelines();

            session()->flash('message', __(":label has been undone.", ['label' => $label]));
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', __('Failed to undo: ') . $e->getMessage());
        }
    }

    /**
     * Set candidate outcome status (shared logic for not_reachable, not_interested, no_show)
     */
    private function setCandidateOutcomeStatus(string $status, string $actionLabel, string $historyNotes)
    {
        if (!$this->selectedCard || !isset($this->selectedCard['id'])) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $candidate = Candidate::find($this->selectedCard['id']);
            if (!$candidate || $candidate->job_post_id != $this->jobId) {
                DB::rollBack();
                return;
            }

            $candidate->update(['status' => $status]);

            CandidateHistory::create([
                'candidate_id' => $candidate->id,
                'action_type' => $status,
                'notes' => $historyNotes,
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            $this->loadActivities($candidate->id);
            $candidate->refresh();
            $this->selectedCard = $this->formatCandidateForCard($candidate);
            $this->loadPipelines();

            session()->flash('message', __('Candidate has been marked as :status.', ['status' => $actionLabel]));
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', __('Failed to update: ') . $e->getMessage());
        }
    }

    /**
     * Save negotiated and offered salary
     */
    public function saveSalaries()
    {
        if (!$this->selectedCard || !isset($this->selectedCard['id'])) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $candidate = Candidate::find($this->selectedCard['id']);
            if (!$candidate || $candidate->job_post_id != $this->jobId) {
                DB::rollBack();
                return;
            }

            $oldNegotiated = $candidate->negotiated_salary;
            $oldOffered = $candidate->offered_salary;
            
            // Convert empty strings to null
            $newNegotiated = $this->negotiatedSalary ? (float) $this->negotiatedSalary : null;
            $newOffered = $this->offeredSalary ? (float) $this->offeredSalary : null;

            // Update candidate salaries
            $candidate->update([
                'negotiated_salary' => $newNegotiated,
                'offered_salary' => $newOffered,
            ]);

            // Create history entries for changes
            if ((float) ($oldNegotiated ?? 0) != (float) ($newNegotiated ?? 0)) {
                CandidateHistory::create([
                    'candidate_id' => $candidate->id,
                    'field_name' => 'negotiated_salary',
                    'old_value' => $oldNegotiated ? number_format((float) $oldNegotiated, 2) : 'N/A',
                    'new_value' => $newNegotiated ? number_format($newNegotiated, 2) : 'N/A',
                    'action_type' => 'salary_changed',
                    'notes' => 'Negotiated salary updated',
                    'changed_by' => $user->id,
                    'changed_at' => now(),
                ]);
            }

            if ((float) ($oldOffered ?? 0) != (float) ($newOffered ?? 0)) {
                CandidateHistory::create([
                    'candidate_id' => $candidate->id,
                    'field_name' => 'offered_salary',
                    'old_value' => $oldOffered ? number_format((float) $oldOffered, 2) : 'N/A',
                    'new_value' => $newOffered ? number_format($newOffered, 2) : 'N/A',
                    'action_type' => 'salary_changed',
                    'notes' => 'Offered salary updated',
                    'changed_by' => $user->id,
                    'changed_at' => now(),
                ]);
            }

            DB::commit();

            // Reload activities and refresh card
            $this->loadActivities($candidate->id);
            $candidate->refresh();
            $candidate->load(['attachments']);
            $this->selectedCard = $this->formatCandidateForCard($candidate);
            
            session()->flash('message', 'Salaries saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save salaries: ' . $e->getMessage());
        }
    }

    /**
     * Handle card action (Members, Labels, Dates, Checklist, Attachment)
     */
    public function handleCardAction($action)
    {
        if (!$this->selectedCard || !isset($this->selectedCard['id'])) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $actionLabels = [
                'members' => 'Added members',
                'labels' => 'Added labels',
                'dates' => 'Added dates',
                'checklist' => 'Added checklist',
                'attachment' => 'Added attachment',
            ];

            $actionLabel = $actionLabels[$action] ?? ucfirst($action);

            // Create history entry
            CandidateHistory::create([
                'candidate_id' => $this->selectedCard['id'],
                'action_type' => 'card_action',
                'field_name' => $action,
                'notes' => $actionLabel,
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            // Reload activities
            $this->loadActivities($this->selectedCard['id']);
            
            session()->flash('message', $actionLabel . ' action recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to record action: ' . $e->getMessage());
        }
    }

    /**
     * Add attachments to existing candidate
     */
    public function addAttachments()
    {
        if (!$this->selectedCard || !isset($this->selectedCard['id']) || empty($this->modalAttachments)) {
            return;
        }

        // Validate attachments
        $this->validate([
            'modalAttachments.*' => 'nullable|file|max:20480', // 20MB max per file
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $candidate = Candidate::find($this->selectedCard['id']);
            if (!$candidate || $candidate->job_post_id != $this->jobId) {
                DB::rollBack();
                return;
            }

            // Handle file uploads
            foreach ($this->modalAttachments as $attachment) {
                $path = $attachment->store('candidate-attachments', 'public');
                CandidateAttachment::create([
                    'candidate_id' => $candidate->id,
                    'file_path' => $path,
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_type' => $attachment->getMimeType(),
                    'file_size' => $attachment->getSize(),
                ]);
            }

            // Create history entry
            CandidateHistory::create([
                'candidate_id' => $candidate->id,
                'action_type' => 'attachment_added',
                'notes' => 'Added ' . count($this->modalAttachments) . ' attachment(s)',
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            DB::commit();

            // Clear attachments and refresh
            $this->modalAttachments = [];
            $this->showAttachmentInput = false;
            
            // Reload activities and refresh card
            $this->loadActivities($candidate->id);
            $candidate->refresh();
            $candidate->load(['attachments']);
            $this->selectedCard = $this->formatCandidateForCard($candidate);
            
            session()->flash('message', 'Attachments added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to add attachments: ' . $e->getMessage());
        }
    }

    /**
     * Toggle attachment input visibility
     */
    public function toggleAttachmentInput()
    {
        $this->showAttachmentInput = !$this->showAttachmentInput;
        if (!$this->showAttachmentInput) {
            $this->modalAttachments = [];
        }
    }

    /**
     * Load activities for the selected candidate
     */
    private function loadActivities($candidateId)
    {
        // Load history entries (exclude stage_changed as we show those from CandidateStageHistory)
        $history = CandidateHistory::where('candidate_id', $candidateId)
            ->where('action_type', '!=', 'stage_changed')
            ->with('changedBy')
            ->orderBy('changed_at', 'desc')
            ->get();

        // Load stage history entries
        $stageHistory = CandidateStageHistory::where('candidate_id', $candidateId)
            ->with(['movedBy', 'fromStage', 'toStage'])
            ->orderBy('moved_at', 'desc')
            ->get();

        // Merge and format activities
        $activities = collect();

        // Add history entries
        foreach ($history as $entry) {
            $activities->push([
                'type' => 'history',
                'action_type' => $entry->action_type,
                'field_name' => $entry->field_name,
                'old_value' => $entry->old_value,
                'new_value' => $entry->new_value,
                'notes' => $entry->notes,
                'user' => $entry->changedBy,
                'user_name' => $entry->changedBy->name ?? 'Unknown',
                'user_initials' => $this->getUserInitials($entry->changedBy),
                'timestamp' => $entry->changed_at,
                'formatted_time' => $entry->changed_at->diffForHumans(),
            ]);
        }

        // Add stage history entries
        foreach ($stageHistory as $entry) {
            $activities->push([
                'type' => 'stage_move',
                'from_stage' => $entry->fromStage?->name ?? 'Unknown',
                'to_stage' => $entry->toStage?->name ?? 'Unknown',
                'notes' => $entry->notes,
                'user' => $entry->movedBy,
                'user_name' => $entry->movedBy->name ?? 'Unknown',
                'user_initials' => $this->getUserInitials($entry->movedBy),
                'timestamp' => $entry->moved_at,
                'formatted_time' => $entry->moved_at->diffForHumans(),
            ]);
        }

        // Sort by timestamp (newest first)
        $this->activities = $activities->sortByDesc('timestamp')->values()->toArray();
    }

    /**
     * Get user initials for avatar
     */
    private function getUserInitials($user)
    {
        if (!$user || !$user->name) {
            return 'U';
        }
        $nameParts = explode(' ', $user->name);
        if (count($nameParts) >= 2) {
            return strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
        }
        return strtoupper(substr($user->name, 0, 2));
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
                ['name' => 'Shortlisted', 'color' => 'indigo', 'order' => 4],
                ['name' => 'Offer', 'color' => 'green', 'order' => 5],
                ['name' => 'Hired', 'color' => 'emerald', 'order' => 6],
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

    /**
     * Check if hire button should be shown
     */
    public function shouldShowHireButton()
    {
        if (!$this->settings || !$this->selectedCard || !$this->selectedCardStageId) {
            return false;
        }

        // If setting is disabled, always show
        if (!$this->settings->show_hire_button_last_stage_only) {
            return true;
        }

        // Get the pipeline and check if current stage is the last one
        $pipeline = Pipeline::with('stages')->find($this->selectedPipelineId);
        if (!$pipeline || !$pipeline->stages->count()) {
            return false;
        }

        $lastStage = $pipeline->stages->sortByDesc('order')->first();
        return $lastStage && $lastStage->id == $this->selectedCardStageId;
    }

    /**
     * Check if user can access this candidate
     */
    public function canAccessCandidate($candidateId)
    {
        if (!$this->settings || !$this->settings->restrict_applicant_access) {
            return true; // No restriction
        }

        $user = Auth::user();
        $candidate = Candidate::find($candidateId);
        
        if (!$candidate) {
            return false;
        }

        // Check if user is the creator
        return $candidate->created_by == $user->id;
    }

    public function render()
    {
        return view('livewire.recruitment.jobs.show')
            ->layout('components.layouts.app');
    }
}
