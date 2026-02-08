<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Get a device
$device = \App\Models\Device::first();
echo "Testing device: " . $device->name . "\n";
echo "Device ID: " . $device->id . "\n";
echo "Device IP: " . $device->ip_address . "\n";
echo "Device Port: " . $device->port . "\n";
echo "Device Model: " . $device->model . "\n\n";

// Test the controller method directly
try {
    echo "Testing ZKTecoService connection...\n";
    $service = new \App\Services\ZKTecoService($device);
    $connectionTest = $service->testConnection();
    echo "Connection test result: " . json_encode($connectionTest) . "\n\n";
    
    if ($connectionTest['success']) {
        echo "Testing downloadAttendance...\n";
        $today = \Carbon\Carbon::today();
        $result = $service->downloadAttendance($today->format('Y-m-d'), $today->format('Y-m-d'), $device->id);
        echo "Download result: " . json_encode($result) . "\n";
    } else {
        echo "Connection test failed, skipping download test\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}
