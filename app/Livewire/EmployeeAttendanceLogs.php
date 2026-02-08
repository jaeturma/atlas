<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;

class EmployeeAttendanceLogs extends Component
{
    use WithPagination;

    public Employee $employee;
    public int $perPage = 10;
    protected $paginationTheme = 'tailwind';

    public function mount(Employee $employee)
    {
        $this->employee = $employee;
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = $this->employee->attendanceLogs()
            ->orderBy('log_datetime', 'desc')
            ->paginate($this->perPage);

        return view('livewire.employee-attendance-logs', compact('logs'));
    }
}
