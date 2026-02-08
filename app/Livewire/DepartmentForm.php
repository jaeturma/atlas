<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Department;
use Livewire\Attributes\On;

class DepartmentForm extends Component
{
    public ?Department $department = null;

    public string $name = '';
    public ?string $code = null;
    public ?string $description = null;
    public ?string $head_name = null;
    public ?string $head_position_title = null;
    public bool $is_active = true;

    public function mount(?Department $department = null)
    {
        if ($department) {
            $this->department = $department;
            $this->populateFromDepartment($department);
        }
    }

    private function populateFromDepartment(Department $dept)
    {
        $this->name = $dept->name ?? '';
        $this->code = $dept->code;
        $this->description = $dept->description;
        $this->head_name = $dept->head_name;
        $this->head_position_title = $dept->head_position_title;
        $this->is_active = (bool) ($dept->is_active ?? true);
    }

    public function save()
    {
        // Ensure name is populated before validation
        if (!$this->name && $this->department) {
            $this->populateFromDepartment($this->department);
        }

        // Manual validation - only on submit
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'head_name' => 'nullable|string|max:255',
            'head_position_title' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($this->department) {
            $this->department->update([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'head_name' => $this->head_name,
                'head_position_title' => $this->head_position_title,
                'is_active' => $this->is_active,
            ]);
            return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
        } else {
            Department::create([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'head_name' => $this->head_name,
                'head_position_title' => $this->head_position_title,
                'is_active' => $this->is_active,
            ]);
            return redirect()->route('departments.index')->with('success', 'Department created successfully.');
        }
    }

    public function render()
    {
        return view('livewire.department-form');
    }
}