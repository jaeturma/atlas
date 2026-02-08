<?php

// Test the ZKTeco library directly and wrapper
require 'vendor/autoload.php';

echo "=== Testing ZKTeco Library ===\n\n";

// Test 1: Try to create ZKTeco instance with a test IP
echo "1. Creating ZKTeco instance with test IP...\n";
try {
    $zk = new \CodingLibs\ZktecoPhp\Libs\ZKTeco('192.168.1.100', 4370, false, 5);
    echo "   ✓ ZKTeco instance created successfully\n";
} catch (\Throwable $e) {
    echo "   ✗ Failed: " . $e->getMessage() . "\n";
    echo "   Exception type: " . get_class($e) . "\n";
    if (method_exists($e, 'getFile')) {
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

// Test 2: Try with wrapper
echo "\n2. Creating ZKTecoWrapper instance...\n";
try {
    $wrapper = new \App\Services\ZKTecoWrapper('192.168.1.100', 4370, false, 5);
    echo "   ✓ ZKTecoWrapper instance created successfully\n";
    echo "   - Socket available: " . ($wrapper->isSocketAvailable() ? 'Yes' : 'No') . "\n";
} catch (\Throwable $e) {
    echo "   ✗ Failed: " . $e->getMessage() . "\n";
}

// Test 3: Check if socket extension is loaded
echo "\n3. Checking socket extension...\n";
if (extension_loaded('sockets')) {
    echo "   ✓ Socket extension is loaded\n";
} else {
    echo "   ✗ Socket extension NOT loaded\n";
}

// Test 4: Check if socket_create function exists
echo "\n4. Checking socket_create function...\n";
if (function_exists('socket_create')) {
    echo "   ✓ socket_create() function exists\n";
} else {
    echo "   ✗ socket_create() function NOT found\n";
}

echo "\n=== Test Complete ===\n";
