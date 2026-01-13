<?php

namespace App\Livewire\Dashboard;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Tasks extends Component
{
    public $showActionModal = false;
    public $taskNotes = '';
    public $actionType = ''; // 'complete' or 'reject'
    public $actionTaskId = null;
    public $customFieldValues = [];
    public $actionTask = null;

    protected $listeners = ['task-updated' => '$refresh'];

    public function mount()
    {
        // Component is ready
    }

    public function openActionModal($taskId, $action)
    {
        $this->actionTaskId = $taskId;
        $this->actionType = $action; // 'complete' or 'reject'
        $this->taskNotes = '';
        $this->showActionModal = true;
        
        // Load task and initialize custom field values for complete action
        if ($action === 'complete') {
            $this->actionTask = Task::findOrFail($taskId);
            $this->customFieldValues = $this->actionTask->custom_field_values ?? [];
            if ($this->actionTask->custom_fields) {
                foreach ($this->actionTask->custom_fields as $field) {
                    if (!isset($this->customFieldValues[$field['name']])) {
                        $this->customFieldValues[$field['name']] = '';
                    }
                }
            }
        } else {
            $this->actionTask = null;
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
        $this->actionTask = null;
        $this->resetValidation();
    }

    public function markAsCompleted()
    {
        $this->validate([
            'taskNotes' => 'required|string|min:3',
        ], [
            'taskNotes.required' => __('Notes are required when completing a task.'),
            'taskNotes.min' => __('Notes must be at least 3 characters.'),
        ]);

        $task = Task::findOrFail($this->actionTaskId);
        
        // Verify task is assigned to current user's employee
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee || $task->assigned_to !== $employee->id) {
            session()->flash('error', __('You can only update your own tasks.'));
            $this->closeActionModal();
            return;
        }

        $task->update([
            'status' => 'completed',
            'completed_at' => \Carbon\Carbon::now(),
            'completion_notes' => $this->taskNotes,
            'rejected_at' => null,
            'rejection_reason' => null,
            'custom_field_values' => !empty($this->customFieldValues) ? $this->customFieldValues : null,
        ]);

        session()->flash('success', __('Task marked as completed.'));
        $this->closeActionModal();
        $this->dispatch('task-updated');
    }

    public function markAsRejected()
    {
        $this->validate([
            'taskNotes' => 'required|string|min:3',
        ], [
            'taskNotes.required' => __('Notes are required when rejecting a task.'),
            'taskNotes.min' => __('Notes must be at least 3 characters.'),
        ]);

        $task = Task::findOrFail($this->actionTaskId);
        
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
        $this->dispatch('task-updated');
    }

    public function render()
    {
        $user = Auth::user();
        $employee = $user->employee;

        $tasks = collect();
        
        if ($employee) {
            // Get pending tasks assigned to the current user's employee
            // Exclude parent tasks (auto-assign tasks) - only show actual assigned tasks
            // Parent tasks have auto_assign = true and parent_task_id = null
            // Child tasks have parent_task_id IS NOT NULL
            $tasks = Task::where('assigned_to', $employee->id)
                ->where('status', 'pending')
                ->where(function($query) {
                    // Show tasks that are either:
                    // 1. One-time tasks (auto_assign = false and parent_task_id = null)
                    // 2. Child tasks of auto-assign parent tasks (parent_task_id IS NOT NULL)
                    $query->where(function($q) {
                        $q->where('auto_assign', false)
                          ->whereNull('parent_task_id');
                    })
                    ->orWhereNotNull('parent_task_id');
                })
                ->with(['assignedTo', 'assignedBy'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('livewire.dashboard.tasks', [
            'tasks' => $tasks,
        ]);
    }
}
