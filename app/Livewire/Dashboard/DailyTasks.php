<?php

namespace App\Livewire\Dashboard;

use App\Models\Employee;
use App\Models\TaskLog;
use App\Models\TaskSetting;
use App\Models\TaskTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DailyTasks extends Component
{
    public $hasTemplate = false;
    public $template = null;
    public $todayLog = null;
    public $isLocked = false;
    public $canEdit = true;
    public $settings = null;

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

        $this->template = TaskTemplate::getTemplateForEmployee($employee);
        $this->hasTemplate = $this->template !== null;

        if ($this->template) {
            $today = Carbon::today()->format('Y-m-d');
            $this->todayLog = TaskLog::where('employee_id', $employee->id)
                ->where('task_template_id', $this->template->id)
                ->where('log_date', $today)
                ->where('period', 'full_day')
                ->first();

            if ($this->todayLog) {
                $this->isLocked = $this->todayLog->is_locked;
                $this->canEdit = $this->todayLog->canEdit();
            } else {
                $this->canEdit = $this->canCreateNewLog($employee);
            }
        }
    }

    public function canCreateNewLog($employee): bool
    {
        if (!$this->settings->lock_after_shift) {
            return true;
        }

        $today = Carbon::today()->format('Y-m-d');
        $shift = $employee->getEffectiveShiftForDate($today);
        if (!$shift) {
            return true;
        }

        $shiftEndTime = $this->parseShiftTime($shift->end_time);
        if (!$shiftEndTime) {
            return true;
        }

        $lockTime = Carbon::parse($today . ' ' . $shiftEndTime)
            ->addMinutes($this->settings->lock_grace_period_minutes);

        $shiftStartTime = $this->parseShiftTime($shift->start_time);
        if ($shiftStartTime && $shiftStartTime > $shiftEndTime) {
            $lockTime->addDay();
        }

        return Carbon::now()->lt($lockTime);
    }

    private function parseShiftTime(?string $time): ?string
    {
        if (!$time) {
            return null;
        }

        $parts = explode(':', $time);
        if (count($parts) >= 2) {
            return sprintf('%02d:%02d:00', (int)$parts[0], (int)$parts[1]);
        }

        return null;
    }

    public function render()
    {
        return view('livewire.dashboard.daily-tasks');
    }
}
