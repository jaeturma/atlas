<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$kernel->handle($request);

$device = \App\Models\Device::first();

echo "=== Detailed Protocol Testing ===\n\n";

if ($device) {
    echo "Testing ADMS Protocol\n";
    echo str_repeat("-", 60) . "\n";
    
    try {
        $adms = new \App\Services\ADMSProtocol(
            $device->ip_address,
            $device->port ?? 4370,
            25,
            0
        );
        
        echo "ADMS Instance Created\n";
        
        $connected = $adms->connect();
        echo "ADMS Connect Result: " . ($connected ? 'SUCCESS' : 'FAILED') . "\n";
        echo "ADMS Error: " . $adms->getLastError() . "\n";
        
        if ($connected) {
            $adms->disconnect();
        }
    } catch (\Throwable $e) {
        echo "ADMS Exception: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    echo "Testing ZKEM Protocol\n";
    echo str_repeat("-", 60) . "\n";
    
    try {
        $zkem = new \App\Services\ZKTecoWrapper(
            $device->ip_address,
            $device->port ?? 4370,
            false,
            25,
            0
        );
        
        echo "ZKEM Instance Created\n";
        
        $connected = $zkem->connect();
        echo "ZKEM Connect Result: " . ($connected ? 'SUCCESS' : 'FAILED') . "\n";
        echo "ZKEM Error: " . $zkem->getLastError() . "\n";
    } catch (\Throwable $e) {
        echo "ZKEM Exception: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    echo "Testing Raw Socket Connection\n";
    echo str_repeat("-", 60) . "\n";
    
    try {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        echo "Socket Created\n";
        
        if ($socket !== false) {
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 5, 'usec' => 0]);
            
            $result = socket_connect($socket, $device->ip_address, $device->port);
            echo "Socket Connect Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
            
            if (!$result) {
                echo "Socket Error: " . socket_strerror(socket_last_error($socket)) . "\n";
            } else {
                echo "Socket Connected Successfully\n";
            }
            
            socket_close($socket);
        }
    } catch (\Throwable $e) {
        echo "Socket Exception: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "No devices found\n";
}
