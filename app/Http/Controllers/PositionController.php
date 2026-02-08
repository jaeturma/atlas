<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of positions
     */
    public function index()
    {
        $positions = Position::orderBy('name')->paginate(15);
        return view('positions.index', compact('positions'));
    }

    /**
     * Show the form for creating a new position
     */
    public function create()
    {
        return view('positions.create');
    }

    /**
     * Store a newly created position
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:positions|max:255',
            'description' => 'nullable|string',
            'daily_rate' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        Position::create($validated);

        return redirect()->route('positions.index')->with('success', 'Position created successfully.');
    }

    /**
     * Display the specified position
     */
    public function show(Position $position)
    {
        return view('positions.show', compact('position'));
    }

    /**
     * Show the form for editing the position
     */
    public function edit(Position $position)
    {
        return view('positions.edit', compact('position'));
    }

    /**
     * Update the specified position
     */
    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:positions,name,' . $position->id . '|max:255',
            'description' => 'nullable|string',
            'daily_rate' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $position->update($validated);

        return redirect()->route('positions.index')->with('success', 'Position updated successfully.');
    }

    /**
     * Remove the specified position
     */
    public function destroy(Position $position)
    {
        $position->delete();
        return redirect()->route('positions.index')->with('success', 'Position deleted successfully.');
    }

    /**
     * Import positions from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|max:2048',
        ]);

        try {
            $file = $request->file('csv_file');
            
            if (!$file || !$file->isValid()) {
                return redirect()->route('positions.index')
                    ->with('error', 'Invalid file upload.');
            }

            // Get the temporary file path
            $filePath = $file->getPathname();
            
            if (!$filePath || !file_exists($filePath)) {
                return redirect()->route('positions.index')
                    ->with('error', 'File not accessible.');
            }

            $handle = fopen($filePath, 'r');
            
            if (!$handle) {
                return redirect()->route('positions.index')
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
                // Expected CSV format: name, description, daily_rate, is_active
                Position::updateOrCreate(
                    ['name' => $data[0]], // Match by name
                    [
                        'description' => $data[1] ?? null,
                        'daily_rate' => !empty($data[2]) && $data[2] !== '?' ? $data[2] : null,
                        'is_active' => isset($data[3]) ? filter_var($data[3], FILTER_VALIDATE_BOOLEAN) : true,
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
            return redirect()->route('positions.index')
                ->with('success', "$imported positions imported successfully.")
                ->with('import_errors', $errors);
        }

        return redirect()->route('positions.index')
            ->with('success', "$imported positions imported successfully.");
            
        } catch (\Exception $e) {
            return redirect()->route('positions.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download sample CSV template
     */
    public function downloadTemplate()
    {
        $filename = 'positions_template.csv';
        $columns = ['name', 'description', 'daily_rate', 'is_active'];
        $sample = ['Senior Developer', 'Lead software development', '1500', 'true'];

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
