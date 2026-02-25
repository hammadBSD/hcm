<?php

namespace App\Console\Commands;

use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Console\Command;

class BackfillEmployeeDesignationId extends Command
{
    protected $signature = 'employees:backfill-designation-id';

    protected $description = 'Set designation_id on employees from the legacy designation string by matching designations.name. Run after designations:sync-from-employees.';

    public function handle(): int
    {
        $this->info('Backfilling designation_id from employees.designation (match by designations.name)...');

        $employees = Employee::whereNull('designation_id')
            ->whereNotNull('designation')
            ->get();

        $designationsByName = Designation::all()->keyBy(fn ($d) => strtolower(trim($d->name)));

        $updated = 0;
        foreach ($employees as $employee) {
            $legacy = trim((string) $employee->getRawOriginal('designation'));
            if ($legacy === '') {
                continue;
            }
            $des = $designationsByName->get(strtolower($legacy));
            if ($des) {
                $employee->designation_id = $des->id;
                $employee->save();
                $updated++;
            }
        }

        $this->info('Done. Set designation_id for ' . $updated . ' employee(s).');
        return self::SUCCESS;
    }
}
