<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$kernel->handle($request);

$device = \App\Models\Device::first();

echo "=== Device Configuration ===\n\n";

if ($device) {
    echo "Device ID: " . $device->id . "\n";
    echo "Name: " . $device->name . "\n";
    echo "Model: " . ($device->model ?? 'not set') . "\n";
    echo "IP: " . $device->ip_address . "\n";
    echo "Port: " . $device->port . "\n";
    echo "Protocol: " . ($device->protocol ?? 'not set') . "\n";
    echo "Timezone: " . ($device->timezone ?? 'not set') . "\n";
    echo "Is Active: " . ($device->is_active ? 'Yes' : 'No') . "\n";
    
    echo "\n=== Testing Connection ===\n\n";
    
    // Test ping
    $ping_cmd = 'ping -n 1 -w 1000 ' . escapeshellarg($device->ip_address);
    exec($ping_cmd, $output, $result_code);
    echo "Ping Result: " . ($result_code === 0 ? 'SUCCESS' : 'FAILED') . "\n";
    
    // Test socket
    $socket = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 2);
    $socket_ok = ($socket !== false);
    echo "Socket Connection: " . ($socket_ok ? 'SUCCESS' : 'FAILED') . "\n";
    if ($socket) {
        fclose($socket);
    }
    
    // Test protocol manager
    echo "\n=== Testing Protocol Manager ===\n\n";
    
    $manager = new \App\Services\DeviceProtocolManager($device);
    $result = $manager->connect();
    
    echo "Connection Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
    $manager->disconnect();
} else {
    echo "No devices found in database\n";
}
