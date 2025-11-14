<?php

namespace App\Livewire\Leaves;

use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest as LeaveRequestModel;
use App\Models\LeaveRequestEvent;
use App\Models\LeaveSetting;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class LeaveRequest extends Component
{
    use WithFileUploads;

    // Form Properties
    public $leaveType = '';
    public $leaveDuration = 'full_day';
    public $leaveDays = '';
    public $leaveFrom = '';
    public $leaveTo = '';
    public $reason = '';
    public $attachment;
    public array $summary = [
        'entitled' => 0.0,
        'used' => 0.0,
        'pending' => 0.0,
        'balance' => 0.0,
    ];
    public $leaveTypeOptions = [];
    public bool $canOverrideBalance = false;
    public bool $balanceDepleted = false;
    public bool $submitDisabled = false;
    public ?string $balanceWarning = null;

    protected $rules = [
        'leaveType' => ['required', 'integer', 'exists:leave_types,id'],
        'leaveDuration' => ['required', 'string'],
        // leaveDays is auto-calculated, so no validation needed
        // 'leaveDays' => ['nullable', 'numeric', 'min:0.1', 'max:365'],
        'leaveFrom' => ['required', 'date'],
        'leaveTo' => ['required', 'date', 'after_or_equal:leaveFrom'],
        'reason' => 'nullable|string|min:10',
        'attachment' => 'nullable|file|max:5120',
    ];

    protected $messages = [
        'leaveType.required' => 'Please select a leave type.',
        'leaveDuration.required' => 'Please select leave duration.',
        'leaveDays.numeric' => 'Leave days must be a valid number.',
        'leaveDays.min' => 'Leave days must be at least 0.1.',
        'leaveDays.max' => 'Leave days cannot exceed 365.',
        'leaveFrom.required' => 'Please select leave start date.',
        'leaveFrom.date' => 'Please enter a valid start date.',
        'leaveTo.required' => 'Please select leave end date.',
        'leaveTo.date' => 'Please enter a valid end date.',
        'leaveTo.after_or_equal' => 'Leave end date must be on or after start date.',
        'reason.min' => 'If provided, reason must be at least 10 characters long.',
    ];

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.request.submit')) {
            abort(403);
        }

        $this->canOverrideBalance = $user->can('leaves.override.balance');

        // Set default dates to today
        $this->leaveFrom = now()->format('Y-m-d');
        $this->leaveTo = now()->format('Y-m-d');

        // Calculate initial leave days
        $calculatedDays = $this->calculateTotalDays();
        $this->leaveDays = number_format($calculatedDays, 1);

        $this->loadSummary($user);
        $this->loadLeaveTypeOptions();
        $this->updateSubmitState();
    }


    public function submit()
    {
        $this->authorizeRequestSubmission();

        // Trim reason if provided
        if ($this->reason) {
            $this->reason = trim($this->reason);
        }

        // Explicitly check date range before validation
        if ($this->leaveFrom && $this->leaveTo) {
            try {
                $fromDate = Carbon::parse($this->leaveFrom);
                $toDate = Carbon::parse($this->leaveTo);
                
                if ($toDate->lt($fromDate)) {
                    throw ValidationException::withMessages([
                        'leaveTo' => __('Leave end date must be on or after start date.'),
                    ]);
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Exception $e) {
                // Invalid date format, let validation handle it
            }
        }

        $validated = $this->validate();

        $user = Auth::user();
        $employee = optional($user)->loadMissing('employee')->employee;

        if (! $employee) {
            throw ValidationException::withMessages([
                'leaveType' => __('No employee record found for this user.'),
            ]);
        }

        $leaveTypeId = (int) $validated['leaveType'];
        $leaveSetting = LeaveSetting::first();
        $autoApprove = (bool) optional($leaveSetting)->auto_approve_requests;

        $balance = EmployeeLeaveBalance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveTypeId,
            ],
            [
                'entitled' => 0,
                'carried_forward' => 0,
                'manual_adjustment' => 0,
                'used' => 0,
                'pending' => 0,
                'balance' => 0,
            ]
        );

        $availableBalance = (float) $balance->balance;
        $requestedDays = $this->calculateTotalDays();

        // Validate calculated days
        if ($requestedDays <= 0) {
            // Check if the issue is invalid date range
            if ($this->leaveFrom && $this->leaveTo) {
                try {
                    $fromDate = Carbon::parse($this->leaveFrom);
                    $toDate = Carbon::parse($this->leaveTo);
                    
                    if ($toDate->lt($fromDate)) {
                        throw ValidationException::withMessages([
                            'leaveTo' => __('Leave end date must be on or after start date.'),
                        ]);
                    }
                } catch (\Exception $e) {
                    // Date parsing failed, but validation should have caught this
                }
            }
            
            throw ValidationException::withMessages([
                'leaveFrom' => __('Please select valid leave dates.'),
            ]);
        }

        if ($requestedDays > 365) {
            throw ValidationException::withMessages([
                'leaveTo' => __('Leave duration cannot exceed 365 days.'),
            ]);
        }

        if (! $this->canOverrideBalance && $availableBalance <= 0) {
            throw ValidationException::withMessages([
                'leaveType' => __('You have no leave balance remaining for this request.'),
            ]);
        }

        if (! $this->canOverrideBalance && $requestedDays > $availableBalance) {
            throw ValidationException::withMessages([
                'leaveDays' => __('Requested leave exceeds your available balance (:balance days).', [
                    'balance' => number_format($availableBalance, 1),
                ]),
            ]);
        }

        $status = $autoApprove
            ? LeaveRequestModel::STATUS_APPROVED
            : LeaveRequestModel::STATUS_PENDING;

        $attachmentPath = null;
        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('leave-attachments', 'public');
        }

        DB::transaction(function () use (
            $employee,
            $user,
            $leaveTypeId,
            $requestedDays,
            $status,
            $autoApprove,
            $balance,
            $availableBalance,
            $attachmentPath
        ) {
            $request = LeaveRequestModel::create([
                'employee_id' => $employee->id,
                'requested_by' => $user->id,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $this->leaveFrom,
                'end_date' => $this->leaveTo,
                'total_days' => $requestedDays,
                'duration' => $this->leaveDuration,
                'reason' => $this->reason,
                'status' => $status,
                'auto_approved' => $autoApprove,
                'balance_snapshot' => $availableBalance,
            ]);

            LeaveRequestEvent::create([
                'leave_request_id' => $request->id,
                'performed_by' => $user->id,
                'event_type' => 'created',
                'notes' => $this->reason ?: __('No reason provided.'),
                'attachment_path' => $attachmentPath,
            ]);

            if ($status === LeaveRequestModel::STATUS_APPROVED) {
                LeaveRequestEvent::create([
                    'leave_request_id' => $request->id,
                    'performed_by' => $user->id,
                    'event_type' => 'approved',
                    'notes' => __('Automatically approved based on leave settings.'),
                ]);

                $balance->used += $requestedDays;
            } else {
                $balance->pending += $requestedDays;
            }

            $balance->balance -= $requestedDays;
            $balance->save();
        });

        // Redirect to My Leaves page with success message
        session()->flash('success', __('Leave request submitted successfully.'));
        
        return $this->redirect(route('leaves.index'), navigate: true);
    }

    private function resetForm()
    {
        $this->leaveType = '';
        $this->leaveDuration = 'full_day';
        $this->leaveFrom = now()->format('Y-m-d');
        $this->leaveTo = now()->format('Y-m-d');
        $this->reason = '';
        $this->leaveDays = '';
        $this->attachment = null;
    }

    protected function authorizeRequestSubmission(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.request.submit')) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.leaves.leave-request')
            ->layout('components.layouts.app');
    }

    protected function loadSummary($user): void
    {
        if (! $user->relationLoaded('employee')) {
            $user->loadMissing('employee');
        }

        $employee = $user->employee;

        if (! $employee) {
            $this->summary = [
                'entitled' => 0.0,
                'used' => 0.0,
                'pending' => 0.0,
                'balance' => 0.0,
            ];
            return;
        }

        $balances = EmployeeLeaveBalance::query()
            ->where('employee_id', $employee->id)
            ->get();

        $this->summary = [
            'entitled' => (float) $balances->sum('entitled'),
            'used' => (float) $balances->sum('used'),
            'pending' => (float) $balances->sum('pending'),
            'balance' => (float) $balances->sum('balance'),
        ];

        $this->balanceDepleted = ($this->summary['balance'] ?? 0) <= 0;
    }

    protected function loadLeaveTypeOptions(): void
    {
        $this->leaveTypeOptions = LeaveType::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->toArray();
    }

    public function updatedLeaveType(): void
    {
        $this->updateSubmitState();
    }

    public function updatedLeaveFrom(): void
    {
        // Recalculate leave days when from date changes
        $this->recalculateLeaveDays();
    }

    public function updatedLeaveTo(): void
    {
        // Recalculate leave days when to date changes
        $this->recalculateLeaveDays();
    }

    public function updatedLeaveDuration(): void
    {
        // Recalculate leave days when duration changes
        $this->recalculateLeaveDays();
    }

    protected function recalculateLeaveDays(): void
    {
        // Always recalculate - validation will handle errors on submit
        $calculatedDays = $this->calculateTotalDays();
        $this->leaveDays = number_format($calculatedDays, 1);
        $this->updateSubmitState();
    }

    protected function calculateTotalDays(): float
    {
        // For half-day leaves, always return 0.5
        if (in_array($this->leaveDuration, ['half_day_morning', 'half_day_afternoon'], true)) {
            return 0.5;
        }

        // If dates are not set, return 0
        if (! $this->leaveFrom || ! $this->leaveTo) {
            return 0.0;
        }

        try {
            $start = Carbon::parse($this->leaveFrom);
            $end = Carbon::parse($this->leaveTo);

            // Ensure end date is not before start date
            if ($end->lt($start)) {
                return 0.0;
            }

            // Calculate difference in days (inclusive of both start and end dates)
            $days = $start->diffInDays($end) + 1;

            return max(1.0, (float) $days);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    protected function updateSubmitState(): void
    {
        $this->balanceWarning = null;
        $shouldBlock = $this->balanceDepleted && ! $this->canOverrideBalance;

        if ($this->balanceDepleted) {
            $this->balanceWarning = $this->canOverrideBalance
                ? __('Leave balance is zero. You are overriding this restriction based on your permissions.')
                : __('You do not have any remaining leave balance. Contact HR or your manager to proceed.');
        }

        $this->submitDisabled = $shouldBlock;
    }
}
