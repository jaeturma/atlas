<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user?->hasRole('Admin') || $user?->hasRole('Superadmin')) {
            return view('dashboard');
        }

        if ($user?->hasRole('DTR Incharge')) {
            $employee = $user->employee;
            $departmentId = $employee?->department_id;
            $department = $departmentId ? Department::find($departmentId) : null;

            $today = Carbon::today();
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();

            $employeeCount = $departmentId
                ? Employee::where('department_id', $departmentId)->count()
                : 0;

            $todayLogs = AttendanceLog::query()
                ->whereDate('log_date', $today)
                ->when($departmentId, function ($query) use ($departmentId) {
                    $query->whereHas('employee', function ($employeeQuery) use ($departmentId) {
                        $employeeQuery->where('department_id', $departmentId);
                    });
                })
                ->count();

            $monthLogs = AttendanceLog::query()
                ->whereBetween('log_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->when($departmentId, function ($query) use ($departmentId) {
                    $query->whereHas('employee', function ($employeeQuery) use ($departmentId) {
                        $employeeQuery->where('department_id', $departmentId);
                    });
                })
                ->count();

            $recentLogs = AttendanceLog::query()
                ->with(['employee', 'device'])
                ->when($departmentId, function ($query) use ($departmentId) {
                    $query->whereHas('employee', function ($employeeQuery) use ($departmentId) {
                        $employeeQuery->where('department_id', $departmentId);
                    });
                })
                ->latest('log_datetime')
                ->limit(10)
                ->get();

            $todaysLogs = AttendanceLog::query()
                ->with(['employee'])
                ->whereDate('log_date', $today)
                ->when($departmentId, function ($query) use ($departmentId) {
                    $query->whereHas('employee', function ($employeeQuery) use ($departmentId) {
                        $employeeQuery->where('department_id', $departmentId);
                    });
                })
                ->latest('log_datetime')
                ->limit(10)
                ->get();

            $currentActivities = Activity::query()
                ->with('employee')
                ->whereDate('date', '<=', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $today);
                })
                ->when($departmentId, function ($query) use ($departmentId) {
                    $query->whereHas('employee', function ($employeeQuery) use ($departmentId) {
                        $employeeQuery->where('department_id', $departmentId);
                    });
                })
                ->orderByDesc('date')
                ->limit(10)
                ->get();

            return view('dashboards.dtr-incharge', compact(
                'department',
                'employeeCount',
                'todayLogs',
                'monthLogs',
                'recentLogs',
                'todaysLogs',
                'currentActivities'
            ));
        }

        $employee = $user?->employee;
        $useMobileView = $this->shouldUseMobileEmployeeView($request);

        if (!$employee) {
            return view($useMobileView ? 'dashboards.employee-mobile' : 'dashboards.employee', [
                'employee' => null,
                'todayLogs' => 0,
                'monthLogs' => 0,
                'recentLogs' => collect(),
            ]);
        }

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $todayLogs = AttendanceLog::query()
            ->where('employee_id', $employee->id)
            ->whereDate('log_date', $today)
            ->count();

        $monthLogs = AttendanceLog::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('log_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->count();

        $recentLogs = AttendanceLog::query()
            ->with('device')
            ->where('employee_id', $employee->id)
            ->latest('log_datetime')
            ->limit(10)
            ->get();

        return view($useMobileView ? 'dashboards.employee-mobile' : 'dashboards.employee', compact(
            'employee',
            'todayLogs',
            'monthLogs',
            'recentLogs'
        ));
    }

    private function shouldUseMobileEmployeeView(Request $request): bool
    {
        $userAgent = strtolower((string) $request->header('User-Agent', ''));

        $appWrapperHeader = strtolower((string) $request->header('X-App-Wrapper', ''));
        if (in_array($appWrapperHeader, ['1', 'true', 'yes'], true)) {
            return true;
        }

        $isAppWrapper = (bool) preg_match('/\bwv\b|webview|appwrapper|atlasapp/', $userAgent);
        if ($isAppWrapper) {
            return true;
        }

        $isTablet = (bool) preg_match('/ipad|tablet|nexus 7|nexus 9|nexus 10|sm-t|gt-p|kindle|silk/', $userAgent);
        $isPhone = (bool) preg_match('/iphone|ipod|android.*mobile|windows phone|blackberry|bb10|opera mini|\bmobile\b/', $userAgent);

        return $isPhone && !$isTablet;
    }
}