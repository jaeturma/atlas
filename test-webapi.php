<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$kernel->handle($request);

$device = \App\Models\Device::first();

echo "=== Testing Web API Support ===\n\n";

if ($device) {
    echo "Device: {$device->name} ({$device->model})\n";
    echo "IP: {$device->ip_address}\n\n";
    
    // Test 1: Find Web API endpoint
    echo "Test 1: Finding Web API Endpoint\n";
    echo str_repeat("-", 60) . "\n";
    
    $webapi = \App\Services\ZKTecoWebAPI::findWebAPI($device->ip_address);
    
    if ($webapi) {
        echo "Web API found!\n";
        
        // Test 2: Authentication
        echo "\nTest 2: Authenticating\n";
        echo str_repeat("-", 60) . "\n";
        
        if ($webapi->authenticate()) {
            echo "Authentication successful!\n";
            
            // Test 3: Get device info
            echo "\nTest 3: Getting Device Info\n";
            echo str_repeat("-", 60) . "\n";
            
            $info = $webapi->getDeviceInfo();
            if ($info) {
                echo "Device Info: " . json_encode($info, JSON_PRETTY_PRINT) . "\n";
            } else {
                echo "Error: " . $webapi->getLastError() . "\n";
            }
            
            // Test 4: Get device time
            echo "\nTest 4: Getting Device Time\n";
            echo str_repeat("-", 60) . "\n";
            
            $time = $webapi->getTime();
            if ($time) {
                echo "Device Time: $time\n";
                if (is_numeric($time)) {
                    echo "Formatted: " . date('Y-m-d H:i:s', $time) . "\n";
                }
            } else {
                echo "Error: " . $webapi->getLastError() . "\n";
            }
            
        } else {
            echo "Authentication failed: " . $webapi->getLastError() . "\n";
        }
    } else {
        echo "Web API endpoint not found on standard ports\n";
    }
    
    echo "\n";
    echo "Test 2: Testing Protocol Manager with all fallbacks\n";
    echo str_repeat("-", 60) . "\n";
    
    $manager = new \App\Services\DeviceProtocolManager($device);
    $result = $manager->connect();
    
    echo "Connection Result:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
    if ($result['success']) {
        echo "\nProtocol: " . $result['protocol'] . "\n";
        
        echo "\nGetting time via manager:\n";
        $time = $manager->getTime();
        if ($time) {
            echo "Time: $time\n";
        } else {
            echo "Error: " . $manager->getLastError() . "\n";
        }
    }
    
} else {
    echo "No devices found\n";
}
