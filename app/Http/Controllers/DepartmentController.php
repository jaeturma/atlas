<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments
     */
    public function index()
    {
        $departments = Department::orderBy('name')->paginate(15);
        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:departments|max:255',
            'code' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'head_name' => 'nullable|string|max:255',
            'head_position_title' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Department::create($validated);

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department
     */
    public function show(Department $department)
    {
        $department->load('employees');
        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the department
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:departments,name,' . $department->id . '|max:255',
            'code' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'head_name' => 'nullable|string|max:255',
            'head_position_title' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department
     */
    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }

    /**
     * Import departments from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|max:2048',
        ]);

        try {
            $file = $request->file('csv_file');
            
            if (!$file || !$file->isValid()) {
                return redirect()->route('departments.index')
                    ->with('error', 'Invalid file upload.');
            }

            // Get the temporary file path
            $filePath = $file->getPathname();
            
            if (!$filePath || !file_exists($filePath)) {
                return redirect()->route('departments.index')
                    ->with('error', 'File not accessible.');
            }

            $handle = fopen($filePath, 'r');
            
            if (!$handle) {
                return redirect()->route('departments.index')
                    ->with('error', 'Unable to read file.');
            }
        
        // Skip header row
        $header = fgetcsv($handle);
        
        $imported = 0;
        $errors = [];
        $row = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            
            try {
                // Expected CSV format: name,code,description,head_name,head_position_title,is_active
                Department::updateOrCreate(
                    ['name' => $data[0]], // Match by name
                    [
                        'code' => $data[1] ?? null,
                        'description' => $data[2] ?? null,
                        'head_name' => $data[3] ?? null,
                        'head_position_title' => $data[4] ?? null,
                        'is_active' => isset($data[5]) ? (strtolower($data[5]) === 'true' || $data[5] === '1') : true,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row $row: " . $e->getMessage();
            }
        }

        fclose($handle);
        
        // Clear progress
        session()->forget('import_progress');

        if (count($errors) > 0) {
            return redirect()->route('departments.index')
                ->with('success', "$imported departments imported successfully.")
                ->with('import_errors', $errors);
        }

        return redirect()->route('departments.index')
            ->with('success', "$imported departments imported successfully.");
            
        } catch (\Exception $e) {
            return redirect()->route('departments.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download sample CSV template
     */
    public function downloadTemplate()
    {
        $filename = 'departments_template.csv';
        $columns = ['name', 'code', 'description', 'head_name', 'head_position_title', 'is_active'];
        $sample = ['IT Department', 'IT', 'Information Technology', 'John Doe', 'IT Manager', 'true'];

        $callback = function() use ($columns, $sample) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $sample);
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
