<?php
$ip = "10.0.0.25";
$port = 4370;

echo "Testing fsockopen behavior with UDP\n";
echo "IP: $ip, Port: $port\n\n";

// Try with TCP first
echo "1. Testing TCP:\n";
$sock_tcp = @fsockopen($ip, $port, $errno_tcp, $err_tcp, 2);
if ($sock_tcp) {
    echo "   Result: Connection opened/created\n";
    echo "   Type: " . gettype($sock_tcp) . "\n";
    fclose($sock_tcp);
} else {
    echo "   Result: Failed\n";
    echo "   Error: $err_tcp\n";
}

// Try with UDP
echo "\n2. Testing UDP:\n";
$sock_udp = @fsockopen('udp://'.$ip, $port, $errno_udp, $err_udp, 2);
if ($sock_udp) {
    echo "   Result: Connection opened/created\n";
    echo "   Type: " . gettype($sock_udp) . "\n";
    
    // Try to send and receive
    echo "\n3. Testing send/recv on UDP socket:\n";
    $test_data = "TEST";
    $sent = @fwrite($sock_udp, $test_data);
    echo "   Sent: $sent bytes\n";
    
    $recv = @fread($sock_udp, 1024);
    echo "   Received: " . strlen($recv) . " bytes\n";
    if (strlen($recv) > 0) {
        echo "   Data (hex): " . bin2hex($recv) . "\n";
    }
    
    fclose($sock_udp);
} else {
    echo "   Result: Failed\n";
    echo "   Error: $err_udp\n";
}

echo "\nConclusion:\n";
echo "TCP socket works like fsockopen() TCP = stream socket\n";
echo "UDP socket with fsockopen() is not reliable - use socket_*() functions instead\n";
