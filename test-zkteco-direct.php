<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

$device = \App\Models\Device::first();

echo "Testing ZKTeco UDP connection\n";
echo "IP: {$device->ip_address}\n";
echo "Port: {$device->port}\n\n";

// Try different approaches
try {
    // Approach 1: Basic instantiation and ping
    echo "Approach 1: Direct ping test\n";
    $zk = new ZKTeco($device->ip_address, $device->port);
    $ping = @$zk->ping(false);
    echo "Ping result: " . json_encode($ping) . "\n\n";
} catch (Exception $e) {
    echo "Ping error: " . $e->getMessage() . "\n\n";
}

try {
    // Approach 2: Connect then get data
    echo "Approach 2: Connect and get version\n";
    $zk = new ZKTeco($device->ip_address, $device->port, false, 25);
    
    echo "Connecting...\n";
    $connected = @$zk->connect();
    echo "Connected: " . ($connected ? "Yes" : "No") . "\n";
    
    if ($connected) {
        echo "Getting version...\n";
        $version = @$zk->version();
        echo "Version: " . json_encode($version) . "\n";
        
        echo "Getting attendance...\n";
        $logs = [];
        $result = $zk->getAttendances(function($data) use (&$logs) {
            $logs[] = $data;
            return true;
        });
        echo "Attendance result: " . ($result ? "Success" : "Failed") . "\n";
        echo "Logs retrieved: " . count($logs) . "\n";
        
        if (count($logs) > 0) {
            echo "First log: " . json_encode($logs[0]) . "\n";
        }
        
        $zk->disconnect();
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
