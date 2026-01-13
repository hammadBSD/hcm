<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\Employee;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AutoAssignTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:auto-assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically assign tasks to employees based on auto-assign templates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        // Get all parent tasks with auto_assign enabled
        $parentTasks = Task::where('auto_assign', true)
            ->whereNull('parent_task_id')
            ->get();

        foreach ($parentTasks as $parentTask) {
            $employeeIds = $parentTask->template_employee_ids ?? [];
            
            if (empty($employeeIds)) {
                continue;
            }

            // Skip one-time tasks
            if ($parentTask->frequency === 'one-time') {
                continue;
            }

            if ($parentTask->frequency === 'daily') {
                // For daily tasks, create tasks for today if they don't exist
                $this->createDailyTasks($parentTask, $employeeIds, $today);
            } elseif ($parentTask->frequency === 'weekly') {
                // For weekly tasks, check if next_assign_date is today or in the past
                if ($parentTask->next_assign_date && $parentTask->next_assign_date->lte($today)) {
                    $this->createWeeklyTasks($parentTask, $employeeIds, $today);
                    
                    // Update next_assign_date to exactly 7 days from today
                    $parentTask->update([
                        'next_assign_date' => $today->copy()->addDays(7),
                    ]);
                }
            }
        }

        $this->info('Auto-assign tasks processed successfully.');
        return 0;
    }

    private function createDailyTasks(Task $parentTask, array $employeeIds, Carbon $date)
    {
        // Refresh parent task to ensure we have the latest data including custom_fields
        $parentTask->refresh();
        
        foreach ($employeeIds as $employeeId) {
            // Check if task already exists for this employee and date
            $existingTask = Task::where('parent_task_id', $parentTask->id)
                ->where('assigned_to', $employeeId)
                ->whereDate('created_at', $date)
                ->first();

            if ($existingTask) {
                continue; // Task already exists for today
            }

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

    private function createWeeklyTasks(Task $parentTask, array $employeeIds, Carbon $date)
    {
        // Refresh parent task to ensure we have the latest data including custom_fields
        $parentTask->refresh();
        
        foreach ($employeeIds as $employeeId) {
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
}
