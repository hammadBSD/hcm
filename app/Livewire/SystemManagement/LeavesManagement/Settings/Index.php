<?php

namespace App\Livewire\SystemManagement\LeavesManagement\Settings;

use App\Models\LeaveSetting;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    public array $form = [
        'auto_assign_enabled' => true,
        'allow_manual_overrides' => true,
        'default_accrual_frequency' => 'annual',
        'default_probation_wait_days' => 0,
        'default_prorate_on_joining' => true,
        'carry_forward_enabled' => false,
        'carry_forward_cap' => null,
        'carry_forward_expiry_days' => null,
        'encashment_enabled' => false,
        'encashment_cap' => null,
        'working_day_rules' => [
            'working_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
        ],
        'notification_preferences' => [
            'notify_manager_on_request' => true,
            'notify_employee_on_status_change' => true,
            'notify_hr_on_low_balance' => false,
        ],
    ];

    public array $frequencies = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'semi-annual' => 'Semi-annual',
        'annual' => 'Annual',
    ];

    public array $weekdays = [
        'mon' => 'Monday',
        'tue' => 'Tuesday',
        'wed' => 'Wednesday',
        'thu' => 'Thursday',
        'fri' => 'Friday',
        'sat' => 'Saturday',
        'sun' => 'Sunday',
    ];

    protected ?LeaveSetting $setting = null;

    public function mount(): void
    {
        $setting = $this->resolveSetting();

        $this->form = array_merge($this->form, $setting->only(array_keys($this->form)));

        $this->form['working_day_rules']['working_days'] = $this->normaliseWorkingDays(
            $this->form['working_day_rules']['working_days'] ?? []
        );

        $this->form['notification_preferences'] = array_merge(
            $this->form['notification_preferences'],
            $setting->notification_preferences ?? []
        );
    }

    public function toggleWorkingDay(string $day): void
    {
        $workingDays = $this->form['working_day_rules']['working_days'] ?? [];

        if (in_array($day, $workingDays)) {
            $this->form['working_day_rules']['working_days'] = array_values(array_diff($workingDays, [$day]));
        } else {
            $workingDays[] = $day;
            $this->form['working_day_rules']['working_days'] = $this->normaliseWorkingDays($workingDays);
        }
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules(), [], $this->attributeLabels());

        $payload = $validated['form'];

        $payload['carry_forward_enabled'] = (bool) $payload['carry_forward_enabled'];
        $payload['encashment_enabled'] = (bool) $payload['encashment_enabled'];
        $payload['auto_assign_enabled'] = (bool) $payload['auto_assign_enabled'];
        $payload['allow_manual_overrides'] = (bool) $payload['allow_manual_overrides'];
        $payload['default_prorate_on_joining'] = (bool) $payload['default_prorate_on_joining'];

        if (! $payload['carry_forward_enabled']) {
            $payload['carry_forward_cap'] = null;
            $payload['carry_forward_expiry_days'] = null;
        }

        if (! $payload['encashment_enabled']) {
            $payload['encashment_cap'] = null;
        }

        $payload['carry_forward_cap'] = $payload['carry_forward_cap'] === '' ? null : $payload['carry_forward_cap'];
        $payload['carry_forward_expiry_days'] = $payload['carry_forward_expiry_days'] === '' ? null : $payload['carry_forward_expiry_days'];
        $payload['encashment_cap'] = $payload['encashment_cap'] === '' ? null : $payload['encashment_cap'];

        $payload['working_day_rules'] = [
            'working_days' => $this->normaliseWorkingDays($payload['working_day_rules']['working_days'] ?? []),
        ];

        $payload['notification_preferences'] = array_merge([
            'notify_manager_on_request' => false,
            'notify_employee_on_status_change' => false,
            'notify_hr_on_low_balance' => false,
        ], $payload['notification_preferences'] ?? []);

        $setting = $this->resolveSetting();

        $setting->fill($payload);
        $setting->save();

        $this->dispatch('notify', type: 'success', message: __('Leave settings saved successfully.'));
    }

    protected function rules(): array
    {
        return [
            'form.auto_assign_enabled' => ['boolean'],
            'form.allow_manual_overrides' => ['boolean'],
            'form.default_accrual_frequency' => ['required', Rule::in(array_keys($this->frequencies))],
            'form.default_probation_wait_days' => ['nullable', 'integer', 'min:0'],
            'form.default_prorate_on_joining' => ['boolean'],
            'form.carry_forward_enabled' => ['boolean'],
            'form.carry_forward_cap' => ['nullable', 'numeric', 'min:0'],
            'form.carry_forward_expiry_days' => ['nullable', 'integer', 'min:0'],
            'form.encashment_enabled' => ['boolean'],
            'form.encashment_cap' => ['nullable', 'numeric', 'min:0'],
            'form.working_day_rules.working_days' => ['array', 'min:1'],
            'form.working_day_rules.working_days.*' => [Rule::in(array_keys($this->weekdays))],
            'form.notification_preferences.notify_manager_on_request' => ['boolean'],
            'form.notification_preferences.notify_employee_on_status_change' => ['boolean'],
            'form.notification_preferences.notify_hr_on_low_balance' => ['boolean'],
        ];
    }

    protected function attributeLabels(): array
    {
        return [
            'form.default_accrual_frequency' => __('Default accrual frequency'),
            'form.default_probation_wait_days' => __('Probation wait (days)'),
            'form.carry_forward_cap' => __('Carry-forward cap'),
            'form.carry_forward_expiry_days' => __('Carry-forward expiry'),
            'form.encashment_cap' => __('Encashment cap'),
            'form.working_day_rules.working_days' => __('Working days'),
        ];
    }

    protected function normaliseWorkingDays(array $days): array
    {
        $unique = array_values(array_unique($days));

        return array_values(array_intersect(array_keys($this->weekdays), $unique));
    }

    public function render()
    {
        return view('livewire.system-management.leaves-management.settings.index', [
            'setting' => $this->resolveSetting(),
        ])->layout('components.layouts.app');
    }

    protected function resolveSetting(): LeaveSetting
    {
        if ($this->setting instanceof LeaveSetting) {
            return $this->setting;
        }

        $this->setting = LeaveSetting::first();

        if (! $this->setting) {
            $this->setting = LeaveSetting::create([
                'auto_assign_enabled' => true,
                'allow_manual_overrides' => true,
                'default_accrual_frequency' => 'annual',
                'default_probation_wait_days' => 0,
                'default_prorate_on_joining' => true,
                'carry_forward_enabled' => false,
                'carry_forward_cap' => null,
                'carry_forward_expiry_days' => null,
                'encashment_enabled' => false,
                'encashment_cap' => null,
                'working_day_rules' => [
                    'working_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                ],
                'notification_preferences' => [
                    'notify_manager_on_request' => true,
                    'notify_employee_on_status_change' => true,
                    'notify_hr_on_low_balance' => false,
                ],
            ]);
        }

        return $this->setting;
    }
}
