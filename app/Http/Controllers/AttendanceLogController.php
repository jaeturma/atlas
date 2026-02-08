<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Department;
use App\Models\ReportSetting;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceLogController extends Controller
{
    /**
     * Display a listing of attendance logs
     */
    public function index(Request $request)
    {
        $query = AttendanceLog::query()->with(['device', 'employee']);

        $this->applyAttendanceScope($query);

        // Filter by device
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('log_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('log_date', '<=', $request->end_date);
        }

        // Filter by status (In/Out)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->orderBy('log_datetime', 'desc')->paginate(10);

        $devices = Device::orderBy('name')->get();
        $employees = $this->scopedEmployeesQuery()->orderBy('last_name')->get();

        $cloudLogs = new LengthAwarePaginator([], 0, 10, 1, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
        $cloudError = null;

        if (auth()->user()?->hasRole('Admin|Superadmin') && $request->boolean('cloud_load')) {
            try {
                $cloudQuery = DB::connection('cloud')->table('attendance_logs');

                if ($request->filled('cloud_badge_number')) {
                    $cloudQuery->where('badge_number', $request->cloud_badge_number);
                }

                if ($request->filled('cloud_device_id')) {
                    $cloudQuery->where('device_id', $request->cloud_device_id);
                }

                if ($request->filled('cloud_status')) {
                    $cloudQuery->where('status', $request->cloud_status);
                }

                if ($request->filled('cloud_start_date')) {
                    $cloudQuery->whereDate('log_date', '>=', $request->cloud_start_date);
                }

                if ($request->filled('cloud_end_date')) {
                    $cloudQuery->whereDate('log_date', '<=', $request->cloud_end_date);
                }

                $cloudLogs = $cloudQuery
                    ->orderBy('log_datetime', 'desc')
                    ->paginate(10, ['*'], 'cloud_page');
            } catch (\Throwable $e) {
                \Log::error('Cloud attendance logs load failed', [
                    'error' => $e->getMessage(),
                ]);
                $cloudError = config('app.debug')
                    ? 'Cloud logs error: ' . $e->getMessage()
                    : 'Unable to load cloud attendance logs.';
            }
        }

        return view('attendance-logs.index', compact('logs', 'devices', 'employees', 'cloudLogs', 'cloudError'));
    }

    /**
     * Sync local attendance logs to cloud database (Admin/Superadmin).
     */
    public function syncToCloud(Request $request)
    {
        if (!auth()->user()?->hasRole('Admin|Superadmin')) {
            abort(403);
        }

        $query = AttendanceLog::query();
        $this->applyAttendanceScope($query);

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('log_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('log_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $inserted = 0;
        $skipped = 0;

        $query->orderBy('id')
            ->chunkById(500, function ($logs) use (&$inserted, &$skipped) {
                $signatures = $logs->map(fn ($log) => $this->cloudSignature($log))->values()->all();

                $existing = DB::connection('cloud')
                    ->table('attendance_logs')
                    ->select(DB::raw("CONCAT_WS('|', log_datetime, badge_number, device_id, status, punch_type) as sig"))
                    ->whereIn(DB::raw("CONCAT_WS('|', log_datetime, badge_number, device_id, status, punch_type)"), $signatures)
                    ->pluck('sig')
                    ->all();

                $existingSet = array_fill_keys($existing, true);
                $rows = [];

                foreach ($logs as $log) {
                    $sig = $this->cloudSignature($log);
                    if (isset($existingSet[$sig])) {
                        $skipped++;
                        continue;
                    }

                    $rows[] = [
                        'device_id' => $log->device_id,
                        'badge_number' => $log->badge_number,
                        'employee_id' => $log->employee_id,
                        'log_datetime' => $log->log_datetime?->format('Y-m-d H:i:s'),
                        'status' => $log->status,
                        'punch_type' => $log->punch_type,
                        'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
                        'updated_at' => $log->updated_at?->format('Y-m-d H:i:s'),
                    ];
                }

                if (!empty($rows)) {
                    DB::connection('cloud')->table('attendance_logs')->insert($rows);
                    $inserted += count($rows);
                }
            });

        return back()->with('success', "Cloud sync complete. Inserted {$inserted} new logs, skipped {$skipped} existing logs.");
    }

    private function cloudSignature(AttendanceLog $log): string
    {
        $logDateTime = $log->log_datetime?->format('Y-m-d H:i:s') ?? '';

        return implode('|', [
            $logDateTime,
            $log->badge_number,
            $log->device_id,
            $log->status,
            $log->punch_type,
        ]);
    }

    /**
     * Show manual attendance log form (superadmin only)
     */
    public function create()
    {
        $employees = Employee::orderBy('last_name')->orderBy('first_name')->get();
        $devices = Device::orderBy('name')->get();

        return view('attendance-logs.create', compact('employees', 'devices'));
    }

    /**
     * Store manual attendance log (superadmin only)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['nullable', 'exists:employees,id'],
            'badge_number' => ['nullable', 'string', 'max:50'],
            'device_id' => ['nullable', 'exists:devices,id'],
            'log_date' => ['required', 'date'],
            'log_time' => ['required', 'date_format:H:i'],
            'status' => ['required', 'in:In,Out'],
            'punch_type' => ['nullable', 'string', 'max:50'],
        ]);

        if (!$data['employee_id'] && empty($data['badge_number'])) {
            return back()->withErrors(['badge_number' => 'Badge number is required when employee is not selected.'])->withInput();
        }

        $badgeNumber = $data['badge_number'];
        if ($data['employee_id']) {
            $employee = Employee::find($data['employee_id']);
            $badgeNumber = $employee?->badge_number;
        }

        $logDateTime = Carbon::createFromFormat('Y-m-d H:i', $data['log_date'] . ' ' . $data['log_time']);

        AttendanceLog::create([
            'device_id' => $data['device_id'],
            'badge_number' => $badgeNumber,
            'employee_id' => $data['employee_id'],
            'log_datetime' => $logDateTime,
            'status' => $data['status'],
            'punch_type' => $data['punch_type'] ?? 'Manual',
        ]);

        return redirect()->route('attendance-logs.index')->with('success', 'Manual attendance log created.');
    }

    /**
     * Show employee attendance records
     */
    public function byEmployee(Employee $employee, Request $request)
    {
        $this->authorizeEmployeeAccess($employee);
        $query = $employee->attendanceLogs()->with('device');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('log_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('log_date', '<=', $request->end_date);
        }

        // Filter by device
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        $printLogs = (clone $query)->orderBy('log_datetime', 'asc')->get();
        $logs = $query->orderBy('log_datetime', 'desc')->paginate(10);
        $devices = Device::orderBy('name')->get();

        return view('attendance-logs.by-employee', compact('employee', 'logs', 'devices', 'printLogs'));
    }

    /**
     * Show device attendance records
     */
    public function byDevice(Device $device, Request $request)
    {
        $query = $device->logs()->with('employee');

        $this->applyAttendanceScope($query);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('log_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('log_date', '<=', $request->end_date);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $logs = $query->orderBy('log_datetime', 'desc')->paginate(10);
        $employees = $this->scopedEmployeesQuery()->orderBy('last_name')->get();

        return view('attendance-logs.by-device', compact('device', 'logs', 'employees'));
    }

    /**
     * Show a single attendance log
     */
    public function show(AttendanceLog $log)
    {
        $log->load(['device', 'employee']);
        if ($log->employee) {
            $this->authorizeEmployeeAccess($log->employee);
        }
        return view('attendance-logs.show', compact('log'));
    }

    /**
     * Display daily time record
     */
    public function dailyTimeRecord(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $period = $request->input('period', 'whole');
        $department_id = $request->input('department_id', null);
        $employee_id = $request->input('employee_id');
        $user = $request->user();
        $isDtrIncharge = $user?->hasRole('DTR Incharge');
        $userEmployee = $user?->employee;

        $monthYear = Carbon::createFromFormat('Y-m', $month);
        
        // Determine date range based on period
        if ($period === '1-15') {
            $startDate = $monthYear->copy()->startOfMonth();
            $endDate = $monthYear->copy()->day(15);
        } elseif ($period === '16-31') {
            $startDate = $monthYear->copy()->day(16);
            $endDate = $monthYear->copy()->endOfMonth();
        } else {
            // whole month
            $startDate = $monthYear->copy()->startOfMonth();
            $endDate = $monthYear->copy()->endOfMonth();
        }

        // Get all departments
        $departments = $this->scopedDepartmentsQuery()->orderBy('name')->get();

        if ($isDtrIncharge && $userEmployee?->department_id) {
            $department_id = $userEmployee->department_id;
        }

        // Get employees based on filter
        $query = $this->scopedEmployeesQuery()->with(['department', 'position']);
        if ($department_id) {
            $query->where('department_id', $department_id);
        }
        $employees = $query->orderBy('last_name')->get();
        $showEmployeeFilter = $isDtrIncharge && $employees->count() > 1;

        return view('attendance-logs.daily-time-record', compact(
            'month',
            'period',
            'monthYear',
            'startDate',
            'endDate',
            'departments',
            'department_id',
            'employees',
            'employee_id',
            'showEmployeeFilter'
        ));
    }

    /**
     * Generate Form 48 Daily Time Record for printing
     */
    public function printForm48(Employee $employee, Request $request)
    {
        $this->authorizeEmployeeAccess($employee);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            return back()->with('error', 'Date range is required');
        }

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $data = $this->buildForm48Data($employee, $startDate, $endDate);

        return view('attendance-logs.form-48', $data);
    }

    /**
     * Download Form 48 as PDF
     */
    public function downloadForm48PDF(Employee $employee, Request $request)
    {
        $this->authorizeEmployeeAccess($employee);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            return back()->with('error', 'Date range is required');
        }

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $data = $this->buildForm48Data($employee, $startDate, $endDate);

        // Render the PDF
        $pdf = Pdf::loadView('attendance-logs.form-48-pdf', $data);
        
        // Generate filename
        $fileName = sprintf(
            'Form48_%s_%s_to_%s.pdf',
            str_replace(' ', '_', $employee->getFullName()),
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        return $pdf->download($fileName);
    }

    /**
     * Build Form 48 data shared by print and PDF views
     */
    protected function buildForm48Data(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        // Get attendance logs for the period
        $logs = $employee->attendanceLogs()
            ->with('device')
            ->whereBetween('log_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('log_datetime', 'asc')
            ->get();

        // Group logs by date for easier processing
        $logsByDate = $logs->groupBy('log_date');

        // Get employee group report settings
        $employeeGroup = $employee->employeeGroup;
        $groupId = $employeeGroup?->id;
        
        // Get report settings for the employee's group (24-hour format from DB)
        $officialArrivalTime = ReportSetting::get('official_arrival', $groupId, '08:00');
        $officialDepartureTime = ReportSetting::get('official_departure', $groupId, '17:00');
        
        // Convert 24-hour format to 12-hour format for display
        $officialArrival = Carbon::createFromFormat('H:i', $officialArrivalTime)->format('g:i');
        $officialDeparture = Carbon::createFromFormat('H:i', $officialDepartureTime)->format('g:i');
        
        // Calculate working days and total hours
        $workingDays = 0;
        $regularDays = 0;
        $saturdays = 0;
        $totalHours = 0;
        $dailyDetails = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->toDateString();
            $dayOfWeek = $date->dayName;
            
            $isSunday = $date->dayOfWeek === 0;

            if (!$isSunday) {
                $workingDays++;
                if ($date->dayOfWeek === 6) {
                    $saturdays++;
                } else {
                    $regularDays++;
                }
            }
            $dayLogs = $logsByDate->get($dateStr, collect());

            if ($dayLogs->count() > 0) {
                $firstLogTime = Carbon::parse($dayLogs->first()->log_datetime)->setDate($date->year, $date->month, $date->day);
                $lastLogTime = Carbon::parse($dayLogs->last()->log_datetime)->setDate($date->year, $date->month, $date->day);
                $firstLogTime = Carbon::parse($dayLogs->first()->log_datetime)->setDate($date->year, $date->month, $date->day);
                $lastLogTime = Carbon::parse($dayLogs->last()->log_datetime)->setDate($date->year, $date->month, $date->day);
                $firstLogTime = Carbon::parse($dayLogs->first()->log_datetime)->setDate($date->year, $date->month, $date->day);
                $lastLogTime = Carbon::parse($dayLogs->last()->log_datetime)->setDate($date->year, $date->month, $date->day);
                // Get AM/PM boundary times from report settings
                $amArrivalStart = ReportSetting::get('am_arrival_start', $groupId, '08:00');
                $amArrivalEnd = ReportSetting::get('am_arrival_end', $groupId, '08:00');
                $amDepartureStart = ReportSetting::get('am_departure_start', $groupId, '12:00');
                $amDepartureEnd = ReportSetting::get('am_departure_end', $groupId, '12:00');
                $pmArrivalStart = ReportSetting::get('pm_arrival_start', $groupId, '13:00');
                $pmArrivalEnd = ReportSetting::get('pm_arrival_end', $groupId, '14:00');
                $pmDepartureStart = ReportSetting::get('pm_departure_start', $groupId, '17:00');
                
                // Helper function to parse time in multiple formats
                $parseTime = function($time) {
                    try {
                        // Try 24-hour format first (H:i)
                        return Carbon::createFromFormat('H:i', $time);
                    } catch (\Exception $e) {
                        try {
                            // Try 12-hour format with AM/PM (g:i A)
                            return Carbon::createFromFormat('g:i A', $time);
                        } catch (\Exception $e2) {
                            // Fallback to parsing whatever format it is
                            return Carbon::parse($time);
                        }
                    }
                };
                
                // Convert to Carbon for comparison
                $amArrStart = $parseTime($amArrivalStart);
                $amArrEnd = $parseTime($amArrivalEnd);
                $amDeptStart = $parseTime($amDepartureStart);
                $amDeptEnd = $parseTime($amDepartureEnd);
                $pmArrStart = $parseTime($pmArrivalStart);
                $pmArrEnd = $parseTime($pmArrivalEnd);
                $pmDeptStart = $parseTime($pmDepartureStart);

                $toMinutes = function($time) {
                    return ($time->hour * 60) + $time->minute;
                };

                $amArrStartMin = $toMinutes($amArrStart);
                $amArrEndMin = $toMinutes($amArrEnd);
                $amDeptStartMin = $toMinutes($amDeptStart);
                $amDeptEndMin = $toMinutes($amDeptEnd);
                $pmArrStartMin = $toMinutes($pmArrStart);
                $pmArrEndMin = $toMinutes($pmArrEnd);
                $pmDeptStartMin = $toMinutes($pmDeptStart);
                
                $amArrival = null;
                $amDeparture = null;
                $pmArrival = null;
                $pmDeparture = null;
                
                $classifiedLogs = []; // Track which logs have been classified
                $earliestBeforeAmDeptStart = null; // Fallback candidate for AM arrival
                
                // Find logs in each period based on report settings time windows
                foreach ($dayLogs as $log) {
                    $logTime = Carbon::parse($log->log_datetime)->setDate($date->year, $date->month, $date->day);
                    $logTimeOnly = Carbon::createFromFormat('H:i', $logTime->format('H:i'));
                    $logId = $log->id;
                    $logMinutes = ($logTimeOnly->hour * 60) + $logTimeOnly->minute;

                    if ($earliestBeforeAmDeptStart === null && $logMinutes < $amDeptStartMin) {
                        $earliestBeforeAmDeptStart = Carbon::parse($log->log_datetime)->format('g:i');
                    }
                    
                    // AM Arrival: within AM arrival window (first occurrence)
                    if ($logMinutes >= $amArrStartMin && $logMinutes <= $amArrEndMin) {
                        if (!$amArrival && !in_array($logId, $classifiedLogs)) {
                            $amArrival = Carbon::parse($log->log_datetime)->format('g:i');
                            $classifiedLogs[] = $logId;
                        }
                    }
                    
                    // AM Departure: within AM departure window (first occurrence, exclude already classified)
                    if (($logMinutes >= $amDeptStartMin) && ($logMinutes <= $amDeptEndMin)) {
                        if (!$amDeparture && !in_array($logId, $classifiedLogs)) {
                            $amDeparture = Carbon::parse($log->log_datetime)->format('g:i');
                            $classifiedLogs[] = $logId;
                        }
                    }
                    
                    // PM Arrival: within PM arrival window (first occurrence, exclude already classified)
                    if (($logMinutes >= $pmArrStartMin) && ($logMinutes <= $pmArrEndMin)) {
                        if (!$pmArrival && !in_array($logId, $classifiedLogs)) {
                            $pmArrival = Carbon::parse($log->log_datetime)->format('g:i');
                            $classifiedLogs[] = $logId;
                        }
                    }
                    
                    // PM Departure: at or after PM departure start (last occurrence, can reuse logs)
                    if ($logMinutes >= $pmDeptStartMin) {
                        $pmDeparture = Carbon::parse($log->log_datetime)->format('g:i');
                    }
                }

                // Fallback: if AM arrival not found in window, use earliest log before AM departure start
                if (!$amArrival && $earliestBeforeAmDeptStart) {
                    $amArrival = $earliestBeforeAmDeptStart;
                }
                
                // Calculate total hours worked (first log to last log)
                $firstLog = $dayLogs->first();
                $lastLog = $dayLogs->last();
                $hoursWorked = Carbon::parse($firstLog->log_datetime)->diffInHours(Carbon::parse($lastLog->log_datetime));
                $totalHours += $hoursWorked;

                $dailyDetails[$dateStr] = [
                    'date' => $date->format('m/d/Y'),
                    'day' => $date->day,
                    'day_of_week' => $date->dayOfWeek, // 0=Sunday, 6=Saturday
                    'am_arrival' => $amArrival,
                    'am_departure' => $amDeparture,
                    'pm_arrival' => $pmArrival,
                    'pm_departure' => $pmDeparture,
                    'hours' => $hoursWorked,
                    'remarks' => ''
                ];
            } else {
                $dailyDetails[$dateStr] = [
                    'date' => $date->format('m/d/Y'),
                    'day' => $date->day,
                    'day_of_week' => $date->dayOfWeek, // 0=Sunday, 6=Saturday
                    'am_arrival' => null,
                    'am_departure' => null,
                    'pm_arrival' => null,
                    'pm_departure' => null,
                    'hours' => 0,
                    'remarks' => $isSunday ? 'SUNDAY' : 'ABSENT'
                ];
            }
        }

        return [
            'employee' => $employee,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dailyDetails' => $dailyDetails,
            'workingDays' => $workingDays,
            'regularDays' => $regularDays,
            'saturdays' => $saturdays,
            'officialArrival' => $officialArrival,
            'officialDeparture' => $officialDeparture,
            'totalHours' => $totalHours,
        ];
    }

    /**
    {
        $month = $request->input('month', now()->format('Y-m'));
        $period = $request->input('period', 'whole'); // whole, 1-15, 16-31
        $department_id = $request->input('department_id');

        $monthYear = Carbon::createFromFormat('Y-m', $month);
        
        // Get departments for filter
        $departments = \App\Models\Department::orderBy('name')->get();

        $user = $request->user();
        $userEmployee = $user?->employee;
        $isDtrIncharge = $user?->hasRole('DTR Incharge');

        if ($isDtrIncharge && $userEmployee?->department_id) {
            $department_id = $userEmployee->department_id;
        }

        // Get employees based on department filter
        $query = Employee::with('department');
        if ($department_id) {
            $query->where('department_id', $department_id);
        }
        if ($employee_id) {
            $query->where('id', $employee_id);
        }
        $employees = $query->orderBy('last_name', 'asc')->get();

        // Calculate date range based on period
        if ($period === '1-15') {
            $startDate = $monthYear->copy()->startOfMonth();
            $endDate = $monthYear->copy()->setDay(15);
        } elseif ($period === '16-31') {
            $startDate = $monthYear->copy()->setDay(16);
            $endDate = $monthYear->copy()->endOfMonth();
        } else {
            // whole month
            $startDate = $monthYear->copy()->startOfMonth();
            $endDate = $monthYear->copy()->endOfMonth();
        }

        return view('attendance-logs.daily-time-record', compact(
            'month',
            'period',
            'monthYear',
            'startDate',
            'endDate',
            'departments',
            'department_id',
            'employees',
            'employee_id'
        ));
    }

    /**
    {
        $date = $request->input('date', today());

        $logs = AttendanceLog::query()
            ->with(['device', 'employee'])
            ->whereDate('log_date', $date)
            ->orderBy('log_time', 'desc')
            ->paginate(100);

        // Statistics
        $totalLogs = AttendanceLog::whereDate('log_date', $date)->count();
        $checkIns = AttendanceLog::whereDate('log_date', $date)->where('status', 'In')->count();
        $checkOuts = AttendanceLog::whereDate('log_date', $date)->where('status', 'Out')->count();
        $devices = Device::orderBy('name')->get();

        return view('attendance-logs.daily-summary', compact(
            'logs',
            'date',
            'totalLogs',
            'checkIns',
            'checkOuts',
            'devices'
        ));
    }

    /**
     * Export attendance logs to CSV
     */
    public function export(Request $request)
    {
        $query = AttendanceLog::query()->with(['device', 'employee']);

        // Apply filters
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('log_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('log_date', '<=', $request->end_date);
        }

        $logs = $query->orderBy('log_datetime', 'desc')->get();

        // Generate CSV
        $fileName = 'attendance-logs-' . now()->format('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Date',
                'Time',
                'Employee',
                'Badge Number',
                'Device',
                'Status',
                'Punch Type',
            ]);

            // Data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->log_date->format('Y-m-d'),
                    $log->log_time,
                    $log->employee ? $log->employee->getFullName() : 'N/A',
                    $log->badge_number,
                    $log->device->name,
                    $log->status,
                    $log->punch_type,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete a log (admin only)
     */
    public function destroy(AttendanceLog $log)
    {
        $log->delete();
        return redirect()->route('attendance-logs.index')->with('success', 'Attendance log deleted successfully');
    }

    /**
     * Get live feed of attendance logs (for real-time monitor)
     * Now includes protocol information and device status
     */
    public function liveFeed(Request $request)
    {
        try {
            $syncPerformed = false;
            $syncMessage = null;
            $windowSeconds = (int) $request->input('window_seconds', 300);
            $windowSeconds = max(10, min(86400, $windowSeconds));
            $windowStart = now()->subSeconds($windowSeconds);
            
            // If device_id is specified and sync is requested, sync fresh logs from that device first
            if ($request->filled('device_id') && $request->boolean('sync', false)) {
                $deviceId = $request->device_id;
                $device = Device::find($deviceId);
                
                if ($device && $device->is_active) {
                    $syncCooldownSeconds = 30;
                    $syncKey = "live_feed_sync_{$deviceId}";

                    if (Cache::has($syncKey)) {
                        $syncPerformed = false;
                        $syncMessage = 'Sync throttled';
                    } else {
                    try {
                        // Choose sync mode: use request param, else device preference, else 'zk'
                        $mode = $request->input('sync_mode', $device->live_sync_mode ?? 'zk');
                        $todayStart = now()->startOfDay()->format('Y-m-d');
                        $todayEnd = now()->format('Y-m-d');

                        // Quick sync of today's logs from the device
                        if ($mode === 'auto') {
                            $syncService = new \App\Services\AttendanceSyncService($device);
                            $result = $syncService->downloadAttendanceRealtime($todayStart, $todayEnd);
                            \Log::info("Live feed sync (AUTO) for device {$deviceId}: " . ($result['logs_count'] ?? 0) . " logs");
                        } else {
                            $zkService = new \App\Services\ZKTecoService($device);
                            $result = $zkService->downloadAttendanceRealtime($todayStart, $todayEnd, $device->id);
                            \Log::info("Live feed sync (ZKEM) for device {$deviceId}: " . ($result['logs_count'] ?? 0) . " logs");
                        }
                        $syncPerformed = true;
                        $syncMessage = $result['message'] ?? 'Synced';
                        Cache::put($syncKey, true, $syncCooldownSeconds);
                    } catch (\Exception $e) {
                        // Silently continue if sync fails, just show existing logs
                        $syncMessage = 'Sync failed: ' . $e->getMessage();
                        \Log::warning("Live feed sync failed for device {$deviceId}: " . $e->getMessage());
                    }
                    }
                }
            }
            
            $query = AttendanceLog::query()
                ->with(['device', 'employee']);

            // Filter by device if specified
            if ($request->filled('device_id')) {
                $query->where('device_id', $request->device_id);
            }

            $logs = $query
                ->orderBy('log_datetime', 'desc')
                ->limit(10) // Limit to last 10 logs for live feed
                ->get();

            // Get device protocol information and status (cached per request)
            $deviceInfo = Cache::remember('live_monitor_device_info', 5, function () {
                $info = [];
                $allDevices = Device::where('is_active', true)
                    ->get()
                    ->keyBy('id');

                foreach ($allDevices as $device) {
                    $protocolManager = new \App\Services\DeviceProtocolManager($device);
                    $protocol = $protocolManager->detectProtocol();

                    // Quick status check with timeout
                    $connectionStatus = 'unknown';
                    $isConnected = false;

                    try {
                        // Just check socket, skip ping for speed
                        $socket = @fsockopen(
                            $device->ip_address,
                            $device->port,
                            $errno,
                            $errstr,
                            1  // 1 second timeout
                        );

                        if ($socket !== false) {
                            fclose($socket);
                            $connectionStatus = 'online_protocol_ok';
                            $isConnected = true;
                        } else {
                            $connectionStatus = 'offline';
                        }
                    } catch (\Exception $e) {
                        // Timeout or error - mark as offline
                        $connectionStatus = 'offline';
                    }

                    $info[$device->id] = [
                        'protocol' => $protocol,
                        'name' => $device->name,
                        'status' => $connectionStatus,
                        'is_connected' => $isConnected,
                        'ip_address' => $device->ip_address,
                        'port' => $device->port,
                        'last_sync' => now()->setTimezone(config('app.user_timezone', 'UTC'))->format('Y-m-d H:i:s'),
                    ];
                }

                return $info;
            });

            // Format logs for real-time display
            // Get user's timezone from session, config, or detect from browser
            $userTimezone = config('app.user_timezone', config('app.timezone', 'UTC'));
            
            $formattedLogs = $logs->map(function ($log) use ($deviceInfo, $userTimezone) {
                $info = $deviceInfo[$log->device_id] ?? [
                    'protocol' => 'unknown',
                    'name' => $log->device->name ?? 'Unknown',
                    'status' => 'unknown',
                    'is_connected' => false,
                    'ip_address' => null,
                    'port' => null,
                ];
                
                // Preserve stored local time but label it with user timezone for display
                $logDateTime = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $log->log_datetime->format('Y-m-d H:i:s'),
                    $userTimezone
                );
                
                return [
                    'id' => $log->id,
                    'badge_number' => $log->badge_number,
                    'device_id' => $log->device_id,
                    'device_name' => $info['name'],
                    'device_protocol' => $info['protocol'],
                    'device_status' => $info['status'],
                    'device_is_connected' => $info['is_connected'],
                    'log_datetime' => $logDateTime->format('Y-m-d H:i:s'),
                    'log_timestamp' => $logDateTime->timestamp,
                    'user_timezone' => $userTimezone,
                    'status' => $log->status,
                    'punch_type' => $log->punch_type,
                    'employee_name' => $log->employee?->getFullName() ?? 'Not Linked',
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'logs' => $formattedLogs,
                'total' => count($formattedLogs),
                'devices' => $deviceInfo,
                'sync_performed' => $syncPerformed,
                'sync_message' => $syncMessage,
                'sync_mode' => $request->input('sync_mode', 'zk'),
                'window_seconds' => $windowSeconds,
                'window_start' => $windowStart->toIso8601String(),
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            // Return error but with whatever logs we have
            \Log::error('LiveFeed error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading logs: ' . $e->getMessage(),
                'logs' => [],
                'total' => 0,
                'devices' => [],
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Display live attendance monitor
     */
    public function liveMonitor()
    {
        $devices = Device::where('is_active', true)->orderBy('name')->get();
        return view('attendance-logs.live-monitor', compact('devices'));
    }

    /**
     * Sync attendance logs from device
     * Includes connection verification before syncing
     */
    public function syncFromDevice(Request $request, Device $device)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            // Step 1: Check if device is reachable
            $socket = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 3);
            $device_reachable = ($socket !== false);
            if ($socket) {
                fclose($socket);
            }

            if (!$device_reachable) {
                return response()->json([
                    'success' => false,
                    'step' => 'connectivity_check',
                    'message' => "Device '{$device->name}' is offline or unreachable at {$device->ip_address}:{$device->port}",
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'device_ip' => $device->ip_address,
                    'device_port' => $device->port,
                ], 400);
            }

            // Step 2: Try to establish protocol connection
            try {
                $protocolManager = new \App\Services\DeviceProtocolManager($device);
                $connection = $protocolManager->connect();

                if (!$connection['success']) {
                    return response()->json([
                        'success' => false,
                        'step' => 'protocol_connection',
                        'message' => "Failed to connect to device via {$connection['protocol']} protocol. Device may require different protocol or configuration.",
                        'device_id' => $device->id,
                        'device_name' => $device->name,
                        'protocol_attempted' => $connection['protocol'] ?? 'unknown',
                        'device_ip' => $device->ip_address,
                        'device_port' => $device->port,
                    ], 400);
                }

                // Step 3: Sync attendance logs using protocol manager
                $syncService = new \App\Services\AttendanceSyncService($device);
                $syncResult = $syncService->downloadAttendanceRealtime(
                    $validated['start_date'],
                    $validated['end_date']
                );

                $protocolManager->disconnect();

                // Return success response
                return response()->json([
                    'success' => $syncResult['success'],
                    'step' => 'attendance_sync',
                    'message' => $syncResult['message'],
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'protocol_used' => $syncResult['protocol'] ?? $connection['protocol'],
                    'logs_count' => $syncResult['logs_count'] ?? 0,
                    'logs_skipped' => $syncResult['logs_skipped'] ?? 0,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'timestamp' => now()->toIso8601String(),
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'step' => 'protocol_connection',
                    'message' => 'Error establishing protocol connection: ' . $e->getMessage(),
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'device_ip' => $device->ip_address,
                    'device_port' => $device->port,
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'step' => 'validation',
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save imported attendance logs from USB/CSV import
     * Simple direct save endpoint
     */
    public function saveImported(Request $request)
    {
        try {
            $logs = $request->input('logs', []);
            
            if (empty($logs)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No logs to save',
                    'saved' => 0,
                    'skipped' => 0
                ], 422);
            }

            $saved = 0;
            $skipped = 0;
            $errors = [];

            foreach ($logs as $log) {
                try {
                    // Validate required fields
                    if (empty($log['badge']) || empty($log['logged_at']) || empty($log['device_id'])) {
                        $skipped++;
                        $errors[] = 'Missing required fields: ' . json_encode($log);
                        continue;
                    }

                    // Parse the logged_at timestamp
                    $loggedAt = Carbon::createFromFormat('Y-m-d H:i:s', $log['logged_at']);
                    
                    // Create or update the attendance log
                    $result = AttendanceLog::firstOrCreate(
                        [
                            'device_id' => $log['device_id'],
                            'badge_number' => $log['badge'],
                            'log_datetime' => $loggedAt
                        ],
                        [
                            'status' => 'IN',
                            'punch_type' => 0
                        ]
                    );

                    if ($result->wasRecentlyCreated) {
                        $saved++;
                    } else {
                        $skipped++;
                    }

                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = 'Log entry error: ' . $e->getMessage();
                    \Log::warning('[API] Save log error', ['log' => $log, 'error' => $e->getMessage()]);
                }
            }

            \Log::info('[API] saveImported completed', [
                'total' => count($logs),
                'saved' => $saved,
                'skipped' => $skipped,
                'errors' => count($errors) > 0 ? array_slice($errors, 0, 3) : []
            ]);

            return response()->json([
                'success' => true,
                'message' => "Saved $saved logs, skipped $skipped",
                'saved' => $saved,
                'skipped' => $skipped,
                'total' => count($logs)
            ], 200);

        } catch (\Exception $e) {
            \Log::error('[API] saveImported error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'saved' => 0,
                'skipped' => 0
            ], 500);
        }
    }

    /**
     * Show final form with holidays and activities
     */
    public function showFinalForm(Employee $employee, Request $request)
    {
        $this->authorizeEmployeeAccess($employee);
        $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date);
        $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date);
        $data = $this->buildFinalFormData($employee, $startDate, $endDate);

        return view('attendance-logs.final-form', $data);
    }

    /**
     * Download final form as PDF
     */
    public function downloadFinalFormPDF(Employee $employee, Request $request)
    {
        $this->authorizeEmployeeAccess($employee);
        $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date);
        $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date);

        // Get logs
        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->whereBetween('log_date', [$startDate, $endDate])
            ->orderBy('log_datetime')
            ->get();

        // Get holidays
        $holidays = \App\Models\Holiday::whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy('date');

        // Get activities
        $activities = $employee->activities()
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy('date');

        // Get all dates
        $dates = collect();
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dates->push($date->copy());
        }

        $pdf = Pdf::loadView('attendance-logs.final-form-pdf', compact(
            'employee',
            'startDate',
            'endDate',
            'logs',
            'holidays',
            'activities',
            'dates'
        ));

        return $pdf->download('final-dtr-' . $employee->badge_number . '-' . $startDate->format('Y-m-d') . '.pdf');
    }

    /**
     * Download final form as Word document
     */
    public function downloadFinalFormWord(Employee $employee, Request $request)
    {
        $this->authorizeEmployeeAccess($employee);
        $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date);
        $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date);

        $data = $this->buildFinalFormData($employee, $startDate, $endDate);
        $html = view('attendance-logs.final-form-word', $data)->render();

        $fileName = sprintf(
            'final-dtr-%s-%s.doc',
            $employee->badge_number,
            $startDate->format('Y-m-d')
        );

        return response($html)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Download Individual Daily Log and Accomplishment Report as Word document
     */
    public function downloadFinalAccReportWord(Employee $employee, Request $request)
    {
        $this->authorizeEmployeeAccess($employee);
        $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date);
        $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date);

        $data = $this->buildFinalFormData($employee, $startDate, $endDate);
        $html = view('attendance-logs.acc-report-word', $data)->render();

        $fileName = sprintf(
            'acc-report-%s-%s.doc',
            $employee->badge_number,
            $startDate->format('Y-m-d')
        );

        return response($html)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    private function buildFinalFormData(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        // Use the same query pattern as Form 48
        $logs = $employee->attendanceLogs()
            ->with('device')
            ->whereBetween('log_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('log_datetime', 'asc')
            ->get();

        // Group logs by date
        $logsByDate = $logs->groupBy('log_date');

        // Get employee group report settings (same as Form 48)
        $employeeGroup = $employee->employeeGroup;
        $groupId = $employeeGroup?->id;

        $amArrivalStartSetting = ReportSetting::get('am_arrival_start', $groupId, '08:00');
        $amArrivalEndSetting = ReportSetting::get('am_arrival_end', $groupId, '08:00');
        $amDepartureStartSetting = ReportSetting::get('am_departure_start', $groupId, '12:00');
        $amDepartureEndSetting = ReportSetting::get('am_departure_end', $groupId, '12:00');
        $pmArrivalStartSetting = ReportSetting::get('pm_arrival_start', $groupId, '13:00');
        $pmArrivalEndSetting = ReportSetting::get('pm_arrival_end', $groupId, '14:00');
        $pmDepartureStartSetting = ReportSetting::get('pm_departure_start', $groupId, '17:00');
        $pmDepartureEndSetting = ReportSetting::get('pm_departure_end', $groupId, '17:00');

        $parseTimeSetting = function ($time) {
            try {
                return Carbon::createFromFormat('H:i', $time);
            } catch (\Exception $e) {
                try {
                    return Carbon::createFromFormat('g:i A', $time);
                } catch (\Exception $e2) {
                    return Carbon::parse($time);
                }
            }
        };

        $toMinutesSetting = function ($time) {
            return ($time->hour * 60) + $time->minute;
        };

        $amArrStartMinSetting = $toMinutesSetting($parseTimeSetting($amArrivalStartSetting));
        $amArrEndMinSetting = $toMinutesSetting($parseTimeSetting($amArrivalEndSetting));
        $amDeptStartMinSetting = $toMinutesSetting($parseTimeSetting($amDepartureStartSetting));
        $amDeptEndMinSetting = $toMinutesSetting($parseTimeSetting($amDepartureEndSetting));
        $pmArrStartMinSetting = $toMinutesSetting($parseTimeSetting($pmArrivalStartSetting));
        $pmArrEndMinSetting = $toMinutesSetting($parseTimeSetting($pmArrivalEndSetting));
        $pmDeptStartMinSetting = $toMinutesSetting($parseTimeSetting($pmDepartureStartSetting));
        $pmDeptEndMinSetting = $toMinutesSetting($parseTimeSetting($pmDepartureEndSetting));

        $timeWindows = [
            'am_arrival_start' => $amArrStartMinSetting,
            'am_arrival_end' => $amArrEndMinSetting,
            'am_departure_start' => $amDeptStartMinSetting,
            'am_departure_end' => $amDeptEndMinSetting,
            'pm_arrival_start' => $pmArrStartMinSetting,
            'pm_arrival_end' => $pmArrEndMinSetting,
            'pm_departure_start' => $pmDeptStartMinSetting,
            'pm_departure_end' => $pmDeptEndMinSetting,
        ];

        $officialArrivalTime = ReportSetting::get('official_arrival', $groupId, '08:00');
        $officialDepartureTime = ReportSetting::get('official_departure', $groupId, '17:00');
        $officialArrival = Carbon::createFromFormat('H:i', $officialArrivalTime)->format('g:i');
        $officialDeparture = Carbon::createFromFormat('H:i', $officialDepartureTime)->format('g:i');

        $regularDays = 0;
        $saturdays = 0;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->dayOfWeek === 0) {
                continue;
            }
            if ($date->dayOfWeek === 6) {
                $saturdays++;
            } else {
                $regularDays++;
            }
        }

        // Get holidays
        $holidays = \App\Models\Holiday::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(function($h) {
                return $h->date->format('Y-m-d');
            });

        // Get activities - employee specific
        $activities = $employee->activities()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orWhere(function($query) use ($startDate, $endDate, $employee) {
                $query->where('employee_id', $employee->id)
                    ->whereNotNull('end_date')
                    ->where('date', '<=', $endDate->toDateString())
                    ->where('end_date', '>=', $startDate->toDateString());
            })
            ->get();

        // Get leave requests for the period
        $leaves = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('start_date', '<=', $endDate->toDateString())
                    ->whereDate('end_date', '>=', $startDate->toDateString());
            })
            ->get();

        // Map activities to all dates they cover
        $activitiesByDate = [];
        foreach ($activities as $activity) {
            $activityStart = $activity->date;
            $activityEnd = $activity->end_date ?? $activity->date;

            for ($d = $activityStart->copy(); $d->lte($activityEnd); $d->addDay()) {
                $dateKey = $d->format('Y-m-d');
                if (!isset($activitiesByDate[$dateKey])) {
                    $activitiesByDate[$dateKey] = [];
                }
                $activitiesByDate[$dateKey][] = $activity;
            }
        }

        // Map leaves to all dates they cover
        $leavesByDate = [];
        foreach ($leaves as $leave) {
            $leaveStart = $leave->start_date;
            $leaveEnd = $leave->end_date ?? $leave->start_date;

            for ($d = $leaveStart->copy(); $d->lte($leaveEnd); $d->addDay()) {
                $dateKey = $d->format('Y-m-d');
                if (!isset($leavesByDate[$dateKey])) {
                    $leavesByDate[$dateKey] = [];
                }
                $leavesByDate[$dateKey][] = $leave;
            }
        }

        // Build daily details with proper time extraction (same logic as Form 48)
        $dailyDetails = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->toDateString();

            // Get logs for this date
            $dayLogs = $logsByDate->get($dateStr, collect());

            if ($dayLogs->count() > 0) {
                $firstLogTime = null;
                $lastLogTime = null;

                // Get AM/PM boundary times from report settings (same as Form 48)
                $amArrivalStart = $amArrivalStartSetting;
                $amArrivalEnd = $amArrivalEndSetting;
                $amDepartureStart = $amDepartureStartSetting;
                $amDepartureEnd = $amDepartureEndSetting;
                $pmArrivalStart = $pmArrivalStartSetting;
                $pmArrivalEnd = $pmArrivalEndSetting;
                $pmDepartureStart = $pmDepartureStartSetting;

                // Helper function to parse time
                $parseTime = function($time) {
                    try {
                        return Carbon::createFromFormat('H:i', $time);
                    } catch (\Exception $e) {
                        try {
                            return Carbon::createFromFormat('g:i A', $time);
                        } catch (\Exception $e2) {
                            return Carbon::parse($time);
                        }
                    }
                };

                // Convert to Carbon for comparison
                $amArrStart = $parseTime($amArrivalStart);
                $amArrEnd = $parseTime($amArrivalEnd);
                $amDeptStart = $parseTime($amDepartureStart);
                $amDeptEnd = $parseTime($amDepartureEnd);
                $pmArrStart = $parseTime($pmArrivalStart);
                $pmArrEnd = $parseTime($pmArrivalEnd);
                $pmDeptStart = $parseTime($pmDepartureStart);

                $toMinutes = function($time) {
                    return ($time->hour * 60) + $time->minute;
                };

                $amArrStartMin = $toMinutes($amArrStart);
                $amArrEndMin = $toMinutes($amArrEnd);
                $amDeptStartMin = $toMinutes($amDeptStart);
                $amDeptEndMin = $toMinutes($amDeptEnd);
                $pmArrStartMin = $toMinutes($pmArrStart);
                $pmArrEndMin = $toMinutes($pmArrEnd);
                $pmDeptStartMin = $toMinutes($pmDeptStart);

                $amArrival = null;
                $amDeparture = null;
                $pmArrival = null;
                $pmDeparture = null;
                $amArrivalMinutes = null;
                $amDepartureMinutes = null;
                $pmArrivalMinutes = null;
                $pmDepartureMinutes = null;

                $classifiedLogs = [];
                $earliestBeforeAmDeptStart = null;
                $earliestBeforeAmDeptStartMinutes = null;

                // Find logs in each period based on report settings time windows
                foreach ($dayLogs as $log) {
                    $logTime = Carbon::parse($log->log_datetime)
                        ->setDate($date->year, $date->month, $date->day)
                        ->startOfMinute();
                    $logTimeOnly = Carbon::createFromFormat('H:i', $logTime->format('H:i'));
                    $logId = $log->id;
                    $logMinutes = ($logTimeOnly->hour * 60) + $logTimeOnly->minute;

                    if ($earliestBeforeAmDeptStart === null && $logMinutes < $amDeptStartMin) {
                        $earliestBeforeAmDeptStart = Carbon::parse($log->log_datetime)->format('g:i');
                        $earliestBeforeAmDeptStartMinutes = $logMinutes;
                    }

                    if (!$firstLogTime || $logTime->lt($firstLogTime)) {
                        $firstLogTime = $logTime->copy();
                    }
                    if (!$lastLogTime || $logTime->gt($lastLogTime)) {
                        $lastLogTime = $logTime->copy();
                    }

                    // AM Arrival: within AM arrival window (first occurrence)
                    if ($logMinutes >= $amArrStartMin && $logMinutes <= $amArrEndMin) {
                        if (!$amArrival && !in_array($logId, $classifiedLogs)) {
                            $amArrival = Carbon::parse($log->log_datetime)->format('g:i');
                            $amArrivalMinutes = $logMinutes;
                            $classifiedLogs[] = $logId;
                        }
                    }

                    // AM Departure: within AM departure window (first occurrence)
                    if (($logMinutes >= $amDeptStartMin) && ($logMinutes <= $amDeptEndMin)) {
                        if (!$amDeparture && !in_array($logId, $classifiedLogs)) {
                            $amDeparture = Carbon::parse($log->log_datetime)->format('g:i');
                            $amDepartureMinutes = $logMinutes;
                            $classifiedLogs[] = $logId;
                        }
                    }

                    // PM Arrival: within PM arrival window (first occurrence)
                    if (($logMinutes >= $pmArrStartMin) && ($logMinutes <= $pmArrEndMin)) {
                        if (!$pmArrival && !in_array($logId, $classifiedLogs)) {
                            $pmArrival = Carbon::parse($log->log_datetime)->format('g:i');
                            $pmArrivalMinutes = $logMinutes;
                            $classifiedLogs[] = $logId;
                        }
                    }

                    // PM Departure: at or after PM departure start (last occurrence)
                    if ($logMinutes >= $pmDeptStartMin) {
                        $pmDeparture = Carbon::parse($log->log_datetime)->format('g:i');
                        $pmDepartureMinutes = $logMinutes;
                    }
                }

                if (!$amArrival && $earliestBeforeAmDeptStart) {
                    $amArrival = $earliestBeforeAmDeptStart;
                    $amArrivalMinutes = $earliestBeforeAmDeptStartMinutes;
                }

                if (!$firstLogTime) {
                    $firstLogTime = $date->copy()->startOfDay();
                }
                if (!$lastLogTime) {
                    $lastLogTime = $firstLogTime->copy();
                }

                $dailyDetails[$dateStr] = [
                    'am_arrival' => $amArrival,
                    'am_departure' => $amDeparture,
                    'pm_arrival' => $pmArrival,
                    'pm_departure' => $pmDeparture,
                    'am_arrival_minutes' => $amArrivalMinutes,
                    'am_departure_minutes' => $amDepartureMinutes,
                    'pm_arrival_minutes' => $pmArrivalMinutes,
                    'pm_departure_minutes' => $pmDepartureMinutes,
                    'late_hours' => null,
                    'late_minutes' => null,
                    'late_time_display' => '',
                    'late_total_minutes' => null,
                    'has_logs' => true,
                ];

                // Calculate late/undertime
                $groupName = strtolower($employee->employeeGroup?->name ?? '');
                $isNonTeaching = str_contains($groupName, 'non') && str_contains($groupName, 'teaching');
                $isTeaching = str_contains($groupName, 'teaching') && !$isNonTeaching;
                if (!$isTeaching && !$isNonTeaching) {
                    $isNonTeaching = true;
                }

                $officialMorningArrival = Carbon::createFromFormat('H:i', $amArrivalEndSetting);
                $officialAfternoonDeparture = Carbon::createFromFormat('H:i', $pmDepartureEndSetting);
                $officialMorningArrival->setDate($date->year, $date->month, $date->day);
                $officialAfternoonDeparture->setDate($date->year, $date->month, $date->day);

                $workedMinutes = 0;
                if ($isTeaching) {
                    if ($amArrival && $amDeparture) {
                        $amIn = Carbon::createFromFormat('g:i', $amArrival);
                        $amOut = Carbon::createFromFormat('g:i', $amDeparture);
                        if ($amOut->gt($amIn)) {
                            $workedMinutes += $amOut->diffInMinutes($amIn);
                        }
                    }
                    if ($pmArrival && $pmDeparture) {
                        $pmIn = Carbon::createFromFormat('g:i', $pmArrival);
                        $pmOut = Carbon::createFromFormat('g:i', $pmDeparture);
                        if ($pmOut->gt($pmIn)) {
                            $workedMinutes += $pmOut->diffInMinutes($pmIn);
                        }
                    }
                } else {
                    if ($lastLogTime->gt($firstLogTime)) {
                        $spanMinutes = $lastLogTime->diffInMinutes($firstLogTime);
                        $lunchStart = $date->copy()->setTime(12, 0);
                        $lunchEnd = $date->copy()->setTime(13, 0);
                        if ($firstLogTime->lt($lunchEnd) && $lastLogTime->gt($lunchStart)) {
                            $spanMinutes = max(0, $spanMinutes - 60);
                        }
                        $workedMinutes = $spanMinutes;
                    }
                }

                $lateMinutes = 0;
                $undertimeMinutes = 0;

                if ($isTeaching) {
                    if ($firstLogTime->gt($officialMorningArrival)) {
                        $lateMinutes += $firstLogTime->diffInMinutes($officialMorningArrival);
                    }
                    if ($lastLogTime->lt($officialAfternoonDeparture)) {
                        $undertimeMinutes += $officialAfternoonDeparture->diffInMinutes($lastLogTime);
                    }
                } else {
                    // Non-teaching (flexy time): late only on Mondays, undertime if < 8 hours/day
                    if ($date->dayOfWeek === 1) {
                        if ($firstLogTime->gt($officialMorningArrival)) {
                            $lateMinutes += $firstLogTime->diffInMinutes($officialMorningArrival);
                        }
                    }

                    if ($workedMinutes < 480) {
                        $undertimeMinutes += (480 - $workedMinutes);
                    }
                }

                $totalMinutes = $lateMinutes + $undertimeMinutes;
                if ($totalMinutes > 0) {
                    $dailyDetails[$dateStr]['late_hours'] = intdiv($totalMinutes, 60);
                    $dailyDetails[$dateStr]['late_minutes'] = $totalMinutes % 60;
                    $dailyDetails[$dateStr]['late_time_display'] = sprintf('%02d:%02d', $dailyDetails[$dateStr]['late_hours'], $dailyDetails[$dateStr]['late_minutes']);
                    $dailyDetails[$dateStr]['late_total_minutes'] = $totalMinutes;
                }
            } else {
                $dailyDetails[$dateStr] = [
                    'am_arrival' => null,
                    'am_departure' => null,
                    'pm_arrival' => null,
                    'pm_departure' => null,
                    'am_arrival_minutes' => null,
                    'am_departure_minutes' => null,
                    'pm_arrival_minutes' => null,
                    'pm_departure_minutes' => null,
                    'late_hours' => null,
                    'late_minutes' => null,
                    'late_time_display' => '',
                    'late_total_minutes' => null,
                    'has_logs' => false,
                ];
            }

            if (!empty($leavesByDate[$dateStr])) {
                $dailyDetails[$dateStr]['ignore_late_undertime'] = true;
            }
        }

        // Get all dates in range
        $dates = collect();
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dates->push($date->copy());
        }

        return compact(
            'employee',
            'startDate',
            'endDate',
            'dailyDetails',
            'holidays',
            'activitiesByDate',
            'leavesByDate',
            'dates',
            'officialArrival',
            'officialDeparture',
            'regularDays',
            'saturdays',
            'timeWindows'
        );
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

    private function scopedDepartmentsQuery()
    {
        $user = auth()->user();

        if ($user?->hasRole('Admin|Superadmin')) {
            return Department::query();
        }

        $employee = $user?->employee;
        if ($employee && $employee->department_id) {
            return Department::query()->where('id', $employee->department_id);
        }

        return Department::query()->whereRaw('1=0');
    }

    private function authorizeEmployeeAccess(Employee $employee): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if ($user->hasRole('Admin|Superadmin')) {
            return;
        }

        $userEmployee = $user->employee;
        if (!$userEmployee) {
            abort(403);
        }

        if ($user->hasRole('DTR Incharge')) {
            if ($userEmployee->department_id && $employee->department_id === $userEmployee->department_id) {
                return;
            }
            abort(403);
        }

        if ($userEmployee->id !== $employee->id) {
            abort(403);
        }
    }

    private function applyAttendanceScope($query): void
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('Admin|Superadmin')) {
            return;
        }

        $employee = $user->employee;
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

        $query->where('badge_number', $employee->badge_number);
    }
}
