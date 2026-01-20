<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\Employee;
use App\Models\TaskAssignmentPermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MyTasks extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = '';
    public $frequencyFilter = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public $showViewFlyout = false;
    public $showEditFlyout = false;
    public $showCreateFlyout = false;
    public $showActionModal = false;
    public $selectedTaskId = null;
    public $editingTaskId = null;
    public $editForAll = false; // Checkbox to edit parent/master task
    public $attachments = []; // Array for multiple file uploads
    public $existingAttachments = []; // Existing attachments when editing
    public $selectedTask = null;
    public $taskNotes = '';
    public $actionType = ''; // 'complete' or 'reject'
    public $actionTaskId = null;
    public $customFieldValues = [];

    public $form = [
        'name' => '',
        'description' => '',
        'assigned_to' => [],
        'due_date' => '',
        'frequency' => 'one-time',
        'custom_fields' => [],
    ];

    public $employeeOptions = [];
    public $employeeSearchTerm = '';

    protected $paginationTheme = 'tailwind';

    public function getFilteredEmployeeOptionsProperty()
    {
        if (empty($this->employeeSearchTerm)) {
            return $this->employeeOptions;
        }
        
        return collect($this->employeeOptions)->filter(function ($employee) {
            return stripos($employee['label'], $this->employeeSearchTerm) !== false;
        })->values()->toArray();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingFrequencyFilter()
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function viewTask($taskId)
    {
        $this->selectedTaskId = $taskId;
        $this->selectedTask = Task::with(['assignedTo.user', 'assignedBy'])->findOrFail($taskId);
        $this->taskNotes = '';
        $this->actionType = '';
        $this->showViewFlyout = true;
        
        // Initialize custom field values
        $this->customFieldValues = $this->selectedTask->custom_field_values ?? [];
        if ($this->selectedTask->custom_fields) {
            foreach ($this->selectedTask->custom_fields as $field) {
                if (!isset($this->customFieldValues[$field['name']])) {
                    $this->customFieldValues[$field['name']] = '';
                }
            }
        }
    }

    /**
     * Check if shift has ended for a task's date
     */
    public function hasShiftEnded($task): bool
    {
        $user = Auth::user();
        
        // Only apply restriction for employee role (not Super Admin or users with view all permissions)
        if ($user->hasRole('Super Admin') || $user->can('tasks.view.all') || $user->can('tasks.view.company')) {
            return false; // Admins can always edit
        }
        
        // Check if user has permission to complete tasks after shift end
        if ($user->can('tasks.complete.after_shift_end')) {
            return false; // User can complete tasks even after shift end
        }
        
        $employee = $user->employee;
        if (!$employee) {
            return false;
        }
        
        // Get the task's date (use created_at date)
        $taskDate = \Carbon\Carbon::parse($task->created_at)->format('Y-m-d');
        
        // Get shift for that date
        $shift = $employee->getEffectiveShiftForDate($taskDate);
        if (!$shift || !$shift->time_to) {
            // No shift info - assume shift has ended for past dates
            $taskDateCarbon = \Carbon\Carbon::parse($taskDate);
            return $taskDateCarbon->lt(\Carbon\Carbon::today());
        }
        
        // Parse shift end time
        $timeToParts = explode(':', $shift->time_to);
        $shiftEndTime = \Carbon\Carbon::createFromTime(
            (int)($timeToParts[0] ?? 0),
            (int)($timeToParts[1] ?? 0),
            (int)($timeToParts[2] ?? 0)
        );
        
        // Check if shift is overnight
        $timeFromParts = explode(':', $shift->time_from);
        $shiftStartTime = \Carbon\Carbon::createFromTime(
            (int)($timeFromParts[0] ?? 0),
            (int)($timeFromParts[1] ?? 0),
            (int)($timeFromParts[2] ?? 0)
        );
        $isOvernight = $shiftStartTime->gt($shiftEndTime);
        
        // Calculate shift end datetime
        $taskDateCarbon = \Carbon\Carbon::parse($taskDate);
        if ($isOvernight) {
            // Overnight shift ends next day
            $shiftEndDateTime = $taskDateCarbon->copy()->addDay()->setTime(
                $shiftEndTime->hour,
                $shiftEndTime->minute,
                $shiftEndTime->second
            );
        } else {
            // Regular shift ends same day
            $shiftEndDateTime = $taskDateCarbon->copy()->setTime(
                $shiftEndTime->hour,
                $shiftEndTime->minute,
                $shiftEndTime->second
            );
        }
        
        // Check if shift has ended
        $now = \Carbon\Carbon::now();
        $todayStart = \Carbon\Carbon::today();
        
        if ($taskDateCarbon->lt($todayStart)) {
            // Past date - shift has ended
            return true;
        } elseif ($taskDateCarbon->isToday()) {
            // Today - check if current time is past shift end
            return $now->gte($shiftEndDateTime);
        } else {
            // Future date - shift hasn't ended
            return false;
        }
    }

    public function closeViewFlyout()
    {
        $this->showViewFlyout = false;
        $this->selectedTask = null;
        $this->selectedTaskId = null;
        $this->taskNotes = '';
        $this->actionType = '';
    }

    public function openEditFlyout($taskId)
    {
        $task = Task::with(['assignedTo'])->findOrFail($taskId);
        
        // Check permission
        $user = Auth::user();
        if (!$user->hasRole('Super Admin') && !$user->can('tasks.edit')) {
            session()->flash('error', __('You do not have permission to edit tasks.'));
            return;
        }

        $this->editingTaskId = $taskId;
        
        // Load task data into form
        $this->form = [
            'name' => $task->name ?: $task->title,
            'description' => $task->description,
            'assigned_to' => [$task->assigned_to], // Array for compatibility
            'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : '',
            'frequency' => $task->frequency,
            'custom_fields' => $task->custom_fields ?? [],
        ];

        // Convert custom fields from stored format to form format
        if (!empty($this->form['custom_fields'])) {
            $formattedFields = [];
            foreach ($this->form['custom_fields'] as $field) {
                $formattedFields[] = [
                    'name' => $field['label'] ?? $field['name'], // Use label if available
                    'type' => $field['type'] ?? 'text',
                ];
            }
            $this->form['custom_fields'] = $formattedFields;
        }

        // Load existing attachments
        $this->existingAttachments = $task->attachments ?? [];
        $this->attachments = [];

        $this->employeeSearchTerm = '';
        $this->loadEmployeeOptions();
        $this->showEditFlyout = true;
    }

    public function closeEditFlyout()
    {
        $this->showEditFlyout = false;
        $this->editingTaskId = null;
        $this->editForAll = false;
        $this->attachments = [];
        $this->existingAttachments = [];
        $this->resetForm();
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }

    public function removeExistingAttachment($index)
    {
        if (isset($this->existingAttachments[$index])) {
            // Delete file from storage
            $attachment = $this->existingAttachments[$index];
            if (isset($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
                Storage::disk('public')->delete($attachment['path']);
            }
            unset($this->existingAttachments[$index]);
            $this->existingAttachments = array_values($this->existingAttachments);
        }
    }

    public function updateTask()
    {
        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.description' => 'required|string',
            'form.assigned_to' => 'required|array|min:1',
            'form.assigned_to.*' => 'exists:employees,id',
            'form.due_date' => 'nullable|date',
            'form.frequency' => 'required|in:one-time,daily,weekly',
            'attachments.*' => 'nullable|file|max:20480', // 20MB max per file
        ]);

        $user = Auth::user();
        if (!$user->hasRole('Super Admin') && !$user->can('tasks.edit')) {
            session()->flash('error', __('You do not have permission to edit tasks.'));
            return;
        }

        $task = Task::findOrFail($this->editingTaskId);

        // Handle assigned_to - it could be an array or single value
        $assignedToId = is_array($this->form['assigned_to']) ? $this->form['assigned_to'][0] : $this->form['assigned_to'];
        
        // Check if user can assign to the selected employee
        $targetEmployee = Employee::findOrFail($assignedToId);
        if (!TaskAssignmentPermission::canAssignTo($user, $targetEmployee)) {
            session()->flash('error', __('You do not have permission to assign tasks to the selected employee.'));
            return;
        }

        // Generate title from name or description
        $title = $this->form['name'] ?: \Illuminate\Support\Str::limit($this->form['description'], 50);

        // Process custom fields - convert field names (spaces to underscores)
        $customFields = [];
        if (!empty($this->form['custom_fields'])) {
            foreach ($this->form['custom_fields'] as $field) {
                if (!empty($field['name'])) {
                    $fieldName = str_replace(' ', '_', trim($field['name']));
                    $customFields[] = [
                        'name' => $fieldName,
                        'type' => $field['type'] ?? 'text',
                        'label' => $field['name'], // Keep original for display
                    ];
                }
            }
        }

        // Process file uploads
        $allAttachments = $this->existingAttachments ?? [];
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                if ($file) {
                    $path = $file->store('task-attachments', 'public');
                    $allAttachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }
        }

        // Update task
        $task->update([
            'name' => $this->form['name'],
            'title' => $title,
            'description' => $this->form['description'],
            'assigned_to' => $assignedToId,
            'due_date' => $this->form['due_date'] ?: null,
            'frequency' => $this->form['frequency'],
            'custom_fields' => !empty($customFields) ? $customFields : null,
            'attachments' => !empty($allAttachments) ? $allAttachments : null,
        ]);

        // If "Edit for all" is checked, also update the parent/master task
        if ($this->editForAll) {
            if ($task->parent_task_id) {
                // This is a child task, update the parent
                $parentTask = Task::find($task->parent_task_id);
                if ($parentTask) {
                    $parentTask->update([
                        'name' => $this->form['name'],
                        'title' => $title,
                        'description' => $this->form['description'],
                        'due_date' => $this->form['due_date'] ?: null,
                        'frequency' => $this->form['frequency'],
                        'custom_fields' => !empty($customFields) ? $customFields : null,
                        'attachments' => !empty($allAttachments) ? $allAttachments : null,
                    ]);
                }
            } elseif ($task->auto_assign) {
                // This is already a parent/master task, it's already updated above
                // No additional action needed
            }
        }

        $message = $this->editForAll 
            ? __('Task and master template updated successfully. Future tasks will use the updated template.')
            : __('Task updated successfully.');

        session()->flash('success', $message);
        $this->closeEditFlyout();
    }

    public function openActionModal($taskId, $action)
    {
        $this->actionTaskId = $taskId;
        $this->actionType = $action; // 'complete' or 'reject'
        $this->taskNotes = '';
        $this->showActionModal = true;
        
        // Load task and initialize custom field values for complete action
        if ($action === 'complete') {
            $task = Task::findOrFail($taskId);
            $this->customFieldValues = $task->custom_field_values ?? [];
            if ($task->custom_fields) {
                foreach ($task->custom_fields as $field) {
                    if (!isset($this->customFieldValues[$field['name']])) {
                        $this->customFieldValues[$field['name']] = '';
                    }
                }
            }
        } else {
            $this->customFieldValues = [];
        }
    }

    public function closeActionModal()
    {
        $this->showActionModal = false;
        $this->actionTaskId = null;
        $this->actionType = '';
        $this->taskNotes = '';
        $this->customFieldValues = [];
        $this->resetValidation();
    }

    public function updateCustomFieldValues()
    {
        if ($this->selectedTask) {
            $this->selectedTask->update([
                'custom_field_values' => $this->customFieldValues,
            ]);
        }
    }

    public function markAsCompleted($taskId = null)
    {
        $taskId = $taskId ?? $this->actionTaskId;
        
        $this->validate([
            'taskNotes' => 'required|string|min:3',
        ], [
            'taskNotes.required' => __('Notes are required when completing a task.'),
            'taskNotes.min' => __('Notes must be at least 3 characters.'),
        ]);

        $task = Task::findOrFail($taskId);
        
        // Verify task is assigned to current user's employee
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee || $task->assigned_to !== $employee->id) {
            session()->flash('error', __('You can only update your own tasks.'));
            $this->closeActionModal();
            return;
        }

        // Update custom field values if viewing task
        if ($this->selectedTask && $this->selectedTask->id === $taskId) {
            $this->updateCustomFieldValues();
        }

        $task->update([
            'status' => 'completed',
            'completed_at' => \Carbon\Carbon::now(),
            'completion_notes' => $this->taskNotes,
            'rejected_at' => null,
            'rejection_reason' => null,
            'custom_field_values' => !empty($this->customFieldValues) ? $this->customFieldValues : $task->custom_field_values,
        ]);

        session()->flash('success', __('Task marked as completed.'));
        $this->closeActionModal();
        if ($this->showViewFlyout) {
            $this->closeViewFlyout();
        }
    }

    public function markAsRejected($taskId = null)
    {
        $taskId = $taskId ?? $this->actionTaskId;
        
        $this->validate([
            'taskNotes' => 'required|string|min:3',
        ], [
            'taskNotes.required' => __('Notes are required when rejecting a task.'),
            'taskNotes.min' => __('Notes must be at least 3 characters.'),
        ]);

        $task = Task::findOrFail($taskId);
        
        // Verify task is assigned to current user's employee
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee || $task->assigned_to !== $employee->id) {
            session()->flash('error', __('You can only update your own tasks.'));
            $this->closeActionModal();
            return;
        }

        $task->update([
            'status' => 'rejected',
            'rejected_at' => \Carbon\Carbon::now(),
            'rejection_reason' => $this->taskNotes,
            'completed_at' => null,
            'completion_notes' => null,
        ]);

        session()->flash('success', __('Task marked as rejected.'));
        $this->closeActionModal();
        if ($this->showViewFlyout) {
            $this->closeViewFlyout();
        }
    }

    public function loadEmployeeOptions()
    {
        // Show all active employees - permission check happens on save
        $employees = Employee::where('status', 'active')
            ->with('user')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $this->employeeOptions = $employees->map(function ($employee) {
            return [
                'value' => $employee->id,
                'label' => $employee->first_name . ' ' . $employee->last_name . ' (' . $employee->employee_code . ')',
                'name' => $employee->first_name . ' ' . $employee->last_name,
            ];
        })->toArray();
    }

    public function removeEmployee($employeeId)
    {
        $this->form['assigned_to'] = array_values(array_filter(
            $this->form['assigned_to'],
            fn ($id) => (int) $id !== (int) $employeeId
        ));
    }

    public function openCreateFlyout()
    {
        $this->resetForm();
        $this->loadEmployeeOptions();
        $this->employeeSearchTerm = '';
        $this->showCreateFlyout = true;
    }

    public function closeCreateFlyout()
    {
        $this->showCreateFlyout = false;
        $this->attachments = [];
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->form = [
            'name' => '',
            'description' => '',
            'assigned_to' => [],
            'due_date' => '',
            'frequency' => 'one-time',
            'custom_fields' => [],
        ];
        $this->attachments = [];
        $this->existingAttachments = [];
    }

    public function addCustomField()
    {
        $this->form['custom_fields'][] = [
            'name' => '',
            'type' => 'text',
        ];
    }

    public function removeCustomField($index)
    {
        unset($this->form['custom_fields'][$index]);
        $this->form['custom_fields'] = array_values($this->form['custom_fields']);
    }

    public function saveTask()
    {
        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.description' => 'required|string',
            'form.assigned_to' => 'required|array|min:1',
            'form.assigned_to.*' => 'exists:employees,id',
            'form.due_date' => 'nullable|date',
            'form.frequency' => 'required|in:one-time,daily,weekly',
            'attachments.*' => 'nullable|file|max:20480', // 20MB max per file
        ]);

        $user = Auth::user();
        $employeeIds = $this->form['assigned_to'];
        
        // Check if user can assign to all selected employees
        foreach ($employeeIds as $employeeId) {
            $targetEmployee = Employee::findOrFail($employeeId);
            if (!TaskAssignmentPermission::canAssignTo($user, $targetEmployee)) {
                session()->flash('error', __('You do not have permission to assign tasks to one or more selected employees.'));
                return;
            }
        }

        // Generate title from name or description (first 50 chars)
        $title = $this->form['name'] ?: \Illuminate\Support\Str::limit($this->form['description'], 50);

        // Process custom fields - convert field names (spaces to underscores)
        $customFields = [];
        if (!empty($this->form['custom_fields'])) {
            foreach ($this->form['custom_fields'] as $field) {
                if (!empty($field['name'])) {
                    $fieldName = str_replace(' ', '_', trim($field['name']));
                    $customFields[] = [
                        'name' => $fieldName,
                        'type' => $field['type'] ?? 'text',
                        'label' => $field['name'], // Keep original for display
                    ];
                }
            }
        }

        // Process file uploads
        $taskAttachments = [];
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                if ($file) {
                    $path = $file->store('task-attachments', 'public');
                    $taskAttachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }
        }

        // If frequency is daily or weekly, automatically enable auto-assign
        if (in_array($this->form['frequency'], ['daily', 'weekly'])) {
            // Create a parent task template
            $parentTask = Task::create([
                'name' => $this->form['name'],
                'title' => $title,
                'description' => $this->form['description'],
                'assigned_to' => $employeeIds[0], // Placeholder, will be replaced by child tasks
                'assigned_by' => $user->id,
                'due_date' => $this->form['due_date'] ?: null,
                'frequency' => $this->form['frequency'],
                'auto_assign' => true,
                'template_employee_ids' => $employeeIds,
                'next_assign_date' => $this->form['frequency'] === 'daily' ? \Carbon\Carbon::today() : \Carbon\Carbon::today()->addDays(7),
                'status' => 'pending',
                'custom_fields' => !empty($customFields) ? $customFields : null,
                'attachments' => !empty($taskAttachments) ? $taskAttachments : null,
            ]);

            // Create initial tasks for all selected employees
            $this->createTasksForEmployees($parentTask, $employeeIds);

            $frequencyText = $this->form['frequency'] === 'daily' ? __('daily') : __('weekly');
            session()->flash('success', __('Task template created successfully. Tasks will be automatically created ' . $frequencyText . ' for the selected employees.'));
        } else {
            // Create individual tasks for each selected employee (one-time)
            foreach ($employeeIds as $employeeId) {
                Task::create([
                    'name' => $this->form['name'],
                    'title' => $title,
                    'description' => $this->form['description'],
                    'assigned_to' => $employeeId,
                    'assigned_by' => $user->id,
                    'due_date' => $this->form['due_date'] ?: null,
                    'frequency' => 'one-time',
                    'auto_assign' => false,
                    'status' => 'pending',
                    'custom_fields' => !empty($customFields) ? $customFields : null,
                    'attachments' => !empty($taskAttachments) ? $taskAttachments : null,
                ]);
            }

            session()->flash('success', __('Tasks assigned successfully to ' . count($employeeIds) . ' employee(s).'));
        }

        $this->closeCreateFlyout();
    }

    private function createTasksForEmployees($parentTask, $employeeIds)
    {
        $today = \Carbon\Carbon::today();
        
        foreach ($employeeIds as $employeeId) {
            $employee = Employee::findOrFail($employeeId);
            
            // Prepare task data
            $taskData = [
                'name' => $parentTask->name,
                'title' => $parentTask->title,
                'description' => $parentTask->description,
                'assigned_to' => $employeeId,
                'assigned_by' => $parentTask->assigned_by,
                'due_date' => $parentTask->due_date,
                'frequency' => $parentTask->frequency,
                'auto_assign' => false,
                'parent_task_id' => $parentTask->id,
                'status' => 'pending',
            ];

            // Only add custom_fields if they exist and are not empty
            if (!empty($parentTask->custom_fields)) {
                $taskData['custom_fields'] = $parentTask->custom_fields;
            }

            Task::create($taskData);
        }
    }

    public function render()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return view('livewire.tasks.my-tasks', [
                'tasks' => collect([]),
                'isEmployeeRole' => true,
            ]);
        }

        $query = Task::with(['assignedTo.user', 'assignedBy']);

        // Check if user can view all tasks (Super Admin or has tasks.view.all permission)
        if ($user->hasRole('Super Admin') || $user->can('tasks.view.all') || $user->can('tasks.view.company')) {
            // Show all tasks, but still exclude parent tasks
            $query->where(function($q) {
                $q->where('auto_assign', false)
                  ->orWhereNull('auto_assign');
            });
        } else {
            // Show only tasks assigned to current user's employee
            $query->where('assigned_to', $employee->id)
                ->where(function($q) {
                    $q->where('auto_assign', false)
                      ->orWhereNull('auto_assign');
                });
        }

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('assignedTo', function ($subQ) {
                      $subQ->where('first_name', 'like', '%' . $this->search . '%')
                           ->orWhere('last_name', 'like', '%' . $this->search . '%')
                           ->orWhere('employee_code', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Apply frequency filter
        if ($this->frequencyFilter) {
            $query->where('frequency', $this->frequencyFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        $tasks = $query->paginate($this->perPage);

        $isEmployeeRole = !($user->hasRole('Super Admin') || $user->can('tasks.view.all') || $user->can('tasks.view.company'));

        // Check if user can edit tasks
        $canEditTasks = $user->hasRole('Super Admin') || $user->can('tasks.edit');

        return view('livewire.tasks.my-tasks', [
            'tasks' => $tasks,
            'isEmployeeRole' => $isEmployeeRole,
            'canEditTasks' => $canEditTasks,
        ]);
    }
}
