<?php

namespace App\Livewire\Recruitment\Jobs;

use App\Models\Designation;
use Illuminate\Support\Facades\Auth;
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

        // Mock job data - will be replaced with actual database query later
        $this->job = [
            'id' => $id,
            'title' => 'Senior Software Developer',
            'department' => 'IT',
            'entry_level' => 'Senior',
            'position_type' => 'Full Time',
            'work_type' => 'Remote',
            'hiring_priority' => 'Urgent',
            'number_of_positions' => 2,
            'status' => 'active',
        ];

        // Initialize with default pipeline and sample cards
        $this->pipelines = [
            [
                'id' => 1,
                'name' => 'Default Pipeline',
                'stages' => [
                    [
                        'id' => 1, 
                        'name' => 'Applied', 
                        'color' => 'blue', 
                        'cards' => [
                            [
                                'id' => 1,
                                'title' => 'John Doe',
                                'description' => 'Senior Developer with 5+ years experience, Looking for an oppertunity to work. Senior Developer with 5+ years experience, Looking for an oppertunity to work. Senior Developer with 5+ years experience, Looking for an oppertunity to work.',
                                'candidate_name' => 'John Doe',
                            ],
                            [
                                'id' => 2,
                                'title' => 'Jane Smith',
                                'description' => 'Full-stack developer, React & Node.js',
                                'candidate_name' => 'Jane Smith',
                            ],
                        ]
                    ],
                    [
                        'id' => 2, 
                        'name' => 'Screening', 
                        'color' => 'yellow', 
                        'cards' => [
                            [
                                'id' => 3,
                                'title' => 'Mike Johnson',
                                'description' => 'Backend developer, PHP & Laravel expert',
                                'candidate_name' => 'Mike Johnson',
                            ],
                        ]
                    ],
                    ['id' => 3, 'name' => 'Interview', 'color' => 'purple', 'cards' => []],
                    ['id' => 4, 'name' => 'Offer', 'color' => 'green', 'cards' => []],
                    ['id' => 5, 'name' => 'Hired', 'color' => 'emerald', 'cards' => []],
                ]
            ]
        ];
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

        // Find the pipeline
        $pipeline = collect($this->pipelines)->firstWhere('id', $this->selectedPipelineId);
        if (!$pipeline) {
            return;
        }

        // Find source and target stages
        $sourceStage = collect($pipeline['stages'])->firstWhere('id', $this->draggedFromStageId);
        $targetStage = collect($pipeline['stages'])->firstWhere('id', $targetStageId);

        if (!$sourceStage || !$targetStage) {
            return;
        }

        // Find the card
        $card = collect($sourceStage['cards'])->firstWhere('id', $this->draggedCardId);
        if (!$card) {
            return;
        }

        // Store card and stage names for the callout
        $cardTitle = $card['title'] ?? 'Card';
        $fromStageName = $sourceStage['name'] ?? 'Unknown';
        $toStageName = $targetStage['name'] ?? 'Unknown';

        // Remove card from source stage
        $sourceStage['cards'] = collect($sourceStage['cards'])->reject(function ($c) {
            return $c['id'] == $this->draggedCardId;
        })->values()->toArray();

        // Add card to target stage
        $targetStage['cards'][] = $card;

        // Update the pipeline
        $pipeline['stages'] = collect($pipeline['stages'])->map(function ($stage) use ($sourceStage, $targetStage) {
            if ($stage['id'] == $sourceStage['id']) {
                return $sourceStage;
            }
            if ($stage['id'] == $targetStage['id']) {
                return $targetStage;
            }
            return $stage;
        })->toArray();

        // Update pipelines array
        $this->pipelines = collect($this->pipelines)->map(function ($p) use ($pipeline) {
            if ($p['id'] == $pipeline['id']) {
                return $pipeline;
            }
            return $p;
        })->toArray();

        // Show success callout
        $this->moveCalloutCardTitle = $cardTitle;
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
        $pipeline = collect($this->pipelines)->firstWhere('id', $this->selectedPipelineId);
        if (!$pipeline) {
            return;
        }

        $stage = collect($pipeline['stages'])->firstWhere('id', $stageId);
        if (!$stage || !isset($stage['cards'])) {
            return;
        }

        $card = collect($stage['cards'])->firstWhere('id', $cardId);
        if (!$card) {
            return;
        }

        $this->selectedCard = $card;
        $this->selectedCardStageId = $stageId;
        $this->showCardDetailModal = true;
    }

    public function closeCardDetail()
    {
        $this->showCardDetailModal = false;
        $this->selectedCard = null;
        $this->selectedCardStageId = null;
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
        if (!$this->selectedPipeline || !isset($this->selectedPipeline['stages'])) {
            return collect();
        }

        $allCandidates = collect();
        foreach ($this->selectedPipeline['stages'] as $stage) {
            if (isset($stage['cards']) && count($stage['cards']) > 0) {
                foreach ($stage['cards'] as $card) {
                    $card['stage_name'] = $stage['name'] ?? 'Unknown';
                    $card['stage_id'] = $stage['id'] ?? null;
                    $allCandidates->push($card);
                }
            }
        }
        return $allCandidates;
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

        // Find the pipeline and stage
        $pipeline = collect($this->pipelines)->firstWhere('id', $this->selectedPipelineId);
        if (!$pipeline || !$this->selectedColumn) {
            return;
        }

        $stage = collect($pipeline['stages'])->firstWhere('id', $this->selectedColumn);
        if (!$stage) {
            return;
        }

        // Generate a new card ID
        $maxId = 0;
        foreach ($pipeline['stages'] as $s) {
            if (isset($s['cards']) && count($s['cards']) > 0) {
                $maxId = max($maxId, max(array_column($s['cards'], 'id')));
            }
        }
        $newCardId = $maxId + 1;

        // Handle file uploads
        $attachmentPaths = [];
        if (!empty($this->candidateAttachments)) {
            foreach ($this->candidateAttachments as $attachment) {
                $path = $attachment->store('candidate-attachments', 'public');
                $attachmentPaths[] = $path;
            }
        }
        
        // Create new card
        $fullName = trim($this->candidateFirstName . ' ' . $this->candidateLastName);
        $newCard = [
            'id' => $newCardId,
            'title' => $fullName,
            'description' => $this->newCardDescription ?: ($this->candidatePosition ? $this->candidatePosition : ''),
            'candidate_name' => $fullName,
            'candidate_first_name' => $this->candidateFirstName,
            'candidate_last_name' => $this->candidateLastName,
            'candidate_email' => $this->candidateEmail,
            'candidate_phone' => $this->candidatePhone,
            'candidate_dob' => $this->candidateDob,
            'candidate_position' => $this->candidatePosition,
            'candidate_designation' => $this->candidateDesignation,
            'candidate_experience' => $this->candidateExperience,
            'candidate_current_address' => $this->candidateCurrentAddress,
            'candidate_current_company' => $this->candidateCurrentCompany,
            'candidate_city' => $this->candidateCity,
            'candidate_country' => $this->candidateCountry,
            'candidate_source' => $this->candidateSource,
            'candidate_notice_period' => $this->candidateNoticePeriod,
            'candidate_linkedin' => $this->candidateLinkedIn,
            'candidate_expected_salary' => $this->candidateExpectedSalary,
            'candidate_availability_date' => $this->candidateAvailabilityDate,
            'candidate_previous_companies' => $this->previousCompanies,
            'candidate_attachments' => $attachmentPaths,
        ];

        // Add card to stage
        if (!isset($stage['cards'])) {
            $stage['cards'] = [];
        }
        $stage['cards'][] = $newCard;

        // Update the pipeline
        $pipeline['stages'] = collect($pipeline['stages'])->map(function ($s) use ($stage) {
            if ($s['id'] == $stage['id']) {
                return $stage;
            }
            return $s;
        })->toArray();

        // Update pipelines array
        $this->pipelines = collect($this->pipelines)->map(function ($p) use ($pipeline) {
            if ($p['id'] == $pipeline['id']) {
                return $pipeline;
            }
            return $p;
        })->toArray();

        $this->closeAddCardModal();
    }

    public function render()
    {
        return view('livewire.recruitment.jobs.show')
            ->layout('components.layouts.app');
    }
}
