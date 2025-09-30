<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeeImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Import extends Component
{
    use WithFileUploads;

    public $file;
    public $showModal = false;
    public $importing = false;
    public $importResults = [];
    public $successCount = 0;
    public $errorCount = 0;
    public $errors = [];
    public $totalRows = 0;
    public $processedRows = 0;
    public $currentBatch = 0;
    public $totalBatches = 0;

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
    ];

    protected $messages = [
        'file.required' => 'Please select a file to import.',
        'file.file' => 'The uploaded file is not valid.',
        'file.mimes' => 'The file must be an Excel file (.xlsx, .xls) or CSV file.',
        'file.max' => 'The file size must not exceed 10MB.',
    ];

    public function render()
    {
        return view('livewire.employees.import')
            ->layout('components.layouts.app');
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->reset(['file', 'importResults', 'successCount', 'errorCount', 'errors']);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['file', 'importResults', 'successCount', 'errorCount', 'errors']);
    }

    public function import()
    {
        try {
            if (!$this->file) {
                $this->addError('file', 'Please select a file to import.');
                session()->flash('error', 'Please select an Excel file before importing.');
                return;
            }

            $this->validate();
        } catch (\Exception $e) {
            $this->addError('file', 'Validation failed: ' . $e->getMessage());
            session()->flash('error', 'Please select a valid Excel file.');
            return;
        }

        try {
            // Increase execution time limit for large imports
            set_time_limit(300); // 5 minutes

            $this->importing = true;
            $this->successCount = 0;
            $this->errorCount = 0;
            $this->errors = [];
            $this->processedRows = 0;
            $this->currentBatch = 0;

        try {
            // Process the Excel file
            $import = new EmployeeImport();
            $data = Excel::toArray($import, $this->file);
            
            if (empty($data) || empty($data[0])) {
                throw new \Exception('The Excel file appears to be empty or invalid.');
            }

            $rows = $data[0];
            
            // The Excel file doesn't have proper headers - first row is data
            // We need to create headers based on the expected column structure
            $headers = [
                'employeecode', 'punchcode', 'firstname', 'lastname', 'fathername', 'mobileno', 'email', 
                'employeereportsto', 'allowmanualattendance', 'roletemplate', 'allowemployeelogin', 'password', 
                'maritalstatus', 'gender', 'dateofbirth', 'placeofbirth', 'emiratesidcnicno', 
                'emiratescnicexpirydate', 'emiratescnicissuancedate', 'religion', 'address', 'state', 
                'accountno', 'accounttitle', 'bank', 'branchcode', 'country', 'province', 'city', 'area', 
                'vendor', 'zipcode', 'emergencycontactperson', 'relationship', 'relativemobileno', 
                'familycode', 'eobiregistrationno', 'eobientrydate', 'socialsecurityno', 'passportno', 
                'visano', 'visaexpiry', 'passportexpiry', 'station', 'department', 'subdepartment', 
                'designation', 'division', 'grade', 'employeestatus', 'employeegroup', 'costcenter', 
                'region', 'glclass', 'joiningdate', 'confirmationdate', 'expectedconfirmationdays', 
                'contractstartdate', 'contractenddate', 'resigndate', 'leavingdate', 'leavingreason', 
                'status', 'positiontype', 'position'
            ];

            // Debug: Log the headers we're using
            Log::info('Using headers: ' . json_encode($headers));
            Log::info('Total rows to process: ' . count($rows));

            // Validate headers
            $this->validateHeaders($headers);

            // Process each row in batches to avoid timeout
            $batchSize = 5; // Process 5 employees at a time for better stability
            $totalRows = count($rows);
            $this->totalRows = $totalRows;
            $this->totalBatches = ceil($totalRows / $batchSize);
            
            for ($i = 0; $i < $totalRows; $i += $batchSize) {
                $this->currentBatch++;
                $batch = array_slice($rows, $i, $batchSize);
                
                foreach ($batch as $index => $row) {
                    $rowNumber = $i + $index + 1; // +1 because arrays are 0-indexed
                    $this->processedRows = $rowNumber;
                    
                    try {
                        // Skip rows with missing required data
                        if ($this->hasMissingRequiredFields($row, $headers)) {
                            $this->errorCount++;
                            $this->errors[] = "Row {$rowNumber}: Skipped - Missing required fields (firstname or mobileno)";
                            Log::warning("Skipping row {$rowNumber}: Missing required fields");
                            continue;
                        }

                        $this->processEmployeeRow($row, $headers, $rowNumber);
                        $this->successCount++;
                    } catch (\Exception $e) {
                        $this->errorCount++;
                        $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
                        Log::error("Employee import error on row {$rowNumber}: " . $e->getMessage());
                    }
                }
                
                // Dispatch real-time progress update
                $this->dispatch('import-progress', [
                    'processed' => $this->processedRows,
                    'total' => $this->totalRows,
                    'success' => $this->successCount,
                    'errors' => $this->errorCount,
                    'currentBatch' => $this->currentBatch,
                    'totalBatches' => $this->totalBatches,
                    'percentage' => round(($this->processedRows / $this->totalRows) * 100, 1)
                ]);
                
                // Small delay to allow UI updates
                usleep(100000); // 0.1 second
            }

            $this->importResults = [
                'success' => $this->successCount,
                'errors' => $this->errorCount,
                'total' => count($rows),
                'error_details' => $this->errors
            ];

            // Handle different success/error scenarios with redirects
            if ($this->errorCount > 0 && $this->successCount > 0) {
                return redirect()->route('employees.import')->with('warning', 
                    "✅ Import completed! Successfully imported {$this->successCount} employees. " .
                    "⚠️ {$this->errorCount} rows were skipped due to missing required data."
                );
            } elseif ($this->errorCount > 0) {
                return redirect()->route('employees.import')->with('error', 
                    "❌ Import failed. {$this->errorCount} rows had errors. " .
                    "Please check your Excel file for missing required fields."
                );
            }

            // Log successful completion
            Log::info("Import completed successfully: {$this->successCount} employees imported, {$this->errorCount} errors");
            
            // Reset the file to avoid any post-processing issues
            $this->file = null;
            $this->importing = false;
            
            // Clear any large data that might cause serialization issues
            $this->errors = [];
            $this->importResults = [];
            
            // Import completed successfully - redirect to avoid Livewire response issues
            return redirect()->route('employees.import')->with('success', 
                "🎉 Import completed successfully! All {$this->successCount} employees have been imported."
            );

        } catch (\Exception $e) {
            $this->importing = false;
            Log::error('Employee import failed: ' . $e->getMessage());
            Log::error('Import stack trace: ' . $e->getTraceAsString());
            
            // Show user-friendly error message
            $this->addError('file', 'Import failed: ' . $e->getMessage());
            session()->flash('error', 
                "❌ Import failed: " . $e->getMessage() . 
                " Please try again or contact support if the problem persists."
            );
        } finally {
            $this->importing = false;
        }
        
        } catch (\Exception $e) {
            // Final safety net to prevent 500 errors
            $this->importing = false;
            Log::error('Critical import error: ' . $e->getMessage());
            session()->flash('error', 
                "❌ An unexpected error occurred during import. " .
                "Please check if all employees were imported successfully and try again if needed."
            );
        }
    }

    private function validateHeaders($headers)
    {
        // Map expected headers to actual headers in the Excel file
        $headerMapping = [
            'first_name' => 'firstname',
            'last_name' => 'lastname', 
            'email' => 'email',
            'mobile' => 'mobileno'
        ];

        $missingHeaders = [];
        foreach ($headerMapping as $expected => $actual) {
            if (!in_array($actual, $headers)) {
                $missingHeaders[] = $actual;
            }
        }

        if (!empty($missingHeaders)) {
            throw new \Exception('Missing required columns: ' . implode(', ', $missingHeaders));
        }
    }

    private function processEmployeeRow($row, $headers, $rowNumber)
    {
        // Create associative array from row data
        $data = array_combine($headers, $row);
        
        // Clean the data
        $data = array_map('trim', $data);
        $data = array_map(function($value) {
            return $value === '' ? null : $value;
        }, $data);

        // Validate required fields (only firstname and mobileno are required now)
        if (empty($data['firstname']) || empty($data['mobileno'])) {
            throw new \Exception('Missing required fields: firstname or mobileno');
        }

        // Handle email - generate random one if missing
        $email = !empty($data['email']) ? $data['email'] : $this->generateRandomEmail($data['firstname'], $data['lastname']);
        
        // Check if user already exists (only if we have a real email)
        if (!empty($data['email']) && \App\Models\User::where('email', $email)->exists()) {
            throw new \Exception('User with email ' . $email . ' already exists');
        }

        DB::transaction(function () use ($data, $email) {
            // Step 1: Create user
            // Handle name - use firstname only if lastname is missing
            $fullName = $this->createFullName($data['firstname'], $data['lastname'] ?? null);
            
            $user = \App\Models\User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => bcrypt('admin123'),
                'punch_code' => null,
                'email_verified_at' => now(),
            ]);

            // Step 2: Create employee record
            $employee = \App\Models\Employee::create([
                'user_id' => $user->id,
                'employee_code' => $data['employeecode'] ?? null,
                'punch_code' => $data['punchcode'] ?? null,
                'first_name' => $data['firstname'],
                'last_name' => $data['lastname'] ?? null,
                'father_name' => $data['fathername'] ?? null,
                'mobile' => $data['mobileno'],
                'reports_to' => $data['employeereportsto'] ?? null,
                'role' => $data['roletemplate'] ?? null,
                'manual_attendance' => strtolower($data['allowmanualattendance']) === 'yes' ? 'yes' : 'no',
                'status' => strtolower($data['status']) ?? 'active',
                'department' => $data['department'] ?? null,
                'designation' => $data['designation'] ?? null,
                'shift' => null, // Not in Excel file
                
                // Documents Section - using Emirates ID as document
                'document_type' => 'emirates_id',
                'document_number' => $data['emiratesidcnicno'] ?? null,
                'issue_date' => $data['emiratescnicissuancedate'] ?? null,
                'expiry_date' => $data['emiratescnicexpirydate'] ?? null,
                'document_file' => null,
                'passport_no' => $data['passportno'] ?? null,
                'visa_no' => $data['visano'] ?? null,
                'visa_expiry' => $data['visaexpiry'] ?? null,
                'passport_expiry' => $data['passportexpiry'] ?? null,
                
                // Profile Picture
                'profile_picture' => null,
                
                // Emergency Contact
                'emergency_contact_name' => $data['emergencycontactperson'] ?? null,
                'emergency_relation' => $data['relationship'] ?? null,
                'emergency_phone' => $data['relativemobileno'] ?? null,
                'emergency_address' => $data['address'] ?? null,
                
                'allow_employee_login' => strtolower($data['allowemployeelogin']) === 'yes',
            ]);

            // Step 3: Create employee additional info
            \App\Models\EmployeeAdditionalInfo::create([
                'employee_id' => $employee->id,
                'date_of_birth' => $data['dateofbirth'] ?? null,
                'gender' => strtolower($data['gender']) ?? null,
                'marital_status' => $data['maritalstatus'] ? strtolower($data['maritalstatus']) : null,
                'nationality' => null, // Not in Excel file
                'blood_group' => null, // Not in Excel file
                'place_of_birth' => $data['placeofbirth'] ?? null,
                'religion' => $data['religion'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'province' => $data['province'] ?? null,
                'city' => $data['city'] ?? null,
                'area' => $data['area'] ?? null,
                'zip_code' => $data['zipcode'] ?? null,
                'family_code' => $data['familycode'] ?? null,
                'address' => $data['address'] ?? null,
                'degree' => null, // Not in Excel file
                'institute' => null, // Not in Excel file
                'passing_year' => null, // Not in Excel file
                'grade' => null, // Not in Excel file
            ]);

            // Step 4: Create employee organizational info
            \App\Models\EmployeeOrganizationalInfo::create([
                'employee_id' => $employee->id,
                'previous_company_name' => null, // Not in Excel file
                'previous_designation' => null, // Not in Excel file
                'from_date' => null, // Not in Excel file
                'to_date' => null, // Not in Excel file
                'reason_for_leaving' => null, // Not in Excel file
                'joining_date' => $data['joiningdate'] ?? null,
                'confirmation_date' => $data['confirmationdate'] ?? null,
                'expected_confirmation_days' => $data['expectedconfirmationdays'] ?? null,
                'contract_start_date' => $data['contractstartdate'] ?? null,
                'contract_end_date' => $data['contractenddate'] ?? null,
                'resign_date' => $data['resigndate'] ?? null,
                'leaving_date' => $data['leavingdate'] ?? null,
                'leaving_reason' => $data['leavingreason'] ?? null,
                'vendor' => $data['vendor'] ?? null,
                'division' => $data['division'] ?? null,
                'grade' => $data['grade'] ?? null,
                'employee_status' => $data['employeestatus'] ? strtolower($data['employeestatus']) : null,
                'employee_group' => $data['employeegroup'] ?? null,
                'cost_center' => $data['costcenter'] ?? null,
                'region' => $data['region'] ?? null,
                'gl_class' => $data['glclass'] ?? null,
                'position_type' => $data['positiontype'] ? strtolower($data['positiontype']) : null,
                'position' => $data['position'] ?? null,
                'station' => $data['station'] ?? null,
                'sub_department' => $data['subdepartment'] ?? null,
            ]);

            // Step 5: Create employee salary legal compliance
            \App\Models\EmployeeSalaryLegalCompliance::create([
                'employee_id' => $employee->id,
                'basic_salary' => null, // Not in Excel file
                'allowances' => null, // Not in Excel file
                'bonus' => null, // Not in Excel file
                'currency' => 'PKR', // Default currency
                'payment_frequency' => 'monthly', // Default frequency
                'bank_account' => $data['accountno'] ?? null,
                'account_title' => $data['accounttitle'] ?? null,
                'bank' => $data['bank'] ?? null,
                'branch_code' => $data['branchcode'] ?? null,
                'tax_id' => null, // Not in Excel file
                'salary_notes' => null, // Not in Excel file
                'eobi_registration_no' => $data['eobiregistrationno'] ?? null,
                'eobi_entry_date' => $data['eobientrydate'] ?? null,
                'social_security_no' => $data['socialsecurityno'] ?? null,
            ]);
        });
    }

    private function hasMissingRequiredFields($row, $headers)
    {
        $data = array_combine($headers, $row);
        
        // Only firstname and mobileno are truly required now
        // lastname is optional (we'll use firstname only if lastname is missing)
        // email is optional (we'll generate a random one if missing)
        $requiredFields = ['firstname', 'mobileno'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field]) || trim($data[$field]) === '') {
                return true;
            }
        }
        
        return false;
    }

    private function generateRandomEmail($firstName = null, $lastName = null)
    {
        // Generate a random string for the email
        $randomString = strtolower(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 10));
        
        // Create email with format: randomstring-undefined@undefined.com
        $email = $randomString . '-undefined@undefined.com';
        
        return $email;
    }

    private function createFullName($firstName, $lastName = null)
    {
        $firstName = trim($firstName ?? '');
        $lastName = trim($lastName ?? '');
        
        if (empty($lastName)) {
            return $firstName; // Use only first name if last name is missing
        }
        
        return $firstName . ' ' . $lastName;
    }
}
