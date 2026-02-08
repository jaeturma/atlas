<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

$device = \App\Models\Device::first();

echo "Testing DeviceController Methods\n";
echo str_repeat("=", 60) . "\n\n";

// Test getStatus
echo "1. Testing getStatus()...\n";
$controller = new \App\Http\Controllers\DeviceController();
try {
    $response = $controller->getStatus($device);
    $status = json_decode($response->getContent(), true);
    echo "   Status: " . $status['connection']['status'] . "\n";
    echo "   Ping: " . ($status['connection']['ping'] ? 'Yes' : 'No') . "\n";
    echo "   Socket: " . ($status['connection']['socket'] ? 'Yes' : 'No') . "\n";
    echo "   Protocol: " . ($status['connection']['protocol'] ? 'Yes' : 'No') . "\n";
    echo "   ✓ getStatus() works\n\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test testConnection
echo "2. Testing testConnection()...\n";
try {
    $response = $controller->testConnection($device);
    $result = json_decode($response->getContent(), true);
    echo "   Result: " . ($result['connected'] ? 'Connected' : 'Not connected') . "\n";
    if ($result['error']) {
        echo "   Error: " . $result['error'] . "\n";
    }
    echo "   ✓ testConnection() works\n\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test getDeviceTime
echo "3. Testing getDeviceTime()...\n";
try {
    $response = $controller->getDeviceTime($device);
    $result = json_decode($response->getContent(), true);
    if ($result['success']) {
        echo "   Device Time: " . $result['device_time'] . "\n";
        echo "   ✓ getDeviceTime() works\n";
    } else {
        echo "   ✗ " . $result['message'] . "\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test downloadUsers
echo "4. Testing downloadUsers()...\n";
try {
    $response = $controller->downloadUsers($device);
    $result = json_decode($response->getContent(), true);
    if ($result['success']) {
        echo "   Users Downloaded: " . $result['users_count'] . "\n";
        echo "   ✓ downloadUsers() works\n";
    } else {
        echo "   ✗ " . $result['message'] . "\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test downloadDeviceLogs
echo "5. Testing downloadDeviceLogs()...\n";
try {
    $response = $controller->downloadDeviceLogs($device);
    $result = json_decode($response->getContent(), true);
    if ($result['success']) {
        echo "   Logs Downloaded: " . $result['logs_count'] . "\n";
        echo "   ✓ downloadDeviceLogs() works\n";
    } else {
        echo "   ✗ " . $result['message'] . "\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

echo str_repeat("=", 60) . "\n";
echo "Routes available:\n";
echo "  GET    /devices/{device}/status\n";
echo "  POST   /devices/{device}/sync-time\n";
echo "  GET    /devices/{device}/device-time\n";
echo "  POST   /devices/{device}/download-users\n";
echo "  POST   /devices/{device}/download-device-logs\n";
echo "  POST   /devices/{device}/clear-logs\n";
echo "  POST   /devices/{device}/restart\n";
echo "  POST   /devices/{device}/test-connection\n";
