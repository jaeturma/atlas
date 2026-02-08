<?php
/**
 * Comprehensive ZKTeco Protocol Test
 * Tests the exact protocol that devices expect
 */

$device_ip = "10.0.0.25";
$device_port = 4370;

echo "===  ZKTeco Protocol Communication Test ===\n";
echo "Device: $device_ip:$device_port\n\n";

//  Socket setup with full error handling
function test_protocol($ip, $port) {
    echo "Creating socket...\n";
    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if (!$socket) {
        echo "ERROR: socket_create failed\n";
        return false;
    }
    
    // Set non-blocking mode to avoid infinite hangs
    socket_set_nonblock($socket);
    
    // Set socket timeout
    $timeout = ['sec' => 1, 'usec' => 0];
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);
    
    echo "Socket created\n";
    
    // Build a CONNECT packet per ZKTeco protocol
    // Structure: 2-byte command + 2-byte checksum + 2-byte session + 2-byte reply_id
    $command = 1000;        // CMD_CONNECT
    $checksum = 0;
    $session = 0;
    $reply_id = 65535;
    
    // Simple packet format (without proper checksum, device might still respond)
    $packet = pack('SSSS', $command, $checksum, $session, $reply_id);
    
    echo "Packet details:\n";
    echo "  Command: $command (0x" . dechex($command) . ")\n";
    echo "  Checksum: $checksum\n";
    echo "  Session: $session\n";
    echo "  Reply ID: $reply_id\n";
    echo "  Packet (hex): " . bin2hex($packet) . "\n";
    echo "  Packet size: " . strlen($packet) . " bytes\n\n";
    
    // Send packet
    echo "Sending CONNECT packet...\n";
    $flags = 0;
    $ret = socket_sendto($socket, $packet, strlen($packet), $flags, $ip, $port);
    
    if ($ret === false) {
        $err = socket_last_error($socket);
        echo "ERROR sending: " . socket_strerror($err) . " (Code: $err)\n";
        socket_close($socket);
        return false;
    }
    
    echo "✓ Sent $ret bytes\n\n";
    
    // Try to receive response (non-blocking)
    echo "Waiting for response (1 sec timeout)...\n";
    
    $response = '';
    $from_ip = '';
    $from_port = 0;
    $attempts = 10;
    $waited = 0;
    
    while ($attempts > 0 && $waited < 1000) {
        $ret = @socket_recvfrom($socket, $response, 2048, 0, $from_ip, $from_port);
        
        if ($ret === false) {
            $err = socket_last_error($socket);
            // Error code 10035 is WSAEWOULDBLOCK (non-blocking, no data)
            // Error code 10054 is connection reset
            if ($err == 10035) {
                // Non-blocking socket, no data yet
                usleep(100000);  // Wait 100ms
                $waited += 100;
                $attempts--;
                continue;
            } else {
                echo "ERROR receiving: " . socket_strerror($err) . " (Code: $err)\n";
                socket_close($socket);
                return false;
            }
        } else if ($ret > 0) {
            echo "✓ Received $ret bytes from $from_ip:$from_port\n\n";
            
            echo "Response (hex): " . bin2hex($response) . "\n\n";
            
            // Parse response
            if ($ret >= 8) {
                $header = unpack('S4', substr($response, 0, 8));
                echo "Response header:\n";
                echo "  Command: " . $header[1] . " (0x" . dechex($header[1]) . ")\n";
                echo "  Checksum: " . $header[2] . "\n";
                echo "  Session: " . $header[3] . "\n";
                echo "  Reply ID: " . $header[4] . "\n";
                
                if ($header[1] == 2005) {
                    echo "  → Device returned CMD_ACK_UNAUTH (requires password)\n";
                } else if ($header[1] == 2000) {
                    echo "  → Device returned CMD_ACK_OK (success)\n";
                } else if ($header[1] == 2001) {
                    echo "  → Device returned CMD_ACK_ERROR\n";
                }
            }
            
            socket_close($socket);
            return true;
        } else {
            echo "0 bytes received\n";
            break;
        }
    }
    
    echo "No response received after timeout\n";
    socket_close($socket);
    return false;
}

// Run test
$result = test_protocol($device_ip, $device_port);

echo "\n" . str_repeat("=", 50) . "\n";
if ($result) {
    echo "✓ Device communication successful!\n";
} else {
    echo "✗ Device communication failed\n";
    echo "\nPossible reasons:\n";
    echo "1. Device IP is incorrect\n";
    echo "2. Device is not on network or powered off\n";
    echo "3. Network firewall is blocking port 4370\n";
    echo "4. Device firmware doesn't support this protocol\n";
    echo "5. Device is in a different network segment\n";
}
