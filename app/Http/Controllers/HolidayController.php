<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        // Type filter
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Per page
        $perPage = $request->get('per_page', 15);
        $holidays = $query->orderBy('date', 'desc')->paginate($perPage);

        $search = $request->get('search');
        
        return view('holidays.index', compact('holidays', 'search', 'perPage'));
    }

    public function create()
    {
        return view('holidays.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays',
            'end_date' => 'nullable|date|after_or_equal:date',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:regular,special',
            'memorandum_attachment' => 'nullable|url',
        ]);

        Holiday::create($validated);

        return redirect()->route('holidays.index')->with('success', 'Holiday created successfully');
    }

    public function edit(Holiday $holiday)
    {
        return view('holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'end_date' => 'nullable|date|after_or_equal:date',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:regular,special',
            'memorandum_attachment' => 'nullable|url',
        ]);

        $holiday->update($validated);

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->route('holidays.index')->with('success', 'Holiday deleted successfully');
    }
}