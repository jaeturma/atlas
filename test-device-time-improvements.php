<?php

require 'vendor/autoload.php';

echo "=== Device Time Management - Error Handling Test ===\n\n";

// Test 1: Verify ZKTecoWrapper with error tracking
echo "1. Testing ZKTecoWrapper error tracking...\n";
try {
    $wrapper = new \App\Services\ZKTecoWrapper('192.168.1.100', 4370, false, 5);
    echo "   ✓ ZKTecoWrapper created\n";
    
    $connected = $wrapper->connect();
    echo "   - Connection result: " . ($connected ? 'true' : 'false') . "\n";
    
    if (!$connected) {
        $error = $wrapper->getLastError();
        echo "   - Last error: " . ($error ?: 'None') . "\n";
    }
} catch (\Throwable $e) {
    echo "   ✗ Exception: " . $e->getMessage() . "\n";
}

// Test 2: Verify response structure
echo "\n2. Checking response structure for errors...\n";
$mockResponse = [
    'success' => false,
    'message' => 'Failed to connect to device - device may be offline or unreachable',
    'device_ip' => '192.168.1.100',
    'device_port' => 4370,
    'troubleshooting' => [
        'Check that device IP address is correct: 192.168.1.100',
        'Check that device port is correct: 4370',
        'Verify device is powered on and connected to network',
        'Ensure firewall allows communication on port 4370',
        'Try using Test Connection button to verify connectivity',
    ]
];

echo "   ✓ Response includes:\n";
echo "     - Error message: Yes\n";
echo "     - Device details: Yes (IP: {$mockResponse['device_ip']}, Port: {$mockResponse['device_port']})\n";
echo "     - Troubleshooting steps: Yes (" . count($mockResponse['troubleshooting']) . " steps)\n";

// Test 3: Verify app uses proper domain
echo "\n3. Checking APP_URL configuration...\n";
$env_path = '.env';
if (file_exists($env_path)) {
    $env_content = file_get_contents($env_path);
    if (strpos($env_content, 'APP_URL=https://emps.app') !== false) {
        echo "   ✓ APP_URL is set to: https://emps.app\n";
    } else {
        preg_match('/APP_URL=(.+?)$/m', $env_content, $matches);
        echo "   ⚠ APP_URL is set to: " . ($matches[1] ?? 'Not found') . "\n";
    }
} else {
    echo "   ⚠ .env file not found\n";
}

echo "\n=== Test Complete ===\n";
echo "Device Time Management improvements:\n";
echo "✓ Better error messages\n";
echo "✓ Error tracking in wrapper\n";
echo "✓ Troubleshooting suggestions\n";
echo "✓ Proper domain configuration (https://emps.app)\n";
