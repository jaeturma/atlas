<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use Livewire\Attributes\On;

class EmployeeList extends Component
{
    public $departmentFilter = '';
    public $isDepartmentLocked = false;
    public $lockedDepartmentId = null;

    public function mount()
    {
        $user = auth()->user();

        if ($user && $user->hasRole('DTR Incharge')) {
            $this->isDepartmentLocked = true;
            $this->lockedDepartmentId = $user->employee?->department_id;
            $this->departmentFilter = $this->lockedDepartmentId ?: '';
        }
    }

    #[On('employee-deleted')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Employee::with(['position', 'department', 'employeeGroup']);

        if ($this->isDepartmentLocked) {
            $this->departmentFilter = $this->lockedDepartmentId ?: '';

            if ($this->lockedDepartmentId) {
                $query->where('department_id', $this->lockedDepartmentId);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($this->departmentFilter) {
            $query->where('department_id', $this->departmentFilter);
        }

        $employees = $query->get();
        if ($this->isDepartmentLocked && $this->lockedDepartmentId) {
            $departments = Department::where('id', $this->lockedDepartmentId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } else {
            $departments = Department::where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return view('livewire.employee-list', compact('employees', 'departments'));
    }
}
