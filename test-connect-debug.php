<?php
require 'vendor/autoload.php';

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

$device_ip = "10.0.0.25";
$device_port = 4370;

echo "Testing ZKTeco Connect - Debugging\n";
echo "Device: $device_ip:$device_port\n\n";

try {
    // Create instance without automatic ping
    $zk = new ZKTeco($device_ip, $device_port, false, 25);
    echo "1. ZKTeco instance created\n";
    
    // Check socket
    echo "2. Checking socket...\n";
    echo "   Socket type: " . gettype($zk->_zkclient) . "\n";
    echo "   Socket resource ID: " . spl_object_hash($zk->_zkclient) . "\n";
    
    // Try ping
    echo "3. Testing ping...\n";
    $ping = @$zk->ping(false);
    echo "   Ping result: " . ($ping ? 'true' : 'false') . "\n";
    
    // Now try connect - but let's trace what happens
    echo "4. Attempting connect...\n";
    
    // Manually call connect and see what data we get
    $zk->_section = "Connect Attempt";
    
    // Replicate what Connect::connect does
    $command = 1000; // CMD_CONNECT
    $command_string = '';
    $chksum = 0;
    $session_id = 0;
    $reply_id = 65535;
    
    echo "   Command: $command\n";
    echo "   Session ID: $session_id\n";
    echo "   Reply ID: $reply_id\n";
    
    // Create header packet
    $header = pack('SSSS', $command, $chksum, $session_id, $reply_id);
    echo "   Header (before checksum): " . bin2hex($header) . "\n";
    
    // Now let's send manually
    echo "   Sending packet...\n";
    $sent = socket_sendto($zk->_zkclient, $header, strlen($header), 0, $device_ip, $device_port);
    echo "   Bytes sent: $sent\n";
    
    if ($sent === false) {
        echo "   Send failed: " . socket_strerror(socket_last_error($zk->_zkclient)) . "\n";
        exit(1);
    }
    
    // Try to receive
    echo "   Waiting for response...\n";
    $response = '';
    $from_ip = '';
    $from_port = 0;
    
    $recv = @socket_recvfrom($zk->_zkclient, $response, 1024, 0, $from_ip, $from_port);
    
    if ($recv === false) {
        $err = socket_last_error($zk->_zkclient);
        echo "   Receive failed: " . socket_strerror($err) . " (Error code: $err)\n";
    } else if ($recv > 0) {
        echo "   Received $recv bytes from $from_ip:$from_port\n";
        echo "   Response (hex): " . bin2hex($response) . "\n";
        echo "   Response (first 8 bytes): " . bin2hex(substr($response, 0, 8)) . "\n";
    } else {
        echo "   0 bytes received (timeout)\n";
    }
    
    // Now try via library
    echo "\n5. Attempting via library connect()...\n";
    $result = @$zk->connect();
    echo "   Result: " . ($result ? 'true' : 'false') . "\n";
    echo "   Session ID after connect: " . $zk->_session_id . "\n";
    echo "   Data recv length: " . strlen($zk->_data_recv) . "\n";
    if (strlen($zk->_data_recv) > 0) {
        echo "   Data recv (hex): " . bin2hex(substr($zk->_data_recv, 0, min(32, strlen($zk->_data_recv)))) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
