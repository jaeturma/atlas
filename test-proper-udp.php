<?php
$device_ip = "10.0.0.25";
$device_port = 4370;

echo "Testing UDP proper way - sendto with bound socket\n";
echo "Device: $device_ip:$device_port\n\n";

// Create UDP socket
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket) {
    echo "Failed to create socket\n";
    exit(1);
}

// Set socket options - SHORTER TIMEOUT
$timeout = ['sec' => 2, 'usec' => 0];
socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);
socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $timeout);

// Get local IP address that can reach the device
$local_ip = "0.0.0.0";
$local_port = 0;  // Let OS choose port

if (!socket_bind($socket, $local_ip, $local_port)) {
    $err = socket_last_error($socket);
    echo "Bind failed: " . socket_strerror($err) . "\n";
    socket_close($socket);
    exit(1);
}

// Get actual bound address
$sock_name = '';
socket_getsockname($socket, $sock_name, $bound_port);
echo "Socket bound to $sock_name:$bound_port\n\n";

// Create CONNECT command
$command = 1000;     // CMD_CONNECT
$checksum = 0;
$session_id = 0;
$reply_id = 65535;

// Build packet - THIS IS SIMPLE FORMAT, NOT WITH FULL CHECKSUM CALCULATION
$header = pack('SSSS', $command, $checksum, $session_id, $reply_id);

echo "Sending CONNECT packet\n";
echo "Header (hex): " . bin2hex($header) . "\n";
echo "Header breakdown:\n";
echo "  Command (1000): " . dechex($command) . "\n";
echo "  Checksum (0): " . dechex($checksum) . "\n";
echo "  Session (0): " . dechex($session_id) . "\n";
echo "  Reply ID (65535): " . dechex($reply_id) . "\n";

$sent = socket_sendto($socket, $header, strlen($header), 0, $device_ip, $device_port);

if ($sent === false) {
    $err = socket_last_error($socket);
    echo "Send error: " . socket_strerror($err) . " (Code: $err)\n";
    socket_close($socket);
    exit(1);
}

echo "Sent: $sent bytes\n\n";

// Wait for response
echo "Waiting for response (timeout 2 sec)...\n";
$response = '';
$from_ip = '';
$from_port = 0;

// Clear error state
socket_clear_error($socket);

$recv = @socket_recvfrom($socket, $response, 2048, 0, $from_ip, $from_port);

$err = socket_last_error($socket);

if ($recv === false) {
    echo "Receive error: " . socket_strerror($err) . " (Code: $err)\n";
} else if ($recv > 0) {
    echo "✓ Received $recv bytes from $from_ip:$from_port\n";
    echo "Response (hex): " . bin2hex($response) . "\n";
    
    // Parse response header
    if ($recv >= 8) {
        $header_data = unpack('S4', substr($response, 0, 8));
        echo "\nResponse header breakdown:\n";
        echo "  Command: " . $header_data[1] . "\n";
        echo "  Checksum: " . $header_data[2] . "\n";
        echo "  Session ID: " . $header_data[3] . "\n";
        echo "  Reply ID: " . $header_data[4] . "\n";
    }
} else {
    echo "0 bytes received (timeout)\n";
}

socket_close($socket);
