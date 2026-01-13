<?php

namespace App\Livewire\Tasks;

use App\Models\Employee;
use App\Models\TaskLog;
use App\Models\TaskSetting;
use App\Models\TaskTemplate;
use App\Models\DeviceAttendance;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DailyLog extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'tailwind';
    public $selectedDate;
    public $selectedPeriod = 'full_day';
    public $template = null;
    public $formData = [];
    public $existingLog = null;
    public $isLocked = false;
    public $canEdit = true;
    public $dailyLogs = [];
    public $selectedDateFilter = ''; // Date filter (replaces month filter)
    public $isAdminView = false;
    public $canCreateAll = false;
    public $canCreateSelf = false;
    public $search = '';
    public $showCreateLogFlyout = false;
    public $showDateField = false;
    public $createLogForm = [
        'employee_id' => null,
        'date' => null,
        'notes' => '',
    ];
    public $deleteLogId = null;
    public $showDeleteModal = false;
    public $createLogError = '';
    public $perPage = 40;
    public $totalLogs = 0;
    public $currentPage = 1;
    public $canEditLog = false;
    public $canDeleteLog = false;
    public $canViewLog = false;
    public $showViewFlyout = false;
    public $viewLogId = null;
    public $viewLogData = null;
    public $showEditFlyout = false;
    public $editLogId = null;
    public $editLogData = null;

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->selectedDateFilter = Carbon::today()->format('Y-m-d'); // Default to today
        $this->search = '';
        
        $user = Auth::user();
        $this->isAdminView = $user && ($user->hasRole('Super Admin') || $user->can('daily-logs.view.all'));
        $this->canCreateAll = $user && ($user->hasRole('Super Admin') || $user->can('daily-logs.create.all'));
        $this->canCreateSelf = $user && ($user->hasRole('Super Admin') || $user->can('daily-logs.create.self') || $user->can('daily-logs.create.all'));
        $this->canEditLog = $user && ($user->hasRole('Super Admin') || $user->can('daily-logs.edit'));
        $this->canDeleteLog = $user && ($user->hasRole('Super Admin') || $user->can('daily-logs.delete'));
        $this->canViewLog = $user && ($user->hasRole('Super Admin') || $user->can('daily-logs.view.all') || $user->can('daily-logs.view.self') || $user->can('daily-logs.create.self') || $user->can('daily-logs.create.all'));
        
        if (!$this->isAdminView) {
            $this->loadTemplate();
            $this->loadExistingLog();
        }
        $this->loadDailyLogs();
    }

    public function updatedSelectedDate()
    {
        if (!$this->isAdminView) {
            $this->loadTemplate();
            $this->loadExistingLog();
        }
    }
    
    public function updatedSelectedDateFilter()
    {
        $this->resetPage(); // Reset pagination when date changes
        $this->loadDailyLogs();
    }
    
    public function updatedSearch()
    {
        $this->resetPage(); // Reset pagination when search changes
        $this->loadDailyLogs();
    }
    
    
    public function openCreateLogFlyout($employeeId = null, $date = null)
    {
        $user = Auth::user();
        $employee = $user ? $user->employee : null;
        
        // Clear any previous errors
        $this->createLogError = '';
        
        // If can only create for self, set employee to logged-in user
        if ($this->canCreateSelf && !$this->canCreateAll && $employee) {
            $this->createLogForm['employee_id'] = $employee->id;
        } else {
            $this->createLogForm['employee_id'] = $employeeId;
        }
        
        // Set date to current date or current shift date
        if ($date) {
            $this->createLogForm['date'] = $date;
        } else {
            // Use today's date by default
            $this->createLogForm['date'] = Carbon::today()->format('Y-m-d');
        }
        
        $this->createLogForm['notes'] = '';
        // Show date field for users who can create for all (they can select any date)
        // Hide for users who can only create for self (they can only create for today)
        $this->showDateField = $this->canCreateAll;
        $this->showCreateLogFlyout = true;
    }
    
    public function closeCreateLogFlyout()
    {
        $this->showCreateLogFlyout = false;
        $this->showDateField = false;
        $this->createLogError = '';
        $this->createLogForm = [
            'employee_id' => null,
            'date' => null,
            'notes' => '',
        ];
    }
    
    public function saveCreateLog()
    {
        $this->validate([
            'createLogForm.employee_id' => 'required|exists:employees,id',
            'createLogForm.date' => 'required|date',
            'createLogForm.notes' => 'required|string|min:3',
        ], [
            'createLogForm.employee_id.required' => __('Please select an employee.'),
            'createLogForm.date.required' => __('Date is required.'),
            'createLogForm.notes.required' => __('Notes are required.'),
            'createLogForm.notes.min' => __('Notes must be at least 3 characters.'),
        ]);
        
        $employee = Employee::findOrFail($this->createLogForm['employee_id']);
        
        // Clear any previous errors
        $this->createLogError = '';
        
        // Check permissions
        $user = Auth::user();
        if (!$user) {
            $this->createLogError = __('User not found.');
            return;
        }
        
        // If can only create for self, verify employee matches
        if ($this->canCreateSelf && !$this->canCreateAll) {
            $currentEmployee = $user->employee;
            if (!$currentEmployee || $currentEmployee->id != $employee->id) {
                $this->createLogError = __('You can only create logs for yourself.');
                return;
            }
        }
        
        // Create log with notes in data and track who created it
        // Note: task_template_id is nullable, so we don't require a template
        TaskLog::create([
            'employee_id' => $employee->id,
            'task_template_id' => null, // No template required
            'log_date' => $this->createLogForm['date'],
            'period' => 'full_day',
            'data' => ['notes' => $this->createLogForm['notes']],
            'created_by' => $user->id, // Track who created this log
        ]);
        
        session()->flash('success', __('Daily log created successfully.'));
        $this->closeCreateLogFlyout();
        $this->loadDailyLogs();
    }
    
    public function confirmDelete($logId)
    {
        $this->deleteLogId = $logId;
        $this->showDeleteModal = true;
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteLogId = null;
    }
    
    public function deleteLog()
    {
        $user = Auth::user();
        if (!$user) {
            session()->flash('error', __('User not found.'));
            return;
        }
        
        // Check permission
        if (!$user->hasRole('Super Admin') && !$user->can('daily-logs.delete')) {
            session()->flash('error', __('You do not have permission to delete daily logs.'));
            $this->closeDeleteModal();
            return;
        }
        
        $log = TaskLog::findOrFail($this->deleteLogId);
        $log->delete();
        
        session()->flash('success', __('Daily log deleted successfully.'));
        $this->closeDeleteModal();
        $this->loadDailyLogs();
    }
    
    public function openViewFlyout($logId)
    {
        $this->viewLogId = $logId;
        $log = TaskLog::with(['employee.department', 'employee.group', 'createdBy'])->find($logId);
        if ($log) {
            $department = $log->employee->department;
            $departmentName = 'N/A';
            if ($department) {
                if (is_object($department) && isset($department->title)) {
                    $departmentName = $department->title;
                } elseif (is_string($department)) {
                    $departmentName = $department;
                }
            }
            
            $group = $log->employee->group;
            $groupName = 'N/A';
            if ($group) {
                if (is_object($group) && isset($group->name)) {
                    $groupName = $group->name;
                } elseif (is_string($group)) {
                    $groupName = $group;
                }
            }
            
            $this->viewLogData = [
                'id' => $log->id,
                'employee_name' => $log->employee->first_name . ' ' . $log->employee->last_name,
                'employee_code' => $log->employee->employee_code,
                'department' => $departmentName,
                'group' => $groupName,
                'date' => $log->log_date->format('Y-m-d'),
                'formatted_date' => $log->log_date->format('M d, Y'),
                'period' => $log->period,
                'period_label' => $this->getPeriodLabel($log->period),
                'is_locked' => $log->is_locked,
                'created_at' => $log->created_at->format('M d, Y h:i A'),
                'created_by' => $log->createdBy ? $log->createdBy->name : null,
                'data' => $log->data ?? [],
                'notes' => isset($log->data['notes']) ? $log->data['notes'] : '',
            ];
        }
        $this->showViewFlyout = true;
    }
    
    public function closeViewFlyout()
    {
        $this->showViewFlyout = false;
        $this->viewLogId = null;
        $this->viewLogData = null;
    }
    
    public function canViewOwnLog($log)
    {
        $user = Auth::user();
        if (!$user || !$user->employee) {
            return false;
        }
        
        // User can view their own log if they can create self
        return $log['employee_id'] == $user->employee->id && 
               ($user->hasRole('Super Admin') || $user->can('daily-logs.create.self') || $user->can('daily-logs.create.all'));
    }
    
    public function openEditFlyout($logId)
    {
        $this->editLogId = $logId;
        $log = TaskLog::with(['employee.department', 'employee.group', 'createdBy'])->find($logId);
        if ($log) {
            $department = $log->employee->department;
            $departmentName = 'N/A';
            if ($department) {
                if (is_object($department) && isset($department->title)) {
                    $departmentName = $department->title;
                } elseif (is_string($department)) {
                    $departmentName = $department;
                }
            }
            
            $group = $log->employee->group;
            $groupName = 'N/A';
            if ($group) {
                if (is_object($group) && isset($group->name)) {
                    $groupName = $group->name;
                } elseif (is_string($group)) {
                    $groupName = $group;
                }
            }
            
            $this->editLogData = [
                'id' => $log->id,
                'employee_id' => $log->employee_id,
                'employee_name' => $log->employee->first_name . ' ' . $log->employee->last_name,
                'employee_code' => $log->employee->employee_code,
                'department' => $departmentName,
                'group' => $groupName,
                'date' => $log->log_date->format('Y-m-d'),
                'formatted_date' => $log->log_date->format('M d, Y'),
                'period' => $log->period,
                'period_label' => $this->getPeriodLabel($log->period),
                'is_locked' => $log->is_locked,
                'notes' => isset($log->data['notes']) ? $log->data['notes'] : '',
                'data' => $log->data ?? [],
            ];
        }
        $this->showEditFlyout = true;
    }
    
    public function closeEditFlyout()
    {
        $this->showEditFlyout = false;
        $this->editLogId = null;
        $this->editLogData = null;
    }
    
    public function updateLog()
    {
        $user = Auth::user();
        if (!$user) {
            session()->flash('error', __('User not found.'));
            return;
        }
        
        // Check permission
        if (!$user->hasRole('Super Admin') && !$user->can('daily-logs.edit')) {
            session()->flash('error', __('You do not have permission to edit daily logs.'));
            $this->closeEditFlyout();
            return;
        }
        
        if (!$this->editLogId || !$this->editLogData) {
            session()->flash('error', __('Log not found.'));
            return;
        }
        
        $log = TaskLog::find($this->editLogId);
        if (!$log) {
            session()->flash('error', __('Log not found.'));
            return;
        }
        
        // Check if log is locked
        if ($log->is_locked) {
            session()->flash('error', __('This log is locked and cannot be edited.'));
            $this->closeEditFlyout();
            return;
        }
        
        // Update the log data
        $data = $log->data ?? [];
        $data['notes'] = $this->editLogData['notes'];
        
        $log->update([
            'data' => $data,
        ]);
        
        session()->flash('success', __('Daily log updated successfully.'));
        $this->closeEditFlyout();
        $this->loadDailyLogs();
    }

    public function updatedSelectedPeriod()
    {
        $this->loadExistingLog();
    }

    public function loadTemplate()
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $employee = $user->employee;
        if (!$employee) {
            return;
        }

        $this->template = TaskTemplate::getTemplateForEmployee($employee);
        
        if ($this->template) {
            // Initialize form data with empty values
            $this->formData = [];
            foreach ($this->template->fields as $field) {
                $defaultValue = '';
                if ($field['type'] === 'checkbox') {
                    $defaultValue = false;
                } elseif ($field['type'] === 'number') {
                    $defaultValue = 0;
                } elseif ($field['type'] === 'select' && isset($field['options'])) {
                    // For select, set first option as default if available
                    if (is_array($field['options']) && count($field['options']) > 0) {
                        $defaultValue = trim($field['options'][0]);
                    } elseif (is_string($field['options'])) {
                        $options = array_map('trim', explode(',', $field['options']));
                        if (count($options) > 0) {
                            $defaultValue = $options[0];
                        }
                    }
                }
                $this->formData[$field['name']] = $defaultValue;
            }
        }
    }

    public function loadExistingLog()
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $employee = $user->employee;
        if (!$employee || !$this->template) {
            return;
        }

        $this->existingLog = TaskLog::where('employee_id', $employee->id)
            ->where('task_template_id', $this->template->id)
            ->where('log_date', $this->selectedDate)
            ->where('period', $this->selectedPeriod)
            ->first();

        if ($this->existingLog) {
            $this->formData = $this->existingLog->data ?? [];
            $this->isLocked = $this->existingLog->is_locked;
            $this->canEdit = $this->existingLog->canEdit();
        } else {
            $this->isLocked = false;
            $this->canEdit = $this->canCreateNewLog();
        }
    }

    public function isEmployeePresent($date): bool
    {
        $user = Auth::user();
        $employee = $user->employee;
        if (!$employee) {
            return false;
        }

        $dateCarbon = Carbon::parse($date);
        
        // Check if employee has checked in on this date
        $checkIn = DeviceAttendance::where('punch_code', $employee->punch_code)
            ->whereDate('punch_time', $dateCarbon)
            ->where('device_type', 'IN')
            ->orderBy('punch_time', 'asc')
            ->first();

        return $checkIn !== null;
    }

    public function isWorkingDay($date): bool
    {
        $dateCarbon = Carbon::parse($date);
        
        // Check if it's a weekend
        if ($dateCarbon->isWeekend()) {
            return false;
        }

        // Check if it's a holiday
        $isHoliday = Holiday::where('status', 'active')
            ->where(function($query) use ($dateCarbon) {
                $query->whereDate('from_date', '<=', $dateCarbon)
                      ->whereDate('to_date', '>=', $dateCarbon);
            })
            ->exists();

        return !$isHoliday;
    }

    public function canCreateNewLog(): bool
    {
        $settings = TaskSetting::getInstance();
        
        if (!$settings->enabled) {
            return false;
        }

        $user = Auth::user();
        $employee = $user->employee;
        if (!$employee) {
            return false;
        }

        // Check if it's a working day
        if (!$this->isWorkingDay($this->selectedDate)) {
            return false;
        }

        // Check if employee is present (has checked in)
        if (!$this->isEmployeePresent($this->selectedDate)) {
            return false;
        }

        // Check lock settings
        if (!$settings->lock_after_shift) {
            return true;
        }

        $shift = $employee->getEffectiveShiftForDate($this->selectedDate);
        if (!$shift) {
            return true;
        }

        // Parse shift end time
        $shiftEndTime = $this->parseShiftTime($shift->time_to);
        if (!$shiftEndTime) {
            return true;
        }

        // Calculate lock time (shift end + grace period)
        $lockTime = Carbon::parse($this->selectedDate . ' ' . $shiftEndTime)
            ->addMinutes($settings->lock_grace_period_minutes ?? 0);

        // If shift is overnight, add a day
        $shiftStartTime = $this->parseShiftTime($shift->time_from);
        if ($shiftStartTime && $shiftStartTime > $shiftEndTime) {
            $lockTime->addDay();
        }

        return Carbon::now()->lt($lockTime);
    }

    public function loadDailyLogs()
    {
        $user = Auth::user();
        if (!$user) {
            $this->dailyLogs = [];
            return;
        }

        // Admin view: Show all employees' logs for the selected month
        if ($this->isAdminView) {
            $this->loadAllEmployeesLogs();
            return;
        }

        // Regular employee view: Show own daily logs
        $employee = $user->employee;
        if (!$employee) {
            $this->dailyLogs = [];
            $this->totalLogs = 0;
            return;
        }

        // Use selected date filter (defaults to today)
        $selectedDate = $this->selectedDateFilter ? Carbon::parse($this->selectedDateFilter) : Carbon::today();
        $dateString = $selectedDate->format('Y-m-d');

        // Load logs for the selected date (no template requirement)
        $logs = TaskLog::where('employee_id', $employee->id)
            ->where('log_date', $dateString)
            ->orderBy('period', 'desc')
            ->get();

        $result = $logs->map(function($log) {
            // Safely get department and group
            $logDepartment = $log->employee->department;
            $logDepartmentName = 'N/A';
            if ($logDepartment) {
                if (is_object($logDepartment) && isset($logDepartment->title)) {
                    $logDepartmentName = $logDepartment->title;
                } elseif (is_string($logDepartment)) {
                    $logDepartmentName = $logDepartment;
                }
            }
            
            $logGroup = $log->employee->group;
            $logGroupName = 'N/A';
            if ($logGroup) {
                if (is_object($logGroup) && isset($logGroup->name)) {
                    $logGroupName = $logGroup->name;
                } elseif (is_string($logGroup)) {
                    $logGroupName = $logGroup;
                }
            }
            
            return [
                'id' => $log->id,
                'employee_id' => $log->employee_id,
                'employee_name' => $log->employee->first_name . ' ' . $log->employee->last_name,
                'employee_code' => $log->employee->employee_code,
                'department' => $logDepartmentName,
                'group' => $logGroupName,
                'date' => $log->log_date->format('Y-m-d'),
                'formatted_date' => $log->log_date->format('M d, Y'),
                'period' => $log->period,
                'period_label' => $this->getPeriodLabel($log->period),
                'is_locked' => $log->is_locked,
                'created_at' => $log->created_at->format('M d, Y h:i A'),
                'has_log' => true,
            ];
        })->toArray();
        
        // Apply search filter
        if (!empty($this->search)) {
            $result = array_filter($result, function($log) {
                $search = strtolower($this->search);
                return (
                    stripos(strtolower($log['employee_name']), $search) !== false ||
                    stripos(strtolower($log['employee_code']), $search) !== false ||
                    stripos(strtolower($log['department']), $search) !== false ||
                    stripos(strtolower($log['group']), $search) !== false
                );
            });
        }
        
        // Convert to collection for pagination
        $collection = collect($result);
        
        // Store total count for pagination
        $this->totalLogs = $collection->count();
        
        // Get current page (default to 1)
        $this->currentPage = $this->getPage() ?? 1;
        
        // Paginate the results
        $this->dailyLogs = $collection->forPage($this->currentPage, $this->perPage)->values()->toArray();
    }
    
    private function loadAllEmployeesLogs()
    {
        // Use selected date filter (defaults to today)
        $selectedDate = $this->selectedDateFilter ? Carbon::parse($this->selectedDateFilter) : Carbon::today();
        $dateString = $selectedDate->format('Y-m-d');
        
        // Get all active employees (no template requirement)
        $employees = Employee::where('status', 'active')
            ->with(['department', 'group'])
            ->get();
        
        // Get all logs for the selected date
        $allLogs = TaskLog::where('log_date', $dateString)
            ->with(['employee.department', 'employee.group', 'taskTemplate'])
            ->get()
            ->groupBy(function($log) {
                return $log->employee_id;
            });
        
        $result = [];
        
        foreach ($employees as $employee) {
            $key = $employee->id;
            
            // Check if log exists for this employee and date
            $employeeLogs = $allLogs->get($key) ?? collect();
            
            if ($employeeLogs->isEmpty()) {
                // No log exists, create empty row
                // Safely get department and group
                $department = $employee->department;
                $departmentName = 'N/A';
                if ($department) {
                    if (is_object($department) && isset($department->title)) {
                        $departmentName = $department->title;
                    } elseif (is_string($department)) {
                        $departmentName = $department;
                    }
                }
                
                $group = $employee->group;
                $groupName = 'N/A';
                if ($group) {
                    if (is_object($group) && isset($group->name)) {
                        $groupName = $group->name;
                    } elseif (is_string($group)) {
                        $groupName = $group;
                    }
                }
                
                $result[] = [
                    'id' => null,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'employee_code' => $employee->employee_code,
                    'department' => $departmentName,
                    'group' => $groupName,
                    'date' => $dateString,
                    'formatted_date' => $selectedDate->format('M d, Y'),
                    'period' => 'full_day',
                    'period_label' => __('Full Day'),
                    'is_locked' => false,
                    'created_at' => null,
                    'has_log' => false,
                ];
            } else {
                // Log exists, add it
                foreach ($employeeLogs as $log) {
                    // Safely get department and group
                    $logDepartment = $log->employee->department;
                    $logDepartmentName = 'N/A';
                    if ($logDepartment) {
                        if (is_object($logDepartment) && isset($logDepartment->title)) {
                            $logDepartmentName = $logDepartment->title;
                        } elseif (is_string($logDepartment)) {
                            $logDepartmentName = $logDepartment;
                        }
                    }
                    
                    $logGroup = $log->employee->group;
                    $logGroupName = 'N/A';
                    if ($logGroup) {
                        if (is_object($logGroup) && isset($logGroup->name)) {
                            $logGroupName = $logGroup->name;
                        } elseif (is_string($logGroup)) {
                            $logGroupName = $logGroup;
                        }
                    }
                    
                    $result[] = [
                        'id' => $log->id,
                        'employee_id' => $log->employee_id,
                        'employee_name' => $log->employee->first_name . ' ' . $log->employee->last_name,
                        'employee_code' => $log->employee->employee_code,
                        'department' => $logDepartmentName,
                        'group' => $logGroupName,
                        'date' => $log->log_date->format('Y-m-d'),
                        'formatted_date' => $log->log_date->format('M d, Y'),
                        'period' => $log->period,
                        'period_label' => $this->getPeriodLabel($log->period),
                        'is_locked' => $log->is_locked,
                        'created_at' => $log->created_at->format('M d, Y h:i A'),
                        'has_log' => true,
                    ];
                }
            }
        }
        
        // Apply search filter
        if (!empty($this->search)) {
            $result = array_filter($result, function($log) {
                $search = strtolower($this->search);
                return (
                    stripos(strtolower($log['employee_name']), $search) !== false ||
                    stripos(strtolower($log['employee_code']), $search) !== false ||
                    stripos(strtolower($log['department']), $search) !== false ||
                    stripos(strtolower($log['group']), $search) !== false
                );
            });
        }
        
        // Sort by employee name
        usort($result, function($a, $b) {
            return strcmp($a['employee_name'], $b['employee_name']);
        });
        
        // Convert to collection for pagination
        $collection = collect($result);
        
        // Store total count for pagination
        $this->totalLogs = $collection->count();
        
        // Get current page (default to 1)
        $this->currentPage = $this->getPage() ?? 1;
        
        // Paginate the results
        $this->dailyLogs = $collection->forPage($this->currentPage, $this->perPage)->values()->toArray();
    }

    private function getPeriodLabel($period): string
    {
        return match($period) {
            'full_day' => __('Full Day'),
            'first_half' => __('First Half'),
            'second_half' => __('Second Half'),
            default => ucfirst(str_replace('_', ' ', $period)),
        };
    }

    public function save()
    {
        $user = Auth::user();
        if (!$user) {
            session()->flash('error', 'User not found.');
            return;
        }

        $employee = $user->employee;
        if (!$employee) {
            session()->flash('error', 'Employee record not found.');
            return;
        }

        if (!$this->template) {
            session()->flash('error', 'No task template assigned to you.');
            return;
        }

        if (!$this->canEdit) {
            session()->flash('error', 'This task log is locked and cannot be edited.');
            return;
        }

        // Check if it's a working day
        if (!$this->isWorkingDay($this->selectedDate)) {
            session()->flash('error', 'Daily logs can only be created on working days.');
            return;
        }

        // Check if employee is present (has checked in)
        if (!$this->isEmployeePresent($this->selectedDate)) {
            session()->flash('error', 'You must be present (checked in) to create a daily log.');
            return;
        }

        // Validate form data based on template fields
        $rules = [];
        foreach ($this->template->fields as $field) {
            $fieldName = 'formData.' . $field['name'];
            if ($field['required'] ?? false) {
                $rules[$fieldName] = 'required';
                if ($field['type'] === 'number') {
                    $rules[$fieldName] .= '|numeric';
                }
            } else {
                if ($field['type'] === 'number') {
                    $rules[$fieldName] = 'nullable|numeric';
                }
            }
        }

        $this->validate($rules, [], array_map(function ($field) {
            return $field['label'] ?? $field['name'];
        }, $this->template->fields));

        $data = [
            'employee_id' => $employee->id,
            'task_template_id' => $this->template->id,
            'log_date' => $this->selectedDate,
            'period' => $this->selectedPeriod,
            'data' => $this->formData,
        ];

        if ($this->existingLog) {
            $this->existingLog->update($data);
            session()->flash('success', 'Task log updated successfully.');
        } else {
            TaskLog::create($data);
            session()->flash('success', 'Task log saved successfully.');
        }

        $this->loadExistingLog();
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
        $settings = TaskSetting::getInstance();
        $user = Auth::user();
        $employee = $user ? $user->employee : null;
        
        // Check if there's a log for today (no template requirement)
        $hasLogToday = false;
        if ($employee) {
            $today = Carbon::today()->format('Y-m-d');
            $hasLogToday = TaskLog::where('employee_id', $employee->id)
                ->where('log_date', $today)
                ->exists();
        }

        // Calculate pagination info
        $totalPages = ceil($this->totalLogs / $this->perPage);
        
        return view('livewire.tasks.daily-log', [
            'settings' => $settings,
            'employee' => $employee,
            'hasLogToday' => $hasLogToday,
            'isAdminView' => $this->isAdminView,
            'canCreateAll' => $this->canCreateAll,
            'canCreateSelf' => $this->canCreateSelf,
            'totalLogs' => $this->totalLogs,
            'totalPages' => $totalPages,
            'currentPage' => $this->currentPage,
            'perPage' => $this->perPage,
        ])->layout('components.layouts.app');
    }
}
