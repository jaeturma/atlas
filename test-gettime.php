<?php

// Test the full getTime flow with the wrapper
require 'vendor/autoload.php';

echo "=== Testing Full getTime Flow ===\n\n";

// Test 1: Create wrapper and try to get device time (will fail gracefully)
echo "1. Creating ZKTecoWrapper and calling getTime...\n";
try {
    $wrapper = new \App\Services\ZKTecoWrapper('192.168.1.100', 4370, false, 5);
    echo "   ✓ ZKTecoWrapper created\n";
    
    // This will fail because device doesn't exist, but it shouldn't crash
    $result = $wrapper->getTime();
    echo "   - getTime() result: " . var_export($result, true) . "\n";
} catch (\Throwable $e) {
    echo "   ✗ Exception: " . $e->getMessage() . "\n";
    echo "   Exception type: " . get_class($e) . "\n";
}

// Test 2: Try the direct ZKTeco class (should also fail gracefully)
echo "\n2. Creating ZKTeco directly and calling getTime...\n";
try {
    $zk = new \CodingLibs\ZktecoPhp\Libs\ZKTeco('192.168.1.100', 4370, false, 5);
    echo "   ✓ ZKTeco created\n";
    
    $result = $zk->getTime();
    echo "   - getTime() result: " . var_export($result, true) . "\n";
} catch (\Throwable $e) {
    echo "   ✗ Exception: " . $e->getMessage() . "\n";
    echo "   Exception type: " . get_class($e) . "\n";
}

echo "\n=== Test Complete ===\n";
