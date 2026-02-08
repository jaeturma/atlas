<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['role:Admin|Superadmin|DTR Incharge'])->group(function () {
        Route::resource('employees', \App\Http\Controllers\EmployeeController::class);
        Route::post('/employees/import', [\App\Http\Controllers\EmployeeController::class, 'import'])->name('employees.import');
        Route::get('/employees/template/download', [\App\Http\Controllers\EmployeeController::class, 'downloadTemplate'])->name('employees.template');
    });
    Route::get('/import/progress', function() {
        return response()->json(['progress' => session('import_progress', 0)]);
    })->name('import.progress');
    
    Route::middleware(['role:Admin|Superadmin'])->group(function () {
        Route::resource('departments', \App\Http\Controllers\DepartmentController::class);
        Route::post('/departments/import', [\App\Http\Controllers\DepartmentController::class, 'import'])->name('departments.import');
        Route::get('/departments/template/download', [\App\Http\Controllers\DepartmentController::class, 'downloadTemplate'])->name('departments.template');

        Route::resource('positions', \App\Http\Controllers\PositionController::class);
        Route::post('/positions/import', [\App\Http\Controllers\PositionController::class, 'import'])->name('positions.import');
        Route::get('/positions/template/download', [\App\Http\Controllers\PositionController::class, 'downloadTemplate'])->name('positions.template');
    });
    Route::middleware(['role:Superadmin'])->group(function () {
        Route::get('/attendance-logs/create', [\App\Http\Controllers\AttendanceLogController::class, 'create'])->name('attendance-logs.create');
        Route::post('/attendance-logs', [\App\Http\Controllers\AttendanceLogController::class, 'store'])->name('attendance-logs.store');

        Route::resource('devices', \App\Http\Controllers\DeviceController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

        // Attendance Logs Downloads/Uploads
        Route::get('/attendance-logs/export', [\App\Http\Controllers\AttendanceLogController::class, 'export'])->name('attendance-logs.export');

        // Report Settings Routes
        Route::get('/report-settings', [\App\Http\Controllers\ReportSettingController::class, 'index'])->name('report-settings.index');
        Route::put('/report-settings', [\App\Http\Controllers\ReportSettingController::class, 'update'])->name('report-settings.update');

        Route::get('/login-otps', [\App\Http\Controllers\LoginOtpController::class, 'index'])->name('login-otps.index');
    });

    Route::middleware(['role:Admin|Superadmin'])->group(function () {
        // User Management Routes
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);
        Route::resource('roles', \App\Http\Controllers\RoleController::class)->except(['show']);
        Route::resource('permissions', \App\Http\Controllers\PermissionController::class)->except(['show']);

        // Holiday Routes
        Route::resource('holidays', \App\Http\Controllers\HolidayController::class);

        Route::post('/attendance-logs/save', [\App\Http\Controllers\AttendanceLogController::class, 'saveImported'])->name('attendance-logs.save');
        Route::post('/attendance-logs/upload', [\App\Http\Controllers\AttendanceLogsUploadController::class, 'uploadFile'])->name('attendance-logs.upload');
        Route::resource('devices', \App\Http\Controllers\DeviceController::class)->only(['index', 'show']);
        Route::post('/devices/test-connection', [\App\Http\Controllers\DeviceController::class, 'testConnection'])->name('devices.test-connection');
        Route::post('/devices/{device}/test-connection', [\App\Http\Controllers\DeviceController::class, 'testConnection'])->name('devices.test-connection-existing');
        Route::post('/devices/{device}/download-logs', [\App\Http\Controllers\DeviceController::class, 'downloadLogs'])->name('devices.download-logs');
        Route::post('/devices/{device}/sync-mode', [\App\Http\Controllers\DeviceController::class, 'updateSyncMode'])->name('devices.update-sync-mode');
        Route::get('/devices/{device}/status', [\App\Http\Controllers\DeviceController::class, 'getStatus'])->name('devices.status');
        Route::post('/devices/{device}/sync-time', [\App\Http\Controllers\DeviceController::class, 'syncTime'])->name('devices.sync-time');
        Route::get('/devices/{device}/device-time', [\App\Http\Controllers\DeviceController::class, 'getDeviceTime'])->name('devices.device-time');
        Route::post('/devices/{device}/download-users', [\App\Http\Controllers\DeviceController::class, 'downloadUsers'])->name('devices.download-users');
        Route::post('/devices/{device}/download-device-logs', [\App\Http\Controllers\DeviceController::class, 'downloadDeviceLogs'])->name('devices.download-device-logs');
        Route::post('/devices/{device}/clear-logs', [\App\Http\Controllers\DeviceController::class, 'clearLogs'])->name('devices.clear-logs');
        Route::post('/devices/{device}/restart', [\App\Http\Controllers\DeviceController::class, 'restart'])->name('devices.restart');

        // System Settings Routes
        Route::get('/settings', [\App\Http\Controllers\SystemSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\SystemSettingController::class, 'update'])->name('settings.update');
    });

    // Attendance Logs Routes
    Route::get('/attendance-logs', [\App\Http\Controllers\AttendanceLogController::class, 'index'])->name('attendance-logs.index');
    Route::middleware(['role:Admin|Superadmin'])->group(function () {
        Route::get('/attendance-logs/live-monitor', [\App\Http\Controllers\AttendanceLogController::class, 'liveMonitor'])->name('attendance-logs.live-monitor');
        Route::get('/attendance-logs/live-feed', [\App\Http\Controllers\AttendanceLogController::class, 'liveFeed'])->name('attendance-logs.live-feed');
        Route::post('/attendance-logs/sync/{device}', [\App\Http\Controllers\AttendanceLogController::class, 'syncFromDevice'])->name('attendance-logs.sync-device');
        Route::post('/attendance-logs/sync-cloud', [\App\Http\Controllers\AttendanceLogController::class, 'syncToCloud'])->name('attendance-logs.sync-cloud');
    });
    Route::get('/attendance-logs/daily-summary', [\App\Http\Controllers\AttendanceLogController::class, 'dailySummary'])->name('attendance-logs.daily-summary');
    Route::get('/attendance-logs/daily-time-record', [\App\Http\Controllers\AttendanceLogController::class, 'dailyTimeRecord'])->name('attendance-logs.daily-time-record');
    Route::get('/attendance-logs/form-48/{employee}', [\App\Http\Controllers\AttendanceLogController::class, 'printForm48'])->name('attendance-logs.form-48');
    Route::post('/attendance-logs/form-48/{employee}/download-pdf', [\App\Http\Controllers\AttendanceLogController::class, 'downloadForm48PDF'])->name('attendance-logs.form-48-download-pdf');
    Route::get('/attendance-logs/{log}', [\App\Http\Controllers\AttendanceLogController::class, 'show'])->name('attendance-logs.show');
    Route::get('/attendance-logs/employee/{employee}', [\App\Http\Controllers\AttendanceLogController::class, 'byEmployee'])->name('attendance-logs.by-employee');
    Route::get('/attendance-logs/device/{device}', [\App\Http\Controllers\AttendanceLogController::class, 'byDevice'])->name('attendance-logs.by-device');
    Route::delete('/attendance-logs/{log}', [\App\Http\Controllers\AttendanceLogController::class, 'destroy'])->name('attendance-logs.destroy');

    // Activity Routes
    Route::middleware(['role:Admin|Superadmin|Employee|DTR Incharge'])->group(function () {
        Route::resource('activities', \App\Http\Controllers\ActivityController::class);
    });

    // Leave Routes
    Route::middleware(['role:Admin|Superadmin|Employee|DTR Incharge|Leave Incharge|Leave Approver'])->group(function () {
        Route::get('/leaves/{leaf}/print', [\App\Http\Controllers\LeaveController::class, 'print'])->name('leaves.print');
        Route::get('/leaves/{leaf}/download-pdf', [\App\Http\Controllers\LeaveController::class, 'downloadPdf'])->name('leaves.download-pdf');
        Route::post('/leaves/batch-approve', [\App\Http\Controllers\LeaveController::class, 'batchApprove'])->name('leaves.batch-approve');
        Route::resource('leaves', \App\Http\Controllers\LeaveController::class);
    });

    // Final Form Route
    Route::get('/attendance-logs/{employee}/final-form', [\App\Http\Controllers\AttendanceLogController::class, 'showFinalForm'])->name('attendance-logs.final-form');
    Route::post('/attendance-logs/{employee}/final-form/download-pdf', [\App\Http\Controllers\AttendanceLogController::class, 'downloadFinalFormPDF'])->name('attendance-logs.final-form-download-pdf');
    Route::post('/attendance-logs/{employee}/final-form/download-word', [\App\Http\Controllers\AttendanceLogController::class, 'downloadFinalFormWord'])->name('attendance-logs.final-form-download-word');
    Route::post('/attendance-logs/{employee}/final-form/download-acc-report-word', [\App\Http\Controllers\AttendanceLogController::class, 'downloadFinalAccReportWord'])->name('attendance-logs.final-form-download-acc-report-word');

    // Face Recognition Routes
    Route::get('/face-recognition', [FaceRecognitionController::class, 'index'])->name('face-recognition.index');
    Route::get('/face-recognition/capture', [FaceRecognitionController::class, 'capture'])->name('face-recognition.capture');
    Route::post('/face-recognition/enroll', [FaceRecognitionController::class, 'enroll'])->name('face-recognition.enroll');
    Route::post('/face-recognition/verify', [FaceRecognitionController::class, 'verify'])->name('face-recognition.verify');
    Route::delete('/face-recognition/clear', [FaceRecognitionController::class, 'clear'])->name('face-recognition.clear');
});

require __DIR__.'/auth.php';

// API Routes for Face Recognition
Route::middleware('auth')->post('/api/face-recognition/match', [\App\Http\Controllers\Api\FaceRecognitionController::class, 'match'])->name('api.face-recognition.match');
