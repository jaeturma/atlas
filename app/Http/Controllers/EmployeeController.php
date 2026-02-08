<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\EmployeeGroup;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $employeesQuery = Employee::query();

        if ($user && $user->hasRole('DTR Incharge')) {
            $departmentId = $user->employee?->department_id;

            if ($departmentId) {
                $employeesQuery->where('department_id', $departmentId);
            } else {
                $employeesQuery->whereRaw('1 = 0');
            }
        }

        $employees = $employeesQuery->paginate(15);
        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (auth()->user()?->hasRole('DTR Incharge')) {
            abort(403);
        }

        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (auth()->user()?->hasRole('DTR Incharge')) {
            abort(403);
        }

        $validated = $request->validate([
            'badge_number' => 'nullable|string|max:50|unique:employees,badge_number',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'civil_status' => 'nullable|string|max:50',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'tin' => 'nullable|string|max:50',
            'sss' => 'nullable|string|max:50',
            'philhealth' => 'nullable|string|max:50',
            'pagibig' => 'nullable|string|max:50',
            'position_id' => 'nullable|exists:positions,id',
            'department_id' => 'nullable|exists:departments,id',
            'salary' => 'nullable|numeric|min:0',
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load('department', 'position', 'employeeGroup');
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $positions = Position::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();
        $employeeGroups = EmployeeGroup::where('is_active', true)->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'positions', 'departments', 'employeeGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'badge_number' => 'nullable|string|max:50|unique:employees,badge_number,' . $employee->id,
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'civil_status' => 'nullable|string|max:50',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'tin' => 'nullable|string|max:50',
            'sss' => 'nullable|string|max:50',
            'philhealth' => 'nullable|string|max:50',
            'pagibig' => 'nullable|string|max:50',
            'position_id' => 'nullable|exists:positions,id',
            'department_id' => 'nullable|exists:departments,id',
            'employee_group_id' => 'required|exists:employee_groups,id',
            'dtr_signatory_department_id' => 'nullable|exists:departments,id',
            'salary' => 'nullable|numeric|min:0',
        ]);

        if (!auth()->user()?->hasRole('Admin|Superadmin|DTR Incharge')) {
            unset($validated['dtr_signatory_department_id']);
        }

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        if (auth()->user()?->hasRole('DTR Incharge')) {
            abort(403);
        }

        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    /**
     * Import employees from CSV
     */
    public function import(Request $request)
    {
        if (auth()->user()?->hasRole('DTR Incharge')) {
            abort(403);
        }

        $request->validate([
            'csv_file' => 'required|file|max:2048',
        ]);

        try {
            $file = $request->file('csv_file');
            
            if (!$file || !$file->isValid()) {
                return redirect()->route('employees.index')
                    ->with('error', 'Invalid file upload.');
            }

            // Get the temporary file path
            $filePath = $file->getPathname();
            
            if (!$filePath || !file_exists($filePath)) {
                return redirect()->route('employees.index')
                    ->with('error', 'File not accessible.');
            }

            $handle = fopen($filePath, 'r');
            
            if (!$handle) {
                return redirect()->route('employees.index')
                    ->with('error', 'Unable to read file.');
            }
        
        // Skip header row
        $header = fgetcsv($handle);
        
        // Count total rows first
        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = $data;
        }
        $totalRows = count($rows);
        
        $imported = 0;
        $errors = [];
        $rowNumber = 1;

        foreach ($rows as $index => $data) {
            $rowNumber++;
            
            // Update progress
            $progress = $totalRows > 0 ? round((($index + 1) / $totalRows) * 100) : 100;
            session(['import_progress' => $progress]);
            
            try {
                // Normalize foreign keys and nullable fields
                $departmentId = null;
                if (!empty($data[17]) && $data[17] !== '?') {
                    $departmentId = Department::find($data[17]) ? $data[17] : null;
                }

                $positionId = null;
                if (!empty($data[18]) && $data[18] !== '?') {
                    $positionId = Position::find($data[18]) ? $data[18] : null;
                }

                $birthdate = (!empty($data[5]) && $data[5] !== '?') ? $data[5] : null;
                $salary = (!empty($data[19]) && $data[19] !== '?') ? $data[19] : null;

                // Expected CSV format: badge_number,first_name,middle_name,last_name,suffix,birthdate,gender,civil_status,email,phone,address,emergency_contact,emergency_phone,tin,sss,philhealth,pagibig,department_id,position_id,salary
                Employee::updateOrCreate(
                    ['email' => $data[8]], // Match by email
                    [
                        'badge_number' => $data[0] ?? null,
                        'first_name' => $data[1],
                        'middle_name' => $data[2] ?? null,
                        'last_name' => $data[3],
                        'suffix' => $data[4] ?? null,
                        'birthdate' => $birthdate,
                        'gender' => $data[6] ?? null,
                        'civil_status' => $data[7] ?? null,
                        'phone' => $data[9] ?? null,
                        'address' => $data[10] ?? null,
                        'emergency_contact' => $data[11] ?? null,
                        'emergency_phone' => $data[12] ?? null,
                        'tin' => $data[13] ?? null,
                        'sss' => $data[14] ?? null,
                        'philhealth' => $data[15] ?? null,
                        'pagibig' => $data[16] ?? null,
                        'department_id' => $departmentId,
                        'position_id' => $positionId,
                        'salary' => $salary,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row $rowNumber: " . $e->getMessage();
            }
        }

        fclose($handle);
        
        // Clear progress
        session()->forget('import_progress');

        if (count($errors) > 0) {
            return redirect()->route('employees.index')
                ->with('success', "$imported employees imported successfully.")
                ->with('import_errors', $errors);
        }

        return redirect()->route('employees.index')
            ->with('success', "$imported employees imported successfully.");
            
        } catch (\Exception $e) {
            return redirect()->route('employees.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download sample CSV template
     */
    public function downloadTemplate()
    {
        $filename = 'employees_template.csv';
        $columns = ['badge_number', 'first_name', 'middle_name', 'last_name', 'suffix', 'birthdate', 'gender', 'civil_status', 'email', 'phone', 'address', 'emergency_contact', 'emergency_phone', 'tin', 'sss', 'philhealth', 'pagibig', 'department_id', 'position_id', 'salary'];
        $sample = ['EMP001', 'Juan', 'Cruz', 'Dela Cruz', 'Jr.', '1990-01-15', 'Male', 'Single', 'juan.delacruz@example.com', '09171234567', '123 Main St', 'Maria Dela Cruz', '09187654321', '123-456-789', '12-3456789-0', '12-345678901-2', '1234-5678-9012', '1', '1', '25000'];

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

    /**
     * Add a deduction to an employee
     */
    public function addDeduction(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'deduction_id' => 'required|exists:deductions,id',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'nullable|string|in:monthly,bi-monthly,weekly',
        ]);

        // Check if already exists
        $exists = $employee->deductions()->where('deduction_id', $validated['deduction_id'])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This deduction is already assigned to the employee.'
            ], 400);
        }

        $employee->deductions()->attach($validated['deduction_id'], [
            'amount' => $validated['amount'],
            'frequency' => $validated['frequency'] ?? 'monthly',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Deduction added successfully.'
        ]);
    }

    /**
     * Update an employee deduction
     */
    public function updateDeduction(Request $request, Employee $employee, $pivotId)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'frequency' => 'nullable|string|in:monthly,bi-monthly,weekly',
        ]);

        \DB::table('employee_deduction_assignments')
            ->where('id', $pivotId)
            ->where('employee_id', $employee->id)
            ->update([
                'amount' => $validated['amount'],
                'frequency' => $validated['frequency'] ?? 'monthly',
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Deduction updated successfully.'
        ]);
    }

    /**
     * Toggle deduction status
     */
    public function toggleDeduction(Request $request, Employee $employee, $pivotId)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        \DB::table('employee_deduction_assignments')
            ->where('id', $pivotId)
            ->where('employee_id', $employee->id)
            ->update([
                'is_active' => $validated['is_active'],
                'updated_at' => now(),
            ]);

        $status = $validated['is_active'] ? 'activated' : 'deactivated';
        return response()->json([
            'success' => true,
            'message' => "Deduction {$status} successfully."
        ]);
    }

    /**
     * Remove a deduction from an employee
     */
    public function removeDeduction(Employee $employee, $pivotId)
    {
        \DB::table('employee_deduction_assignments')
            ->where('id', $pivotId)
            ->where('employee_id', $employee->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deduction removed successfully.'
        ]);
    }
}
