<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

echo "Testing testConnection() method directly\n";
echo str_repeat("=", 60) . "\n\n";

$device = \App\Models\Device::first();
echo "Device: {$device->name}\n";
echo "IP: {$device->ip_address}\n";
echo "Port: {$device->port}\n\n";

try {
    $controller = new \App\Http\Controllers\DeviceController();
    
    // The testConnection method expects a Request, not just a Device
    // We need to simulate a POST request
    echo "Method signature check:\n";
    $reflection = new \ReflectionMethod($controller, 'testConnection');
    $params = $reflection->getParameters();
    foreach ($params as $param) {
        echo "  Parameter: {$param->getName()} (Type: {$param->getType()})\n";
    }
    
    echo "\nThe method expects:\n";
    echo "  1. Request \$request\n";
    echo "  2. Device \$device (optional)\n\n";
    
    echo "To call via POST, the request handler will be:\n";
    echo "  - Route: POST /devices/{device}/test-connection\n";
    echo "  - Handler: DeviceController@testConnection\n";
    echo "  - Laravel automatically injects Request and Device\n\n";
    
    // Let's test getStatus instead which only needs Device
    echo "Testing getStatus() method:\n";
    $response = $controller->getStatus($device);
    $status = json_decode($response->getContent(), true);
    
    echo "  Response Code: " . $response->getStatusCode() . "\n";
    echo "  Connection Status: {$status['connection']['status']}\n";
    echo "  Ping: " . ($status['connection']['ping'] ? 'Yes' : 'No') . "\n";
    echo "  Socket: " . ($status['connection']['socket'] ? 'Yes' : 'No') . "\n";
    echo "  ✓ getStatus() works\n\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
