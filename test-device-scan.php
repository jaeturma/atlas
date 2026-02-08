<?php
$ip = "10.0.0.25";

echo "Scanning device for common ports and services\n";
echo "Device IP: $ip\n\n";

$ports = [
    21 => "FTP",
    22 => "SSH",
    80 => "HTTP",
    443 => "HTTPS",
    502 => "Modbus",
    4370 => "ZKTeco(UDP)",
    8000 => "HTTP-Alt",
    8080 => "HTTP-Proxy",
    8888 => "HTTP-Alt2",
    9090 => "HTTP-Alt3",
    5555 => "ZKTeco-Alt",
];

foreach ($ports as $port => $service) {
    $sock = @fsockopen($ip, $port, $errno, $errstr, 2);
    if ($sock) {
        echo "✓ Port $port ($service) - OPEN\n";
        fclose($sock);
    } else {
        echo "  Port $port ($service) - CLOSED\n";
    }
}

echo "\nChecking for ZKTeco web interface...\n";
$urls = [
    "http://$ip/",
    "http://$ip/system/",
    "http://$ip:8000/",
];

foreach ($urls as $url) {
    echo "\nTrying: $url\n";
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 2]]));
    if ($response !== false) {
        echo "✓ Got response\n";
        echo "Content length: " . strlen($response) . " bytes\n";
        // Try to identify it
        if (stripos($response, 'zk') !== false) {
            echo "Contains 'ZK' - likely ZKTeco device\n";
        }
        if (stripos($response, 'html') !== false) {
            echo "Contains HTML\n";
            echo substr($response, 0, 500) . "...\n";
        }
    } else {
        echo "No response\n";
    }
}
