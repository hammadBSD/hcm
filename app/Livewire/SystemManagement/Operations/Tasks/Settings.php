<?php

namespace App\Livewire\SystemManagement\Operations\Tasks;

use App\Models\TaskSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Settings extends Component
{
    public array $form = [
        'enabled' => true,
        'lock_after_shift' => false,
        'mandatory' => false,
        'split_periods' => false,
        'lock_grace_period_minutes' => 0,
    ];

    protected ?TaskSetting $setting = null;

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->can('tasks.manage.settings') && !$user->hasRole('Super Admin'))) {
            abort(403, 'You do not have permission to manage task settings.');
        }

        $setting = $this->resolveSetting();
        $this->form = array_merge($this->form, $setting->only(array_keys($this->form)));
    }

    public function save(): void
    {
        $validated = $this->validate([
            'form.enabled' => 'required|boolean',
            'form.lock_after_shift' => 'required|boolean',
            'form.mandatory' => 'required|boolean',
            'form.split_periods' => 'required|boolean',
            'form.lock_grace_period_minutes' => 'required|integer|min:0|max:1440',
        ], [], [
            'form.enabled' => 'enabled',
            'form.lock_after_shift' => 'lock after shift',
            'form.mandatory' => 'mandatory',
            'form.split_periods' => 'split periods',
            'form.lock_grace_period_minutes' => 'grace period',
        ]);

        $payload = $validated['form'];
        $payload['enabled'] = (bool) $payload['enabled'];
        $payload['lock_after_shift'] = (bool) $payload['lock_after_shift'];
        $payload['mandatory'] = (bool) $payload['mandatory'];
        $payload['split_periods'] = (bool) $payload['split_periods'];

        $setting = $this->resolveSetting();
        $setting->update($payload);

        session()->flash('success', 'Task settings saved successfully.');
    }

    protected function resolveSetting(): TaskSetting
    {
        if ($this->setting instanceof TaskSetting) {
            return $this->setting;
        }

        $this->setting = TaskSetting::getInstance();
        return $this->setting;
    }

    public function render()
    {
        return view('livewire.system-management.operations.tasks.settings')
            ->layout('components.layouts.app');
    }
}
