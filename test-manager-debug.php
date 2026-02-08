<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$kernel->handle($request);

$device = \App\Models\Device::first();

echo "=== Protocol Manager Debug ===\n\n";

if ($device) {
    echo "Device: {$device->name}\n";
    echo "IP: {$device->ip_address}:{$device->port}\n";
    echo "Protocol Setting: " . ($device->protocol ?? 'auto') . "\n\n";
    
    // Test direct ADMS
    echo "Direct ADMS Test:\n";
    echo str_repeat("-", 60) . "\n";
    $adms = new \App\Services\ADMSProtocol($device->ip_address, $device->port);
    $adms_result = $adms->connect();
    echo "Result: " . ($adms_result ? "SUCCESS" : "FAILED") . "\n";
    echo "Error: " . ($adms->getLastError() ?: "None") . "\n\n";
    
    if ($adms_result) {
        $adms->disconnect();
    }
    
    // Test protocol manager step by step
    echo "Protocol Manager Debug:\n";
    echo str_repeat("-", 60) . "\n";
    
    $manager = new \App\Services\DeviceProtocolManager($device);
    echo "Manager created\n";
    
    // Check detection
    echo "Detecting protocol...\n";
    $manager->initialize();
    echo "Protocol type: " . $manager->getProtocolType() . "\n\n";
    
    // Get handler
    $handler = $manager->getHandler();
    echo "Handler class: " . get_class($handler) . "\n";
    
    // Try to connect directly with handler
    echo "Testing handler directly:\n";
    $handler_result = $handler->connect();
    echo "Handler connect result: " . ($handler_result ? "SUCCESS" : "FAILED") . "\n";
    echo "Handler error: " . ($handler->getLastError() ?: "None") . "\n\n";
    
    if ($handler_result) {
        $handler->disconnect();
    }
    
    // Now test through manager
    echo "Testing through manager:\n";
    $manager2 = new \App\Services\DeviceProtocolManager($device);
    $result = $manager2->connect();
    echo "Manager result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
} else {
    echo "No devices found\n";
}
