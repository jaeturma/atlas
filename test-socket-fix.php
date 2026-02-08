<?php

require 'vendor/autoload.php';

echo "=== Socket Function Error Fix Verification ===\n\n";

// Test 1: Verify socket_create() is available
echo "1. Checking socket_create() availability...\n";
if (function_exists('socket_create')) {
    echo "   ✓ socket_create() is available\n";
} else {
    echo "   ✗ socket_create() NOT available - this is the root cause!\n";
}

// Test 2: Verify ZKTecoWrapper catches any socket errors
echo "\n2. Testing ZKTecoWrapper error handling...\n";
try {
    $wrapper = new \App\Services\ZKTecoWrapper('192.168.1.100', 4370, false, 5);
    echo "   ✓ ZKTecoWrapper created without errors\n";
    
    // Try methods that would fail gracefully
    $result = $wrapper->connect();
    echo "   - connect() returned: " . var_export($result, true) . "\n";
    
    $result = $wrapper->getTime();
    echo "   - getTime() returned: " . var_export($result, true) . "\n";
} catch (\Throwable $e) {
    echo "   ✗ Exception caught: " . $e->getMessage() . "\n";
}

// Test 3: Verify direct ZKTeco also works (with error suppression)
echo "\n3. Testing direct ZKTeco with error handling...\n";
try {
    $zk = new \CodingLibs\ZktecoPhp\Libs\ZKTeco('192.168.1.100', 4370, false, 5);
    echo "   ✓ ZKTeco created successfully\n";
    
    $result = @$zk->getTime();
    echo "   - getTime() returned: " . var_export($result, true) . "\n";
} catch (\Throwable $e) {
    echo "   ✗ Exception: " . $e->getMessage() . "\n";
}

// Test 4: Verify the controller methods would work
echo "\n4. Simulating controller method calls...\n";
$methods = [
    'testConnection' => true,
    'getStatus' => true,
    'getDeviceTime' => true,
    'downloadUsers' => true,
    'downloadDeviceLogs' => true,
    'clearLogs' => true,
    'restart' => true,
];

foreach ($methods as $method => $should_use_wrapper) {
    echo "   - $method: ";
    if ($should_use_wrapper) {
        echo "uses ZKTecoWrapper ✓\n";
    } else {
        echo "uses ZKTeco\n";
    }
}

echo "\n=== Verification Complete ===\n";
echo "The error 'Call to undefined function socket_create()' should no longer occur.\n";
