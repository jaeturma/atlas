<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Department;
use App\Models\EmployeeGroup;
use Livewire\Attributes\Validate;
use Carbon\Carbon;

class EmployeeForm extends Component
{
    public ?Employee $employee = null;

    public ?int $employeeId = null;

    #[Validate('nullable|string|max:50')]
    public ?string $badge_number = null;

    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $middle_name = null;

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('nullable|string|max:50')]
    public ?string $suffix = null;

    #[Validate('nullable|date')]
    public ?string $birthdate = null;

    #[Validate('nullable|string|max:50')]
    public ?string $gender = null;

    #[Validate('nullable|string|max:50')]
    public ?string $civil_status = null;

    #[Validate('nullable|email|max:255')]
    public ?string $email = null;

    #[Validate('nullable|string|max:20')]
    public ?string $phone = null;

    #[Validate('nullable|string|max:500')]
    public ?string $address = null;

    #[Validate('nullable|string|max:255')]
    public ?string $emergency_contact = null;

    #[Validate('nullable|string|max:20')]
    public ?string $emergency_phone = null;

    #[Validate('nullable|string|max:50')]
    public ?string $tin = null;

    #[Validate('nullable|string|max:50')]
    public ?string $sss = null;

    #[Validate('nullable|string|max:50')]
    public ?string $philhealth = null;

    #[Validate('nullable|string|max:50')]
    public ?string $pagibig = null;

    #[Validate('nullable|exists:positions,id')]
    public ?int $position_id = null;

    #[Validate('required|exists:departments,id')]
    public ?int $department_id = null;

    #[Validate('nullable|exists:departments,id')]
    public ?int $dtr_signatory_department_id = null;

    #[Validate('required|exists:employee_groups,id')]
    public ?int $employee_group_id = null;

    public function mount(?Employee $employee = null, ?int $employeeId = null)
    {
        $this->employeeId = $employeeId ?? $this->employeeId;
        $this->loadEmployee($employee);
    }

    private function loadEmployee(?Employee $employee = null): void
    {
        if (!$employee && $this->employeeId) {
            $employee = Employee::find($this->employeeId);
        }

        if (!$employee) {
            $routeEmployee = request()->route('employee');
            if ($routeEmployee instanceof Employee) {
                $employee = $routeEmployee;
            } elseif (is_numeric($routeEmployee)) {
                $employee = Employee::find((int) $routeEmployee);
            }
        }

        if (!$employee) {
            return;
        }

        $this->employee = $employee;
        $this->badge_number = $employee->badge_number;
        $this->first_name = $employee->first_name ?? '';
        $this->middle_name = $employee->middle_name;
        $this->last_name = $employee->last_name ?? '';
        $this->suffix = $employee->suffix;
        $this->birthdate = $employee->birthdate
            ? ($employee->birthdate instanceof Carbon
                ? $employee->birthdate->format('Y-m-d')
                : Carbon::parse($employee->birthdate)->format('Y-m-d'))
            : null;
        $this->gender = $employee->gender;
        $this->civil_status = $employee->civil_status;
        $this->email = $employee->email ?? '';
        $this->phone = $employee->phone;
        $this->address = $employee->address;
        $this->emergency_contact = $employee->emergency_contact;
        $this->emergency_phone = $employee->emergency_phone;
        $this->tin = $employee->tin;
        $this->sss = $employee->sss;
        $this->philhealth = $employee->philhealth;
        $this->pagibig = $employee->pagibig;
        $this->position_id = $employee->position_id;
        $this->department_id = $employee->department_id;
        $this->employee_group_id = $employee->employee_group_id;
        $this->dtr_signatory_department_id = $employee->dtr_signatory_department_id;
    }


    public function save()
    {
        try {
            // Custom validation rules based on create/edit mode
            $rules = [
                'badge_number' => ['nullable', 'string', 'max:50'],
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'suffix' => 'nullable|string|max:50',
                'birthdate' => 'nullable|date',
                'gender' => 'nullable|string|max:50',
                'civil_status' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'emergency_contact' => 'nullable|string|max:255',
                'emergency_phone' => 'nullable|string|max:20',
                'tin' => 'nullable|string|max:50',
                'sss' => 'nullable|string|max:50',
                'philhealth' => 'nullable|string|max:50',
                'pagibig' => 'nullable|string|max:50',
                'position_id' => 'nullable|exists:positions,id',
                'department_id' => 'required|exists:departments,id',
                'dtr_signatory_department_id' => 'nullable|exists:departments,id',
                'employee_group_id' => 'required|exists:employee_groups,id',
            ];

            // Add unique validation
            if ($this->employee) {
                $rules['badge_number'][] = 'unique:employees,badge_number,' . $this->employee->id;
                $rules['email'] = 'nullable|email|max:255|unique:employees,email,' . $this->employee->id;
            } else {
                $rules['badge_number'][] = 'unique:employees,badge_number';
                $rules['email'] = 'nullable|email|max:255|unique:employees,email';
            }

            $validated = $this->validate($rules);

            // Ensure position_id and department_id are integers or null
            $data = [
                'badge_number' => $validated['badge_number'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'suffix' => $validated['suffix'],
                'birthdate' => $validated['birthdate'],
                'gender' => $validated['gender'],
                'civil_status' => $validated['civil_status'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_phone' => $validated['emergency_phone'],
                'tin' => $validated['tin'],
                'sss' => $validated['sss'],
                'philhealth' => $validated['philhealth'],
                'pagibig' => $validated['pagibig'],
                'position_id' => $this->position_id ? (int) $this->position_id : null,
                'department_id' => $this->department_id ? (int) $this->department_id : null,
                'employee_group_id' => $this->employee_group_id ? (int) $this->employee_group_id : null,
            ];

            if (auth()->user()?->hasRole('Admin|Superadmin|DTR Incharge')) {
                $data['dtr_signatory_department_id'] = $this->dtr_signatory_department_id
                    ? (int) $this->dtr_signatory_department_id
                    : null;
            }

            if ($this->employee) {
                $this->employee->update($data);
                $this->dispatch('swal:success', message: 'Employee updated successfully.', redirect: route('employees.show', ['employee' => $this->employee->id]));
                return;
            }

            $employee = Employee::create($data);
            $this->dispatch('swal:success', message: 'Employee created successfully.', redirect: route('employees.show', ['employee' => $employee->id]));
            return;
        } catch (\Throwable $e) {
            \Log::error('Employee save failed', [
                'employee_id' => $this->employee?->id,
                'error' => $e->getMessage(),
            ]);

            $this->addError('save', $e->getMessage() ?: 'Unable to save changes. Please try again.');
        }
    }

    public function render()
    {
        if (!$this->employee && !$this->first_name && !$this->last_name) {
            $this->loadEmployee();
        }

        $positions = Position::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();
        $employeeGroups = EmployeeGroup::where('is_active', true)->orderBy('name')->get();
        
        return view('livewire.employee-form', compact('positions', 'departments', 'employeeGroups'));
    }
}
