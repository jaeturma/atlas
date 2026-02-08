<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

$device = \App\Models\Device::first();
$ip = $device->ip_address;

echo "=== Scanning common ZKTeco ports on {$ip} ===\n";

$commonPorts = [
    23 => 'Telnet',
    80 => 'HTTP',
    443 => 'HTTPS',
    502 => 'Modbus',
    4370 => 'ZKTeco standard',
    8080 => 'HTTP Alt',
    8000 => 'HTTP Alt 2',
    5000 => 'Flask/Dev',
    9200 => 'Elasticsearch',
];

foreach ($commonPorts as $port => $name) {
    $sock = @fsockopen($ip, $port, $errno, $errstr, 1);
    $status = $sock ? '✓ OPEN' : '✗ CLOSED';
    
    if ($sock) {
        echo "$port ($name): $status\n";
        
        // Try to get some data from the port
        stream_set_blocking($sock, false);
        stream_set_timeout($sock, 1);
        
        $banner = @fread($sock, 512);
        if (!empty($banner)) {
            echo "  Banner: " . json_encode(substr($banner, 0, 100)) . "\n";
        }
        
        fclose($sock);
    } else {
        echo "$port ($name): $status\n";
    }
}

echo "\n=== Checking HTTP/Web interface ===\n";

// Try HTTP on port 80 and 8080
foreach ([80, 8080] as $port) {
    echo "\nTrying HTTP on port $port:\n";
    $fp = @fsockopen($ip, $port, $errno, $errstr, 2);
    if ($fp) {
        $out = "GET / HTTP/1.1\r\nHost: {$ip}\r\nConnection: Close\r\n\r\n";
        fwrite($fp, $out);
        
        stream_set_blocking($fp, false);
        stream_set_timeout($fp, 2);
        
        $response = '';
        for ($i = 0; $i < 5; $i++) {
            $chunk = fread($fp, 1024);
            if ($chunk === false) break;
            $response .= $chunk;
        }
        fclose($fp);
        
        if (!empty($response)) {
            echo "✓ Got response (" . strlen($response) . " bytes)\n";
            echo "First 200 chars: " . substr($response, 0, 200) . "\n";
        } else {
            echo "✗ No response\n";
        }
    }
}
