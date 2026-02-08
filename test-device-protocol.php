<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

$device = \App\Models\Device::first();

echo "Device: {$device->name}\n";
echo "IP: {$device->ip_address}\n";
echo "Port: {$device->port}\n\n";

$sock = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 5);

if ($sock) {
    echo "✓ Socket connection successful!\n";
    
    // Set non-blocking mode
    stream_set_blocking($sock, false);
    stream_set_timeout($sock, 2);
    
    // Try different commands
    $commands = [
        "CONNECT\n",
        "CONNECT",
        "CONNECT\r\n",
        "GLOG\n",
        "\n",
    ];
    
    foreach ($commands as $i => $cmd) {
        echo "\nAttempt " . ($i + 1) . ": Sending command: " . json_encode($cmd) . "\n";
        
        $sock = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 5);
        if (!$sock) {
            echo "  Connection failed\n";
            continue;
        }
        
        fwrite($sock, $cmd);
        fflush($sock);
        
        usleep(500000); // Wait 500ms for response
        
        $response = "";
        while ($data = fread($sock, 1024)) {
            $response .= $data;
            if (strlen($response) > 1000) break;
        }
        
        if (!empty($response)) {
            echo "  Response: " . json_encode(substr($response, 0, 100)) . "\n";
            echo "  Hex dump: " . bin2hex(substr($response, 0, 50)) . "\n";
        } else {
            echo "  No response\n";
        }
        
        fclose($sock);
    }
    
} else {
    echo "✗ Socket connection failed: $errstr (Error $errno)\n";
}
