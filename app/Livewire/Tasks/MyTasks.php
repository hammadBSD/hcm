<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\Employee;
use App\Models\TaskAssignmentPermission;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MyTasks extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $frequencyFilter = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public $showViewFlyout = false;
    public $showCreateFlyout = false;
    public $showActionModal = false;
    public $selectedTaskId = null;
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

    public function closeViewFlyout()
    {
        $this->showViewFlyout = false;
        $this->selectedTask = null;
        $this->selectedTaskId = null;
        $this->taskNotes = '';
        $this->actionType = '';
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
            
            if ($parentTask->frequency === 'daily') {
                // For daily tasks, create task for today
                Task::create([
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
                    'custom_fields' => $parentTask->custom_fields,
                ]);
            } else {
                // For weekly tasks, create task for today
                Task::create([
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
                    'custom_fields' => $parentTask->custom_fields,
                ]);
            }
        }
    }

    public function render()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return view('livewire.tasks.my-tasks', [
                'tasks' => collect([]),
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

        return view('livewire.tasks.my-tasks', [
            'tasks' => $tasks,
        ]);
    }
}
