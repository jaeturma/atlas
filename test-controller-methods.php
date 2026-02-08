<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$kernel->handle($request);

$device = \App\Models\Device::first();

echo "=== Testing DeviceController Methods ===\n\n";

if ($device) {
    $controller = new \App\Http\Controllers\DeviceController();
    
    // Test getStatus
    echo "1. Testing getStatus()\n";
    echo str_repeat("-", 60) . "\n";
    try {
        $response = $controller->getStatus($device);
        $data = json_decode($response->getContent(), true);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
    
    // Test getDeviceTime
    echo "2. Testing getDeviceTime()\n";
    echo str_repeat("-", 60) . "\n";
    try {
        $response = $controller->getDeviceTime($device);
        $data = json_decode($response->getContent(), true);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
    
    // Test syncTime
    echo "3. Testing syncTime()\n";
    echo str_repeat("-", 60) . "\n";
    try {
        $response = $controller->syncTime($device);
        $data = json_decode($response->getContent(), true);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
    
} else {
    echo "No devices found\n";
}
