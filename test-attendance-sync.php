<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Get the device
$device = \App\Models\Device::first();
if (!$device) {
    echo "No device found\n";
    exit(1);
}

echo "Testing device: {$device->name} (ID: {$device->id})\n";
echo "Model: {$device->model}\n";
echo "IP: {$device->ip_address}:{$device->port}\n\n";

// Test using AttendanceSyncService
echo "Testing AttendanceSyncService...\n";
$service = new \App\Services\AttendanceSyncService($device);

// Test connection
echo "Testing connection...\n";
$connResult = $service->testConnection();
echo "Connection result: " . json_encode($connResult) . "\n\n";

if ($connResult['success']) {
    // Test download
    $today = \Carbon\Carbon::today();
    echo "Downloading logs for {$today->format('Y-m-d')}...\n";
    $result = $service->downloadAttendance($today->format('Y-m-d'), $today->format('Y-m-d'), $device->id);
    echo "Download result: " . json_encode($result) . "\n";
} else {
    echo "Connection failed, skipping download\n";
}
