<?php

namespace App\Livewire\Dashboard;

use App\Models\Employee;
use App\Models\TaskLog;
use App\Models\TaskSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DailyTasks extends Component
{
    public $todayLog = null;
    public $hasLogToday = false;
    public $settings = null;
    public $showCreateLogFlyout = false;
    public $createLogForm = [
        'notes' => '',
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $employee = $user->employee;
        if (!$employee) {
            return;
        }

        $this->settings = TaskSetting::getInstance();
        
        if (!$this->settings->enabled) {
            return;
        }

        // Check if log exists for today (no template requirement)
        $today = Carbon::today()->format('Y-m-d');
        $this->todayLog = TaskLog::where('employee_id', $employee->id)
            ->where('log_date', $today)
            ->first();
        
        $this->hasLogToday = $this->todayLog !== null;
    }
    
    public function openCreateLogFlyout()
    {
        $this->showCreateLogFlyout = true;
    }
    
    public function closeCreateLogFlyout()
    {
        $this->showCreateLogFlyout = false;
    }
    
    public function saveLog()
    {
        $user = Auth::user();
        if (!$user) {
            session()->flash('error', __('User not found.'));
            return;
        }

        $employee = $user->employee;
        if (!$employee) {
            session()->flash('error', __('Employee record not found.'));
            return;
        }

        $this->validate([
            'createLogForm.notes' => 'required|string|min:3',
        ], [
            'createLogForm.notes.required' => __('Notes are required.'),
            'createLogForm.notes.min' => __('Notes must be at least 3 characters.'),
        ]);

        $today = Carbon::today()->format('Y-m-d');
        
        // Check if a log already exists for this employee, date, and period (same shift)
        $existingLog = TaskLog::where('employee_id', $employee->id)
            ->where('log_date', $today)
            ->where('period', 'full_day')
            ->first();
        
        $newEntry = [
            'notes' => $this->createLogForm['notes'],
            'created_at' => Carbon::now()->toDateTimeString(),
            'created_by' => $user->id,
            'created_by_name' => $user->name,
        ];
        
        if ($existingLog) {
            // Add entry to existing log
            $data = $existingLog->data ?? [];
            if (!isset($data['entries']) || !is_array($data['entries'])) {
                $data['entries'] = [];
            }
            $data['entries'][] = $newEntry;
            
            $existingLog->update([
                'data' => $data,
            ]);
            
            session()->flash('success', __('Log entry added successfully.'));
        } else {
            // Create new log with first entry
            TaskLog::create([
                'employee_id' => $employee->id,
                'task_template_id' => null, // No template required
                'log_date' => $today,
                'period' => 'full_day',
                'data' => [
                    'entries' => [$newEntry]
                ],
                'created_by' => $user->id,
            ]);
            
            session()->flash('success', __('Daily log created successfully.'));
        }

        $this->createLogForm['notes'] = '';
        $this->closeCreateLogFlyout();
        $this->loadData();
    }

    public function render()
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        
        return view('livewire.dashboard.daily-tasks', [
            'hasLogToday' => $this->hasLogToday,
            'todayLog' => $this->todayLog,
            'settings' => $this->settings,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }
}
