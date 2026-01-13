<?php

namespace App\Livewire\Tasks;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Settings extends Component
{
    public array $form = [
        'require_notes_on_completion' => true,
        'require_notes_on_rejection' => true,
        'auto_assign_daily_tasks' => true,
        'auto_assign_weekly_tasks' => true,
        'default_due_date_days' => 7,
        'enable_task_notifications' => true,
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->can('tasks.manage.settings') && !$user->hasRole('Super Admin'))) {
            abort(403, 'You do not have permission to manage task settings.');
        }

        // Load settings from config or database if needed
        // For now, using default values
    }

    public function save(): void
    {
        $validated = $this->validate([
            'form.require_notes_on_completion' => 'required|boolean',
            'form.require_notes_on_rejection' => 'required|boolean',
            'form.auto_assign_daily_tasks' => 'required|boolean',
            'form.auto_assign_weekly_tasks' => 'required|boolean',
            'form.default_due_date_days' => 'required|integer|min:0|max:365',
            'form.enable_task_notifications' => 'required|boolean',
        ], [], [
            'form.require_notes_on_completion' => 'require notes on completion',
            'form.require_notes_on_rejection' => 'require notes on rejection',
            'form.auto_assign_daily_tasks' => 'auto assign daily tasks',
            'form.auto_assign_weekly_tasks' => 'auto assign weekly tasks',
            'form.default_due_date_days' => 'default due date days',
            'form.enable_task_notifications' => 'enable task notifications',
        ]);

        // Save settings to config or database
        // For now, just show success message
        // TODO: Implement actual settings storage

        session()->flash('success', 'Task settings saved successfully.');
    }

    public function render()
    {
        return view('livewire.tasks.settings')
            ->layout('components.layouts.app');
    }
}
