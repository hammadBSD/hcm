<?php

namespace App\Livewire\Leaves\EmployeesLeaves;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest as LeaveRequestModel;
use App\Models\LeaveRequestEvent;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Search and Filter Properties
    public $search = '';
    public $employeeFilter = '';
    public $dateFilter = '';
    public $statusFilter = '';
    public $leaveTypeFilter = '';
    public $selectAll = false;
    public $selectedRequests = [];
    
    // Sorting Properties
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    public array $leaveTypeOptions = [];
    public array $employeeOptions = [];
    public bool $showViewFlyout = false;
    public bool $showApproveFlyout = false;
    public bool $showRejectFlyout = false;
    public bool $showEditFlyout = false;
    public bool $showCreateRequestFlyout = false;
    public ?int $activeRequestId = null;
    public array $activeRequest = [];
    public array $activeEvents = [];
    public array $approveForm = [
        'decision' => 'approve',
        'notes' => '',
    ];
    public array $rejectForm = [
        'decision' => 'reject',
        'notes' => '',
    ];
    public array $editForm = [
        'leave_type_id' => null,
        'start_date' => '',
        'end_date' => '',
        'total_days' => '',
        'duration' => 'full_day',
        'reason' => '',
    ];
    public array $createRequestForm = [
        'employee_id' => null,
        'leave_type_id' => null,
        'start_date' => '',
        'end_date' => '',
        'total_days' => '',
        'duration' => 'full_day',
        'reason' => '',
    ];
    public array $createRequestSummary = [];
    public array $createRequestLeaveBalances = [];
    public $approveAttachment;
    public $rejectAttachment;

    public function mount()
    {
        $user = Auth::user();

        if (!$user || (!$user->can('leaves.manage.all') && !$user->can('leaves.view.all'))) {
            abort(403);
        }

        $this->loadEmployeeOptions();
        $this->loadLeaveTypeOptions();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedEmployeeFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedLeaveTypeFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->employeeFilter = '';
        $this->dateFilter = '';
        $this->statusFilter = '';
        $this->leaveTypeFilter = '';
        $this->selectedRequests = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedRequests = $this->getFilteredRequests()->pluck('id')->toArray();
        } else {
            $this->selectedRequests = [];
        }
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

    public function getFilteredRequests(): Collection
    {
        $query = LeaveRequestModel::query()
            ->with([
                'employee.user',
                'employee.department',
                'employee.designation',
                'leaveType',
            ]);

        if ($this->search) {
            $searchTerm = trim($this->search);
            $query->where(function ($builder) use ($searchTerm) {
                $builder->whereHas('employee.user', function ($sub) use ($searchTerm) {
                    $sub->where('name', 'like', '%' . $searchTerm . '%');
                })->orWhereHas('employee', function ($sub) use ($searchTerm) {
                    $sub->where('employee_code', 'like', '%' . $searchTerm . '%')
                        ->orWhereRaw("concat_ws(' ', first_name, last_name) like ?", ['%' . $searchTerm . '%']);
                });
            });
        }

        if ($this->employeeFilter) {
            $query->where('employee_id', $this->employeeFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->leaveTypeFilter) {
            $query->where('leave_type_id', $this->leaveTypeFilter);
        }

        if ($this->dateFilter) {
            [$start, $end] = $this->resolveDateRange($this->dateFilter);
            $query->whereBetween('created_at', [$start, $end]);
        }

        $requests = $query->get()->map(function (LeaveRequestModel $request) {
            $employee = $request->employee;
            $user = $employee?->user;

            $name = $user?->name
                ?? trim($employee?->first_name . ' ' . $employee?->last_name)
                ?: __('Unknown Employee');

            $initials = collect(explode(' ', $name))
                ->filter()
                ->map(fn ($segment) => Str::upper(mb_substr($segment, 0, 1)))
                ->join('');

            if ($initials === '') {
                $initials = Str::upper(mb_substr($name, 0, 2));
            }

            $departmentName = optional($employee?->department)->title
                ?? $employee?->department
                ?? __('Not assigned');

            $designationName = optional($employee?->designation)->name
                ?? $employee?->designation
                ?? __('No designation');

            $startDate = $request->start_date?->format('Y-m-d');
            $endDate = $request->end_date?->format('Y-m-d');
            $createdAt = $request->created_at;

            return [
                'id' => $request->id,
                'employee_name' => $name,
                'employee_initials' => Str::upper(Str::limit($initials, 2, '')),
                'employee_code' => $employee?->employee_code ?? __('N/A'),
                'department' => $departmentName,
                'position' => $designationName,
                'leave_type' => $request->leaveType?->name ?? __('Unknown'),
                'leave_type_code' => $request->leaveType?->code,
                'total_days' => (float) $request->total_days,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $request->status,
                'created_at' => $createdAt?->toDateTimeString(),
                'created_date' => $createdAt?->format('M d, Y'),
                'created_time' => $createdAt?->format('h:i A'),
            ];
        });

        $requests = $requests->sortBy(function ($request) {
            return match ($this->sortBy) {
                'employee_name' => $request['employee_name'],
                'department' => $request['department'],
                'leave_type' => $request['leave_type'],
                'start_date' => $request['start_date'],
                'status' => $request['status'],
                'created_at' => $request['created_at'],
                default => $request['created_at'],
            };
        }, SORT_NATURAL | SORT_FLAG_CASE, $this->sortDirection === 'desc');

        return $requests->values();
    }

    public function openViewFlyout(int $requestId): void
    {
        $this->loadActiveRequest($requestId);
        $this->showViewFlyout = true;
    }

    public function openApproveFlyout(int $requestId): void
    {
        $this->authorizeApproval();
        $this->loadActiveRequest($requestId);
        $this->showApproveFlyout = true;
    }

    public function openRejectFlyout(int $requestId): void
    {
        $this->authorizeApproval();
        $this->loadActiveRequest($requestId);
        $this->showRejectFlyout = true;
    }

    public function closeFlyouts(): void
    {
        $this->showViewFlyout = false;
        $this->showApproveFlyout = false;
        $this->showRejectFlyout = false;
        $this->showEditFlyout = false;
        $this->showCreateRequestFlyout = false;
        $this->activeRequestId = null;
        $this->activeRequest = [];
        $this->activeEvents = [];
        $this->resetApproveForm();
        $this->resetRejectForm();
        $this->resetEditForm();
        $this->resetCreateRequestForm();
        $this->resetErrorBag();
    }

    public function openCreateRequestFlyout(): void
    {
        if (!Auth::user()->can('leaves.manage.all')) {
            abort(403);
        }

        // Set default dates
        $this->createRequestForm['start_date'] = now()->format('Y-m-d');
        $this->createRequestForm['end_date'] = now()->format('Y-m-d');
        $this->createRequestForm['total_days'] = '1.0';
        
        $this->showCreateRequestFlyout = true;
    }

    public function updatedCreateRequestFormStartDate(): void
    {
        $this->recalculateCreateRequestDays();
    }

    public function updatedCreateRequestFormEndDate(): void
    {
        $this->recalculateCreateRequestDays();
    }

    public function updatedCreateRequestFormDuration(): void
    {
        $this->recalculateCreateRequestDays();
    }

    public function updatedCreateRequestFormEmployeeId(): void
    {
        if ($this->createRequestForm['employee_id']) {
            $this->loadCreateRequestSummary((int) $this->createRequestForm['employee_id']);
        } else {
            $this->createRequestLeaveBalances = [];
            $this->createRequestSummary = [
                'entitled' => 0.0,
                'used' => 0.0,
                'pending' => 0.0,
                'balance' => 0.0,
            ];
        }
    }

    protected function loadCreateRequestSummary(int $employeeId): void
    {
        $employee = Employee::find($employeeId);
        
        if (!$employee) {
            $this->createRequestLeaveBalances = [];
            $this->createRequestSummary = [
                'entitled' => 0.0,
                'used' => 0.0,
                'pending' => 0.0,
                'balance' => 0.0,
            ];
            return;
        }

        $balances = EmployeeLeaveBalance::query()
            ->with('leaveType:id,name,code')
            ->where('employee_id', $employee->id)
            ->get();

        // Store balances per leave type
        $this->createRequestLeaveBalances = $balances->map(function ($balance) {
            return [
                'leave_type_id' => $balance->leave_type_id,
                'leave_type_name' => $balance->leaveType?->name ?? __('Unknown'),
                'leave_type_code' => $balance->leaveType?->code,
                'entitled' => (float) $balance->entitled,
                'used' => (float) $balance->used,
                'pending' => (float) $balance->pending,
                'balance' => (float) $balance->balance,
            ];
        })->toArray();

        // Also keep aggregated summary for backward compatibility
        $this->createRequestSummary = [
            'entitled' => (float) $balances->sum('entitled'),
            'used' => (float) $balances->sum('used'),
            'pending' => (float) $balances->sum('pending'),
            'balance' => (float) $balances->sum('balance'),
        ];
    }

    protected function recalculateCreateRequestDays(): void
    {
        $days = $this->calculateCreateRequestDays();
        $this->createRequestForm['total_days'] = number_format($days, 1);
    }

    protected function calculateCreateRequestDays(): float
    {
        // For half-day leaves, always return 0.5
        if (in_array($this->createRequestForm['duration'] ?? 'full_day', ['half_day_morning', 'half_day_afternoon'], true)) {
            return 0.5;
        }

        $startDate = $this->createRequestForm['start_date'] ?? '';
        $endDate = $this->createRequestForm['end_date'] ?? '';

        // If dates are not set, return 0
        if (! $startDate || ! $endDate) {
            return 0.0;
        }

        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

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

    public function submitCreateRequest(): void
    {
        if (!Auth::user()->can('leaves.manage.all')) {
            abort(403);
        }

        // Validate date range before validation
        if ($this->createRequestForm['start_date'] && $this->createRequestForm['end_date']) {
            try {
                $fromDate = Carbon::parse($this->createRequestForm['start_date']);
                $toDate = Carbon::parse($this->createRequestForm['end_date']);
                
                if ($toDate->lt($fromDate)) {
                    throw ValidationException::withMessages([
                        'createRequestForm.end_date' => __('Leave end date must be on or after start date.'),
                    ]);
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Exception $e) {
                // Invalid date format, let validation handle it
            }
        }

        $this->validate([
            'createRequestForm.employee_id' => ['required', 'exists:employees,id'],
            'createRequestForm.leave_type_id' => ['required', 'exists:leave_types,id'],
            'createRequestForm.start_date' => ['required', 'date'],
            'createRequestForm.end_date' => ['required', 'date', 'after_or_equal:createRequestForm.start_date'],
            'createRequestForm.total_days' => ['nullable'],
            'createRequestForm.duration' => ['required', 'string', 'in:full_day,half_day_morning,half_day_afternoon'],
            'createRequestForm.reason' => ['nullable', 'string', 'min:10'],
        ], [], [
            'createRequestForm.employee_id' => __('employee'),
            'createRequestForm.leave_type_id' => __('leave type'),
            'createRequestForm.start_date' => __('start date'),
            'createRequestForm.end_date' => __('end date'),
            'createRequestForm.duration' => __('duration'),
            'createRequestForm.reason' => __('reason'),
        ]);

        $calculatedDays = $this->calculateCreateRequestDays();

        // Validate calculated days
        if ($calculatedDays <= 0) {
            throw ValidationException::withMessages([
                'createRequestForm.start_date' => __('Please select valid leave dates.'),
            ]);
        }

        if ($calculatedDays > 365) {
            throw ValidationException::withMessages([
                'createRequestForm.end_date' => __('Leave duration cannot exceed 365 days.'),
            ]);
        }

        DB::transaction(function () use ($calculatedDays) {
            $user = Auth::user();
            $employee = Employee::findOrFail($this->createRequestForm['employee_id']);

            $balance = EmployeeLeaveBalance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $this->createRequestForm['leave_type_id'],
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

            $request = LeaveRequestModel::create([
                'employee_id' => $employee->id,
                'requested_by' => $user->id,
                'leave_type_id' => $this->createRequestForm['leave_type_id'],
                'start_date' => $this->createRequestForm['start_date'],
                'end_date' => $this->createRequestForm['end_date'],
                'total_days' => $calculatedDays,
                'duration' => $this->createRequestForm['duration'],
                'reason' => $this->createRequestForm['reason'] ?: __('Leave request created by HR.'),
                'status' => LeaveRequestModel::STATUS_PENDING,
                'auto_approved' => false,
                'balance_snapshot' => (float) $balance->balance,
            ]);

            LeaveRequestEvent::create([
                'leave_request_id' => $request->id,
                'performed_by' => $user->id,
                'event_type' => 'created',
                'notes' => $this->createRequestForm['reason'] ?: __('Leave request created by HR.'),
                'attachment_path' => null,
            ]);

            $balance->pending += $calculatedDays;
            $balance->balance -= $calculatedDays;
            $balance->save();
        });

        session()->flash('success', __('Leave request created successfully.'));

        $this->closeCreateRequestFlyout();
        $this->resetPage();
    }

    public function closeCreateRequestFlyout(): void
    {
        $this->showCreateRequestFlyout = false;
        $this->resetCreateRequestForm();
        $this->resetErrorBag();
    }

    protected function resetCreateRequestForm(): void
    {
        $this->createRequestForm = [
            'employee_id' => null,
            'leave_type_id' => null,
            'start_date' => '',
            'end_date' => '',
            'total_days' => '',
            'duration' => 'full_day',
            'reason' => '',
        ];
        $this->createRequestLeaveBalances = [];
        $this->createRequestSummary = [
            'entitled' => 0.0,
            'used' => 0.0,
            'pending' => 0.0,
            'balance' => 0.0,
        ];
    }

    public function submitApproval(): void
    {
        $this->authorizeApproval();

        if (! $this->activeRequestId) {
            throw ValidationException::withMessages([
                'approveForm.notes' => __('Select a leave request to approve.'),
            ]);
        }

        $this->validate([
            'approveForm.notes' => ['nullable', 'string', 'max:2000'],
            'approveAttachment' => ['nullable', 'file', 'max:5120'],
        ]);

        // Always approve when using this flyout
        $this->approveForm['decision'] = 'approve';

        $attachmentPath = null;

        if ($this->approveAttachment) {
            $attachmentPath = $this->approveAttachment->store('leave-approvals', 'public');
        }

        DB::transaction(function () use ($attachmentPath) {
            /** @var LeaveRequestModel $request */
            $request = LeaveRequestModel::query()
                ->lockForUpdate()
                ->with(['employee'])
                ->findOrFail($this->activeRequestId);

            if ($request->status === LeaveRequestModel::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'approveForm.notes' => __('This request is already approved.'),
                ]);
            }

            $days = (float) $request->total_days;

            $balance = EmployeeLeaveBalance::firstOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'leave_type_id' => $request->leave_type_id,
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

            $balance->pending = max(0, (float) $balance->pending - $days);
            $balance->used += $days;
            $balance->save();

            $request->status = LeaveRequestModel::STATUS_APPROVED;
            $request->save();

            $request->events()->create([
                'performed_by' => Auth::id(),
                'event_type' => 'approved',
                'notes' => $this->approveForm['notes'],
                'attachment_path' => $attachmentPath,
                'meta' => [
                    'decision' => $this->approveForm['decision'],
                ],
            ]);
        });

        session()->flash('success', __('Leave request approved successfully.'));

        $this->approveAttachment = null;
        $this->closeFlyouts();
        $this->resetPage();
    }

    public function submitRejection(): void
    {
        $this->authorizeApproval();

        if (! $this->activeRequestId) {
            throw ValidationException::withMessages([
                'rejectForm.notes' => __('Select a leave request to reject.'),
            ]);
        }

        $this->validate([
            'rejectForm.notes' => ['required', 'string', 'min:5', 'max:2000'],
            'rejectAttachment' => ['nullable', 'file', 'max:5120'],
        ]);

        // Always reject when using this flyout
        $this->rejectForm['decision'] = 'reject';

        $attachmentPath = null;

        if ($this->rejectAttachment) {
            $attachmentPath = $this->rejectAttachment->store('leave-rejections', 'public');
        }

        DB::transaction(function () use ($attachmentPath) {
            /** @var LeaveRequestModel $request */
            $request = LeaveRequestModel::query()
                ->lockForUpdate()
                ->with(['employee'])
                ->findOrFail($this->activeRequestId);

            if ($request->status === LeaveRequestModel::STATUS_REJECTED) {
                throw ValidationException::withMessages([
                    'rejectForm.notes' => __('This request is already rejected.'),
                ]);
            }

            $days = (float) $request->total_days;

            $balance = EmployeeLeaveBalance::firstOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'leave_type_id' => $request->leave_type_id,
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

            $balance->pending = max(0, (float) $balance->pending - $days);
            $balance->balance += $days;
            $balance->save();

            $request->status = LeaveRequestModel::STATUS_REJECTED;
            $request->save();

            $request->events()->create([
                'performed_by' => Auth::id(),
                'event_type' => 'rejected',
                'notes' => $this->rejectForm['notes'],
                'attachment_path' => $attachmentPath,
                'meta' => [
                    'decision' => $this->rejectForm['decision'],
                ],
            ]);
        });

        session()->flash('success', __('Leave request rejected successfully.'));

        $this->rejectAttachment = null;
        $this->closeFlyouts();
        $this->resetPage();
    }

    public function editRequest(int $id): void
    {
        $this->authorizeAllManagement();

        /** @var LeaveRequestModel $request */
        $request = LeaveRequestModel::query()
            ->with(['leaveType', 'employee'])
            ->findOrFail($id);

        // Only allow editing pending requests
        if ($request->status !== LeaveRequestModel::STATUS_PENDING) {
            session()->flash('error', __('Only pending leave requests can be edited.'));
            return;
        }

        $this->activeRequestId = $id;
        $this->editForm = [
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date?->format('Y-m-d') ?? '',
            'end_date' => $request->end_date?->format('Y-m-d') ?? '',
            'total_days' => (string) $request->total_days,
            'duration' => $request->duration ?? 'full_day',
            'reason' => $request->reason ?? '',
        ];

        $this->showEditFlyout = true;
    }

    public function submitEdit(): void
    {
        $this->authorizeAllManagement();

        if (! $this->activeRequestId) {
            throw ValidationException::withMessages([
                'editForm.leave_type_id' => __('Select a leave request to edit.'),
            ]);
        }

        $this->validate([
            'editForm.leave_type_id' => ['required', 'exists:leave_types,id'],
            'editForm.start_date' => ['required', 'date'],
            'editForm.end_date' => ['required', 'date', 'after_or_equal:editForm.start_date'],
            'editForm.total_days' => ['required', 'numeric', 'min:0.1', 'max:365'],
            'editForm.duration' => ['required', 'string', 'in:full_day,half_day_morning,half_day_afternoon'],
            'editForm.reason' => ['required', 'string', 'min:10', 'max:2000'],
        ], [], [
            'editForm.leave_type_id' => __('leave type'),
            'editForm.start_date' => __('start date'),
            'editForm.end_date' => __('end date'),
            'editForm.total_days' => __('total days'),
            'editForm.duration' => __('duration'),
            'editForm.reason' => __('reason'),
        ]);

        DB::transaction(function () {
            /** @var LeaveRequestModel $request */
            $request = LeaveRequestModel::query()
                ->lockForUpdate()
                ->findOrFail($this->activeRequestId);

            // Only allow editing pending requests
            if ($request->status !== LeaveRequestModel::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'editForm.reason' => __('Only pending leave requests can be edited.'),
                ]);
            }

            $oldDays = (float) $request->total_days;
            $oldLeaveTypeId = $request->leave_type_id;
            $newDays = (float) $this->editForm['total_days'];
            $newLeaveTypeId = $this->editForm['leave_type_id'];

            // Update balance before changing the request
            $leaveTypeChanged = $oldLeaveTypeId !== $newLeaveTypeId;
            $daysChanged = $oldDays !== $newDays;

            if ($leaveTypeChanged || $daysChanged) {
                // Adjust old leave type balance if it changed
                if ($leaveTypeChanged) {
                    $oldBalance = EmployeeLeaveBalance::firstOrCreate(
                        [
                            'employee_id' => $request->employee_id,
                            'leave_type_id' => $oldLeaveTypeId,
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

                    // Remove pending days from old leave type
                    $oldBalance->pending = max(0, (float) $oldBalance->pending - $oldDays);
                    $oldBalance->save();
                }

                // Adjust new leave type balance
                $newBalance = EmployeeLeaveBalance::firstOrCreate(
                    [
                        'employee_id' => $request->employee_id,
                        'leave_type_id' => $newLeaveTypeId,
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

                if ($leaveTypeChanged) {
                    // Add new days to new leave type
                    $newBalance->pending = (float) $newBalance->pending + $newDays;
                } else {
                    // Just adjust the difference in days
                    $difference = $newDays - $oldDays;
                    $newBalance->pending = max(0, (float) $newBalance->pending + $difference);
                }

                $newBalance->save();
            }

            // Update the request
            $request->leave_type_id = $newLeaveTypeId;
            $request->start_date = $this->editForm['start_date'];
            $request->end_date = $this->editForm['end_date'];
            $request->total_days = $newDays;
            $request->duration = $this->editForm['duration'];
            $request->reason = $this->editForm['reason'];
            $request->save();

            // Create edit event
            $request->events()->create([
                'performed_by' => Auth::id(),
                'event_type' => 'edited',
                'notes' => __('Leave request details updated.'),
                'attachment_path' => null,
                'meta' => [
                    'old_days' => $oldDays,
                    'new_days' => $newDays,
                    'old_leave_type_id' => $oldLeaveTypeId,
                    'new_leave_type_id' => $newLeaveTypeId,
                ],
            ]);
        });

        session()->flash('success', __('Leave request updated successfully.'));

        $this->closeFlyouts();
        $this->resetPage();
    }

    public function deleteRequest(int $id): void
    {
        $this->authorizeAllManagement();

        /** @var LeaveRequestModel $request */
        $request = LeaveRequestModel::query()->findOrFail($id);

        $request->delete();

        session()->flash('success', __('Leave request has been deleted.'));
        $this->resetPage();
    }

    public function createLeaveRequest(): void
    {
        $this->authorizeAllManagement();

        $this->dispatch('notify', type: 'info', message: __('Manual creation is not yet available from this screen.'));
    }

    protected function authorizeApproval(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.approve.requests')) {
            abort(403);
        }
    }

    protected function authorizeAllManagement(): void
    {
        $user = Auth::user();

        if (!$user || !$user->can('leaves.manage.all')) {
            abort(403);
        }
    }

    public function render()
    {
        $filteredRequests = $this->getFilteredRequests();
        
        return view('livewire.leaves.employees-leaves.index', [
            'leaveRequests' => $filteredRequests,
            'employeeOptions' => $this->employeeOptions,
            'leaveTypeOptions' => $this->leaveTypeOptions,
        ])->layout('components.layouts.app');
    }

    protected function loadActiveRequest(int $requestId): void
    {
        /** @var LeaveRequestModel $request */
        $request = LeaveRequestModel::query()
            ->with([
                'employee.user',
                'employee.department',
                'employee.designation',
                'leaveType',
                'requester',
                'events.performer',
            ])
            ->findOrFail($requestId);

        $employee = $request->employee;
        $user = $employee?->user;

        $name = $user?->name
            ?? trim($employee?->first_name . ' ' . $employee?->last_name)
            ?: __('Unknown Employee');

        $departmentName = optional($employee?->department)->title
            ?? $employee?->department
            ?? __('Not assigned');

        $designationName = optional($employee?->designation)->name
            ?? $employee?->designation
            ?? __('No designation');

        $this->activeRequestId = $request->id;
        $this->activeRequest = [
            'id' => $request->id,
            'employee_name' => $name,
            'employee_code' => $employee?->employee_code ?? __('N/A'),
            'department' => $departmentName,
            'designation' => $designationName,
            'leave_type' => $request->leaveType?->name ?? __('Unknown'),
            'leave_type_code' => $request->leaveType?->code,
            'total_days' => (float) $request->total_days,
            'duration' => [
                'start' => $request->start_date?->format('M d, Y'),
                'end' => $request->end_date?->format('M d, Y'),
            ],
            'status' => $request->status,
            'reason' => $request->reason,
            'requested_by' => $request->requester?->name,
            'requested_at' => $request->created_at?->format('M d, Y h:i A'),
        ];

        $this->activeEvents = $request->events()
            ->latest()
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'type' => $event->event_type,
                    'notes' => $event->notes,
                    'performed_by' => $event->performer?->name ?? __('System'),
                    'created_at' => $event->created_at?->format('M d, Y h:i A'),
                    'attachment_path' => $event->attachment_path,
                ];
            })
            ->toArray();
    }

    protected function loadEmployeeOptions(): void
    {
        $this->employeeOptions = Employee::query()
            ->with('user')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'user_id', 'employee_code', 'first_name', 'last_name'])
            ->map(function (Employee $employee) {
                $name = $employee->user?->name
                    ?? trim($employee->first_name . ' ' . $employee->last_name)
                    ?: __('Employee #:id', ['id' => $employee->id]);

                return [
                    'id' => $employee->id,
                    'label' => $name,
                    'code' => $employee->employee_code,
                ];
            })
            ->toArray();
    }

    protected function loadLeaveTypeOptions(): void
    {
        $this->leaveTypeOptions = LeaveType::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->toArray();
    }

    protected function resolveDateRange(string $filter): array
    {
        $now = Carbon::now();

        return match ($filter) {
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ],
            'this_quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_year' => [
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
            ],
            default => [$now->copy()->startOfCentury(), $now->copy()->endOfCentury()],
        };
    }

    protected function resetApproveForm(): void
    {
        $this->approveForm = [
            'decision' => 'approve',
            'notes' => '',
        ];
        $this->approveAttachment = null;
    }

    protected function resetRejectForm(): void
    {
        $this->rejectForm = [
            'decision' => 'reject',
            'notes' => '',
        ];
        $this->rejectAttachment = null;
    }

    protected function resetEditForm(): void
    {
        $this->editForm = [
            'leave_type_id' => null,
            'start_date' => '',
            'end_date' => '',
            'total_days' => '',
            'duration' => 'full_day',
            'reason' => '',
        ];
    }
}
