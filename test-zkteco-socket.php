<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

$device = \App\Models\Device::first();

echo "Testing ZKTeco UDP socket\n";
echo "Creating socket...\n";

$sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
echo "Socket type: " . get_class($sock) . "\n";
echo "Socket created: " . ($sock ? 'Yes' : 'No') . "\n";

if ($sock) {
    echo "\nTesting UDP communication directly:\n";
    
    // Try to send a ping packet
    echo "1. Creating ZKTeco instance...\n";
    $zk = new ZKTeco($device->ip_address, $device->port);
    echo "   ZKTeco instance created\n";
    echo "   Socket type in ZKTeco: " . get_class($zk->_zkclient) . "\n";
    
    echo "2. Calling ping...\n";
    try {
        $result = @$zk->ping(false);
        echo "   Ping result: " . ($result ? 'Success/True' : 'Failed/False') . "\n";
    } catch (Exception $e) {
        echo "   Ping exception: " . $e->getMessage() . "\n";
    }
}
