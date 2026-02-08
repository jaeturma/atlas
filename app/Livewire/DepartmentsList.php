<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Department;
use Livewire\Attributes\On;

class DepartmentsList extends Component
{
    public function render()
    {
        $departments = Department::paginate(15);
        return view('livewire.departments-list', compact('departments'));
    }

    #[On('department-deleted')]
    public function refresh()
    {
        // Component will re-render automatically
    }
}
