<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$kernel->handle($request);

$device = \App\Models\Device::first();

echo "=== Testing ADMS PUSH and Web API ===\n\n";

if ($device) {
    $ip = $device->ip_address;
    $port = $device->port;
    
    echo "Device: {$device->name} ({$device->model})\n";
    echo "IP: $ip:$port\n\n";
    
    // Test 1: Web API (HTTP)
    echo "Test 1: Web API (HTTP)\n";
    echo str_repeat("-", 60) . "\n";
    
    $urls = [
        "http://$ip:8080/api/device",
        "http://$ip:80/api/device",
        "http://$ip/api/device",
    ];
    
    foreach ($urls as $url) {
        echo "Trying: $url\n";
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                echo "  Error: $error\n";
            } else {
                echo "  HTTP Code: $http_code\n";
                if ($response) {
                    echo "  Response (first 200 chars): " . substr($response, 0, 200) . "\n";
                }
            }
        } catch (\Exception $e) {
            echo "  Exception: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    
    // Test 2: ADMS PUSH mode (TCP on 4370)
    echo "Test 2: ADMS PUSH Mode (TCP 4370)\n";
    echo str_repeat("-", 60) . "\n";
    
    try {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 3, 'usec' => 0]);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 3, 'usec' => 0]);
        
        $connected = socket_connect($socket, $ip, $port);
        echo "Socket Connect: " . ($connected ? "SUCCESS\n" : "FAILED\n");
        
        if ($connected) {
            // Try ADMS PUSH handshake
            $push_header = "hPUSH";
            $version = "\x01\x00";
            $password = pack('I', 0);
            
            $packet = $push_header . $version . $password;
            echo "Sending PUSH handshake packet\n";
            
            $sent = socket_send($socket, $packet, strlen($packet), 0);
            echo "Bytes sent: $sent\n";
            
            // Try to read response
            $response = @socket_read($socket, 1024, PHP_BINARY_READ);
            if ($response !== false && !empty($response)) {
                echo "Received response: " . strlen($response) . " bytes\n";
                echo "Response hex: " . bin2hex(substr($response, 0, 20)) . "\n";
            } else {
                echo "No response received\n";
            }
            
            socket_close($socket);
        }
    } catch (\Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // Test 3: Alternative ADMS ports
    echo "Test 3: Alternative ADMS Ports\n";
    echo str_repeat("-", 60) . "\n";
    
    $alt_ports = [8080, 8081, 9001, 9002, 5000, 5001];
    
    foreach ($alt_ports as $alt_port) {
        echo "Trying port $alt_port... ";
        $socket = @fsockopen($ip, $alt_port, $errno, $errstr, 1);
        if ($socket !== false) {
            echo "OPEN\n";
            fclose($socket);
        } else {
            echo "closed\n";
        }
    }
    
} else {
    echo "No devices found\n";
}
