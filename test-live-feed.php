<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

// Simulate the liveFeed response
$query = \App\Models\AttendanceLog::query()
    ->with(['device'])
    ->whereDate('log_date', today());

$logs = $query->orderBy('log_datetime', 'desc')
    ->limit(500)
    ->get();

echo "Logs fetched: " . count($logs) . "\n\n";

// Format logs for real-time display
$formattedLogs = $logs->map(function ($log) {
    return [
        'id' => $log->id,
        'badge_number' => $log->badge_number,
        'device_id' => $log->device_id,
        'device_name' => $log->device->name ?? 'Unknown',
        'log_datetime' => $log->log_datetime->toIso8601String(),
        'status' => $log->status,
        'punch_type' => $log->punch_type,
        'employee_name' => $log->employee?->getFullName() ?? 'Not Linked',
    ];
})->toArray();

$response = [
    'success' => true,
    'logs' => $formattedLogs,
    'total' => count($formattedLogs),
    'timestamp' => now()->toIso8601String(),
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
