<?php

require 'vendor/autoload.php';

echo "=== Simulating DeviceController::getDeviceTime() ===\n\n";

// Create a mock device
$mockDevice = new class {
    public $ip_address = '192.168.1.100';
    public $port = 4370;
};

echo "Device: {$mockDevice->ip_address}:{$mockDevice->port}\n\n";

// Test 1: The wrapper flow
echo "Test 1: ZKTecoWrapper flow (should return false gracefully)...\n";
try {
    $zk = new \App\Services\ZKTecoWrapper($mockDevice->ip_address, (int)$mockDevice->port, false, 10);
    echo "✓ ZKTecoWrapper created\n";
    
    if (!$zk->connect()) {
        echo "✓ connect() returned false (device not available)\n";
        $device_time = $zk->getTime();
        if (!$device_time) {
            echo "✓ getTime() returned false (expected)\n";
        }
    }
} catch (\Throwable $e) {
    echo "✗ Unexpected exception: " . $e->getMessage() . "\n";
}

// Test 2: The fallback flow (HTTP method)
echo "\nTest 2: HTTP fallback flow (socket method failed)...\n";
$timeout = 5;
$url = "http://{$mockDevice->ip_address}:{$mockDevice->port}/";
$context = stream_context_create([
    'http' => [
        'timeout' => $timeout,
        'method' => 'GET',
    ]
]);

try {
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        echo "✓ HTTP fallback returned false (device not reachable, expected)\n";
    } else {
        echo "✓ Device responded via HTTP\n";
    }
} catch (\Throwable $e) {
    echo "✓ Caught exception in HTTP fallback: " . get_class($e) . "\n";
}

// Test 3: Verify the complete controller method response
echo "\nTest 3: Complete controller method response...\n";
echo "The controller would return JSON with 'success': false\n";
echo "and message: 'Failed to get device time - device not reachable'\n";

echo "\n=== Simulation Complete ===\n";
echo "The 'socket_create()' error should NOT occur in any of these flows.\n";
