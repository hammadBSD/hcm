<?php

namespace App\Livewire\Employees;

use Livewire\Component;

class Register extends Component
{
    // Tab management
    public $activeTab = 'general';

    // General Info form properties
    public $prefix = '';
    public $employeeCode = '';
    public $punchCode = '';
    public $firstName = '';
    public $lastName = '';
    public $fatherName = '';
    public $mobile = '';
    public $email = '';
    public $reportsTo = '';
    public $role = '';
    public $manualAttendance = 'no';
    public $status = 'active';
    public $department = '';
    public $designation = '';
    public $password = '';
    public $shift = '';
    public $allowEmployeeLogin = false;
    public $profilePicture;
    public $emergencyContactName = '';
    public $emergencyRelation = '';
    public $emergencyPhone = '';
    public $emergencyAddress = '';

    // Additional Info form properties
    public $dateOfBirth = '';
    public $gender = '';
    public $maritalStatus = '';
    public $nationality = '';
    public $bloodGroup = '';
    public $address = '';

    // Company Info form properties
    public $companyName = '';
    public $previousDesignation = '';
    public $fromDate = '';
    public $toDate = '';
    public $reasonForLeaving = '';

    // Documents form properties
    public $documentType = '';
    public $documentNumber = '';
    public $issueDate = '';
    public $expiryDate = '';
    public $documentFile;
    public $degree = '';
    public $institute = '';
    public $passingYear = '';
    public $grade = '';

    // Salary form properties
    public $basicSalary = '';
    public $allowances = '';
    public $bonus = '';
    public $currency = 'USD';
    public $paymentFrequency = 'monthly';
    public $bankAccount = '';
    public $taxId = '';
    public $salaryNotes = '';

    public function render()
    {
        return view('livewire.employees.register')
            ->layout('components.layouts.app');
    }

    public function nextTab()
    {
        $tabs = ['general', 'additional', 'company', 'documents', 'salary'];
        $currentIndex = array_search($this->activeTab, $tabs);
        
        if ($currentIndex !== false && $currentIndex < count($tabs) - 1) {
            $this->activeTab = $tabs[$currentIndex + 1];
        }
    }

    public function previousTab()
    {
        $tabs = ['general', 'additional', 'company', 'documents', 'salary'];
        $currentIndex = array_search($this->activeTab, $tabs);
        
        if ($currentIndex !== false && $currentIndex > 0) {
            $this->activeTab = $tabs[$currentIndex - 1];
        }
    }

    public function resetForm()
    {
        $this->reset();
    }

    public function saveDraft()
    {
        // Save draft functionality
        session()->flash('message', 'Draft saved successfully!');
    }

    public function submit()
    {
        // Validate and submit form
        $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        // Create employee logic here
        session()->flash('message', 'Employee created successfully!');
    }

    public function addQualification()
    {
        // Add qualification logic
    }

    public function removeQualification()
    {
        // Remove qualification logic
    }
}
