<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isEmployeeView = $user?->hasRole('Employee') && !$user?->hasRole('Admin|Superadmin|DTR Incharge|Leave Incharge|Leave Approver');

        $monthParam = $request->input('month', Carbon::now()->format('Y-m'));
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

        $query = LeaveRequest::with(['employee', 'leaveType'])
            ->where(function ($q) use ($start, $end) {
                $q->whereDate('start_date', '<=', $end)
                    ->whereDate('end_date', '>=', $start);
            });

        $this->applyLeaveScope($query);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('status', 'like', '%' . $search . '%')
                    ->orWhereHas('leaveType', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%')
                            ->orWhere('code', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('employee', function ($q2) use ($search) {
                        $q2->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('badge_number', 'like', '%' . $search . '%');
                    });
            });
        }

        $leaves = $query->orderBy('start_date', 'desc')->paginate($perPage);

        return view($isEmployeeView ? 'leaves.index-employee' : 'leaves.index', [
            'leaves' => $leaves,
            'selectedMonth' => $monthParam,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $isEmployeeView = $user?->hasRole('Employee') && !$user?->hasRole('Admin|Superadmin|DTR Incharge|Leave Incharge|Leave Approver');

        $leaveTypes = LeaveType::query()->where('is_active', true)->orderBy('name')->get();

        if ($isEmployeeView) {
            $employee = $user?->employee;
            if (!$employee) {
                abort(403);
            }
            $availableLeave = $this->buildAvailableLeave($employee);
            return view('leaves.create-employee', compact('employee', 'leaveTypes', 'availableLeave'));
        }

        $employees = $this->scopedEmployeesQuery()->orderBy('first_name')->get();
        $selectedEmployeeId = old('employee_id') ?? $request->input('employee_id');
        $selectedEmployee = $selectedEmployeeId ? $employees->firstWhere('id', (int) $selectedEmployeeId) : null;
        $availableLeave = $selectedEmployee ? $this->buildAvailableLeave($selectedEmployee) : collect();

        return view('leaves.create', compact('employees', 'leaveTypes', 'selectedEmployee', 'availableLeave'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $canApprove = $user?->hasRole('Admin|Superadmin|Leave Incharge|Leave Approver');

        $validated = $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where(function ($query) {
                    $this->applyEmployeeScope($query);
                }),
            ],
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'leave_period' => ['nullable', 'in:full,morning,afternoon'],
            'reason' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'approved_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('approved_attachment') && !$canApprove) {
            abort(403);
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = isset($validated['end_date']) ? Carbon::parse($validated['end_date']) : $startDate->copy();
        $leavePeriod = $validated['leave_period'] ?? 'full';
        if (in_array($leavePeriod, ['morning', 'afternoon'], true) && !$startDate->isSameDay($endDate)) {
            return back()->withErrors(['leave_period' => 'Half-day leave is only allowed for a single date.'])->withInput();
        }
        $numberOfDays = in_array($leavePeriod, ['morning', 'afternoon'], true)
            ? 0.5
            : $this->calculateLeaveDays($startDate, $endDate);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        $balance = LeaveBalance::firstOrCreate(
            ['employee_id' => $validated['employee_id'], 'leave_type_id' => $leaveType->id],
            ['total_days' => $leaveType->default_days, 'used_days' => 0]
        );

        $remaining = max((float) $balance->total_days - (float) $balance->used_days, 0);
        if ($numberOfDays > $remaining) {
            return back()
                ->withErrors(['leave_type_id' => 'Insufficient leave balance. Remaining: ' . number_format($remaining, 2) . ' day(s).'])
                ->withInput();
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file && $file->isValid()) {
                $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $file->getClientOriginalName() ?: 'attachment');
                $content = file_get_contents($file->getPathname());
                if ($content !== false) {
                    Storage::disk('public')->put('leave-attachments/' . $filename, $content);
                    $validated['attachment'] = 'leave-attachments/' . $filename;
                }
            }
        }

        if ($request->hasFile('approved_attachment')) {
            $file = $request->file('approved_attachment');
            if ($file && $file->isValid()) {
                $filename = time() . '_' . uniqid() . '_approved_' . str_replace(' ', '_', $file->getClientOriginalName() ?: 'attachment');
                $content = file_get_contents($file->getPathname());
                if ($content !== false) {
                    Storage::disk('public')->put('leave-attachments/' . $filename, $content);
                    $validated['approved_attachment'] = 'leave-attachments/' . $filename;
                }
            }
        }

        $validated['end_date'] = $endDate->format('Y-m-d');
        $validated['leave_period'] = $leavePeriod;
        $validated['number_of_days'] = $numberOfDays;
        $validated['status'] = 'Filed';

        LeaveRequest::create($validated);

        $balance->used_days = (float) $balance->used_days + $numberOfDays;
        $balance->save();

        return redirect()->route('leaves.index')->with('success', 'Leave filed successfully.');
    }

    public function show(LeaveRequest $leaf)
    {
        $leave = $leaf;
        $this->authorizeLeaveAccess($leave);
        $leave->load(['employee.department', 'employee.position', 'leaveType', 'approver', 'validator']);
        return view('leaves.show', compact('leave'));
    }

    public function print(LeaveRequest $leaf)
    {
        $leave = $leaf;
        $this->authorizeLeaveAccess($leave);
        $leave->load(['employee.department', 'employee.position', 'leaveType', 'approver', 'validator']);

        $employee = $leave->employee;
        $department = $employee?->department;
        $position = $employee?->position;

        return view('leaves.print', compact('leave', 'employee', 'department', 'position'));
    }

    public function downloadPdf(LeaveRequest $leaf)
    {
        $leave = $leaf;
        $this->authorizeLeaveAccess($leave);
        $leave->load(['employee.department', 'employee.position', 'leaveType', 'approver', 'validator']);

        $employee = $leave->employee;
        $department = $employee?->department;
        $position = $employee?->position;

        $pdf = Pdf::loadView('leaves.pdf', compact('leave', 'employee', 'department', 'position'))
            ->setPaper('a4', 'portrait');

        $filename = 'leave-form-' . $leave->id . '.pdf';

        return $pdf->download($filename);
    }

    public function edit(LeaveRequest $leaf)
    {
        $leave = $leaf;
        $this->authorizeLeaveAccess($leave);
        $user = auth()->user();
        $canManageValidated = $user?->hasRole('Admin|Superadmin|Leave Incharge');

        if ($leave->status === 'Validated' && !$canManageValidated) {
            abort(403);
        }
        $isEmployeeView = $user?->hasRole('Employee') && !$user?->hasRole('Admin|Superadmin|DTR Incharge|Leave Incharge|Leave Approver');

        $leaveTypes = LeaveType::query()->where('is_active', true)->orderBy('name')->get();

        if ($isEmployeeView) {
            $employee = $user?->employee;
            if (!$employee) {
                abort(403);
            }
            $availableLeave = $this->buildAvailableLeave($employee);
            return view('leaves.edit-employee', compact('leave', 'leaveTypes', 'employee', 'availableLeave'));
        }

        $employees = $this->scopedEmployeesQuery()->orderBy('first_name')->get();
        $selectedEmployee = $employees->firstWhere('id', $leave->employee_id);
        $availableLeave = $selectedEmployee ? $this->buildAvailableLeave($selectedEmployee) : collect();

        return view('leaves.edit', compact('leave', 'leaveTypes', 'employees', 'selectedEmployee', 'availableLeave'));
    }

    public function update(Request $request, LeaveRequest $leaf)
    {
        $leave = $leaf;
        $this->authorizeLeaveAccess($leave);
        $user = auth()->user();
        $canValidate = $user?->hasRole('Admin|Superadmin|DTR Incharge');
        $canApprove = $user?->hasRole('Admin|Superadmin|Leave Incharge|Leave Approver');
        $canManageValidated = $user?->hasRole('Admin|Superadmin|Leave Incharge');

        if ($leave->status === 'Validated' && !$canManageValidated) {
            abort(403);
        }

        if ($leave->status === 'Approved') {
            if (!$canApprove) {
                abort(403);
            }
            if (empty($user?->pnpki_full_name) || empty($user?->pnpki_serial_number) || empty($user?->pnpki_certificate_path)) {
                return back()->withErrors([
                    'approved_attachment' => 'PNPKI credentials are required to manage approved leave attachments.',
                ])->withInput();
            }
            $validated = $request->validate([
                'approved_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ]);

            if (!$request->hasFile('approved_attachment') && empty($leave->approved_attachment)) {
                return back()->withErrors([
                    'approved_attachment' => 'Upload the PNPKI-signed leave document for approved leaves.',
                ])->withInput();
            }

            if ($request->hasFile('approved_attachment')) {
                $file = $request->file('approved_attachment');
                if ($file && $file->isValid()) {
                    if (!empty($leave->approved_attachment)) {
                        Storage::disk('public')->delete($leave->approved_attachment);
                    }
                    $filename = time() . '_' . uniqid() . '_approved_' . str_replace(' ', '_', $file->getClientOriginalName() ?: 'attachment');
                    $content = file_get_contents($file->getPathname());
                    if ($content !== false) {
                        Storage::disk('public')->put('leave-attachments/' . $filename, $content);
                        $validated['approved_attachment'] = 'leave-attachments/' . $filename;
                    }
                }
            }

            $leave->update($validated);

            return redirect()->route('leaves.index')->with('success', 'Approved attachment updated successfully.');
        }

        $rules = [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where(function ($query) {
                    $this->applyEmployeeScope($query);
                }),
            ],
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'leave_period' => ['nullable', 'in:full,morning,afternoon'],
            'reason' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];

        if ($canValidate || $canApprove) {
            $rules['status'] = ['nullable', 'in:Filed,Validated,Approved'];
            $rules['approved_attachment'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
        }

        if ($request->hasFile('approved_attachment') && !$canApprove) {
            abort(403);
        }

        $validated = $request->validate($rules);

        $desiredStatus = ($canValidate || $canApprove) && array_key_exists('status', $validated)
            ? $validated['status']
            : $leave->status;

        if ($desiredStatus === 'Validated' && !$canValidate) {
            abort(403);
        }

        if ($desiredStatus === 'Approved' && !$canApprove) {
            abort(403);
        }

        if ($desiredStatus === 'Approved' && $leave->status !== 'Validated' && $desiredStatus !== $leave->status) {
            return back()->withErrors([
                'status' => 'Leave must be validated by DTR Incharge before approval.',
            ])->withInput();
        }

        if ($desiredStatus === 'Approved') {
            if (empty($user?->pnpki_full_name) || empty($user?->pnpki_serial_number) || empty($user?->pnpki_certificate_path)) {
                return back()->withErrors([
                    'status' => 'PNPKI credentials are required to approve leave. Please assign PNPKI to the approving authority first.',
                ])->withInput();
            }

            if (!$request->hasFile('approved_attachment') && empty($leave->approved_attachment)) {
                return back()->withErrors([
                    'approved_attachment' => 'Upload the PNPKI-signed leave document before approving.',
                ])->withInput();
            }
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = isset($validated['end_date']) ? Carbon::parse($validated['end_date']) : $startDate->copy();
        $leavePeriod = $validated['leave_period'] ?? 'full';
        if (in_array($leavePeriod, ['morning', 'afternoon'], true) && !$startDate->isSameDay($endDate)) {
            return back()->withErrors(['leave_period' => 'Half-day leave is only allowed for a single date.'])->withInput();
        }
        $numberOfDays = in_array($leavePeriod, ['morning', 'afternoon'], true)
            ? 0.5
            : $this->calculateLeaveDays($startDate, $endDate);

        $oldEmployeeId = $leave->employee_id;
        $oldLeaveTypeId = $leave->leave_type_id;
        $oldDays = (float) $leave->number_of_days;

        $oldBalance = LeaveBalance::firstOrCreate(
            ['employee_id' => $oldEmployeeId, 'leave_type_id' => $oldLeaveTypeId],
            ['total_days' => 0, 'used_days' => 0]
        );
        $oldBalance->used_days = max((float) $oldBalance->used_days - $oldDays, 0);
        $oldBalance->save();

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        $newBalance = LeaveBalance::firstOrCreate(
            ['employee_id' => $validated['employee_id'], 'leave_type_id' => $leaveType->id],
            ['total_days' => $leaveType->default_days, 'used_days' => 0]
        );

        $remaining = max((float) $newBalance->total_days - (float) $newBalance->used_days, 0);
        if ($numberOfDays > $remaining) {
            $oldBalance->used_days = (float) $oldBalance->used_days + $oldDays;
            $oldBalance->save();

            return back()
                ->withErrors(['leave_type_id' => 'Insufficient leave balance. Remaining: ' . number_format($remaining, 2) . ' day(s).'])
                ->withInput();
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file && $file->isValid()) {
                if (!empty($leave->attachment)) {
                    Storage::disk('public')->delete($leave->attachment);
                }
                $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $file->getClientOriginalName() ?: 'attachment');
                $content = file_get_contents($file->getPathname());
                if ($content !== false) {
                    Storage::disk('public')->put('leave-attachments/' . $filename, $content);
                    $validated['attachment'] = 'leave-attachments/' . $filename;
                }
            }
        }

        if ($request->hasFile('approved_attachment')) {
            $file = $request->file('approved_attachment');
            if ($file && $file->isValid()) {
                if (!empty($leave->approved_attachment)) {
                    Storage::disk('public')->delete($leave->approved_attachment);
                }
                $filename = time() . '_' . uniqid() . '_approved_' . str_replace(' ', '_', $file->getClientOriginalName() ?: 'attachment');
                $content = file_get_contents($file->getPathname());
                if ($content !== false) {
                    Storage::disk('public')->put('leave-attachments/' . $filename, $content);
                    $validated['approved_attachment'] = 'leave-attachments/' . $filename;
                }
            }
        }

        $validated['end_date'] = $endDate->format('Y-m-d');
        $validated['leave_period'] = $leavePeriod;
        $validated['number_of_days'] = $numberOfDays;
        if (($canValidate || $canApprove) && array_key_exists('status', $validated)) {
            $validated['status'] = $validated['status'] ?: $leave->status;
        } else {
            unset($validated['status']);
        }

        if ($canValidate && ($validated['status'] ?? $leave->status) === 'Validated') {
            $validated['validated_by_user_id'] = $user?->id;
            $validated['validated_at'] = $leave->validated_at ?? now();
        }

        if ($canValidate && ($validated['status'] ?? $leave->status) !== 'Validated') {
            $validated['validated_by_user_id'] = null;
            $validated['validated_at'] = null;
        }

        if ($canApprove && ($validated['status'] ?? $leave->status) === 'Approved') {
            $validated['approved_by_user_id'] = $user?->id;
            $validated['approved_at'] = $leave->approved_at ?? now();
            $validated['approved_pnpki_full_name'] = $user?->pnpki_full_name;
            $validated['approved_pnpki_serial_number'] = $user?->pnpki_serial_number;
            $validated['approved_pnpki_certificate_path'] = $user?->pnpki_certificate_path;
        }

        if ($canApprove && ($validated['status'] ?? $leave->status) !== 'Approved') {
            $validated['approved_by_user_id'] = null;
            $validated['approved_at'] = null;
            $validated['approved_pnpki_full_name'] = null;
            $validated['approved_pnpki_serial_number'] = null;
            $validated['approved_pnpki_certificate_path'] = null;
        }

        $leave->update($validated);

        $newBalance->used_days = (float) $newBalance->used_days + $numberOfDays;
        $newBalance->save();

        return redirect()->route('leaves.index')->with('success', 'Leave updated successfully.');
    }

    public function destroy(LeaveRequest $leaf)
    {
        $leave = $leaf;
        $this->authorizeLeaveAccess($leave);
        $user = auth()->user();
        $canManageValidated = $user?->hasRole('Admin|Superadmin|Leave Incharge');
        $canManageFiled = $user?->hasRole('Admin|Superadmin|Leave Incharge');
        $employeeOwner = $user?->employee;
        $isOwner = $employeeOwner && $leave->employee_id === $employeeOwner->id;

        if ($leave->status === 'Approved') {
            abort(403);
        }

        if ($leave->status === 'Validated' && !$canManageValidated) {
            abort(403);
        }

        if ($leave->status === 'Filed' && !($canManageFiled || $isOwner)) {
            abort(403);
        }

        $balance = LeaveBalance::where('employee_id', $leave->employee_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->first();

        if ($balance) {
            $balance->used_days = max((float) $balance->used_days - (float) $leave->number_of_days, 0);
            $balance->save();
        }

        if (!empty($leave->attachment)) {
            Storage::disk('public')->delete($leave->attachment);
        }

        if (!empty($leave->approved_attachment)) {
            Storage::disk('public')->delete($leave->approved_attachment);
        }

        $leave->delete();

        return redirect()->route('leaves.index')->with('success', 'Leave deleted successfully.');
    }

    public function batchApprove(Request $request)
    {
        $user = auth()->user();
        $canApprove = $user?->hasRole('Admin|Superadmin|Leave Incharge|Leave Approver');

        if (!$canApprove) {
            abort(403);
        }

        if (empty($user?->pnpki_full_name) || empty($user?->pnpki_serial_number) || empty($user?->pnpki_certificate_path)) {
            return back()->withErrors([
                'batch_approve' => 'PNPKI credentials are required to approve leaves. Please assign PNPKI to the approving authority first.',
            ]);
        }

        $validated = $request->validate([
            'leave_ids' => ['required', 'array'],
            'leave_ids.*' => ['integer', 'exists:leave_requests,id'],
        ]);

        $leaves = LeaveRequest::whereIn('id', $validated['leave_ids'])->get();

        $approvedCount = 0;
        $errors = [];

        foreach ($leaves as $leave) {
            $this->authorizeLeaveAccess($leave);

            if ($leave->status !== 'Validated') {
                $errors[] = "Leave #{$leave->id} is not validated.";
                continue;
            }

            if (empty($leave->approved_attachment)) {
                $errors[] = "Leave #{$leave->id} is missing the PNPKI-signed attachment.";
                continue;
            }

            $leave->update([
                'status' => 'Approved',
                'approved_by_user_id' => $user?->id,
                'approved_at' => $leave->approved_at ?? now(),
                'approved_pnpki_full_name' => $user?->pnpki_full_name,
                'approved_pnpki_serial_number' => $user?->pnpki_serial_number,
                'approved_pnpki_certificate_path' => $user?->pnpki_certificate_path,
            ]);

            $approvedCount++;
        }

        if (!empty($errors)) {
            return back()->withErrors(['batch_approve' => implode(' ', $errors)]);
        }

        return redirect()->route('leaves.index')->with('success', "{$approvedCount} leave(s) approved successfully.");
    }

    private function calculateLeaveDays(Carbon $start, Carbon $end): float
    {
        return (float) $start->diffInDays($end) + 1;
    }

    private function buildAvailableLeave(Employee $employee)
    {
        $leaveTypes = LeaveType::query()->where('is_active', true)->orderBy('name')->get();
        $balances = LeaveBalance::where('employee_id', $employee->id)->get()->keyBy('leave_type_id');

        return $leaveTypes->map(function ($type) use ($balances) {
            $balance = $balances->get($type->id);
            $total = $balance ? (float) $balance->total_days : (float) $type->default_days;
            $used = $balance ? (float) $balance->used_days : 0;
            $remaining = max($total - $used, 0);

            return (object) [
                'type' => $type,
                'total' => $total,
                'used' => $used,
                'remaining' => $remaining,
            ];
        });
    }

    private function scopedEmployeesQuery()
    {
        $user = auth()->user();

        if ($user?->hasRole('Admin|Superadmin|DTR Incharge|Leave Incharge|Leave Approver')) {
            return Employee::query();
        }

        $employee = $user?->employee;
        if (!$employee) {
            return Employee::query()->whereRaw('1=0');
        }

        return Employee::query()->where('id', $employee->id);
    }

    private function applyEmployeeScope($query): void
    {
        $user = auth()->user();
        if ($user?->hasRole('Admin|Superadmin|DTR Incharge|Leave Incharge|Leave Approver')) {
            return;
        }

        $employee = $user?->employee;
        if (!$employee) {
            $query->whereRaw('1=0');
            return;
        }

        $query->where('id', $employee->id);
    }

    private function applyLeaveScope($query): void
    {
        $user = auth()->user();
        if ($user?->hasRole('Admin|Superadmin|DTR Incharge|Leave Incharge|Leave Approver')) {
            return;
        }

        $employee = $user?->employee;
        if (!$employee) {
            $query->whereRaw('1=0');
            return;
        }

        $query->where('employee_id', $employee->id);
    }

    private function authorizeLeaveAccess(LeaveRequest $leave): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if ($user->hasRole('Admin|Superadmin|DTR Incharge|Leave Incharge|Leave Approver')) {
            return;
        }

        $employee = $user->employee;
        if (!$employee) {
            abort(403);
        }

        if ($leave->employee_id !== $employee->id) {
            abort(403);
        }
    }
}
