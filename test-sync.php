<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

// Get all devices
$devices = \App\Models\Device::all();
echo "Total Devices: " . count($devices) . "\n";

foreach ($devices as $device) {
    echo "- Device: {$device->name} ({$device->ip_address}:{$device->port}) - Active: " . ($device->is_active ? 'Yes' : 'No') . "\n";
}

// Check today's logs
$todayLogs = \App\Models\AttendanceLog::whereDate('log_date', date('Y-m-d'))->get();
echo "\nToday's Logs: " . count($todayLogs) . "\n";

foreach ($todayLogs->take(5) as $log) {
    echo "- Badge: {$log->badge_number}, Status: {$log->status}, Time: {$log->log_datetime}\n";
}

echo "\nTotal Logs in DB: " . \App\Models\AttendanceLog::count() . "\n";
