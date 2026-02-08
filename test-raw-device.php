<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

$device = \App\Models\Device::first();

echo "=== ZKTeco Device Raw Communication Test ===\n";
echo "Device: {$device->name} ({$device->ip_address}:{$device->port})\n\n";

$sock = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 10);

if (!$sock) {
    echo "❌ Failed to connect: $errstr\n";
    exit;
}

echo "✓ Connected to device\n";

// Set to non-blocking to read whatever data is available
stream_set_blocking($sock, false);
stream_set_timeout($sock, 1);

// Try various commands - ZKTeco uses binary protocol
// Common commands: GLOG (get log), TIME (get time), etc.

$commands = [
    // Try common command patterns
    ['name' => 'GLOG command', 'data' => "GLOG\r\n"],
    ['name' => 'TIME command', 'data' => "TIME\r\n"],
    ['name' => 'CONNECT', 'data' => "CONNECT\r\n"],
    ['name' => 'Binary GLOG (0x04)', 'data' => "\x04\x00\x00\x00"],
    ['name' => 'Device info request', 'data' => "DEVICEINFO\r\n"],
];

foreach ($commands as $cmd) {
    // Reconnect for each test
    if (is_resource($sock)) {
        fclose($sock);
    }
    
    $sock = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 5);
    if (!$sock) continue;
    
    stream_set_blocking($sock, false);
    stream_set_timeout($sock, 2);
    
    echo "\n--- Testing: {$cmd['name']} ---\n";
    echo "Sending: " . json_encode($cmd['data']) . "\n";
    
    @fwrite($sock, $cmd['data']);
    @fflush($sock);
    
    usleep(500000); // Wait 500ms
    
    $response = "";
    $readAttempts = 0;
    while ($readAttempts < 5) {
        $chunk = @fread($sock, 1024);
        if ($chunk === false || $chunk === '') {
            $readAttempts++;
            usleep(100000); // Wait 100ms between attempts
            continue;
        }
        $response .= $chunk;
        $readAttempts = 0;
    }
    
    if (!empty($response)) {
        echo "Response length: " . strlen($response) . " bytes\n";
        echo "Raw (first 200 bytes): " . json_encode(substr($response, 0, 200)) . "\n";
        echo "Hex dump: " . bin2hex(substr($response, 0, 100)) . "\n";
        
        // Try to detect if it contains readable text
        $printable = preg_replace('/[^\x20-\x7E\r\n\t]/', '.', $response);
        echo "Printable: " . substr($printable, 0, 100) . "\n";
    } else {
        echo "No response received\n";
    }
    
    if (is_resource($sock)) {
        fclose($sock);
    }
}

echo "\n=== Test Complete ===\n";
