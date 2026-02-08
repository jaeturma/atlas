<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Employee;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isEmployeeView = $user?->hasRole('Employee') && !$user?->hasRole('Admin|Superadmin|DTR Incharge');

        $monthParam = $request->input('month');
        if (!$monthParam) {
            $monthParam = Carbon::now()->format('Y-m');
        }
        try {
            $start = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
            $end = Carbon::createFromFormat('Y-m', $monthParam)->endOfMonth();
        } catch (\Exception $e) {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            $monthParam = Carbon::now()->format('Y-m');
        }

        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $query = Activity::with('employee', 'holiday')
            ->where(function($q) use ($start, $end) {
                $q->whereDate('date', '<=', $end)
                  ->where(function($q2) use ($start) {
                      $q2->whereNull('end_date')
                         ->orWhereDate('end_date', '>=', $start);
                  });
            });

        $this->applyActivityScope($query);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('activity_type', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('employee', function($q2) use ($search) {
                      $q2->where('first_name', 'like', '%' . $search . '%')
                         ->orWhere('last_name', 'like', '%' . $search . '%')
                         ->orWhere('badge_number', 'like', '%' . $search . '%');
                  });
            });
        }

        $activities = $query->orderBy('date', 'desc')->paginate($perPage);

        return view($isEmployeeView ? 'activities.index-employee' : 'activities.index', [
            'activities' => $activities,
            'selectedMonth' => $monthParam,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $isEmployeeView = $user?->hasRole('Employee') && !$user?->hasRole('Admin|Superadmin|DTR Incharge');

        if ($isEmployeeView) {
            $employee = $user?->employee;
            if (!$employee) {
                abort(403);
            }
            return view('activities.create-employee', compact('employee'));
        }

        $employees = $this->scopedEmployeesQuery()->orderBy('first_name')->get();
        return view('activities.create', compact('employees'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where(function ($query) {
                    $this->applyEmployeeScope($query);
                }),
            ],
            'date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:date',
            'activity_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'memorandum_link' => 'nullable|url',
            'certificate_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'att_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Handle file uploads
        foreach(['certificate_attachment', 'att_attachment'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                if ($file && $file->isValid()) {
                    try {
                        // Generate unique filename
                        $originalName = $file->getClientOriginalName();
                        if (empty($originalName)) {
                            $originalName = 'file';
                        }
                        $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $originalName);
                        
                        // Get file content and store
                        $content = file_get_contents($file->getPathname());
                        if ($content !== false) {
                            Storage::disk('public')->put('attachments/' . $filename, $content);
                            $validated[$field] = 'attachments/' . $filename;
                        }
                    } catch (\Exception $e) {
                        \Log::error("File upload error for $field: " . $e->getMessage());
                    }
                }
            }
        }

        Activity::create($validated);

        return redirect()->route('activities.index')->with('success', 'Activity created successfully');
    }

    public function edit(Activity $activity)
    {
        $this->authorizeActivityAccess($activity);
        $user = auth()->user();
        $isEmployeeView = $user?->hasRole('Employee') && !$user?->hasRole('Admin|Superadmin|DTR Incharge');

        if ($isEmployeeView) {
            return view('activities.edit-employee', compact('activity'));
        }

        $employees = $this->scopedEmployeesQuery()->orderBy('first_name')->get();
        return view('activities.edit', compact('activity', 'employees'));
    }

    public function show(Activity $activity)
    {
        $this->authorizeActivityAccess($activity);
        return view('activities.show', compact('activity'));
    }

    public function update(Request $request, Activity $activity)
    {

        $this->authorizeActivityAccess($activity);

        $validated = $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where(function ($query) {
                    $this->applyEmployeeScope($query);
                }),
            ],
            'date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:date',
            'activity_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'memorandum_link' => 'nullable|url',
            'certificate_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'att_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Handle file uploads
        foreach(['certificate_attachment', 'att_attachment'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                if ($file && $file->isValid()) {
                    try {
                        // Delete old file if exists
                        $oldPath = $activity->$field;
                        if (is_string($oldPath) && !empty(trim($oldPath))) {
                            Storage::disk('public')->delete($oldPath);
                        }
                        
                        // Generate unique filename
                        $originalName = $file->getClientOriginalName();
                        if (empty($originalName)) {
                            $originalName = 'file';
                        }
                        $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $originalName);
                        
                        // Get file content and store
                        $content = file_get_contents($file->getPathname());
                        if ($content !== false) {
                            Storage::disk('public')->put('attachments/' . $filename, $content);
                            $validated[$field] = 'attachments/' . $filename;
                        }
                    } catch (\Exception $e) {
                        \Log::error("File upload error for $field: " . $e->getMessage());
                    }
                }
            }
        }

        $activity->update($validated);

        return redirect()->route('activities.index')->with('success', 'Activity updated successfully');
    }

    public function destroy(Activity $activity)
    {
        $this->authorizeActivityAccess($activity);
        $activity->delete();
        return redirect()->route('activities.index')->with('success', 'Activity deleted successfully');
    }

    private function scopedEmployeesQuery()
    {
        $user = auth()->user();

        if ($user?->hasRole('Admin|Superadmin')) {
            return Employee::query();
        }

        $employee = $user?->employee;
        if (!$employee) {
            return Employee::query()->whereRaw('1=0');
        }

        if ($user->hasRole('DTR Incharge') && $employee->department_id) {
            return Employee::query()->where('department_id', $employee->department_id);
        }

        return Employee::query()->where('id', $employee->id);
    }

    private function applyEmployeeScope($query): void
    {
        $user = auth()->user();
        if ($user?->hasRole('Admin|Superadmin')) {
            return;
        }

        $employee = $user?->employee;
        if (!$employee) {
            $query->whereRaw('1=0');
            return;
        }

        if ($user->hasRole('DTR Incharge') && $employee->department_id) {
            $query->where('department_id', $employee->department_id);
            return;
        }

        $query->where('id', $employee->id);
    }

    private function applyActivityScope($query): void
    {
        $user = auth()->user();
        if ($user?->hasRole('Admin|Superadmin')) {
            return;
        }

        $employee = $user?->employee;
        if (!$employee) {
            $query->whereRaw('1=0');
            return;
        }

        if ($user->hasRole('DTR Incharge') && $employee->department_id) {
            $query->whereHas('employee', function ($q) use ($employee) {
                $q->where('department_id', $employee->department_id);
            });
            return;
        }

        $query->where('employee_id', $employee->id);
    }

    private function authorizeActivityAccess(Activity $activity): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if ($user->hasRole('Admin')) {
            return;
        }

        $employee = $user->employee;
        if (!$employee) {
            abort(403);
        }

        if ($user->hasRole('DTR Incharge')) {
            if ($employee->department_id && $activity->employee?->department_id === $employee->department_id) {
                return;
            }
            abort(403);
        }

        if ($activity->employee_id !== $employee->id) {
            abort(403);
        }
    }
}
