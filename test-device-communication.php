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

// Try direct socket connection
echo "Attempting socket connection...\n";
$sock = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 5);

if ($sock) {
    echo "✓ Socket connection successful!\n";
    
    // Try sending a command
    echo "\nAttempting to send command...\n";
    fwrite($sock, "GET_LOG\t2025-12-08\t2025-12-08\n");
    
    echo "Waiting for response...\n";
    $response = "";
    $timeout = time() + 3;
    
    while (!feof($sock) && time() < $timeout) {
        $line = fgets($sock, 1024);
        if (!empty($line)) {
            $response .= $line . "\n";
            if (strlen($response) > 5000) break; // Limit response size
        }
    }
    
    if (!empty($response)) {
        echo "Response received:\n";
        echo substr($response, 0, 500) . (strlen($response) > 500 ? "..." : "") . "\n";
    } else {
        echo "No response received\n";
    }
    
    fclose($sock);
} else {
    echo "✗ Socket connection failed: $errstr (Error $errno)\n";
    echo "\nTrying ping test...\n";
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $output = @shell_exec("ping -n 1 -w 1000 {$device->ip_address}");
        if (strpos($output, 'Reply') !== false || strpos($output, 'bytes') !== false) {
            echo "✓ Device is reachable via ping\n";
        } else {
            echo "✗ Device is not reachable\n";
        }
    }
}
