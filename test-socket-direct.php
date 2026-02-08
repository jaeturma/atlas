<?php
echo "Testing socket functions\n";

$sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
echo "Socket created: " . ($sock ? 'Yes' : 'No') . "\n";

if ($sock) {
    echo "Socket resource type: " . get_resource_type($sock) . "\n";
    
    $result = @socket_bind($sock, '0.0.0.0', 0);
    echo "Socket bind: " . ($result ? 'Success' : 'Failed') . "\n";
    
    if (!$result) {
        echo "Bind error: " . socket_strerror(socket_last_error()) . "\n";
    }
    
    @socket_close($sock);
} else {
    echo "Socket creation error: " . socket_strerror(socket_last_error()) . "\n";
}
