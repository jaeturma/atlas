<?php
$device_ip = "10.0.0.25";
$device_port = 4370;

echo "Testing UDP with proper socket binding and connect\n";
echo "Device: $device_ip:$device_port\n\n";

// Create UDP socket
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket) {
    echo "Failed to create socket\n";
    exit(1);
}

// Set socket options
$timeout = ['sec' => 5, 'usec' => 0];
socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);

// Bind to local interface on random port
$local_port = 5555;
if (!socket_bind($socket, '127.0.0.1', $local_port)) {
    echo "Bind failed\n";
    socket_close($socket);
    exit(1);
}
echo "Socket bound to 127.0.0.1:$local_port\n";

// Try connecting UDP socket to device (sets default destination)
echo "Attempting UDP socket_connect...\n";
$connect_result = @socket_connect($socket, $device_ip, $device_port);
echo "Connect result: " . ($connect_result ? 'true' : 'false') . "\n";

if (!$connect_result) {
    echo "Connect error: " . socket_strerror(socket_last_error($socket)) . "\n";
}

// Now send packet without specifying address
$header = pack('SSSS', 1000, 0, 0, 65535);
echo "\nSending CONNECT packet...\n";
echo "Header (hex): " . bin2hex($header) . "\n";

$sent = @socket_send($socket, $header, strlen($header), 0);
echo "Bytes sent: " . ($sent === false ? "error - " . socket_strerror(socket_last_error($socket)) : $sent) . "\n";

// Try to receive  
echo "Waiting for response...\n";
$response = '';
$recv = @socket_recv($socket, $response, 1024, 0);

if ($recv === false) {
    $err = socket_last_error($socket);
    echo "Receive failed: " . socket_strerror($err) . " (Error: $err)\n";
} else if ($recv > 0) {
    echo "Received $recv bytes\n";
    echo "Response (hex): " . bin2hex($response) . "\n";
} else {
    echo "0 bytes received\n";
}

socket_close($socket);
