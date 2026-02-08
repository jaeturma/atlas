<?php
$device_ip = "10.0.0.25";
$device_port = 4370;
$local_port = 5555 + rand(0, 1000); // Random local port

echo "Testing UDP socket with binding\n";
echo "Device: $device_ip:$device_port\n";
echo "Local port: $local_port\n\n";

// Create UDP socket
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket) {
    echo "Failed to create socket\n";
    exit(1);
}

echo "1. Socket created\n";

// Set socket options
$timeout = ['sec' => 5, 'usec' => 0];
socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);
echo "2. Socket options set\n";

// Try to bind to local port
if (!socket_bind($socket, '0.0.0.0', $local_port)) {
    echo "Failed to bind to local port $local_port\n";
    echo "Socket error: " . socket_strerror(socket_last_error()) . "\n";
    socket_close($socket);
    exit(1);
}

echo "3. Socket bound to 0.0.0.0:$local_port\n";

// Send a simple packet to device (ping command would be Util::CMD_PING = 0x0001)
// For now just send 8 null bytes as a test
$test_packet = chr(0xFF) . chr(0xFF) . chr(0x00) . chr(0x00) . chr(0x00) . chr(0x00) . chr(0x00) . chr(0x00);

echo "\n4. Sending test packet to device...\n";
$sent = socket_sendto($socket, $test_packet, strlen($test_packet), 0, $device_ip, $device_port);
echo "   Bytes sent: $sent\n";

if ($sent === false) {
    echo "Failed to send packet\n";
    echo "Socket error: " . socket_strerror(socket_last_error()) . "\n";
    socket_close($socket);
    exit(1);
}

// Try to receive response
echo "5. Waiting for response...\n";
$data = '';
$from_ip = '';
$from_port = 0;

$result = @socket_recvfrom($socket, $data, 1024, 0, $from_ip, $from_port);

if ($result === false) {
    echo "   No response received (timeout or no data)\n";
    echo "   Socket error: " . socket_strerror(socket_last_error()) . "\n";
} else if ($result > 0) {
    echo "   Received $result bytes from $from_ip:$from_port\n";
    echo "   Data (hex): " . bin2hex($data) . "\n";
} else {
    echo "   0 bytes received\n";
}

socket_close($socket);
echo "\nDone\n";
