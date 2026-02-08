<?php
/**
 * Test alternative connection methods to ZKTeco device
 * Approach 1: Direct socket with keepalive
 * Approach 2: TCP instead of UDP (some devices support TCP)
 * Approach 3: Raw binary protocol with proper handshake
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

$device = \App\Models\Device::first();
$ip = $device->ip_address;
$port = $device->port;

echo "Testing Alternative Connection Methods\n";
echo "Device: $ip:$port\n";
echo str_repeat("=", 60) . "\n\n";

// Method 1: TCP Connection (some ZKTeco devices support TCP on 4370)
echo "METHOD 1: TCP Connection to port 4370\n";
echo str_repeat("-", 40) . "\n";
$sock = @fsockopen($ip, $port, $errno, $errstr, 3);
if ($sock) {
    echo "✓ TCP Connection established\n";
    
    // Send CONNECT command via TCP
    $cmd = pack('SSSS', 1000, 0, 0, 65535);  // CMD_CONNECT
    echo "Sending CONNECT packet...\n";
    $sent = @fwrite($sock, $cmd);
    echo "Sent: $sent bytes\n";
    
    // Try to receive
    stream_set_timeout($sock, 2);
    $response = @fread($sock, 1024);
    
    if (strlen($response) > 0) {
        echo "✓ Received " . strlen($response) . " bytes\n";
        echo "Response (hex): " . bin2hex($response) . "\n";
        
        // Parse header
        if (strlen($response) >= 8) {
            $h = unpack('S4', substr($response, 0, 8));
            echo "Response command: " . $h[1] . "\n";
        }
    } else {
        echo "✗ No response from device\n";
    }
    
    fclose($sock);
} else {
    echo "✗ TCP Connection failed: $errstr\n";
}

echo "\n";

// Method 2: Try UDP with connect socket and socket_send
echo "METHOD 2: UDP with socket_connect\n";
echo str_repeat("-", 40) . "\n";
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if ($socket) {
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 0]);
    
    // Try socket_connect on UDP
    $connected = @socket_connect($socket, $ip, $port);
    if ($connected) {
        echo "✓ UDP socket connected\n";
        
        // Send via socket_send (no address needed)
        $cmd = pack('SSSS', 1000, 0, 0, 65535);
        $sent = @socket_send($socket, $cmd, strlen($cmd), 0);
        
        if ($sent > 0) {
            echo "✓ Sent $sent bytes\n";
            
            $response = '';
            $recv = @socket_recv($socket, $response, 1024, 0);
            
            if ($recv > 0) {
                echo "✓ Received $recv bytes\n";
                echo "Response (hex): " . bin2hex($response) . "\n";
            } else {
                echo "✗ No response\n";
            }
        } else {
            echo "✗ Send failed\n";
        }
    } else {
        echo "✗ UDP socket_connect failed: " . socket_strerror(socket_last_error()) . "\n";
    }
    
    socket_close($socket);
} else {
    echo "✗ Socket creation failed\n";
}

echo "\n";

// Method 3: Try with Device model's getCustomData to see if there's config
echo "METHOD 3: Check Device Configuration\n";
echo str_repeat("-", 40) . "\n";
echo "Device model: {$device->model}\n";
echo "Device serial: {$device->serial_number}\n";
echo "Device location: {$device->location}\n";

// Try using ZKTeco library with TCP flag (if available)
echo "\nMETHOD 4: ZKTeco Library - Try with different settings\n";
echo str_repeat("-", 40) . "\n";

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

// Try without shouldPing
echo "Attempt 1: Without shouldPing\n";
$zk = new ZKTeco($ip, $port, false, 10, 0);
$result = @$zk->connect();
echo "Result: " . ($result ? 'true' : 'false') . "\n";

// Try with password 0
echo "\nAttempt 2: With explicit password 0\n";
$zk = new ZKTeco($ip, $port, false, 10, 0);
$result = @$zk->connect();
echo "Result: " . ($result ? 'true' : 'false') . "\n";

// Try with password 1
echo "\nAttempt 3: With password 1\n";
$zk = new ZKTeco($ip, $port, false, 10, 1);
$result = @$zk->connect();
echo "Result: " . ($result ? 'true' : 'false') . "\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "SUMMARY:\n";
echo "If Method 1 (TCP) works: Device supports TCP on port 4370\n";
echo "If Method 4 attempts work: Standard ZKTeco protocol works\n";
echo "Otherwise: Device may require different configuration or firmware update\n";
